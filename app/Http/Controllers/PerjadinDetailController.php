<?php

namespace App\Http\Controllers;

use App\Models\PerjadinDetail;
use App\Models\PerjadinDetailRep;
use App\Models\PerjadinDetailTransport;
use App\Models\PerjadinDetailUh;
use App\Models\PerjadinDetailHotel;
use App\Models\PerjadinDetailLampiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerjadinDetailController extends BaseController
{
    public function show($id)
    {
        try {
            $result = PerjadinDetail::where('id', $id)->with('hotel', 'transport', 'uang_harian', 'representatif', 'master.mak', 'ppk', 'bendahara','lampiran')->first();
            return $this->sendResponse($result, 'Data berhasil dibuat');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 'Error');
        }
    }

    // public function update(Request $request, $id)
    // {
    //     $data = json_decode($request->getContent());
    //     DB::beginTransaction();
    //     try {
    //         $perjadin = PerjadinDetail::findOrFail($id);
    //         // return Storage::url($inventory->image);
    //         $perjadin->update([
    //             'ppk' => $data->ppk->id,
    //             'bendahara'=> $data->bendahara->id,
    //             'tanggal_kuitansi' => Carbon::parse($data->tanggal_kuitansi)->format('Y-m-d'),
    //         ]);
    //         $result = PerjadinDetail::where('id', $id)->with('hotel', 'transport', 'uang_harian', 'representatif', 'master.mak','ppk','bendahara' )->first();
    //         DB::commit();
    //         return $this->sendResponse($result, 'Data berhasil di perbaharui');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return $this->sendError($e->getMessage(), 'Error');
    //     }
    // }

    public function update(Request $request, $id)
    {
        $umum = json_decode($request->input('umum'));
        DB::beginTransaction();
        try {
            $perjadin = PerjadinDetail::with('uang_harian', 'hotel', 'transport', 'representatif')->where('id', $umum->id)->first();
            // return Storage::url($inventory->image);
            $perjadin->update([
                'status' => 'LENGKAP',
            ]);

            foreach ($umum->hotel as $key => $hotel) {
               $hot = PerjadinDetailHotel::findOrFail($hotel->id);
                   $hot->update([
                'realisasi_hari' => $hotel->realisasi_hari,
                'realisasi_biaya' => $hotel->realisasi_biaya,
                'notes' => $hotel->notes,
            ]);
        }

            foreach ($umum->uang_harian as $key => $uang_harian) {
               $uh = PerjadinDetailUh::findOrFail($uang_harian->id);
                   $uh->update([
                'realisasi_hari' => $uang_harian->realisasi_hari,
                'realisasi_biaya' => $uang_harian->realisasi_biaya,
                'notes' => $uang_harian->notes,
            ]);
        }

            foreach ($umum->transport as $key => $transport) {
               $transport = PerjadinDetailTransport::findOrFail($transport->id);
                   $transport->update([
                'realisasi_biaya' => $transport->realisasi_biaya,
                'notes' => $transport->notes,
            ]);
        }
            
            foreach ($umum->representatif as $key => $representatif) {
               $representatif = PerjadinDetailRep::findOrFail($representatif->id);
                   $representatif->update([
                'realisasi_hari' => $representatif->realisasi_hari,
                'realisasi_biaya' => $representatif->realisasi_biaya,
                'notes' => $representatif->notes,
            ]);        
            }

            if ($request->jumlah_lampiran > 0) {
                for ($i = 0; $i < $request->jumlah_lampiran; $i++) {
                    $file_path = $request->file[$i]->store('perjadin/realisasi/'.$umum->id, 'public');
                    $detail = PerjadinDetailLampiran::create([
                        'perjadin_detail_id' => $perjadin->id,
                        'file_name' => $request->file[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
                    ]);
                }
            }
 DB::commit();
          $result = PerjadinDetail::where('id', $id)->with('hotel', 'transport', 'uang_harian', 'representatif', 'master.mak', 'ppk', 'bendahara','lampiran')->first();
            return $this->sendResponse($result, 'Data berhasil di perbaharui');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), 'Error');
        }
    
    }
}
