<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ekskul extends Model
{
    use HasFactory;

    protected $table = 'ekskul';
    protected $primaryKey = 'id_ekskul';
    public $timestamps = false;

    protected $fillable = ['nama_ekskul', 'jadwal_ekskul', 'id_guru'];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function siswaEkskul()
    {
        return $this->hasMany(EkskulSiswa::class, 'id_ekskul', 'id_ekskul');
    }
}

