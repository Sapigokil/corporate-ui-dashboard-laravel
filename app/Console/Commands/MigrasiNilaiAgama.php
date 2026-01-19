<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrasiNilaiAgama extends Command
{
    protected $signature = 'nilai:migrasi-agama';
    protected $description = 'Migrasi nilai agama dari mapel master ke mapel agama sesuai siswa';

    public function handle()
    {
        $this->info('Mulai migrasi nilai agama...');

        DB::beginTransaction();

        try {
            // 1. Ambil semua mapel agama MASTER (agama_khusus NULL)
            $mapelMasterIds = DB::table('mata_pelajaran')
                ->whereNull('agama_khusus')
                ->pluck('id_mapel');

            if ($mapelMasterIds->isEmpty()) {
                $this->warn('Mapel agama master tidak ditemukan.');
                return Command::SUCCESS;
            }

            // 2. Ambil nilai akhir yang masih pakai mapel master
            $nilaiMaster = DB::table('nilai_akhir')
                ->whereIn('id_mapel', $mapelMasterIds)
                ->get();

            if ($nilaiMaster->isEmpty()) {
                $this->warn('Tidak ada nilai agama master untuk dimigrasi.');
                return Command::SUCCESS;
            }

            $counter = 0;

            foreach ($nilaiMaster as $nilai) {
                // 3. Ambil agama siswa
                $agamaSiswa = DB::table('detail_siswa')
                    ->where('id_siswa', $nilai->id_siswa)
                    ->value('agama');

                if (!$agamaSiswa) {
                    continue;
                }

                // 4. Cari mapel agama sesuai siswa
                $mapelAgama = DB::table('mata_pelajaran')
                    ->where('agama_khusus', $agamaSiswa)
                    ->first();

                if (!$mapelAgama) {
                    continue;
                }

                // 5. Insert / update nilai ke mapel agama siswa
                DB::table('nilai_akhir')->updateOrInsert(
                    [
                        'id_siswa'     => $nilai->id_siswa,
                        'id_mapel'     => $mapelAgama->id_mapel,
                        'semester'     => $nilai->semester,
                        'tahun_ajaran' => $nilai->tahun_ajaran,
                    ],
                    [
                        'id_kelas'      => $nilai->id_kelas,
                        'nilai_s1'      => $nilai->nilai_s1,
                        'nilai_s2'      => $nilai->nilai_s2,
                        'nilai_s3'      => $nilai->nilai_s3,
                        'nilai_s4'      => $nilai->nilai_s4,
                        'nilai_s5'      => $nilai->nilai_s5,
                        'rata_sumatif'  => $nilai->rata_sumatif,
                        'bobot_sumatif' => $nilai->bobot_sumatif,
                        'nilai_project' => $nilai->nilai_project,
                        'rata_project'  => $nilai->rata_project,
                        'bobot_project' => $nilai->bobot_project,
                        'nilai_akhir'   => $nilai->nilai_akhir,
                        'capaian_akhir' => $nilai->capaian_akhir,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]
                );

                $counter++;
            }

            DB::commit();
            $this->info("Migrasi selesai. Total data diproses: {$counter}");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Migrasi GAGAL: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
