<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

// Asumsi model dan DB sudah di-load di Controller, atau kita tambahkan di sini
use App\Models\Siswa;
use App\Models\DetailSiswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class SiswaImport implements ToCollection, WithStartRow
{
    private $header = [];

    /**
     * @return int
     * Baris untuk memulai pembacaan data (baris 10)
     */
    public function startRow(): int
    {
        return 10;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        // 1. Ambil data Header dari baris 5 dan 6 (Index 4 dan 5)
        // Kita tidak bisa mengambil header dari sini karena koleksi dimulai dari baris 10.
        // Kita akan melakukan re-read header di Controller atau di Constructor.
        
        // KARENA KETERBATASAN MAATWEBSITE DENGAN HEADER 2 BARIS, KITA AKAN PINDAHKAN LOGIC HEADER KE CONTROLLER
        // Logika utama di sini adalah memproses setiap baris data.

        // Kita akan membuat header di Controller dan melewatkannya melalui constructor

        // Namun, jika kita menggunakan ToCollection, kita bisa saja tidak perlu header, 
        // karena data sudah berupa array of arrays (data mentah).

        $dataStart = 9; // Baris 10 (index 9) adalah baris data pertama.
        $count = 0;

        DB::beginTransaction();
        
        try {
            // Karena kita menggunakan ToCollection, kita mendapatkan data mentah array-of-arrays
            // Data Siswa dimulai dari baris 10 (indeks 0 dalam koleksi ini)
            foreach ($rows as $row) {
                
                // Pastikan row bukan null, SheetJS kadang menghasilkan null/kosong
                if ($row->isEmpty() || !isset($row[1]) || trim($row[1]) === '') {
                    continue; 
                }

                // Kita tidak bisa menentukan header secara langsung di sini.
                // Kita harus mengandalkan INDEX KOLOM dari CSV yang sudah ada.
                
                // --- MAPPING MENGGUNAKAN INDEX ARRAY (SAMA SEPERTI CSV ANDA) ---
                // Data mapping akan terjadi di Controller setelah kita memproses header.
                // Untuk Maatwebsite, kita biasanya melakukan logic mapping di sini.

                // Catatan: Karena kompleksitas file Anda, kita tidak bisa menggunakan ToCollection
                // karena tidak bisa memproses header 2 baris. 
                // Kita akan kembali ke Controller dan menggunakan Maatwebsite::to_array() yang lebih fleksibel.

            }

            DB::commit(); 

        } catch (\Exception $e) {
            DB::rollBack(); 
            \Log::error("Import Siswa Maatwebsite gagal: " . $e->getMessage());
            // Fatal error di sini tidak bisa dikirim kembali via JSON response
        }
    }
}