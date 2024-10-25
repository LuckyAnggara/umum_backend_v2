<?php

namespace App\Http\Controllers;

use App\Models\Sbm;
use Illuminate\Http\Request;

class SbmController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('limit', 100);
        $name = $request->input('query');
        $tahun_anggaran = $request->input('tahun');

        try {
            // Mengambil data inventaris dengan paginasi
            $sbm = Sbm::when($tahun_anggaran, function ($query, $tahun_anggaran) {
                return $query->where('tahun_anggaran', $tahun_anggaran);
            })->when($name, function ($query, $name) {
                return $query->where('daerah', 'like', '%' . $name . '%')
                    ->orWhere('biaya', 'like', '%' . $name . '%')
                    ->orWhere('nilai', 'like', '%' . $name . '%');
            })

                ->orderBy('daerah', 'asc')
                ->latest()
                ->paginate($perPage);

            return response()->json(['data' => $sbm], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
