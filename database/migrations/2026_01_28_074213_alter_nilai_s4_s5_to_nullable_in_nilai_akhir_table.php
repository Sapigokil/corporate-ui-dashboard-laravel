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
        Schema::table('nilai_akhir', function (Blueprint $table) {
            // Mengubah kolom nilai_s4 dan nilai_s5 menjadi NULLABLE dengan tipe tetap INTEGER
            if (Schema::hasColumn('nilai_akhir', 'nilai_s4')) {
                $table->integer('nilai_s4')->nullable()->change();
            }

            if (Schema::hasColumn('nilai_akhir', 'nilai_s5')) {
                $table->integer('nilai_s5')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_akhir', function (Blueprint $table) {
            // Mengembalikan ke NOT NULL jika dilakukan rollback
            // Pastikan data yang ada di database tidak ada yang NULL sebelum melakukan rollback
            $table->integer('nilai_s4')->nullable(false)->change();
            $table->integer('nilai_s5')->nullable(false)->change();
        });
    }
};