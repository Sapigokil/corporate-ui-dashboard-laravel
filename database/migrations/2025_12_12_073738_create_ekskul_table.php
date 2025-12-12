<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekskul', function (Blueprint $table) {
            $table->id('id_ekskul'); 
            
            $table->string('nama_ekskul', 100);
            $table->string('jadwal_ekskul', 100)->nullable();
            
            // Kolom Foreign Key (FK) - Merujuk ke 'id_guru'
            $table->unsignedBigInteger('id_guru')->nullable();
            
            // $timestamps = false
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul');
    }
};