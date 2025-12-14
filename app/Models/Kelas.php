<?php
// File: app/Models/Kelas.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AnggotaKelas;
use App\Models\Siswa;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';
    public $timestamps = false;

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan',
        'wali_kelas',
        'jumlah_siswa',
        'id_anggota',
        'id_guru',
    ];

    /**
     * Relasi ke tabel Guru (Many to One)
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'id_kelas', 'id_kelas');
    }

    /**
     * Relasi opsional ke tabel Anggota (jika ada)
     */
    public function anggotaKelas()
    {
        return $this->hasMany(AnggotaKelas::class, 'id_kelas', 'id_kelas');
    }

    /**
     * Scope untuk filter berdasarkan jurusan
     */
    public function scopeJurusan($query, $jurusan)
    {
        return $query->where('jurusan', $jurusan);
    }
}
