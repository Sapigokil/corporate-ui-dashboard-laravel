<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notifikasi extends Model
{
    use HasFactory;

    // Nama tabel (karena nama tabel tidak mengikuti aturan Laravel)
    protected $table = 'notifikasi';

    // Primary key
    protected $primaryKey = 'id_notifikasi';

    // Primary key auto increment (tipe int)
    public $incrementing = true;

    // Tipe data primary key
    protected $keyType = 'int';

    // Jika tidak menggunakan created_at & updated_at
    public $timestamps = false;

    // Kolom yang dapat diisi
    protected $fillable = [
        'deskripsi',
        'tanggal',
        'kategori'
    ];
}
