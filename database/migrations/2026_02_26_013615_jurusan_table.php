<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('jurusan', function (Blueprint $table) {
            $table->increments('id_jurusan'); // primary key manual
            $table->string('nama_jurusan', 30);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

 
    public function down(): void
    {
        Schema::dropIfExists('jurusan');
    }
};
