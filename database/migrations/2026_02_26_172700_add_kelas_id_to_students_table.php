<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        if (!Schema::hasTable('student')) {
            return;
        }

        Schema::table('student', function (Blueprint $table) {
            if (Schema::hasColumn('student', 'kelas')) {
                $table->dropColumn('kelas');
            }

            if (!Schema::hasColumn('student', 'kelas_id')) {
                $table->unsignedInteger('kelas_id')->nullable()->after('jurusan_id');
                
                $table->foreign('kelas_id')
                    ->references('id_kelas')
                    ->on('kelas')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('student')) {
            return;
        }

        Schema::table('student', function (Blueprint $table) {
            if (Schema::hasColumn('student', 'kelas_id')) {
                $table->dropForeign(['kelas_id']);
                $table->dropColumn('kelas_id');
            }

            if (!Schema::hasColumn('student', 'kelas')) {
                $table->integer('kelas')->nullable()->after('jurusan_id');
            }
        });
    }
};
