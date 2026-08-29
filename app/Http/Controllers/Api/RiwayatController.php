<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index()
    {
        $logs = LogAktivitas::orderBy('created_at', 'desc')->get();
        return response()->json($logs);
    }

    public function clear()
    {
        LogAktivitas::truncate();
        return response()->json(['message' => 'Riwayat berhasil dihapus']);
    }
}
