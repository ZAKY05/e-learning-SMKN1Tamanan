<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_lokasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lokasi');
            $table->string('latitude', 10, 8);
            $table->string('longitude', 11, 8);
            $table->integer('radius')->default(10);
            $table->text('alamat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_lokasi');
    }
};
