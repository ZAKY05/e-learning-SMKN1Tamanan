<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_jadwal', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_ajaran', 9);           // 2025/2026
            $table->enum('semester', ['ganjil', 'genap']);
            $table->tinyInteger('total_jam_per_minggu')->default(48);
            $table->tinyInteger('jam_mulok_tambahan')->default(2);
            $table->tinyInteger('jam_senin')->default(10);
            $table->tinyInteger('jam_selasa')->default(10);
            $table->tinyInteger('jam_rabu')->default(10);
            $table->tinyInteger('jam_kamis')->default(10);
            $table->tinyInteger('jam_jumat')->default(8);
            $table->time('waktu_mulai')->default('07:00');
            $table->tinyInteger('durasi_jam_menit')->default(45);
            $table->json('slot_khusus')->nullable();      // breaks, upacara, sholat config
            $table->timestamps();

            $table->unique(['tahun_ajaran', 'semester'], 'setting_jadwal_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_jadwal');
    }
};
