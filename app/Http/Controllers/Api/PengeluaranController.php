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
        $isGuru = $request->user()?->role === 'guru';
        $query = Pengeluaran::orderBy('tanggal', 'desc')->orderBy('id', 'desc');

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
            $paginated = $query->paginate($perPage);
            if ($isGuru) {
                $paginated->setCollection(Pengeluaran::maskKasbonForGuru($paginated->getCollection()));
            }
            return response()->json($paginated);
        }

        $items = $query->get();
        if ($isGuru) {
            $items = Pengeluaran::maskKasbonForGuru($items);
        }

        return response()->json($items);
    }

    public function store(Request $request)
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat mencatat pengeluaran.');
        }

        $request->validate([
            'keperluan' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
            'tanggal' => 'required|date',
            'kategori' => 'nullable|string|max:100',
        ]);

        $kategori = trim((string) $request->input('kategori', ''));
        if ($kategori === '') {
            $kategori = str_contains(strtolower($request->keperluan), 'kasbon') ? 'Kasbon Guru' : 'Operasional';
        }

        $pengeluaran = Pengeluaran::create([
            'keterangan' => $request->keperluan,
            'jumlah' => $request->nominal,
            'tanggal' => $request->tanggal,
            'kategori' => $kategori,
        ]);

        LogAktivitas::create([
            'type' => 'expense',
            'title' => $kategori === 'Kasbon Guru' ? 'Kasbon Guru Baru' : 'Pengeluaran Baru',
            'description' => $request->keperluan,
            'amount' => $request->nominal,
        ]);

        return response()->json($pengeluaran, 201);
    }

    public function update(Request $request, $id)
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat mengubah pengeluaran.');
        }

        $pengeluaran = Pengeluaran::findOrFail($id);

        $request->validate([
            'keperluan' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
            'tanggal' => 'required|date',
            'kategori' => 'nullable|string|max:100',
        ]);

        $kategori = trim((string) $request->input('kategori', $pengeluaran->kategori ?: 'Operasional'));

        $pengeluaran->update([
            'keterangan' => $request->keperluan,
            'jumlah' => $request->nominal,
            'tanggal' => $request->tanggal,
            'kategori' => $kategori,
        ]);

        LogAktivitas::create([
            'type' => 'expense',
            'title' => 'Pengeluaran Diperbarui',
            'description' => $request->keperluan,
            'amount' => $request->nominal,
        ]);

        return response()->json($pengeluaran);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat menghapus pengeluaran.');
        }

        $pengeluaran = Pengeluaran::findOrFail($id);

        LogAktivitas::create([
            'type' => 'system',
            'title' => 'Pengeluaran Dihapus',
            'description' => 'Menghapus pengeluaran: ' . $pengeluaran->keterangan,
            'amount' => null,
        ]);

        $pengeluaran->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
