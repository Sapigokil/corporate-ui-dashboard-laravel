<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Kelas</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        h3 {
            margin-bottom: 4px;
        }
        .sub-header {
            margin-bottom: 12px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        th {
            background: #f2f2f2;
        }
        td.text-left {
            text-align: left;
        }
    </style>
</head>
<body>

    <h3>Data Kelas (sesuai Tingkat dan Jurusan)</h3>

    <div class="sub-header">
        Wali Kelas
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Kelas</th>
                <th>Tingkat</th>
                <th>Jurusan</th>
                <th>Wali Kelas</th>
                <th>Jumlah Siswa</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($kelas as $i => $k)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="text-left">{{ $k->nama_kelas }}</td>
                <td>{{ $k->tingkat }}</td>
                <td>{{ $k->jurusan ?? '-' }}</td>
                <td class="text-left">{{ $k->wali_kelas ?? '-' }}</td>
                <td>{{ $k->siswas_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
