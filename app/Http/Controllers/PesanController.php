<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PesanController extends Controller
{

    public function kirim(){

        $no_wa = '6282116562811';
        $pesan = 'hai';
     

       $response = Http::withHeaders([
    'Content-Type' => 'application/json',
    'Authorization' => 'fbec06eb35b86ac0184853a4fabcd747'
])->post('https://api.alatwa.com/send/text', [
      "device" => "888662421399",
            "phone" => "628116562811",
            "message" => $pesan,
]);

       return  $response;
    }

    static function kirimPesan($no_wa, $pesan){

        $header = array(
            "Content-Type: application/json",
            "Authorization: fbec06eb35b86ac0184853a4fabcd747"
        );
        $no_wa = PesanController::formatWa($no_wa);
        $data = array(
            "device" => "888662421399",
            "phone" => "628116562811",
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

        curl_close($post);
       echo $response;

    }

    static function formatWa($no_wa){
         // Menghapus karakter selain angka dari nomor telepon
        $phoneNumber = preg_replace('/[^0-9]/', '', $no_wa);

        // Menghilangkan awalan '0' jika ada
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = substr($phoneNumber, 1);
        }

        // Menambahkan awalan '+62' jika belum ada
        if (substr($phoneNumber, 0, 3) !== '+62') {
            $phoneNumber = '+62' . $phoneNumber;
        }
        // Mengembalikan nomor telepon yang diformat
        return (string)$phoneNumber; 
    }
}
