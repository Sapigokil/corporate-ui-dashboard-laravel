<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id('id_mapel'); 
            
            $table->string('nama_mapel', 150)->unique();
            $table->string('nama_singkat', 50)->nullable();
            $table->string('kategori', 50)->nullable(); // Umum / Kejuruan / Pilihan / Mulok
            $table->string('urutan', 10)->nullable(); 
            
            // Kolom Foreign Key (FK) - Menggunakan unsignedBigInteger
            $table->unsignedBigInteger('id_guru')->nullable();
            $table->unsignedBigInteger('id_pembelajaran')->nullable();
            
            // $timestamps = false
        });
    }
    public function down(): void { Schema::dropIfExists('mata_pelajaran'); }
};