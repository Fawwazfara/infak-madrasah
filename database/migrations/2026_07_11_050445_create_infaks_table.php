<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('infaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->nullable()->constrained('siswas')->onDelete('cascade');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->onDelete('cascade'); // Added for Bambim
            $table->integer('jumlah');
            $table->string('bulan');
            $table->integer('tahun');
            $table->date('tanggal_bayar')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infaks');
    }
};
