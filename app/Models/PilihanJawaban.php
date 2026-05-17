<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilihanJawaban extends Model
{
    use HasFactory;

    protected $table = 'pilihan_jawaban';
    protected $primaryKey = 'id_pilihan';

    protected $fillable = [
        'soal_id',
        'pilihan',
        'isi_pilihan',
        'gambar_pilihan',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function soal()
    {
        return $this->belongsTo(SoalKuis::class, 'soal_id', 'id_soal');
    }
}
