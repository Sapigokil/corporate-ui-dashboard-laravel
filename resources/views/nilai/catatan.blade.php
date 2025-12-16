{{-- File: resources/views/nilai/catatan.blade.php --}}

@extends('layouts.app')

@section('title', 'Catatan Wali Kelas')

@php
    $request = request();
    
    // --- LOGIKA TAHUN AJARAN & SEMESTER ---
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
    
    $tahunMulai = 2025; 
    $tahunAkhir = date('Y') + 5; 
    $tahunAjaranList = [];
    for ($tahun = $tahunAkhir; $tahun >= $tahunMulai; $tahun--) {
        $tahunAjaranList[] = $tahun . '/' . ($tahun + 1);
    }
    $semesterList = ['Ganjil', 'Genap']; 
    $dataEkskul = $dataEkskulTersimpan ?? [];
@endphp

@section('content')
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <x-app.navbar />

    <div class="container-fluid py-4 px-5">
        <div class="row">
            <div class="col-12">
                <div class="card my-4 shadow-xs border">
                    
                    {{-- HEADER OFFSET BIRU (IDENTIK DENGAN SUMATIF/PROJECT) --}}
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-white text-capitalize ps-3 mb-0">
                                <i class="fas fa-clipboard-check me-2"></i> Input Catatan & Absensi Wali Kelas
                            </h6>
                            <div class="pe-3">
                                {{-- Tombol Download Template --}}
                                <button class="btn bg-gradient-light text-dark btn-sm mb-0 me-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#downloadTemplateModal">
                                    <i class="fas fa-file-excel me-1"></i> Download Template
                                </button>
                                
                                {{-- Tombol Import --}}
                                <button class="btn bg-gradient-success btn-sm mb-0" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#importModal">
                                    <i class="fas fa-file-import me-1"></i> Import
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pb-2">
                        {{-- NOTIFIKASI --}}
                        @if (session('success'))
                            <div class="alert bg-gradient-success mx-4 alert-dismissible text-white fade show" role="alert">
                                <span class="text-sm"><strong>Sukses!</strong> {!! session('success') !!}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert bg-gradient-danger mx-4 alert-dismissible text-white fade show" role="alert">
                                <span class="text-sm"><strong>Gagal!</strong> {{ session('error') }}</span>
                                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        {{-- FILTER DATA --}}
                        <div class="p-4 border-bottom">
                            <form method="GET" action="{{ route('master.catatan.input') }}" class="row align-items-end">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold text-uppercase">Pilih Kelas</label>
                                    <select name="id_kelas" id="kelasSelect" required class="form-select border ps-2">
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach ($kelas as $k)
                                            <option value="{{ $k->id_kelas }}" {{ $request->id_kelas == $k->id_kelas ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bold text-uppercase">Pilih Siswa</label>
                                    <select name="id_siswa" id="siswaSelect" required class="form-select border ps-2">
                                        <option value="">-- Pilih Siswa --</option>
                                        @foreach ($siswa as $s)
                                            <option value="{{ $s->id_siswa }}" {{ $request->id_siswa == $s->id_siswa ? 'selected' : '' }}>
                                                {{ $s->nama_siswa }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold text-uppercase">Tahun Ajaran</label>
                                    <select name="tahun_ajaran" class="form-select border ps-2">
                                        @foreach ($tahunAjaranList as $ta)
                                            <option value="{{ $ta }}" {{ request('tahun_ajaran', $defaultTahunAjaran) == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label text-xs font-weight-bold text-uppercase">Semester</label>
                                    <select name="semester" class="form-select border ps-2">
                                        @foreach ($semesterList as $sem)
                                            <option value="{{ $sem }}" {{ request('semester', $defaultSemester) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3 text-end">
                                    <button type="submit" class="btn bg-gradient-dark w-100 mb-0 text-capitalize">Tampilkan</button>
                                </div>
                            </form>
                        </div>

                        {{-- INPUT FORM AREA --}}
                        <div class="p-4">
                            @if (!$request->id_kelas || !$request->id_siswa)
                                <div class="text-center py-5 border rounded bg-gray-100">
                                    <i class="fas fa-filter text-secondary mb-3 fa-2x"></i>
                                    <p class="text-secondary mb-0">Silakan pilih filter di atas untuk mulai mengisi catatan rapor siswa.</p>
                                </div>
                            @else
                                <form action="{{ route('master.catatan.simpan') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_kelas" value="{{ $request->id_kelas }}">
                                    <input type="hidden" name="id_siswa" value="{{ $request->id_siswa }}">
                                    <input type="hidden" name="tahun_ajaran" value="{{ $request->tahun_ajaran }}">
                                    <input type="hidden" name="semester" value="{{ $request->semester }}">

                                    <div class="row">
                                        {{-- SISI KIRI: PENGEMBANGAN DIRI --}}
                                        <div class="col-lg-7 border-end">
                                            <h6 class="text-uppercase text-primary text-xs font-weight-bolder opacity-7 mb-3">I. Aspek Pengembangan Diri</h6>
                                            
                                            <div class="mb-4">
                                                <label class="form-label font-weight-bold">1. Kokurikuler</label>
                                                <select id="levelKokurikuler" class="form-select border mb-2 ps-2">
                                                    <option value="">-- Pilih Capaian Otomatis --</option>
                                                    <option value="berkembang">Berkembang</option>
                                                    <option value="cakap">Cakap</option>
                                                    <option value="mahir">Mahir</option>
                                                </select>
                                                <div class="input-group input-group-outline is-filled">
                                                    <textarea id="kokurikulerText" name="kokurikuler" rows="4" class="form-control text-sm">{{ old('kokurikuler', $rapor->kokurikuler ?? $templateKokurikuler) }}</textarea>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label font-weight-bold text-dark d-flex align-items-center">
                                                    <i class="fas fa-star text-warning me-2"></i> 2. Ekstrakurikuler (Maks 3)
                                                </label>
                                                <div class="table-responsive border border-radius-lg shadow-sm">
                                                    <table class="table align-items-center mb-0">
                                                        <thead class="bg-dark text-white text-center">
                                                            <tr>
                                                                <th class="text-xxs font-weight-bolder opacity-9 text-uppercase ps-3 py-3 text-white">Nama Ekstrakurikuler</th>
                                                                <th class="text-xxs font-weight-bolder opacity-9 text-uppercase py-3 text-white" width="180px">Predikat</th>
                                                                <th class="text-xxs font-weight-bolder opacity-9 text-uppercase py-3 text-white">Keterangan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white">
                                                            @for ($i = 0; $i < 3; $i++)
                                                                @php
                                                                    $savedId = $dataEkskul[$i]['id_ekskul'] ?? '';
                                                                    $savedPred = $dataEkskul[$i]['predikat'] ?? '';
                                                                    $savedKet = $dataEkskul[$i]['keterangan'] ?? '';
                                                                    $rowBg = ($i % 2 == 0) ? 'bg-white' : 'bg-gray-50';
                                                                @endphp
                                                                <tr class="{{ $rowBg }}">
                                                                    <td class="p-2 ps-3 text-center">
                                                                        <select name="ekskul[{{ $i }}][id_ekskul]" class="form-select border-0 text-xs font-weight-bold">
                                                                            <option value="">-- Pilih Ekskul --</option>
                                                                            @foreach($ekskul as $e)
                                                                                <option value="{{ $e->id_ekskul }}" {{ $savedId == $e->id_ekskul ? 'selected' : '' }}>{{ $e->nama_ekskul }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td class="p-2 text-center">
                                                                        <select name="ekskul[{{ $i }}][predikat]" class="form-select border-0 text-xs font-weight-bold text-primary">
                                                                            <option value="">-- Pilih --</option>
                                                                            <option value="Sangat Baik" {{ $savedPred == 'Sangat Baik' ? 'selected' : '' }}>Sangat Baik</option>
                                                                            <option value="Baik" {{ $savedPred == 'Baik' ? 'selected' : '' }}>Baik</option>
                                                                            <option value="Cukup" {{ $savedPred == 'Cukup' ? 'selected' : '' }}>Cukup</option>
                                                                            <option value="Kurang" {{ $savedPred == 'Kurang' ? 'selected' : '' }}>Kurang</option>
                                                                        </select>
                                                                    </td>
                                                                    <td class="p-2">
                                                                        <input type="text" name="ekskul[{{ $i }}][keterangan]" class="form-control form-control-sm border-0 bg-transparent text-xs" placeholder="..." value="{{ $savedKet }}">
                                                                    </td>
                                                                </tr>
                                                            @endfor
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- SISI KANAN: ABSENSI & CATATAN --}}
                                        <div class="col-lg-5">
                                            <h6 class="text-uppercase text-primary text-xs font-weight-bolder opacity-7 mb-3">II. Absensi & Catatan Wali</h6>
                                            <div class="bg-light p-3 border-radius-lg mb-4">
                                                <label class="text-xs font-weight-bold text-dark text-uppercase">Ketidakhadiran (Hari)</label>
                                                <div class="row text-center mt-2">
                                                    <div class="col-4">
                                                        <label class="text-xxs">Sakit</label>
                                                        <input type="number" name="sakit" class="form-control form-control-sm border text-center" value="{{ $rapor->sakit ?? 0 }}">
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="text-xxs">Ijin</label>
                                                        <input type="number" name="ijin" class="form-control form-control-sm border text-center" value="{{ $rapor->ijin ?? 0 }}">
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="text-xxs">Alpha</label>
                                                        <input type="number" name="alpha" class="form-control form-control-sm border text-center" value="{{ $rapor->alpha ?? 0 }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label font-weight-bold text-dark">Catatan Wali Kelas</label>
                                                <div class="input-group input-group-outline is-filled">
                                                    <textarea name="catatan_wali_kelas" class="form-control text-sm" rows="6" placeholder="Tulis catatan perkembangan siswa di sini...">{{ $rapor->catatan_wali_kelas ?? '' }}</textarea>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn bg-gradient-success w-100 py-2">
                                                <i class="fas fa-save me-2 text-xs"></i> Simpan Seluruh Data
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-app.footer />
    </div>

    {{-- MODAL DOWNLOAD TEMPLATE --}}
    <div class="modal fade" id="downloadTemplateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-semibold">Download Template Catatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('master.catatan.template') }}" method="GET">
                    <div class="modal-body">
                        <p class="text-secondary text-sm">Gunakan template ini untuk meng-import catatan & absensi secara massal.</p>
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Kelas</label>
                            <select name="id_kelas" required class="form-select border ps-2">
                                <option value="">Pilih Kelas</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Semester</label>
                            <select name="semester" required class="form-select border ps-2">
                                @foreach($semesterList as $sem)
                                    <option value="{{ $sem }}" {{ (request('semester') ?? $defaultSemester) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-uppercase">Tahun Ajaran</label>
                            <select name="tahun_ajaran" required class="form-select border ps-2">
                                @foreach ($tahunAjaranList as $ta)
                                    <option value="{{ $ta }}" {{ (request('tahun_ajaran') ?? $defaultTahunAjaran) == $ta ? 'selected' : '' }}>{{ $ta }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn bg-gradient-info btn-sm">Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL IMPORT --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-semibold" id="importModalLabel">Import Catatan Wali Kelas</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('master.catatan.import') }}" method="POST" enctype="multipart/form-data"> 
                    @csrf
                    <div class="modal-body">
                        <p class="text-secondary font-weight-bold text-center mb-4">
                            Pastikan Excel sesuai dengan Template yang telah diunduh.
                        </p>
                        
                        <div class="mb-3">
                            <label for="id_kelas_import" class="form-label text-xs font-weight-bold text-uppercase">Kelas:</label>
                            <select name="id_kelas" id="id_kelas_import" required class="form-select border ps-2">
                                <option value="">Pilih Kelas</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id_kelas }}" {{ request('id_kelas') == $k->id_kelas ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="semester_import" class="form-label text-xs font-weight-bold text-uppercase">Semester:</label>
                            <select name="semester" id="semester_import" required class="form-select border ps-2">
                                @foreach($semesterList as $sem)
                                    <option value="{{ $sem }}" {{ (request('semester') ?? $defaultSemester) == $sem ? 'selected' : '' }}>
                                        {{ $sem }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tahun_ajaran_import" class="form-label text-xs font-weight-bold text-uppercase">Tahun Ajaran:</label>
                            <select name="tahun_ajaran" id="tahun_ajaran_import" required class="form-select border ps-2">
                                @foreach ($tahunAjaranList as $ta)
                                    <option value="{{ $ta }}" {{ (request('tahun_ajaran') ?? $defaultTahunAjaran) == $ta ? 'selected' : '' }}>
                                        {{ $ta }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="file_excel" class="form-label text-xs font-weight-bold text-uppercase d-block">Pilih File Excel:</label>
                            <input type="file" name="file_excel" id="file_excel" required class="form-control border ps-2" accept=".xlsx, .xls">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm bg-gradient-success">Lanjutkan Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- LOADING OVERLAY --}}
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; color: white; font-size: 1.5rem; z-index: 9999;">
        <div class="spinner-border text-light me-3" role="status"><span class="visually-hidden">Loading...</span></div>
        Sedang memproses... Mohon tunggu.
    </div>

</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logika Refresh halaman saat ganti kelas
        const kelasSelect = document.getElementById('kelasSelect');
        if(kelasSelect) {
            kelasSelect.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('id_kelas', this.value);
                url.searchParams.delete('id_siswa'); 
                window.location.href = url.toString();
            });
        }

        // JS Template Auto-fill (Client Side)
        const templates = {
            X: { 
                berkembang: "Ananda sudah berkembang dalam menyusun refleksi kesehatan dan kebugaran.", 
                cakap: "Ananda sudah cakap dalam menyusun refleksi kesehatan dan kebugaran secara mandiri.", 
                mahir: "Ananda sudah mahir dalam menyusun refleksi kesehatan dan kebugaran serta mampu membimbing teman sejawat." 
            },
            XI: { 
                berkembang: "Ananda sudah berkembang dalam menghayati ajaran agama dan kebiasaan hebat.", 
                cakap: "Ananda sudah cakap dalam menghayati ajaran agama dan menyampaikan informasi kebiasaan hebat.", 
                mahir: "Ananda sudah mahir dalam menghayati ajaran agama serta menjadi teladan kebiasaan hebat." 
            }
        };

        const currentKelas = "{{ ($siswaTerpilih && $siswaTerpilih->id_kelas == 4) ? 'X' : 'XI' }}";

        const levelKokurikuler = document.getElementById('levelKokurikuler');
        if(levelKokurikuler) {
            levelKokurikuler.addEventListener('change', function() {
                if(this.value && templates[currentKelas]) {
                    document.getElementById('kokurikulerText').value = templates[currentKelas][this.value];
                    document.getElementById('kokurikulerText').parentElement.classList.add('is-filled');
                }
            });
        }

        // Overlay & Submit Protection
        const importForm = document.querySelector('#importModal form');
        if (importForm) {
            importForm.addEventListener('submit', function() {
                document.getElementById('loadingOverlay').style.display = 'flex';
                this.querySelector('button[type="submit"]').disabled = true;
            });
        }
    });
</script>
@endsection