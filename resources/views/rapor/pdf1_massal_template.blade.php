<!DOCTYPE html>
<html>
<head>
    <title>Cetak Massal Rapor</title>
    <style>
        /* CSS Page Break */
        .page-break { page-break-after: always; }
        .page-break:last-child { page-break-after: never; }

        /* Copy SEMUA CSS dari pdf1_template ke sini agar konsisten */
        @page { margin: 50px 50px 50px 50px; }
        .header-fixed { position: fixed; top: 0px; left: 0px; right: 0px; height: 90px; border-bottom: 2px solid #000; background-color: white; z-index: 1000; }
        .footer-fixed { position: fixed; bottom: -30px; left: 0; right: 0; height: 40px; border-top: 1px solid #000; z-index: 1000; }
        body { font-family: 'Arial', sans-serif; font-size: 11pt; padding-top: 110px; padding-bottom: 50px; }
        /* ... Sertakan CSS tabel Anda yang lain di sini ... */
    </style>
</head>
<body>
    <div class="header-fixed">
        </div>
    <div class="footer-fixed"></div>

    @foreach($allData as $data)
        <div class="page-break">
            {{-- Panggil konten tabel saja --}}
            @include('rapor.pdf1_template_content', $data)
        </div>
    @endforeach

    <script type="text/php">
        if (isset($pdf)) {
            $font_italic = $fontMetrics->get_font("helvetica", "italic");
            $font_bold = $fontMetrics->get_font("helvetica", "bold");
            $size = 9;

            // Loop per halaman untuk mengisi Header dan Footer secara dinamis
            foreach ($pdf->get_pages() as $pageNumber => $page) {
                $pdf->reopen_object($page);
                
                // Tulis nomor halaman dan identitas di sini agar tidak bertumpuk
                // Ini memastikan teks hanya ditulis 1x per halaman
                
                $pdf->close_object();
            }
        }
    </script>
</body>
</html>