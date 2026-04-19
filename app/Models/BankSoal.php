<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSoal extends Model
{
    use HasFactory;

    protected $table = 'bank_soal';
    protected $primaryKey = 'id_soal';

    protected $fillable = [
        'pengajaran_id',
        'jenis_soal',
        'pertanyaan',
        'opsi',
        'kunci_jawaban',
        'skor',
    ];

    protected $casts = [
        'opsi' => 'array', // auto cast JSON ke array
        'skor' => 'integer',
    ];

    /**
     * Relasi ke pengajaran
     */
    public function pengajaran()
    {
        return $this->belongsTo(Pengajaran::class, 'pengajaran_id', 'id_pengajaran');
    }

    /**
     * Relasi ke ujian_soal (pivot)
     */
    public function ujianSoal()
    {
        return $this->hasMany(UjianSoal::class, 'soal_id', 'id_soal');
    }

    /**
     * Relasi ke ujian (through pivot)
     */
    public function ujian()
    {
        return $this->belongsToMany(Ujian::class, 'ujian_soal', 'soal_id', 'ujian_id');
    }

    /**
     * Relasi ke jawaban siswa
     */
    public function jawabanSiswa()
    {
        return $this->hasMany(UjianJawaban::class, 'soal_id', 'id_soal');
    }

    /**
     * Scope: filter by jenis soal
     */
    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenis_soal', $jenis);
    }
}
