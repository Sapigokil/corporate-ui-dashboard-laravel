{{-- File: resources/views/pembelajaran/index.blade.php (REVISI FINAL UNTUK KERAPIAN Aksi) --}}
@extends('layouts.app') 

@section('title', 'Data Pembelajaran Mata Pelajaran per Kelas')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3"><i class="fas fa-chalkboard-teacher me-2"></i> Data Pembelajaran</h6>
                                <div class="pe-3">
                                    <a href="{{ route('master.pembelajaran.create') }}" class="btn btn-sm btn-light mb-0">
                                        <i class="fas fa-plus me-1"></i> Tambah Pembelajaran
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            
                            {{-- Notifikasi --}}
                            @if (session('success'))
                                <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert bg-gradient-danger alert-dismissible text-white fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- Tombol Export --}}
                            <div class="d-flex justify-content-end mb-3">
                                <a href="{{ route('master.pembelajaran.export.pdf') }}" class="btn btn-sm btn-info me-2 text-white">
                                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                                </a>
                                <a href="{{ route('master.pembelajaran.export.csv') }}" class="btn btn-sm btn-secondary text-white">
                                    <i class="fas fa-file-csv me-1"></i> Export CSV
                                </a>
                            </div>

                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">No</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mata Pelajaran</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kelas Terdampak</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Guru Pengampu</th>
                                            <th class="text-secondary opacity-7" style="min-width: 150px;">Aksi</th> {{-- Tetapkan lebar minimum untuk stabilitas --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $groupedPembelajaran = $pembelajaran->groupBy('id_mapel');
                                            $no = 1;
                                        @endphp
                                        
                                        @forelse ($groupedPembelajaran as $id_mapel => $items)
                                            @php
                                                $rowspan = $items->count();
                                                $mapel_name = $items->first()->mapel->nama_mapel ?? 'Mapel Tidak Ditemukan';
                                            @endphp
                                            
                                            @foreach ($items as $p)
                                                <tr>
                                                    {{-- Kolom Mata Pelajaran (Hanya muncul sekali per kelompok) --}}
                                                    @if ($loop->first)
                                                        <td class="align-middle text-center" rowspan="{{ $rowspan }}">
                                                            <p class="text-xs font-weight-bold mb-0">{{ $no++ }}</p>
                                                        </td>
                                                        <td rowspan="{{ $rowspan }}" class="align-middle">
                                                            <p class="text-sm font-weight-bold mb-0 text-primary">{{ $mapel_name }}</p>
                                                        </td>
                                                    @endif
                                                    
                                                    {{-- Kolom Detail Kelas --}}
                                                    <td class="align-middle">
                                                        <p class="text-xs font-weight-bold mb-0">{{ $p->kelas->nama_kelas ?? 'Kelas Tidak Ditemukan' }}</p>
                                                    </td>
                                                    
                                                    {{-- Kolom Detail Guru --}}
                                                    <td class="align-middle">
                                                        <p class="text-xs font-weight-bold mb-0">{{ ($p->id_guru == 0) ? 'Belum Ditentukan' : ($p->guru->nama_guru ?? '-') }}</p>
                                                    </td>
                                                    
                                                    {{-- Kolom Aksi (PENTING: Mengatur posisi tombol) --}}
                                                    <td class="align-middle">
                                                        
                                                        <div class="d-flex align-items-center">
                                                            {{-- Tombol Edit Massal (Hanya muncul sekali, dan menempati ruang yang sama di semua baris) --}}
                                                            <div style="min-width: 50px;"> {{-- Placeholder lebar tombol Edit --}}
                                                                @if ($loop->first)
                                                                    <a href="{{ route('master.pembelajaran.edit', $p->id_pembelajaran) }}" 
                                                                       class="btn btn-link text-warning p-0 m-0 text-xs" 
                                                                       title="Edit Tautan Massal Mapel Ini">
                                                                        <i class="fas fa-pencil-alt me-1"></i> Edit
                                                                    </a>
                                                                @else
                                                                    {{-- Placeholder agar tombol Hapus tidak bergeser --}}
                                                                    &nbsp;
                                                                @endif
                                                            </div>
                                                            
                                                            {{-- Aksi Hapus (Delete) --}}
                                                            <form action="{{ route('master.pembelajaran.destroy', $p->id_pembelajaran) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Yakin hapus tautan ini ({{ $mapel_name }} di {{ $p->kelas->nama_kelas ?? 'Kelas ini' }})?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-link text-danger p-0 m-0 text-xs" title="Hapus Tautan Tunggal">
                                                                    <i class="fas fa-trash-alt me-1"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </div>

                                                    </td>
                                                </tr>
                                            @endforeach
                                            
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Tidak ada data pembelajaran yang ditemukan.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            <x-app.footer />
        </div>
        
    </main>
@endsection