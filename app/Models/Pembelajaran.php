<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelajaran extends Model
{
    use HasFactory;

    protected $table = 'pembelajaran';
    protected $primaryKey = 'id_pembelajaran';
    public $timestamps = false;

    protected $fillable = [
        'id_kelas',
        'id_mapel',
        'id_guru',
    ];

    // Relasi ke tabel kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    // Relasi ke tabel mapel
    public function mapel()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    // Relasi ke tabel guru
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }
}
