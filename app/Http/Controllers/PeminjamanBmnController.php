<?php

namespace App\Http\Controllers;

use App\Models\Bmn;
use App\Models\DetailPeminjamanBmn;
use App\Models\PeminjamanBmn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanBmnController extends BaseController
{
    public function index(Request $request)
    {
        $perPage = $request->input('limit', 5);
        $name = $request->input('query');
        $status = $request->input('status');

        try {
            // Mengambil data inventaris dengan paginasi
            $result = PeminjamanBmn::with('bmn')->when($name, function ($query, $name) {
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

    // public function store(Request $request)
    // {
    //     // Mulai transaksi database
    //     $data = json_decode($request->getContent());
    //     DB::beginTransaction();
    //     try {
    //         $ticketNumber = PeminjamanBmn::generateTicketNumber();

    //         $result = PeminjamanBmn::create([
    //             'tiket' => $ticketNumber,
    //             'nup' => $data->nup,
    //             'jenis_layanan' => $data->jenis_layanan,
    //             'nip' => $data->nip,
    //             'nama_peminta' => $data->nama_peminta,
    //             'catatan' => $data->catatan ?? null,
    //             'penerima' => $data->penerima ?? null,
    //             'unit' => $data->unit ?? null,
    //             'ttd' => $data->ttd ?? null,
    //             'no_wa' => $data->no_wa,
    //             'tanggal_diterima' => $data->tanggal_diterima ?? null,
    //             'tanggal_pengembalian' => Carbon::parse($data->tanggal_pengembalian)->toDateTimeString() ?? null,
    //             'status' => $data->status ?? 'VERIFIKASI ADMIN',
    //         ]);

    //         if ($result) {
    //             $bmn = Bmn::where('nup', $data->nup)->first();

    //             if ($bmn) {
    //                 $bmn->sewa = 'ANTRIAN PINJAM';
    //                 $bmn->save();
    //             }


    //             $shorten = PesanController::shorten('/#/user/bmn/peminjaman/' . $ticketNumber . '/output');
    //             $pesan = 'Permintaan peminjaman BMN pengajuan di tanggal ' . $result->created_at . ' dengan Nomor Tiket *' . $ticketNumber . '* berhasil dibuat, silahkan menunggu Informasi selanjutnya ' . $shorten . ' (klik link untuk melihat tiket)';
    //             PesanController::kirimPesan($data->no_wa, $pesan);
    //         }

    //         DB::commit();
    //         return response()->json(['data' => $result], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['message' => $e->getMessage()], 500);
    //     }
    // }


    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'detail' => 'required|array',
        ]);
        // Mulai transaksi database
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {

            $ticketNumber = PeminjamanBmn::generateTicketNumber();
            $result = PeminjamanBmn::create([
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
                'tanggal_pengembalian' => Carbon::parse($data->tanggal_pengembalian)->toDateTimeString() ?? null,
                'status' => $data->status ?? 'VERIFIKASI ADMIN',
            ]);

            if ($result) {
                foreach ($data->detail as $key => $value) {
                    DetailPeminjamanBmn::create([
                        'peminjaman_bmn_id' => $result->id,
                        'bmn_id' => $value->id,
                        'checked' => true,
                    ]);
                }

                $catatan = 'Permintaan peminjaman BMN baru telah dibuat';
                $shorten = PesanController::shorten('/#/user/bmn/peminjaman/' . $ticketNumber . '/output');
                $pesan = 'Permintaan Peminjaman BMN  Nomor Tiket ' . $ticketNumber . ' berhasil dibuat, silahkan menunggu Informasi selanjutnya ' . $shorten . ' (klik link untuk melihat tiket)';

                LogPermintaanPersediaanController::createLogPermintaan($result->id, 'ORDER', $catatan, $data->nama);
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
            $result = PeminjamanBmn::with('bmn')
                ->where('tiket', $id)
                ->first();
            return response()->json(['data' => $result], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateDone(Request $request, $id)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {
            $result = PeminjamanBmn::with('bmn')->findOrFail($id);
            $result->update([
                'status' => $data->status,
                'tanggal_diterima' => Carbon::createFromFormat('d F Y', $data->tanggalPenerimaan)->format('Y-m-d'),
                'penerima' => $data->name,
                'ttd' => $data->image,
            ]);



            if ($result) {
                $detail = Bmn::where('nup', $result->nup)->first();
                $detail->update([
                    'sewa' => 'di pinjam'
                ]);
                $pesan = 'BMN pengajuan Nomor Tiket ' .  $result->tiket . ' di tanggal ' . $result->created_at . ' telah diterima oleh ' . $data->name;
                PesanController::kirimPesan($result->no_wa, $pesan);
            } else {
                $result->update([
                    'status' => 'PENDING'
                ]);
            }

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

    public function update(Request $request, $id)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {
            // Cari dan update data PermintaanPersediaan berdasarkan ID
            $result = PeminjamanBmn::with('bmn')->findOrFail($id);

            $detail = Bmn::where('nup', $result->nup)->first();
            if ($detail->sewa == 'tersedia') {

                $result->update([
                    'status' => $data->status
                ]);

                if ($data->status == 'APPROVE') {

                    $pesan = 'Peminjaman BMN pengajuan Nomor Tiket ' .  $result->tiket . ' di tanggal ' . $result->created_at . ' di Terima';
                    PesanController::kirimPesan($result->no_wa, $pesan);
                } else {
                    $pesan = 'Peminjaman BMN pengajuan Nomor Tiket ' .  $result->tiket . ' di tanggal ' . $result->created_at . ' di Tolak';
                    PesanController::kirimPesan($result->no_wa, $pesan);
                }
            } else {
                $result->update([
                    'status' => 'PENDING'
                ]);
            }
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
        $data = PeminjamanBmn::where('tiket', $tiket)->first();
        if ($data) {

            return $data->status;
        } else {
            return 'delete';
        }
    }

    public function destroy($id)
    {
        try {
            // Cari dan hapus data bmn berdasarkan ID
            $ptj = PeminjamanBmn::findOrFail($id);
            if ($ptj) {
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
