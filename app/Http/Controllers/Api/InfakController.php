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
        $tahunSekarang = date('Y'); // Simplifying for now
        
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

        $studentNames = [];
        $totalAmount = 0;

        foreach ($siswaIds as $sId) {
            $siswaName = 'Siswa Tidak Diketahui';
            if ($sId) {
                $siswa = Siswa::find($sId);
                if ($siswa) {
                    $siswaName = $siswa->nama_lengkap;
                }
            }
            if (!in_array($siswaName, $studentNames)) {
                $studentNames[] = $siswaName;
            }

            foreach ($bulanArray as $bulan) {
                $infak = Infak::create([
                    'siswa_id' => $sId,
                    'kelas_id' => $request->kelas_id,
                    'jumlah' => $request->nominal,
                    'bulan' => $this->mapBulanToEnglish($bulan),
                    'tahun' => $tahunSekarang,
                    'tanggal_bayar' => $request->tanggal_bayar,
                    'keterangan' => 'Via API'
                ]);
                $totalAmount += $request->nominal;
            }
        }

        $namesStr = implode(', ', $studentNames);
        $monthsStr = implode(', ', $bulanArray);

        LogAktivitas::create([
            'type' => 'income',
            'title' => 'Pemasukan Baru (Massal/Satuan)',
            'description' => "Penerimaan infak dari $namesStr untuk bulan $monthsStr di $kelasName",
            'amount' => $totalAmount
        ]);

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

        $infak = Infak::findOrFail($id);
        $infak->update([
            'tanggal_bayar' => $request->tanggal_bayar,
            'jumlah' => $request->jumlah,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
        ]);

        return response()->json(['message' => 'Infak updated successfully']);
    }

    public function getBySiswa(Request $request, $id)
    {
        $tahun = $request->query('tahun', date('Y'));
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
            'tahun' => 'required|numeric',
            'months' => 'required|array',
            'nominal' => 'required|numeric',
            'tanggal_bayar' => 'required|date'
        ]);

        $siswaId = $request->siswa_id;
        $tahun = $request->tahun;
        $requestedMonths = array_map([$this, 'mapBulanToEnglish'], $request->months);

        // Get existing records for the student and year
        $existingInfak = Infak::where('siswa_id', $siswaId)
                              ->where('tahun', $tahun)
                              ->get();

        $existingMonths = $existingInfak->pluck('bulan')->toArray();

        // Find which months to delete and which to insert
        $toDelete = array_diff($existingMonths, $requestedMonths);
        $toInsert = array_diff($requestedMonths, $existingMonths);

        // Delete un-checked months
        if (!empty($toDelete)) {
            $siswaName = 'Siswa';
            $siswa = Siswa::find($siswaId);
            if ($siswa) $siswaName = $siswa->nama_lengkap;

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
                'jumlah' => $request->nominal,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tanggal_bayar' => $request->tanggal_bayar,
                'keterangan' => 'Disinkronisasi via Edit Siswa'
            ]);

            $siswaName = 'Siswa';
            $siswa = Siswa::find($siswaId);
            if ($siswa) $siswaName = $siswa->nama_lengkap;

            LogAktivitas::create([
                'type' => 'income',
                'title' => 'Pemasukan Baru (Sync)',
                'description' => "Penerimaan infak dari $siswaName untuk bulan $bulan $tahun",
                'amount' => $request->nominal
            ]);
        }

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
