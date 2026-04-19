// database/migrations/2025_01_15_000007_create_ujian_soal_table.php sebagai pivot untuk ujian
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ujian_soal')) {
            return;
        }

        Schema::create('ujian_soal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ujian_id');
            $table->unsignedBigInteger('soal_id');
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->foreign('ujian_id')
                  ->references('id_ujian')
                  ->on('ujian')
                  ->onDelete('cascade');

            $table->foreign('soal_id')
                  ->references('id_soal')
                  ->on('bank_soal')
                  ->onDelete('cascade');

            $table->unique(['ujian_id', 'soal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_soal');
    }
};
