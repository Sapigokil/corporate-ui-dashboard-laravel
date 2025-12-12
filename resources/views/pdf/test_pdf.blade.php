<!DOCTYPE html>
<html>
<head>
    <title>Laporan PDF Corporate</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            background-color: #004d99; /* Warna Corporate Biru */
            color: white;
            padding: 20px;
            margin-bottom: 30px;
        }
        h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 0 40px;
        }
        .text-corporate {
            border-left: 5px solid #004d99;
            padding-left: 15px;
            margin-top: 20px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            padding: 10px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Uji Coba Corporate UI</h1>
    </div>

    <div class="content">
        <h2>Informasi Dasar</h2>
        <p>Dokumen ini adalah hasil uji coba pembuatan PDF menggunakan Dompdf dengan tema Corporate UI sederhana.</p>

        <div class="text-corporate">
            <h3>Pernyataan Corporate</h3>
            <p>
                "Dengan fokus pada efisiensi dan profesionalisme, kami berkomitmen untuk menyajikan data dengan kejelasan dan integritas tertinggi. Dokumen ini adalah representasi dari komitmen tersebut."
            </p>
            <p><strong>Tanggal Dibuat:</strong> {{ date('d F Y') }}</p>
        </div>

        <p>Dompdf akan merender HTML dan CSS ini menjadi format Portable Document Format (PDF).</p>
    </div>

    <div class="footer">
        Halaman 1 dari 1 | Dibuat oleh Sistem Otomatis
    </div>
</body>
</html>