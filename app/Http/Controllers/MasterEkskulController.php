<?php
// File: app/Http/Controllers/MasterEkskulController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ekskul;
use App\Models\EkskulSiswa;
use App\Models\Guru;

class MasterEkskulController extends Controller
{
    // === 1. INDEX MASTER EKSKUL ===
    public function index()
    {
        // Menggunakan nama method index standar
        $ekskul = Ekskul::with(['guru', 'siswaEkskul'])->get();
        $guru = Guru::orderBy('nama_guru')->get();

        return view('ekskul.list_index', compact('ekskul', 'guru'));
    }

    // 2. Tampilkan Form Create Master Ekskul
    public function create()
    {
        $gurus = Guru::orderBy('nama_guru')->get();
        return view('ekskul.list_create', compact('gurus'));
    }

    // 3. Simpan Master Ekskul
    public function store(Request $request)
    {
        $request->validate([
            'nama_ekskul' => 'required|string|max:100',
            'id_guru' => 'nullable|numeric', 
            'jadwal_ekskul' => 'nullable|string|max:100',
        ]);
        
        $idGuruInput = $request->input('id_guru');
        $dataToCreate['id_guru'] = ($idGuruInput === "" || $idGuruInput === null || $idGuruInput == 0) ? null : $idGuruInput;
        $dataToCreate['nama_ekskul'] = $request->input('nama_ekskul');
        $dataToCreate['jadwal_ekskul'] = $request->input('jadwal_ekskul');

        Ekskul::create($dataToCreate);

        return redirect()->route('master.ekskul.list.index')->with('success', 'Data ekstrakurikuler baru berhasil ditambahkan.');
    }

    // 4. Tampilkan Form Edit Master Ekskul
    public function edit($id_ekskul)
    {
        $ekskul = Ekskul::findOrFail($id_ekskul);
        $gurus = Guru::orderBy('nama_guru')->get();
        return view('ekskul.list_edit', compact('ekskul', 'gurus'));
    }

    // 5. Update Master Ekskul
    public function update(Request $request, $id_ekskul)
    {
        $ekskul = Ekskul::findOrFail($id_ekskul);

        $validatedData = $request->validate([
            'nama_ekskul' => 'required|string|max:100',
            'id_guru' => 'nullable|numeric', 
            'jadwal_ekskul' => 'nullable|string|max:100',
        ]);
        
        $dataToUpdate = $request->only('nama_ekskul', 'jadwal_ekskul');
        
        $idGuruInput = $request->input('id_guru');
        $dataToUpdate['id_guru'] = ($idGuruInput === "" || $idGuruInput === null || $idGuruInput == 0) ? null : $idGuruInput;

        $ekskul->update($dataToUpdate);

        return redirect()->route('master.ekskul.list.index')->with('success', 'Data ekstrakurikuler berhasil diperbarui.');
    }

    // 6. Hapus Master Ekskul
    public function destroy($id_ekskul)
    {
        $ekskul = Ekskul::findOrFail($id_ekskul);
        
        EkskulSiswa::where('id_ekskul', $id_ekskul)->delete();
        
        $ekskul->delete();

        return redirect()->route('master.ekskul.list.index')->with('success', 'Data ekstrakurikuler dan semua pesertanya berhasil dihapus.');
    }
}