<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuruKode extends Model
{
    use HasFactory;

    protected $table = 'guru_kode';

    protected $fillable = [
        'guru_id',
        'kode',
        'tahun_ajaran',
        'semester',
    ];

    /**
     * Relasi ke guru
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id', 'id_guru');
    }
}
