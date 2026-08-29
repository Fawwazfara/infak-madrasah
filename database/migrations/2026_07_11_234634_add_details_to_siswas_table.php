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
        Schema::table('siswas', function (Blueprint $table) {
            $table->text('alamat')->nullable();
            $table->string('blok')->nullable();
            $table->string('nama_wali_1')->nullable();
            $table->string('wa_wali_1')->nullable();
            $table->string('nama_wali_2')->nullable();
            $table->string('wa_wali_2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'blok', 'nama_wali_1', 'wa_wali_1', 'nama_wali_2', 'wa_wali_2']);
        });
    }
};
