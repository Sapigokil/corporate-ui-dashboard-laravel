<?php

namespace App\Services;

use App\Models\Sumatif;
use App\Models\Project;
use App\Models\NilaiAkhir;
use App\Services\CapaianAkhirService;


class NilaiAkhirService
{
    public function generateDanSimpan(
        int $idSiswa,
        int $idKelas,
        int $idMapel,
        int $semester,
        string $tahunAjaran
    ): void {

        $sumatif = Sumatif::where([
            'id_siswa'      => $idSiswa,
            'id_kelas'      => $idKelas,
            'id_mapel'      => $idMapel,
            'semester'      => $semester,
            'tahun_ajaran'  => $tahunAjaran,
        ])->get();

        $project = Project::where([
            'id_siswa'      => $idSiswa,
            'id_kelas'      => $idKelas,
            'id_mapel'      => $idMapel,
            'semester'      => $semester,
            'tahun_ajaran'  => $tahunAjaran,
        ])->first();


        $tpSumatif = $sumatif
            ->filter(fn ($s) => $s->nilai)
            ->map(fn ($s) => [
                'nilai' => (float) $s->nilai,
                'tp' => $s->tujuan_pembelajaran
            ]);

        $tpProject = $project
            ? collect([[
                'nilai' => (float) $project->nilai,
                'tp' => $project->tujuan_pembelajaran
            ]])
            : collect();

        $semuaNilai = $tpSumatif->merge($tpProject);

        $capaian = app(CapaianAkhirService::class)
            ->generate($semuaNilai);

        NilaiAkhir::updateOrCreate(
            [
                'id_siswa' => $idSiswa,
                'id_kelas' => $idKelas,
                'id_mapel' => $idMapel,
                'semester' => $semester,
                'tahun_ajaran' => $tahunAjaran,
            ],
            ['capaian_akhir' => $capaian]
        );
    }
}