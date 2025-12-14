<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\Guru;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $mapel = MataPelajaran::with('guru')->orderBy('kategori', 'asc')->orderBy('urutan', 'asc')->get();
        $dataSekolahOpen = true;

        return view('mapel.index', compact('mapel', 'dataSekolahOpen'));
    }

    public function create()
    {
        $guru = Guru::all();
        $dataSekolahOpen = true;

        $kategoriList = [
            1 => 'Mata Pelajaran Umum',
            2 => 'Mata Pelajaran Kejuruan',
            3 => 'Mata Pelajaran Pilihan',
            4 => 'Muatan Lokal',
        ];

        return view('mapel.create', compact('guru', 'kategoriList', 'dataSekolahOpen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_mapel' => 'required',
            'nama_singkat' => 'required',
            // 'kelompok' => 'nullable',
            'kategori' => 'required',
            'urutan' => 'required|numeric',
            'id_guru' => 'nullable|exists:guru,id_guru',
        ]);

        MataPelajaran::create($request->all());

        return redirect()->route('master.mapel.index')->with('success','Mata Pelajaran berhasil ditambahkan');
    }

    public function edit($id_mapel)
    {
        $mapel = MataPelajaran::findOrFail($id_mapel);
        $guru = Guru::all();
        $dataSekolahOpen = true;

        $kategoriList = [
            1 => 'Mata Pelajaran Umum',
            2 => 'Mata Pelajaran Kejuruan',
            3 => 'Mata Pelajaran Pilihan',
            4 => 'Muatan Lokal',
        ];

        return view('mapel.edit', compact('mapel','kategoriList','guru','dataSekolahOpen'));
    }

    public function update(Request $request, $id_mapel)
    {
        $request->validate([
            'nama_mapel' => 'required',
            'nama_singkat' => 'required',
            // 'kelompok' => 'required',
            'kategori' => 'required',
            'urutan' => 'required|numeric',
            'id_guru' => 'nullable|exists:guru,id_guru',
        ]);

        MataPelajaran::where('id_mapel',$id_mapel)->update($request->except('_token','_method'));

        return redirect()->route('master.mapel.index')->with('success','Data berhasil diperbarui');
    }

    public function destroy($id_mapel)
    {
        MataPelajaran::destroy($id_mapel);
        return back()->with('success','Data berhasil dihapus');
    }
}
