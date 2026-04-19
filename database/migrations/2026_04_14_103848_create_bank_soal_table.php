// database/migrations/2025_01_15_000005_create_bank_soal_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bank_soal')) {
            return;
        }

        Schema::create('bank_soal', function (Blueprint $table) {
            $table->id('id_soal');
            $table->unsignedBigInteger('pengajaran_id');
            $table->enum('jenis_soal', ['pg', 'essay', 'true_false']);
            $table->text('pertanyaan');
            $table->json('opsi')->nullable(); // untuk PG: {"A":"opsi A","B":"opsi B"}
            $table->text('kunci_jawaban');
            $table->integer('skor')->default(1);
            $table->timestamps();

            $table->foreign('pengajaran_id')
                  ->references('id_pengajaran')
                  ->on('pengajaran')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_soal');
    }
};
