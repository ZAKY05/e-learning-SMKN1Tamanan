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
        Schema::create('kuis', function (Blueprint $table) {
            $table->id('id_kuis');
            $table->unsignedBigInteger('guru_id');
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('materi_id')->nullable();
            $table->string('judul_kuis');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['kuis_harian', 'uts', 'uas']); // tipe ujian
            $table->integer('durasi_menit'); // durasi pengerjaan dalam menit
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->integer('bobot_nilai')->default(100);
            $table->boolean('acak_soal')->default(false); // acak urutan soal
            $table->boolean('tampilkan_nilai')->default(true); // tampilkan nilai setelah selesai
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamps();

            // Foreign keys
            $table->foreign('guru_id')->references('id_guru')->on('guru')->onDelete('cascade');
            $table->foreign('mapel_id')->references('id_mapel')->on('mapel')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id_kelas')->on('kelas')->onDelete('cascade');
            $table->foreign('materi_id')->references('id_materi')->on('materi')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuis');
    }
};