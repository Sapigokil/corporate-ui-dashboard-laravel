<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\InfoSekolah;
use App\Models\Catatan;
use App\Models\StatusRapor;
use App\Models\BobotNilai; // WAJIB ADA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

class RaporController extends Controller
{
    /**
     * AJAX: Get Detail Progress Per Siswa (FIX WARNA & STATUS)
     */
    public function getDetailProgress(Request $request)
    {
        $id_siswa = $request->id_siswa;
        $id_kelas = $request->id_kelas;
        $tahun_ajaran = $request->tahun_ajaran;
        
        $semesterRaw = $request->semester ?? 'Ganjil';
        $semesterEnum = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        // 1. Ambil Target Sumatif
        $bobotSetting = BobotNilai::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', strtoupper($semesterRaw))
            ->first();
        
        $targetMin = $bobotSetting->jumlah_sumatif ?? 3; 

        $agamaSiswa = DB::table('detail_siswa')->where('id_siswa', $id_siswa)->value('agama');
        $agamaSiswa = strtolower(trim($agamaSiswa));

        // 2. Ambil Mapel
        $pembelajaran = DB::table('pembelajaran')
        ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
        ->where('pembelajaran.id_kelas', $id_kelas)
        ->where('mata_pelajaran.is_active', 1)
        ->where(function ($q) use ($agamaSiswa) {
            $q->whereNull('mata_pelajaran.agama_khusus')
            ->orWhereRaw('LOWER(TRIM(mata_pelajaran.agama_khusus)) = ?', [$agamaSiswa]);
        })
        ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel', 'mata_pelajaran.kategori')
        ->orderBy('mata_pelajaran.urutan', 'asc')
        ->orderBy('mata_pelajaran.nama_mapel', 'asc')
        ->get();

        // 3. Loop Mapel
        $data = $pembelajaran->map(function($mp) use ($id_siswa, $semesterEnum, $tahun_ajaran, $targetMin) {
            
            // Cek Nilai Akhir
            $nilai = DB::table('nilai_akhir')
                ->where('id_siswa', $id_siswa)
                ->where('id_mapel', $mp->id_mapel)
                ->where('semester', (string)$semesterEnum) 
                ->where('tahun_ajaran', trim($tahun_ajaran))
                ->first();
            
            // Cek Jumlah Sumatif
            $countSumatif = DB::table('sumatif')
                ->where('id_siswa', $id_siswa)
                ->where('id_mapel', $mp->id_mapel)
                ->where('semester', $semesterEnum)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->whereNotNull('nilai')
                ->count();

            $hasNilaiAkhir = ($nilai && !is_null($nilai->nilai_akhir) && $nilai->nilai_akhir > 0);
            
            // Syarat Lengkap: Jumlah Sumatif >= Target DAN Nilai Akhir Ada
            $isLengkap = ($countSumatif >= $targetMin) && $hasNilaiAkhir;

            return [
                'nama_mapel' => $mp->nama_mapel,
                'kategori' => match((int)$mp->kategori) {
                    1 => 'Umum', 2 => 'Kejuruan', 3 => 'Pilihan', 4 => 'Muatan Lokal', default => 'Lainnya'
                },
                // PERBAIKAN DI SINI: Gunakan key 'is_lengkap' agar cocok dengan JS
                'is_lengkap' => $isLengkap, 
                'progress_text' => $countSumatif . ' / ' . $targetMin, 
                'nilai_akhir' => $hasNilaiAkhir ? (int)$nilai->nilai_akhir : '-'
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Halaman Cetak Rapor (List Siswa per Kelas)
     */
    public function cetakIndex(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        $siswaList = [];

        if ($id_kelas) {
            $siswaList = Siswa::with('kelas')
                ->where('id_kelas', $id_kelas)
                ->orderBy('nama_siswa', 'asc')
                ->get();

            foreach ($siswaList as $s) {
                $s->status_monitoring = DB::table('status_rapor')
                    ->where('id_siswa', $s->id_siswa)
                    ->where('semester', (int)$semesterInt)
                    ->where('tahun_ajaran', trim((string)$tahun_ajaran))
                    ->first();

                $s->data_catatan = DB::table('catatan')
                    ->where('id_siswa', $s->id_siswa)
                    ->where('semester', (int)$semesterInt)
                    ->where('tahun_ajaran', trim((string)$tahun_ajaran))
                    ->first();
            }
        }

        return view('rapor.cetak_rapor', compact('kelas', 'siswaList', 'id_kelas', 'semesterRaw', 'tahun_ajaran'));
    }

    /**
     * Proses Cetak PDF Rapor Satuan
     */
    public function cetak_proses($id_siswa, Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';

        // Menggunakan Logic Baru
        $data = $this->persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran);

        $pdf = Pdf::loadView('rapor.pdf1_template', $data)
                ->setPaper('a4', 'portrait')
                ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->stream('Rapor_'.$data['siswa']->nama_siswa.'.pdf');
    }

    /**
     * Proses Cetak Rapor Massal per Kelas (ZIP)
     */
    public function cetak_massal(Request $request)
    {
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';

        if (!$id_kelas) return redirect()->back()->with('error', 'Silakan pilih kelas terlebih dahulu.');

        $daftarSiswa = Siswa::where('id_kelas', $id_kelas)->orderBy('nama_siswa', 'asc')->get();
        
        $zip = new ZipArchive;
        $zipFileName = 'Rapor_Kelas_' . $id_kelas . '_' . time() . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($daftarSiswa as $siswa) {
                $data = $this->persiapkanDataRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
                
                $pdf = \Pdf::loadView('rapor.pdf1_template', $data)
                        ->setPaper('a4', 'portrait')
                        ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
                
                $safeName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $siswa->nama_siswa);
                $zip->addFromString($safeName . '.pdf', $pdf->output());
            }
            $zip->close();
            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
    }

    /**
     * Download Rapor Massal dalam SATU FILE PDF
     */
    public function download_massal_pdf(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';

        if (!$id_kelas) return redirect()->back()->with('error', 'Silakan pilih kelas.');

        $daftarSiswa = Siswa::where('id_kelas', $id_kelas)->orderBy('nama_siswa', 'asc')->get();
        $allData = [];

        foreach ($daftarSiswa as $siswa) {
            $allData[] = $this->persiapkanDataRapor($siswa->id_siswa, $semesterRaw, $tahun_ajaran);
        }

        $pdf = Pdf::loadView('rapor.pdf2_massal_template', compact('allData'))
                ->setPaper('a4', 'portrait')
                ->setOption(['isPhpEnabled' => true, 'isRemoteEnabled' => true, 'margin_top' => 0, 'margin_bottom' => 0]);

        $filename = 'RAPOR_MASSAL_' . time() . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * ==========================================================
     * 🔥 CORE LOGIC: PERHITUNGAN NILAI RAPOR (REVISI BOBOT & TARGET)
     * ==========================================================
     */
    private function persiapkanDataRapor($id_siswa, $semesterRaw, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;
        $siswa = Siswa::with('kelas')->findOrFail($id_siswa);
        $agamaSiswa = DB::table('detail_siswa')->where('id_siswa', $id_siswa)->value('agama');
        $agamaSiswa = ucfirst(strtolower(trim($agamaSiswa)));
        $getSekolah = InfoSekolah::first();

        // 1. AMBIL SETTING BOBOT (WAJIB ADA DI DB)
        $bobotSetting = BobotNilai::where('tahun_ajaran', $tahun_ajaran)
            ->where('semester', strtoupper($semesterRaw))
            ->first();

        // Fallback jika belum disetting (agar tidak error, tapi idealnya harus diset)
        $targetMinSumatif = $bobotSetting->jumlah_sumatif ?? 0; // Target Pembagi Minimal
        $persenSumatif    = $bobotSetting->bobot_sumatif ?? 50; // Default 50:50 jika null
        $persenProject    = $bobotSetting->bobot_project ?? 50;

        // 2. AUTO-SYNC: Hitung & Update Nilai Akhir
        $pembelajaranSiswa = DB::table('pembelajaran')->where('id_kelas', $siswa->id_kelas)->get();
        
        foreach ($pembelajaranSiswa as $pb) {
            
            // A. HITUNG RATA-RATA SUMATIF (DENGAN TARGET MINIMAL)
            // Ambil semua nilai sumatif yg terisi
            $nilaiSumatifList = DB::table('sumatif')
                ->where([
                    'id_siswa' => $id_siswa, 
                    'id_mapel' => $pb->id_mapel, 
                    'semester' => $semesterInt, 
                    'tahun_ajaran' => $tahun_ajaran
                ])
                ->whereNotNull('nilai')
                ->pluck('nilai');

            $jumlahTerisi = $nilaiSumatifList->count();
            $totalNilaiS  = $nilaiSumatifList->sum();

            // Logika Pembagi: Pilih mana yang lebih besar (Terisi vs Target)
            // Contoh: Terisi 2, Target 3 => Pembagi 3 (Nilai jadi turun)
            // Contoh: Terisi 4, Target 3 => Pembagi 4 (Rata-rata murni)
            $pembagiS = max($jumlahTerisi, $targetMinSumatif);

            $avgSumatif = ($pembagiS > 0) ? round($totalNilaiS / $pembagiS, 2) : 0;

            // B. HITUNG RATA-RATA PROJECT
            $avgProject = DB::table('project')
                ->where([
                    'id_siswa' => $id_siswa, 
                    'id_mapel' => $pb->id_mapel, 
                    'semester' => $semesterInt, 
                    'tahun_ajaran' => $tahun_ajaran
                ])
                ->avg('nilai') ?? 0;
            
            // C. HITUNG NILAI AKHIR BERBOBOT
            // Rumus: (RataS * BobotS%) + (RataP * BobotP%)
            $nilaiAkhirCalculated = 0;

            // Hitung hanya jika ada minimal 1 nilai masuk (Sumatif atau Project)
            if ($avgSumatif > 0 || $avgProject > 0) {
                $bobotS_nominal = $avgSumatif * ($persenSumatif / 100);
                $bobotP_nominal = $avgProject * ($persenProject / 100);
                
                // Pembulatan Akhir (Menjadi Integer)
                $nilaiAkhirCalculated = (int) round($bobotS_nominal + $bobotP_nominal);
            }

            // D. UPDATE KE DATABASE NILAI_AKHIR
            if ($nilaiAkhirCalculated > 0) {
                // Cek data lama untuk mempertahankan capaian/deskripsi jika sudah ada
                $existing = DB::table('nilai_akhir')
                    ->where([
                        'id_siswa' => $id_siswa, 
                        'id_mapel' => $pb->id_mapel, 
                        'semester' => $semesterInt, 
                        'tahun_ajaran' => $tahun_ajaran
                    ])->first();

                // Jika capaian belum ada, generate otomatis
                $deskripsi = $existing->capaian_akhir ?? $this->generateCapaianDariSumatif($id_siswa, $pb->id_mapel, $semesterInt, $tahun_ajaran);

                DB::table('nilai_akhir')->updateOrInsert(
                    [
                        'id_siswa' => $id_siswa, 
                        'id_mapel' => $pb->id_mapel, 
                        'semester' => $semesterInt, 
                        'tahun_ajaran' => $tahun_ajaran
                    ],
                    [
                        'id_kelas' => $siswa->id_kelas, 
                        'nilai_akhir' => $nilaiAkhirCalculated, 
                        'capaian_akhir' => $deskripsi, 
                        'updated_at' => now()
                    ]
                );
            }
        }

        // --- 3. AMBIL DATA FINAL UNTUK VIEW (Grouping Mapel) ---
        $mapelFinal = [];
        $daftarUrutan = [1 => 'MATA PELAJARAN UMUM', 2 => 'MATA PELAJARAN KEJURUAN', 3 => 'MATA PELAJARAN PILIHAN', 4 => 'MUATAN LOKAL'];
        
        foreach ($daftarUrutan as $key => $headerLabel) {
            $kelompok = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $siswa->id_kelas)
                ->where('mata_pelajaran.kategori', $key)
                ->where('mata_pelajaran.is_active', 1)
                ->where(function ($q) use ($agamaSiswa) {
                    $q->whereNull('mata_pelajaran.agama_khusus')
                    ->orWhereRaw('LOWER(TRIM(mata_pelajaran.agama_khusus)) = ?', [$agamaSiswa]);
                })
                ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
                ->orderBy('mata_pelajaran.urutan', 'asc')
                ->get();

            if ($kelompok->isNotEmpty()) {
                foreach ($kelompok as $mp) {
                    // Ambil nilai yang BARU SAJA DIUPDATE DI ATAS
                    $nf = DB::table('nilai_akhir')->where([
                        'id_siswa' => $id_siswa, 
                        'id_mapel' => $mp->id_mapel, 
                        'semester' => $semesterInt, 
                        'tahun_ajaran' => $tahun_ajaran
                    ])->first();

                    $mp->nilai_akhir = $nf->nilai_akhir ?? 0;
                    $mp->capaian = $nf->capaian_akhir ?? '-';
                }
                $mapelFinal[$key] = $kelompok;
            }
        }

        // --- 4. DATA PELENGKAP (Ekskul, Wali, Dll) ---
        $catatan = DB::table('catatan')->where(['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])->first();
        $dataEkskul = [];
        if ($catatan && !empty($catatan->ekskul)) {
            $ids = array_map('trim', explode(',', $catatan->ekskul));
            $grades = !empty($catatan->predikat) ? array_map('trim', explode(',', $catatan->predikat)) : [];
            $descs = !empty($catatan->keterangan) ? array_map('trim', explode('|', $catatan->keterangan)) : [];

            foreach ($ids as $index => $idEkstra) {
                if ($idEkstra != "") {
                    $namaEkskulReal = DB::table('ekskul')->where('id_ekskul', $idEkstra)->value('nama_ekskul');
                    $dataEkskul[] = (object)[
                        'nama' => $namaEkskulReal ?? 'Ekstra ID: ' . $idEkstra,
                        'predikat' => $grades[$index] ?? '-',
                        'keterangan' => $descs[$index] ?? '-'
                    ];
                }
            }
        }

        $tktRaw = strtoupper(preg_replace("/[^a-zA-Z0-9]/", "", trim($siswa->kelas->tingkat ?? '')));
        $fase = match (true) {
            ($tktRaw === 'X' || $tktRaw === '10') => 'E',
            ($tktRaw === 'XI' || $tktRaw === '11' || $tktRaw === 'XII' || $tktRaw === '12') => 'F',
            default => '-'
        };

        $namaWali = $siswa->kelas->wali_kelas ?? 'Wali Kelas';
        $dataGuru = DB::table('guru')->where('nama_guru', 'LIKE', '%' . $namaWali . '%')->first();

        return [
            'siswa'         => $siswa,
            'fase'          => $fase,
            'sekolah'       => $getSekolah->nama_sekolah ?? 'SMKN 1 SALATIGA',
            'infoSekolah'   => $getSekolah->jalan ?? 'Alamat Sekolah',
            'info_sekolah'  => $getSekolah,
            'mapelGroup'    => $mapelFinal,
            'dataEkskul'    => $dataEkskul,
            'catatan'       => $catatan,
            'semester'      => $semesterRaw,
            'tahun_ajaran'  => $tahun_ajaran,
            'semesterInt'   => $semesterInt,
            'nama_wali'     => $namaWali,
            'nip_wali'      => $dataGuru->nip ?? '-',
            'nama_kepsek'   => $getSekolah->nama_kepsek ?? 'NAMA KEPALA SEKOLAH',
            'nip_kepsek'    => $getSekolah->nip_kepsek ?? '-',
        ];
    }

    /**
     * Generate Deskripsi Capaian (Helper)
     */
    private function generateCapaianDariSumatif($id_siswa, $id_mapel, $semester, $tahun_ajaran)
    {
        $nilaiTp = DB::table('sumatif')
            ->where(['id_siswa' => $id_siswa, 'id_mapel' => $id_mapel, 'semester' => $semester, 'tahun_ajaran' => $tahun_ajaran])
            ->whereNotNull('nilai')
            ->select('nilai', 'tujuan_pembelajaran')
            ->orderBy('nilai', 'asc')
            ->get();

        if ($nilaiTp->isEmpty()) return 'Perlu penguatan dalam hal Belum ditentukan.';

        $tpRendah = $nilaiTp->first();
        $tpTinggi = $nilaiTp->last();

        $narasiRendah = ($tpRendah->nilai < 78) ? 'Perlu peningkatan dalam hal' : 'Perlu penguatan dalam hal';
        $narasiTinggi = ($tpTinggi->nilai >= 78) ? 'Baik dalam hal' : 'Cukup dalam hal';

        if ($tpRendah->tujuan_pembelajaran === $tpTinggi->tujuan_pembelajaran) {
            return "{$narasiRendah} {$tpRendah->tujuan_pembelajaran}.";
        }
        return "{$narasiRendah} {$tpRendah->tujuan_pembelajaran}, namun menunjukkan capaian {$narasiTinggi} {$tpTinggi->tujuan_pembelajaran}.";
    }

    /**
     * Sinkronisasi Kelas (FIX: Force Update & Handle Nilai 0)
     */
    public function sinkronkanKelas(Request $request)
    {
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        if (!$id_kelas) return response()->json(['message' => 'Kelas tidak ditemukan'], 400);

        DB::beginTransaction();
        try {
            // 1. Ambil Pengaturan Bobot
            $bobotSetting = BobotNilai::where('tahun_ajaran', $tahun_ajaran)
                ->where('semester', strtoupper($semesterRaw))
                ->first();

            // Default jika bobot tidak ditemukan (agar tidak error division by zero)
            $targetMinSumatif = $bobotSetting->jumlah_sumatif ?? 0;
            $persenSumatif    = $bobotSetting->bobot_sumatif ?? 50; 
            $persenProject    = $bobotSetting->bobot_project ?? 50;

            // 2. Ambil Siswa & Mapel
            $siswaList = Siswa::where('id_kelas', $id_kelas)->pluck('id_siswa');
            
            $pembelajaran = DB::table('pembelajaran')
                ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
                ->where('pembelajaran.id_kelas', $id_kelas)
                ->where('mata_pelajaran.is_active', 1)
                ->select('pembelajaran.id_mapel', 'mata_pelajaran.agama_khusus')
                ->get();

            // 3. PRE-FETCH DATA (Batch Query)
            $allSumatif = DB::table('sumatif')
                ->whereIn('id_siswa', $siswaList)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->whereNotNull('nilai')
                ->select('id_siswa', 'id_mapel', 'nilai')
                ->get()
                ->groupBy('id_siswa');

            $allProject = DB::table('project')
                ->whereIn('id_siswa', $siswaList)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->select('id_siswa', 'id_mapel', 'nilai')
                ->get()
                ->groupBy('id_siswa');

            // Ambil Capaian Lama (Agar deskripsi yang sudah diedit manual tidak hilang)
            $allExisting = DB::table('nilai_akhir')
                ->whereIn('id_siswa', $siswaList)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->select('id_siswa', 'id_mapel', 'capaian_akhir')
                ->get()
                ->groupBy('id_siswa');

            $mapelTuntasPerSiswa = []; // Counter tuntas

            // 4. LOOPING HITUNG NILAI & STATUS
            foreach ($siswaList as $id_siswa) {
                $sumatifSiswa = $allSumatif->get($id_siswa) ?? collect();
                $projectSiswa = $allProject->get($id_siswa) ?? collect();
                $existingSiswa = $allExisting->get($id_siswa) ?? collect();
                $agamaSiswa = strtolower(trim(DB::table('detail_siswa')->where('id_siswa', $id_siswa)->value('agama')));

                $mapelTuntasPerSiswa[$id_siswa] = 0; // Reset counter

                foreach ($pembelajaran as $pb) {
                    // Filter Agama
                    if (!is_null($pb->agama_khusus)) {
                        if (strtolower(trim($pb->agama_khusus)) !== $agamaSiswa) continue;
                    }

                    // A. Hitung Sumatif
                    $nilaiS_list = $sumatifSiswa->where('id_mapel', $pb->id_mapel)->pluck('nilai');
                    $jumlahTerisi = $nilaiS_list->count(); // Jumlah Real (Misal: 2)
                    $totalNilaiS = $nilaiS_list->sum();
                    
                    // Pembagi (Max antara Real vs Target)
                    $pembagiS = max($jumlahTerisi, $targetMinSumatif);
                    $rataSumatif = ($pembagiS > 0) ? round($totalNilaiS / $pembagiS, 2) : 0;

                    // B. Hitung Project
                    $rataProject = $projectSiswa->where('id_mapel', $pb->id_mapel)->avg('nilai') ?? 0;

                    // C. Hitung Nilai Akhir
                    $bobotS = $rataSumatif * ($persenSumatif / 100);
                    $bobotP = $rataProject * ($persenProject / 100);
                    $nilaiFinal = (int) round($bobotS + $bobotP);

                    // D. Simpan ke Database (Force Sync)
                    // ... (Kode simpan DB tetap sama) ...
                    $oldData = $existingSiswa->where('id_mapel', $pb->id_mapel)->first();
                    $deskripsi = $oldData->capaian_akhir ?? $this->generateCapaianDariSumatif($id_siswa, $pb->id_mapel, $semesterInt, $tahun_ajaran);

                    DB::table('nilai_akhir')->updateOrInsert(
                        [
                            'id_siswa' => $id_siswa, 
                            'id_mapel' => $pb->id_mapel, 
                            'semester' => $semesterInt, 
                            'tahun_ajaran' => $tahun_ajaran
                        ],
                        [
                            'id_kelas' => $id_kelas, 
                            'nilai_akhir' => $nilaiFinal, 
                            'capaian_akhir' => $deskripsi, 
                            'updated_at' => now()
                        ]
                    );

                    // ======================================================
                    // 🔥 PERBAIKAN LOGIKA STATUS TUNTAS (SYARAT DIPERKETAT)
                    // ======================================================
                    // Syarat: Nilai > 0 DAN Jumlah Sumatif >= Target
                    
                    $isSumatifLengkap = ($jumlahTerisi >= $targetMinSumatif);

                    if ($nilaiFinal > 0 && $isSumatifLengkap) {
                        $mapelTuntasPerSiswa[$id_siswa]++;
                    }
                }
            }

            // 5. UPDATE STATUS RAPOR (Sekaligus)
            $allCatatan = DB::table('catatan')
                ->whereIn('id_siswa', $siswaList)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->select('id_siswa', 'catatan_wali_kelas')
                ->get()
                ->keyBy('id_siswa');
            
            foreach ($siswaList as $id_siswa) {
                // Hitung total mapel siswa ini (tergantung agama)
                $agamaSiswa = strtolower(trim(DB::table('detail_siswa')->where('id_siswa', $id_siswa)->value('agama')));
                $totalMapelSeharusnya = $pembelajaran->filter(function($m) use ($agamaSiswa){
                     return is_null($m->agama_khusus) || strtolower(trim($m->agama_khusus)) == $agamaSiswa;
                })->count();

                $mapelTuntas = $mapelTuntasPerSiswa[$id_siswa] ?? 0;
                
                $catatan = $allCatatan->get($id_siswa);
                $isCatatanReady = ($catatan && !empty(trim($catatan->catatan_wali_kelas)));

                // Syarat Siap Cetak: Mapel Tuntas 100% DAN Catatan Ada
                $statusAkhir = ($mapelTuntas >= $totalMapelSeharusnya && $isCatatanReady) ? 'Siap Cetak' : 'Belum Lengkap';

                StatusRapor::updateOrCreate(
                    ['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => (string)$tahun_ajaran],
                    [
                        'id_kelas' => $id_kelas, 
                        'total_mapel_seharusnya' => $totalMapelSeharusnya, 
                        'mapel_tuntas_input' => $mapelTuntas, 
                        'is_catatan_wali_ready' => $isCatatanReady ? 1 : 0, 
                        'status_akhir' => $statusAkhir
                    ]
                );
            }

            DB::commit();
            return response()->json(['message' => 'Sinkronisasi Berhasil! Data Nilai & Status Rapor telah diperbarui.']);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log error untuk developer
            // \Log::error($e->getMessage()); 
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }
}