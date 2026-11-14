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

        // Window 6 bulan (bulan ini + 5 sebelumnya) untuk ringkasan pendapatan & pengeluaran madrasah
        $windowStart = Carbon::now()->subMonths(5)->startOfMonth();
        $windowEnd = Carbon::now()->endOfMonth();

        $infakByMonth = Infak::query()
            ->whereBetween('tanggal_bayar', [$windowStart, $windowEnd])
            ->groupByRaw('YEAR(tanggal_bayar), MONTH(tanggal_bayar)')
            ->selectRaw('YEAR(tanggal_bayar) as y, MONTH(tanggal_bayar) as m, SUM(jumlah) as total')
            ->get()
            ->keyBy(fn ($row) => $row->y . '-' . str_pad((string) $row->m, 2, '0', STR_PAD_LEFT));

        $pengeluaranByMonth = \App\Models\Pengeluaran::query()
            ->whereBetween('tanggal', [$windowStart, $windowEnd])
            ->groupByRaw('YEAR(tanggal), MONTH(tanggal)')
            ->selectRaw('YEAR(tanggal) as y, MONTH(tanggal) as m, SUM(jumlah) as total')
            ->get()
            ->keyBy(fn ($row) => $row->y . '-' . str_pad((string) $row->m, 2, '0', STR_PAD_LEFT));

        $bulanIniKey = Carbon::now()->format('Y-m');
        $bulanLaluKey = Carbon::now()->subMonth()->format('Y-m');

        $totalPemasukanBulanIni = $infakByMonth[$bulanIniKey]->total ?? 0;
        $totalPemasukanBulanLalu = $infakByMonth[$bulanLaluKey]->total ?? 0;
        $totalPengeluaranBulanIni = $pengeluaranByMonth[$bulanIniKey]->total ?? 0;

        // Total all-time madrasah
        $totalPemasukanAllTime = Infak::sum('jumlah');
        $totalPengeluaranAllTime = \App\Models\Pengeluaran::sum('jumlah');
        $saldoAllTime = $totalPemasukanAllTime - $totalPengeluaranAllTime;

        $chartLabels = [];
        $chartPemasukan = [];
        $chartPengeluaran = [];

        $indoShort = [
            'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr', 'May' => 'Mei', 'Jun' => 'Jun',
            'Jul' => 'Jul', 'Aug' => 'Agu', 'Sep' => 'Sep', 'Oct' => 'Okt', 'Nov' => 'Nov', 'Dec' => 'Des'
        ];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $chartLabels[] = $indoShort[$date->format('M')] ?? $date->format('M');

            $key = $date->format('Y-m');
            $chartPemasukan[] = $infakByMonth[$key]->total ?? 0;
            $chartPengeluaran[] = $pengeluaranByMonth[$key]->total ?? 0;
        }

        $persenPemasukan = 0;
        if ($totalPemasukanBulanLalu > 0) {
            $persenPemasukan = round((($totalPemasukanBulanIni - $totalPemasukanBulanLalu) / $totalPemasukanBulanLalu) * 100);
        } elseif ($totalPemasukanBulanIni > 0) {
            $persenPemasukan = 100;
        }

        $totalPengeluaranBulanLalu = $pengeluaranByMonth[$bulanLaluKey]->total ?? 0;
        $persenPengeluaran = 0;
        if ($totalPengeluaranBulanLalu > 0) {
            $persenPengeluaran = round((($totalPengeluaranBulanIni - $totalPengeluaranBulanLalu) / $totalPengeluaranBulanLalu) * 100);
        } elseif ($totalPengeluaranBulanIni > 0) {
            $persenPengeluaran = 100;
        }

        // Tambahan ringkasan khusus kelas yang diampu guru (jika login sebagai guru)
        $kelasSummary = null;
        if ($isGuru) {
            $kelasIds = Kelas::where('guru_id', $user->id)->pluck('id')->toArray();
            $kelasByMonth = collect();
            $kelasAllTime = 0;

            if (!empty($kelasIds)) {
                $kelasByMonth = Infak::query()
                    ->whereIn('kelas_id', $kelasIds)
                    ->whereBetween('tanggal_bayar', [$windowStart, $windowEnd])
                    ->groupByRaw('YEAR(tanggal_bayar), MONTH(tanggal_bayar)')
                    ->selectRaw('YEAR(tanggal_bayar) as y, MONTH(tanggal_bayar) as m, SUM(jumlah) as total')
                    ->get()
                    ->keyBy(fn ($row) => $row->y . '-' . str_pad((string) $row->m, 2, '0', STR_PAD_LEFT));
                $kelasAllTime = Infak::whereIn('kelas_id', $kelasIds)->sum('jumlah');
            }

            $kelasIni = $kelasByMonth[$bulanIniKey]->total ?? 0;
            $kelasLalu = $kelasByMonth[$bulanLaluKey]->total ?? 0;
            $kelasPersen = 0;
            if ($kelasLalu > 0) {
                $kelasPersen = round((($kelasIni - $kelasLalu) / $kelasLalu) * 100);
            } elseif ($kelasIni > 0) {
                $kelasPersen = 100;
            }

            $chartKelas = [];
            for ($i = 5; $i >= 0; $i--) {
                $kKey = Carbon::now()->subMonths($i)->format('Y-m');
                $chartKelas[] = $kelasByMonth[$kKey]->total ?? 0;
            }

            $kelasSummary = [
                'pemasukan_bulan_ini' => $kelasIni,
                'pemasukan_all_time' => $kelasAllTime,
                'persen_pemasukan' => $kelasPersen,
                'chart_pemasukan' => $chartKelas,
            ];
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
                'pengeluaran' => $chartPengeluaran,
            ],
            'kelas_summary' => $kelasSummary,
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

        // 1 query: semua siswa yang sudah bayar bulan ini (tahun ajaran berjalan),
        // pengganti cek exists() per siswa (N+1).
        $paidSiswaSet = array_fill_keys(
            Infak::where('bulan', $currentMonth)
                ->where('tahun', \App\AcademicYear::current())
                ->whereNotNull('siswa_id')
                ->distinct()
                ->pluck('siswa_id')
                ->all(),
            true
        );

        foreach ($kelasList as $kelas) {
            $lunas = 0;
            $nunggak = 0;

            foreach ($kelas->siswas as $siswa) {
                // Check if this student has an infak for the current month
                $hasPaidThisMonth = isset($paidSiswaSet[$siswa->id]);

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
                        'backgroundColor' => ['#A8551E', '#D6CFC2'],
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
