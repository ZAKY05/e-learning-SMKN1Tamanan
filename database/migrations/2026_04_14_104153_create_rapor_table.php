// database/migrations/2025_01_15_000012_create_rapor_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rapor')) {
            return;
        }

        Schema::create('rapor', function (Blueprint $table) {
            $table->id('id_rapor');
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedInteger('kelas_id');
            $table->string('tahun_ajaran', 9);
            $table->enum('semester', ['ganjil', 'genap']);
            $table->json('nilai_mapels'); // format: [{"mapel_id":1, "nilai":85, "predikat":"B"}]
            $table->text('catatan_wali_kelas')->nullable();
            $table->integer('rata_rata');
            $table->integer('ranking_kelas')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')
                  ->references('id_siswa')
                  ->on('student')
                  ->onDelete('cascade');

            $table->foreign('kelas_id')
                  ->references('id_kelas')
                  ->on('kelas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapor');
    }
};
