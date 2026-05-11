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
        // Ubah enum status_kehadiran untuk menambah 'terlambat'
        DB::statement("ALTER TABLE detail_presensi MODIFY COLUMN status_kehadiran ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alpha') DEFAULT 'alpha'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE detail_presensi MODIFY COLUMN status_kehadiran ENUM('hadir', 'izin', 'sakit', 'alpha') DEFAULT 'alpha'");
    }
};
