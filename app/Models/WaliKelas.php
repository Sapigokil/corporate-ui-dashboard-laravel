<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaliKelas extends Model
{
    use HasFactory;

    protected $table = 'wali_kelas';
    protected $primaryKey = 'id_wali';
    public $timestamps = false; // Diasumsikan tidak menggunakan created_at/updated_at

    protected $fillable = [
        'id_guru',
        'id_kelas',
        'id_tahun_ajaran', // String/VARCHAR
    ];

    /**
     * Relasi ke Guru yang menjadi Wali Kelas
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    /**
     * Relasi ke Kelas yang diwalikan
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }
}