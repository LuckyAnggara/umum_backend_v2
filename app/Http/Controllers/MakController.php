<?php

namespace App\Http\Controllers;

use App\Models\Mak;
use App\Models\MakDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            $mak = Mak::with('unit', 'detail')->when($name, function ($query, $name) {
                return $query
                    ->where('keterangan', 'like', '%' . $name . '%')
                    ->orWhere('kode_mak', 'like', '%' . $name . '%');
            })->when($unit, function ($query, $unit) {
                return $query
                    ->where('unit_id', $unit);
            })
                ->where('tahun_anggaran', $tahun)
                ->where('unit_id', Auth::user()->unit_id)
                ->orderBy('created_at', 'desc')
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
}
