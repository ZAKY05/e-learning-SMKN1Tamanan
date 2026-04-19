<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        if (Schema::hasTable('guru')) {
            return;
        }

        Schema::create('guru', function (Blueprint $table) {
            $table->bigIncrements('id_guru');
            $table->string('nip', 20)->unique();
            $table->string('nama', 50);
            $table->string('no_telp', 15)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto_profil', 255)->nullable();
            $table->timestamps();
        });

        // Tambahkan FK guru_id di tabel users (kolom sudah ada dari migration sebelumnya)
        if (Schema::hasColumn('users', 'guru_id') && !$this->hasForeignKey('users', 'guru_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('guru_id')
                    ->references('id_guru')
                    ->on('guru')
                    ->onDelete('cascade');
            });
        }
    }

    private function hasForeignKey(string $tableName, string $columnName): bool
    {
        try {
            $foreignKeys = Schema::getForeignKeys($tableName);
            foreach ($foreignKeys as $fk) {
                if (in_array($columnName, $fk['columns'])) {
                    return true;
                }
            }
        }
        catch (\Exception $e) {
        // Fallback: assume no FK
        }
        return false;
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'guru_id')) {
            Schema::table('users', function (Blueprint $table) {
                try {
                    $table->dropForeign(['guru_id']);
                }
                catch (\Exception $e) {
                // FK might not exist
                }
            });
        }
        Schema::dropIfExists('guru');
    }
};
