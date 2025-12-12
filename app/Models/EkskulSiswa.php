<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulSiswa extends Model
{
    use HasFactory;

    protected $table = 'ekskul_siswa';

    protected $fillable = [
        'id_siswa',
        'id_ekskul',
        'keterangan',
        'id_catatan'
    ];

    public $timestamps = false;

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'id_ekskul', 'id_ekskul');
    }
}

