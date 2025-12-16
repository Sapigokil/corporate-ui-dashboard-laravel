<?php
// File: app/Imports/ProjectImport.php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Project; // 🛑 PENTING: Gunakan Model Project
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectImport implements ToCollection, WithHeadingRow, SkipsUnknownSheets
{
    use Importable;
    
    protected $filters;
    protected $storedCount = 0;
    protected $skippedCount = 0;
    protected $siswaCache;
    
    // Data mapping semester (INT: 1=Ganjil, 2=Genap)
    protected $semesterMap = [
        'GANJIL' => 1,
        'GENAP' => 2,
    ];

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        
        // Cache Siswa berdasarkan Nama (metode pencocokan yang sama dengan Sumatif)
        $this->siswaCache = Siswa::where('id_kelas', $filters['id_kelas'])
            ->get()
            ->keyBy(function ($siswa) {
                return strtoupper(trim($siswa->nama_siswa));
            });
    }

    public function onUnknownSheet($sheetName)
    {
        // Abaikan sheet yang tidak terduga
    }
    
    public function collection(Collection $rows)
    {
        $dataToStore = [];
        $filterData = $this->filters;

        Log::info('Project Import Started', [
            'filters' => $filterData, 
            'rows_count' => $rows->count(),
        ]);

        // MAPPING SEMESTER 
        $semesterString = strtoupper($filterData['semester']);
        $semesterInt = $this->semesterMap[$semesterString] ?? null;

        if (is_null($semesterInt)) {
            throw new \Exception("Nilai semester ('".$filterData['semester']."') tidak valid untuk database (harus Ganjil/Genap).");
        }


        foreach ($rows as $row) {
            
            // 🛑 PENTING: Heading Row dari Excel Project Template: 'nilai_project' dan 'tujuan_pembelajaran'
            $excelNamaSiswa = trim($row['nama_siswa'] ?? null);
            $excelNilai = (int) ($row['nilai_project'] ?? null); // 🛑 KUNCI: nilai_project
            $excelTujuanPembelajaran = trim($row['tujuan_pembelajaran'] ?? null);
            
            // 1. Validasi Nilai & Nama Siswa Kosong
            if (empty($excelNamaSiswa) || empty($excelNilai) || $excelNilai < 0 || $excelNilai > 100) {
                $this->skippedCount++;
                continue;
            }

            // 2. Pencocokan Nama Siswa
            $upperNamaSiswa = strtoupper($excelNamaSiswa);
            $siswaMatch = $this->siswaCache->get($upperNamaSiswa);

            if (!$siswaMatch) {
                $this->skippedCount++;
                Log::warning('Import Skipped: Siswa not found in cache for current Class filter', ['name' => $excelNamaSiswa, 'upper_name' => $upperNamaSiswa]);
                continue;
            }
            
            // Hitung nilai bobot (60%)
            $nilaiBobot = round($excelNilai * 0.6, 2);

            // 3. Persiapan Data Store
            $dataToStore[] = [
                'id_siswa' => $siswaMatch->id_siswa,
                'id_kelas' => $filterData['id_kelas'],
                'id_mapel' => $filterData['id_mapel'],
                // 🛑 Project TIDAK memiliki 'sumatif', tetapi di ProjectController kita menggunakan ProjectModel
                // 'sumatif' tidak diperlukan di sini.
                'tahun_ajaran' => $filterData['tahun_ajaran'],
                
                'semester' => $semesterInt, 
                'nilai' => $excelNilai,
                'nilai_bobot' => $nilaiBobot, // 🛑 BARU: Nilai Bobot untuk Project
                'tujuan_pembelajaran' => $excelTujuanPembelajaran ?: 'Diimport',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        } // End Foreach Rows

        // 4. Proses Penyimpanan (UpdateOrCreate)
        DB::beginTransaction();
        try {
            $currentStoredCount = 0;
            foreach ($dataToStore as $data) {
                // Kunci unik Project: (id_siswa, id_mapel, semester, tahun_ajaran)
                Project::updateOrCreate( // 🛑 PENTING: Gunakan Project::updateOrCreate
                    [
                        'id_siswa' => $data['id_siswa'],
                        'id_mapel' => $data['id_mapel'],
                        'semester' => $data['semester'], 
                        'tahun_ajaran' => $data['tahun_ajaran'],
                    ],
                    [
                        'id_kelas' => $data['id_kelas'],
                        'nilai' => $data['nilai'],
                        'nilai_bobot' => $data['nilai_bobot'],
                        'tujuan_pembelajaran' => $data['tujuan_pembelajaran'],
                    ]
                );
                $currentStoredCount++; 
            }
            
            $this->storedCount = $currentStoredCount;
            DB::commit();
            
            Log::info('Project Import Success (Transaction Committed)', ['stored' => $this->storedCount, 'skipped' => $this->skippedCount]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Database Storage Failed', ['error' => $e->getMessage()]);
            throw new \Exception("Penyimpanan data Project ke database gagal: " . $e->getMessage());
        }
    }
    
    // Heading Row di baris 6, sama dengan template Sumatif
    public function headingRow(): int
    {
        return 6; 
    }

    public function getStoredCount(): int
    {
        return $this->storedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}