<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Catatan;
use App\Models\StatusRapor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RaporController extends Controller
{
    /**
     * Halaman Monitoring Progres Per Mata Pelajaran
     */
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $id_kelas = $request->id_kelas;
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL') ? 1 : 2;

        $monitoring = [];

        if ($id_kelas) {
            $pembelajaran = DB::table('pembelajaran')
                ->leftJoin('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel') 
                ->where('pembelajaran.id_kelas', $id_kelas)
                ->select('pembelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
                ->get();

            if ($pembelajaran->isNotEmpty()) {
                $totalSiswaKelas = DB::table('siswa')->where('id_kelas', $id_kelas)->count();

                foreach ($pembelajaran as $mp) {
                    $namaMapel = $mp->nama_mapel ?? "Mapel ID: " . $mp->id_mapel;

                    $siswaTuntasIds = DB::table(function ($query) use ($mp, $semesterInt, $tahun_ajaran) {
                        $query->select('id_siswa')
                            ->from('sumatif')
                            ->where('id_mapel', $mp->id_mapel)
                            ->where('semester', $semesterInt)
                            ->where('tahun_ajaran', $tahun_ajaran)
                            ->where('nilai', '>', 0)
                            ->unionAll(
                                DB::table('project')
                                    ->select('id_siswa')
                                    ->where('id_mapel', $mp->id_mapel)
                                    ->where('semester', $semesterInt)
                                    ->where('tahun_ajaran', $tahun_ajaran)
                                    ->where('nilai', '>', 0)
                            );
                    }, 'combined_grades')
                    ->select('id_siswa', DB::raw('count(*) as total'))
                    ->groupBy('id_siswa')
                    ->having('total', '>=', 1) // Konsisten dengan Sinkronisasi (Minimal 1 Nilai)
                    ->pluck('id_siswa');

                    $monitoring[] = (object)[
                        'id_mapel' => $mp->id_mapel,
                        'nama_mapel' => $namaMapel,
                        'tuntas' => $siswaTuntasIds->count(),
                        'belum' => $totalSiswaKelas - $siswaTuntasIds->count(),
                        'total_siswa' => $totalSiswaKelas
                    ];
                }
            }
        }

        return view('rapor.index_rapor', compact('kelas', 'monitoring', 'id_kelas', 'semesterRaw', 'tahun_ajaran'));
    }

    /**
     * AJAX: Mendapatkan daftar nama siswa untuk Modal Detail di Monitoring
     */
    public function getDetailSiswa(Request $request)
    {
        $id_mapel = $request->id_mapel;
        $id_kelas = $request->id_kelas;
        $tipe = $request->tipe; 
        $semester = (strtoupper($request->semester) == 'GANJIL') ? 1 : 2;
        $tahun_ajaran = $request->tahun_ajaran;

        $semuaSiswa = DB::table('siswa')
            ->where('id_kelas', $id_kelas)
            ->select('id_siswa', 'nama_siswa', 'nis')
            ->get();

        $tuntasIds = DB::table(function ($query) use ($id_mapel, $semester, $tahun_ajaran) {
            $query->select('id_siswa')
                ->from('sumatif')
                ->where('id_mapel', $id_mapel)
                ->where('semester', $semester)
                ->where('tahun_ajaran', $tahun_ajaran)
                ->where('nilai', '>', 0)
                ->unionAll(
                    DB::table('project')
                        ->select('id_siswa')
                        ->where('id_mapel', $id_mapel)
                        ->where('semester', $semester)
                        ->where('tahun_ajaran', $tahun_ajaran)
                        ->where('nilai', '>', 0)
                );
        }, 'combined_grades')
        ->select('id_siswa', DB::raw('count(*) as total'))
        ->groupBy('id_siswa')
        ->having('total', '>=', 1) // Konsisten dengan Sinkronisasi
        ->pluck('id_siswa')
        ->toArray();

        if ($tipe == 'tuntas') {
            $result = $semuaSiswa->whereIn('id_siswa', $tuntasIds);
        } else {
            $result = $semuaSiswa->whereNotIn('id_siswa', $tuntasIds);
        }

        return response()->json($result->values());
    }

    /**
     * Mesin Sinkronisasi: Update data ke tabel status_rapor
     */
    public function perbaruiStatusRapor($id_siswa, $semester, $tahun_ajaran)
    {
        $semesterInt = (strtoupper($semester) == 'GANJIL' || $semester == '1') ? 1 : 2;
        $siswa = Siswa::findOrFail($id_siswa);

        $daftarMapel = DB::table('pembelajaran')->where('id_kelas', $siswa->id_kelas)->pluck('id_mapel');
        $totalMapel = $daftarMapel->count();
        $mapelTuntas = 0;

        foreach ($daftarMapel as $id_mapel) {
            $sumatifCount = DB::table('sumatif')
                ->where('id_siswa', $id_siswa)
                ->where('id_mapel', $id_mapel)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', (string)$tahun_ajaran)
                ->where('nilai', '>', 0)
                ->count();

            $projectCount = DB::table('project')
                ->where('id_siswa', $id_siswa)
                ->where('id_mapel', $id_mapel)
                ->where('semester', $semesterInt)
                ->where('tahun_ajaran', (string)$tahun_ajaran)
                ->where('nilai', '>', 0)
                ->count();

            if (($sumatifCount + $projectCount) >= 1) { 
                $mapelTuntas++; 
            }
        }

        $isCatatanReady = DB::table('catatan')
            ->where('id_siswa', $id_siswa)
            ->where('semester', $semesterInt)
            ->where('tahun_ajaran', (string)$tahun_ajaran)
            ->whereNotNull('catatan_wali_kelas') 
            ->whereRaw("TRIM(catatan_wali_kelas) != ''") 
            ->exists();

        return StatusRapor::updateOrCreate(
            [
                'id_siswa' => $id_siswa, 
                'semester' => $semesterInt, 
                'tahun_ajaran' => (string)$tahun_ajaran
            ],
            [
                'id_kelas' => $siswa->id_kelas,
                'total_mapel_seharusnya' => $totalMapel,
                'mapel_tuntas_input' => $mapelTuntas,
                'is_catatan_wali_ready' => $isCatatanReady ? 1 : 0,
                'status_akhir' => ($mapelTuntas >= $totalMapel && $isCatatanReady) ? 'Siap Cetak' : 'Belum Lengkap'
            ]
        );
    }

    /**
     * Tombol Sinkronisasi Massal (Satu Kelas)
     */
    public function sinkronkanKelas(Request $request)
    {
        try {
            $id_kelas = $request->id_kelas;
            $semester = $request->semester;
            $tahun_ajaran = $request->tahun_ajaran;

            if (!$id_kelas) {
                return response()->json(['success' => false, 'message' => 'ID Kelas tidak ditemukan'], 400);
            }

            $daftarSiswa = Siswa::where('id_kelas', $id_kelas)->get();

            foreach ($daftarSiswa as $s) {
                $this->perbaruiStatusRapor($s->id_siswa, $semester, $tahun_ajaran);
            }

            return response()->json(['success' => true, 'message' => 'Sinkronisasi berhasil']);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Halaman Cetak Rapor Per Siswa
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
            $siswaList = Siswa::where('id_kelas', $id_kelas)
                ->orderBy('nama_siswa', 'asc')
                ->get();

            foreach ($siswaList as $s) {
                // Gunakan casting (int) dan trim() agar data terbaca sempurna
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
     * AJAX: Get Mapel by Kelas
     */
    public function getMapelByKelas($id_kelas)
    {
        $mapel = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('pembelajaran.id_kelas', $id_kelas)
            ->select('mata_pelajaran.id_mapel', 'mata_pelajaran.nama_mapel')
            ->get();

        return response()->json($mapel);
    }

    public function cetak_proses($id_siswa, Request $request)
    {
        $semesterRaw = $request->semester ?? 'Ganjil';
        $tahun_ajaran = $request->tahun_ajaran ?? '2025/2026';
        $semesterInt = (strtoupper($semesterRaw) == 'GANJIL' || $semesterRaw == '1') ? 1 : 2;

        $siswa = Siswa::with('kelas')->findOrFail($id_siswa);
        $infoSekolah = DB::table('info_sekolah')->first();

        $namaWali = $siswa->kelas->wali_kelas;
        $dataGuru = DB::table('guru')
        ->where('nama_guru', 'LIKE', '%' . $namaWali . '%')
        ->first();

        // Ambil Mata Pelajaran berdasarkan kategori (Umum / Kejuruan / Pilihan)
        // Asumsi: tabel mata_pelajaran memiliki kolom 'kategori'
        $mapelGroup = DB::table('pembelajaran')
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            ->where('pembelajaran.id_kelas', $siswa->id_kelas)
            ->select('mata_pelajaran.*')
            ->get()
            ->groupBy('kategori');

        foreach ($mapelGroup as $kategori => $daftarMapel) {
            foreach ($daftarMapel as $mp) {
                // Ambil Nilai Akhir (Rata-rata Sumatif & Project)
                $nilaiSumatif = DB::table('sumatif')
                    ->where(['id_siswa' => $id_siswa, 'id_mapel' => $mp->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])
                    ->avg('nilai') ?: 0;

                $nilaiProject = DB::table('project')
                    ->where(['id_siswa' => $id_siswa, 'id_mapel' => $mp->id_mapel, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])
                    ->avg('nilai') ?: 0;

                $mp->nilai_akhir = round(($nilaiSumatif + $nilaiProject) / 2);
                
                // Logika Capaian Kompetensi (Contoh sederhana)
                $mp->capaian = $mp->nilai_akhir >= 85 
                    ? "Menunjukkan penguasaan yang sangat baik dalam hal seluruh kompetensi."
                    : "Perlu penguatan dalam beberapa materi inti.";
            }
        }

        $catatan = DB::table('catatan')
            ->where(['id_siswa' => $id_siswa, 'semester' => $semesterInt, 'tahun_ajaran' => $tahun_ajaran])
            ->first();

        $dataEkskul = [];
        if ($catatan && !empty($catatan->ekskul)) {
            // 1. Split data string
            $ids = explode(',', $catatan->ekskul);       // Contoh: "3,6"
            $predikats = explode(',', $catatan->predikat); // Contoh: "A,B"
            $keterangans = explode('|', $catatan->keterangan); // Contoh: "Aktif|Sangat Baik"

            // 2. Loop dan cocokkan dengan tabel ekskul
            foreach ($ids as $index => $id) {
                $namaEkskul = DB::table('ekskul')->where('id_ekskul', trim($id))->value('nama_ekskul');
                
                if ($namaEkskul) {
                    $dataEkskul[] = (object)[
                        'nama' => $namaEkskul,
                        'predikat' => $predikats[$index] ?? '-',
                        'keterangan' => $keterangans[$index] ?? '-'
                    ];
                }
            }
        }    

        $data = [
            'siswa' => $siswa,
            'infoSekolah' => $infoSekolah,
            'nipd' => $siswa->nis,
            'nisn' => $siswa->nisn,
            'mapelGroup' => $mapelGroup,
            'catatan' => $catatan,
            'dataEkskul' => $dataEkskul,
            'semester' => $semesterRaw,
            'tahun_ajaran' => $tahun_ajaran,
            'semesterInt' => $semesterInt,
            'namaWali' => $namaWali,
            'nip_wali' => $dataGuru->nip ?? '-',
        ];

        $pdf = Pdf::loadView('rapor.pdf1_template', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('Rapor_'.$siswa->nama_siswa.'.pdf');
    }
}