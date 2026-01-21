<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username', // BARU: NIP atau NISN
        'email',
        'password',
        'role',      // BARU: Field role dari migration terbaru
        'id_guru',  // BARU: Link ke tabel guru
        'id_siswa', // BARU: Link ke tabel siswa
        'phone',    // Sisa dari migrate lama (tetap dipertahankan)
        'location', // Sisa dari migrate lama
        'about',    // Sisa dari migrate lama
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ==========================================
    // RELASI KE MODEL LAIN
    // ==========================================

    /**
     * Relasi ke Guru (Optional/Nullable)
     * $user->guru->nama_guru
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    /**
     * Relasi ke Siswa (Optional/Nullable)
     * $user->siswa->nama_siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }
}