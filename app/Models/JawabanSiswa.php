<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanSiswa extends Model
{
    use HasFactory;

    protected $table = 'jawaban_siswa';
    protected $primaryKey = 'id_jawaban';

    protected $fillable = [
        'hasil_id',
        'soal_id',
        'pilihan_id',
        'jawaban_essay',
        'is_correct',
        'poin',
        'catatan_guru',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function hasil()
    {
        return $this->belongsTo(HasilKuis::class, 'hasil_id', 'id_hasil');
    }

    public function soal()
    {
        return $this->belongsTo(SoalKuis::class, 'soal_id', 'id_soal');
    }

    public function pilihan()
    {
        return $this->belongsTo(PilihanJawaban::class, 'pilihan_id', 'id_pilihan');
    }
}
