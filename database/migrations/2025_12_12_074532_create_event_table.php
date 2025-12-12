<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event', function (Blueprint $table) {
            $table->id('id_event'); 
            
            $table->text('deskripsi');
            $table->date('tanggal');
            $table->string('kategori', 50)->nullable();
            
            // $timestamps = false
        });
    }
    public function down(): void { Schema::dropIfExists('event'); }
};