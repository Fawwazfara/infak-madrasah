<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 0);
        $query = Pengeluaran::orderBy('tanggal', 'desc');

        if ($request->filled('bulan') && $request->query('bulan') !== 'Semua') {
            $bulan = $request->query('bulan');
            $bulanIndoKeAngka = [
                'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
                'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
                'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
            ];
            if (isset($bulanIndoKeAngka[$bulan])) {
                $query->whereMonth('tanggal', $bulanIndoKeAngka[$bulan]);
            }
        }

        if ($perPage > 0) {
            return response()->json($query->paginate($perPage));
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'keperluan' => 'required|string',
            'nominal' => 'required|numeric',
            'tanggal' => 'required|date',
        ]);

        $pengeluaran = Pengeluaran::create([
            'keterangan' => $request->keperluan,
            'jumlah' => $request->nominal,
            'tanggal' => $request->tanggal,
            'kategori' => 'Umum' // default
        ]);

        LogAktivitas::create([
            'type' => 'expense',
            'title' => 'Pengeluaran Baru',
            'description' => $request->keperluan,
            'amount' => $request->nominal
        ]);

        return response()->json($pengeluaran, 201);
    }

    public function destroy($id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        
        LogAktivitas::create([
            'type' => 'system',
            'title' => 'Pengeluaran Dihapus',
            'description' => 'Menghapus pengeluaran: ' . $pengeluaran->keterangan,
            'amount' => null
        ]);

        $pengeluaran->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
