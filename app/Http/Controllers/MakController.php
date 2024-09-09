<?php

namespace App\Http\Controllers;

use App\Models\Mak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MakController extends Controller
{
    public function index(Request $request)
    {
        $name = $request->input('query');
        $tahun = $request->input('tahun');
        $perPage = $request->input('limit',5);

        try {
            // Mengambil data inventaris dengan paginasi
            $mak = Mak::when($name, function ($query, $name) {
                return $query
                    ->where('keterangan', 'like', '%' . $name . '%')
                    ->orWhere('kode_mak', 'like', '%' . $name . '%');
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
}
