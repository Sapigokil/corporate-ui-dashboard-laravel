<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class NilaiCalculator
{
    /**
     * 1. Hitung Rata-rata Sumatif
     * Aturan: Pembagi min target, Hasil akhir 1 desimal.
     */
    public static function hitungRataSumatif(Collection $sumatifData, int $targetMin): float
    {
        $nilaiValid = collect([]);
        
        // Ambil nilai S1-S5
        for ($i = 1; $i <= 5; $i++) {
            $val = $sumatifData->firstWhere('sumatif', $i)->nilai ?? null;
            if (!is_null($val) && $val !== '') {
                $nilaiValid->push((float) $val);
            }
        }

        $pembagi = max($nilaiValid->count(), $targetMin);

        if ($pembagi === 0) return 0;

        // Hitung rata-rata
        $rata = $nilaiValid->sum() / $pembagi;

        // Rounding 1 Desimal (Sesuai Request)
        return round($rata, 1);
    }

    /**
     * 2. Hitung Bobot Sumatif
     * Aturan: (Rata * Persen), Hasil akhir 1 desimal.
     */
    public static function hitungBobotSumatif(float $rataSumatif, float $persenBobot): float
    {
        if ($rataSumatif <= 0) return 0;

        $hasil = $rataSumatif * ($persenBobot / 100);

        // Rounding 1 Desimal
        return round($hasil, 1);
    }

    /**
     * 3. Hitung Bobot Project
     * Aturan: (Nilai * Persen), Hasil akhir 1 desimal.
     */
    public static function hitungBobotProject(float $nilaiProject, float $persenBobot): float
    {
        if ($nilaiProject <= 0) return 0;

        $hasil = $nilaiProject * ($persenBobot / 100);

        // Rounding 1 Desimal
        return round($hasil, 1);
    }

    /**
     * 4. Hitung Nilai Akhir
     * Aturan: Penjumlahan bobot -> Pembulatan (Rounding) -> Integer.
     */
    public static function hitungNilaiAkhir(float $bobotSumatif, float $bobotProject): int
    {
        if ($bobotSumatif <= 0 && $bobotProject <= 0) return 0;

        // Jumlahkan desimalnya dulu (misal: 40.5 + 39.4 = 79.9)
        $total = $bobotSumatif + $bobotProject;

        // Bulatkan di akhir (79.9 -> 80)
        return (int) round($total);
    }

    /**
     * MAIN FUNCTION: Wrapper untuk mempermudah Controller
     * Fungsi ini memanggil 4 fungsi di atas sekaligus.
     */
    public static function process($sumatifData, $nilaiProject, $bobotConfig)
    {
        // Ambil Config
        $targetMin = $bobotConfig->jumlah_sumatif ?? 0;
        $pSumatif  = $bobotConfig->bobot_sumatif ?? 50;
        $pProject  = $bobotConfig->bobot_project ?? 50;

        // 1. Hitung Rata Sumatif
        $rataSumatif = self::hitungRataSumatif($sumatifData, $targetMin);

        // 2. Hitung Bobot Sumatif
        $bobotSumatif = self::hitungBobotSumatif($rataSumatif, $pSumatif);

        // 3. Hitung Bobot Project
        // Pastikan nilai project float
        $nilaiProject = (float) ($nilaiProject ?? 0);
        $bobotProject = self::hitungBobotProject($nilaiProject, $pProject);

        // 4. Hitung Nilai Akhir
        $nilaiAkhir = self::hitungNilaiAkhir($bobotSumatif, $bobotProject);

        // Persiapkan Array Nilai S1-S5 untuk DB
        $s_vals = [];
        for ($i = 1; $i <= 5; $i++) {
            $s_vals['nilai_s'.$i] = $sumatifData->firstWhere('sumatif', $i)->nilai ?? null;
        }

        return [
            's_vals'        => $s_vals,
            'rata_sumatif'  => $rataSumatif,  // 1 desimal
            'bobot_sumatif' => $bobotSumatif, // 1 desimal
            'nilai_project' => $nilaiProject,
            'rata_project'  => $nilaiProject,
            'bobot_project' => $bobotProject, // 1 desimal
            'nilai_akhir'   => $nilaiAkhir    // Integer bulat
        ];
    }
}