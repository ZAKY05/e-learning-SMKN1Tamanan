<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianSoal extends Model
{
    use HasFactory;

    protected $table = 'ujian_soal';

    protected $fillable = [
        'ujian_id',
        'soal_id',
        'urutan',
    ];

    /**
     * Relasi ke ujian
     */
    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id', 'id_ujian');
    }

    /**
     * Relasi ke bank soal
     */
    public function soal()
    {
        return $this->belongsTo(BankSoal::class, 'soal_id', 'id_soal');
    }
}
