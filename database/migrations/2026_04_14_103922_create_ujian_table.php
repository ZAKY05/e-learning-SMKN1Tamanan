// database/migrations/2025_01_15_000006_create_ujian_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ujian')) {
            return;
        }

        Schema::create('ujian', function (Blueprint $table) {
            $table->id('id_ujian');
            $table->unsignedBigInteger('pengajaran_id');
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->integer('durasi_menit');
            $table->boolean('acak_soal')->default(false);
            $table->boolean('acak_opsi')->default(false);
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->timestamps();

            $table->foreign('pengajaran_id')
                  ->references('id_pengajaran')
                  ->on('pengajaran')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian');
    }
};
