<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Kelas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h3, h4 { text-align: center; margin: 10px 0; }
        h3 { font-size: 24px; } /* Data Kelas lebih besar */
        h4 { font-size: 20px; } /* Daftar Siswa sedikit lebih kecil */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>

<h3>Data Kelas</h3>

<p style="font-size:14px; line-height:1.5;">
    <span style="display:inline-block; width:120px;"><strong>Nama Kelas</strong></span>: {{ $kelas->nama_kelas }}<br>
    <span style="display:inline-block; width:120px;"><strong>Tingkat</strong></span>: {{ $kelas->tingkat }}<br>
    <span style="display:inline-block; width:120px;"><strong>Jurusan</strong></span>: {{ $kelas->jurusan }}<br>
    <span style="display:inline-block; width:120px;"><strong>Wali Kelas</strong></span>: {{ $kelas->wali_kelas }}
</p>

<h4>Daftar Siswa</h4>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>NISN</th>
            <th>Nama Siswa</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($kelas->siswas as $i => $siswa)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $siswa->nisn }}</td>
                <td>{{ $siswa->nama_siswa }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" align="center">Belum ada siswa</td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
