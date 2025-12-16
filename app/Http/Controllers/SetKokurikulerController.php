<?php

namespace App\Http\Controllers;

use App\Models\SetKokurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetKokurikulerController extends Controller
{
    /**
     * Menampilkan daftar template kokurikuler
     */
    public function index()
    {
        $data = SetKokurikuler::orderBy('created_at', 'desc')->get();
        return view('data.kok_index', compact('data'));
    }

    /**
     * Menyimpan template baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'tingkat'   => 'required',
            'judul'     => 'required|string|max:150',
            'deskripsi' => 'required|string',
        ]);

        SetKokurikuler::create([
            'tingkat'   => $request->tingkat,
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'aktif'     => $request->has('aktif') ? 1 : 0,
            'user'      => Auth::user()->name,
        ]);

        return back()->with('success', 'Template Kokurikuler berhasil ditambahkan!');
    }

    /**
     * Memperbarui data template
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tingkat'   => 'required',
            'judul'     => 'required|string|max:150',
            'deskripsi' => 'required|string',
        ]);

        $kok = SetKokurikuler::findOrFail($id);
        $kok->update([
            'tingkat'   => $request->tingkat,
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'aktif'     => $request->has('aktif') ? 1 : 0,
            'user'      => Auth::user()->name,
        ]);

        return back()->with('success', 'Template Kokurikuler berhasil diperbarui!');
    }

    /**
     * Menghapus template
     */
    public function destroy($id)
    {
        $kok = SetKokurikuler::findOrFail($id);
        $kok->delete();

        return back()->with('success', 'Template Kokurikuler berhasil dihapus!');
    }

    /**
     * Mengubah status aktif secara cepat (AJAX atau Simple Toggle)
     */
    public function toggleStatus($id)
    {
        $kok = SetKokurikuler::findOrFail($id);
        $kok->aktif = !$kok->aktif;
        $kok->save();

        return back()->with('success', 'Status berhasil diubah!');
    }
}