// database/migrations/2025_01_15_000010_create_absensi_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('absensi')) {
            return;
        }

        Schema::create('absensi', function (Blueprint $table) {
            $table->id('id_absensi');
            $table->unsignedBigInteger('pengajaran_id');
            $table->unsignedBigInteger('siswa_id');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa']);
            $table->text('keterangan')->nullable();
            $table->integer('pertemuan_ke');
            $table->timestamps();

            $table->foreign('pengajaran_id')
                  ->references('id_pengajaran')
                  ->on('pengajaran')
                  ->onDelete('cascade');

            $table->foreign('siswa_id')
                  ->references('id_siswa')
                  ->on('student')
                  ->onDelete('cascade');

            // Cegah double absen di hari yang sama untuk siswa & pengajaran yang sama
            $table->unique(['pengajaran_id', 'siswa_id', 'tanggal'], 'unique_absensi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
