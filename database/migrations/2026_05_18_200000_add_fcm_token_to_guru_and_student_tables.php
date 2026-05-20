<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom fcm_token pada tabel guru dan student
     * untuk mendukung push notification via Firebase Cloud Messaging (FCM)
     * di aplikasi mobile Flutter.
     */
    public function up(): void
    {
        // Tambah fcm_token ke tabel guru
        if (!Schema::hasColumn('guru', 'fcm_token')) {
            Schema::table('guru', function (Blueprint $table) {
                $table->string('fcm_token', 512)->nullable()->after('foto_profil')
                    ->comment('Firebase Cloud Messaging token untuk push notification');
            });
        }

        // Tambah fcm_token ke tabel student (siswa/pelajar)
        if (!Schema::hasColumn('student', 'fcm_token')) {
            Schema::table('student', function (Blueprint $table) {
                $table->string('fcm_token', 512)->nullable()->after('foto_profil')
                    ->comment('Firebase Cloud Messaging token untuk push notification');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('guru', 'fcm_token')) {
            Schema::table('guru', function (Blueprint $table) {
                $table->dropColumn('fcm_token');
            });
        }

        if (Schema::hasColumn('student', 'fcm_token')) {
            Schema::table('student', function (Blueprint $table) {
                $table->dropColumn('fcm_token');
            });
        }
    }
};
