<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapor', function (Blueprint $table) {
            $table->id('id_rapor'); 
            
            // Kolom Foreign Key yang tetap merujuk ke tabel yang ada
            $table->unsignedBigInteger('id_kelas'); 
            $table->unsignedBigInteger('id_mapel'); 
            $table->unsignedBigInteger('id_siswa'); 
            
            // PERUBAHAN: id_tahun_ajaran menjadi VARCHAR (string)
            $table->string('id_tahun_ajaran', 50); // Tipe string untuk menyimpan "2024/2025" atau ID string
            
            // Data Rapor
            $table->float('nilai')->default(0); 
            $table->text('capaian')->nullable();
            $table->enum('semester', ['Ganjil', 'Genap']);
            
            // Menjamin hanya ada satu nilai rapor untuk kombinasi Siswa-Mapel-TahunAjaran-Semester
            $table->unique(['id_siswa', 'id_mapel', 'id_tahun_ajaran', 'semester'], 'rapor_unique_key');
            
            // Model Rapor tidak memiliki timestamps
        });
    }
    public function down(): void { Schema::dropIfExists('rapor'); }
};