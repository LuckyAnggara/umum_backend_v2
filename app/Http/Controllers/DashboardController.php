<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Arsip;
use App\Models\PeminjamanBmn;
use App\Models\PermintaanLayananBmn;
use App\Models\PermintaanPersediaan;
use App\Models\Rate;
use App\Models\Tempat;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function index(Request $request)
    {
        $date = $request->input('date');
        try {
            // Mengambil data inventaris dengan paginasi
            $tempat = Tempat::whereDate('created_at', $date)
                ->get();

            $arsip = Arsip::all()->count();

            $agenda = Agenda::whereDate('created_at', $date)
                ->get();

            $layanan = $this->getLayanan($date);
            $rate = $this->getRate($date);

            
            return response()->json(['tempat' => $tempat, 'rate'=>$rate, 'agenda'=> $agenda, 'layanan'=> $layanan, 'arsip'=> $arsip], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    function getRate($date){
        $rate = Rate::whereMonth('created_at', Carbon::createFromFormat('Y-m-d', $date)->format('m'))->get();
        return $rate->avg('value');
 }

     function getLayanan($date){
        $persediaan = PermintaanPersediaan::whereMonth('created_at', Carbon::createFromFormat('Y-m-d', $date)->format('m'));
        $servis = PermintaanLayananBmn::whereMonth('created_at', Carbon::createFromFormat('Y-m-d', $date)->format('m'));
        $bmn = PeminjamanBmn::whereMonth('created_at', Carbon::createFromFormat('Y-m-d', $date)->format('m'));

        $persediaanBelum = $persediaan->whereNot('status','DONE')->get()->count();
        $persediaanSelesai = $persediaan->where('status','DONE')->get()->count();

        $servisBelum = $servis->whereNot('status','DONE')->get()->count();
        $servisSelesai = $servis->where('status','DONE')->get()->count();

        $bmnBelum = $bmn->whereNot('status','DONE')->get()->count();
        $bmnSelesai = $bmn->where('status','DONE')->get()->count();
        
        $selesai = $persediaanSelesai + $bmnSelesai + $servisSelesai;
        $belum = $persediaanBelum + $bmnBelum + $servisBelum;

        return [
            'selesai' => $selesai,
            'belum' => $belum
        ];
 }
}
