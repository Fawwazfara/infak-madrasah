<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Infak;
use App\Models\LogAktivitas;
use App\Models\Siswa;

class InfakController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Infak::with(['siswa', 'kelas'])->orderBy('tanggal_bayar', 'desc');

        if ($user->role === 'guru') {
            $kelasIds = \App\Models\Kelas::where('guru_id', $user->id)->pluck('id');
            $query->whereIn('kelas_id', $kelasIds);
        }

        return response()->json($query->get());
    }

    public function getTerbaru(Request $request)
    {
        $user = $request->user();
        $query = Infak::with(['siswa', 'kelas'])->orderBy('created_at', 'desc')->take(5);

        if ($user->role === 'guru') {
            $kelasIds = \App\Models\Kelas::where('guru_id', $user->id)->pluck('id');
            $query->whereIn('kelas_id', $kelasIds);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal_bayar' => 'required|date',
            'nominal' => 'required|numeric',
            'months' => 'required|array',
            'siswa_id' => 'nullable', // Satuan lama
            'siswa_ids' => 'nullable|array' // Massal baru
        ]);

        $bulanArray = $request->months;
        $tahunSekarang = \App\AcademicYear::current(); // Tahun ajaran (Jul-Jun)
        
        // Handle array of siswa_ids or single siswa_id
        $siswaIds = [];
        if ($request->has('siswa_ids') && is_array($request->siswa_ids)) {
            $siswaIds = $request->siswa_ids;
        } elseif ($request->has('siswa_id') && $request->siswa_id) {
            $siswaIds = [$request->siswa_id];
        }

        $kelasName = 'Kelas Tidak Diketahui';
        $kelas = \App\Models\Kelas::find($request->kelas_id);
        if ($kelas) $kelasName = $kelas->nama_kelas;

        // Batch: ambil semua siswa terpilih dalam 1 query (bukan find per siswa)
        $siswaMap = Siswa::whereIn('id', $siswaIds)->get()->keyBy('id');

        $studentNames = [];
        $totalAmount = 0;
        $rows = [];

        \Illuminate\Support\Facades\DB::transaction(function () use (&$studentNames, &$totalAmount, $siswaIds, $siswaMap, $bulanArray, $request, $tahunSekarang, $kelasName) {
            foreach ($siswaIds as $sId) {
                $siswaName = 'Siswa Tidak Diketahui';
                $waWali = null;
                if ($sId) {
                    $siswa = $siswaMap->get($sId);
                    if ($siswa) {
                        $siswaName = $siswa->nama_lengkap;
                        $waWali = $siswa->wa_wali_1;
                    }
                }
                if (!in_array($siswaName, $studentNames)) {
                    $studentNames[] = $siswaName;
                }

                foreach ($bulanArray as $bulan) {
                    $rows[] = [
                        'siswa_id' => $sId,
                        'kelas_id' => $request->kelas_id,
                        'jumlah' => $request->nominal,
                        'bulan' => $this->mapBulanToEnglish($bulan),
                        'tahun' => $tahunSekarang,
                        'tanggal_bayar' => $request->tanggal_bayar,
                        'keterangan' => 'Via API',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $totalAmount += $request->nominal;
                }

                // Antrekan Notifikasi WA (dikirim bertahap dengan delay agar tidak keblokir)
                if ($waWali) {
                    $bulanStr = implode(', ', $bulanArray);
                    $pesan = "Assalamu'alaikum Wr. Wb.\n\n"
                        . "Kami dari Madrasah As-Sajjaad memberitahukan bahwa infak ananda *{$siswaName}* untuk bulan *{$bulanStr}* sudah kami terima.\n\n"
                        . "Terima kasih atas kepercayaan dan kebaikan Bapak/Ibu. Semoga Allah melipatgandakan pahalanya dan memberkahi rezeki keluarga.\n\n"
                        . "Wassalamu'alaikum Wr. Wb.\n"
                        . "Tata Usaha Madrasah As-Sajjaad";
                    \App\Services\WhatsAppService::queue($waWali, $pesan, 'terima-kasih');
                }
            }

            // Bulk insert: 1 query untuk semua baris (bukan 1 query per baris)
            if ($rows) {
                Infak::insert($rows);
            }

            $namesStr = implode(', ', $studentNames);
            $monthsStr = implode(', ', $bulanArray);

            LogAktivitas::create([
                'type' => 'income',
                'title' => 'Pemasukan Baru (Massal/Satuan)',
                'description' => "Penerimaan infak dari $namesStr untuk bulan $monthsStr di $kelasName",
                'amount' => $totalAmount
            ]);
        });

        return response()->json(['message' => 'Infak recorded successfully']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah' => 'required|numeric',
            'bulan' => 'required|string',
            'tahun' => 'required|numeric',
        ]);

        $infak = Infak::with('siswa')->findOrFail($id);

        $oldTanggal = substr((string) $infak->tanggal_bayar, 0, 10);
        $oldJumlah = (float) $infak->jumlah;

        $infak->update([
            'tanggal_bayar' => $request->tanggal_bayar,
            'jumlah' => $request->jumlah,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
        ]);

        $siswaName = $infak->siswa ? $infak->siswa->nama_lengkap : 'Siswa Tidak Diketahui';
        $newTanggal = substr((string) $request->tanggal_bayar, 0, 10);
        $newJumlah = (float) $request->jumlah;

        $changes = [];
        if ($oldTanggal !== $newTanggal) {
            $changes[] = "tanggal $oldTanggal -> $newTanggal";
        }
        if ($oldJumlah !== $newJumlah) {
            $changes[] = 'nominal Rp' . number_format($oldJumlah, 0, ',', '.')
                . ' -> Rp' . number_format($newJumlah, 0, ',', '.');
        }

        LogAktivitas::create([
            'type' => 'system',
            'title' => 'Edit Data Infak',
            'description' => 'Edit infak ' . $siswaName . ' bulan ' . $infak->bulan . ' ' . $infak->tahun
                . ($changes ? ': ' . implode(', ', $changes) : ': tidak ada perubahan nilai'),
            'amount' => null,
        ]);

        return response()->json(['message' => 'Infak updated successfully']);
    }

    public function getBySiswa(Request $request, $id)
    {
        $tahun = \App\AcademicYear::current();
        $infak = Infak::where('siswa_id', $id)
                      ->where('tahun', $tahun)
                      ->orderBy('created_at', 'desc')
                      ->get();
                      
        return response()->json($infak);
    }

    public function syncBySiswa(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'kelas_id' => 'required|exists:kelas,id',
            'tahun' => 'nullable|numeric',
            'months' => 'nullable|array', // boleh kosong = semua bulan dibatalkan
            'nominal' => 'nullable|numeric|min:0',
            'tanggal_bayar' => 'required|date',
            'update_existing' => 'nullable|boolean',
        ]);

        $siswaId = $request->siswa_id;
        $tahun = \App\AcademicYear::current();
        $requestedMonths = array_map([$this, 'mapBulanToEnglish'], $request->input('months', []));

        // Get existing records for the student and year
        $existingInfak = Infak::where('siswa_id', $siswaId)
                              ->where('tahun', $tahun)
                              ->get();

        $existingMonths = $existingInfak->pluck('bulan')->toArray();

        // Find which months to delete and which to insert
        $toDelete = array_diff($existingMonths, $requestedMonths);
        $toInsert = array_diff($requestedMonths, $existingMonths);
        $toKeep = array_intersect($requestedMonths, $existingMonths);

        $nominal = $request->filled('nominal') ? (float) $request->nominal : null;

        if (!empty($toInsert) && ($nominal === null || $nominal <= 0)) {
            return response()->json([
                'message' => 'Nominal wajib diisi karena ada bulan baru yang belum tercatat.',
            ], 422);
        }

        $siswa = Siswa::find($siswaId);
        $siswaName = $siswa ? $siswa->nama_lengkap : 'Siswa';
        $waWali = $siswa ? $siswa->wa_wali_1 : null;

        // Semua perubahan (hapus/tambah/update + log) dibungkus transaksi
        // supaya tidak pernah menyisakan data setengah jadi bila gagal di tengah jalan.
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $siswaId, $siswaName, $waWali, $tahun, $toDelete, $toInsert, $toKeep, $nominal) {
            // Delete un-checked months
            if (!empty($toDelete)) {
                foreach ($toDelete as $bulanDel) {
                    LogAktivitas::create([
                        'type' => 'system',
                        'title' => 'Pembatalan Infak (Sync)',
                        'description' => "Penghapusan infak dari $siswaName bulan $bulanDel $tahun",
                        'amount' => null
                    ]);
                }

                Infak::where('siswa_id', $siswaId)
                     ->where('tahun', $tahun)
                     ->whereIn('bulan', $toDelete)
                     ->delete();
            }

            // Insert newly checked months
            foreach ($toInsert as $bulan) {
                Infak::create([
                    'siswa_id' => $siswaId,
                    'kelas_id' => $request->kelas_id,
                    'jumlah' => $nominal ?? 0,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'tanggal_bayar' => $request->tanggal_bayar,
                    'keterangan' => 'Disinkronisasi via Edit Siswa'
                ]);

                LogAktivitas::create([
                    'type' => 'income',
                    'title' => 'Pemasukan Baru (Sync)',
                    'description' => "Penerimaan infak dari $siswaName untuk bulan $bulan $tahun",
                    'amount' => $nominal ?? 0
                ]);
            }

            // Perbarui tanggal bayar (dan nominal) bulan yang sudah ada jika diminta.
            // Untuk kasus telat input: pindahkan tanggal supaya masuk laporan bulan yang benar.
            if ($request->boolean('update_existing') && !empty($toKeep)) {
                $updateData = ['tanggal_bayar' => $request->tanggal_bayar];
                if ($nominal !== null && $nominal > 0) {
                    $updateData['jumlah'] = $nominal;
                }

                Infak::where('siswa_id', $siswaId)
                     ->where('tahun', $tahun)
                     ->whereIn('bulan', $toKeep)
                     ->update($updateData);

                LogAktivitas::create([
                    'type' => 'system',
                    'title' => 'Perubahan Tanggal Bayar Infak',
                    'description' => "Tanggal bayar infak $siswaName bulan " . implode(', ', $toKeep) . " $tahun diubah ke " . $request->tanggal_bayar,
                    'amount' => null
                ]);
            }

            // Antrekan Notifikasi WA (dikirim bertahap dengan delay agar tidak keblokir)
            if (!empty($toInsert) && $waWali) {
                $bulanStr = implode(', ', $toInsert);
                $pesan = "Assalamu'alaikum Wr. Wb.\n\n"
                    . "Kami dari Madrasah As-Sajjaad memberitahukan bahwa infak ananda *{$siswaName}* untuk bulan *{$bulanStr}* sudah kami terima.\n\n"
                    . "Terima kasih atas kepercayaan dan kebaikan Bapak/Ibu. Semoga Allah melipatgandakan pahalanya dan memberkahi rezeki keluarga.\n\n"
                    . "Wassalamu'alaikum Wr. Wb.\n"
                    . "Tata Usaha Madrasah As-Sajjaad";
                \App\Services\WhatsAppService::queue($waWali, $pesan, 'terima-kasih');
            }
        });

        return response()->json(['message' => 'Infak synchronized successfully']);
    }

    public function destroy($id)
    {
        $infak = Infak::findOrFail($id);
        
        $siswaName = 'Siswa Tidak Diketahui';
        if ($infak->siswa_id) {
            $siswa = Siswa::find($infak->siswa_id);
            if ($siswa) {
                $siswaName = $siswa->nama_lengkap;
            }
        }

        LogAktivitas::create([
            'type' => 'system',
            'title' => 'Pemasukan Dibatalkan',
            'description' => "Penghapusan infak dari $siswaName bulan {$infak->bulan} {$infak->tahun}",
            'amount' => null
        ]);

        $infak->delete();
        return response()->json(['message' => 'Infak deleted successfully']);
    }

    public function blastReminder(Request $request)
    {
        $months = ['July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May', 'June'];
        $currentMonthName = \Carbon\Carbon::now()->locale('en')->monthName;
        $currentIndex = array_search($currentMonthName, $months);

        if ($currentIndex === false || $currentIndex < 2) {
            return response()->json(['message' => 'Belum cukup bulan untuk menunggak 2 bulan (tahun ajaran baru dimulai).'], 400);
        }

        $previousMonths = array_slice($months, 0, $currentIndex);
        $tahun = date('Y');

        // Batasi query jika rolenya guru (hanya untuk kelasnya sendiri)
        $user = $request->user();
        if ($user && $user->role === 'guru') {
            $kelasIds = \App\Models\Kelas::where('guru_id', $user->id)->pluck('id');
            $siswas = Siswa::whereIn('kelas_id', $kelasIds)->whereNotNull('wa_wali_1')->where('wa_wali_1', '!=', '')->get();
        } else {
            $siswas = Siswa::whereNotNull('wa_wali_1')->where('wa_wali_1', '!=', '')->get();
        }

        $queuedCount = 0;

        // Antrean unik per bulan: agar klik Blast kedua kali tidak mengulang
        // pesan yang sudah dikirim/diantrekan di bulan yang sama.
        $groupKey = 'tunggakan-' . date('Y-m');
        $alreadyQueued = \App\Models\WaOutbox::where('group_key', $groupKey)->pluck('phone')->all();

        // Map bulan ke angka untuk perbandingan dengan created_at
        $monthToNumber = [
            'July' => 7, 'August' => 8, 'September' => 9, 'October' => 10,
            'November' => 11, 'December' => 12, 'January' => 1, 'February' => 2,
            'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6
        ];

        // 1 query: bulan yang SUDAH dibayar untuk semua siswa (pengganti query per siswa)
        $paidMonthsBySiswa = Infak::where('tahun', \App\AcademicYear::current())
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get(['siswa_id', 'bulan'])
            ->groupBy('siswa_id');

        foreach ($siswas as $siswa) {
            // Filter bulan: hanya hitung bulan SETELAH siswa terdaftar di sistem
            $siswaCreatedAt = \Carbon\Carbon::parse($siswa->created_at);
            $siswaCreatedMonth = (int) $siswaCreatedAt->month;
            $siswaCreatedYear = (int) $siswaCreatedAt->year;

            $applicableMonths = array_filter($previousMonths, function($month) use ($monthToNumber, $siswaCreatedMonth, $siswaCreatedYear, $tahun) {
                $monthNum = $monthToNumber[$month] ?? 0;
                $monthYear = ($monthNum >= 7) ? (int)$tahun : (int)$tahun + 1; // Tahun ajaran: Jul-Des = tahun ini, Jan-Jun = tahun depan
                
                // Jika siswa terdaftar di tahun yang sama
                if ($siswaCreatedYear == $monthYear) {
                    return $monthNum >= $siswaCreatedMonth;
                }
                // Jika bulan ini di tahun setelah siswa terdaftar, pasti berlaku
                return $monthYear > $siswaCreatedYear;
            });

            if (empty($applicableMonths)) continue;

            $paidMonths = $paidMonthsBySiswa->get($siswa->id, collect())
                ->pluck('bulan')
                ->all();
            
            $missedMonths = array_diff($applicableMonths, $paidMonths);
            
            if (count($missedMonths) >= 2) {
                // Translate month names for message
                $indoMonths = array_map(function($m) {
                    $map = ['July'=>'Juli', 'August'=>'Agustus', 'September'=>'September', 'October'=>'Oktober', 'November'=>'November', 'December'=>'Desember', 'January'=>'Januari', 'February'=>'Februari', 'March'=>'Maret', 'April'=>'April', 'May'=>'Mei', 'June'=>'Juni'];
                    return $map[$m] ?? $m;
                }, $missedMonths);

                $bulanStr = implode(', ', $indoMonths);
                $pesan = "Assalamu'alaikum Wr. Wb.\n\n"
                    . "Bapak/Ibu Wali yang kami hormati, perkenalkan kami dari Tata Usaha Madrasah As-Sajjaad. Mohon maaf mengganggu waktunya.\n\n"
                    . "Kami ingin menyampaikan informasi dengan hormat bahwa infak ananda *{$siswa->nama_lengkap}* untuk bulan *{$bulanStr}* belum tercatat di sistem kami.\n\n"
                    . "Apabila berkenan, kami mohon bantuan Bapak/Ibu untuk dapat menyelesaikannya. Namun jika sudah melakukan pembayaran, mohon abaikan pesan ini dan mohon maaf atas ketidaknyamanannya.\n\n"
                    . "Terima kasih banyak atas perhatian dan kerja samanya. Semoga Allah senantiasa memberi kelancaran dan keberkahan rezeki untuk keluarga Bapak/Ibu. 🙏\n\n"
                    . "Wassalamu'alaikum Wr. Wb.\n"
                    . "Tata Usaha Madrasah As-Sajjaad";

                $phone = preg_replace('/[^0-9]/', '', $siswa->wa_wali_1);
                if ($phone && !in_array($phone, $alreadyQueued)) {
                    \App\Services\WhatsAppService::queue($phone, $pesan, $groupKey);
                    $alreadyQueued[] = $phone;
                    $queuedCount++;
                }
            }
        }

        $pending = \App\Services\WhatsAppService::pendingCount();

        return response()->json([
            'message' => "Blast WA diantrekan ke {$queuedCount} wali santri yang menunggak 2 bulan atau lebih. Pesan dikirim bertahap (antrean: {$pending}).",
            'queued' => $queuedCount,
            'remaining' => $pending,
        ]);
    }

    private function mapBulanToEnglish($shortMonth)
    {
        $map = [
            'Jan' => 'January',
            'Feb' => 'February',
            'Mar' => 'March',
            'Apr' => 'April',
            'Mei' => 'May',
            'Jun' => 'June',
            'Jul' => 'July',
            'Agu' => 'August',
            'Sep' => 'September',
            'Okt' => 'October',
            'Nov' => 'November',
            'Des' => 'December'
        ];

        return $map[$shortMonth] ?? $shortMonth;
    }
}
