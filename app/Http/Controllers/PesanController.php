<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPesan;
use App\Models\Tempat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PesanController extends Controller
{
    public function test()
    {
        // PesanController::reminder();
        $today = Carbon::now();
        // return $today->format('d F Y');

        $data = Tempat::whereDate(
            'created_at',
            $today
        )->get();

        if ($data) {
            foreach ($data as $key => $value) {
                sleep(5);
                // $foundRoom = $collection->firstWhere('id', $value->ruangan);
                $pesan = 'Selamat Pagi, Hari ini ' . $today->format('d F Y') . ' ada Kegiatan *' . $value->kegiatan . '* bertampat di    Jam ' . Carbon::parse($value->jam_mulai)->format('h:i') . ' - ' . Carbon::parse($value->jam_akhir)->format('h:i');
                // PesanController::kirimPesan($value->no_wa, $pesan, 20);

                return $pesan;
            }
        }
    }

    // FONTAL
    // static function kirimPesan($no_wa, $pesan)
    // {

    //     $header = array(
    //         "Content-Type: application/json",
    //         "Authorization: fbec06eb35b86ac0184853a4fabcd747"
    //     );

    //     $no_wa = PesanController::formatWa($no_wa);
    //     $data = array(
    //         "device" => "888662421399",
    //         "phone" => $no_wa,
    //         "message" => $pesan,
    //     );

    //     $param_post = json_encode($data, JSON_PRETTY_PRINT);
    //     $post        = curl_init("https://api.alatwa.com/send/text");
    //     curl_setopt($post, CURLOPT_HTTPHEADER, $header);
    //     curl_setopt($post, CURLOPT_POST, 1);
    //     curl_setopt($post, CURLOPT_POSTFIELDS, $param_post);
    //     curl_setopt($post, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($post, CURLOPT_CONNECTTIMEOUT, 0);
    //     curl_setopt($post, CURLOPT_TIMEOUT, 5);
    //     $response = curl_exec($post);

    //     return  $response;
    //     curl_close($post);
    // }

    // ALAT WA
    static function kirimPesan($no_wa, $pesan, $delay = 5)
    {
        ProcessPesan::dispatch($no_wa, $pesan)
            ->delay(now()->addSeconds($delay));
        // try {
        //     $dataSending = array();
        //     $dataSending["api_key"] = 'PZRWB4JG5LTLT2ZV';
        //     $dataSending["number_key"] = 'n74BlBzROOvfHNwk';
        //     $dataSending["phone_no"] = PesanController::formatWa($no_wa);
        //     $dataSending["message"] = $pesan;
        //     $response = Http::withOptions([
        //         'verify' => false,
        //         'timeout' => 0,
        //     ])->withHeaders([
        //         'Content-Type: application/json'
        //     ])
        //         ->send('POST', 'https://api.watzap.id/v1/send_message', [
        //             'body' => json_encode($dataSending)
        //         ])->json();

        //     return true;
        // } catch (\Exception $e) {
        //     return response()->json(['message' => $e->getMessage()], 500);
        // }


        //   $dataSending = Array();
        //     $dataSending["api_key"] = 'PZRWB4JG5LTLT2ZV';
        //     $dataSending["number_key"] ='kKOzvBMutColefBl'; 
        //     $dataSending["phone_no"] = $no_wa;
        //     $dataSending["message"] = $pesan;
        //     $curl = curl_init();
        //     curl_setopt_array($curl, array(
        //     CURLOPT_URL => 'https://api.watzap.id/v1/send_message',
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        //     CURLOPT_POSTFIELDS => json_encode($dataSending),
        //     CURLOPT_HTTPHEADER => array(
        //         'Content-Type: application/json'
        //     ),
        //     ));
        //     $response = curl_exec($curl);
        //     curl_close($curl);

        //     // return $response;
        //     echo $response;
    }

    static function formatWa($no_wa)
    {
        // Menghapus karakter selain angka dari nomor telepon
        $phoneNumber = preg_replace('/[^0-9]/', '', $no_wa);

        // Menghilangkan awalan '0' jika ada
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = substr($phoneNumber, 1);
        }

        // Menambahkan awalan '+62' jika belum ada
        if (substr($phoneNumber, 0, 3) !== '62') {
            $phoneNumber = '62' . $phoneNumber;
        }
        // Mengembalikan nomor telepon yang diformat
        return (string)$phoneNumber;
    }



    static function shorten($url)
    {

        try {
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 0,
            ])->withHeaders([
                "api-key" => "MkYDs0DGqXMDsUCL2LUwi7sNiElg0RIzMWoog818rCuMR",
                "Accept" => "application/json",
                "Content-Type" => "application/json"
            ])
                ->post('https://shrtlnk.dev/api/v2/link', [
                    "url" => env('FRONTEND_URL') . $url,
                ])->json();

            return $response['shrtlnk'];
        } catch (\Exception $e) {
            return false;
        }



        // $post        = curl_init("https://shrtlnk.dev/api/v2/link");
        // curl_setopt($post, CURLOPT_HTTPHEADER, $header);
        // curl_setopt($post, CURLOPT_POST, 1);
        // curl_setopt($post, CURLOPT_POSTFIELDS, $param_post);
        // curl_setopt($post, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($post, CURLOPT_CONNECTTIMEOUT, 0);
        // curl_setopt($post, CURLOPT_TIMEOUT, 5);
        // $response = curl_exec($post);
        // curl_close($post);
        // $data = json_decode($response);
        // return $data;
    }

    static function reminder()
    {
        $rooms = [
            [
                'id' => 1,
                'label' => 'Auditorium'
            ],
            [
                'id' => 2,
                'label' => 'Ruang Rapat Inspektur Jenderal'
            ],
            [
                'id' => 3,
                'label' => 'Ruang Rapat Sekretaris Inspektorat Jenderal'
            ],
        ];

        // Konversi array menjadi Collection
        $collection = collect($rooms);
        $today = Carbon::now();
        $data = Tempat::whereDate('tanggal', $today)->get();

        if ($data) {
            foreach ($data as $key => $value) {

                $foundRoom = $collection->firstWhere('id', $value->ruangan);
                sleep(5);

                $pesan = 'Selamat Pagi, Hari ini ' . Carbon::now()->format('d F Y') . ' ada Kegiatan *' . $value->kegiatan . '* bertampat di *' . $foundRoom['label'] . '*  pada pukul ' . Carbon::parse($value->jam_mulai)->format('H:i') . ' - ' . Carbon::parse($value->jam_akhir)->format('H:i');
                PesanController::kirimPesan($value->no_wa, $pesan, 5);
            }
        }
    }


    static function reminderPimpinan()
    {
        $rooms = [
            [
                'id' => 1,
                'label' => 'Auditorium'
            ],
            [
                'id' => 2,
                'label' => 'Ruang Rapat Inspektur Jenderal'
            ],
            [
                'id' => 3,
                'label' => 'Ruang Rapat Sekretaris Inspektorat Jenderal'
            ],
        ];

        // Konversi array menjadi Collection
        $collection = collect($rooms);
        $today = Carbon::now();
        $data = Tempat::whereDate('created_at', $today)->get();
        $today = $today->format('d F Y');
        if ($data) {
            foreach ($data as $key => $value) {
                $foundRoom = $collection->firstWhere('id', $value->ruangan);
                $pesan = 'Selamat Pagi, Hari ini ' . Carbon::now()->format('d F Y') . ' ada Kegiatan *' . $value->kegiatan . '* bertampat di *' . $foundRoom['label'] . '*  pada pukul ' . $value->jam_mulai . ' - ' . $value->jam_akhir;
                PesanController::kirimPesan($value->no_wa, $pesan, 5);
            }
        }
    }
}
