<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\InfoSekolah;
use App\Models\MataPelajaran;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LedgerTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    /**
     * =========================================================================
     * 1. HELPER FUNCTIONS
     * =========================================================================
     */

    private function mapSemesterToInt(?string $semester): int
    {
        return (strtoupper($semester) == 'GENAP') ? 2 : 1;
    }

    private function buildFilename(Request $request, string $ext): string
    {
        $kelas = Kelas::find($request->id_kelas);

        $namaKelas = $kelas
            ? preg_replace('/[^A-Za-z0-9\-]/', '_', $kelas->nama_kelas)
            : 'Tanpa_Kelas';

        $semester = $request->semester ?? 'Ganjil';
        $tahun = str_replace('/', '-', $request->tahun_ajaran ?? 'Tahun');

        return "Ledger_{$namaKelas}_{$semester}_{$tahun}.{$ext}";
    }

    private function getKelasIdsFromRequest(Request $request)
    {
        $mode = $request->mode ?? 'kelas';

        if ($mode === 'kelas') {
            return $request->id_kelas ? [$request->id_kelas] : [];
        }

        // Mode Jurusan
        $jurusan = $request->jurusan;
        $tingkat = $request->tingkat;
        
        $kelasQuery = Kelas::query();

        if ($jurusan) {
            $kelasQuery->where('jurusan', $jurusan);
        }

        if (!empty($tingkat)) {
            $kelasQuery->where('tingkat', $tingkat);
        }
        
        return $kelasQuery->pluck('id_kelas')->toArray();
    }

    /**
     * =========================================================================
     * 2. CORE LOGIC
     * =========================================================================
     */
    private function buildDataCore($kelasIds, $semesterInt, $tahun_ajaran)
    {
        // A. Ambil Data Mapel (Header Tabel)
        $rawMapelData = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->whereIn('pembelajaran.id_kelas', $kelasIds)
            ->where('mata_pelajaran.is_active', 1)
            ->select(
                'mata_pelajaran.id_mapel',
                'mata_pelajaran.nama_mapel',
                'mata_pelajaran.nama_singkat',
                'mata_pelajaran.kategori',
                'mata_pelajaran.urutan',
                DB::raw("
                    CASE 
                        WHEN mata_pelajaran.nama_mapel LIKE '%Agama%' THEN 'AGAMA' 
                        ELSE mata_pelajaran.id_mapel 
                    END AS mapel_key
                ")
            )
            ->distinct()
            ->orderBy('mata_pelajaran.kategori')
            ->orderBy('mata_pelajaran.urutan')
            ->get();

        // Ambil ID Mapel Agama untuk lookup nilai nanti
        $globalAgamaIds = DB::table('mata_pelajaran')
            ->where('nama_mapel', 'LIKE', '%Agama%')
            ->pluck('id_mapel')
            ->map(fn($id) => (string)$id)
            ->toArray();
        
        if (!in_array("1", $globalAgamaIds)) {
            $globalAgamaIds[] = "1"; 
        }

        // Grouping Header Mapel & Sorting
        $daftarMapel = $rawMapelData
            ->groupBy('mapel_key')
            ->map(function ($items) {
                $first = $items->first();
                if ($first->mapel_key === 'AGAMA') {
                    return (object)[
                        'id_mapel'     => 'AGAMA',
                        'nama_mapel'   => 'Pendidikan Agama',
                        'nama_singkat' => 'Agama',
                        'kategori'     => $first->kategori,
                        'urutan'       => $first->urutan
                    ];
                }
                return (object)[
                    'id_mapel'     => (string)$first->id_mapel,
                    'nama_mapel'   => $first->nama_mapel,
                    'nama_singkat' => $first->nama_singkat,
                    'kategori'     => $first->kategori,
                    'urutan'       => $first->urutan
                ];
            })
            ->sort(function ($a, $b) {
                // Sorting: Agama -> Kategori -> Urutan
                $aIsAgama = ($a->id_mapel === 'AGAMA');
                $bIsAgama = ($b->id_mapel === 'AGAMA');

                if ($aIsAgama && !$bIsAgama) return -1;
                if (!$aIsAgama && $bIsAgama) return 1;

                if ($a->kategori != $b->kategori) {
                    return $a->kategori <=> $b->kategori;
                }
                return $a->urutan <=> $b->urutan;
            })
            ->values();

        // B. Ambil Siswa
        $siswaList = Siswa::whereIn('id_kelas', $kelasIds)
            ->orderBy('nama_siswa')
            ->get();

        // C. Ambil Nilai Akhir
        $rawNilai = DB::table('nilai_akhir')
            ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', trim($tahun_ajaran))
            ->select('id_siswa', 'id_mapel', 'nilai_akhir') 
            ->get();

        $mapNilai = [];
        foreach ($rawNilai as $rn) {
            $sId = $rn->id_siswa;
            $mId = (string)$rn->id_mapel; 
            $mapNilai[$sId][$mId] = $rn->nilai_akhir;
        }

        // D. Ambil Absensi
        $rawAbsen = DB::table('catatan')
            ->whereIn('id_siswa', $siswaList->pluck('id_siswa'))
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', trim($tahun_ajaran))
            ->select('id_siswa', 'sakit', 'ijin', 'alpha') // Pastikan nama kolom 'izin' atau 'ijin' sesuai DB
            ->get();
        
        $mapAbsen = [];
        foreach ($rawAbsen as $ra) {
            $mapAbsen[$ra->id_siswa] = $ra;
        }

        // E. Build Data Ledger Awal (Belum Ranking)
        $dataLedger = [];

        foreach ($siswaList as $siswa) {
            $nilaiPerMapel = [];
            $totalNilai = 0;
            $jumlahMapelTerisi = 0;

            foreach ($daftarMapel as $mapel) {
                $score = 0;

                // Logika Mapel Agama vs Biasa
                if ($mapel->id_mapel === 'AGAMA') {
                    foreach ($globalAgamaIds as $idAgamaAsli) {
                        if (isset($mapNilai[$siswa->id_siswa][$idAgamaAsli])) {
                            $val = $mapNilai[$siswa->id_siswa][$idAgamaAsli];
                            if ($val > 0) {
                                $score = $val;
                                break; 
                            }
                        }
                    }
                } else {
                    $mId = (string)$mapel->id_mapel;
                    if (isset($mapNilai[$siswa->id_siswa][$mId])) {
                        $score = $mapNilai[$siswa->id_siswa][$mId];
                    }
                }

                $nilaiPerMapel[$mapel->id_mapel] = $score;

                if ($score > 0) {
                    $totalNilai += $score;
                    $jumlahMapelTerisi++;
                }
            }

            $absensi = $mapAbsen[$siswa->id_siswa] ?? null;

            $dataLedger[] = (object)[
                'nama_siswa' => $siswa->nama_siswa,
                'nipd'       => $siswa->nipd,
                'nisn'       => $siswa->nisn,
                'scores'     => $nilaiPerMapel, 
                'total'      => $totalNilai,
                'rata_rata'  => $jumlahMapelTerisi ? round($totalNilai / $jumlahMapelTerisi, 2) : 0,
                'absensi'    => (object)[
                    'sakit' => $absensi->sakit ?? 0,
                    'izin'  => $absensi->izin ?? 0, // Sesuaikan 'izin' atau 'ijin'
                    'alpha' => $absensi->alpha ?? 0,
                ],
                'ranking_no' => 0 // Placeholder ranking
            ];
        }

        return [
            'daftarMapel' => $daftarMapel,
            'dataLedger'  => collect($dataLedger), // Return Collection agar mudah di-sort
            'kelasObj'    => Kelas::find($kelasIds[0] ?? null)
        ];
    }

    /**
     * Helper: Menghitung Ranking & Mengurutkan Data Akhir
     */
    private function calculateRankingAndSort($collection, $sortBy)
    {
        // 1. HITUNG RANKING (Berdasarkan Nilai Tertinggi)
        // Kita sort dulu by Rata-rata DESC untuk memberi nomor ranking yang benar
        $rankedData = $collection->sort(function ($a, $b) {
            // Prioritas 1: Rata-rata Tinggi -> Rendah
            if ($b->rata_rata != $a->rata_rata) {
                return $b->rata_rata <=> $a->rata_rata;
            }
            // Prioritas 2: Nama A -> Z (Jika nilai sama)
            return strcmp($a->nama_siswa, $b->nama_siswa);
        })->values();

        // Assign Nomor Ranking (Juara 1, 2, 3...)
        foreach ($rankedData as $index => $item) {
            $item->ranking_no = $index + 1;
        }

        // 2. SORTING TAMPILAN (Sesuai Pilihan User)
        if ($sortBy === 'absen') {
            // Jika user minta urut Absen, kita sort ulang berdasarkan Nama
            // TAPI nomor ranking ($item->ranking_no) TETAP NEMPEL (tidak berubah)
            return $rankedData->sortBy(fn($item) => $item->nama_siswa)->values();
        } 
        
        // Default: Sort by Ranking (Nilai Tertinggi) -> Sudah dilakukan di step 1
        return $rankedData;
    }

    /**
     * =========================================================================
     * 3. MAIN PAGES
     * =========================================================================
     */

    public function index(Request $request)
    {
        // 1. Data Pendukung Filter
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        
        $jurusanList = Kelas::select('jurusan')
            ->whereNotNull('jurusan')
            ->distinct()
            ->orderBy('jurusan')
            ->pluck('jurusan');

        $tingkatList = Kelas::select('tingkat')
            ->whereNotNull('tingkat')
            ->distinct()
            ->orderBy('tingkat')
            ->pluck('tingkat');

        // Logic Tahun Ajaran
        $tahunSekarang = date('Y');
        $tahunAjaranList = [];
        for ($t = $tahunSekarang - 3; $t <= $tahunSekarang + 3; $t++) {
            $tahunAjaranList[] = $t . '/' . ($t + 1);
        }
        rsort($tahunAjaranList);

        $defaultSemester = 'Ganjil';
        $bulanSekarang = date('n');
        if ($bulanSekarang < 7) {
            $defaultTahunAjaran = ($tahunSekarang - 1) . '/' . $tahunSekarang;
            $defaultSemester = 'Genap';
        } else {
            $defaultTahunAjaran = $tahunSekarang . '/' . ($tahunSekarang + 1);
        }

        // Set default request jika kosong
        if (!$request->has('tahun_ajaran')) {
            $request->merge([
                'tahun_ajaran' => $defaultTahunAjaran, 
                'semester' => $defaultSemester,
                'mode' => 'kelas',
                // Default Filter Baru
                'show_ranking' => '0', // Sembunyikan Ranking
                'sort_by' => 'absen'   // Urutkan Absen
            ]);
        }

        // 2. Ambil Parameter Request
        $mode = $request->mode;
        $id_kelas = $request->id_kelas;
        $jurusan = $request->jurusan;
        $semesterRaw = $request->semester;
        $tahun_ajaran = $request->tahun_ajaran;
        
        // Parameter Filter Baru
        $showRanking = $request->show_ranking ?? '0';
        $sortBy = $request->sort_by ?? 'absen';

        // Validasi Logika: Jika Ranking Disembunyikan, Paksa Sort by Absen
        if ($showRanking == '0') {
            $sortBy = 'absen';
        }
        
        $semesterInt = $this->mapSemesterToInt($semesterRaw);

        // 3. Cek apakah filter terisi
        $kelasIds = $this->getKelasIdsFromRequest($request);

        // Default Kosong
        $daftarMapel = collect();
        $dataLedger = collect();

        if (!empty($kelasIds)) {
            // Panggil Core Logic
            $coreData = $this->buildDataCore($kelasIds, $semesterInt, $tahun_ajaran);
            
            $daftarMapel = $coreData['daftarMapel'];
            
            // HITUNG RANKING & SORTING AKHIR
            $dataLedger = $this->calculateRankingAndSort($coreData['dataLedger'], $sortBy);
        }

        $semesterList = ['Ganjil', 'Genap'];
        $kbm = 75;

        return view('rapor.ledger_index', compact(
            'kelas',
            'jurusanList',
            'tingkatList',
            'mode',
            'id_kelas',
            'jurusan',
            'semesterRaw',
            'tahun_ajaran',
            'daftarMapel',
            'dataLedger',
            'tahunAjaranList',
            'semesterList',
            'defaultSemester',
            'defaultTahunAjaran',
            'kbm',
            'showRanking', // Variable untuk View
            'sortBy'       // Variable untuk View
        ));
    }

    /**
     * Export Excel
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(
            new LedgerTemplateExport($request), 
            $this->buildFilename($request, 'xlsx')
        );
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);

        $semesterRaw = $request->semester ?? 'Ganjil';
        $semesterInt = $this->mapSemesterToInt($semesterRaw);
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';

        $kelasIds = $this->getKelasIdsFromRequest($request);
        
        $core = $this->buildDataCore($kelasIds, $semesterInt, $tahun_ajaran);
        
        // Sorting PDF (Default Ranking agar rapi / Sesuai Request bisa disesuaikan)
        $sortBy = $request->sort_by ?? 'ranking'; 
        $dataLedger = $this->calculateRankingAndSort($core['dataLedger'], $sortBy);

        $infoSekolah = InfoSekolah::first();
        $namaSekolah = $infoSekolah->nama_sekolah ?? 'NAMA SEKOLAH';
        $alamatSekolah = implode(', ', array_filter([
            $infoSekolah->jalan ?? null,
            $infoSekolah->kelurahan ?? null,
            $infoSekolah->kecamatan ?? null,
            $infoSekolah->kota_kab ?? null
        ]));

        $kelasObj = $core['kelasObj'];
        $namaKelasLabel = ($request->mode == 'jurusan') 
            ? 'Jurusan ' . $request->jurusan 
            : ($kelasObj->nama_kelas ?? '-');

        $nama_wali = $kelasObj->wali_kelas ?? '-';
        $nip_wali  = '-';

        $dataView = [
            'namaSekolah'   => $namaSekolah,
            'alamatSekolah' => $alamatSekolah,
            'kelas'         => (object)['nama_kelas' => $namaKelasLabel],
            'daftarMapel'   => $core['daftarMapel'],
            'dataLedger'    => $dataLedger,
            'semesterRaw'   => $semesterRaw,
            'tahun_ajaran'  => $tahun_ajaran,
            'nama_wali'     => $nama_wali,
            'nip_wali'      => $nip_wali,
            'showRanking'   => $request->show_ranking ?? '1' // PDF biasanya tampil ranking
        ];

        $pdf = Pdf::loadView('rapor.ledger_pdf', $dataView)
            ->setPaper('a4', 'landscape');

        return $pdf->stream($this->buildFilename($request, 'pdf'));
    }

    /**
     * PUBLIC API: Digunakan oleh LedgerTemplateExport (EXCEL)
     */
    public function buildLedgerData(Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = $this->mapSemesterToInt($semesterRaw);

        $kelasIds = $this->getKelasIdsFromRequest($request);
        $core = $this->buildDataCore($kelasIds, $semesterInt, $tahun_ajaran);

        $infoSekolah = InfoSekolah::first();
        $data['namaSekolah'] = $infoSekolah->nama_sekolah ?? 'SEKOLAH';
        $data['alamatSekolah'] = $infoSekolah->alamat ?? '';
        
        $data['semester'] = $semesterRaw;
        $data['tahun_ajaran'] = $tahun_ajaran;
        
        $kelasObj = $core['kelasObj'];
        $data['namaKelas'] = $kelasObj ? $kelasObj->nama_kelas : 'Semua';
        $data['waliKelas'] = $kelasObj ? $kelasObj->wali_kelas : '-';

        // Excel biasanya urut ranking
        $data['dataLedger'] = $this->calculateRankingAndSort($core['dataLedger'], 'ranking');
        $data['daftarMapel'] = $core['daftarMapel'];

        return $data;
    }
}