<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin-top: 100px;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
        }

        .school-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .page-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
        }

        th {
            background-color: #eee;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="school-name">{{ $namaSekolah }}</div>
    <div class="page-title">DATA SISWA</div>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>NISN / NIPD</th>
            <th>Kelas</th>
            <th>Ekskul</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($siswas as $i => $siswa)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $siswa['nama'] }}</td>
            <td>{{ $siswa['nisn'] }} / {{ $siswa['nipd'] }}</td>
            <td>{{ $siswa['kelas'] }}</td>
            <td>{{ $siswa['ekskul'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
