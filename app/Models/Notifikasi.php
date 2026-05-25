<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';

    protected $fillable = [
        'user_id',
        'tipe',
        'judul',
        'isi',
        'data',
        'is_read',
    ];

     protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}