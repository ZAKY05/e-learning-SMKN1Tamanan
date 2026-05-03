<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_import_mapping', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['mapel', 'kelas']); // jenis mapping
            $table->string('singkatan', 100);           // singkatan di Excel ASC
            $table->unsignedBigInteger('target_id');     // ID mapel atau ID kelas
            $table->timestamps();
            $table->unique(['tipe', 'singkatan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_import_mapping');
    }
};
