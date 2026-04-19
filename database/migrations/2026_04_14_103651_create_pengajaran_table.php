// database/migrations/2025_01_15_000001_create_pengajaran_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pengajaran')) {
            return;
        }

        Schema::create('pengajaran', function (Blueprint $table) {
            $table->id('id_pengajaran');
            $table->unsignedInteger('guru_id');      // dari tabel guru
            $table->unsignedInteger('kelas_id');     // dari tabel kelas
            $table->unsignedInteger('mapel_id');     // dari tabel mapel
            $table->string('tahun_ajaran', 9);       // 2024/2025
            $table->enum('semester', ['ganjil', 'genap']);
            $table->timestamps();

            $table->foreign('guru_id')
                  ->references('id_guru')
                  ->on('guru')
                  ->onDelete('cascade');

            $table->foreign('kelas_id')
                  ->references('id_kelas')
                  ->on('kelas')
                  ->onDelete('cascade');

            $table->foreign('mapel_id')
                  ->references('id_mapel')
                  ->on('mapel')
                  ->onDelete('cascade');

            // Unique: satu guru tidak bisa mengajar mapel sama di kelas sama dalam 1 semester
            $table->unique(['guru_id', 'kelas_id', 'mapel_id', 'tahun_ajaran', 'semester'], 'unique_pengajaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajaran');
    }
};
