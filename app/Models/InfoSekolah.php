<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoSekolah extends Model
{
    use HasFactory;

    protected $table = 'info_sekolah';
    protected $primaryKey = 'id_infosekolah';
    public $timestamps = false;

    protected $fillable = [
        'nama_sekolah',
        'jenjang',
        'nisn',
        'npsn',
        'jalan',
        'kelurahan',
        'kecamatan',
        'kota_kab',
        'provinsi',
        'kode_pos',
        'email',
        'telp_fax',
        'website',
        'nama_kepsek',
        'nip_kepsek',
    ];

    /**
     * Accessor untuk menampilkan nama sekolah dalam huruf besar
     */
    public function getNamaSekolahUpperAttribute()
    {
        return strtoupper($this->nama_sekolah);
    }

    /**
     * Scope untuk filter berdasarkan provinsi
     */
    public function scopeProvinsi($query, $provinsi)
    {
        return $query->where('provinsi', $provinsi);
    }
}
