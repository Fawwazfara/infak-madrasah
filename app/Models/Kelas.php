<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $fillable = ['nama_kelas', 'guru_id'];

    public function wali_kelas()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function siswas()
    {
        return $this->hasMany(Siswa::class);
    }
}
