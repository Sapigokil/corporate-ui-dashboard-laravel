<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->id('id_guru'); // Primary Key: id_guru (BIGINT UNSIGNED)
            // --- TAMBAHKAN BARIS INI ---
            // Kolom FK ke tabel users (tanpa constraint)
            $table->unsignedBigInteger('id_user')->unique()->nullable(); 
            // --------------------------
            $table->string('nama_guru', 150);
            $table->string('nip', 30)->unique()->nullable(); 
            $table->string('nuptk', 30)->unique()->nullable(); 
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('jenis_ptk', 50)->nullable();
            $table->string('role', 50)->nullable(); 
            $table->enum('status', ['aktif', 'non-aktif', 'cuti'])->default('aktif');

            // $timestamps = false
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};