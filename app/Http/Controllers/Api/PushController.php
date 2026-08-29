<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TunggakanNotification;
use Illuminate\Support\Facades\Artisan;

class PushController extends Controller
{
    /**
     * Store the Push Subscription.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint'    => 'required',
            'keys.auth'   => 'required',
            'keys.p256dh' => 'required'
        ]);

        $endpoint = $request->endpoint;
        $token = $request->keys['auth'];
        $key = $request->keys['p256dh'];
        $user = Auth::user();
        
        $user->updatePushSubscription($endpoint, $key, $token);
        
        return response()->json(['success' => true], 200);
    }

    public function testPush(Request $request)
    {
        $user = Auth::user();
        $user->notify(new TunggakanNotification(
            "Info Infak", 
            "Test Kirim Notifikasi Berhasil!"
        ));
        
        return response()->json(['success' => true]);
    }

    public function sendReminders(Request $request)
    {
        // Must be admin to trigger manually
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $kelasList = \App\Models\Kelas::with('wali_kelas')->get();
            $months = ['July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May', 'June'];
            
            $currentMonthName = \Carbon\Carbon::now()->locale('en')->monthName; // e.g. August
            $currentIndex = array_search($currentMonthName, $months);
            
            if ($currentIndex === false || $currentIndex === 0) {
                return response()->json(['message' => 'Notifikasi tunggakan berhasil dikirim ke guru-guru!']);
            }

            $previousMonths = array_slice($months, 0, $currentIndex);

            foreach ($kelasList as $kelas) {
                if (!$kelas->wali_kelas) continue;

                $siswas = \App\Models\Siswa::where('kelas_id', $kelas->id)->get();
                $tunggakanCount = 0;

                foreach ($siswas as $siswa) {
                    $paidMonths = \App\Models\Infak::where('siswa_id', $siswa->id)
                        ->whereIn('bulan', $previousMonths)
                        ->pluck('bulan')
                        ->toArray();
                    
                    $missed = count(array_diff($previousMonths, $paidMonths));
                    if ($missed > 0) {
                        $tunggakanCount++;
                    }
                }

                if ($tunggakanCount > 0) {
                    $msg = "Assalamu'alaikum, di " . $kelas->nama_kelas . " masih ada " . $tunggakanCount . " siswa yang memiliki tunggakan infak dari bulan-bulan sebelumnya. Yuk cek detailnya!";
                    try {
                        $kelas->wali_kelas->notify(new \App\Notifications\TunggakanNotification("Info Tunggakan Infak", $msg));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Push Error: " . $e->getMessage());
                    }
                }
            }
            return response()->json(['message' => 'Notifikasi tunggakan berhasil dikirim ke guru-guru!']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
