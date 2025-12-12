<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan_rapor', function (Blueprint $table) {
            $table->id('id_catatan'); 
            
            // Kolom Foreign Key
            $table->unsignedBigInteger('id_kelas');
            $table->unsignedBigInteger('id_siswa');
            
            // Data
            $table->text('kokurikuler')->nullable();
            $table->json('ekskul')->nullable(); // Disimpan sebagai JSON
            
            // Model CatatanRapor tidak memiliki timestamps
        });
    }
    public function down(): void { Schema::dropIfExists('catatan_rapor'); }
};