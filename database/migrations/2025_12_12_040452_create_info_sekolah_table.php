<?php
// database/migrations/..._create_info_sekolah_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_sekolah', function (Blueprint $table) {
            $table->id('id_infosekolah'); // Sesuai primaryKey di model
            $table->string('nama_sekolah', 150);
            $table->string('jenjang', 50)->nullable();
            $table->string('nisn', 15)->nullable();
            $table->string('npsn', 15)->unique(); // NPSN biasanya unik
            $table->string('jalan')->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kota_kab', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('telp_fax', 50)->nullable();
            $table->string('website', 100)->nullable();
            $table->string('nama_kepsek', 150)->nullable();
            $table->string('nip_kepsek', 30)->nullable();
            
            // $table->timestamps(); // Diabaikan karena model set $timestamps = false;
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_sekolah');
    }
};