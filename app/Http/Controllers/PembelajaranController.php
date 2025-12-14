<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelajaran;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\Guru;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class PembelajaranController extends Controller
{
    // 🟩 Halaman utama
    public function dataPembelajaran() 
    {
        // Menggunakan join untuk mengakses kolom 'urutan' dari tabel mata_pelajaran
        $pembelajaran = Pembelajaran::select('pembelajaran.*') // Pilih semua kolom dari tabel pembelajaran
            ->join('mata_pelajaran', 'pembelajaran.id_mapel', '=', 'mata_pelajaran.id_mapel')
            
            // 🛑 PENGURUTAN UTAMA: Berdasarkan kolom 'urutan' di tabel mata_pelajaran
            ->orderBy('mata_pelajaran.urutan', 'asc')
            
            // PENGURUTAN SEKUNDER: Berdasarkan nama mapel (opsional, untuk mapel dengan urutan sama)
            ->orderBy('mata_pelajaran.nama_mapel', 'asc') 
            
            // Ambil relasi setelah join
            ->with(['mapel', 'kelas', 'guru'])
            ->get();

        // Ambil data untuk dropdown (tetap sama)
        $mapel = MataPelajaran::orderBy('nama_mapel')->get();
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $guru  = Guru::orderBy('nama_guru')->get();

        return view('pembelajaran.index', compact('pembelajaran', 'mapel', 'kelas', 'guru'));
    }

    // 🟫 Tampilkan form create
    public function create()
    {
        // 🛑 REVISI: Urutkan Mapel berdasarkan urutan, lalu nama
        $mapel = MataPelajaran::orderBy('urutan', 'asc')->orderBy('nama_mapel', 'asc')->get();
        
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $guru  = Guru::orderBy('nama_guru')->get();
        
        return view('pembelajaran.create', compact('mapel', 'kelas', 'guru'));
    }
    
    // 🟦 Simpan data pembelajaran (Mass Store dengan Guru Opsional melalui Placeholder)
    public function store(Request $request)
    {
        // 1. Validasi Input Dasar (ID Mapel)
        $request->validate([
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'kelas_guru' => 'required|array', 
        ]);
        
        // 🛑 ASUMSI ID GURU PLACEHOLDER: Anda harus memastikan ID 0 ada di tabel guru!
        $PLACEHOLDER_GURU_ID = 0; 

        $id_mapel = $request->id_mapel;
        $data_pembelajaran = $request->kelas_guru;
        $counter = 0;

        // 2. Loop dan Proses Data Jamak
        foreach ($data_pembelajaran as $data) {
            
            $id_kelas = $data['id_kelas'];
            // id_guru akan null jika tidak dicentang (karena input disable)
            $id_guru = $data['id_guru'] ?? $PLACEHOLDER_GURU_ID; 
            $is_active = isset($data['active']); 

            if ($is_active) {
                // Jika aktif, pastikan ID Guru valid (0 adalah placeholder)
                if (empty($id_guru) || $id_guru === "") {
                    $id_guru = $PLACEHOLDER_GURU_ID;
                }
                
                $existing = Pembelajaran::where('id_mapel', $id_mapel)
                                        ->where('id_kelas', $id_kelas)
                                        ->first();
                
                if (!$existing) {
                    // Create baru
                    Pembelajaran::create([
                        'id_mapel' => $id_mapel,
                        'id_kelas' => $id_kelas,
                        'id_guru'  => $id_guru, // Menggunakan ID valid atau Placeholder ID 0
                    ]);
                    $counter++;
                } else {
                    // Update guru yang ada
                    $existing->update(['id_guru' => $id_guru]);
                }
            } else {
                // Skema Delete: Jika Tidak Aktif, Hapus Record Pembelajaran
                Pembelajaran::where('id_mapel', $id_mapel)
                            ->where('id_kelas', $id_kelas)
                            ->delete();
            }
        }

        // 3. Tangani Hasil
        if ($counter > 0) {
            return redirect()->route('master.pembelajaran.index')
                ->with('success', "Berhasil menambahkan {$counter} tautan pembelajaran baru dan memperbarui data lainnya.");
        } else {
            return redirect()->route('master.pembelajaran.index')
                ->with('success', 'Perubahan pada tautan pembelajaran berhasil disimpan.');
        }
    }

    // 🟪 Tampilkan form edit (Mass Edit berdasarkan ID Mapel)
    public function edit($id_pembelajaran)
    {
        // 1. Ambil data Pembelajaran awal untuk mendapatkan ID Mapel
        $pembelajaran_awal = Pembelajaran::findOrFail($id_pembelajaran);
        $id_mapel_edit = $pembelajaran_awal->id_mapel;

        // 2. Ambil data Mata Pelajaran yang sedang diedit
        $mapel_edit = MataPelajaran::findOrFail($id_mapel_edit);

        // 3. Ambil data Master untuk dropdown
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $guru  = Guru::orderBy('nama_guru')->get();

        // 4. Ambil semua data Pembelajaran yang sudah ada untuk Mapel ini.
        $existing_pembelajaran = Pembelajaran::where('id_mapel', $id_mapel_edit)
                                            ->get()
                                            ->keyBy('id_kelas'); 
        
        return view('pembelajaran.edit', compact(
            'mapel_edit', 
            'kelas', 
            'guru', 
            'existing_pembelajaran',
            'pembelajaran_awal' 
        ));
    }

    // 🟨 Proses Update data pembelajaran (Mass Update)
    public function update(Request $request, $id_pembelajaran) 
    {
        $pembelajaran_awal = Pembelajaran::findOrFail($id_pembelajaran);
        $id_mapel_edit = $pembelajaran_awal->id_mapel;

        $request->validate([
            'kelas_guru' => 'required|array', 
        ]);

        $PLACEHOLDER_GURU_ID = 0; 
        $data_pembelajaran = $request->kelas_guru;
        $counter_created = 0;
        $counter_deleted = 0;
        $counter_updated = 0;

        foreach ($data_pembelajaran as $data) {
            
            $id_kelas = $data['id_kelas'];
            $id_guru = $data['id_guru'] ?? $PLACEHOLDER_GURU_ID; 
            $is_active = isset($data['active']); 

            if ($is_active) {
                // Jika aktif, pastikan ID Guru valid (placeholder 0)
                if (empty($id_guru) || $id_guru === "") {
                    $id_guru = $PLACEHOLDER_GURU_ID;
                }
                
                $existing = Pembelajaran::where('id_mapel', $id_mapel_edit)
                                        ->where('id_kelas', $id_kelas)
                                        ->first();
                
                if (!$existing) {
                    // Create baru
                    Pembelajaran::create([
                        'id_mapel' => $id_mapel_edit, 
                        'id_kelas' => $id_kelas,
                        'id_guru'  => $id_guru,
                    ]);
                    $counter_created++;
                } else {
                    // Update guru yang ada jika berbeda
                    if ($existing->id_guru != $id_guru) {
                        $existing->update(['id_guru' => $id_guru]);
                        $counter_updated++;
                    }
                }
            } else {
                // Skema Delete: Jika Tidak Aktif, Hapus Record Pembelajaran
                $deleted = Pembelajaran::where('id_mapel', $id_mapel_edit) 
                            ->where('id_kelas', $id_kelas)
                            ->delete();
                if($deleted) $counter_deleted++;
            }
        }

        $message = "Berhasil memperbarui tautan pembelajaran (Dibuat: $counter_created, Diperbarui: $counter_updated, Dihapus: $counter_deleted).";
        return redirect()->route('master.pembelajaran.index')->with('success', $message);
    }

    // 🟥 Hapus data pembelajaran
    public function destroy($id)
    {
        $pembelajaran = Pembelajaran::findOrFail($id);
        $pembelajaran->delete();

        return redirect()->route('master.pembelajaran.index')
            ->with('success', 'Data pembelajaran berhasil dihapus.');
    }

    public function exportPdf()
{
    $pembelajaran = Pembelajaran::with(['mapel', 'kelas', 'guru'])
        ->orderBy('id', 'asc')
        ->get();

    $pdf = Pdf::loadView('exports.data_pembelajaran_pdf', compact('pembelajaran'))
        ->setPaper('a4', 'landscape');

    return $pdf->download('data_pembelajaran.pdf');
}


public function exportCsv()
{
    $pembelajaran = Pembelajaran::with(['mapel', 'kelas', 'guru'])
        ->orderBy('id', 'asc')
        ->get();

    $filename = 'data_pembelajaran.csv';
    $handle = fopen($filename, 'w+');

    // Header kolom
    fputcsv($handle, ['No', 'Mata Pelajaran', 'Tingkat', 'Kelas', 'Jurusan', 'Guru Mapel']);

    foreach ($pembelajaran as $i => $p) {
        fputcsv($handle, [
            $i + 1,
            $p->mapel->nama_mapel ?? '-',
            $p->kelas->tingkat ?? '-',
            $p->kelas->nama_kelas ?? '-',
            $p->kelas->jurusan ?? '-',
            $p->guru->nama_guru ?? '-',
        ]);
    }

    fclose($handle);

    return Response::download($filename)->deleteFileAfterSend(true);
    }

}
