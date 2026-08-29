<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Infak;
use Carbon\Carbon;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Siswa::with(['kelas', 'infaks']);

        if ($user->role === 'guru') {
            // Guru only sees their classes
            $kelasIds = Kelas::where('guru_id', $user->id)->pluck('id');
            $query->whereIn('kelas_id', $kelasIds);
        }

        // Sort alphabetically
        $query->orderBy('nama_lengkap', 'asc');

        $siswas = $query->get();

        // Transform data for frontend table
        $months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
        // Current academic year setup: July to June. We'll map the months to the payment array.
        
        $result = $siswas->map(function($siswa) use ($months) {
            $payments = [];
            foreach ($months as $m) {
                // Check if there is an infak for this month
                // In a real app we'd also check the academic year
                $hasPaid = $siswa->infaks->where('bulan', $this->mapBulanIndonesia($m))->count() > 0;
                $payments[] = $hasPaid;
            }

            return [
                'id' => $siswa->id,
                'name' => $siswa->nama_lengkap, // Pass full name instead of first word
                'fullName' => $siswa->nama_lengkap,
                'nis' => $siswa->nis,
                'blok' => $siswa->blok ?: 'N/A',
                'waliName' => $siswa->nama_wali_1 ?: 'Wali Siswa',
                'phone' => $siswa->wa_wali_1 ? '+62 ' . ltrim($siswa->wa_wali_1, '0') : '+62 812-0000-0000',
                'kelas' => $siswa->kelas ? $siswa->kelas->nama_kelas : '',
                'payments' => $payments
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $siswa = Siswa::create([
            'nama_lengkap' => $request->nama_lengkap,
            'kelas_id' => $request->kelas_id,
            'nis' => (string)rand(1000000, 9999999), // Auto generate NIS
            'jenis_kelamin' => $request->jenis_kelamin ?: 'L',
            'alamat' => $request->alamat,
            'blok' => $request->blok,
            'nama_wali_1' => $request->nama_wali_1,
            'wa_wali_1' => $request->wa_wali_1,
            'nama_wali_2' => $request->nama_wali_2,
            'wa_wali_2' => $request->wa_wali_2,
        ]);

        return response()->json(['message' => 'Siswa created successfully', 'data' => $siswa], 201);
    }

    public function show($id)
    {
        $siswa = Siswa::findOrFail($id);
        return response()->json($siswa);
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $request->validate([
            'nama_lengkap' => 'required|string',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $siswa->update([
            'nama_lengkap' => $request->nama_lengkap,
            'kelas_id' => $request->kelas_id,
            'jenis_kelamin' => $request->jenis_kelamin ?: $siswa->jenis_kelamin,
            'alamat' => $request->alamat,
            'blok' => $request->blok,
            'nama_wali_1' => $request->nama_wali_1,
            'wa_wali_1' => $request->wa_wali_1,
            'nama_wali_2' => $request->nama_wali_2,
            'wa_wali_2' => $request->wa_wali_2,
        ]);

        return response()->json(['message' => 'Siswa updated successfully', 'data' => $siswa], 200);
    }

    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        $siswa->delete();
        
        return response()->json(['message' => 'Siswa deleted successfully']);
    }

    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $query = Siswa::with(['kelas', 'infaks']);

        if ($user->role === 'guru') {
            $kelasIds = Kelas::where('guru_id', $user->id)->pluck('id');
            $query->whereIn('kelas_id', $kelasIds);
        }

        $siswas = $query->get();
        $months = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

        $fileName = 'backup_siswa_' . date('Y_m_d_His') . '.csv';
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('No', 'Nama Lengkap', 'NIS', 'Kelas', 'Wali', 'Telepon');
        foreach ($months as $m) {
            $columns[] = $m;
        }

        $callback = function() use($siswas, $columns, $months) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $i = 1;
            foreach ($siswas as $siswa) {
                $row = [
                    $i++,
                    $siswa->nama_lengkap,
                    $siswa->nis,
                    $siswa->kelas ? $siswa->kelas->nama_kelas : '',
                    $siswa->nama_wali_1 ?: '-',
                    $siswa->wa_wali_1 ?: '-'
                ];
                
                foreach ($months as $m) {
                    $hasPaid = $siswa->infaks->where('bulan', $this->mapBulanIndonesia($m))->count() > 0;
                    $row[] = $hasPaid ? 'Lunas' : 'Belum';
                }
                
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getUnpaidStudents(Request $request, $kelas_id)
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun', date('Y'));

        if (!$bulan) {
            return response()->json(['error' => 'Bulan is required'], 400);
        }

        $bulanFull = $this->mapBulanIndonesia($bulan);

        $siswas = Siswa::where('kelas_id', $kelas_id)->get();

        $paidStudentIds = Infak::where('kelas_id', $kelas_id)
            ->where('bulan', $bulanFull)
            ->where('tahun', $tahun)
            ->pluck('siswa_id')
            ->toArray();

        $unpaidSiswas = $siswas->reject(function ($siswa) use ($paidStudentIds) {
            return in_array($siswa->id, $paidStudentIds);
        })->values();

        $result = $unpaidSiswas->map(function($siswa) {
            return [
                'id' => $siswa->id,
                'fullName' => $siswa->nama_lengkap,
                'nis' => $siswa->nis,
            ];
        });

        return response()->json($result);
    }

    private function mapBulanIndonesia($shortMonth)
    {
        $map = [
            'Jul' => 'July',
            'Agu' => 'August',
            'Sep' => 'September',
            'Okt' => 'October',
            'Nov' => 'November',
            'Des' => 'December',
            'Jan' => 'January',
            'Feb' => 'February',
            'Mar' => 'March',
            'Apr' => 'April',
            'Mei' => 'May',
            'Jun' => 'June'
        ];

        return $map[$shortMonth] ?? $shortMonth;
    }
}
