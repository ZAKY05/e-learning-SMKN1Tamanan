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
        Schema::create('detail_presensi', function (Blueprint $table) {
            $table->id('id_detail');
            $table->unsignedBigInteger('presensi_id');
            $table->unsignedBigInteger('siswa_id');
            $table->time('waktu_presensi')->nullable();
            $table->string('latitude', 20)->nullable(); // koordinat siswa saat presensi
            $table->string('longitude', 20)->nullable();
            $table->decimal('jarak_meter', 8, 2)->nullable(); // jarak dari lokasi guru
            $table->enum('status_kehadiran', ['hadir', 'izin', 'sakit', 'alpha'])->default('alpha');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('presensi_id')->references('id_presensi')->on('presensi')->onDelete('cascade');
            $table->foreign('siswa_id')->references('id_siswa')->on('student')->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['presensi_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_presensi');
    }
};