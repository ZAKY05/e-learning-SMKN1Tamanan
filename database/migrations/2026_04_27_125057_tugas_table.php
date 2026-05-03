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
        Schema::create('tugas', function (Blueprint $table) {
            $table->id('id_tugas');
            $table->unsignedBigInteger('guru_id');
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('materi_id')->nullable(); // relasi ke materi jika ada
            $table->string('judul_tugas');
            $table->text('deskripsi');
            $table->string('file_path')->nullable(); // file lampiran tugas
            $table->string('file_name')->nullable();
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_deadline');
            $table->integer('bobot_nilai')->default(100); // nilai maksimal
            $table->enum('status', ['draft', 'published', 'closed'])->default('published');
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
        Schema::dropIfExists('tugas');
    }
};