// database/migrations/2025_01_15_000002_create_materi_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('materi')) {
            return;
        }

        Schema::create('materi', function (Blueprint $table) {
            $table->id('id_materi');
            $table->unsignedBigInteger('pengajaran_id');
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->enum('tipe_file', ['pdf', 'ppt', 'pptx', 'video', 'zip', 'link'])->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->foreign('pengajaran_id')
                  ->references('id_pengajaran')
                  ->on('pengajaran')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materi');
    }
};
