<!DOCTYPE html>
<html lang="en">

<head>
    <title>Items Report</title>
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
        <h1 class="text-center m-0 p-0">Laporan Persediaan</h1>
    </div>

    <div class="add-detail mt-10">
        <div class="w-100 float-left mt-10">
            <p class="m-0 pt-5 text-bold w-100">Tanggal Data - <span class="gray-color">{{ $date }} </span></p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="mx-auto table-section bill-tb mt-10 w-100">
        <table class="table w-100 mt-10">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:35%">Nama</th>
                    <th style="width:15%">Unit / Satuan</th>
                    <th style="width:15%">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $key => $item)
                <tr>
                    <td class="center">{{$key+1}}</td>
                    <td>{{strtoupper($item->nama)}}</td>
                    <td>{{strtoupper($item->satuan)}}</td>
                    <td>{{number_format($item->balance)}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>

</html>