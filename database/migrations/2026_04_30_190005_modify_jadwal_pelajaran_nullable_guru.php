<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the existing unique constraint that requires guru_id
        Schema::table('jadwal_pelajaran', function (Blueprint $table) {
            $table->dropForeign(['guru_id']);
            $table->dropUnique('jadwal_guru_unik');
        });

        // Make guru_id nullable (for DDKV/produktif subjects)
        Schema::table('jadwal_pelajaran', function (Blueprint $table) {
            $table->unsignedBigInteger('guru_id')->nullable()->change();
        });

        // Recreate foreign key and unique constraint
        Schema::table('jadwal_pelajaran', function (Blueprint $table) {
            $table->foreign('guru_id')->references('id_guru')->on('guru')->onDelete('cascade');
            // Unique constraint: guru can only teach 1 class per time slot (when guru_id is not null)
            $table->unique(['guru_id', 'hari', 'jam_ke', 'tahun_ajaran', 'semester'], 'jadwal_guru_unik');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_pelajaran', function (Blueprint $table) {
            $table->dropForeign(['guru_id']);
            $table->dropUnique('jadwal_guru_unik');
        });

        Schema::table('jadwal_pelajaran', function (Blueprint $table) {
            $table->unsignedBigInteger('guru_id')->nullable(false)->change();
            $table->foreign('guru_id')->references('id_guru')->on('guru')->onDelete('cascade');
            $table->unique(['guru_id', 'hari', 'jam_ke', 'tahun_ajaran', 'semester'], 'jadwal_guru_unik');
        });
    }
};
