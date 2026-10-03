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

        // Satu query grup per bulan untuk window 6 bulan (bulan ini + 5 sebelumnya),
        // mencakup metrik "bulan ini", "bulan lalu", dan data chart sekaligus.
        $windowStart = Carbon::now()->subMonths(5)->startOfMonth();
        $windowEnd = Carbon::now()->endOfMonth();

        $infakByMonth = Infak::query()
            ->when($isGuru, fn ($q) => $q->whereIn('kelas_id', $kelasIds))
            ->whereBetween('tanggal_bayar', [$windowStart, $windowEnd])
            ->groupByRaw('YEAR(tanggal_bayar), MONTH(tanggal_bayar)')
            ->selectRaw('YEAR(tanggal_bayar) as y, MONTH(tanggal_bayar) as m, SUM(jumlah) as total')
            ->get()
            ->keyBy(fn ($row) => $row->y . '-' . str_pad((string) $row->m, 2, '0', STR_PAD_LEFT));

        $pengeluaranByMonth = collect();
        if (!$isGuru) {
            $pengeluaranByMonth = \App\Models\Pengeluaran::query()
                ->whereBetween('tanggal', [$windowStart, $windowEnd])
                ->groupByRaw('YEAR(tanggal), MONTH(tanggal)')
                ->selectRaw('YEAR(tanggal) as y, MONTH(tanggal) as m, SUM(jumlah) as total')
                ->get()
                ->keyBy(fn ($row) => $row->y . '-' . str_pad((string) $row->m, 2, '0', STR_PAD_LEFT));
        }

        $bulanIniKey = Carbon::now()->format('Y-m');
        $bulanLaluKey = Carbon::now()->subMonth()->format('Y-m');

        $totalPemasukanBulanIni = $infakByMonth[$bulanIniKey]->total ?? 0;
        $totalPemasukanBulanLalu = $infakByMonth[$bulanLaluKey]->total ?? 0;
        $totalPengeluaranBulanIni = $isGuru ? 0 : ($pengeluaranByMonth[$bulanIniKey]->total ?? 0);

        // Total all-time (2 query agregat)
        $totalPemasukanAllTime = Infak::query()
            ->when($isGuru, fn ($q) => $q->whereIn('kelas_id', $kelasIds))
            ->sum('jumlah');
        $totalPengeluaranAllTime = $isGuru ? 0 : \App\Models\Pengeluaran::sum('jumlah');
        $saldoAllTime = $totalPemasukanAllTime - $totalPengeluaranAllTime;

        // Data chart (label dihitung, nilai diambil dari map grup di atas)
        $chartLabels = [];
        $chartPemasukan = [];
        $chartPengeluaran = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);

            $engShort = $date->format('M');
            $indoShort = [
                'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr', 'May' => 'Mei', 'Jun' => 'Jun',
                'Jul' => 'Jul', 'Aug' => 'Agu', 'Sep' => 'Sep', 'Oct' => 'Okt', 'Nov' => 'Nov', 'Dec' => 'Des'
            ];
            $chartLabels[] = $indoShort[$engShort] ?? $engShort;

            $key = $date->format('Y-m');
            $chartPemasukan[] = $infakByMonth[$key]->total ?? 0;

            if (!$isGuru) {
                $chartPengeluaran[] = $pengeluaranByMonth[$key]->total ?? 0;
            }
        }

        $persenPemasukan = 0;
        if ($totalPemasukanBulanLalu > 0) {
            $persenPemasukan = round((($totalPemasukanBulanIni - $totalPemasukanBulanLalu) / $totalPemasukanBulanLalu) * 100);
        } else if ($totalPemasukanBulanIni > 0) {
            $persenPemasukan = 100;
        }

        $persenPengeluaran = 0;
        if (!$isGuru) {
            $totalPengeluaranBulanLalu = $pengeluaranByMonth[$bulanLaluKey]->total ?? 0;
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
