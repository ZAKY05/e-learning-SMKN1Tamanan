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
        Schema::create('guru_mapel', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('guru_id');
            $table->unsignedInteger('mapel_id');
            $table->timestamps();

            $table->foreign('guru_id')
                  ->references('id_guru')
                  ->on('guru')
                  ->onDelete('cascade');
            $table->foreign('mapel_id')
                ->references('id_mapel')
                ->on('mapel')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guru_mapel');
    }
};
