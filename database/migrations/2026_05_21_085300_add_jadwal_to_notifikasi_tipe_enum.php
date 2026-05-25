<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the enum to include 'jadwal'
        DB::statement("ALTER TABLE `notifikasi` MODIFY COLUMN `tipe` ENUM('presensi', 'materi', 'tugas', 'kuis', 'penilaian', 'jadwal')");
    }

    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE `notifikasi` MODIFY COLUMN `tipe` ENUM('presensi', 'materi', 'tugas', 'kuis', 'penilaian')");
    }
};
