<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rapor extends Model
{
    protected $table = 'rapor';
    protected $primaryKey = 'id_rapor';
    public $incrementing = true; // kalau id_rapor auto increment
    protected $keyType = 'int';

    protected $fillable = [
        'id_kelas', 
        'id_mapel', 
        'id_siswa', 
        'nilai', 
        'capaian',
        'id_tahun_ajaran',
        'semester'
    ];
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function mapel()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    // public function tahunAjaran()
    // {
    //     return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran');
    // }
}