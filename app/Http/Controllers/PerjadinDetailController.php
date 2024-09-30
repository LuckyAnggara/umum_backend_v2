<?php

namespace App\Http\Controllers;

use App\Models\PerjadinDetail;
use App\Models\PerjadinDetailCatatan;
use App\Models\PerjadinDetailRep;
use App\Models\PerjadinDetailTransport;
use App\Models\PerjadinDetailUh;
use App\Models\PerjadinDetailHotel;
use App\Models\PerjadinDetailLampiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PerjadinDetailController extends BaseController
{
    public function show($id)
    {
        try {

            $result = PerjadinDetail::where('id', $id)->with('hotel', 'transport', 'uang_harian', 'representatif', 'master.mak', 'ppk', 'bendahara', 'lampiran')->first();
            return $this->sendResponse($result, 'Data tersedia');
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


    /// STORE REALISASI
    public function store(Request $request)
    {
        $umum = json_decode($request->input('umum'));

        // return $request;
        // return $umum;
        DB::beginTransaction();

        try {

            // DELETE EXISTING
            $real = PerjadinDetail::where('id', $umum->id)->with('hotel', 'transport', 'uang_harian', 'representatif', 'master.mak', 'ppk', 'bendahara', 'lampiran')->first();
            foreach ($real->hotel as $key => $value) {
                $value->delete();
            }
            foreach ($real->transport as $key => $value) {
                $value->delete();
            }
            foreach ($real->uang_harian as $key => $value) {
                $value->delete();
            }
            foreach ($real->representatif as $key => $value) {
                $value->delete();
            }

            // STORE HOTEL
            foreach ($umum->hotel as $key => $value) {
                $hot = PerjadinDetailHotel::create([
                    'perjadin_detail_id' => $umum->id,
                    'keterangan' => $value->keterangan,
                    'hari' => $value->hari,
                    'realisasi_hari' => $value->realisasi_hari,
                    'biaya' => $value->biaya,
                    'realisasi_biaya' => $value->realisasi_biaya,
                    'notes' => $value->notes,
                    'bukti' => $value->bukti
                ]);
            }
            // STORE TRANSPORT
            foreach ($umum->transport as $key => $value) {
                $pes = PerjadinDetailTransport::create([
                    'perjadin_detail_id' => $umum->id,
                    'keterangan' => $value->keterangan,
                    'tipe' => $value->tipe,
                    'biaya' => $value->biaya,
                    'realisasi_biaya' => $value->realisasi_biaya,
                    'notes' => $value->notes,
                    'bukti' => $value->bukti
                ]);
            }
            // STORE UH
            foreach ($umum->uang_harian as $key => $value) {
                $dar = PerjadinDetailUh::create([
                    'perjadin_detail_id' => $umum->id,
                    'keterangan' => $value->keterangan,
                    'hari' => $value->hari,
                    'realisasi_hari' => $value->realisasi_hari,
                    'biaya' => $value->biaya,
                    'realisasi_biaya'
                    => $value->realisasi_biaya,
                    'notes' => $value->notes,
                    'bukti' => $value->bukti
                ]);
            }
            // STORE REPRESENTATIF
            foreach ($umum->representatif as $key => $value) {
                $dar = PerjadinDetailRep::create([
                    'perjadin_detail_id' => $umum->id,
                    'keterangan' => $value->keterangan,
                    'hari' => $value->hari,
                    'realisasi_hari' => $value->realisasi_hari,
                    'biaya' => $value->biaya,
                    'realisasi_biaya'
                    => $value->realisasi_biaya,
                    'notes' => $value->notes,
                    'bukti' => $value->bukti
                ]);
            }
            if ($request->jumlah_lampiran_uh > 0) {
                for ($i = 0; $i < $request->jumlah_lampiran_uh; $i++) {
                    $file_path = $request->file_uh[$i]->store('perjadin/ptj/uh', 'public');
                    $lampiran = PerjadinDetailLampiran::create([
                        'perjadin_detail_id' => $umum->id,
                        'type' => 'UH',
                        'file_name' => $umum->no_sppd . ' - ' . $request->file_uh[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
                    ]);
                }
            }
            if ($request->jumlah_lampiran_hotel > 0) {
                for ($i = 0; $i < $request->jumlah_lampiran_hotel; $i++) {
                    $file_path = $request->file_hotel[$i]->store('perjadin/ptj/hotel', 'public');
                    $lampiran = PerjadinDetailLampiran::create([
                        'perjadin_detail_id' => $umum->id,
                        'type' => 'HOTEL',
                        'file_name' => $umum->no_sppd . ' - ' . $request->file_hotel[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
                    ]);
                }
            }
            if ($request->jumlah_lampiran_transport > 0) {
                for ($i = 0; $i < $request->jumlah_lampiran_transport; $i++) {
                    $file_path = $request->file_transport[$i]->store('perjadin/ptj/transport', 'public');
                    $lampiran = PerjadinDetailLampiran::create([
                        'perjadin_detail_id' => $umum->id,
                        'type' => 'TRANSPORT',
                        'file_name' => $umum->no_sppd . ' - ' . $request->file_transport[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
                    ]);
                }
            }
            if ($request->jumlah_lampiran_rep > 0) {
                for ($i = 0; $i < $request->jumlah_lampiran_rep; $i++) {
                    $file_path = $request->file_rep[$i]->store('perjadin/ptj/rep', 'public');
                    $lampiran = PerjadinDetailLampiran::create([
                        'perjadin_detail_id' => $umum->id,
                        'type' => 'REP',
                        'file_name' => $umum->no_sppd . ' - ' . $request->file_rep[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
                    ]);
                }
            }
            if ($request->jumlah_lampiran_lainnya > 0) {
                for ($i = 0; $i < $request->jumlah_lampiran_lainnya; $i++) {
                    $file_path = $request->file_lainnya[$i]->store('perjadin/ptj/lainnya', 'public');
                    $lampiran = PerjadinDetailLampiran::create([
                        'perjadin_detail_id' => $umum->id,
                        'type' => 'LAINNYA',
                        'file_name' => $umum->no_sppd . ' - ' . $request->file_lainnya[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
                    ]);
                }
            }

            if ($request->jumlah_lampiran_delete > 0) {
                for ($i = 0; $i < $request->jumlah_lampiran_delete; $i++) {
                    $lampiranId = $request->file_delete[$i];
                    $file = PerjadinDetailLampiran::findOrFail($lampiranId);
                    if ($file) {
                        Storage::disk('public')->delete($file->lampiran);
                        $file->delete();
                    }
                }
            }



            // $catatan = 'Perencanaan Perjalanan Dinas telah di Buat';
            // PerjadinLogController::createLogPerjadin($umum->perjadin_id, 'Pertanggung Jawaban', $catatan);
            $result = PerjadinDetail::where('id', $umum->id)->with('hotel', 'transport', 'uang_harian', 'representatif', 'master.mak', 'ppk', 'bendahara', 'lampiran')->first();
            DB::commit();
            return $this->sendResponse($result, 'Data berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), 'Error');
        }
    }

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
                    $file_path = $request->file[$i]->store('perjadin/realisasi/' . $umum->id, 'public');
                    $detail = PerjadinDetailLampiran::create([
                        'perjadin_detail_id' => $perjadin->id,
                        'file_name' => $request->file[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
                    ]);
                }
            }
            DB::commit();
            $result = PerjadinDetail::where('id', $id)->with('hotel', 'transport', 'uang_harian', 'representatif', 'master.mak', 'ppk', 'bendahara', 'lampiran')->first();
            return $this->sendResponse($result, 'Data berhasil di perbaharui');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), 'Error');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {
            $perjadin = PerjadinDetail::findOrFail($id);
            $perjadin->update([
                'status' => $request->status,
            ]);
            $catatan = PerjadinDetailCatatan::create([
                'catatan' => $data->catatan,
                'perjadin_detail_id' => $id,
                'user_id' => Auth::id(),
            ]);
            $result = PerjadinDetail::where('id', $id)->with('hotel', 'transport', 'uang_harian', 'representatif', 'master.mak', 'ppk', 'bendahara', 'lampiran')->first();
            DB::commit();
            return $this->sendResponse($result, 'Data berhasil di perbaharui');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), 'Error');
        }
    }
}
