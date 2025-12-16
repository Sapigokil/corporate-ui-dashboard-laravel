<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('set_kokurikuler', function (Blueprint $table) {
            $table->bigIncrements('id_kok'); 
            $table->string('tingkat', 50); // Menyimpan tingkat kelas   
            $table->string('judul', 150);
            $table->text('deskripsi');
            $table->boolean('aktif')->default(true); // Menyimpan status aktif (1/0)
            $table->string('user'); // Menyimpan nama user atau ID user yang membuat
            $table->timestamps(); // Menghasilkan created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('set_kokurikuler');
    }
};