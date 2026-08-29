<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function index()
    {
        return response()->json(Pengeluaran::orderBy('tanggal', 'desc')->get());
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
