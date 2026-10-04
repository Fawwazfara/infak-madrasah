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

        $data = $this->hitungLaporan($bulan);
        $data['bulan'] = $bulan;

        return response()->json($data);
    }

    public function cetakPdf(Request $request)
    {
        $bulan = $request->query('bulan', date('F'));

        $data = $this->hitungLaporan($bulan);
        $data['bulan'] = $bulan;
        $data['tahun'] = date('Y');

        $pdf = Pdf::loadView('laporan_pdf', $data);
        return $pdf->download('laporan_keuangan_' . strtolower($bulan) . '_' . $data['tahun'] . '.pdf');
    }

    /**
     * Rincian pemasukan satu kelas pada bulan laporan: daftar siswa yang
     * membayar (nama, tanggal, nominal). Dipakai drill-down "Pemasukan per
     * Kelas" di frontend; rentang tanggal dihitung sama dengan hitungLaporan
     * supaya totalnya konsisten dengan angka di tabel.
     */
    public function detailKelas(Request $request)
    {
        $request->validate([
            'bulan' => 'required|string',
            'kelas_id' => 'required|integer|exists:kelas,id',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);
        [$periodeMulai, $periodeAkhir] = $this->periodeDariBulan($request->bulan);

        $rows = Infak::with('siswa')
            ->where('kelas_id', $kelas->id)
            ->whereBetween('tanggal_bayar', [$periodeMulai, $periodeAkhir])
            ->get()
            ->sortBy(fn ($i) => $i->siswa ? $i->siswa->nama_lengkap : '')
            ->values()
            ->map(fn ($i) => [
                'siswa' => $i->siswa ? $i->siswa->nama_lengkap : 'Siswa Tidak Diketahui',
                'tanggal_bayar' => substr((string) $i->tanggal_bayar, 0, 10),
                'jumlah' => (float) $i->jumlah,
                'bulan' => $i->bulan,
            ]);

        return response()->json([
            'kelas' => $kelas->nama_kelas,
            'bulan' => $request->bulan,
            'jumlah_transaksi' => $rows->count(),
            'total' => $rows->sum('jumlah'),
            'rows' => $rows,
        ]);
    }

    /**
     * Nama bulan (ID/EN) -> rentang tanggal bulan berjalan (tahun kaliner),
     * sama seperti perhitungan laporan utama.
     *
     * @return array{0: string, 1: string} [periodeMulai, periodeAkhir] (Y-m-d)
     */
    private function periodeDariBulan(string $bulan): array
    {
        $bulanIndoKeBulanAngka = [
            'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
            'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
            'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12',
            'January' => '01', 'February' => '02', 'March' => '03', 'May' => '05',
            'June' => '06', 'July' => '07', 'August' => '08', 'October' => '10', 'December' => '12'
        ];

        $bulanAngka = $bulanIndoKeBulanAngka[$bulan] ?? date('m');
        $tahun = date('Y'); // Asumsi tahun ini (sama dengan laporan lama)

        $periodeMulai = \Carbon\Carbon::createFromDate((int) $tahun, (int) $bulanAngka, 1)->startOfMonth()->toDateString();
        $periodeAkhir = \Carbon\Carbon::createFromDate((int) $tahun, (int) $bulanAngka, 1)->endOfMonth()->toDateString();

        return [$periodeMulai, $periodeAkhir];
    }

    /**
     * Hitung laporan keuangan satu bulan: pemasukan per kelas (1 query grup,
     * bukan 1 query per kelas) + daftar pengeluaran.
     */
    private function hitungLaporan(string $bulan): array
    {
        $kelasList = Kelas::all();
        $kelasOrder = [
            'TK' => 1, 'Kelas 1' => 2, 'Kelas 2' => 3, 'Kelas 3' => 4, 'Kelas 4A' => 5, 'Kelas 4B' => 6,
            'Kelas 5' => 7, 'Kelas 6' => 8, 'Kelas 7' => 9, 'Kelas 8' => 10, 'Kelas 9' => 11, 'Kelas Ulya' => 12, 'Bambim' => 13
        ];
        $kelasList = $kelasList->sortBy(function ($k) use ($kelasOrder) {
            return $kelasOrder[$k->nama_kelas] ?? 99;
        })->values();

        [$periodeMulai, $periodeAkhir] = $this->periodeDariBulan($bulan);

        // Satu query agregat GROUP BY kelas_id untuk seluruh kelas
        $sumPerKelas = Infak::whereIn('kelas_id', $kelasList->pluck('id'))
            ->whereBetween('tanggal_bayar', [$periodeMulai, $periodeAkhir])
            ->groupBy('kelas_id')
            ->selectRaw('kelas_id, SUM(jumlah) as total')
            ->pluck('total', 'kelas_id');

        $pemasukanPerKelas = [];
        $totalPemasukan = 0;

        foreach ($kelasList as $kelas) {
            $nominal = $sumPerKelas[$kelas->id] ?? 0;

            $pemasukanPerKelas[] = [
                'kelas_id' => $kelas->id,
                'kelas' => $kelas->nama_kelas,
                'nominal' => $nominal
            ];
            $totalPemasukan += $nominal;
        }

        // Pengeluaran List (berdasarkan bulan tanggal)
        $pengeluaranList = Pengeluaran::whereBetween('tanggal', [$periodeMulai, $periodeAkhir])
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalPengeluaran = $pengeluaranList->sum('jumlah');

        return [
            'pemasukan_per_kelas' => $pemasukanPerKelas,
            'pengeluaran_list' => $pengeluaranList,
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
        ];
    }
}
