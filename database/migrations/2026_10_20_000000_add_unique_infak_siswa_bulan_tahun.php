<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu siswa hanya boleh punya satu pembayaran per bulan per tahun ajaran.
     * Duplikat lama (mis. double-submit) dibersihkan dengan menyisakan baris
     * paling awal sebelum unique index dipasang.
     */
    public function up(): void
    {
        if (!Schema::hasTable('infaks')) {
            return;
        }

        $duplicates = DB::table('infaks')
            ->select('siswa_id', 'tahun', 'bulan', DB::raw('MIN(id) as keep_id'))
            ->whereNotNull('siswa_id')
            ->groupBy('siswa_id', 'tahun', 'bulan')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('infaks')
                ->where('siswa_id', $duplicate->siswa_id)
                ->where('tahun', $duplicate->tahun)
                ->where('bulan', $duplicate->bulan)
                ->where('id', '<>', $duplicate->keep_id)
                ->delete();
        }

        $indexExists = collect(Schema::getIndexes('infaks'))
            ->contains(fn ($index) => $index['name'] === 'infaks_siswa_tahun_bulan_unique');

        if (!$indexExists) {
            Schema::table('infaks', function (Blueprint $table) {
                $table->unique(
                    ['siswa_id', 'tahun', 'bulan'],
                    'infaks_siswa_tahun_bulan_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('infaks')) {
            return;
        }

        $indexExists = collect(Schema::getIndexes('infaks'))
            ->contains(fn ($index) => $index['name'] === 'infaks_siswa_tahun_bulan_unique');

        if ($indexExists) {
            Schema::table('infaks', function (Blueprint $table) {
                $table->dropUnique('infaks_siswa_tahun_bulan_unique');
            });
        }
    }
};
