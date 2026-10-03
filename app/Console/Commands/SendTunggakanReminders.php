<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Infak;
use Carbon\Carbon;
use App\Notifications\TunggakanNotification;

class SendTunggakanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'infak:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send web push notifications to users for arrears in the previous months.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all classes beserta wali kelas (guru_id)
        $kelasList = Kelas::with('wali_kelas')->get();

        $months = ['July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May', 'June'];

        // Find current month index
        $currentMonthName = Carbon::now()->locale('en')->monthName; // e.g. August
        $currentIndex = array_search($currentMonthName, $months);

        // If it's July (index 0), there are no previous months to check
        if ($currentIndex === false || $currentIndex === 0) {
            $this->info("No previous months to check.");
            return;
        }

        $previousMonths = array_slice($months, 0, $currentIndex);

        // Batch: 1 query siswa + 1 query bulan terbayar (pengganti N+1 per siswa)
        $allSiswas = Siswa::select('id', 'kelas_id')->get();
        $paidMonthsBySiswa = Infak::whereIn('siswa_id', $allSiswas->pluck('id'))
            ->whereIn('bulan', $previousMonths)
            ->get(['siswa_id', 'bulan'])
            ->groupBy('siswa_id');

        $tunggakanPerKelas = [];
        foreach ($allSiswas as $siswa) {
            $paidMonths = $paidMonthsBySiswa->get($siswa->id, collect())->pluck('bulan')->all();
            $missed = count(array_diff($previousMonths, $paidMonths));
            if ($missed > 0) {
                $tunggakanPerKelas[$siswa->kelas_id] = ($tunggakanPerKelas[$siswa->kelas_id] ?? 0) + 1;
            }
        }

        foreach ($kelasList as $kelas) {
            if (!$kelas->wali_kelas) continue;

            $tunggakanCount = $tunggakanPerKelas[$kelas->id] ?? 0;

            if ($tunggakanCount > 0) {
                $msg = "Assalamu'alaikum, di " . $kelas->nama_kelas . " masih ada " . $tunggakanCount . " siswa yang memiliki tunggakan infak dari bulan-bulan sebelumnya. Yuk cek detailnya!";
                // Send notification
                try {
                    $kelas->wali_kelas->notify(new TunggakanNotification("Info Tunggakan Infak", $msg));
                    $this->info("Sent notification to " . $kelas->wali_kelas->name . " for " . $kelas->nama_kelas);
                } catch (\Throwable $e) {
                    \Log::error("Push Notification Error for " . $kelas->wali_kelas->name . ": " . $e->getMessage());
                    $this->error("Failed to send to " . $kelas->wali_kelas->name . ": " . $e->getMessage());
                }
            }
        }
    }
}
