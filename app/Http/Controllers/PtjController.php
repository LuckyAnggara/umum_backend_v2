<?php

namespace App\Http\Controllers;

use App\Models\Ptj;
use App\Models\PtjLampiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PtjController extends BaseController
{

    public function index(Request $request)
    {
        $perPage = $request->input('limit', 5);
        $name = $request->input('query');

        try {
            // Mengambil data inventaris dengan paginasi
            $inventory = Ptj::with('lampiran')->when($name, function ($query, $name) {
                return $query->where('nama_kegiatan', 'like', '%' . $name . '%')
                    ->orWhere('unit', 'like', '%' . $name . '%');
            })
                ->orderBy('created_at', 'desc')
                ->latest()
                ->paginate($perPage);

            return response()->json(['data' => $inventory], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


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

            if ($result) {
                for ($i = 0; $i < $request->jumlah_lampiran; $i++) {
                    $file_path = $request->file[$i]->store('ptj', 'public');
                    $detail = PtjLampiran::create([
                        'ptj_id' => $result->id,
                        'file_name' => $request->file[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
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
            $ptj = Ptj::findOrFail($id);
            if ($ptj) {
                $lampiran = PtjLampiran::where('ptj_id', $ptj->id)->get();
                foreach ($lampiran as $key => $value) {
                    Storage::disk('public')->delete($value->lampiran);
                    $value->delete();
                }
                $ptj->delete();
            }

            // Berikan respons sukses
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }
}
