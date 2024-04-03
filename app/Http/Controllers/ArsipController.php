<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\ArsipLampiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\returnSelf;

class ArsipController extends BaseController
{
    public function index(Request $request)
    {
        $name = $request->input('query');
        $tingkatPerkembangan = $request->input('tingkat-perkembangan');
        $perPage = $request->input('limit');
        $lokasi = $request->input('lokasi');
        $startDate = $request->input('start-date');
        $endDate = $request->input('end-date');

        try {
            // Mengambil data inventaris dengan paginasi
            $arsip = Arsip::with('lampiran')
                ->when($tingkatPerkembangan, function ($query, $tingkatPerkembangan) {
                    return $query->where('tingkat_perkembangan', $tingkatPerkembangan);
                })
                ->when($lokasi, function ($query, $lokasi) {
                    return $query->where('tingkat_perkembangan', $lokasi);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('tanggal', [$startDate, $endDate]);
                })->when($name, function ($query, $name) {
                    return $query->where('nomor_surat', 'like', '%' . $name . '%')
                        ->orWhere('klasifikasi', 'like', '%' . $name . '%')
                        ->orWhere('pencipta_arsip', 'like', '%' . $name . '%')
                        ->orWhere('pengolah_arsip', 'like', '%' . $name . '%')
                        ->orWhere('uraian', 'like', '%' . $name . '%');
                })
                ->orderBy('created_at', 'desc')
                ->latest()
                ->paginate($perPage);
            return response()->json(['data' => $arsip], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // Mulai transaksi database
        DB::beginTransaction();
        // return $request->jam_mulai['hours'];


        try {
            $result = Arsip::create([
                'nomor_surat' => $request->nomor_surat,
                'tanggal_surat' =>  Carbon::parse($request->tanggal)->toDateString(),
                'klasifikasi' => $request->klasifikasi,
                'pencipta_arsip' => $request->pencipta_arsip,
                'pengolah_arsip' => $request->pengolah_arsip,
                'tingkat_perkembangan' => $request->tingkat_perkembangan,
                'jumlah' => $request->jumlah,
                'uraian' => $request->uraian,
                'lokasi' => $request->lokasi,
                'lemari' => $request->lemari,
                'rak' => $request->rak,
                'no_box' => $request->no_box,
                'no_folder' => $request->no_folder,
                'jenis_media' => $request->jenis_media,
            ]);

            if ($result) {
                for ($i = 0; $i < $request->jumlah_lampiran; $i++) {
                    $file_path = $request->file[$i]->store('arsip', 'public');
                    $detail = ArsipLampiran::create([
                        'arsip_id' => $result->id,
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

    public function show($id)
    {
        try {
            // Ambil data inventory berdasarkan ID
            $result =  Arsip::with('lampiran')->where('id', $id)->first();
            // Berikan respons dengan data inventory
            return response()->json(['data' => $result], 200);
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $arsip = Arsip::findOrFail($id);
            // return Storage::url($inventory->image);
            $arsip->update([
                'nomor_surat' => $request->nomor_surat,
                'tanggal_surat' =>  Carbon::parse($request->tanggal)->toDateString(),
                'klasifikasi' => $request->klasifikasi,
                'pencipta_arsip' => $request->pencipta_arsip,
                'pengolah_arsip' => $request->pengolah_arsip,
                'tingkat_perkembangan' => $request->tingkat_perkembangan,
                'jumlah' => $request->jumlah,
                'uraian' => $request->uraian,
                'lokasi' => $request->lokasi,
                'lemari' => $request->lemari,
                'rak' => $request->rak,
                'no_box' => $request->no_box,
                'no_folder' => $request->no_folder,
                'jenis_media' => $request->jenis_media,
            ]);

            // return $request->file_lama[0];

            if ($request->jumlah_lampiran_delete > 0) {
                for ($i = 0; $i < $request->jumlah_lampiran_delete; $i++) {
                    $lampiranId = $request->file_delete[$i];
                    $file = ArsipLampiran::findOrFail($lampiranId);
                    if ($file) {
                        Storage::disk('public')->delete($file->lampiran);
                        $file->delete();
                    }
                }
            }

            if ($request->jumlah_lampiran > 0) {
                for ($i = 0; $i < $request->jumlah_lampiran; $i++) {
                    $file_path = $request->file[$i]->store('arsip', 'public');
                    $detail = ArsipLampiran::create([
                        'arsip_id' => $arsip->id,
                        'file_name' => $request->file[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
                    ]);
                }
            }

            $result =  Arsip::with('lampiran')->where('id', $id)->first();

            DB::commit();
            return $this->sendResponse($result, 'Data berhasil di perbaharui');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), 'Error');
        }
    }

    public function destroy($id)
    {
        try {
            // Cari dan hapus data bmn berdasarkan ID
            $result =  Arsip::with('lampiran')->where('id', $id)->first();

            if (count($result->lampiran) > 0) {
                foreach ($result->lampiran as $key => $lampiran) {
                    Storage::disk('public')->delete($lampiran->lampiran);
                }
            }
            $result->delete();
            // Berikan respons sukses
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }
}
