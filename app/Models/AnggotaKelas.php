<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaKelas extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'anggota_kelas';

    // Primary key tabel
    protected $primaryKey = 'id_anggota';

    // Tabel tidak memiliki kolom timestamps (created_at, updated_at)
    public $timestamps = false;

    // Kolom yang bisa diisi (fillable)
    protected $fillable = [
        'id_kelas',
        'nisn',
        'nama_siswa',
    ];

    /**
     * Relasi ke tabel kelas
     * Setiap anggota kelas memiliki satu kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }
}
