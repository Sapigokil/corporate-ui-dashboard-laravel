<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id('id_siswa'); // Primary Key: id_siswa (int)
            
            // --- TAMBAHKAN BARIS INI ---
            // Kolom FK ke tabel users (tanpa constraint)
            $table->unsignedBigInteger('id_user')->unique()->nullable();
            // --------------------------

            $table->string('nipd', 30)->unique();
            $table->string('nisn', 15)->unique();
            $table->string('nama_siswa', 150);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tingkat', 50)->nullable(); 
            
            // Kolom Foreign Key (FK)
            $table->unsignedBigInteger('id_kelas')->nullable();
            $table->unsignedBigInteger('id_ekskul')->nullable(); 

            // $timestamps = false
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};