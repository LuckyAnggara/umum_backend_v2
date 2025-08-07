@inject('carbon', 'Carbon\Carbon')

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal SP / ST</th>
            <th>No SP / NO ST</th>
            <th>No SPPD</th>
            <th>Nama Pegawai</th>
            <th>MAK</th>
            <th>Tanggal Awal Kegiatan</th>
            <th>Tanggal Akhir Kegiatan</th>
            <th>Jumlah Hari</th>

            <!-- Anggaran -->
            <th>Uang Harian</th>
            <th>Pesawat</th>
            <th>Taksi Jakarta</th>
            <th>Taksi Provinsi</th>
            <th>Hotel</th>
            <th>Transport</th>
            <th>Representatif</th>
            <!-- Realisasi -->

            <th>R Uang Harian</th>
            <th>R Pesawat</th>
            <th>R Taksi Jakarta</th>
            <th>R Taksi Provinsi</th>
            <th>R Hotel</th>
            <th>R Transport</th>
            <th>R Representatif</th>
        </tr>
    </thead>
    <tbody>
        @php
        $no = 0;
        @endphp
        @foreach($data as $value)

  
        <tr>
            <td>{{ ++$no}}</td>
            <td>{{ $value->master->no_st }}</td>
            <td>{{ $value->master->tanggal_st->format('d F Y')}}</td>
            <td>{{ $value->no_sppd }}</td>
            <td>{{ $value->nama }}</td>
            <td>{{ $value->master->mak->kode_mak ?? '' }}</td>
            <td>{{ $value->tanggal_awal ?? '' }}</td>
            <td>{{ $value->tanggal_akhir ?? '' }}</td>
            <td>{{ $value->jumlah_hari ?? '' }}</td>

            <td>{{ collect($value->uang_harian)->sum('biaya') ?? 0 }}</td>
            <td>{{ collect($value->pesawat)->sum('biaya') ?? 0 }}</td>
            <td>{{ collect($value->taksi_jakarta)->sum('biaya') ?? 0 }}</td>
            <td>{{ collect($value->taksi_tujuan)->sum('biaya') ?? 0 }}</td>
            <td>{{ collect($value->hotel)->sum('biaya') ?? 0 }}</td>
            <td>{{ collect($value->transport)->sum('biaya') ?? 0 }}</td>
            <td>{{ collect($value->representatif)->sum('biaya') ?? 0 }}</td>


                        <td>{{ collect($value->uang_harian)->sum('realisasi_biaya') ?? 0 }}</td>
            <td>{{ collect($value->pesawat)->sum('realisasi_biaya') ?? 0 }}</td>
            <td>{{ collect($value->taksi_jakarta)->sum('realisasi_biaya') ?? 0 }}</td>
            <td>{{ collect($value->taksi_tujuan)->sum('realisasi_biaya') ?? 0 }}</td>
            <td>{{ collect($value->hotel)->sum('realisasi_biaya') ?? 0 }}</td>
            <td>{{ collect($value->transport)->sum('realisasi_biaya') ?? 0 }}</td>
            <td>{{ collect($value->representatif)->sum('realisasi_biaya') ?? 0 }}</td>


        </tr>
        @endforeach
    </tbody>
</table>