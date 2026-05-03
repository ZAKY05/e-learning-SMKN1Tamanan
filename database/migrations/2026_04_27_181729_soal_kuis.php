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
        Schema::create('soal_kuis', function (Blueprint $table) {
            $table->id('id_soal');
            $table->unsignedBigInteger('kuis_id');
            $table->text('pertanyaan');
            $table->string('gambar')->nullable(); // gambar soal jika ada
            $table->enum('tipe_soal', ['pilihan_ganda', 'essay']);
            $table->integer('poin')->default(1); // poin per soal
            $table->integer('nomor_urut'); // urutan soal
            $table->timestamps();

            // Foreign keys
            $table->foreign('kuis_id')->references('id_kuis')->on('kuis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soal_kuis');
    }
};