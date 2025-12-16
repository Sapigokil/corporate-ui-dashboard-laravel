<?php
// File: app/Exports/ProjectTemplateExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents; 
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Support\Collection;


class ProjectTemplateExport implements WithTitle, ShouldAutoSize, WithEvents
{
    protected $filters;
    protected $siswa;
    protected $kelas;
    protected $mapel;
    protected $startDataRow = 7; 

    public function __construct(array $filters, $siswa, $kelas, $mapel)
    {
        $this->filters = $filters;
        $this->siswa = $siswa; 
        $this->kelas = $kelas;
        $this->mapel = $mapel;
    }

    public function title(): string
    {
        return 'Input Nilai Project';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // --- PENGATURAN LEBAR KOLOM MANUAL ---
                // Kita tambahkan kolom NISN/ID Siswa untuk proses import
                $sheet->getColumnDimension('A')->setWidth(5);  // No
                $sheet->getColumnDimension('B')->setWidth(30); // Nama Siswa
                $sheet->getColumnDimension('C')->setWidth(15); // NISN (Hidden)
                $sheet->getColumnDimension('D')->setWidth(15); // Nilai Project
                $sheet->getColumnDimension('E')->setWidth(50); // Tujuan Pembelajaran
                
                
                // =========================================================
                // 1. TULIS HEADER TEMPLATE (Baris 1-5)
                // =========================================================
                $sheet->setCellValue('A1', 'Kelas:');
                $sheet->setCellValue('B1', $this->kelas->nama_kelas);
                
                $sheet->setCellValue('A2', 'Mata Pelajaran:');
                $sheet->setCellValue('B2', $this->mapel->nama_mapel);

                $sheet->setCellValue('A3', 'Tipe Nilai:');
                $sheet->setCellValue('B3', 'Project'); // 🛑 PERUBAHAN LABEL

                $sheet->setCellValue('A4', 'Semester:');
                $sheet->setCellValue('B4', $this->filters['semester']);
                
                $sheet->setCellValue('A5', 'Tahun Ajaran:');
                $sheet->setCellValue('B5', $this->filters['tahun_ajaran']);
                
                // Style Header Utama
                $sheet->getStyle('A1:A5')->getFont()->setBold(true);
                $sheet->getStyle('A1:B5')->getAlignment()->setHorizontal('left');
                
                
                // =========================================================
                // 2. TULIS HEADER KOLOM DATA (Baris 6)
                // Kita tambahkan kolom NISN/ID Siswa untuk Import
                // =========================================================
                $headerRow = [
                    'No', 
                    'Nama Siswa', 
                    'NISN', // 🛑 NISN sebagai kunci lookup, sembunyikan atau pindahkan jika tidak ingin ditampilkan
                    'Nilai Project', // 🛑 PERUBAHAN LABEL
                    'Tujuan Pembelajaran' // 🛑 PERUBAHAN LABEL
                ];
                
                // Tulis header di baris 6
                $sheet->fromArray($headerRow, null, 'A6');
                
                // Style Header Kolom Data
                $sheet->getStyle('A6:E6')->getFont()->setBold(true);
                $sheet->getStyle('A6:E6')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A6:E6')->getFill()
                     ->setFillType(Fill::FILL_SOLID)
                     ->getStartColor()->setARGB('FFA0A0A0'); 
                
                
                // =========================================================
                // 3. TULIS DATA SISWA (Baris 7 dan seterusnya)
                // =========================================================
                $dataSiswaArray = [];
                $i = 1;
                foreach ($this->siswa as $siswa) {
                    $dataSiswaArray[] = [
                        $i++, // Kolom A: No Urut
                        $siswa->nama_siswa, // Kolom B: Nama Siswa
                        $siswa->detail->nisn ?? '-', // Kolom C: NISN
                        null, // Kolom D: Nilai Project (Kosong)
                        null, // Kolom E: Tujuan Pembelajaran (Kosong)
                    ];
                }
                
                // Tulis semua data siswa mulai dari baris 7 (A7)
                $sheet->fromArray($dataSiswaArray, null, 'A' . $this->startDataRow, false);
                
                // Sembunyikan Kolom C (NISN) setelah diisi, karena kolom ini hanya digunakan untuk lookup saat Import
                $sheet->getColumnDimension('C')->setVisible(false);
                
                
                // =========================================================
                // 4. VALIDASI NILAI DAN DROPDOWN
                // =========================================================
                
                // --- VALIDASI NILAI PROJECT (Kolom D, Mulai Baris 7) --- 🛑 PERUBAHAN KOLOM
                $lastRow = $sheet->getHighestRow();
                if ($lastRow < $this->startDataRow) {
                    $lastRow = $this->startDataRow;
                }
                
                $validation = $sheet->getCell('D7')->getDataValidation();
                $validation->setType(DataValidation::TYPE_WHOLE);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setErrorTitle('Input Error');
                $validation->setError('Nilai harus bilangan bulat antara 0 dan 100.');
                $validation->setPromptTitle('Input Nilai');
                $validation->setPrompt('Masukkan nilai Project (0 - 100)');

                // Aplikasikan ke rentang data yang luas
                for ($r = $this->startDataRow; $r <= $lastRow + 100; $r++) {
                    $sheet->getCell('D' . $r)->setDataValidation(clone $validation);
                }
                
                // --- VALIDASI DROPDOWN (B1, B2, B4) ---
                // Logika Drodown (Kelas, Mapel, Semester) tetap sama
                $kelasList = Kelas::pluck('nama_kelas')->toArray();
                $mapelList = MataPelajaran::pluck('nama_mapel')->toArray();
                $semesterList = ['Ganjil', 'Genap'];
                $startRowDropdown = 100;
                
                $this->writeDropdownSource($sheet, 'Z', $kelasList, $startRowDropdown);
                $this->writeDropdownSource($sheet, 'AA', $mapelList, $startRowDropdown);
                $this->writeDropdownSource($sheet, 'AB', $semesterList, $startRowDropdown);

                $endRowKelas = $startRowDropdown + count($kelasList) - 1;
                $endRowMapel = $startRowDropdown + count($mapelList) - 1;
                $endRowSemester = $startRowDropdown + count($semesterList) - 1;

                $rangeKelas = '=$Z$'.$startRowDropdown.':$Z$'.$endRowKelas;
                $rangeMapel = '=$AA$'.$startRowDropdown.':$AA$'.$endRowMapel;
                $rangeSemester = '=$AB$'.$startRowDropdown.':$AB$'.$endRowSemester;
                
                // Apply Dropdown Validation (B1, B2, B4)
                $this->applyDropdownValidation($sheet, 'B1', $rangeKelas);
                $this->applyDropdownValidation($sheet, 'B2', $rangeMapel);
                $this->applyDropdownValidation($sheet, 'B4', $rangeSemester);
                
                // Sembunyikan kolom data sumber (Z, AA, AB)
                $sheet->getColumnDimension('Z')->setVisible(false);
                $sheet->getColumnDimension('AA')->setVisible(false);
                $sheet->getColumnDimension('AB')->setVisible(false);
            },
        ];
    }
    
    // Helper method for writing dropdown data (ditulis sebagai bagian dari main class)
    protected function writeDropdownSource(Worksheet $sheet, $col, array $data, $startRow)
    {
        $currentRow = $startRow;
        foreach ($data as $item) {
            $sheet->setCellValue($col . $currentRow, $item);
            $currentRow++;
        }
    }
    
    // Helper method for applying dropdown validation
    protected function applyDropdownValidation(Worksheet $sheet, $cell, $formula)
    {
        $validation = $sheet->getCell($cell)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1($formula);
    }
}