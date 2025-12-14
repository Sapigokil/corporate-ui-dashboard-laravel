<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\DetailGuru;
use App\Models\Pembelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel; // Digunakan untuk import Excel
use Carbon\Carbon; // Digunakan untuk konversi tanggal
use PhpOffice\PhpSpreadsheet\Shared\Date; // Digunakan untuk konversi tanggal Excel

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $query = Guru::query();

        // Terapkan logika pencarian jika parameter 'search' ada
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            
            // Mencari nama guru, NIP, atau NUPTK
            $query->where(function ($q) use ($search) {
                $q->where('nama_guru', 'like', '%' . $search . '%')
                  ->orWhere('nip', 'like', '%' . $search . '%')
                  ->orWhere('nuptk', 'like', '%' . $search . '%');
            });
        }
        
        // Terapkan Pagination dan ambil hasil
        $gurus = $query->paginate(20)->withQueryString(); 

        return view('guru.index', compact('gurus'));
    }

    public function create()
    {
        // Ambil data Kelas dan Mata Pelajaran dari database
        $guru = new \App\Models\Guru(); 
        $detail = new \App\Models\DetailGuru();
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $mapelList = MataPelajaran::orderBy('nama_mapel')->get(); 

        // Kirim kedua variabel tersebut ke view
        return view('guru.create', compact('guru', 'detail', 'kelasList', 'mapelList'));
    }

    public function show($id)
    {
        // Memuat Model Guru berdasarkan ID, sekaligus mengambil data relasi (eager loading)
        // detailGuru, pembelajaran, pembelajaran.kelas, dan pembelajaran.mapel
        $guru = Guru::with([
            'detailGuru', 
            'pembelajaran.kelas', 
            'pembelajaran.mapel'
        ])->findOrFail($id);

        // Kirim data yang sudah dimuat ke view 'guru.show'
        return view('guru.show', compact('guru'));
    }

    /**
     * Simpan guru baru yang baru dibuat ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi Data Gabungan
        $request->validate([
            // Field Model Guru
            'nama_guru' => 'required|string|max:255',
            'nip' => 'nullable|string|max:18|unique:guru,nip',
            'nuptk' => 'nullable|string|max:16|unique:guru,nuptk',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'jenis_ptk' => 'required|string|max:100',
            'role' => 'required|string|max:100', 
            'status' => 'required|in:aktif,non-aktif',
            
            // Field Model DetailGuru (Contoh beberapa field penting)
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'agama' => 'nullable|string|max:50',
            'alamat' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:15',
            'email' => 'nullable|email|unique:detail_guru,email', 
            'nik' => 'nullable|string|max:20|unique:detail_guru,nik',
            
            // Field Model Pembelajaran (Jika di-input sebagai array)
            'pembelajaran.*.id_kelas' => 'nullable|integer|exists:kelas,id_kelas',
            'pembelajaran.*.id_mapel' => 'nullable|integer|exists:mata_pelajaran,id_mapel',
        ]);

        // 2. Database Transaction
        DB::beginTransaction();

            try {
                // A. Create Model Guru - Ganti getFillable() dengan array field
            $guru = Guru::create($request->only([
                'nama_guru', 'nip', 'nuptk', 'jenis_kelamin', 'jenis_ptk', 'role', 'status'
            ]));

            // B. Create Model DetailGuru - Ganti getFillable() dengan array field
            $detailData = $request->only([
                'tempat_lahir', 'tanggal_lahir', 'status_kepegawaian', 'agama', 'alamat', 'rt', 'rw', 'dusun', 'kelurahan', 'kecamatan', 'kode_pos', 'no_telp', 'no_hp', 'email', 'tugas_tambahan', 'sk_cpns', 'tgl_cpns', 'sk_pengangkatan', 'tmt_pengangkatan', 'lembaga_pengangkatan', 'pangkat_gol', 'sumber_gaji', 'nama_ibu_kandung', 'status_perkawinan', 'nama_suami_istri', 'nip_suami_istri', 'pekerjaan_suami_istri', 'tmt_pns', 'lisensi_kepsek', 'diklat_kepengawasan', 'keahlian_braille', 'keahlian_isyarat', 'npwp', 'nama_wajib_pajak', 'kewarganegaraan', 'bank', 'norek_bank', 'nama_rek', 'nik', 'no_kk', 'karpeg', 'karis_karsu', 'lintang', 'bujur', 'nuks'
            ]);
            
            // ... (Logika create DetailGuru dan Pembelajaran)
            $guru->detailGuru()->create($detailData);

            // ... (Logika Pembelajaran)
            
            DB::commit();
            return redirect()->route('master.guru.index')->with('success', 'Data Guru berhasil ditambahkan!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error saat menyimpan data Guru: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data guru: ' . $e->getMessage());
        }
    }


    // --- METHOD EDIT ---
    /**
     * Tampilkan form untuk mengedit guru tertentu.
     */
    public function edit($id)
    {
        // Eager loading relasi DetailGuru dan Pembelajaran
        $guru = Guru::with('detailGuru', 'pembelajaran')
                    ->findOrFail($id);
        
        // Asumsi: Anda juga butuh daftar Kelas dan Mata Pelajaran untuk form Pembelajaran
        $kelasList = Kelas::all(); // Ganti Kelas dengan nama model Anda
        $mapelList = MataPelajaran::all(); // Ganti MataPelajaran dengan nama model Anda

        return view('guru.edit', compact('guru', 'kelasList', 'mapelList'));
    }

    /**
     * Perbarui data guru tertentu di database.
     */
    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        // 1. Validasi Data Gabungan
        $request->validate([
            // ... (Validasi Guru dan DetailGuru yang sama)
            'nip' => ['nullable', 'string', 'max:18', Rule::unique('guru', 'nip')->ignore($guru->id_guru, 'id_guru')],
            // ...
            
            // Field Model Pembelajaran (Dibuat OPSI)
            'pembelajaran.*.id_kelas' => 'nullable|integer|exists:kelas,id_kelas',
            'pembelajaran.*.id_mapel' => 'nullable|integer|exists:mata_pelajaran,id_mapel',
        ]);

        DB::beginTransaction();

            try {
                // A. Update Model Guru - Ganti getFillable() dengan array field
            $guru->update($request->only([
                'nama_guru', 'nip', 'nuptk', 'jenis_kelamin', 'jenis_ptk', 'role', 'status'
            ]));

            // B. Update Model DetailGuru - Ganti getFillable() dengan array field
            $detailData = $request->only([
                'tempat_lahir', 'tanggal_lahir', 'status_kepegawaian', 'agama', 'alamat', 'rt', 'rw', 'dusun', 'kelurahan', 'kecamatan', 'kode_pos', 'no_telp', 'no_hp', 'email', 'tugas_tambahan', 'sk_cpns', 'tgl_cpns', 'sk_pengangkatan', 'tmt_pengangkatan', 'lembaga_pengangkatan', 'pangkat_gol', 'sumber_gaji', 'nama_ibu_kandung', 'status_perkawinan', 'nama_suami_istri', 'nip_suami_istri', 'pekerjaan_suami_istri', 'tmt_pns', 'lisensi_kepsek', 'diklat_kepengawasan', 'keahlian_braille', 'keahlian_isyarat', 'npwp', 'nama_wajib_pajak', 'kewarganegaraan', 'bank', 'norek_bank', 'nama_rek', 'nik', 'no_kk', 'karpeg', 'karis_karsu', 'lintang', 'bujur', 'nuks'
            ]);
            
            // ... (Logika update DetailGuru dan Pembelajaran)
            $guru->detailGuru()->updateOrCreate(
                ['id_guru' => $guru->id_guru],
                $detailData
            );
            
            // ... (Logika Pembelajaran)

            DB::commit();
            return redirect()->route('master.guru.index')->with('success', 'Data Guru dan detailnya berhasil diperbarui!');
        
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error saat memperbarui data Guru: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data guru: ' . $e->getMessage());
        }
    }


    // --- METHOD DELETE (DESTROY) ---
    /**
     * Hapus guru tertentu dari database.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $guru = Guru::findOrFail($id);
            
            // Hapus data DetailGuru terkait 
            if ($guru->detailGuru) {
                $guru->detailGuru->delete();
            }

            // Hapus data Pembelajaran terkait 
            $guru->pembelajaran()->delete(); 

            // Hapus Model Guru utama
            $guru->delete();

            DB::commit();
            return redirect()->route('master.guru.index')->with('success', 'Data Guru dan semua relasinya berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus data guru: ' . $e->getMessage());
        }
    }
    
    
        // PDF CSV
    public function exportPdf()
    {
        $guru = Guru::all();

        $pdf = Pdf::loadView('exports.data_guru_pdf', [
            'guru' => $guru
        ]);

        return $pdf->download('data-guru.pdf');
    }

    public function exportCsv()
    {
        $guru = Guru::all();

        return response()->streamDownload(function() use ($guru) {

            $file = fopen('php://output', 'w');

            // HEADER CSV
            fputcsv($file, ['No','Nama Guru','NIP','NUPTK','Jenis Kelamin','Jenis PTK','Role','Status']);

            $no = 1;

            foreach ($guru as $g) {
                fputcsv($file, [
                    $no++,
                    $g->nama_guru,
                    $g->nip,
                    $g->nuptk,
                    $g->jenis_kelamin,
                    $g->jenis_ptk,
                    $g->role,
                    $g->status,
                ]);
            }

            fclose($file);

        }, 'data-guru.csv', [
            'Content-Type' => 'text/csv'
        ]);
    }


    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $csv = array_map('str_getcsv', file($request->file('file')));

        $startImport = false;

        // Helper untuk tanggal
        $fixDate = function ($date) {
            if (empty(trim($date))) return null;
            $d = date_create($date);
            return $d ? date_format($d, 'Y-m-d') : null;
        };

        foreach ($csv as $row) {

            // Skip row kosong
            if (empty(trim($row[1] ?? ''))) continue;

            // Skip header
            if (strtolower(trim($row[1])) === 'nama') continue;

            // Deteksi baris data asli
            if (!$startImport) {
                if (strlen(trim($row[1])) > 2) {
                    $startImport = true;
                } else {
                    continue;
                }
            }

            // Jenis kelamin otomatis null kalau tidak L/P
            $jk = strtoupper(trim($row[3] ?? ''));
            $jk = in_array($jk, ['L', 'P']) ? $jk : null;

            // =====================
            // Insert ke tabel guru
            // =====================
            $guru = Guru::create([
                'id_pembelajaran' => null, // sesuaikan kolom CSV
                'nama_guru'       => $row[1] ?? null,
                'nip'             => $row[6] ?? null,
                'nuptk'           => $row[2] ?? null,
                'jenis_kelamin'   => $jk,
                'jenis_ptk'       => $row[8] ?? null,
                'role'            => 'guru_mapel',
                'status'          => 'aktif',
            ]);

            // =========================
            // Insert ke detail_guru
            // Semua otomatis nullable
            // =========================
            DetailGuru::create([
                'id_guru'               => $guru->id_guru,
                'tempat_lahir'          => $row[4] ?? null,
                'tanggal_lahir'         => $fixDate($row[5] ?? null),
                'status_kepegawaian'    => $row[7] ?? null,
                'agama'                 => $row[9] ?? null,
                'alamat'                => $row[10] ?? null,
                'rt'                    => $row[11] ?? null,
                'rw'                    => $row[12] ?? null,
                'dusun'                 => $row[13] ?? null,
                'kelurahan'             => $row[14] ?? null,
                'kecamatan'             => $row[15] ?? null,
                'kode_pos'              => $row[16] ?? null,
                'no_telp'               => $row[17] ?? null,
                'no_hp'                 => $row[18] ?? null,
                'email'                 => $row[19] ?? null,
                'tugas_tambahan'        => $row[20] ?? null,
                'sk_cpns'               => $row[21] ?? null,
                'tgl_cpns'              => $fixDate($row[22] ?? null),
                'sk_pengangkatan'       => $row[23] ?? null,
                'tmt_pengangkatan'      => $fixDate($row[24] ?? null),
                'lembaga_pengangkatan'  => $row[25] ?? null,
                'pangkat_gol'           => $row[26] ?? null,
                'sumber_gaji'           => $row[27] ?? null,
                'nama_ibu_kandung'      => $row[28] ?? null,
                'status_perkawinan'     => $row[29] ?? null,
                'nama_suami_istri'      => $row[30] ?? null,
                'nip_suami_istri'       => $row[31] ?? null,
                'pekerjaan_suami_istri' => $row[32] ?? null,
                'tmt_pns'               => $fixDate($row[33] ?? null),
                'lisensi_kepsek'        => $row[34] ?? null,
                'diklat_kepengawasan'   => $row[35] ?? null,
                'keahlian_braille'      => $row[36] ?? null,
                'keahlian_isyarat'      => $row[37] ?? null,
                'npwp'                  => $row[38] ?? null,
                'nama_wajib_pajak'      => $row[39] ?? null,
                'kewarganegaraan'       => $row[40] ?? null,
                'bank'                  => $row[41] ?? null,
                'norek_bank'            => $row[42] ?? null,
                'nama_rek'              => $row[43] ?? null,
                'nik'                   => $row[44] ?? null,
                'no_kk'                 => $row[45] ?? null,
                'karpeg'                => $row[46] ?? null,
                'karis_karsu'           => $row[47] ?? null,
                'lintang'               => $row[48] ?? null,
                'bujur'                 => $row[49] ?? null,
                'nuks'                  => $row[50] ?? null,
            ]);
        }

        return back()->with('success', 'Data CSV berhasil diimport');
    }

    public function importXlsx(Request $request)
    {
        // === START: PENGATURAN BATAS PHP UNTUK TUGAS BERAT ===
        set_time_limit(0); 
        ini_set('memory_limit', '512M'); 
        // === END: PENGATURAN BATAS PHP ===
        
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        
        $file = $request->file('file');
        
        try {
            $rows = Excel::toArray(new \stdClass(), $file)[0]; 
        } catch (\Exception $e) {
            \Log::error("Gagal membaca file Excel Guru: " . $e->getMessage());
            return back()->with('error', 'Gagal membaca file Excel Guru. Pastikan format file benar. Error: ' . $e->getMessage());
        }

        if (count($rows) < 6) { 
            return back()->with('error', 'File Excel tidak valid atau baris data kurang dari 6!');
        }
        
        // =================================================================
        // LOGIKA HEADER (Baris 5 dan 6)
        // =================================================================
        // Logika pembuatan header tetap sama dan sudah teruji
        $headerRow1 = $rows[4] ?? []; 
        $headerRow2 = $rows[5] ?? []; 
        $maxColumns = max(count($rows[4]), count($rows[5]), count($rows[7] ?? [])); 
        $rawHeader = array_pad(array_merge($headerRow1, $headerRow2), $maxColumns, null);
        $rawHeader = array_slice($rawHeader, 0, $maxColumns);

        $header = [];
        $headerKeys = [];
        foreach ($rawHeader as $h) {
            $h = strtolower(trim((string)$h)); 
            $h = str_replace(["\r", "\n"], '_', $h);
            $cleanH = str_replace([' ', '.', '-', '/', '\\', '(', ')', ' '], '_', $h); 
            
            if (empty($cleanH)) {
                 $cleanH = 'kolom_kosong_' . (count($header) + 1);
            }
            
            $originalCleanH = $cleanH;
            $counter = 1;
            while (in_array($cleanH, $headerKeys)) {
                $cleanH = $originalCleanH . '_' . $counter++;
            }

            $headerKeys[] = $cleanH;
            $header[] = $cleanH;
        }

        $headerCount = count($header);
        
        // ================================
        // DATA MULAI DARI BARIS 6 (Index 5)
        // ================================
        $dataStart = 5; 
        $countInsert = 0;
        $countUpdate = 0;
        $currentRow = 0;
        $skippedRows = []; 

        DB::beginTransaction();
        
        try {
            // Helper untuk konversi tanggal
            $parseDate = function($value) {
                if (empty($value)) return null;
                try {
                     if (is_numeric($value) && $value > 0) {
                        return Carbon::instance(Date::excelToDateTimeObject($value))->toDateString();
                     }
                     return Carbon::parse($value)->toDateString();
                } catch (\Exception $e) {
                    return null;
                }
            };
            
            for ($i = $dataStart; $i < count($rows); $i++) {
                $currentRow = $i + 1;
                $row = $rows[$i]; 
                
                if (empty(array_filter($row))) {
                    $skippedRows[] = ['row' => $currentRow, 'reason' => 'Baris dianggap kosong'];
                    continue;
                }
                
                if (count($row) != $headerCount) {
                    $row = array_pad($row, $headerCount, null);
                    if (count($row) > $headerCount) {
                        $row = array_slice($row, 0, $headerCount);
                    }
                }
                
                $mapped = array_combine($header, $row);
                if (!$mapped) {
                     $skippedRows[] = ['row' => $currentRow, 'reason' => 'Gagal memetakan'];
                     continue;
                }
                
                // =================================================================
                // 🛑 DATA MAPPING DAN VALIDASI WAJIB
                // =================================================================
                $namaGuru = trim((string)($mapped['nama'] ?? '')); 
                $nipGuru = trim((string)($mapped['nip'] ?? '')); 
                $nuptkGuru = trim((string)($mapped['nuptk'] ?? '')); 
                
                if (empty($namaGuru) || $namaGuru === '0') {
                    $skippedRows[] = ['row' => $currentRow, 'reason' => "Nama Guru kosong atau nilainya '0'"];
                    continue;
                }
                
                // Konversi Boolean
                $isLisensiKepsek = strtolower(trim($mapped['sudah_lisensi_kepala_sekolah'] ?? '')) == 'ya' ? 1 : 0;
                $isDiklatPengawasan = strtolower(trim($mapped['pernah_diklat_kepengawasan'] ?? '')) == 'ya' ? 1 : 0;
                $isKeahlianBraille = strtolower(trim($mapped['keahlian_braille'] ?? '')) == 'ya' ? 1 : 0;
                $isKeahlianIsyarat = strtolower(trim($mapped['keahlian_bahasa_isyarat'] ?? '')) == 'ya' ? 1 : 0;
                
                
                // 1. SIAPKAN DATA UNTUK MODEL GURU
                $guruData = [
                    'nama_guru'                    => $namaGuru, 
                    'nip'                          => $nipGuru, 
                    'nuptk'                        => $nuptkGuru, 
                    'jenis_kelamin'                => $mapped['jk'] ?? null, 
                    'jenis_ptk'                    => $mapped['jenis_ptk'] ?? null,
                    'role'                         => $mapped['role'] ?? 'guru_mapel', 
                    'status'                       => $mapped['status'] ?? 'aktif', 
                ];

                // 2. SIAPKAN DATA UNTUK MODEL DETAIL GURU
                $detailData = [
                    'id_pembelajaran'              => null,
                    'tempat_lahir'                 => $mapped['tempat_lahir'] ?? null,
                    'tanggal_lahir'                => $parseDate($mapped['tanggal_lahir'] ?? null),
                    'status_kepegawaian'           => $mapped['status_kepegawaian'] ?? null,
                    'agama'                        => $mapped['agama'] ?? null,
                    'alamat'                       => $mapped['alamat_jalan'] ?? null, 
                    'rt'                           => $mapped['rt'] ?? null,
                    'rw'                           => $mapped['rw'] ?? null,
                    'dusun'                        => $mapped['nama_dusun'] ?? null, 
                    'kelurahan'                    => $mapped['desa_kelurahan'] ?? null, 
                    'kecamatan'                    => $mapped['kecamatan'] ?? null,
                    'kode_pos'                     => $mapped['kode_pos'] ?? null,
                    'no_telp'                      => $mapped['telepon'] ?? null, 
                    'no_hp'                        => $mapped['hp'] ?? null,
                    'email'                        => $mapped['email'] ?? null,
                    'lintang'                      => $mapped['lintang'] ?? null,
                    'bujur'                        => $mapped['bujur'] ?? null,
                    'tugas_tambahan'               => $mapped['tugas_tambahan'] ?? null,
                    'sk_cpns'                      => $mapped['sk_cpns'] ?? null,
                    'tgl_cpns'                     => $parseDate($mapped['tanggal_cpns'] ?? null),
                    'sk_pengangkatan'              => $mapped['sk_pengangkatan'] ?? null,
                    'tmt_pengangkatan'             => $parseDate($mapped['tmt_pengangkatan'] ?? null),
                    'lembaga_pengangkatan'         => $mapped['lembaga_pengangkatan'] ?? null,
                    'pangkat_gol'                  => $mapped['pangkat_golongan'] ?? null, 
                    'sumber_gaji'                  => $mapped['sumber_gaji'] ?? null,
                    'tmt_pns'                      => $parseDate($mapped['tmt_pns'] ?? null),
                    'karpeg'                       => $mapped['karpeg'] ?? null, 
                    'karis_karsu'                  => $mapped['karis_karsu'] ?? null, 
                    'nuks'                         => $mapped['nuks'] ?? null,
                    'nama_ibu_kandung'             => $mapped['nama_ibu_kandung'] ?? null,
                    'status_perkawinan'            => $mapped['status_perkawinan'] ?? null,
                    'nama_suami_istri'             => $mapped['nama_suami_istri'] ?? null, 
                    'nip_suami_istri'              => $mapped['nip_suami_istri'] ?? null, 
                    'pekerjaan_suami_istri'        => $mapped['pekerjaan_suami_istri'] ?? null, 
                    'npwp'                         => $mapped['npwp'] ?? null,
                    'nama_wajib_pajak'             => $mapped['nama_wajib_pajak'] ?? null, 
                    'kewarganegaraan'              => $mapped['kewarganegaraan'] ?? null,
                    'bank'                         => $mapped['bank'] ?? null,
                    'norek_bank'                   => $mapped['nomor_rekening_bank'] ?? null, 
                    'nama_rek'                     => $mapped['rekening_atas_nama'] ?? null, 
                    'nik'                          => $mapped['nik'] ?? null,
                    'no_kk'                        => $mapped['no_kk'] ?? null,
                    'lisensi_kepsek'               => $isLisensiKepsek, 
                    'diklat_kepengawasan'          => $isDiklatPengawasan, 
                    'keahlian_braille'             => $isKeahlianBraille,
                    'keahlian_isyarat'             => $isKeahlianIsyarat, 
                ];


                // =================================================================
                // 🛑 UPSERT LOGIC BERDASARKAN NUPTK
                // =================================================================
                
                $guru = null;
                $action = 'INSERT';

                if (!empty($nuptkGuru)) {
                    // 3A. Jika NUPTK ada, coba cari dan update
                    $guru = Guru::where('nuptk', $nuptkGuru)->first();

                    if ($guru) {
                        // UPDATE data Guru
                        $guru->update($guruData);
                        $action = 'UPDATE';
                    } else {
                        // NUPTK ada tapi belum di DB, CREATE baru
                        $guru = Guru::create($guruData);
                        $countInsert++;
                    }
                } else {
                    // 3B. NUPTK kosong (Guru Bantu), lakukan INSERT
                    
                    // Safety check: Pastikan NIP tidak duplikat jika NIP diisi
                    if (!empty($nipGuru) && Guru::where('nip', $nipGuru)->exists()) {
                        $skippedRows[] = ['row' => $currentRow, 'reason' => "NIP ($nipGuru) sudah ada, NUPTK kosong. Skipped."];
                        continue;
                    }
                    
                    $guru = Guru::create($guruData);
                    $countInsert++;
                }

                // Jika proses update/insert di atas gagal mendapatkan model Guru, skip baris.
                if (!$guru) {
                    $skippedRows[] = ['row' => $currentRow, 'reason' => 'Gagal mendapatkan/membuat instance Guru.'];
                    continue;
                }
                
                // 4. UPSERT DETAIL GURU (Selalu update detail yang terhubung dengan Guru ini)
                $guru->detailGuru()->updateOrCreate(
                    ['id_guru' => $guru->id_guru],
                    $detailData
                );
                
                if ($action === 'UPDATE') {
                    $countUpdate++;
                } else {
                    // $countInsert sudah dihitung di atas
                }
            }

            DB::commit(); 
            
            $totalProcessed = $countInsert + $countUpdate;
            if (count($skippedRows) > 0) {
                 \Log::warning("Import Guru: Ditemukan " . count($skippedRows) . " baris yang dilewati. Detail: " . json_encode($skippedRows));
            }
            
            $message = "Import Excel Guru selesai! Total diproses: $totalProcessed (Insert: $countInsert, Update: $countUpdate). Ditemukan " . count($skippedRows) . " baris dilewati. Detail di log.";
            return redirect()->route('master.guru.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack(); 
            $errorMessage = "Import Excel Guru gagal pada Baris ke-$currentRow. Pesan Error: " . $e->getMessage();
            \Log::error($errorMessage);
            return redirect()->back()->with('error', $errorMessage);
        }
    }

}