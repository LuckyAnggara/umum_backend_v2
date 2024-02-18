<?php

namespace App\Http\Controllers;

use App\Models\Ptj;
use App\Models\PtjLampiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PtjController extends BaseController
{
     public function store(Request $request)
    {
 
        DB::beginTransaction();
        try {
            // Simpan data ke database menggunakan metode create
            $result = Ptj::create([
                'nama_kegiatan' => $request->nama_kegiatan,
                'realisasi' => $request->realisasi,
                'tanggal' => Carbon::now(),
                'nama' => $request->nama,
                'unit' => $request->unit,
                'nip' => $request->nip,
                'no_wa' => $request->no_wa,
            ]);

            if($result){
                for ($i=0; $i < $request->jumlah_lampiran; $i++) { 
                    $file_path = $request->file[$i]->store('ptj', 'public');
                    $detail= PtjLampiran::create([
                        'ptj_id'=> $result->id,
                        'file_name'=> $request->file[$i]->getClientOriginalName(),
                        'lampiran'=> $file_path,
                    ]);
                }

            }
        
            DB::commit();
            return $this->sendResponse($result, 'Data berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), 'Error');
        }
    }

}
