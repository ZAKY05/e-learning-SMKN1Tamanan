<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mapel', function (Blueprint $table) {
            $table->string('kode_mapel', 10)->nullable()->after('nama_mapel');
            $table->enum('kategori', ['umum', 'produktif', 'mulok'])->default('umum')->after('jenis');
        });
    }

    public function down(): void
    {
        Schema::table('mapel', function (Blueprint $table) {
            $table->dropColumn(['kode_mapel', 'kategori']);
        });
    }
};
