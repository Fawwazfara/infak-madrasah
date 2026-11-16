<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function index()
    {
        $teachers = User::where('role', 'guru')->orderBy('name')->get();

        $kelasByGuru = Kelas::whereIn('guru_id', $teachers->pluck('id'))
            ->get()
            ->groupBy(fn ($k) => (int) $k->guru_id);

        $result = $teachers->map(function ($teacher) use ($kelasByGuru) {
            $teacherKelas = $kelasByGuru->get((int) $teacher->id) ?? collect();
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'password' => 'Terkunci',
                'kelas' => $teacherKelas->pluck('nama_kelas')->values(),
                'kelas_ids' => $teacherKelas->pluck('id')->map(fn ($id) => (int) $id)->values(),
                'colorClass' => 'bg-[#F9EFE7] text-[#A8551E]',
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request)
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat membuat akun guru.');
        }

        $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'kelas_ids' => 'array',
            'kelas_ids.*' => 'integer|exists:kelas,id',
        ]);

        $teacher = DB::transaction(function () use ($request) {
            $teacher = User::create([
                'name' => $request->fullName,
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'role' => 'guru',
            ]);

            if ($request->has('kelas_ids') && is_array($request->kelas_ids) && count($request->kelas_ids) > 0) {
                Kelas::whereIn('id', $request->kelas_ids)->update(['guru_id' => $teacher->id]);
            }

            return $teacher;
        });

        return response()->json(['message' => 'Guru created successfully', 'id' => $teacher->id], 201);
    }

    public function updateKelas(Request $request, $id)
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat mengatur kelas guru.');
        }

        $teacher = User::where('role', 'guru')->findOrFail($id);

        $validated = $request->validate([
            'kelas_ids' => 'present|array',
            'kelas_ids.*' => 'integer|exists:kelas,id',
        ]);

        $kelasIds = array_values(array_unique(array_map('intval', $validated['kelas_ids'])));

        DB::transaction(function () use ($teacher, $kelasIds) {
            $clearQuery = Kelas::where('guru_id', $teacher->id);
            if (!empty($kelasIds)) {
                $clearQuery->whereNotIn('id', $kelasIds);
            }
            $clearQuery->update(['guru_id' => null]);

            if (!empty($kelasIds)) {
                Kelas::whereIn('id', $kelasIds)->update(['guru_id' => $teacher->id]);
            }
        });

        $assigned = Kelas::where('guru_id', $teacher->id)->get();

        return response()->json([
            'message' => 'Penugasan kelas berhasil diperbarui.',
            'kelas' => $assigned->pluck('nama_kelas')->values(),
            'kelas_ids' => $assigned->pluck('id')->map(fn ($kid) => (int) $kid)->values(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat menghapus akun guru.');
        }

        $teacher = User::where('role', 'guru')->findOrFail($id);

        Kelas::where('guru_id', $teacher->id)->update(['guru_id' => null]);
        $teacher->delete();

        return response()->json(['message' => 'Guru deleted successfully']);
    }

    public function resetPassword(Request $request, $id)
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat mereset password guru.');
        }

        $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $teacher = User::where('role', 'guru')->findOrFail($id);
        $teacher->password = Hash::make($request->new_password);
        $teacher->save();

        return response()->json(['message' => 'Password berhasil direset']);
    }
}
