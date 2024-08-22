<?php

namespace App\Http\Controllers;

use App\Models\DetailPermintaanPersediaan;
use App\Models\MutasiPersediaan;
use App\Models\PermintaanPersediaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermintaanPersediaanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('limit', 5);
        $name = $request->input('query');
        $status = $request->input('status');

        try {
            // Mengambil data inventaris dengan paginasi
            $result = PermintaanPersediaan::with('detail')->when($name, function ($query, $name) {
                return $query->where('nama', 'like', '%' . $name . '%')
                    ->orWhere('tiket', 'like', '%' . $name . '%')
                    ->orWhere('nip', 'like', '%' . $name . '%')
                    ->orWhere('unit', 'like', '%' . $name . '%');
            })->when($status, function ($query, $status) {
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
        $request->validate([
            'nama' => 'required|string',
            'detail' => 'required|array',
        ]);
        // Mulai transaksi database
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {

            $ticketNumber = PermintaanPersediaan::generateTicketNumber();
            $result = PermintaanPersediaan::create([
                'tiket' => $ticketNumber,
                'nama' => $data->nama,
                'unit' => $data->unit,
                'nip' => $data->nip,
                'no_wa' => $data->no_wa,
                'catatan' => $data->catatan,
                'status' => 'ORDER',
                'user_id' => Auth::id(),
            ]);

            if ($result) {
                foreach ($data->detail as $key => $value) {
                    DetailPermintaanPersediaan::create([
                        'permintaan_persediaan_id' => $result->id,
                        'inventory_id' => $value->id,
                        'jumlah' => $value->qty,
                        'checked' => true,
                    ]);
                }

                $catatan = 'Permintaan persediaan baru telah dibuat';
                $shorten = PesanController::shorten('/#/user/persediaan/permintaan/' . $ticketNumber . '/output');
                $pesan = 'Permintaan Persediaan Nomor Tiket ' . $ticketNumber . ' berhasil dibuat, silahkan menunggu Informasi selanjutnya ' . $shorten . ' (klik link untuk melihat tiket)';

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
            $result = PermintaanPersediaan::with('detail.persediaan', 'log')->where('tiket', $id)->first();
            return response()->json(['data' => $result], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {
            // Cari dan update data PermintaanPersediaan berdasarkan ID
            $result = PermintaanPersediaan::with('detail')->findOrFail($id);
            $result->update([
                'status' => $data->status
            ]);

            if ($data->status == 'APPROVE') {
                $catatan = 'Permintaan di terima';
                foreach ($data->detail as $key => $value) {
                    $detail = DetailPermintaanPersediaan::find($value->id);
                    $detail->update([
                        'jumlah_accept' => $value->confirm_permintaan,
                        'checked' => $value->checked
                    ]);
                }
                LogPermintaanPersediaanController::createLogPermintaan($result->id, 'APPROVE', $catatan, Auth::user()->name);
            } else if ($data->status == 'PROCESS') {
                $catatan = 'Permintaan di proses';
                LogPermintaanPersediaanController::createLogPermintaan($result->id, 'PROCESS', $catatan, Auth::user()->name);

                $pesan = 'Permintaan Persediaan Nomor Tiket ' . $result->tiket . ' sedang dalam proses oleh Bagian UMUM';
                PesanController::kirimPesan($result->no_wa, $pesan);
            } else if ($data->status == 'DONE') {
                $catatan = 'Permintaan selesai';
                LogPermintaanPersediaanController::createLogPermintaan($result->id, 'DONE', $catatan, Auth::user()->name);
            } else {
                $catatan = 'Permintaan di tolak';
                $pesan = 'Permintaan Persediaan Nomor Tiket ' . $result->tiket . ' di *TOLAK* oleh Bagian UMUM';
                PesanController::kirimPesan($result->no_wa, $pesan);
                LogPermintaanPersediaanController::createLogPermintaan($result->id, 'REJECT', $catatan, Auth::user()->name);
            }
            $output = PermintaanPersediaan::with('detail.persediaan', 'log')->findOrFail($id);
            // Commit transaksi jika berhasil
            DB::commit();
            // Berikan respons sukses
            return response()->json(['data' => $output, 'message' => 'Data berhasil diperbarui'], 200);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollback();
            // Berikan respons error
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateDone(Request $request, $id)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {

            $result = PermintaanPersediaan::with('detail.persediaan', 'log')->findOrFail($id);
            $result->update([
                'status' => $data->status,
                'tanggal_diterima' => Carbon::createFromFormat('d F Y', $data->tanggalPenerimaan)->format('Y-m-d'),
                'penerima' => $data->name,
                'ttd' => $data->image,
            ]);

            $catatan = 'Permintaan selesai';
            foreach ($result->detail as $key => $value) {
                if ($value->checked == true) {
                    MutasiPersediaanController::createMutasi($value->inventory_id, 'KREDIT', $value->jumlah_accept, 'PERMINTAAN DARI TIKET NOMOR #' . $result->tiket);
                }
            }

            LogPermintaanPersediaanController::createLogPermintaan($result->id, 'DONE', $catatan, $data->name);
            $output = PermintaanPersediaan::with('detail.persediaan', 'log')->findOrFail($id);


            // Commit transaksi jika berhasil
            DB::commit();
            // Berikan respons sukses
            return response()->json(['data' => $output, 'message' => 'Data berhasil diperbarui'], 200);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollback();
            // Berikan respons error
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updateUndo(Request $request, $id)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {

            $result = PermintaanPersediaan::with('detail')->findOrFail($id);
            $result->update([
                'status' => 'ORDER',
                'tanggal_diterima' => null,
                'penerima' => null,
                'ttd' => null,
            ]);

            $catatan = 'Permintaan di reset ke proses awal';

            foreach ($result->detail as $key => $value) {
                $detail = DetailPermintaanPersediaan::find($value->id);
                $detail->update([
                    'jumlah_accept' => $value->jumlah,
                    'checked' => true
                ]);
            }
            LogPermintaanPersediaanController::createLogPermintaan($result->id, 'UNDO', $catatan, Auth::user()->name);
            $output = PermintaanPersediaan::with('detail.persediaan', 'log')->findOrFail($id);
            // Commit transaksi jika berhasil
            DB::commit();
            // Berikan respons sukses
            return response()->json(['data' => $output, 'message' => 'Data berhasil diperbarui'], 200);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollback();
            // Berikan respons error
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function getStatus($tiket)
    {

        $data = PermintaanPersediaan::where('tiket', $tiket)->first();

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
