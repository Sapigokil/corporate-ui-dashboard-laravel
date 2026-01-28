<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
    @page {
        margin: 140px 30px 40px 30px;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
    }

    /* ===== HEADER ===== */
    .header {
        position: fixed;
        top: -100px;
        left: 0;
        right: 0;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    .header-table td {
        border: none;
        text-align: left !important;
    }

    .school-name {
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .school-address {
        font-size: 9px;
        line-height: 1.3;
    }

    .ledger-title {
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        text-align: right;
    }

    .ledger-info {
        font-size: 10px;
        text-align: right;
        margin-top: 4px;
    }

    /* ===== TABLE ===== */
    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; 
    }

    th, td {
        border: 1px solid #444; /* Border sedikit lebih abu agar tidak terlalu keras */
        padding: 4px 2px;
        text-align: center;
        vertical-align: middle;
        word-wrap: break-word; 
        overflow-wrap: break-word;
    }

    th {
        font-weight: bold;
        font-size: 9px;
        color: #333;
    }

    /* WARNA SOFT HEADER */
    .bg-basic   { background-color: #eeeeee; } /* Abu-abu muda (No, Nama, NIS) */
    .bg-mapel   { background-color: #dbe5f1; } /* Biru muda soft (Mapel) */
    .bg-summary { background-color: #fde9d9; } /* Oranye muda (Total, Rata) */
    .bg-absen   { background-color: #eaf1dd; } /* Hijau muda (Absen) */
    .bg-rank    { background-color: #ffe0b2; } /* Kuning/Oranye soft (Rank) */
    
    /* WARNA DATA */
    .bg-rank-data { background-color: #fff2cc; font-weight: bold; }

    .text-left {
        text-align: left;
        padding-left: 4px;
    }
    
    .space-ttd {
        height: 70px;
    }

    .font-bold {
        font-weight: bold;
    }

    .ttd-table {
        width: 100%;
        margin-top: 40px;
    }

    .ttd-table td {
        border: none;
        text-align: left;
        vertical-align: top;
    }
    </style>
</head>
<body>

{{-- HEADER (muncul di setiap halaman) --}}
<div class="header">
    <table class="header-table" style="table-layout: auto;">
        <tr>
            <td width="60%">
                <table cellpadding="0" cellspacing="0" style="table-layout: auto;">
                    <tr>
                        {{-- Logo Sekolah --}}
                        <td width="70" style="vertical-align: middle; border: none;">
                            <img src="{{ public_path('assets/img/theme/logo-sekolah-sml.png') }}" width="75">
                        </td>
                        <td style="padding-left:6px; vertical-align: middle; text-align: left; border: none;">
                            <div class="school-name">{{ $namaSekolah }}</div>
                            <div class="school-address">
                                {{ $alamatSekolah }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            <td width="40%" style="vertical-align: middle;">
                <div class="ledger-title">Daftar Nilai Ledger Siswa</div>
                <div class="ledger-info">
                    Kelas: {{ $kelas->nama_kelas ?? '-' }} |
                    Semester: {{ $semesterRaw }} |
                    Tahun Ajaran: {{ $tahun_ajaran }}
                </div>
            </td>
        </tr>
    </table>
</div>

@php
    $jumlahMapel = count($daftarMapel);
    $tampilRanking = isset($showRanking) && $showRanking == '1';

    // =========================================================
    // CONFIG WIDTH (Sama seperti sebelumnya)
    // =========================================================
    
    $wNo   = 3;   
    $wNama = 15;
    $wId   = 5;
    
    $wTotal = 4;
    $wRata  = 4;
    
    $wAbsen = 2.5;
    $wRank  = 4;

    $totalFixed = $wNo + $wNama + ($wId * 2) + $wTotal + $wRata + ($wAbsen * 3);
    
    if ($tampilRanking) {
        $totalFixed += $wRank;
    }
    
    $sisaLebar = 100 - $totalFixed;
    
    if ($jumlahMapel > 0) {
        $wMapel = $sisaLebar / $jumlahMapel;
    } else {
        $wMapel = 5; 
    }
@endphp

<table>
    <thead>
        <tr>
            {{-- DATA SISWA (Warna Abu Lembut) --}}
            <th class="bg-basic" style="width: {{ $wNo }}%;">No</th>
            <th class="bg-basic" style="width: {{ $wNama }}%;">Nama Siswa</th>
            <th class="bg-basic" style="width: {{ $wId }}%;">NIS</th>
            <th class="bg-basic" style="width: {{ $wId }}%;">NISN</th>

            {{-- MAPEL (Warna Biru Lembut) --}}
            @foreach($daftarMapel as $mp)
                <th class="bg-mapel" style="width: {{ $wMapel }}%;">
                    <div style="font-size: 8px;">
                        {{ $mp->nama_singkat ?? $mp->nama_mapel }}
                    </div>
                </th>
            @endforeach

            {{-- NILAI (Warna Oranye Lembut) --}}
            <th class="bg-summary" style="width: {{ $wTotal }}%;">Total</th>
            <th class="bg-summary" style="width: {{ $wRata }}%;">Rata</th> 
            
            {{-- ABSENSI (Warna Hijau Lembut) --}}
            <th class="bg-absen" style="width: {{ $wAbsen }}%;">S</th>
            <th class="bg-absen" style="width: {{ $wAbsen }}%;">I</th>
            <th class="bg-absen" style="width: {{ $wAbsen }}%;">A</th>

            {{-- RANKING (Warna Kuning/Gold Lembut) --}}
            @if($tampilRanking)
                <th class="bg-rank" style="width: {{ $wRank }}%;">Rank</th>
            @endif
        </tr>
    </thead>

    <tbody>
        @foreach($dataLedger as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="text-left" style="font-size: 9px;">
                {{ $row->nama_siswa }}
            </td>

            <td style="font-size: 9px;">{{ $row->nipd ?? '-' }}</td>
            <td style="font-size: 9px;">{{ $row->nisn ?? '-' }}</td>

            {{-- NILAI MAPEL --}}
            @foreach($daftarMapel as $mp)
                @php
                    $nilai = $row->scores[$mp->id_mapel] ?? 0;
                @endphp
                <td style="font-size: 9px;">
                    {{ $nilai > 0 ? (int) $nilai : '-' }}
                </td>
            @endforeach

            {{-- REKAP --}}
            <td style="font-weight:bold;">{{ (int) $row->total }}</td>
            <td style="font-weight:bold;">{{ number_format($row->rata_rata, 1) }}</td>
            
            {{-- ABSENSI --}}
            <td>{{ $row->absensi->sakit }}</td>
            <td>{{ $row->absensi->izin }}</td>
            <td>{{ $row->absensi->alpha }}</td>

            {{-- RANKING (Tanpa #) --}}
            @if($tampilRanking)
                <td class="bg-rank-data">
                    {{ $row->ranking_no ?? '-' }}
                </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

{{-- TANDA TANGAN --}}
<table class="ttd-table" style="table-layout: auto;">
    <tr>
        <td style="width: 75%; border: none;"></td>
        <td style="width: 25%; border: none;">
            Salatiga, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
            Wali Kelas,
            <div class="space-ttd"></div>
            <span style="font-weight:bold; text-decoration: underline;">
                {{ $nama_wali }}
            </span><br>
            NIP. {{ $nip_wali }}
        </td>
    </tr>
</table>

</body>
</html>