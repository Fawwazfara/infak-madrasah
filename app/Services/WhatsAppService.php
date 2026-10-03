<?php

namespace App\Services;

use App\Models\WaOutbox;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Kirim pesan WhatsApp langsung via Fonnte API (tanpa jeda).
     * Panggil hanya lewat process() agar delay antar pesan selalu terjaga.
     *
     * @return bool
     */
    public static function sendMessage($phone, $message)
    {
        $token = config('services.fonnte.token');

        if (empty($token)) {
            Log::warning('Fonnte token is missing. WhatsApp message not sent.');
            return false;
        }

        if (empty($phone)) {
            return false;
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);
        $url = 'https://api.fonnte.com/send';

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->connectTimeout(5)->post($url, [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp message sent to {$phone}", ['response' => $response->json()]);
                return true;
            }

            Log::error("Failed to send WhatsApp message to {$phone}", ['response' => $response->json()]);
            return false;
        } catch (\Exception $e) {
            Log::error("Exception when sending WhatsApp message to {$phone}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Antrekan pesan ke outbox (tidak langsung dikirim).
     */
    public static function queue(string $phone, string $message, ?string $groupKey = null): WaOutbox
    {
        return WaOutbox::create([
            'phone' => $phone,
            'message' => $message,
            'group_key' => $groupKey,
            'status' => 'pending',
        ]);
    }

    public static function pendingCount(): int
    {
        return WaOutbox::where('status', 'pending')->count();
    }

    /**
     * Sisa detik sebelum pesan berikutnya boleh dikirim.
     */
    public static function retryAfter(): int
    {
        $delay = (int) config('services.fonnte.delay', 60);
        $last = (int) Cache::get('fonnte_last_sent_at', 0);
        $elapsed = time() - $last;

        return max(0, $delay - $elapsed);
    }

    /**
     * Kirim pesan dari outbox dengan jeda minimal FONNTE_DELAY detik
     * antar pesan supaya nomor tidak diblokir WhatsApp.
     *
     * Tidak pernah mengeblok worker (sleep) di dalam request: bila jeda
     * belum tercapai, metode ini langsung mengembalikan retry_after dan
     * frontend yang menunggu, sehingga server tetap responsif.
     *
     * @return array{sent:int, failed:int, remaining:int, retry_after:int}
     */
    public static function process(int $limit = 1): array
    {
        $limit = max(1, min(3, $limit));
        $sent = 0;
        $failed = 0;

        // Kunci antar tab/pengguna: mencegah dua request mengirim bersamaan
        // dan melanggar jeda delay. Kunci kedaluwarsa otomatis (10 dtk).
        $lock = Cache::lock('fonnte_outbox_send', 10);

        if ($lock->get()) {
            try {
                $items = WaOutbox::where('status', 'pending')
                    ->orderBy('id')
                    ->limit($limit)
                    ->get();

                foreach ($items as $item) {
                    // Jeda antar pesan belum tercapai: kirim retry_after ke client.
                    if (self::retryAfter() > 0) {
                        break;
                    }

                    $ok = self::sendMessage($item->phone, $item->message);

                    Cache::put('fonnte_last_sent_at', time(), 60 * 60);

                    if ($ok) {
                        $item->update(['status' => 'sent', 'sent_at' => now()]);
                        $sent++;
                    } else {
                        $item->update(['status' => 'failed', 'error' => 'Fonnte mengembalikan error, cek token/koneksi.']);
                        $failed++;
                    }

                    // Dengan delay aktif, cukup 1 pesan per request agar jeda
                    // FONNTE_DELAY selalu terpenuhi sebelum pesan berikutnya.
                    if (self::retryAfter() > 0) {
                        break;
                    }
                }
            } finally {
                $lock->release();
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'remaining' => self::pendingCount(),
            'retry_after' => self::retryAfter(),
        ];
    }
}
