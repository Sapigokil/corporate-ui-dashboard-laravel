<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan', function (Blueprint $table) {
            $table->id('id_catatan'); 
            
            // Kolom Foreign Key
            $table->unsignedBigInteger('id_siswa');
            $table->unsignedBigInteger('id_kelas');
            $table->unsignedBigInteger('id_ekskul')->nullable();
            
            // Data Catatan
            $table->text('konkurikuler')->nullable();
            $table->text('keterangan')->nullable(); // Digunakan oleh EkskulSiswa
            $table->integer('sakit')->default(0);
            $table->integer('ijin')->default(0);
            $table->integer('alpha')->default(0);
            $table->text('catatan_wali_kelas')->nullable();
            
            $table->timestamps(); // Model Catatan memiliki timestamps = true
        });
    }

    public function down(): void { Schema::dropIfExists('catatan'); }
};