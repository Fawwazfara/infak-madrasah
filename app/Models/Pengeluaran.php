<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $fillable = ['keterangan', 'jumlah', 'tanggal', 'kategori'];

    /**
     * Tentukan apakah baris pengeluaran termasuk kasbon guru.
     */
    public function isKasbonGuru(): bool
    {
        $kategori = strtolower(trim((string) $this->kategori));
        $keterangan = strtolower(trim((string) $this->keterangan));

        return $kategori === 'kasbon guru' || str_contains($keterangan, 'kasbon');
    }

    /**
     * Samarkan seluruh entri kasbon guru untuk tampilan role guru
     * (contoh: "Kasbon Ust. Syukron" -> "Kasbon A") tanpa mengubah database.
     */
    public static function maskKasbonForGuru(Collection $items): Collection
    {
        // Urutkan sementara berdasarkan ID naik agar pemetaan huruf A, B, C stabil
        $orderedKasbon = $items
            ->filter(fn ($item) => $item instanceof self ? $item->isKasbonGuru() : self::rowIsKasbon($item))
            ->sortBy('id');

        $letterMap = [];
        $counter = 0;

        foreach ($orderedKasbon as $item) {
            $key = self::extractKasbonSubjectKey((string) ($item->keterangan ?? ''), (int) ($item->id ?? 0));
            if (!isset($letterMap[$key])) {
                $letterMap[$key] = self::indexToAlpha($counter);
                $counter++;
            }
        }

        return $items->map(function ($item) use ($letterMap) {
            $isKasbon = $item instanceof self ? $item->isKasbonGuru() : self::rowIsKasbon($item);
            if (!$isKasbon) {
                if ($item instanceof self) {
                    $item->setAttribute('is_anonymized', false);
                }
                return $item;
            }

            $key = self::extractKasbonSubjectKey((string) ($item->keterangan ?? ''), (int) ($item->id ?? 0));
            $code = $letterMap[$key] ?? 'A';

            $cloned = $item instanceof self ? clone $item : clone (object) $item;
            $cloned->keterangan = 'Kasbon ' . $code;
            $cloned->kategori = 'Kasbon Guru';
            if ($cloned instanceof self) {
                $cloned->setAttribute('is_anonymized', true);
            } else {
                $cloned->is_anonymized = true;
            }

            return $cloned;
        })->values();
    }

    private static function rowIsKasbon($row): bool
    {
        $kategori = strtolower(trim((string) ($row->kategori ?? '')));
        $keterangan = strtolower(trim((string) ($row->keterangan ?? '')));

        return $kategori === 'kasbon guru' || str_contains($keterangan, 'kasbon');
    }

    private static function extractKasbonSubjectKey(string $keterangan, int $fallbackId): string
    {
        $clean = strtolower($keterangan);
        $clean = preg_replace('/\b(kasbon|guru|ustadzah|ustadz|ust|bapak|bpk|ibu|pak|bu|gaji|awal)\b/iu', ' ', $clean);
        $clean = preg_replace('/[^a-z0-9]+/i', ' ', (string) $clean);
        $clean = trim((string) $clean);

        return $clean !== '' ? $clean : 'anon_' . $fallbackId;
    }

    private static function indexToAlpha(int $index): string
    {
        $label = '';
        $n = $index;
        do {
            $label = chr(65 + ($n % 26)) . $label;
            $n = intdiv($n, 26) - 1;
        } while ($n >= 0);

        return $label;
    }
}
