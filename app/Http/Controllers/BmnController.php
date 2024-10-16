<?php

namespace App\Http\Controllers;

use App\Models\Bmn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BmnController extends BaseController
{
    public function index(Request $request)
    {
        $perPage = $request->input('limit', 5);
        $name = $request->input('query');
        $status = $request->input('status');
        $mobil = $request->input('mobil');

        try {
            // Mengambil data inventaris dengan paginasi
            $bmn = Bmn::with('pinjam')->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })->when($mobil, function ($query, $mobil) {
                return $query->where('mobil_dinas', $mobil);
            })->when($name, function ($query, $name) {
                return $query->where('nama', 'like', '%' . $name . '%')
                    ->orWhere('nup', 'like', '%' . $name . '%');
            })

                ->orderBy('created_at', 'desc')
                ->latest()
                ->paginate($perPage);

            return response()->json(['data' => $bmn], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nup' => 'required|string|unique:bmns',
            'nama' => 'required|string',
            'tahun_perolehan' => 'required|string',
        ]);

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            $file_path = null;
            if ($request->file) {
                $file_path = $request->file->store('bmn', 'public');
            }
            // Simpan data ke database menggunakan metode create
            $result = Bmn::create([
                'nup' => $request->nup,
                'nama' => $request->nama,
                'keterangan' => $request->keterangan,
                'ruangan' => $request->ruangan,
                'tahun_perolehan' => $request->tahun_perolehan,
                'penanggung_jawab' => $request->penanggung_jawab,
                'image' => $file_path,
            ]);

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
            // Ambil data bmn berdasarkan ID
            $bmn = Bmn::findOrFail($id);
            // Berikan respons dengan data bmn
            return response()->json(['data' => $bmn], 200);
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Cari dan update data bmn berdasarkan ID
            $bmn = Bmn::findOrFail($id);
            // return Storage::url($bmn->image);
            if ($request->file == 'null') {
                if ($bmn->image) {
                    Storage::disk('public')->delete($bmn->image);
                }
            } else {
                if ($bmn->image) {
                    Storage::disk('public')->delete($bmn->image);
                }
                $file_path = $request->file->store('bmn', 'public');
            }



            $bmn->update([

                'nup' => $request->nup,
                'nama' => $request->nama,
                'keterangan' => $request->keterangan,
                'ruangan' => $request->ruangan,
                'tahun_perolehan' => $request->tahun_perolehan,
                'penanggung_jawab' => $request->penanggung_jawab,
                'image' =>  $request->file == 'null' ? null : $file_path ?? $bmn->image,


            ]);

            // Commit transaksi jika berhasil
            DB::commit();
            // Berikan respons sukses
            return response()->json(['message' => 'Data berhasil diperbarui'], 200);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollback();
            // Berikan respons error
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Cari dan hapus data bmn berdasarkan ID
            $bmn = Bmn::findOrFail($id);
            if ($bmn->image) {
                Storage::disk('public')->delete($bmn->image);
            }
            $bmn->delete();
            // Berikan respons sukses
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }

    public function cekNup(Request $request)
    {
        $name = $request->input('query');
        $bmn = Bmn::where('nup', $name)->first();
        if ($bmn) {
            return true;
        }
        return "false";
    }

    public function showNup($nup)
    {
        try {
            // Ambil data bmn berdasarkan ID
            $bmn = Bmn::where('nup', $nup)->first();
            return response()->json(['data' => $bmn], 200);

            // Berikan respons dengan data bmn
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }
}
