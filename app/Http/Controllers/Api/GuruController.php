<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function index()
    {
        // Get all teachers and their classes
        $teachers = User::where('role', 'guru')->get();

        $result = $teachers->map(function ($teacher) {
            $kelas = Kelas::where('guru_id', $teacher->id)->pluck('nama_kelas');
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'password' => 'Terkunci', // Usually shouldn't send raw password back, but per the UI design they want to view it. But passwords are hashed. We might need a separate way or just say 'Terkunci'. 
                // However the prompt UI shows the password, in a real app we'd reset it or not show it. I will leave it empty.
                'kelas' => $kelas,
                'colorClass' => 'bg-primary-container text-on-primary-container'
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fullName' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'kelas_ids' => 'array'
        ]);

        $teacher = User::create([
            'name' => $request->fullName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru'
        ]);

        if ($request->has('kelas_ids') && is_array($request->kelas_ids)) {
            Kelas::whereIn('id', $request->kelas_ids)->update(['guru_id' => $teacher->id]);
        }

        return response()->json(['message' => 'Guru created successfully'], 201);
    }

    public function destroy($id)
    {
        $teacher = User::findOrFail($id);
        
        // Remove guru_id from associated classes
        Kelas::where('guru_id', $teacher->id)->update(['guru_id' => null]);
        
        $teacher->delete();
        
        return response()->json(['message' => 'Guru deleted successfully']);
    }
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|string|min:6'
        ]);

        $teacher = User::findOrFail($id);
        $teacher->password = Hash::make($request->new_password);
        $teacher->save();

        return response()->json(['message' => 'Password berhasil direset']);
    }
}
