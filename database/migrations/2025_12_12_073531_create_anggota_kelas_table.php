<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota_kelas', function (Blueprint $table) {
            $table->id('id_anggota'); // Primary Key
            
            // Kolom Foreign Key (FK) - Merujuk ke Primary Key 'id_kelas' di tabel 'kelas'
            // Kita gunakan unsignedBigInteger() di sini, TANPA constraint.
            $table->unsignedBigInteger('id_kelas'); 
            
            $table->string('nisn', 15)->unique(); 
            $table->string('nama_siswa', 150);
            
            // $timestamps = false
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota_kelas');
    }
};