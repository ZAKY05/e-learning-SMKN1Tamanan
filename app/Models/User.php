<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory>, HasApiTokens */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'guru_id',
        'siswa_id',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi ke siswa
    public function siswa()
    {
        return $this->belongsTo(Student::class, 'siswa_id', 'id_siswa');
    }

    // Relasi ke guru
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id', 'id_guru');
    }

    // Helper cek role
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isGuru()
    {
        return $this->role === 'guru';
    }

    public function isSiswa()
    {
        return $this->role === 'siswa';
    }

    /**
     * Get the user's profile photo path.
     */
    public function getFotoProfilAttribute()
    {
        if ($this->isGuru()) {
            return $this->guru?->foto_profil;
        }
        if ($this->isSiswa()) {
            return $this->siswa?->foto_profil;
        }
        return null;
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute()
    {
        $fotoPath = $this->foto_profil;
        if ($fotoPath && file_exists(public_path($fotoPath))) {
            return asset($fotoPath);
        }
        return asset('Template/assets/images/avatar/1.png');
    }
}
