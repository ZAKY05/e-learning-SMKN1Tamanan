// database/migrations/2025_01_15_000009_create_ujian_jawaban_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ujian_jawaban')) {
            return;
        }

        Schema::create('ujian_jawaban', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hasil_id');
            $table->unsignedBigInteger('soal_id');
            $table->text('jawaban_siswa');
            $table->integer('nilai_perolehan')->nullable();
            $table->text('komentar_guru')->nullable(); // untuk essay
            $table->timestamps();

            $table->foreign('hasil_id')
                  ->references('id_hasil')
                  ->on('ujian_hasil')
                  ->onDelete('cascade');

            $table->foreign('soal_id')
                  ->references('id_soal')
                  ->on('bank_soal')
                  ->onDelete('cascade');

            $table->unique(['hasil_id', 'soal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_jawaban');
    }
};
