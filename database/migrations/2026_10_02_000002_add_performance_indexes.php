<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('infaks', function (Blueprint $table) {
            // Kepatuhan/riwayat: where siswa_id + tahun + bulan
            $table->index(['siswa_id', 'tahun', 'bulan'], 'infaks_siswa_tahun_bulan_index');
            // Laporan per kelas per periode
            $table->index(['kelas_id', 'tanggal_bayar'], 'infaks_kelas_tanggal_index');
            // Filter & urutkan laporan/dashboard per tanggal bayar
            $table->index('tanggal_bayar', 'infaks_tanggal_bayar_index');
        });

        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->index('tanggal', 'pengeluarans_tanggal_index');
        });

        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->index('created_at', 'log_aktivitas_created_at_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            // Unread count: where receiver + is_read
            $table->index(['receiver_id', 'is_read'], 'messages_receiver_read_index');
            // Chat list: pesan terakhir antara dua pengguna
            $table->index(['sender_id', 'receiver_id', 'created_at'], 'messages_pair_created_index');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->index('nama_lengkap', 'siswas_nama_lengkap_index');
        });
    }

    public function down(): void
    {
        Schema::table('infaks', function (Blueprint $table) {
            $table->dropIndex('infaks_siswa_tahun_bulan_index');
            $table->dropIndex('infaks_kelas_tanggal_index');
            $table->dropIndex('infaks_tanggal_bayar_index');
        });

        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->dropIndex('pengeluarans_tanggal_index');
        });

        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->dropIndex('log_aktivitas_created_at_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_receiver_read_index');
            $table->dropIndex('messages_pair_created_index');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropIndex('siswas_nama_lengkap_index');
        });
    }
};
