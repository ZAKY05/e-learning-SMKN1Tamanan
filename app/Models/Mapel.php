<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'mapel';
    protected $primaryKey = 'id_mapel';

    protected $fillable = [
        'nama_mapel',
        'kode_mapel',
        'jenis',
        'kategori',
        'jurusan_id',
        'jam_per_minggu',
    ];

    /**
     * Cek apakah mapel produktif (DDKV) yang diatur kaprog
     */
    public function isProduktif(): bool
    {
        return $this->kategori === 'produktif';
    }

    public function mapel(): BelongsToMany
    {
        return $this->belongsToMany(
            Student::class,
            'guru_mapel',
            'guru_id',
            'mapel_id'
        );
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id', 'id_jurusan');
    }

    public function gurus(): BelongsToMany
    {
        return $this->belongsToMany(
            Guru::class,
            'guru_mapel',
            'mapel_id',
            'guru_id'
        );
    }

    // Tambahkan method ini ke model Mapel yang sudah ada

    // Relasi ke pengajaran
    public function pengajaran()
    {
        return $this->hasMany(Pengajaran::class, 'mapel_id', 'id_mapel');
    }

    // Relasi ke bank_soal (through pengajaran)
    public function bankSoal()
    {
        return $this->hasManyThrough(BankSoal::class, Pengajaran::class, 'mapel_id', 'pengajaran_id');
    }
}
