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
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            // Ubah tipe data menjadi Integer
            $table->integer('kategori')->change();
            $table->integer('urutan')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            // Kembalikan ke String/Varchar jika di-rollback
            $table->string('kategori')->change();
            $table->string('urutan')->change();
        });
    }
};