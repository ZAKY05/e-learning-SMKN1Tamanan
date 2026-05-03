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
        Schema::create('rekap_nilai', function (Blueprint $table) {
            $table->id('id_rekap');
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedBigInteger('kelas_id');
            $table->string('tahun_ajaran', 9); // contoh: 2024/2025
            $table->enum('semester', ['ganjil', 'genap']);
            
            // Nilai Tugas (rata-rata dari semua tugas)
            $table->decimal('nilai_tugas', 5, 2)->nullable();
            $table->integer('jumlah_tugas')->default(0);
            
            // Nilai Kuis Harian (rata-rata dari semua kuis harian)
            $table->decimal('nilai_kuis', 5, 2)->nullable();
            $table->integer('jumlah_kuis')->default(0);
            
            // Nilai UTS
            $table->decimal('nilai_uts', 5, 2)->nullable();
            
            // Nilai UAS
            $table->decimal('nilai_uas', 5, 2)->nullable();
            
            // Nilai Akhir (kalkulasi otomatis dari bobot)
            // Contoh: Tugas 30%, Kuis 20%, UTS 20%, UAS 30%
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            
            // Predikat nilai (A, B, C, D, E)
            $table->string('predikat', 2)->nullable();
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('siswa_id')->references('id_siswa')->on('student')->onDelete('cascade');
            $table->foreign('mapel_id')->references('id_mapel')->on('mapel')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id_kelas')->on('kelas')->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['siswa_id', 'mapel_id', 'tahun_ajaran', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_nilai');
    }
};