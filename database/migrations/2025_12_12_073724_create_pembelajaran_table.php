<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelajaran', function (Blueprint $table) {
            $table->id('id_pembelajaran'); 
            
            // Kolom Foreign Key (FK) - Menggunakan unsignedBigInteger
            $table->unsignedBigInteger('id_kelas'); 
            $table->unsignedBigInteger('id_mapel'); 
            $table->unsignedBigInteger('id_guru'); 
            
            // Kolom ini biasanya harus unik (kombinasi)
            $table->unique(['id_kelas', 'id_mapel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelajaran');
    }
};