// database/migrations/2025_01_15_000003_create_tugas_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tugas')) {
            return;
        }

        Schema::create('tugas', function (Blueprint $table) {
            $table->id('id_tugas');
            $table->unsignedBigInteger('pengajaran_id');
            $table->string('judul', 150);
            $table->text('deskripsi');
            $table->string('file_soal', 255)->nullable();
            $table->dateTime('deadline');
            $table->integer('maksimal_nilai')->default(100);
            $table->enum('status', ['draft', 'published', 'closed'])->default('published');
            $table->timestamps();

            $table->foreign('pengajaran_id')
                  ->references('id_pengajaran')
                  ->on('pengajaran')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};
