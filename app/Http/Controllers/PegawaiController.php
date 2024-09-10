<?php

namespace App\Http\Controllers;

use App\Models\PerjadinDetail;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PDO;

class PegawaiController extends Controller
{

    public function searchSimpeg(Request $request)
    {
        $nip = $request->nip;
        try {
            $cariPegawai = PerjadinDetail::where('nip', $nip)->first();
            if ($cariPegawai) {
                $data = [
                    'success' => true,
                    'message' => 'Data tersedia',
                    'data' =>  [
                        'name' => $cariPegawai->nama,
                        'nip' => $cariPegawai->nip,
                        'jabatan' => $cariPegawai->jabatan,
                        'pangkat' => $cariPegawai->pangkat,
                        'unit' => $cariPegawai->unit,
                        'dari_lapkin' => false,
                    ]
                ];
                return response()->json($data, 200);
            } else {
                $response = Http::withOptions([
                    'verify' => false,
                ])->get('https://lapkin.bbmakmur.com/api/employee-show/' . $nip)->json();
                $data = [
                    'success' => $response['success'],
                    'message' => $response['message'],
                    'data' =>  [...$response['data'], 'dari_lapkin' => true]
                ];
                return response()->json($data, 200);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
