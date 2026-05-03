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
        Schema::create('jadwal_pelajaran', function (Blueprint $table) {
            $table->id('id_jadwal');
            $table->unsignedBigInteger('guru_id');
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedBigInteger('kelas_id');
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->tinyInteger('jam_ke');             // jam pelajaran ke-1 s/d 11
            $table->string('tahun_ajaran', 9);         // contoh: 2024/2025
            $table->enum('semester', ['ganjil', 'genap']);
            $table->timestamps();

            // Foreign keys
            $table->foreign('guru_id')->references('id_guru')->on('guru')->onDelete('cascade');
            $table->foreign('mapel_id')->references('id_mapel')->on('mapel')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id_kelas')->on('kelas')->onDelete('cascade');

            // 1 kelas hanya boleh 1 mapel di hari & jam tertentu pada tahun ajaran & semester tertentu
            $table->unique(['kelas_id', 'hari', 'jam_ke', 'tahun_ajaran', 'semester'], 'jadwal_kelas_unik');
            // 1 guru tidak boleh mengajar 2 kelas di hari & jam yang sama
            $table->unique(['guru_id', 'hari', 'jam_ke', 'tahun_ajaran', 'semester'], 'jadwal_guru_unik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajaran');
    }
};
