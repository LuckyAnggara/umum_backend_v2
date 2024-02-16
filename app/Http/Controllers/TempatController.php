<?php

namespace App\Http\Controllers;

use App\Models\Tempat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TempatController extends BaseController
{
    public function index(Request $request)
    {
        $name = $request->input('query');
        $date = $request->input('date');

        try {
            // Mengambil data inventaris dengan paginasi
            $tempat = Tempat::where('ruangan', $name)->when($date, function ($query, $date) {
                return $query->whereDate('tanggal', $date);
            })->get();
            return response()->json(['data' => $tempat], 200);
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
            $result = Tempat::create([
                'ruangan' => $data->ruangan,
                'tanggal' => $data->tanggal,
                'jam_mulai' => $this->convertDate($data->tanggal, $data->jam_mulai),
                'jam_akhir' => $this->convertDate($data->tanggal, $data->jam_akhir),
                'nip' => $data->nip ?? null,
                'nama' => $data->nama,
                'jumlah_peserta' => $data->jumlah_peserta,
                'unit' => $data->unit,
                'no_wa' => $data->no_wa,
                'status' => 'BELUM SELESAI',
                'kegiatan' => $data->kegiatan,
            ]);

            $pesan = 'Booking kegiatan di tanggal ' . $data->tanggal;
            PesanController::kirimPesan($data->no_wa, $pesan);
            DB::commit();
            return $this->sendResponse($result, 'Data berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), 'Error');
        }
    }

    public function destroy($id, Request $request)
    {
        // $no_wa = '082116562811';

        try {
            // Cari dan hapus data bmn berdasarkan ID
            $tempat = Tempat::findOrFail($id);

            if ($tempat) {
                PesanController::kirimPesan($tempat->no_wa, $request->input('pesan'));
                $tempat->delete();
            }
            // $tempat->delete();
            // Berikan respons sukses
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }

    function convertDate($date, $time)
    {
        // Konversi objek waktu ke format string
        $timeString = sprintf('%02d:%02d:%02d', $time->hours, $time->minutes, $time->seconds);

        $carbonDateTime = Carbon::parse($date);

        // Format objek Carbon sesuai dengan kebutuhan
        $formattedDate = $carbonDateTime->format('Y-m-d');

        // Gabungkan tanggal dan waktu
        $dateTimeString = $formattedDate . ' ' . $timeString;

        // Buat objek Carbon dari string yang sudah diformat
        $carbonDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        // Format objek Carbon sesuai dengan kebutuhan
        $formattedDateTime = $carbonDateTime->format('Y-m-d\TH:i:s');
        return $formattedDateTime;
    }
}
