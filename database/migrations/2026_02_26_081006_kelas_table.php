<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{

    public function up(): void
{
    Schema::create('kelas', function (Blueprint $table) {

        $table->id('id_kelas');

        $table->tinyInteger('tingkat'); 
        $table->unsignedInteger('jurusan_id');
        $table->string('golongan', 5);

        $table->timestamps();

        $table->foreign('jurusan_id')
              ->references('id_jurusan')
              ->on('jurusan')
              ->onDelete('cascade');
    });
}


    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
