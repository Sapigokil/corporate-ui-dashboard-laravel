<!DOCTYPE html>
<html>
<head>
    <title>Laporan Hasil Belajar</title>
    @php
        $labelKategori = [
            1 => 'MATA PELAJARAN UMUM',
            2 => 'MATA PELAJARAN KEJURUAN',
            3 => 'MATA PELAJARAN PILIHAN',
            4 => 'MUATAN LOKAL'
        ];
    @endphp
    <style>
        /* 1. Reset Global & Page Setup */
        * { box-sizing: border-box; }

        @page {
            /* Margin 50px kiri-kanan adalah batas aman standar printer */
            margin: 160px 50px 80px 50px;
        }      

        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 11pt; 
            line-height: 1.3; 
            margin: 0; 
            padding: 0; 
            width: 100%;
        }

        /* 2. Header & Footer (Must be before <main>) */
        header {
            position: fixed;
            top: -140px; 
            left: 0px;
            right: 0px;
            height: 125px;
            border-bottom: 2px solid #000;
            background-color: white;
            z-index: 1000;
            width: 100%;
        }

        footer {
            position: fixed;
            bottom: -50px;
            left: 0px;
            right: 0px;
            height: 2px;
            border-top: 1px solid #000;
            z-index: 1000;
        }

        /* 3. Tabel Header - Tanpa Nested Table agar tidak overflow */
        .header-table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; /* Memaksa kolom mengikuti lebar tetap */
            font-size: 10pt;
            margin: 0;
        }

        /* Definisi Lebar Kolom (Total 100%) */
        .col-1 { width: 85px; }   /* Label Kiri */
        .col-2 { width: 15px; }   /* Titik Dua Kiri */
        .col-3 { width: 230px; }  /* Value Kiri (Nama/Sekolah) */
        .col-4 { width: 20px; }   /* Spacer */
        .col-5 { width: 110px; }  /* Label Kanan */
        .col-6 { width: 15px; }   /* Titik Dua Kanan */
        .col-7 { width: auto; }   /* Value Kanan (Kelas/Fase) */

        .header-table td { 
            vertical-align: top; 
            padding: 1px 0; 
            word-wrap: break-word;
            overflow: hidden;
        }
        
        /* 4. Konten Utama */
        main {
            width: 100%;
        }

        .judul-rapor {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 15px;
            margin-top: 0;
        }

        .main-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            margin-bottom: 20px;
            table-layout: fixed;
        }

        .main-table th, .main-table td { 
            border: 1px solid black; 
            padding: 5px 8px; 
            font-size: 9pt; 
            word-wrap: break-word;
        }
        
        .bg-light { background-color: #f2f2f2; }
        .font-bold { font-weight: bold; }
        .text-center { text-align: center !important; }
        .text-justify { text-align: justify; }

        .keep-together { 
            page-break-inside: avoid; 
            display: block;
            width: 100%;
        }

        .table-ttd { width: 100%; margin-top: 20px; border-collapse: collapse; table-layout: fixed; }
        .table-ttd td { border: none !important; text-align: center; vertical-align: top; font-size: 9.5pt; }
        
        .ttd-kepsek { width: 100%; margin-top: 20px; text-align: center; }
        .nama-kepsek {
            font-weight: bold;
            text-decoration: underline;
            display: block;
            margin-top: 60px;
        }
    </style>
</head>
<body>

    <header>
        <table class="header-table">
            <tr>
                <td class="col-1">Nama</td>
                <td class="col-2 text-center">:</td>
                <td class="col-3 font-bold">{{ strtoupper($siswa->nama_siswa) }}</td>
                <td class="col-4"></td>
                <td class="col-5">Kelas</td>
                <td class="col-6 text-center">:</td>
                <td class="col-7">{{ $siswa->kelas->nama_kelas }}</td>
            </tr>
            <tr>
                <td class="col-1">NIS/NISN</td>
                <td class="col-2 text-center">:</td>
                <td class="col-3">{{ $siswa->nipd }} / {{ $siswa->nisn }}</td>
                <td class="col-4"></td>
                <td class="col-5">Fase</td>
                <td class="col-6 text-center">:</td>
                <td class="col-7">{{ $siswa->kelas->fase ?? '-' }}</td>
            </tr>
            <tr>
                <td class="col-1">Sekolah</td>
                <td class="col-2 text-center">:</td>
                <td class="col-3">{{ $infoSekolah->nama_sekolah ?? 'SMKN 1 SALATIGA' }}</td>
                <td class="col-4"></td>
                <td class="col-5">Semester</td>
                <td class="col-6 text-center">:</td>
                <td class="col-7">{{ $semesterInt }} ({{ $semester }})</td>
            </tr>
            <tr>
                <td class="col-1">Alamat</td>
                <td class="col-2 text-center">:</td>
                <td class="col-3" style="font-size: 8.5pt;">{{ $infoSekolah->alamat ?? '-' }}</td>
                <td class="col-4"></td>
                <td class="col-5">Tahun Pelajaran</td>
                <td class="col-6 text-center">:</td>
                <td class="col-7">{{ $tahun_ajaran }}</td>
            </tr>
        </table>
    </header>

    <footer></footer>

    <main>
        <div class="judul-rapor">LAPORAN HASIL BELAJAR</div>

        <table class="main-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">No</th>
                    <th class="text-center" style="width: 180px;">Mata Pelajaran</th>
                    <th class="text-center" style="width: 60px;">Nilai Akhir</th>
                    <th class="text-center">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mapelGroup as $kategori => $mapels)
                    <tr class="bg-light font-bold">
                        <td colspan="4" style="text-transform: uppercase;">{{ $labelKategori[$kategori] ?? $kategori }}</td>
                    </tr>
                    @foreach($mapels as $m)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $m->nama_mapel }}</td>
                        <td class="text-center">{{ number_format((float)$m->nilai_akhir, 0, '', '') }}</td>
                        <td class="text-justify" style="font-size: 8.5pt;">{{ $m->capaian }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <div class="keep-together">
            <table class="main-table">
                <thead><tr class="bg-light text-center"><th class="font-bold">KOKURIKULER</th></tr></thead>
                <tbody><tr><td class="text-justify" style="min-height: 50px;">{{ $catatan->kokurikuler ?? '-' }}</td></tr></tbody>
            </table>
        </div>

        <div class="keep-together">
            <table class="main-table">
                <thead>
                    <tr class="bg-light text-center">
                        <th style="width: 40px;">No</th>
                        <th style="width: 200px;">Kegiatan Ekstrakurikuler</th>
                        <th style="width: 80px;">Predikat</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < 3; $i++)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $dataEkskul[$i]->nama ?? '-' }}</td>
                        <td class="text-center">{{ $dataEkskul[$i]->predikat ?? '-' }}</td>
                        <td>{{ $dataEkskul[$i]->keterangan ?? '-' }}</td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div class="keep-together">
            <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                <tr>
                    <td style="width: 48%; vertical-align: top; padding-right: 10px;">
                        <table class="main-table" style="margin-top: 0;">
                            <thead><tr class="bg-light text-center"><th colspan="2">Ketidakhadiran</th></tr></thead>
                            <tbody>
                                <tr><td>Sakit</td><td class="text-center">{{ $catatan->sakit ?? 0 }} hari</td></tr>
                                <tr><td>Izin</td><td class="text-center">{{ $catatan->ijin ?? 0 }} hari</td></tr>
                                <tr><td>Tanpa Keterangan</td><td class="text-center">{{ $catatan->alpha ?? 0 }} hari</td></tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="width: 52%; vertical-align: top;">
                        <table class="main-table" style="margin-top: 0;">
                            <thead><tr class="bg-light text-center"><th>Catatan Wali Kelas</th></tr></thead>
                            <tbody>
                                <tr><td style="height: 78px; vertical-align: top; font-style: italic;">{{ $catatan->catatan_wali_kelas ?? '-' }}</td></tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="keep-together">
            <table class="table-ttd">
                <tr>
                    <td style="width: 33%;">Mengetahui,<br>Orang Tua/Wali,<br><br><br><br>..........................</td>
                    <td style="width: 33%;"></td>
                    <td style="width: 33%;">Salatiga, 19 Desember 2025<br>Wali Kelas,<br><br><br><br><strong>{{ $nama_wali }}</strong><br>NIP. {{ $nip_wali }}</td>
                </tr>
            </table>
            <div class="ttd-kepsek">
                Mengetahui,<br>Kepala Sekolah
                <span class="nama-kepsek">{{ $info_sekolah->nama_kepsek ?? 'NAMA KEPALA SEKOLAH' }}</span>
                <span>NIP. {{ $info_sekolah->nip_kepsek ?? '-' }}</span>
            </div>
        </div>
    </main>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("helvetica", "italic");
            $size = 8;
            $width = $pdf->get_width();
            $height = $pdf->get_height();
            $y = $height - 35;
            $marginSide = 50;

            $leftText = "{{ $siswa->kelas->nama_kelas }} / {{ strtoupper($siswa->nama_siswa) }} / {{ $siswa->nipd }}";
            $pdf->page_text($marginSide, $y, $leftText, $font, $size, array(0,0,0));

            $rightText = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $pdf->page_text($width - $marginSide - 100, $y, $rightText, $font, $size, array(0,0,0));
        }
    </script>

</body>
</html>