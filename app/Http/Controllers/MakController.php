<?php

namespace App\Http\Controllers;

use App\Models\Mak;
use App\Models\MakDetail;
use App\Models\MakNominatif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MakController extends BaseController
{
    public function index(Request $request)
    {
        $name = $request->input('query');
        $unit = $request->input('unit');
        $tahun = $request->input('tahun');
        $perPage = $request->input('limit', 5);

        try {
            // Mengambil data inventaris dengan paginasi
            $mak = Mak::with('unit', 'detail', 'nominatif.detail')->when($name, function ($query, $name) {
                return $query
                    ->where('keterangan', 'like', '%' . $name . '%')
                    ->orWhere('kode_mak', 'like', '%' . $name . '%');
            })->when($unit, function ($query, $unit) {
                return $query
                    ->where('unit_id', $unit);
            })->when($tahun, function ($query, $tahun) {
                return $query
                    ->where('tahun_anggaran', $tahun);
            })->orderBy('created_at', 'desc')
                ->latest()
                ->paginate($perPage);

            return response()->json(['data' => $mak], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $result = Mak::with('unit', 'detail')->where('id', $id)->first();
            return $this->sendResponse($result, 'Ada');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 'Error');
        }
    }

    public function store(Request $request)
    {

        DB::beginTransaction();
        try {
            $result = Mak::create([
                'tahun_anggaran' => $request->tahun_anggaran,
                'unit_id' => $request->unit,
                'kode_mak' =>  $request->kode_mak,
                'keterangan' =>  $request->keterangan,
                'anggaran' => $request->anggaran,
                'user_id' => Auth::id(),
            ]);

            if ($result) {
                foreach ($request->detail as $key => $value) {
                    $details = MakNominatif::create([
                        'mak_id' => $result->id,
                        'uraian' => $value['uraian'],  // gunakan akses array
                        'type' => $value['type'],       // gunakan akses array
                        'jumlah' => $value['type'] == 'detail' ?  (int) preg_replace('/[^0-9]/', '', $value['jumlah']) : 0,
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

    public function destroy($id)
    {
        try {
            // Cari dan hapus data bmn berdasarkan ID
            $data = Mak::findOrFail($id);
            if ($data) {
                $data->delete();
            }
            // Berikan respons sukses
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
