@inject('carbon', 'Carbon\Carbon')

<table>
    <thead>
        <tr>
            <th colspan="11" style="text-align:center">Master Data</th>
            <th colspan="8" style="text-align:center">Anggaran</th>
            <th colspan="7" style="text-align:center">Realisasi</th>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal SP / ST</th>
            <th>No SP / NO ST</th>
            <th>No SPPD</th>
            <th>Nama Pegawai</th>
            <th>MAK</th>
            <th>Nama Kegiatan</th>
            <th>Tempat Kegiatan</th>
            <th>Tanggal Awal Kegiatan</th>
            <th>Tanggal Akhir Kegiatan</th>
            <th>Jumlah Hari</th>

            <!-- Anggaran -->
            <th>Uang Harian (Total)</th>
            <th>Pesawat</th>
            <th>Taksi Jakarta</th>
            <th>Taksi Provinsi</th>
            <th>Hotel (Total)</th>
            <th>Transport</th>
            <th>Representatif (Total)</th>
            <th>Total Rencana Anggaran Biaya (RAB)</th>

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
        @php
            $jumlah_hari = $value->jumlah_hari ?? 0;

            $uang_harian_unit = collect($value->uang_harian)->sum('biaya') ?? 0;
            $total_harian = $uang_harian_unit * $jumlah_hari;

            $hotel_unit = collect($value->hotel)->sum('biaya') ?? 0;
            $total_hotel = $hotel_unit * $jumlah_hari;

            $representatif_unit = collect($value->representatif)->sum('biaya') ?? 0;
            $total_representatif = $representatif_unit * $jumlah_hari;

            $pesawat = collect($value->pesawat)->sum('biaya') ?? 0;
            $taksi_jakarta = collect($value->taksi_jakarta)->sum('biaya') ?? 0;
            $taksi_tujuan = collect($value->taksi_tujuan)->sum('biaya') ?? 0;
            $transport = collect($value->transport)->sum('biaya') ?? 0;

            $total_rab = $total_harian
                         + $pesawat
                         + $taksi_jakarta
                         + $taksi_tujuan
                         + $total_hotel
                         + $transport
                         + $total_representatif;
        @endphp

        <tr>
            <td>{{ ++$no}}</td>
            <td>{{ $value->master->no_st }}</td>
            <td>{{ $value->master->tanggal_st->format('d F Y')}}</td>
            <td>{{ $value->no_sppd }}</td>
            <td>{{ $value->nama }}</td>
            <td>{{ $value->master->mak->kode_mak ?? '' }}</td>
            <td>{{ $value->master->nama_kegiatan ?? '' }}</td>
            <td>{{ $value->master->tempat_kegiatan ?? '' }}</td>
            <td>{{ $value->tanggal_awal ?? '' }}</td>
            <td>{{ $value->tanggal_akhir ?? '' }}</td>
            <td>{{ $jumlah_hari }}</td>

            <td>{{ $total_harian }}</td>
            <td>{{ $pesawat }}</td>
            <td>{{ $taksi_jakarta }}</td>
            <td>{{ $taksi_tujuan }}</td>
            <td>{{ $total_hotel }}</td>
            <td>{{ $transport }}</td>
            <td>{{ $total_representatif }}</td>
            <td>{{ $total_rab }}</td>

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