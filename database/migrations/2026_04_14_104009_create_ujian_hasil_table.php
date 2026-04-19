// database/migrations/2025_01_15_000008_create_ujian_hasil_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ujian_hasil')) {
            return;
        }

        Schema::create('ujian_hasil', function (Blueprint $table) {
            $table->id('id_hasil');
            $table->unsignedBigInteger('ujian_id');
            $table->unsignedBigInteger('siswa_id');
            $table->integer('nilai_total')->nullable();
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();
            $table->boolean('is_finished')->default(false);
            $table->timestamps();

            $table->foreign('ujian_id')
                  ->references('id_ujian')
                  ->on('ujian')
                  ->onDelete('cascade');

            $table->foreign('siswa_id')
                  ->references('id_siswa')
                  ->on('student')
                  ->onDelete('cascade');

            $table->unique(['ujian_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_hasil');
    }
};
