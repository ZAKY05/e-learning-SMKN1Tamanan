<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banklokasi extends Model
{
    protected $table = 'bank_lokasi';
    public $timestamps = false;

    protected $fillable = [
        'nama_lokasi',
        'latitude',
        'longitude',
        'radius',
        'alamat'
    ];

}
