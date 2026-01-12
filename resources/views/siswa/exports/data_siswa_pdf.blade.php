<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; }
    </style>
</head>
<body>

<h3>DATA SISWA</h3>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>NISN / NIPD</th>
            <th>Kelas / Tingkat</th>
            <th>Ekskul</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($siswas as $siswa)
        <tr>
            <td>{{ $siswa['nipd'] }}</td>
            <td>{{ $siswa['nisn'] }}</td>
            <td>{{ $siswa['nama'] }}</td>
            <td>{{ $siswa['kelas'] }}</td>
            <td>{{ $siswa['tingkat'] }}</td>
        </tr>
    @endforeach

    </tbody>
</table>

</body>
</html>
