<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Samakan kolom `tahun` pada tabel infaks dengan tahun ajaran
     * (Juli s/d Juni = satu tahun ajaran), dihitung dari tanggal record dibuat.
     */
    public function up(): void
    {
        DB::table('infaks')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                if (empty($row->created_at)) {
                    continue;
                }

                $created = \Carbon\Carbon::parse($row->created_at);
                $academicStart = $created->month >= 7 ? $created->year : $created->year - 1;

                if ((int) $row->tahun !== $academicStart) {
                    DB::table('infaks')->where('id', $row->id)->update(['tahun' => $academicStart]);
                }
            }
        });
    }

    public function down(): void
    {
        // Tidak ada perbaikan balik otomatis: cukup jalankan ulang sebelum update semantic.
    }
};
