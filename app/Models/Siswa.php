<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_lengkap', 'nis', 'kelas_id', 'jenis_kelamin',
        'alamat', 'blok', 'nama_wali_1', 'wa_wali_1', 'nama_wali_2', 'wa_wali_2'
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function infaks()
    {
        return $this->hasMany(Infak::class);
    }
}
