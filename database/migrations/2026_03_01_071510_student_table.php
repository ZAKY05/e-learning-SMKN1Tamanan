<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        if (Schema::hasTable('student')) {
            return;
        }

        Schema::create('student', function (Blueprint $table) {

            $table->bigIncrements('id_siswa'); // BIGINT AUTO_INCREMENT PRIMARY KEY

            $table->string('nis', 15)->unique();
            $table->string('nama', 30);

            $table->unsignedInteger('jurusan_id')->nullable();
            $table->unsignedBigInteger('kelas_id')->nullable();

            $table->string('foto_profil', 255)->nullable();

            $table->timestamps();

            $table->foreign('jurusan_id')
                ->references('id_jurusan')
                ->on('jurusan')
                ->onDelete('set null');

            $table->foreign('kelas_id')
                ->references('id_kelas')
                ->on('kelas')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student');
    }
};