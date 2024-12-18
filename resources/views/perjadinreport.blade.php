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
        </tr>
        @endforeach
    </tbody>
</table>