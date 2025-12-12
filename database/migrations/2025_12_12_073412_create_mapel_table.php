<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapel', function (Blueprint $table) {
            $table->id('id_mapel'); 
            $table->string('nama_mapel', 150)->unique();
            $table->string('kelompok_mapel', 50)->nullable(); 
            $table->integer('jam_pelajaran')->default(0); 
            
            // $timestamps = false
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapel');
    }
};