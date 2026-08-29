<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Infak;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getKelas(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'guru') {
            $kelas = Kelas::where('guru_id', $user->id)->get();
        } else {
            $kelas = Kelas::all();
        }

        $kelasOrder = [
            'TK' => 1, 'Kelas 1' => 2, 'Kelas 2' => 3, 'Kelas 3' => 4, 'Kelas 4A' => 5, 'Kelas 4B' => 6,
            'Kelas 5' => 7, 'Kelas 6' => 8, 'Kelas 7' => 9, 'Kelas 8' => 10, 'Kelas 9' => 11, 'Kelas Ulya' => 12, 'Bambim' => 13
        ];
        
        $kelas = $kelas->sortBy(function($k) use ($kelasOrder) {
            return $kelasOrder[$k->nama_kelas] ?? 99;
        })->values();

        return response()->json($kelas);
    }

    public function statistik(Request $request)
    {
        $user = $request->user();
        $isGuru = $user->role === 'guru';

        $kelasIds = [];
        if ($isGuru) {
            $kelasIds = Kelas::where('guru_id', $user->id)->pluck('id')->toArray();
        }

        // Metrics for current month
        $currentMonth = date('m');
        $currentYear = date('Y');

        $queryInfakBulanIni = Infak::whereMonth('tanggal_bayar', $currentMonth)
                                   ->whereYear('tanggal_bayar', $currentYear);
        if ($isGuru) {
            $queryInfakBulanIni->whereIn('kelas_id', $kelasIds);
        }
        $totalPemasukanBulanIni = $queryInfakBulanIni->sum('jumlah');

        $totalPengeluaranBulanIni = 0;
        if (!$isGuru) {
            $totalPengeluaranBulanIni = \App\Models\Pengeluaran::whereMonth('tanggal', $currentMonth)
                                            ->whereYear('tanggal', $currentYear)
                                            ->sum('jumlah');
        }
        
        // We will define Kas / Saldo as total all time pemasukan - pengeluaran
        // Wait, the UI says "Total Kas" vs "Pengeluaran" vs "Saldo Akhir Saat Ini"
        // Let's provide both all-time and this-month metrics
        $queryAllInfak = Infak::query();
        if ($isGuru) {
            $queryAllInfak->whereIn('kelas_id', $kelasIds);
        }
        $totalPemasukanAllTime = $queryAllInfak->sum('jumlah');
        $totalPengeluaranAllTime = $isGuru ? 0 : \App\Models\Pengeluaran::sum('jumlah');
        $saldoAllTime = $totalPemasukanAllTime - $totalPengeluaranAllTime;

        // Chart Data (Last 6 Months)
        $chartLabels = [];
        $chartPemasukan = [];
        $chartPengeluaran = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $m = $date->format('m');
            $y = $date->format('Y');
            
            $engShort = $date->format('M');
            $indoShort = [
                'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr', 'May' => 'Mei', 'Jun' => 'Jun',
                'Jul' => 'Jul', 'Aug' => 'Agu', 'Sep' => 'Sep', 'Oct' => 'Okt', 'Nov' => 'Nov', 'Dec' => 'Des'
            ];
            $chartLabels[] = $indoShort[$engShort] ?? $engShort;
            
            $qInfak = Infak::whereMonth('tanggal_bayar', $m)->whereYear('tanggal_bayar', $y);
            if ($isGuru) {
                $qInfak->whereIn('kelas_id', $kelasIds);
            }
            $chartPemasukan[] = $qInfak->sum('jumlah');
            
            if (!$isGuru) {
                $chartPengeluaran[] = \App\Models\Pengeluaran::whereMonth('tanggal', $m)->whereYear('tanggal', $y)->sum('jumlah');
            }
        }
        
        // Also calculate differences from last month for percentages
        $lastMonthDate = Carbon::now()->subMonth();
        $lm_m = $lastMonthDate->format('m');
        $lm_y = $lastMonthDate->format('Y');
        
        $qInfakLastMonth = Infak::whereMonth('tanggal_bayar', $lm_m)->whereYear('tanggal_bayar', $lm_y);
        if ($isGuru) {
            $qInfakLastMonth->whereIn('kelas_id', $kelasIds);
        }
        $totalPemasukanBulanLalu = $qInfakLastMonth->sum('jumlah');
        
        $persenPemasukan = 0;
        if ($totalPemasukanBulanLalu > 0) {
            $persenPemasukan = round((($totalPemasukanBulanIni - $totalPemasukanBulanLalu) / $totalPemasukanBulanLalu) * 100);
        } else if ($totalPemasukanBulanIni > 0) {
            $persenPemasukan = 100;
        }
        
        $persenPengeluaran = 0;
        if (!$isGuru) {
            $totalPengeluaranBulanLalu = \App\Models\Pengeluaran::whereMonth('tanggal', $lm_m)->whereYear('tanggal', $lm_y)->sum('jumlah');
            if ($totalPengeluaranBulanLalu > 0) {
                $persenPengeluaran = round((($totalPengeluaranBulanIni - $totalPengeluaranBulanLalu) / $totalPengeluaranBulanLalu) * 100);
            } else if ($totalPengeluaranBulanIni > 0) {
                $persenPengeluaran = 100;
            }
        }

        return response()->json([
            'pemasukan_bulan_ini' => $totalPemasukanBulanIni,
            'pengeluaran_bulan_ini' => $totalPengeluaranBulanIni,
            'pemasukan_all_time' => $totalPemasukanAllTime,
            'saldo_all_time' => $saldoAllTime,
            'persen_pemasukan' => $persenPemasukan,
            'persen_pengeluaran' => $persenPengeluaran,
            'chart' => [
                'labels' => $chartLabels,
                'pemasukan' => $chartPemasukan,
                'pengeluaran' => $chartPengeluaran
            ]
        ]);
    }

    public function kepatuhan(Request $request)
    {
        $user = $request->user();
        
        // This month (calendar month)
        $currentMonth = date('F');
        $currentYear = date('Y');

        if ($user->role === 'guru') {
            $kelasList = Kelas::where('guru_id', $user->id)->with('siswas')->get();
        } else {
            $kelasList = Kelas::with('siswas')->get();
        }

        $kelasOrder = [
            'TK' => 1, 'Kelas 1' => 2, 'Kelas 2' => 3, 'Kelas 3' => 4, 'Kelas 4A' => 5, 'Kelas 4B' => 6,
            'Kelas 5' => 7, 'Kelas 6' => 8, 'Kelas 7' => 9, 'Kelas 8' => 10, 'Kelas 9' => 11, 'Kelas Ulya' => 12, 'Bambim' => 13
        ];
        
        $kelasList = $kelasList->sortBy(function($k) use ($kelasOrder) {
            return $kelasOrder[$k->nama_kelas] ?? 99;
        })->values();

        $totalBelumBayar = 0;
        $kepatuhanList = [];

        foreach ($kelasList as $kelas) {
            $lunas = 0;
            $nunggak = 0;

            foreach ($kelas->siswas as $siswa) {
                // Check if this student has an infak for the current month
                $hasPaidThisMonth = Infak::where('siswa_id', $siswa->id)
                    ->where('bulan', $currentMonth)
                    ->where('tahun', $currentYear)
                    ->exists();

                if ($hasPaidThisMonth) {
                    $lunas++;
                } else {
                    $nunggak++;
                    $totalBelumBayar++;
                }
            }

            $totalSiswa = $lunas + $nunggak;
            $persentase = $totalSiswa > 0 ? round(($lunas / $totalSiswa) * 100) : 0;

            $kepatuhanList[] = [
                'nama_kelas' => $kelas->nama_kelas,
                'persentase' => $persentase,
                'lunas' => $lunas,
                'nunggak' => $nunggak,
                'pieData' => [
                    'labels' => ['Lunas', 'Menunggak'],
                    'datasets' => [[
                        'backgroundColor' => ['#1B5E20', '#E65100'],
                        'borderWidth' => 0,
                        'data' => [$lunas, $nunggak]
                    ]]
                ]
            ];
        }

        return response()->json([
            'total_belum_bayar_bulan_ini' => $totalBelumBayar,
            'kepatuhan_per_kelas' => $kepatuhanList,
            'bulan' => $currentMonth
        ]);
    }
}
