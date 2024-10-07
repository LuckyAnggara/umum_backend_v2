<?php

namespace App\Http\Controllers;

use App\Models\NonPerjadin;
use App\Models\NonPerjadinLampiran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NonPerjadinController extends BaseController
{
    public function index(Request $request)
    {
        $name = $request->input('query');
        $startDate = $request->input('start-date');
        $endDate = $request->input('end-date');
        $date = $request->input('date');
        $perPage = $request->input('limit', 5);
        $isAdmin = $request->input('is-admin', false);
        $status = $request->input('status');
        $unit = $request->input('unit');

        try {
            // Mengambil data inventaris dengan paginasi
            $agenda = NonPerjadin::with('user', 'unit')
                ->when($name, function ($query, $name) {
                    return $query
                        ->where('uraian', 'like', '%' . $name . '%')
                        ->orWhere('nomor_transaksi', 'like', '%' . $name . '%');
                })->when($unit, function ($query, $unit) {
                    return $query
                        ->where('unit_id', $unit);
                })
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($date, function ($query) use ($date) {
                    // Original date string with the double timezone

                    return $query->whereDate('tanggal_transaksi',  Carbon::parse($date)->format('Y-m-d'));
                })
                // ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                //     return $query->whereBetween('created_at', [$startDate, $endDate]);
                // })
                ->when($isAdmin, function ($query) {
                    return $query->where('user_id', Auth::id());
                })
                ->orderBy('created_at', 'desc')
                ->latest()
                ->paginate($perPage);

            return response()->json(['data' => $agenda], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    //
    public function store(Request $request)
    {
        $umum = json_decode($request->input('umum'));

        DB::beginTransaction();

        try {

            $latestTrx = NonPerjadin::max('nomor_transaksi'); // Replace 'Detail' with your actual model if different
            // Increment the number
            $noTrx = $latestTrx + 1;
            // Format the number to 4 digits with leading zeros
            $no_trx = sprintf('%04d', $noTrx);

            $result = NonPerjadin::create([
                'tahun_anggaran' => $umum->tahun_anggaran,
                'nomor_transaksi' => $no_trx,
                'tanggal_transaksi' => Carbon::parse($umum->tanggal_transaksi)->format('Y-m-d'),
                'uraian' => $umum->uraian,
                'mak_id' => $umum->mak->id,
                'ppk' => $umum->ppk->id,
                'bendahara' => $umum->bendahara->id,
                'total_anggaran' => $umum->total_anggaran,
                'penerima' => $umum->penerima,
                'nip_penerima' => $umum->nip_penerima,
                'total_realisasi' => 0,
                'status' => 'PENGAJUAN',
                'user_id' => Auth::id(),
                'unit_id' => Auth::user()->unit_id,
            ]);

            if ($result) {

                for ($i = 0; $i < $request->jumlah_lampiran; $i++) {
                    $file_path = $request->file[$i]->store('nonperjadin/pengajuan', 'public');
                    $detail = NonPerjadinLampiran::create([
                        'non_perjadin_id' => $result->id,
                        'file_name' => $request->file[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
                    ]);
                }

                // BUAT DETAIL DI MAK
                $mak = MakDetailController::createMakDetail($result, 'NON PERJADIN', 'BELUM');
                $catatan = $umum->catatan;
                NonPerjadinLogController::createLog($result->id, 'PENGAJUAN', $catatan);
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
            $result = NonPerjadin::where('id', $id)->with('mak.detail', 'log', 'lampiran', 'bendahara', 'ppk')->first();
            return $this->sendResponse($result, 'Ada');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 'Error');
        }
    }
}
