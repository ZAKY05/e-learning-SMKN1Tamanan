<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianJawaban extends Model
{
    use HasFactory;

    protected $table = 'ujian_jawaban';

    protected $fillable = [
        'hasil_id',
        'soal_id',
        'jawaban_siswa',
        'nilai_perolehan',
        'komentar_guru',
    ];

    protected $casts = [
        'nilai_perolehan' => 'integer',
    ];

    /**
     * Relasi ke hasil ujian
     */
    public function hasil()
    {
        return $this->belongsTo(UjianHasil::class, 'hasil_id', 'id_hasil');
    }

    /**
     * Relasi ke bank soal
     */
    public function soal()
    {
        return $this->belongsTo(BankSoal::class, 'soal_id', 'id_soal');
    }

    /**
     * Cek apakah jawaban benar (untuk PG/TrueFalse)
     */
    public function isJawabanBenar()
    {
        if ($this->soal->jenis_soal === 'essay') {
            return null; // essay tidak bisa auto check
        }
        return strtolower(trim($this->jawaban_siswa)) === strtolower(trim($this->soal->kunci_jawaban));
    }
}
