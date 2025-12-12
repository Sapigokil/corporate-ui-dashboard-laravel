<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id('id_kelas'); 
            
            $table->string('nama_kelas', 100)->unique(); 
            $table->string('tingkat', 50); 
            $table->string('jurusan', 150)->nullable();
            
            $table->string('wali_kelas', 100)->nullable(); 
            $table->integer('jumlah_siswa')->default(0);

            // Kolom Foreign Key (FK) - Menggunakan tipe data yang sama dengan Primary Key di tabel 'guru'
            // Primary Key di 'guru' dibuat dengan $table->id('id_guru'), yang menghasilkan BIGINT UNSIGNED
            // Kita gunakan unsignedBigInteger() di sini, TANPA constraint.
            $table->unsignedBigInteger('id_guru')->nullable(); 
            
            // Kolom id_anggota dihilangkan karena tidak sesuai dengan konvensi relasi hasMany (AnggotaKelas)
            // Relasi many-to-one/hasMany tidak memerlukan FK di tabel induk.
            
            // $timestamps = false
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};