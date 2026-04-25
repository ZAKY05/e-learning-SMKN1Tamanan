<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Mapel;



class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';
    protected $primaryKey = 'id_guru';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nip',
        'nama',
        'jenis_kelamin',
        'no_telp',
        'alamat',
        'foto_profil',
    ];

    /**
     * The subjects taught by the teacher.
     */
    public function mapels(): BelongsToMany
    {
        return $this->belongsToMany(
            Mapel::class,
            'guru_mapel',
            'guru_id',
            'mapel_id'
        );
    }

    // Tambahkan method ini ke model Guru yang sudah ada

    // Relasi ke user (login)
    public function user()
    {
        return $this->hasOne(User::class, 'guru_id', 'id_guru');
    }

   
}
