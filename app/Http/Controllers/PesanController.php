<?php

namespace App\Http\Controllers;

use App\Models\Tempat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PesanController extends Controller
{
    static function kirimPesan($no_wa, $pesan)
    {

        $header = array(
            "Content-Type: application/json",
            "Authorization: fbec06eb35b86ac0184853a4fabcd747"
        );

        $no_wa = PesanController::formatWa($no_wa);
        $data = array(
            "device" => "888662421399",
            "phone" => $no_wa,
            "message" => $pesan,
        );

        $param_post = json_encode($data, JSON_PRETTY_PRINT);
        $post        = curl_init("https://api.alatwa.com/send/text");
        curl_setopt($post, CURLOPT_HTTPHEADER, $header);
        curl_setopt($post, CURLOPT_POST, 1);
        curl_setopt($post, CURLOPT_POSTFIELDS, $param_post);
        curl_setopt($post, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($post, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($post, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($post);

        return  $response;
        curl_close($post);
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
        $header = array(
            "api-key: 73IZOwsYpSDSxMolipru6BV0WizX9eSH6Lj7wFUzl27n5",
            "Accept: application/json",
            "Content-Type: application/json"
        );

        $data = array(
            "url" => env('FRONTEND_URL') . $url,
        );

        $param_post = json_encode($data, JSON_PRETTY_PRINT);
        $post        = curl_init("https://shrtlnk.dev/api/v2/link");
        curl_setopt($post, CURLOPT_HTTPHEADER, $header);
        curl_setopt($post, CURLOPT_POST, 1);
        curl_setopt($post, CURLOPT_POSTFIELDS, $param_post);
        curl_setopt($post, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($post, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($post, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($post);
        curl_close($post);
        $data = json_decode($response);
        return $data->shrtlnk;
    }

    static function remainder()
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

        if ($data) {
            foreach ($data as $key => $value) {

                $foundRoom = $collection->firstWhere('id', $value->ruangan);

                $pesan = 'Remainder Kegiatan *' . $value->kegiatan . '* bertampat di *' . $foundRoom['label'] . '* di tanggal *' . $value->tanggal . '*  Jam ' . $value->jam_mulai . ' - ' . $value->jam_akhir;
                PesanController::kirimPesan($value->no_wa, $pesan);
            }
        }
    }
}
