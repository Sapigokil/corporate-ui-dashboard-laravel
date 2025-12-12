<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekskul_siswa', function (Blueprint $table) {
            $table->id(); 
            
            // Kolom Foreign Key (FK)
            $table->unsignedBigInteger('id_siswa');
            $table->unsignedBigInteger('id_ekskul');
            $table->unsignedBigInteger('id_catatan')->nullable(); // Asumsi: FK ke tabel Catatan
            
            $table->text('keterangan')->nullable(); // Nilai atau deskripsi ekskul
            
            // Memastikan siswa hanya terdaftar sekali di ekskul tertentu
            $table->unique(['id_siswa', 'id_ekskul']); 

            // $timestamps = false
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_siswa');
    }
};