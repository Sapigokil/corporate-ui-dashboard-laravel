<?php

namespace App\Services;

use Illuminate\Support\Collection;

class CapaianAkhirService
{
    public function generate(Collection $semuaNilai): ?string
    {
        if ($semuaNilai->isEmpty()) {
            return null;
        }

        // 🔥 Batasi maksimal 2 TP
        if ($semuaNilai->count() > 2) {
            $low  = $semuaNilai->sortBy('nilai')->first();
            $high = $semuaNilai->sortByDesc('nilai')->first();

            $semuaNilai = ($low['tp'] === $high['tp'])
                ? collect([$low])
                : collect([$low, $high]);
        }

        $nilaiValid = $semuaNilai
            ->filter(fn ($n) => trim((string) $n['tp']) !== '');

        if ($nilaiValid->isEmpty()) {
            return null;
        }

        $terendah  = $nilaiValid->sortBy('nilai')->first();
        $tertinggi = $nilaiValid->sortByDesc('nilai')->first();

        // =========================
        // Kasus 1: Tunggal / Sama
        // =========================
        if ($nilaiValid->count() === 1 || $terendah['nilai'] === $tertinggi['nilai']) {
            $narasi = $terendah['nilai'] > 84
                ? 'Menunjukkan penguasaan yang baik dalam hal'
                : 'Perlu penguatan dalam hal';

            return $nilaiValid->count() === 1
                ? "{$narasi} kompetensi yang dipelajari."
                : "{$narasi} {$nilaiValid->pluck('tp')->unique()->implode(', ')}.";
        }

        // =========================
        // Kasus 2: Terendah vs Tertinggi
        // =========================
        $kunciRendah = $terendah['nilai'] < 81
            ? 'Perlu peningkatan dalam hal'
            : 'Perlu penguatan dalam hal';

        $kunciTinggi = $tertinggi['nilai'] > 89
            ? 'Mahir dalam hal'
            : 'Baik dalam hal';

        return "{$kunciRendah} {$terendah['tp']}, namun menunjukkan capaian {$kunciTinggi} {$tertinggi['tp']}.";
    }
}
