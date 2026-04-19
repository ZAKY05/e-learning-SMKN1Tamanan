<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilai';
    protected $primaryKey = 'id_nilai';

    protected $fillable = [
        'siswa_id',
        'nilaiable_id',
        'nilaiable_type',
        'nilai',
        'catatan',
        'input_by',
    ];

    protected $casts = [
        'nilai' => 'integer',
    ];

    /**
     * Relasi polymorphic ke tugas atau ujian
     */
    public function nilaiable()
    {
        return $this->morphTo();
    }

    /**
     * Relasi ke siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'id_siswa');
    }

    /**
     * Relasi ke guru (yang input nilai)
     */
    public function inputBy()
    {
        return $this->belongsTo(Guru::class, 'input_by', 'id_guru');
    }
}
