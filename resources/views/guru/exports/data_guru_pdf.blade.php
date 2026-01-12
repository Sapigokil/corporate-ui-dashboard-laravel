<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Guru</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        h2 {
            text-align: center;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
        }
        th {
            background: #f0f0f0;
            text-align: center;
        }
    </style>
</head>
<body>

<h2>DATA GURU</h2>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Guru</th>
            <th>NIP</th>
            <th>NUPTK</th>
            <th>Jenis Kelamin</th>
            <th>Jenis PTK</th>
            <th>Role</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($guru as $i => $g)
            <tr>
                <td align="center">{{ $i + 1 }}</td>
                <td>{{ $g->nama_guru }}</td>
                <td>{{ $g->nip ?? '-' }}</td>
                <td>{{ $g->nuptk ?? '-' }}</td>
                <td>{{ $g->jenis_kelamin }}</td>
                <td>{{ $g->jenis_ptk }}</td>
                <td>{{ Str::title($g->role) }}</td>
                <td>{{ Str::title($g->status) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
