<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_siswa', function (Blueprint $table) {
            $table->id('id_detail'); 
            
            // Foreign Key
            $table->unsignedBigInteger('id_siswa')->unique(); 
            $table->unsignedBigInteger('id_kelas')->nullable(); // (Redundant FK, tapi dimasukkan sesuai Model)
            
            // Data Pribadi Siswa
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 150)->nullable();
            $table->string('nik', 20)->unique()->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('email', 100)->nullable();
            
            // Data Kesehatan & Fisik
            $table->string('bb')->nullable()->comment('Berat Badan');
            $table->string('tb')->nullable()->comment('Tinggi Badan');
            $table->string('lingkar_kepala', 10)->nullable();
            
            // Data Alamat Rinci
            $table->string('alamat', 255)->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('dusun', 100)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('jenis_tinggal', 50)->nullable();
            $table->string('alat_transportasi', 50)->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('lintang', 20)->nullable();
            $table->string('bujur', 20)->nullable();
            $table->string('no_kk', 20)->nullable();
            $table->string('jarak_rumah', 50)->nullable();

            // Data Akademik & Bantuan
            $table->string('sekolah_asal', 150)->nullable();
            $table->string('skhun', 50)->nullable();
            $table->string('rombel', 50)->nullable();
            $table->string('no_peserta_ujian_nasional', 50)->nullable();
            $table->string('no_seri_ijazah', 50)->nullable();
            $table->string('no_regis_akta_lahir', 50)->nullable();
            
            // Data Bantuan
            $table->string('penerima_kps', 50)->nullable();
            $table->string('no_kps', 50)->nullable();
            $table->string('penerima_kip', 50)->nullable();
            $table->string('no_kip', 50)->nullable();
            $table->string('nama_kip', 150)->nullable();
            $table->string('no_kks', 50)->nullable();
            $table->string('layak_pip_usulan', 50)->nullable();
            $table->string('alasan_layak_pip', 100)->nullable();
            $table->string('kebutuhan_khusus', 100)->nullable();

            // Data Keuangan
            $table->string('bank', 50)->nullable();
            $table->string('no_rek_bank', 50)->nullable();
            $table->string('rek_atas_nama', 150)->nullable();

            // Data Keluarga & Wali
            $table->string('anak_ke_berapa', 50)->nullable();
            $table->string('jml_saudara_kandung', 50)->nullable();
            
            // Data Ayah
            $table->string('nama_ayah', 150)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('tahun_lahir_ayah', 4)->nullable();
            $table->string('jenjang_pendidikan_ayah', 50)->nullable();
            $table->string('penghasilan_ayah', 50)->nullable();
            $table->string('nik_ayah', 20)->nullable();

            // Data Ibu
            $table->string('nama_ibu', 150)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->string('tahun_lahir_ibu', 4)->nullable();
            $table->string('jenjang_pendidikan_ibu', 50)->nullable();
            $table->string('penghasilan_ibu', 50)->nullable();
            $table->string('nik_ibu', 20)->nullable();

            // Data Wali
            $table->string('nama_wali', 150)->nullable();
            $table->string('pekerjaan_wali', 100)->nullable();
            $table->string('tahun_lahir_wali', 4)->nullable();
            $table->string('jenjang_pendidikan_wali', 50)->nullable();
            $table->string('penghasilan_wali', 50)->nullable();
            $table->string('nik_wali', 20)->nullable();

            // $timestamps = false
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_siswa');
    }
};