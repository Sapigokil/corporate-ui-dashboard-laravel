<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapelController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil status dari request, default '1' (Active)
        $statusFilter = $request->input('is_active', '1'); 

        $query = MataPelajaran::with('guru');

        // 2. Terapkan Filter Active
        if ($statusFilter !== 'all') {
            $query->where('is_active', $statusFilter);
        }

        // 3. Sorting Standar (Lebih Cepat & Bersih)
        // Karena tipe data sudah INT, cukup pakai orderBy biasa.
        $allMapel = $query->orderBy('urutan', 'asc')
            ->get()
            ->groupBy('kategori');

        // Label Kategori
        $kategoriLabel = [
            1 => 'Mata Pelajaran Umum',
            2 => 'Mata Pelajaran Kejuruan',
            3 => 'Mata Pelajaran Pilihan',
            4 => 'Muatan Lokal',
        ];

        return view('mapel.index', compact('allMapel', 'kategoriLabel', 'statusFilter'));
    }

    public function create()
    {
        $guru = Guru::all();
        $kategoriList = [
            1 => 'Mata Pelajaran Umum',
            2 => 'Mata Pelajaran Kejuruan',
            3 => 'Mata Pelajaran Pilihan',
            4 => 'Muatan Lokal',
        ];
        return view('mapel.create', compact('guru', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_mapel'    => 'required|string|max:255',
            'nama_singkat'  => 'required|string|max:50',
            'kategori'      => 'required|integer',
            'urutan'        => 'required|numeric|min:1',
            // 'id_guru' dihapus karena sudah tidak ada di form
            'is_active'     => 'required|in:0,1', // Tambahkan validasi ini
            'agama_khusus'  => 'nullable|string|in:Islam,Kristen,Katholik,Hindu,Budha,Khonghucu', 
        ]);

        // Simpan semua data request (termasuk is_active)
        MataPelajaran::create($request->all());

        return redirect()->route('master.mapel.index')->with('success','Mata Pelajaran berhasil ditambahkan');
    }

    public function edit($id_mapel)
    {
        $mapel = MataPelajaran::findOrFail($id_mapel);
        $guru = Guru::all();
        $kategoriList = [
            1 => 'Mata Pelajaran Umum',
            2 => 'Mata Pelajaran Kejuruan',
            3 => 'Mata Pelajaran Pilihan',
            4 => 'Muatan Lokal',
        ];
        return view('mapel.edit', compact('mapel','kategoriList','guru'));
    }

    public function update(Request $request, $id_mapel)
    {
        $request->validate([
            'nama_mapel'    => 'required|string|max:255',
            'nama_singkat'  => 'required|string|max:50',
            'kategori'      => 'required|integer',
            'urutan'        => 'required|numeric|min:1',
            // 'id_guru' dihapus
            'is_active'     => 'required|in:0,1', // Tambahkan validasi ini
            'agama_khusus'  => 'nullable|string|in:Islam,Kristen,Katholik,Hindu,Budha,Khonghucu',
        ]);

        // Update data (kecuali token & method)
        MataPelajaran::where('id_mapel', $id_mapel)->update($request->except('_token', '_method'));

        return redirect()->route('master.mapel.index')->with('success','Data berhasil diperbarui');
    }

    public function destroy($id_mapel)
    {
        MataPelajaran::destroy($id_mapel);
        return back()->with('success','Data berhasil dihapus');
    }

    /**
     * AJAX Handler untuk menyimpan urutan & kategori baru
     */
    public function updateUrutan(Request $request)
    {
        // Data yang dikirim: category_id (tujuan), items (array ID mapel yang sudah urut)
        $kategoriId = $request->kategori_id;
        $items = $request->items; // Array [id_mapel, id_mapel, ...]

        if (!$kategoriId || !is_array($items)) {
            return response()->json(['status' => 'error'], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($items as $index => $idMapel) {
                MataPelajaran::where('id_mapel', $idMapel)->update([
                    'kategori' => $kategoriId, // Update Kategori (jika pindah tabel)
                    'urutan'   => $index + 1   // Update Urutan (berdasarkan posisi baru)
                ]);
            }
            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}