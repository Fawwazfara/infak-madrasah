<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Infak;
use App\Models\Pengeluaran;
use App\Models\Kelas;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function cetakFormSetoran($kelas_id)
    {
        $kelas = Kelas::findOrFail($kelas_id);
        $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap', 'asc')->get();

        $data = [
            'kelas' => $kelas,
            'siswas' => $siswas
        ];

        $pdf = Pdf::loadView('form_setoran_pdf', $data);
        return $pdf->download('form_setoran_' . strtolower(str_replace(' ', '_', $kelas->nama_kelas)) . '.pdf');
    }

    public function index(Request $request)
    {
        // Parameter bulan (misal: "Agustus")
        $bulan = $request->query('bulan', date('F'));
        
        // Pemasukan per Kelas (dari Infak bulan tersebut)
        $kelasList = Kelas::all();
        $kelasOrder = [
            'TK' => 1, 'Kelas 1' => 2, 'Kelas 2' => 3, 'Kelas 3' => 4, 'Kelas 4A' => 5, 'Kelas 4B' => 6,
            'Kelas 5' => 7, 'Kelas 6' => 8, 'Kelas 7' => 9, 'Kelas 8' => 10, 'Kelas 9' => 11, 'Kelas Ulya' => 12, 'Bambim' => 13
        ];
        $kelasList = $kelasList->sortBy(function($k) use ($kelasOrder) {
            return $kelasOrder[$k->nama_kelas] ?? 99;
        })->values();
        
        $pemasukanPerKelas = [];
        $totalPemasukan = 0;

        $bulanIndoKeBulanAngka = [
            'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
            'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
            'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12',
            'January' => '01', 'February' => '02', 'March' => '03', 'May' => '05',
            'June' => '06', 'July' => '07', 'August' => '08', 'October' => '10', 'December' => '12'
        ];
        
        $bulanAngka = $bulanIndoKeBulanAngka[$bulan] ?? date('m');
        $tahun = date('Y'); // Asumsi tahun ini

        foreach ($kelasList as $kelas) {
            $nominal = Infak::where('kelas_id', $kelas->id)
                ->whereMonth('tanggal_bayar', $bulanAngka)
                ->whereYear('tanggal_bayar', $tahun)
                ->sum('jumlah');
                
            $pemasukanPerKelas[] = [
                'kelas' => $kelas->nama_kelas,
                'nominal' => $nominal
            ];
            $totalPemasukan += $nominal;
        }

        // Pengeluaran List (berdasarkan bulan tanggal)
        // Kita perlu mencari pengeluaran di bulan tersebut (menggunakan MonthName di database kita tidak punya, kita pakai tanggal)
        // Tapi "Agustus" perlu diubah ke "08" untuk memfilter tanggal 'Y-m-d'
        
        $bulanAngka = $bulanIndoKeBulanAngka[$bulan] ?? date('m');
        $tahun = date('Y'); // Asumsi tahun ini

        $pengeluaranList = Pengeluaran::whereMonth('tanggal', $bulanAngka)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();
            
        $totalPengeluaran = $pengeluaranList->sum('jumlah');

        return response()->json([
            'pemasukan_per_kelas' => $pemasukanPerKelas,
            'pengeluaran_list' => $pengeluaranList,
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'bulan' => $bulan
        ]);
    }

    public function cetakPdf(Request $request)
    {
        $bulan = $request->query('bulan', date('F'));
        $kelasList = Kelas::all();
        $kelasOrder = [
            'TK' => 1, 'Kelas 1' => 2, 'Kelas 2' => 3, 'Kelas 3' => 4, 'Kelas 4A' => 5, 'Kelas 4B' => 6,
            'Kelas 5' => 7, 'Kelas 6' => 8, 'Kelas 7' => 9, 'Kelas 8' => 10, 'Kelas 9' => 11, 'Kelas Ulya' => 12, 'Bambim' => 13
        ];
        $kelasList = $kelasList->sortBy(function($k) use ($kelasOrder) {
            return $kelasOrder[$k->nama_kelas] ?? 99;
        })->values();
        
        $pemasukanPerKelas = [];
        $totalPemasukan = 0;

        $bulanIndoKeBulanAngka = [
            'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
            'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
            'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12',
            'January' => '01', 'February' => '02', 'March' => '03', 'May' => '05',
            'June' => '06', 'July' => '07', 'August' => '08', 'October' => '10', 'December' => '12'
        ];
        
        $bulanAngka = $bulanIndoKeBulanAngka[$bulan] ?? date('m');
        $tahun = date('Y');

        foreach ($kelasList as $kelas) {
            $nominal = Infak::where('kelas_id', $kelas->id)
                ->whereMonth('tanggal_bayar', $bulanAngka)
                ->whereYear('tanggal_bayar', $tahun)
                ->sum('jumlah');
                
            $pemasukanPerKelas[] = [
                'kelas' => $kelas->nama_kelas,
                'nominal' => $nominal
            ];
            $totalPemasukan += $nominal;
        }

        $pengeluaranList = Pengeluaran::whereMonth('tanggal', $bulanAngka)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();
            
        $totalPengeluaran = $pengeluaranList->sum('jumlah');

        $data = [
            'pemasukan_per_kelas' => $pemasukanPerKelas,
            'pengeluaran_list' => $pengeluaranList,
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'bulan' => $bulan,
            'tahun' => $tahun
        ];

        $pdf = Pdf::loadView('laporan_pdf', $data);
        return $pdf->download('laporan_keuangan_' . strtolower($bulan) . '_' . $tahun . '.pdf');
    }
}
