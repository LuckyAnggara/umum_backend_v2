<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AgendaLampiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AgendaController extends BaseController
{
    public function index(Request $request)
    {
        $pimpinan = $request->input('query');
        $date = $request->input('date');
        $startDate = $request->input('start-date');
        $endDate = $request->input('end-date');


        try {
            // Mengambil data inventaris dengan paginasi
            $agenda = Agenda::with('lampiran')->where('pimpinan', $pimpinan)->when($date, function ($query, $date) {
                return $query->whereDate('tanggal', $date);
            })->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {

                return $query->whereBetween('tanggal', [$startDate, $endDate]);
            })

                ->get();
            return response()->json(['data' => $agenda], 200);
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
            $result = Agenda::create([
                'kegiatan' => $request->kegiatan,
                'tanggal' =>  Carbon::parse($request->tanggal)->toDateString(),
                'jam_mulai' => $this->convertDate($request->tanggal, $request->jam_mulai['hours'], $request->jam_mulai['minutes'], $request->jam_mulai['seconds'],),
                'jam_akhir' => $this->convertDate($request->tanggal, $request->jam_akhir['hours'], $request->jam_akhir['minutes'], $request->jam_akhir['seconds']),
                'pimpinan' => $request->pimpinan,
                'tempat' => $request->tempat,
                'status' => 'BELUM SELESAI',
                'user_id' => Auth::id(),
            ]);

            if ($result) {
                for ($i = 0; $i < $request->jumlah_lampiran; $i++) {
                    $file_path = $request->file[$i]->store('agenda', 'public');
                    $detail = AgendaLampiran::create([
                        'agenda_id' => $result->id,
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
            $agenda = Agenda::findOrFail($id);
            if ($agenda) {
                $lampiran = AgendaLampiran::where('agenda_id', $agenda->id)->get();
                foreach ($lampiran as $key => $value) {
                    Storage::disk('public')->delete($value->lampiran);
                    $value->delete();
                }
                $agenda->delete();
            }

            // Berikan respons sukses
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }

    function convertDate($date, $hours, $minutes, $seconds)
    {
        // Konversi objek waktu ke format string
        $timeString = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        $carbonDateTime = Carbon::parse($date);

        // Format objek Carbon sesuai dengan kebutuhan
        $formattedDate = $carbonDateTime->format('Y-m-d');

        // Gabungkan tanggal dan waktu
        $dateTimeString = $formattedDate . ' ' . $timeString;

        // Buat objek Carbon dari string yang sudah diformat
        $carbonDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        // Format objek Carbon sesuai dengan kebutuhan
        $formattedDateTime = $carbonDateTime->format('H:i:s');
        return $formattedDateTime;
    }
}
