<table border="1">
    <colgroup>
        <col style="width: 35px;">  {{-- No --}}
        <col style="width: 200px;"> {{-- Nama --}}
        
        {{-- Kolom NIS & NISN --}}
        <col style="width: 100px;"> {{-- NIS --}}
        <col style="width: 100px;"> {{-- NISN --}}

        {{-- Kolom Mapel --}}
        @foreach($daftarMapel as $mp)
            <col style="width: 60px;"> 
        @endforeach

        <col style="width: 60px;"> {{-- Total --}}
        <col style="width: 60px;"> {{-- Rata-rata --}}
        <col style="width: 40px;"> {{-- S --}}
        <col style="width: 40px;"> {{-- I --}}
        <col style="width: 40px;"> {{-- A --}}

        {{-- [BARU] Kolom Ranking di Paling Belakang --}}
        @if(request('show_ranking', '0') == '1')
            <col style="width: 70px;">
        @endif
    </colgroup>

    <thead>
        <tr>
            {{-- HEADER: Hapus Rank dari depan --}}
            <th style="background-color: #eeeeee; font-weight: bold; text-align: center; vertical-align: middle;">No</th>
            <th style="background-color: #eeeeee; font-weight: bold; text-align: center; vertical-align: middle;">Nama Siswa</th>
            <th style="background-color: #eeeeee; font-weight: bold; text-align: center; vertical-align: middle;">NIS</th>
            <th style="background-color: #eeeeee; font-weight: bold; text-align: center; vertical-align: middle;">NISN</th>

            @foreach($daftarMapel as $mp)
                <th style="background-color: #dbe5f1; font-weight: bold; text-align: center; vertical-align: middle;">
                    {{ $mp->nama_singkat ?? $mp->nama_mapel }}
                </th>
            @endforeach

            <th style="background-color: #fde9d9; font-weight: bold; text-align: center; vertical-align: middle;">Total</th>
            <th style="background-color: #fde9d9; font-weight: bold; text-align: center; vertical-align: middle;">Rata-rata</th>
            <th style="background-color: #eaf1dd; font-weight: bold; text-align: center; vertical-align: middle;">S</th>
            <th style="background-color: #eaf1dd; font-weight: bold; text-align: center; vertical-align: middle;">I</th>
            <th style="background-color: #eaf1dd; font-weight: bold; text-align: center; vertical-align: middle;">A</th>

            {{-- [BARU] Header Ranking di Belakang --}}
            @if(request('show_ranking', '0') == '1')
                <th style="background-color: #ffc000; font-weight: bold; text-align: center; vertical-align: middle;">Rank</th>
            @endif
        </tr>
    </thead>

    <tbody>
        @foreach($dataLedger as $i => $row)
        <tr>
            <td style="text-align: center;">{{ $loop->iteration }}</td>
            <td>{{ $row->nama_siswa }}</td>

            {{-- Format Text untuk NIS/NISN agar angka 0 di depan tidak hilang --}}
            <td style="text-align: center; mso-number-format:'\@'">{{ $row->nipd ?? '-' }}</td>
            <td style="text-align: center; mso-number-format:'\@'">{{ $row->nisn ?? '-' }}</td>
            
            @foreach($daftarMapel as $mp)
                <td style="text-align: center;">{{ $row->scores[$mp->id_mapel] ?? '-' }}</td>
            @endforeach

            <td style="text-align: center; font-weight: bold;">{{ $row->total }}</td>
            <td style="text-align: center; font-weight: bold;">{{ $row->rata_rata }}</td>
            
            <td style="text-align: center;">{{ $row->absensi->sakit }}</td>
            <td style="text-align: center;">{{ $row->absensi->izin }}</td>
            <td style="text-align: center;">{{ $row->absensi->alpha }}</td>

            {{-- [BARU] Isi Ranking --}}
            @if(request('show_ranking', '0') == '1')
                <td style="text-align: center; font-weight: bold; background-color: #fff2cc;">
                    {{ $row->ranking_no ?? '-' }}
                </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>