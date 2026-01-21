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
        Schema::table('users', function (Blueprint $table) {
            // 1. Tambahkan kolom username (untuk NIP/NISN) setelah kolom name
            $table->string('username')->nullable()->unique()->after('name');

            // 2. Tambahkan kolom penghubung ke data Guru & Siswa setelah password
            $table->unsignedBigInteger('id_guru')->nullable()->after('password');
            $table->unsignedBigInteger('id_siswa')->nullable()->after('id_guru');

            // 3. Tambahkan Index agar query pencarian lebih cepat
            $table->index('id_guru');
            $table->index('id_siswa');
            
            // OPSI: Jika ingin foreign key constraint (pastikan tipe data ID di tabel guru/siswa sama, biasanya bigInteger)
            // $table->foreign('id_guru')->references('id_guru')->on('guru')->onDelete('set null');
            // $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom jika rollback
            $table->dropColumn(['username', 'id_guru', 'id_siswa']);
        });
    }
};