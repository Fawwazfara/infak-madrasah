<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
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
        // Get all classes
        $kelasList = Kelas::with('guru')->get();
        
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

        foreach ($kelasList as $kelas) {
            if (!$kelas->guru) continue;

            $siswas = Siswa::where('kelas_id', $kelas->id)->get();
            $tunggakanCount = 0;

            foreach ($siswas as $siswa) {
                // Check if they missed any previous month
                $paidMonths = Infak::where('siswa_id', $siswa->id)
                    ->whereIn('bulan', $previousMonths)
                    ->pluck('bulan')
                    ->toArray();
                
                $missed = count(array_diff($previousMonths, $paidMonths));
                if ($missed > 0) {
                    $tunggakanCount++;
                }
            }

            if ($tunggakanCount > 0) {
                // Send notification
                try {
                    $kelas->guru->notify(new TunggakanNotification("Info Tunggakan Infak", $msg));
                    $this->info("Sent notification to " . $kelas->guru->name . " for " . $kelas->nama_kelas);
                } catch (\Throwable $e) {
                    \Log::error("Push Notification Error for " . $kelas->guru->name . ": " . $e->getMessage());
                    $this->error("Failed to send to " . $kelas->guru->name . ": " . $e->getMessage());
                }
            }
        }
    }
}
