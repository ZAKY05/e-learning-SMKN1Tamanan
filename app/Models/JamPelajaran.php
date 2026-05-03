<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamPelajaran extends Model
{
    use HasFactory;

    protected $table = 'jam_pelajaran';
    protected $primaryKey = 'id_jam';

    protected $fillable = [
        'jam_ke',
        'waktu_mulai',
        'waktu_selesai',
        'jenis',
    ];
}
