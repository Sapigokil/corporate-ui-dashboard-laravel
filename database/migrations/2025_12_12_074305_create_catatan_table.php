<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catatan', function (Blueprint $table) {
            // 1. Primary Key
            $table->bigIncrements('id_catatan'); 

            // 2. Relasi (Menggunakan bigint UNSIGNED sesuai gambar)
            $table->unsignedBigInteger('id_siswa');
            $table->unsignedBigInteger('id_kelas');
            // $table->unsignedBigInteger('id_ekskul')->nullable();

            // 3. Konten Teks (Type: Text)
            $table->text('kokurikuler')->nullable();
            $table->text('ekskul')->nullable();
            $table->text('predikat')->nullable();
            $table->text('keterangan')->nullable();
            $table->text('catatan_wali_kelas')->nullable();

            // 4. Absensi (Type: Int(11), Default: 0)
            $table->integer('sakit')->default(0);
            $table->integer('ijin')->default(0);
            $table->integer('alpha')->default(0);

            // 5. Identitas Akademik
            $table->string('tahun_ajaran', 40);
            $table->integer('semester'); // Sesuai gambar: int(11)

            // 6. Timestamps
            $table->timestamps(); // Menghasilkan created_at & updated_at
        });
    }

    public function down(): void { Schema::dropIfExists('catatan'); }
};