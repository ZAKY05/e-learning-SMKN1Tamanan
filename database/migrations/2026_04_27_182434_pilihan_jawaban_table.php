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
        Schema::create('pilihan_jawaban', function (Blueprint $table) {
            $table->id('id_pilihan');
            $table->unsignedBigInteger('soal_id');
            $table->string('pilihan'); // A, B, C, D, E
            $table->text('isi_pilihan'); // isi dari pilihan jawaban
            $table->string('gambar_pilihan')->nullable(); // gambar untuk pilihan jika ada
            $table->boolean('is_correct')->default(false); // jawaban yang benar
            $table->timestamps();

            // Foreign keys
            $table->foreign('soal_id')->references('id_soal')->on('soal_kuis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilihan_jawaban');
    }
};