<?php

namespace App\Http\Controllers;

use App\Models\MakDetail;
use Illuminate\Http\Request;

class MakDetailController extends Controller
{
    static function createMakDetail($data, $tipe, $status)
    {
        // $data = json_decode($data);
        // Membuat objek LogPermintaanPersediaan
        $mak = MakDetail::create([
            'mak_id' => $data->mak_id,
            'type' => $tipe,
            'kegiatan_id' => $data->id,
            'nama_kegiatan' => $tipe == 'PERJADIN' ? $data->nama_kegiatan : $data->uraian,
            'total_anggaran' => $data->total_anggaran,
            'total_realisasi' => 0,
            'status_realisasi' => $status
        ]);

        // Jika perlu, Anda dapat mengembalikan objek yang telah dibuat
        return $mak;
    }
}
