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
        // 1. UPDATE TABEL HEADER (nilai_akhir_rapor)
        // Menyimpan Snapshot Identitas Siswa & Data Ekskul JSON
        Schema::table('nilai_akhir_rapor', function (Blueprint $table) {
            
            // Snapshot Identitas Siswa (Penting jika siswa dihapus/lulus)
            if (!Schema::hasColumn('nilai_akhir_rapor', 'nama_siswa_snapshot')) {
                $table->string('nama_siswa_snapshot')->nullable()->after('id_siswa');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'nisn_snapshot')) {
                $table->string('nisn_snapshot', 20)->nullable()->after('nama_siswa_snapshot');
            }
            if (!Schema::hasColumn('nilai_akhir_rapor', 'nipd_snapshot')) {
                $table->string('nipd_snapshot', 20)->nullable()->after('nisn_snapshot');
            }

            // Snapshot Fase (E/F)
            if (!Schema::hasColumn('nilai_akhir_rapor', 'fase_snapshot')) {
                $table->char('fase_snapshot', 2)->nullable()->after('tingkat');
            }

            // Snapshot Data Ekskul (JSON Lengkap: Nama + Nilai + Ket)
            // Kolom 'data_ekskul' yang lama (LongText) mungkin hanya menyimpan ID (CSV), 
            // jadi kita buat kolom baru khusus JSON agar aman.
            if (!Schema::hasColumn('nilai_akhir_rapor', 'data_ekskul_snapshot')) {
                $table->longText('data_ekskul_snapshot')->nullable()->after('data_ekskul')
                    ->comment('Menyimpan JSON lengkap nama & nilai ekskul saat generate');
            }
        });

        // 2. UPDATE TABEL DETAIL (nilai_akhir)
        // Menyimpan Snapshot Identitas Mapel & Guru
        Schema::table('nilai_akhir', function (Blueprint $table) {
            
            // Snapshot Kelas saat nilai ini dibuat
            if (!Schema::hasColumn('nilai_akhir', 'nama_kelas_snapshot')) {
                $table->string('nama_kelas_snapshot')->nullable()->after('id_kelas');
            }

            // Snapshot Identitas Mapel (Nama, Kode, Kategori)
            if (!Schema::hasColumn('nilai_akhir', 'nama_mapel_snapshot')) {
                $table->string('nama_mapel_snapshot')->nullable()->after('id_mapel');
            }
            if (!Schema::hasColumn('nilai_akhir', 'kode_mapel_snapshot')) {
                $table->string('kode_mapel_snapshot', 50)->nullable()->after('nama_mapel_snapshot');
            }
            if (!Schema::hasColumn('nilai_akhir', 'kategori_mapel_snapshot')) {
                $table->string('kategori_mapel_snapshot', 50)->nullable()->after('kode_mapel_snapshot');
            }

            // Snapshot Guru Pengampu
            if (!Schema::hasColumn('nilai_akhir', 'nama_guru_snapshot')) {
                $table->string('nama_guru_snapshot')->nullable()->after('kategori_mapel_snapshot');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_akhir_rapor', function (Blueprint $table) {
            $table->dropColumn([
                'nama_siswa_snapshot',
                'nisn_snapshot',
                'nipd_snapshot',
                'fase_snapshot',
                'data_ekskul_snapshot'
            ]);
        });

        Schema::table('nilai_akhir', function (Blueprint $table) {
            $table->dropColumn([
                'nama_kelas_snapshot',
                'nama_mapel_snapshot',
                'kode_mapel_snapshot',
                'kategori_mapel_snapshot',
                'nama_guru_snapshot'
            ]);
        });
    }
};