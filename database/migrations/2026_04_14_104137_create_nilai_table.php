// database/migrations/2025_01_15_000011_create_nilai_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nilai')) {
            return;
        }

        Schema::create('nilai', function (Blueprint $table) {
            $table->id('id_nilai');
            $table->unsignedBigInteger('siswa_id');
            $table->morphs('nilaiable'); // tugas_id atau ujian_id
            $table->integer('nilai');
            $table->text('catatan')->nullable();
            $table->unsignedInteger('input_by'); // guru_id
            $table->timestamps();

            $table->foreign('siswa_id')
                  ->references('id_siswa')
                  ->on('student')
                  ->onDelete('cascade');

            $table->foreign('input_by')
                  ->references('id_guru')
                  ->on('guru')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai');
    }
};
