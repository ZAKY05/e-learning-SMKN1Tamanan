<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    use HasFactory;

    protected $table = 'materi';
    protected $primaryKey = 'id_materi';

    protected $fillable = [
        'pengajaran_id',
        'judul',
        'deskripsi',
        'file_path',
        'tipe_file',
        'urutan',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Relasi ke pengajaran
     */
    public function pengajaran()
    {
        return $this->belongsTo(Pengajaran::class, 'pengajaran_id', 'id_pengajaran');
    }

    /**
     * Aksesoris: URL file lengkap
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_path && $this->tipe_file !== 'link') {
            return asset('storage/' . $this->file_path);
        }
        return $this->file_path;
    }

    /**
     * Scope: hanya materi yang dipublish
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
