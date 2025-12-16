<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('status_rapor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa', 'id_siswa')->onDelete('cascade');
            $table->foreignId('id_kelas')->constrained('kelas', 'id_kelas')->onDelete('cascade');
            $table->integer('semester'); // 1 atau 2
            $table->string('tahun_ajaran', 10); // Contoh: 2025/2026
            
            // Kolom kontrol kelengkapan
            $table->integer('total_mapel_seharusnya')->default(0);
            $table->integer('mapel_tuntas_input')->default(0); // Jumlah mapel yang sudah penuhi 2+1
            $table->boolean('is_catatan_wali_ready')->default(false); // Status input wali kelas
            
            // Status Final
            $table->enum('status_akhir', ['Belum Lengkap', 'Siap Cetak'])->default('Belum Lengkap');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_rapors');
    }
};
