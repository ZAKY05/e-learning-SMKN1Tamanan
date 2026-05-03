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
        Schema::create('hasil_kuis', function (Blueprint $table) {
            $table->id('id_hasil');
            $table->unsignedBigInteger('kuis_id');
            $table->unsignedBigInteger('siswa_id');
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();
            $table->decimal('nilai', 5, 2)->nullable(); // nilai akhir
            $table->integer('jumlah_benar')->default(0);
            $table->integer('jumlah_salah')->default(0);
            $table->integer('tidak_dijawab')->default(0);
            $table->enum('status', ['sedang_mengerjakan', 'selesai', 'dinilai'])->default('sedang_mengerjakan');
            $table->timestamps();

            // Foreign keys
            $table->foreign('kuis_id')->references('id_kuis')->on('kuis')->onDelete('cascade');
            $table->foreign('siswa_id')->references('id_siswa')->on('student')->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['kuis_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_kuis');
    }
};