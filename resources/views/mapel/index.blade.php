@extends('layouts.app') 

@section('page-title', 'Data Master Mata Pelajaran')

@section('content')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        
        <x-app.navbar />
        
        <div class="container-fluid py-4 px-5"> 
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        {{-- HEADER --}}
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3 mb-0">
                                    <i class="fas fa-book me-2"></i> Data Master Mata Pelajaran
                                </h6>
                                
                                {{-- AREA KANAN: FILTER & TOMBOL TAMBAH --}}
                                <div class="pe-3 d-flex align-items-center gap-3">
                                    
                                    {{-- FORM FILTER STATUS --}}
                                    <form action="{{ route('master.mapel.index') }}" method="GET" class="d-flex align-items-center bg-white rounded p-1">
                                        <select name="is_active" class="form-select form-select-sm border-0 ps-2 pe-4" 
                                                style="outline: none; box-shadow: none;" 
                                                onchange="this.form.submit()">
                                            <option value="1" {{ $statusFilter == '1' ? 'selected' : '' }}>Tampilkan: Aktif</option>
                                            <option value="0" {{ $statusFilter == '0' ? 'selected' : '' }}>Tampilkan: Non-Aktif</option>
                                            <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>Tampilkan: Semua</option>
                                        </select>
                                    </form>

                                    <a href="{{ route('master.mapel.create') }}" class="btn btn-sm btn-light mb-0">
                                        <i class="fas fa-plus me-1"></i> Tambah
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body pb-2 px-4">
                            @if (session('success'))
                                <div class="alert bg-gradient-success alert-dismissible text-white fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- INSTRUKSI --}}
                            <div class="alert alert-light text-dark text-sm border mb-3" role="alert">
                                <i class="fas fa-info-circle me-2 text-info"></i> 
                                <strong>Info:</strong> Geser baris untuk mengubah urutan. Mapel yang non-aktif tidak akan muncul di Rapor.
                            </div>

                            {{-- LOOPING TABEL PER KATEGORI --}}
                            @foreach ($kategoriLabel as $key => $label)
                                @php
                                    $groupMapel = $allMapel[$key] ?? collect(); 
                                @endphp

                                <div class="mb-4 border rounded p-3 bg-white shadow-sm">
                                    <h6 class="font-weight-bolder text-uppercase text-xs mb-3 text-primary">
                                        <i class="fas fa-layer-group me-1"></i> {{ $label }} (Kategori {{ $key }})
                                    </h6>
                                    
                                    <div class="table-responsive p-0">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="width: 50px;">Geser</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="width: 50px;">Urut</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Mata Pelajaran</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Singkatan</th>
                                                    {{-- KOLOM PENGAMPU DIHAPUS --}}
                                                    <th class="text-secondary opacity-7">Status</th>
                                                    <th class="text-secondary opacity-7">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="sortable-table" data-kategori-id="{{ $key }}">
                                                @forelse ($groupMapel as $m)
                                                <tr data-id="{{ $m->id_mapel }}" class="bg-white">
                                                    {{-- HANDLE DRAG --}}
                                                    <td class="align-middle text-center cursor-move" style="cursor: move;">
                                                        <i class="fas fa-grip-vertical text-secondary"></i>
                                                    </td>
                                                    {{-- NOMOR URUT --}}
                                                    <td class="align-middle text-center">
                                                        <span class="badge bg-light text-dark urutan-badge">{{ $m->urutan }}</span>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">{{ $m->nama_mapel }}</p>
                                                        @if($m->agama_khusus)
                                                            <span class="badge bg-gradient-warning text-xxs py-1 px-2">Khusus {{ $m->agama_khusus }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        <p class="text-xs font-weight-bold mb-0">{{ $m->nama_singkat }}</p>
                                                    </td>
                                                    {{-- STATUS --}}
                                                    <td class="align-middle">
                                                        @if($m->is_active)
                                                            <span class="badge badge-sm bg-gradient-success">Active</span>
                                                        @else
                                                            <span class="badge badge-sm bg-gradient-secondary">Non-Active</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        {{-- Hanya Tombol Edit --}}
                                                        <a href="{{ route('master.mapel.edit', $m->id_mapel) }}" class="btn btn-link text-primary px-3 mb-0" title="Edit Data">
                                                            <i class="fas fa-pencil-alt text-dark me-2" aria-hidden="true"></i>Edit
                                                        </a>
                                                        {{-- TOMBOL DELETE DIHAPUS --}}
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr class="empty-row">
                                                    {{-- Colspan disesuaikan karena kolom berkurang --}}
                                                    <td colspan="6" class="text-center text-xs text-muted py-3">
                                                        Belum ada mapel di kategori ini.
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
            <x-app.footer />
        </div>
    </main>

    {{-- SCRIPT DRAG & DROP (TETAP SAMA) --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tables = document.querySelectorAll('.sortable-table');

            tables.forEach(function (table) {
                new Sortable(table, {
                    group: 'shared-mapel',
                    animation: 150,
                    handle: '.cursor-move',
                    ghostClass: 'bg-light',
                    
                    onEnd: function (evt) {
                        var itemEl = evt.item;
                        var newTarget = evt.to;
                        var kategoriId = newTarget.getAttribute('data-kategori-id');
                        
                        var itemIds = [];
                        var rows = newTarget.querySelectorAll('tr[data-id]');
                        
                        rows.forEach(function (row, index) {
                            itemIds.push(row.getAttribute('data-id'));
                            row.querySelector('.urutan-badge').textContent = index + 1;
                        });

                        var emptyRow = newTarget.querySelector('.empty-row');
                        if(rows.length > 0 && emptyRow) {
                            emptyRow.style.display = 'none';
                        }

                        $.ajax({
                            url: "{{ route('master.mapel.update_urutan') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                kategori_id: kategoriId,
                                items: itemIds
                            },
                            success: function (response) {
                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                });
                                Toast.fire({
                                    icon: 'success',
                                    title: 'Urutan diperbarui!'
                                });
                            },
                            error: function (xhr) {
                                Swal.fire('Error', 'Gagal menyimpan perubahan urutan.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection