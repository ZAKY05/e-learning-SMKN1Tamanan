<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory as FactoriesHasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'jurusan';
    protected $primaryKey = 'id_jurusan';

    protected $fillable = [
        'nama_jurusan',
        'deskripsi',
    ];

    public function students()
    {
        return $this->hasMany(Pelajar::class , 'jurusan_id', 'id_jurusan');
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class , 'jurusan_id', 'id_jurusan');
    }
}
