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
        Schema::create('bobot_nilai', function (Blueprint $table) {
            $table->id('id_bobot');
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedBigInteger('kelas_id')->nullable(); // null = berlaku untuk semua kelas
            $table->string('tahun_ajaran', 9);
            $table->enum('semester', ['ganjil', 'genap']);
            $table->integer('bobot_tugas')->default(30); // dalam persen
            $table->integer('bobot_kuis')->default(20); // dalam persen
            $table->integer('bobot_uts')->default(20); // dalam persen
            $table->integer('bobot_uas')->default(30); // dalam persen
            $table->timestamps();

            // Foreign keys
            $table->foreign('mapel_id')->references('id_mapel')->on('mapel')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id_kelas')->on('kelas')->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['mapel_id', 'kelas_id', 'tahun_ajaran', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bobot_nilai');
    }
};