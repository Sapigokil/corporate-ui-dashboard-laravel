<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilai_akhir', function (Blueprint $table) {
            $table->integer('nilai_s4')->default(0)->after('nilai_s3');
            $table->integer('nilai_s5')->default(0)->after('nilai_s4');
        });
    }

    public function down(): void
    {
        Schema::table('nilai_akhir', function (Blueprint $table) {
            $table->dropColumn(['nilai_s4', 'nilai_s5']);
        });
    }
};
