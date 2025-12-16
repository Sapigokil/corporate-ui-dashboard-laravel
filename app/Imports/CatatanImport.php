<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Ekskul;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CatatanImport implements ToCollection, WithStartRow
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function startRow(): int { return 7; }

    public function collection(Collection $rows)
    {
        $mapEkskul = Ekskul::pluck('id_ekskul', 'nama_ekskul')->toArray();
        $semesterDB = $this->mapSemesterToInt($this->filters['semester']);

        foreach ($rows as $row) {
            if (empty($row[1])) continue;

            $siswa = Siswa::where('nama_siswa', $row[1])
                          ->where('id_kelas', $this->filters['id_kelas'])
                          ->first();

            if ($siswa) {
                // Proses penggabungan kolom ekskul dari Excel (Kolom H, K, N)
                $validIds = []; $validPreds = []; $validKets = [];
                foreach ([7, 10, 13] as $idx) {
                    if (!empty($row[$idx]) && isset($mapEkskul[$row[$idx]])) {
                        $validIds[] = $mapEkskul[$row[$idx]];
                        $validPreds[] = $row[$idx + 1] ?? '-';
                        $validKets[] = $row[$idx + 2] ?? '-';
                    }
                }

                DB::table('catatan')->updateOrInsert(
                    [
                        'id_siswa' => $siswa->id_siswa,
                        'id_kelas' => $this->filters['id_kelas'],
                        'tahun_ajaran' => $this->filters['tahun_ajaran'],
                        'semester' => $semesterDB,
                    ],
                    [
                        'sakit' => $row[2] ?? 0,
                        'ijin' => $row[3] ?? 0,
                        'alpha' => $row[4] ?? 0,
                        'kokurikuler' => $row[5] ?? '-',
                        'catatan_wali_kelas' => $row[6] ?? '-',
                        'ekskul' => implode(',', $validIds),
                        'predikat' => implode(',', $validPreds),
                        'keterangan' => implode(' | ', $validKets),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function mapSemesterToInt(string $semester): int
    {
        return (strtoupper($semester) === 'GANJIL') ? 1 : 2;
    }
}