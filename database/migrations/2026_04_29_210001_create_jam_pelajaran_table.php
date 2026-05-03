<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jam_pelajaran', function (Blueprint $table) {
            $table->id('id_jam');
            $table->tinyInteger('jam_ke');           // 1, 2, 3, ... 11
            $table->time('waktu_mulai');              // 07:00
            $table->time('waktu_selesai');             // 07:45
            $table->enum('jenis', ['reguler', 'istirahat'])->default('reguler');
            $table->timestamps();
        });

        // Seed default 11 jam pelajaran SMK + 2 istirahat
        DB::table('jam_pelajaran')->insert([
            ['jam_ke' => 1,  'waktu_mulai' => '07:00', 'waktu_selesai' => '07:45', 'jenis' => 'reguler', 'created_at' => now(), 'updated_at' => now()],
            ['jam_ke' => 2,  'waktu_mulai' => '07:45', 'waktu_selesai' => '08:30', 'jenis' => 'reguler', 'created_at' => now(), 'updated_at' => now()],
            ['jam_ke' => 3,  'waktu_mulai' => '08:30', 'waktu_selesai' => '09:15', 'jenis' => 'reguler', 'created_at' => now(), 'updated_at' => now()],
            ['jam_ke' => 4,  'waktu_mulai' => '09:15', 'waktu_selesai' => '10:00', 'jenis' => 'reguler', 'created_at' => now(), 'updated_at' => now()],
            ['jam_ke' => 5,  'waktu_mulai' => '10:15', 'waktu_selesai' => '11:00', 'jenis' => 'reguler', 'created_at' => now(), 'updated_at' => now()],
            ['jam_ke' => 6,  'waktu_mulai' => '11:00', 'waktu_selesai' => '11:45', 'jenis' => 'reguler', 'created_at' => now(), 'updated_at' => now()],
            ['jam_ke' => 7,  'waktu_mulai' => '12:15', 'waktu_selesai' => '13:00', 'jenis' => 'reguler', 'created_at' => now(), 'updated_at' => now()],
            ['jam_ke' => 8,  'waktu_mulai' => '13:00', 'waktu_selesai' => '13:45', 'jenis' => 'reguler', 'created_at' => now(), 'updated_at' => now()],
            ['jam_ke' => 9,  'waktu_mulai' => '13:45', 'waktu_selesai' => '14:30', 'jenis' => 'reguler', 'created_at' => now(), 'updated_at' => now()],
            ['jam_ke' => 10, 'waktu_mulai' => '14:30', 'waktu_selesai' => '15:15', 'jenis' => 'reguler', 'created_at' => now(), 'updated_at' => now()],
            ['jam_ke' => 11, 'waktu_mulai' => '15:15', 'waktu_selesai' => '16:00', 'jenis' => 'reguler', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jam_pelajaran');
    }
};
