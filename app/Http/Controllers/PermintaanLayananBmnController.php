<?php

namespace App\Http\Controllers;

use App\Models\PermintaanLayananBmn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermintaanLayananBmnController extends BaseController
{
    public function index(Request $request)
    {
        $perPage = $request->input('limit', 5);
        $name = $request->input('query');
        $status = $request->input('status');

        try {
            // Mengambil data inventaris dengan paginasi
            $result = PermintaanLayananBmn::when($name, function ($query, $name) {
                return $query
                    ->where('nup', 'like', '%' . $name . '%')
                    ->orWhere('nama_peminta', 'like', '%' . $name . '%')
                    ->orWhere('unit', 'like', '%' . $name . '%')
                    ->orWhere('tiket', 'like', '%' . $name . '%');
            })
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })

                ->orderBy('created_at', 'desc')
                ->latest()
                ->paginate($perPage);

            return response()->json(['data' => $result], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // Mulai transaksi database
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {
            $ticketNumber = PermintaanLayananBmn::generateTicketNumber();
            $result = PermintaanLayananBmn::create([
                'tiket' => $ticketNumber,
                'nup' => $data->nup,
                'jenis_layanan' => $data->jenis_layanan,
                'nip' => $data->nip,
                'nama_peminta' => $data->nama_peminta,
                'catatan' => $data->catatan ?? null,
                'penerima' => $data->penerima ?? null,
                'unit' => $data->unit ?? null,
                'ttd' => $data->ttd ?? null,
                'no_wa' => $data->no_wa,
                'tanggal_diterima' => $data->tanggal_diterima ?? null,
                'status' => $data->status ?? 'ORDER',
            ]);

            if ($result) {

                $shorten = PesanController::shorten('/#/user/bmn/permintaan/' . $ticketNumber . '/output');
                $pesan = 'Permintaan layanan BMN Nomor Tiket ' . $ticketNumber . ' berhasil dibuat, silahkan menunggu Informasi selanjutnya ' . $shorten . ' (klik link untuk melihat tiket)';

                PesanController::kirimPesan($data->no_wa, $pesan);
            }

            DB::commit();
            return response()->json(['data' => $result], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $result = PermintaanLayananBmn::with('bmn')
                ->where('tiket', $id)
                ->first();
            return response()->json(['data' => $result], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateDoneBawa(Request $request, $id)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {
            $result = PermintaanLayananBmn::with('bmn')->findOrFail($id);
            $result->update([
                'status' => $data->status,
                'tanggal_diterima' => Carbon::createFromFormat('d F Y', $data->tanggalPenerimaan)->format('Y-m-d'),
                'penerima' => $data->name,
                'ttd' => $data->image,
            ]);
            // Commit transaksi jika berhasil
            DB::commit();
            // Berikan respons sukses
            return response()->json(['data' => $result, 'message' => 'Data berhasil diperbarui'], 200);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollback();
            // Berikan respons error
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateDoneBalik(Request $request, $id)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {
            $result = PermintaanLayananBmn::with('bmn')->findOrFail($id);
            $result->update([
                'status' => $data->status,
                'tanggal_terima_pengembalian' => Carbon::createFromFormat('d F Y', $data->tanggalPenerimaan)->format('Y-m-d'),
                'penerima_pengembalian' => $data->name,
                'ttd_pengembalian' => $data->image,
            ]);
            // Commit transaksi jika berhasil
            DB::commit();
            // Berikan respons sukses
            return response()->json(['data' => $result, 'message' => 'Data berhasil diperbarui'], 200);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollback();
            // Berikan respons error
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getStatus($tiket)
    {
        $data = PermintaanLayananBmn::where('tiket', $tiket)->first();
        if ($data) {
            if ($data->status == 'DONE') {
                return 'delete';
            } else {
                return $data->status;
            }
        } else {
            return 'delete';
        }
    }
}
