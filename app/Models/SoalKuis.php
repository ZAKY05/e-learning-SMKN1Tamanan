<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoalKuis extends Model
{
    use HasFactory;

    protected $table = 'soal_kuis';
    protected $primaryKey = 'id_soal';

    protected $fillable = [
        'kuis_id',
        'pertanyaan',
        'gambar',
        'tipe_soal',
        'poin',
        'nomor_urut',
    ];

    public function kuis()
    {
        return $this->belongsTo(Kuis::class, 'kuis_id', 'id_kuis');
    }

    public function pilihanJawaban()
    {
        return $this->hasMany(PilihanJawaban::class, 'soal_id', 'id_soal');
    }
}
