<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\AnggotaKelas;
use App\Models\Guru;
use App\Models\DetailSiswa;
use App\Models\Siswa;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    // Halaman Data Kelas
    public function index()
    {
        $kelas = Kelas::orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->withCount('siswas') // Menambahkan kolom siswas_count
            ->get();

        return view('kelas.index', compact('kelas'));
    }

    /**
     * Halaman Index Data Kelas (versi rapi)
     */
    public function list()
    {
        // 🛑 Menggunakan withCount untuk menghitung jumlah siswa secara efisien
        $kelas = Kelas::orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->withCount('siswas') // Menambahkan kolom siswas_count
            ->get();

        return view('kelas.index', compact('kelas'));
    }

    public function show($id_kelas)
    {
        $kelas = Kelas::with('guru')->withCount('siswas')->findOrFail($id_kelas);

        $anggota = \App\Models\Siswa::where('id_kelas', $id_kelas)->get();

        return view('kelas.show', compact('kelas', 'anggota'));
    }

    public function edit($id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);
        $guru = \App\Models\Guru::orderBy('nama_guru')->get();
        return view('kelas.edit', compact('kelas', 'guru'));
    }

    public function create()
    {
        $guru = Guru::orderBy('nama_guru')->get(); // untuk dropdown wali kelas
        return view('kelas.create', compact('guru'));
    }


    // Simpan data kelas baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:100',
            'tingkat' => 'required|integer',
            'jurusan' => 'required|string',
            'wali_kelas' => 'required|string',
            // 'jumlah_siswa' => 'nullable|integer|min:0',
            // 'id_anggota' => 'nullable|integer',

        ]);

        Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'tingkat' => $request->tingkat,
            'jurusan' => $request->jurusan,
            'wali_kelas' => $request->wali_kelas,
            // 'jumlah_siswa' => $request->jumlah_siswa ?? 0,
            // 'id_anggota' => null, 
            // 'id_guru' => null, 

        ]);

        return redirect()->route('master.kelas.index')->with('success', 'Kelas berhasil ditambahkan!');
    }

    // Update data kelas
    public function update(Request $request, $id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:100',
            'tingkat' => 'required|string',
            'jurusan' => 'required|string',
            'wali_kelas' => 'required|string',
            'jumlah_siswa' => 'nullable|integer|min:0',
        ]);

        $kelas->update($validated);
        return redirect()->back()->with('success', 'Data kelas berhasil diperbarui.');
    }

     // Hapus data kelas
    public function destroy($id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);
        $kelas->delete();

        return redirect()->back()->with('success', 'Data kelas berhasil dihapus.');
    }

    public function anggota($id_kelas)
    {
        $kelas = Kelas::findOrFail($id_kelas);

        $anggota = Siswa::select(
                'siswa.nama_siswa',
                'siswa.nisn',
                'detail_siswa.id_kelas'
            )
            ->join('detail_siswa', 'detail_siswa.id_siswa', '=', 'siswa.id_siswa')
            ->where('detail_siswa.id_kelas', $id_kelas)
            ->get();

        return view('kelas.index', compact('kelas', 'anggota'));
    }



    // Tambah anggota ke kelas
    public function tambahAnggota(Request $request, $id_kelas)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nisn' => 'required|string|max:20|unique:anggota_kelas,nisn',
        ]);

        AnggotaKelas::create([
            'nama' => $validated['nama'],
            'nisn' => $validated['nisn'],
            'id_kelas' => $id_kelas,
        ]);

        // Update jumlah siswa otomatis
        Kelas::find($id_kelas)->update([
            'jumlah_siswa' => AnggotaKelas::where('id_kelas', $id_kelas)->count()
        ]);

        return redirect()->back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    // Hapus anggota tertentu
    public function hapusAnggota($id_siswa)
    {
        DetailSiswa::where('id_siswa', $id_siswa)->update([
            'id_kelas' => null
        ]);

        return back()->with('success', 'Anggota dihapus dari kelas.');
    }



    /**
     * 🔹 Export semua data kelas ke PDF
     */
    public function exportPdf()
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();

        $pdf = Pdf::loadView('exports.data_kelas_pdf', compact('kelas'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('data_kelas.pdf');
    }

    /**
     * 🔹 Export semua data kelas ke CSV
     */
    public function exportCsv()
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();

        $filename = 'data_kelas.csv';
        $handle = fopen($filename, 'w+');

        // Header
        fputcsv($handle, ['No', 'Nama Kelas', 'Tingkat', 'Jurusan', 'Wali Kelas', 'Jumlah Siswa']);

        foreach ($kelas as $i => $k) {
            fputcsv($handle, [
                $i + 1,
                $k->nama_kelas,
                $k->tingkat,
                $k->jurusan,
                $k->wali_kelas,
                $k->jumlah_siswa
            ]);
        }

        fclose($handle);

        return Response::download($filename)->deleteFileAfterSend(true);
    }

    /**
     * 🔹 Export data satu kelas (dengan anggota) ke PDF
     */
    public function exportKelas($id)
    {
        $kelas = Kelas::with('anggotaKelas')->findOrFail($id);

        $pdf = Pdf::loadView('exports.kelas_single_pdf', compact('kelas'))
            ->setPaper('a4', 'portrait');

        $filename = 'data_kelas_' . str_replace(' ', '_', strtolower($kelas->nama_kelas)) . '.pdf';

        return $pdf->download($filename);
    }
}
