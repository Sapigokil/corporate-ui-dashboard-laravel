@extends('layouts.app')

@section('page-title', 'Ledger Nilai Siswa')

@section('content')
<style>
    .table-responsive {
        max-height: 90vh;          
        overflow: auto;
        border-radius: 3px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .table-ledger {
        border-collapse: collapse !important;
        border-spacing: 0;
        width: 100%;
    }
    .table-ledger tbody tr:hover {
        background-color: #f9fafb;
    }

    .table-ledger thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        border: none !important;
        border-bottom: 0 !important;  
        padding: 6px 4px !important;
        text-align: center;
        vertical-align: middle;
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        line-height: 1.2;
    }

    /* HEADER KATEGORI (ROW 1) */
    .table-ledger thead tr:first-child th {
        top: 0;
        height: 32px;
        color: #fff;
        font-weight: 700;
        z-index: 30;
    }

    .table-ledger thead tr:nth-child(2) th.kategori-sub {
        background-color: #f8f9fa !important;
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid transparent;
        top: 27px; /* tinggi baris kategori */
        z-index: 25;
    }

    /* WARNA KATEGORI */
    .kategori-1 { background-color: #b0bec5 !important; } /* Umum */
    .kategori-2 { background-color: #b0bec5 !important; } /* Kejuruan */
    .kategori-3 { background-color: #b0bec5 !important; } /* Pilihan */
    .kategori-4 { background-color: #b0bec5 !important; } /* Mulok */
    .kategori-5 { background-color: #b0bec5 !important; } /* Rekap */
    .kategori-6 { background-color: #b0bec5 !important; } /* Absen */
    .kategori-7 { background-color: #fb8c00 !important; } /* Ranking (Orange) */

    /* Sub Header Warna */
    .table-ledger thead tr:nth-child(2) th.kategori-1 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-2 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-3 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-4 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-5 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-6 { background-color: #cfd8dc !important; color: #37474f; }
    .table-ledger thead tr:nth-child(2) th.kategori-7 { background-color: #ffe0b2 !important; color: #e65100; }

    /* Sticky Columns (No, Nama, NIS, NISN) */
    .sticky-col {
        position: sticky;
        left: 0;
        z-index: 10;
        background-color: #ffffff !important; 
        border-right: 1px solid #d0d7de !important;
        border-bottom: 1px solid #e9ecef !important;
    }
    
    .sticky-col-header {
        position: sticky;
        top: 0;
        left: 0;
        z-index: 40 !important;
        background-color: #37474f !important;
        color: #fff !important;
    }

    .col-nama {
        width: 220px !important;
        min-width: 220px !important;
        max-width: 220px !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .col-id {
        width: 100px !important;
        min-width: 100px !important;
        text-align: center;
    }
    .col-nilai {
        width: 55px !important;
        min-width: 55px !important;
        text-align: center;
        font-size: 0.85rem;
    }
    .bg-light-danger { background-color: #fde8e8 !important; color: #c81e1e !important; font-weight: bold; }
    .bg-rekap { background-color: #fff8e1 !important; color: #344767; }
    .bg-absen { background-color: #e3f2fd !important; color: #344767; }
    
    /* RANKING STYLE */
    .bg-ranking { background-color: #fff3e0 !important; font-weight: bold; color: #e65100; }

    .table-ledger tbody td {
        border: none !important;
        border-bottom: 1px solid #e0e0e0 !important;
        padding: 8px 6px;
    }
</style>

@php
    $tahunSekarang = date('Y');
    $bulanSekarang = date('n');

    if ($bulanSekarang < 7) {
        $defaultTA1 = $tahunSekarang - 1;
        $defaultTA2 = $tahunSekarang;
        $defaultSemester = 'Genap';
    } else {
        $defaultTA1 = $tahunSekarang;
        $defaultTA2 = $tahunSekarang + 1;
        $defaultSemester = 'Ganjil';
    }

    $defaultTahunAjaran = $defaultTA1 . '/' . $defaultTA2;

    $tahunMulai = $tahunSekarang - 3;
    $tahunAkhir = $tahunSekarang + 3;

    $tahunAjaranList = [];
    for ($tahun = $tahunAkhir; $tahun >= $tahunMulai; $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }

    $semesterList = ['Ganjil', 'Genap'];
    $tingkatList = ['10', '11', '12'];
@endphp

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />
    
    <div class="container-fluid py-4 px-5">
        <div class="card shadow-xs border mb-5">
            <div class="card-header bg-gradient-primary py-3">
                <h6 class="text-white mb-0"><i class="fas fa-table me-2"></i> Ledger Nilai Siswa</h6>
            </div>
            <div class="card-body">
                {{-- FORM FILTER LEDGER (STYLE CLEAN & AUTO-SUBMIT) --}}
                <div class="p-4 border-bottom">
                    <form action="{{ route('ledger.ledger_index') }}" method="GET" id="filterForm">
                        
                        {{-- ROW 1: FILTER UTAMA --}}
                        <div class="row align-items-end mb-3">
                            
                            {{-- 1. Mode Ledger --}}
                            <div class="col-md-2">
                                <label class="form-label">Mode Ledger:</label>
                                <select name="mode" class="form-select" onchange="this.form.submit()">
                                    <option value="kelas" {{ request('mode', 'kelas') == 'kelas' ? 'selected' : '' }}>Per Kelas</option>
                                    <option value="jurusan" {{ request('mode') == 'jurusan' ? 'selected' : '' }}>Per Jurusan</option>
                                </select>
                            </div>

                            {{-- 2. Kelas --}}
                            <div class="col-md-2">
                                <label class="form-label">Kelas:</label>
                                <select name="id_kelas" class="form-select" onchange="this.form.submit()"
                                    {{ request('mode', 'kelas') == 'jurusan' ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 3. Jurusan --}}
                            <div class="col-md-2">
                                <label class="form-label">Jurusan:</label>
                                <select name="jurusan" class="form-select" onchange="this.form.submit()"
                                    {{ request('mode', 'kelas') == 'kelas' ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Jurusan --</option>
                                    @foreach($jurusanList as $j)
                                        <option value="{{ $j }}" {{ request('jurusan') == $j ? 'selected' : '' }}>
                                            {{ $j }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 4. Tingkat --}}
                            <div class="col-md-2">
                                <label class="form-label">Tingkat:</label>
                                <select name="tingkat" class="form-select" onchange="this.form.submit()"
                                    {{ request('mode', 'kelas') == 'kelas' ? 'disabled' : '' }}>
                                    <option value="">-- Semua --</option>
                                    @foreach($tingkatList as $t)
                                        <option value="{{ $t }}" {{ request('tingkat') == $t ? 'selected' : '' }}>
                                            {{ $t }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 5. Semester --}}
                            <div class="col-md-2">
                                <label class="form-label">Semester:</label>
                                <select name="semester" class="form-select" onchange="this.form.submit()">
                                    @foreach($semesterList as $sem)
                                        <option value="{{ $sem }}" {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>
                                            {{ $sem }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 6. Tahun Ajaran --}}
                            <div class="col-md-2">
                                <label class="form-label">Tahun Ajaran:</label>
                                <select name="tahun_ajaran" class="form-select" onchange="this.form.submit()">
                                    @foreach($tahunAjaranList as $ta)
                                        <option value="{{ $ta }}" {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>
                                            {{ $ta }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- ROW 2: FILTER TAMPILAN & RANKING (BARU) --}}
                        <div class="row align-items-end">
                            
                            {{-- Filter A: Opsi Ranking --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Opsi Tampilan (Filter A)</label>
                                <select name="show_ranking" id="selectShowRanking" class="form-select" onchange="handleFilterChange()">
                                    <option value="0" {{ $showRanking == '0' ? 'selected' : '' }}>Sembunyikan Ranking</option>
                                    <option value="1" {{ $showRanking == '1' ? 'selected' : '' }}>Tampilkan Ranking</option>
                                </select>
                            </div>

                            {{-- Filter B: Urutkan Berdasar --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Urutkan Data (Filter B)</label>
                                <select name="sort_by" id="selectSortBy" class="form-select" onchange="this.form.submit()">
                                    <option value="absen" {{ $sortBy == 'absen' ? 'selected' : '' }}>Berdasarkan Absen (Nama)</option>
                                    <option value="ranking" {{ $sortBy == 'ranking' ? 'selected' : '' }}>Berdasarkan Ranking (Nilai)</option>
                                </select>
                            </div>

                        </div>

                        {{-- Hidden Submit Button --}}
                        <button type="submit" class="d-none"></button>
                    </form>
                </div>

                @if(
                    (request('mode','kelas') == 'kelas' && request('id_kelas')) ||
                    (request('mode') == 'jurusan' && request('jurusan'))
                )
                @php
                    $catLabels = [1 => 'Umum', 2 => 'Kejuruan', 3 => 'Pilihan', 4 => 'Mulok'];
                    $groupedMapel = $daftarMapel->groupBy('kategori');
                @endphp

                <div class="table-responsive p-0">
                    <table id="ledgerTable" class="table table-ledger align-items-center mb-0">
                        <thead>
                            <tr>
                                {{-- 1. HEADER NO & NAMA (STICKY) --}}
                                <th rowspan="2" class="sticky-col-header" style="width: 45px;">No</th>
                                <th rowspan="2" class="sticky-col-header col-nama">Nama Siswa</th>
                                <th rowspan="2" class="sticky-col-header col-id">NIS</th>
                                <th rowspan="2" class="sticky-col-header col-id">NISN</th>

                                {{-- 2. HEADER KATEGORI MAPEL --}}
                                @foreach($groupedMapel as $catId => $mapels)
                                    <th colspan="{{ count($mapels)}}" class="kategori-header kategori-{{ $catId }}">
                                        {{ $catLabels[$catId] ?? 'Lainnya' }}
                                    </th>
                                @endforeach

                                {{-- 3. HEADER REKAP & ABSEN --}}
                                <th colspan="2" class="kategori-header kategori-5">REKAP</th>
                                <th colspan="3" class="kategori-header kategori-6">ABSEN</th>

                                {{-- 4. HEADER RANKING (PALING BELAKANG) --}}
                                @if($showRanking == '1')
                                    <th rowspan="2" class="kategori-header kategori-7">RANK</th>
                                @endif
                            </tr>

                            <tr>
                                {{-- SUB HEADER NAMA MAPEL --}}
                                @foreach($groupedMapel as $catId => $mapels)
                                    @foreach($mapels as $mp)
                                        <th class="col-nilai kategori-sub kategori-{{ $catId }}" 
                                            data-bs-toggle="tooltip" title="{{ $mp->nama_mapel }}">
                                            {{ substr($mp->nama_singkat ?? $mp->nama_mapel, 0, 5) }}
                                        </th>
                                    @endforeach
                                @endforeach

                                {{-- SUB HEADER REKAP & ABSEN --}}
                                <th class="kategori-sub kategori-5">JML</th>
                                <th class="kategori-sub kategori-5">AVG</th>
                                <th class="kategori-sub kategori-6">S</th>
                                <th class="kategori-sub kategori-6">I</th>
                                <th class="kategori-sub kategori-6">A</th>
                            </tr>
                        </thead>
                        <tbody id="ledgerBody">
                            @forelse($dataLedger as $idx => $row)
                            <tr>
                                {{-- NO --}}
                                <td class="text-center text-sm sticky-col">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- NAMA SISWA --}}
                                <td class="text-sm sticky-col col-nama font-weight-bold text-dark"
                                    data-bs-toggle="tooltip" title="{{ $row->nama_siswa }}">
                                    {{ $row->nama_siswa }}
                                </td>

                                {{-- NIS & NISN --}}
                                <td class="text-sm text-center col-id sticky-col">
                                    {{ $row->nipd ?? '-' }}
                                </td>
                                <td class="text-sm text-center col-id sticky-col">
                                    {{ $row->nisn ?? '-' }}
                                </td>

                                {{-- NILAI MAPEL --}}
                                @foreach($groupedMapel as $catId => $mapels)
                                    @foreach($mapels as $mp)
                                        @php $val = $row->scores[$mp->id_mapel] ?? 0; @endphp
                                        <td class="col-nilai text-sm {{ $val <= 0 ? 'bg-light-danger' : '' }}">
                                            {{ $val > 0 ? (int)$val : '-' }}
                                        </td>
                                    @endforeach
                                @endforeach

                                {{-- REKAP --}}
                                <td class="col-nilai text-sm font-weight-bold bg-rekap">
                                    {{ (int)$row->total }}
                                </td>
                                <td class="col-nilai text-sm font-weight-bold text-primary bg-rekap avg-cell">
                                    {{ number_format($row->rata_rata, 1) }}
                                </td>

                                {{-- ABSEN --}}
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->sakit }}</td>
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->izin }}</td>
                                <td class="col-nilai text-sm text-secondary bg-absen">{{ $row->absensi->alpha }}</td>

                                {{-- RANKING (PALING BELAKANG) --}}
                                @if($showRanking == '1')
                                    <td class="col-nilai text-sm text-center bg-ranking">
                                        {{ $row->ranking_no }}
                                    </td>
                                @endif
                            </tr>
                            @empty
                            <tr><td colspan="100%" class="text-center py-4">Data tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- EXPORT BUTTON --}}
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('ledger.export.excel', request()->query()) }}"
                    class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i> Download Excel
                    </a>

                    <a href="{{ route('ledger.export.pdf', request()->query()) }}"
                    target="_blank"
                    class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf me-1"></i> Download PDF
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>

{{-- SCRIPT INTERAKSI --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeSelect = document.querySelector('select[name="mode"]');
    const tingkatSelect = document.querySelector('select[name="tingkat"]');

    function toggleTingkat() {
        if (modeSelect.value === 'jurusan') {
            tingkatSelect.removeAttribute('disabled');
        } else {
            tingkatSelect.setAttribute('disabled', 'disabled');
        }
    }

    if(modeSelect) {
        toggleTingkat();
        modeSelect.addEventListener('change', toggleTingkat);
    }

    // Logic Javascript untuk Filter Ranking
    const selectShowRanking = document.getElementById('selectShowRanking');
    const selectSortBy = document.getElementById('selectSortBy');
    const filterForm = document.getElementById('filterForm');

    // Inisialisasi state awal
    if(selectShowRanking) {
        if(selectShowRanking.value == '0') {
            selectSortBy.setAttribute('disabled', 'disabled');
        } else {
            selectSortBy.removeAttribute('disabled');
        }
    }

    window.handleFilterChange = function() {
        if (selectShowRanking.value == '0') {
            // Jika Sembunyikan Ranking -> Otomatis Sort by Absen & Disable pilihan
            selectSortBy.value = 'absen';
            selectSortBy.setAttribute('disabled', 'disabled');
            filterForm.submit();
        } else {
            // Jika Tampilkan Ranking -> Enable pilihan
            selectSortBy.removeAttribute('disabled');
            filterForm.submit();
        }
    };
});
</script>
@endsection