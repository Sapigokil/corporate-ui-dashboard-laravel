<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wali_kelas', function (Blueprint $table) {
            $table->id('id_wali'); 
            
            // Kolom Foreign Key (FK) - Menggunakan unsignedBigInteger
            $table->unsignedBigInteger('id_guru'); 
            $table->unsignedBigInteger('id_kelas'); 
            
            // Kolom Tahun Ajaran (String/VARCHAR)
            $table->string('id_tahun_ajaran', 50); 
            
            // Menjamin kombinasi Guru-Kelas-Tahun Ajaran unik
            $table->unique(['id_guru', 'id_kelas', 'id_tahun_ajaran'], 'unique_wali_kelas_assignment');
            
            // $timestamps = false
        });
    }
    public function down(): void { Schema::dropIfExists('wali_kelas'); }
};