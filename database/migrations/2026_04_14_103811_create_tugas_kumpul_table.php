// database/migrations/2025_01_15_000004_create_tugas_kumpul_table.php pengumpulan tugas
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tugas_kumpul')) {
            return;
        }

        Schema::create('tugas_kumpul', function (Blueprint $table) {
            $table->id('id_kumpul');
            $table->unsignedBigInteger('tugas_id');
            $table->unsignedBigInteger('siswa_id');     // ke student.id_siswa
            $table->string('file_jawaban', 255);
            $table->text('komentar_siswa')->nullable();
            $table->dateTime('waktu_kumpul');
            $table->integer('nilai')->nullable();
            $table->text('komentar_guru')->nullable();
            $table->enum('status_penilaian', ['belum', 'sudah'])->default('belum');
            $table->timestamps();

            $table->foreign('tugas_id')
                  ->references('id_tugas')
                  ->on('tugas')
                  ->onDelete('cascade');

            $table->foreign('siswa_id')
                  ->references('id_siswa')
                  ->on('student')
                  ->onDelete('cascade');

            // Satu siswa hanya bisa kumpul sekali per tugas
            $table->unique(['tugas_id', 'siswa_id'], 'unique_tugas_siswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_kumpul');
    }
};
