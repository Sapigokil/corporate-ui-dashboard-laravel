<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'event';
    protected $primaryKey = 'id_event';
    public $timestamps = false; // Karena tidak ada created_at dan updated_at

    protected $fillable = [
        'deskripsi',
        'tanggal',
        'kategori',
    ];

    /**
     * Contoh accessor (opsional)
     * Format tanggal event agar lebih mudah dibaca
     */
    public function getFormattedTanggalAttribute()
    {
        return \Carbon\Carbon::parse($this->tanggal)->translatedFormat('d F Y');
    }
}
