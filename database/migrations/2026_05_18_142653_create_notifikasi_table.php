<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Tipe notifikasi: presensi | materi | tugas | kuis | penilaian
            $table->enum('tipe', ['presensi', 'materi', 'tugas', 'kuis', 'penilaian']);

            $table->string('judul');
            $table->text('isi');

            // Data tambahan untuk navigasi di Flutter (opsional)
            // Contoh: {"mapel": "IPAS", "id_tugas": 5}
            $table->json('data')->nullable();

            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};