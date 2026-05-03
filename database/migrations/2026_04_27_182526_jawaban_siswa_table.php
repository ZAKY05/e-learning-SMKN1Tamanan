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
        Schema::create('jawaban_siswa', function (Blueprint $table) {
            $table->id('id_jawaban');
            $table->unsignedBigInteger('hasil_id'); // relasi ke hasil_kuis
            $table->unsignedBigInteger('soal_id');
            $table->unsignedBigInteger('pilihan_id')->nullable(); // untuk pilihan ganda
            $table->text('jawaban_essay')->nullable(); // untuk essay
            $table->boolean('is_correct')->nullable(); // benar/salah (pilihan ganda auto, essay manual)
            $table->decimal('poin', 5, 2)->default(0); // poin yang didapat
            $table->text('catatan_guru')->nullable(); // feedback guru untuk essay
            $table->timestamps();

            // Foreign keys
            $table->foreign('hasil_id')->references('id_hasil')->on('hasil_kuis')->onDelete('cascade');
            $table->foreign('soal_id')->references('id_soal')->on('soal_kuis')->onDelete('cascade');
            $table->foreign('pilihan_id')->references('id_pilihan')->on('pilihan_jawaban')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_siswa');
    }
};