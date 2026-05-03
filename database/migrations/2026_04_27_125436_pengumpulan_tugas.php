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
        Schema::create('pengumpulan_tugas', function (Blueprint $table) {
            $table->id('id_pengumpulan');
            $table->unsignedBigInteger('tugas_id');
            $table->unsignedBigInteger('siswa_id');
            $table->text('jawaban')->nullable(); // jawaban text
            $table->string('file_path')->nullable(); // file jawaban
            $table->string('file_name')->nullable();
            $table->dateTime('tanggal_pengumpulan');
            $table->decimal('nilai', 5, 2)->nullable(); // nilai yang diberikan guru
            $table->text('catatan_guru')->nullable(); // feedback dari guru
            $table->enum('status', ['dikumpulkan', 'terlambat', 'dinilai'])->default('dikumpulkan');
            $table->timestamps();

            // Foreign keys
            $table->foreign('tugas_id')->references('id_tugas')->on('tugas')->onDelete('cascade');
            $table->foreign('siswa_id')->references('id_siswa')->on('student')->onDelete('cascade');
            
            // Unique constraint: satu siswa hanya bisa mengumpulkan satu kali per tugas
            $table->unique(['tugas_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_tugas');
    }
};