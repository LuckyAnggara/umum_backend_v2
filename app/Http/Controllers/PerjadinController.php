<?php

namespace App\Http\Controllers;

use App\Models\Perjadin;
use App\Models\PerjadinDetail;
use App\Models\PerjadinDetailDarat;
use App\Models\PerjadinDetailHotel;
use App\Models\PerjadinDetailPesawat;
use App\Models\PerjadinDetailRep;
use App\Models\PerjadinDetailTransport;
use App\Models\PerjadinDetailUh;
use App\Models\PerjadinLampiran;
use App\Models\PerjadinLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PerjadinController extends BaseController
{
    public function index(Request $request)
    {
        $name = $request->input('query');
        $status = $request->input('status');
        $startDate = $request->input('start-date');
        $endDate = $request->input('end-date');
        $perPage = $request->input('limit', 5);
        $isAdmin = $request->input('is-admin', false);
        $status = $request->input('status');

        try {
            // Mengambil data inventaris dengan paginasi
            $agenda = Perjadin::with('user.unit')
                ->when($name, function ($query, $name) {
                    return $query
                        ->where('nama_kegiatan', 'like', '%' . $name . '%')
                        ->orWhere('tempat_kegiatan', 'like', '%' . $name . '%')
                        ->orWhere('no_st', 'like', '%' . $name . '%');
                })
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('tanggal', [$startDate, $endDate]);
                })
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

    public function store(Request $request)
    {
        $umum = json_decode($request->input('umum'));

        DB::beginTransaction();

        try {
            $result = Perjadin::create([
                'tahun_anggaran' => $umum->tahun_anggaran,
                'no_st' => $umum->no_st,
                'tanggal_st' => Carbon::parse($umum->tanggal_st)->format('Y-m-d'),
                'tanggal_awal' => Carbon::parse($umum->tanggal_awal)->format('Y-m-d'),
                'tanggal_akhir' => Carbon::parse($umum->tanggal_akhir)->format('Y-m-d'),
                'nama_kegiatan' => $umum->nama_kegiatan,
                'tempat_kegiatan' => $umum->tempat_kegiatan,
                'provinsi_id' => $umum->provinsi_id,
                'mak_id' => $umum->mak->id,
                'total_anggaran' => $umum->total_anggaran,
                'total_realisasi' => 0,
                'status' => 'PERENCANAAN',
                'user_id' => Auth::id(),
            ]);

            if ($result) {
                foreach ($umum->detail as $key => $detail) {
                    $details = PerjadinDetail::create([
                        'perjadin_id' => $result->id,
                        'tanggal_sppd' => Carbon::parse($umum->tanggal_st)->format('Y-m-d'),
                        'nip' => $detail->nip,
                        'nama' => $detail->nama,
                        'jabatan' => $detail->jabatan,
                        'pangkat' => $detail->pangkat,
                        'unit' => $detail->unit,
                        'peran' => $detail->peran,
                        'tanggal_awal' => Carbon::parse($detail->tanggal_awal)->format('Y-m-d'),
                        'tanggal_akhir' => Carbon::parse($detail->tanggal_akhir)->format('Y-m-d'),
                        'jumlah_hari' => $detail->jumlah_hari ?? 0,
                    ]);

                    // STORE HOTEL
                    foreach ($detail->hotel as $key => $hotel) {
                        $hot = PerjadinDetailHotel::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $hotel->keterangan,
                            'hari' => $hotel->hari,
                            'realisasi_hari' => $hotel->hari,
                            'biaya' => $hotel->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                    }
                    // STORE TRANSPORT
                    foreach ($detail->transport as $key => $transport) {
                        $pes = PerjadinDetailTransport::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $transport->keterangan,
                            'tipe' => $transport->tipe,
                            'biaya' => $transport->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                    }
                    // STORE UH
                    foreach ($detail->uang_harian as $key => $uang_harian) {
                        $dar = PerjadinDetailUh::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $uang_harian->keterangan,
                            'hari' => $uang_harian->hari,
                            'realisasi_hari' => $hotel->hari,
                            'biaya' => $uang_harian->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                    }
                    // STORE REPRESENTATIF
                    foreach ($detail->representatif as $key => $representatif) {
                        $dar = PerjadinDetailRep::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $representatif->keterangan,
                            'hari' => $representatif->hari,
                            'realisasi_hari' => $hotel->hari,
                            'biaya' => $representatif->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                    }
                }
                for ($i = 0; $i < $request->jumlah_lampiran; $i++) {
                    $file_path = $request->file[$i]->store('perjadin/perencanaan', 'public');
                    $detail = PerjadinLampiran::create([
                        'perjadin_id' => $result->id,
                        'file_name' => $request->file[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
                    ]);
                }

                $catatan = 'Perencanaan Perjalanan Dinas telah di Buat';
                PerjadinLogController::createLogPerjadin($result->id, 'PERENCANAAN', $catatan);
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
            $result = Perjadin::where('id', $id)->with('mak', 'detail.hotel', 'detail.transport', 'detail.uang_harian', 'detail.representatif', 'detail.ppk', 'detail.bendahara', 'lampiran', 'provinsi')->first();
            return $this->sendResponse($result, 'Ada');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 'Error');
        }
    }


    // BELUM BERES
    public function update(Request $request, $id)
    {
        $umum = json_decode($request->input('umum'));
        $editDetail = json_decode($request->input('editDetail'));

        DB::beginTransaction();
        try {
            $perjadin = Perjadin::where('id', $id)->with('detail')->first();
            // return Storage::url($inventory->image);
            $perjadin->update([
                'tahun_anggaran' => $umum->tahun_anggaran,
                'no_st' => $umum->no_st,
                'tanggal_st' => Carbon::parse($umum->tanggal_st)->format('Y-m-d'),
                'tanggal_awal' => Carbon::parse($umum->tanggal_awal)->format('Y-m-d'),
                'tanggal_akhir' => Carbon::parse($umum->tanggal_akhir)->format('Y-m-d'),
                'nama_kegiatan' => $umum->nama_kegiatan,
                'tempat_kegiatan' => $umum->tempat_kegiatan,
                'mak_id' => $umum->mak->id,
                'total_anggaran' => $umum->total_anggaran,
                'total_realisasi' => 0,
                'status' => 'PERENCANAAN',
                'user_id' => Auth::id(),
            ]);

            if ($editDetail) {
                foreach ($perjadin->detail as $key => $value) {
                    $value->delete();
                }
                foreach ($umum->detail as $key => $detail) {

                    // if ($detail->id) {
                    //     $details = PerjadinDetail::findOrFail($detail->id);
                    //     $details->update([
                    //         'tanggal_sppd' => Carbon::parse($umum->tanggal_st)->format('Y-m-d'),
                    //         'nip' => $detail->nip,
                    //         'nama' => $detail->nama,
                    //         'jabatan' => $detail->jabatan,
                    //         'pangkat' => $detail->pangkat,
                    //         'unit' => $detail->unit,
                    //         'peran' => $detail->peran,
                    //         'tanggal_awal' => Carbon::parse($detail->tanggal_awal)->format('Y-m-d'),
                    //         'tanggal_akhir' => Carbon::parse($detail->tanggal_akhir)->format('Y-m-d'),
                    //         'jumlah_hari' => $detail->jumlah_hari,
                    //     ]);
                    // } else {
                    $details = PerjadinDetail::create([
                        'perjadin_id' => $perjadin->id,
                        'tanggal_sppd' => Carbon::parse($umum->tanggal_st)->format('Y-m-d'),
                        'nip' => $detail->nip,
                        'nama' => $detail->nama,
                        'jabatan' => $detail->jabatan,
                        'pangkat' => $detail->pangkat,
                        'unit' => $detail->unit,
                        'peran' => $detail->peran,
                        'tanggal_awal' => Carbon::parse($detail->tanggal_awal)->format('Y-m-d'),
                        'tanggal_akhir' => Carbon::parse($detail->tanggal_akhir)->format('Y-m-d'),
                        'jumlah_hari' => $detail->jumlah_hari,
                    ]);
                    // }

                    // STORE HOTEL
                    foreach ($detail->hotel as $key => $hotel) {
                        $hot = PerjadinDetailHotel::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $hotel->keterangan,
                            'hari' => $hotel->hari,
                            'realisasi_hari' => $hotel->hari,
                            'biaya' => $hotel->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                    }
                    // STORE TRANSPORT
                    foreach ($detail->transport as $key => $transport) {
                        $pes = PerjadinDetailTransport::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $transport->keterangan,
                            'tipe' => $transport->tipe,
                            'biaya' => $transport->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                    }
                    // STORE UH
                    foreach ($detail->uang_harian as $key => $uang_harian) {
                        $dar = PerjadinDetailUh::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $uang_harian->keterangan,
                            'hari' => $uang_harian->hari,
                            'realisasi_hari' => $hotel->hari,
                            'biaya' => $uang_harian->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                    }
                    // STORE REPRESENTATIF
                    foreach ($detail->representatif as $key => $representatif) {
                        $dar = PerjadinDetailRep::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $representatif->keterangan,
                            'hari' => $representatif->hari,
                            'realisasi_hari' => $hotel->hari,
                            'biaya' => $representatif->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                    }
                }
            }

            if ($request->jumlah_lampiran_delete > 0) {
                for ($i = 0; $i < $request->jumlah_lampiran_delete; $i++) {
                    $lampiranId = $request->file_delete[$i];
                    $file = PerjadinLampiran::findOrFail($lampiranId);
                    if ($file) {
                        Storage::disk('public')->delete($file->lampiran);
                        $file->delete();
                    }
                }
            }

            if ($request->jumlah_lampiran > 0) {
                for ($i = 0; $i < $request->jumlah_lampiran; $i++) {
                    $file_path = $request->file[$i]->store('perjadin/perencanaan', 'public');
                    $detail = PerjadinLampiran::create([
                        'perjadin_id' => $perjadin->id,
                        'file_name' => $request->file[$i]->getClientOriginalName(),
                        'lampiran' => $file_path,
                    ]);
                }
            }
            $catatan = 'Perjalanan Dinas telah di di perbaharui';
            PerjadinLogController::createLogPerjadin($perjadin->id, 'PEMBAHARUAN', $catatan);

            $result = Perjadin::where('id', $id)->with('mak', 'detail.hotel', 'detail.transport', 'detail.uang_harian', 'detail.representatif', 'detail.ppk', 'detail.bendahara', 'lampiran', 'provinsi')->first();
            DB::commit();
            return $this->sendResponse($result, 'Data berhasil di perbaharui');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), 'Error');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {
            if ($request->status == 'VERIFIKASI') {
                $perjadin = Perjadin::findOrFail($id);
                $perjadin->update([
                    'status' => $request->status,
                ]);
                $result = Perjadin::with('user.unit')->where('id', $id)->first();
            } elseif ($data->status == 'PERTANGGUNG JAWABAN') {
                $perjadin = Perjadin::with('detail')->where('id', $id)->first();
                $perjadin->update([
                    'status' => $data->status,
                ]);
                foreach ($perjadin->detail as $key => $detail) {
                    $latestSppd = PerjadinDetail::max('no_sppd'); // Replace 'Detail' with your actual model if different
                    // Increment the number
                    $nextSppd = $latestSppd + 1;
                    // Format the number to 4 digits with leading zeros
                    $no_sppd = sprintf('%04d', $nextSppd);
                    $detail->update([
                        'no_sppd' => $no_sppd,
                        'ppk' => $data->ppk->id,
                        'bendahara' => $data->bendahara->id,
                    ]);
                }

                $result = Perjadin::where('id', $id)->with('mak', 'detail.hotel', 'detail.transport', 'detail.uang_harian', 'detail.representatif', 'lampiran', 'detail.ppk', 'detail.bendahara', 'provinsi')->first();
            }
            PerjadinLogController::createLogPerjadin($perjadin->id, $request->status, $request->catatan);

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
            $result = Perjadin::with('mak', 'detail.hotel', 'detail.transport', 'detail.uang_harian', 'detail.representatif', 'lampiran')->where('id', $id)->first();
            if (count($result->lampiran) > 0) {
                foreach ($result->lampiran as $key => $lampiran) {
                    Storage::disk('public')->delete($lampiran->lampiran);
                }
            }
            if (count($result->detail) > 0) {
                foreach ($result->detail as $key => $detail) {
                    if ($detail->hotel) {
                        foreach ($detail->hotel as $key => $hotel) {
                            $hotel->delete();
                        }
                    }
                    if ($detail->transport) {
                        foreach ($detail->transport as $key => $transport) {
                            $transport->delete();
                        }
                    }
                    if ($detail->uh) {
                        foreach ($detail->uh as $key => $uh) {
                            $uh->delete();
                        }
                    }
                    if ($detail->rep) {
                        foreach ($detail->rep as $key => $rep) {
                            $rep->delete();
                        }
                    }
                    $detail->delete();
                }
            }
            $result->delete();
            // Berikan respons sukses
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
