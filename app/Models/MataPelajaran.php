<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';
    protected $primaryKey = 'id_mapel';
    public $timestamps = false;

    protected $fillable = [
        'nama_mapel',
        'nama_singkat',
        'kategori',      // Umum / Kejuruan / Pilihan / Mulok
        'urutan',        // nomor urut tampilan rapor
        'id_guru',
        'id_pembelajaran',
        'agama_khusus',
        'is_active'
    ];

    /**
     * Relasi ke Guru
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    /**
     * Relasi ke Pembelajaran
     */
    public function pembelajaran()
    {
        return $this->belongsTo(Pembelajaran::class, 'id_pembelajaran', 'id_pembelajaran');
    }

    /**
     * Scope: Grup berdasarkan kelompok (A–D)
     */
    public function scopeKelompok($query, $kelompok)
    {
        return $query->where('kelompok', $kelompok)->orderBy('urutan');
    }
}
