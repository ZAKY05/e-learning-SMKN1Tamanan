<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_kode', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guru_id');
            $table->string('kode', 5);
            $table->string('tahun_ajaran', 9);
            $table->enum('semester', ['ganjil', 'genap']);
            $table->timestamps();

            $table->foreign('guru_id')->references('id_guru')->on('guru')->onDelete('cascade');
            $table->unique(['guru_id', 'tahun_ajaran', 'semester'], 'guru_kode_guru_unik');
            $table->unique(['kode', 'tahun_ajaran', 'semester'], 'guru_kode_kode_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_kode');
    }
};
