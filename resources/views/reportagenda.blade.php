<!DOCTYPE html>
<html lang="en">

<head>
    <title>Agenda Pimpinan</title>
</head>
<style type="text/css">
    body {
        font-family: 'Roboto Condensed', sans-serif;
    }

    .m-0 {
        margin: 0px;
    }

    .mb-5 {
        margin-bottom: 5px;
    }

    .p-0 {
        padding: 0px;
    }

    .pt-5 {
        padding-top: 5px;
    }

    .mt-10 {
        margin-top: 10px;
    }

    .mt-25 {
        margin-top: 25px;
    }

    .mb-25 {
        margin-bottom: 25px;
    }

    .mb-75 {
        margin-bottom: 75px;
    }

    .mx-auto {
        margin: auto;
    }

    .text-center {
        text-align: center !important;
    }

    .w-100 {
        width: 100%;
    }

    .w-50 {
        width: 50%;
    }

    .w-85 {
        width: 85%;
    }

    .w-15 {
        width: 15%;
    }


    .gray-color {
        color: #5D5D5D;
    }

    .text-bold {
        font-weight: bold;
    }

    .border {
        border: 1px solid black;
    }

    table tr,
    th,
    td {
        border: 1px solid #d2d2d2;
        border-collapse: collapse;
        padding: 7px 8px;
    }

    table tr th {
        background: #F4F4F4;
        font-size: 15px;
    }

    table tr td {
        font-size: 13px;
    }

    table {
        border-collapse: collapse;
    }

    .box-text p {
        line-height: 10px;
    }

    .float-left {
        float: left;
    }

    .float-right {
        float: right;
    }

    .center {
        text-align: center;
    }

    .total-part {
        font-size: 16px;
        line-height: 12px;
    }

    .total-right p {
        padding-right: 20px;
    }

    .item-center {
        align-items: center;
    }
</style>

<body>
    <div class="head-title">
        <h1 class="m-0 p-0 text-center">Agenda Pimpinan</h1>
    </div>

    <div class="add-detail mt-10">
        <div class="w-100 float-left mt-10">
            <p class="text-bold w-100 m-0 pt-5">Tanggal Data - <span class="gray-color">{{ $fromDate }} -
                    {{ $toDate }} </span></p>
        </div>
        <div style="clear: both;"></div>
    </div>
    {{ $data }}
    <div class="table-section bill-tb w-100 mx-auto mt-10">
        <table class="w-100 mt-10 table">
            <thead>
                <tr>
                    <th style="width:5%">Tanggal</th>
                    <th style="width:10%">Inspektur Jenderal</th>
                    <th style="width:10%">Sekretaris Inspektorat Jenderal</th>
                    <th style="width:10%">Inspektur Wilayah I</th>
                    <th style="width:10%">Inspektur Wilayah II</th>
                    <th style="width:10%">Inspektur Wilayah III</th>
                    <th style="width:10%">Inspektur Wilayah IV</th>
                    <th style="width:10%">Inspektur Wilayah V</th>
                    <th style="width:10%">Inspektur Wilayah VI</th>
                </tr>
            </thead>
            <tbody>
                @foreach (json_decode($data) as $key => $item)
                    <tr>
                        <td>{{ $item->tanggal }}</td>
                        @if (count($item->detail) > 0)
                            @foreach ($item->detail as $key => $detail)
                                @if ($detail->pimpinan == 1)
                                    <td>
                                        <ol>
                                            @foreach ($detail->kegiatan as $key => $kegiatan)
                                                <li>{{ $kegiatan->kegiatan }}</li>
                                            @endforeach
                                        </ol>
                                    </td>
                                @else
                                    <td></td>
                                @break
                            @endif
                        @endforeach


                </tr>
            @endforeach
        </tbody>
    </table>
</div>

</body>

</html>
