<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Http;

class PegawaiController extends Controller
{
  
public function searchSimpeg(Request $request)
    {  
$nip = $request->nip;
         try {

            // DARI APLIKASI DSE
        //     $client = new Client([
        //     'headers' => [
        //         'Content-Type' => 'application/json',
        //     ],
        //     'verify'=>false
        // ]);
        // $response = json_decode($client->post(
        //     'https://dse.kemenkumham.go.id/index.php/home/get_ajax_pegawai/',
        //     array(
        //         'form_params' => array(
        //             'nip' => $nip
        //             )
        //         )
        // )->getBody(), true);

 
$response = Http::withOptions([
    'verify' => false,
])->get('https://lapkin.bbmakmur.com/api/employee-show/'.$nip)->json();

            return response()->json( $response, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


}
