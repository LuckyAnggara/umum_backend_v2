<?php

namespace App\Http\Controllers;

use App\Models\MakDetail;
use App\Models\MakNominatifDetail;
use App\Models\Perjadin;
use App\Models\PerjadinDetail;
use App\Models\PerjadinDetailDarat;
use App\Models\PerjadinDetailHotel;
use App\Models\PerjadinDetailPesawat;
use App\Models\PerjadinDetailRep;
use App\Models\PerjadinDetailTaksiJakarta;
use App\Models\PerjadinDetailTaksiTujuan;
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
        $unit = $request->input('unit');

        try {
            // Mengambil data inventaris dengan paginasi
            $agenda = Perjadin::with('user', 'unit')
                ->when($name, function ($query, $name) {
                    return $query
                        ->where('nama_kegiatan', 'like', '%' . $name . '%')
                        ->orWhere('tempat_kegiatan', 'like', '%' . $name . '%')
                        ->orWhere('no_st', 'like', '%' . $name . '%');
                })->when($unit, function ($query, $unit) {
                    return $query
                        ->where('unit_id', $unit);
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
                'tempat_kedudukan' => $umum->tempat_kedudukan,
                'provinsi_id' => $umum->provinsi_id,
                'mak_id' => $umum->mak->id,
                'total_anggaran' => $umum->total_anggaran,
                'total_realisasi' => 0,
                'status' => 'PERENCANAAN',
                'user_id' => Auth::id(),
                'unit_id' => Auth::user()->unit_id,
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
                        'nominatif_hotel_id' => $detail->nominatif_hotel ? $detail->nominatif_hotel->id : null,
                        'nominatif_pesawat_id' => $detail->nominatif_pesawat ? $detail->nominatif_pesawat->id : null,
                        'nominatif_uh_id' => $detail->nominatif_uh ? $detail->nominatif_uh->id : null,
                        'nominatif_transport_id' =>
                        $detail->nominatif_transport ? $detail->nominatif_transport->id : null,
                        'nominatif_taksi_jakarta_id' => $detail->nominatif_taksi_jakarta ? $detail->nominatif_taksi_jakarta->id : null,
                        'nominatif_taksi_tujuan_id' =>
                        $detail->nominatif_taksi_tujuan ? $detail->nominatif_taksi_tujuan->id : null,
                        'nominatif_representatif_id' => $detail->nominatif_representatif ? $detail->nominatif_representatif->id : null,
                        'tanggal_awal' => Carbon::parse($detail->tanggal_awal)->format('Y-m-d'),
                        'tanggal_akhir' => Carbon::parse($detail->tanggal_akhir)->format('Y-m-d'),
                        'jumlah_hari' => $detail->jumlah_hari ?? 0,
                    ]);

                    $total_hotel = 0;
                    $total_uh = 0;
                    $total_pesawat = 0;
                    $total_rep = 0;
                    $total_taksi_jakarta = 0;
                    $total_taksi_tujuan = 0;
                    $total_transport = 0;

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
                        $total_hotel += $hotel->biaya * $hotel->hari;
                    }

                    if ($detail->nominatif_hotel) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_hotel->id,
                            'kegiatan_id' => $result->id,
                            'jumlah' => $total_hotel,
                            'status_realisasi' => 'BELUM'
                        ]);
                    }


                    // STORE PESAWAT
                    foreach ($detail->pesawat as $key => $value) {
                        $pes = PerjadinDetailPesawat::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $value->keterangan,
                            'biaya' => $value->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                        $total_pesawat += $value->biaya;
                    }

                    if ($detail->nominatif_pesawat) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_pesawat->id,
                            'kegiatan_id' => $result->id,
                            'jumlah' => $total_pesawat,
                            'status_realisasi' => 'BELUM'
                        ]);
                    }

                    // STORE TAKSI JAKARTA
                    foreach ($detail->taksi_jakarta as $key => $value) {
                        $val = PerjadinDetailTaksiJakarta::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $value->keterangan,
                            'biaya' => $value->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                        $total_taksi_jakarta += $value->biaya;
                    }

                    if ($detail->nominatif_taksi_jakarta) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_taksi_jakarta->id,
                            'kegiatan_id' => $result->id,
                            'jumlah' => $total_taksi_jakarta,
                            'status_realisasi' => 'BELUM'
                        ]);
                    }

                    // STORE TAKSI TUJUAN
                    foreach ($detail->taksi_tujuan as $key => $value) {
                        $val = PerjadinDetailTaksiTujuan::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $value->keterangan,
                            'biaya' => $value->biaya,
                            'realisasi_biaya' => 0,
                        ]);

                        $total_taksi_tujuan += $value->biaya;
                    }

                    if ($detail->nominatif_taksi_tujuan) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_taksi_tujuan->id,
                            'kegiatan_id' => $result->id,
                            'jumlah' => $total_taksi_tujuan,
                            'status_realisasi' => 'BELUM'
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
                        $total_transport += $transport->biaya;
                    }


                    if ($detail->nominatif_transport) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_transport->id,
                            'kegiatan_id' => $result->id,
                            'jumlah' => $total_transport,
                            'status_realisasi' => 'BELUM'
                        ]);
                    }


                    // STORE UH
                    foreach ($detail->uang_harian as $key => $uang_harian) {
                        $dar = PerjadinDetailUh::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $uang_harian->keterangan,
                            'hari' => $uang_harian->hari,
                            'realisasi_hari' => $uang_harian->hari,
                            'biaya' => $uang_harian->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                        $total_uh += $uang_harian->biaya * $uang_harian->hari;
                    }

                    if ($detail->nominatif_uh) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_uh->id,
                            'kegiatan_id' => $result->id,
                            'jumlah' => $total_uh,
                            'status_realisasi' => 'BELUM'
                        ]);
                    }


                    // STORE REPRESENTATIF
                    foreach ($detail->representatif as $key => $representatif) {
                        $rep = PerjadinDetailRep::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $representatif->keterangan,
                            'hari' => $representatif->hari,
                            'realisasi_hari' => $representatif->hari,
                            'biaya' => $representatif->biaya,
                            'realisasi_biaya' => 0,
                        ]);

                        $total_rep += $representatif->biaya * $representatif->hari;
                    }


                    if ($detail->nominatif_representatif) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_representatif->id,
                            'kegiatan_id' => $result->id,
                            'jumlah' => $total_rep,
                            'status_realisasi' => 'BELUM'
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

                // BUAT DETAIL DI MAK
                $mak = MakDetailController::createMakDetail($result, 'PERJADIN', 'BELUM');
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
            $result = Perjadin::where('id', $id)->with('mak.nominatif.detail', 'log', 'detail.hotel', 'detail.transport', 'detail.pesawat', 'detail.taksi_jakarta',  'detail.taksi_tujuan', 'detail.uang_harian', 'detail.representatif', 'detail.ppk', 'detail.bendahara', 'lampiran', 'provinsi', 'detail.nominatif_hotel.detail', 'detail.nominatif_uh.detail', 'detail.nominatif_transport.detail', 'detail.nominatif_pesawat.detail', 'detail.nominatif_taksi_jakarta.detail', 'detail.nominatif_taksi_tujuan.detail', 'detail.nominatif_representatif.detail')->first();
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
            $perjadin =
                Perjadin::where('id', $id)->with('mak.nominatif.detail', 'log', 'detail.hotel', 'detail.transport', 'detail.pesawat', 'detail.taksi_jakarta',  'detail.taksi_tujuan', 'detail.uang_harian', 'detail.representatif', 'detail.ppk', 'detail.bendahara', 'lampiran', 'provinsi', 'detail.nominatif_hotel.detail', 'detail.nominatif_uh.detail', 'detail.nominatif_transport.detail', 'detail.nominatif_pesawat.detail', 'detail.nominatif_taksi_jakarta.detail', 'detail.nominatif_taksi_tujuan.detail', 'detail.nominatif_representatif.detail')->first();
            // return Storage::url($inventory->image);
            $perjadin->update([
                'tahun_anggaran' => $umum->tahun_anggaran,
                'no_st' => $umum->no_st,
                'tanggal_st' => Carbon::parse($umum->tanggal_st)->format('Y-m-d'),
                'tanggal_awal' => Carbon::parse($umum->tanggal_awal)->format('Y-m-d'),
                'tanggal_akhir' => Carbon::parse($umum->tanggal_akhir)->format('Y-m-d'),
                'nama_kegiatan' => $umum->nama_kegiatan,
                'tempat_kegiatan' => $umum->tempat_kegiatan,
                'tempat_kedudukan' => $umum->tempat_kedudukan,
                'mak_id' => $umum->mak->id,
                'total_anggaran' => $umum->total_anggaran,
                'total_realisasi' => 0,
                'status' => 'PERENCANAAN',
                'user_id' => Auth::id(),
            ]);


            // UBAH DETAIL MAK

            $mak = MakDetail::where('TYPE', 'PERJADIN')->where('kegiatan_id', $id)->first();
            $mak->delete();
            $mak = MakDetailController::createMakDetail($perjadin, 'PERJADIN', 'BELUM');


            if ($editDetail) {
                $nominatifDetail = MakNominatifDetail::where('kegiatan_id', $id)->get();
                if ($nominatifDetail) {
                    foreach ($nominatifDetail as $key => $value) {
                        $value->delete();
                    }
                }
                foreach ($perjadin->detail as $key => $value) {
                    if ($value->hotel) {
                        foreach ($value->hotel as $key => $hotel) {
                            $hotel->delete();
                        }
                    }
                    if ($value->pesawat) {
                        foreach ($value->pesawat as $key => $value) {
                            $value->delete();
                        }
                    }
                    if ($value->taksi_tujuan) {
                        foreach ($value->taksi_tujuan as $key => $value) {
                            $value->delete();
                        }
                    }
                    if ($value->taksi_jakarta) {
                        foreach ($value->taksi_jakarta as $key => $value) {
                            $value->delete();
                        }
                    }
                    if ($value->transport) {
                        foreach ($value->transport as $key => $transport) {
                            $transport->delete();
                        }
                    }
                    if ($value->uang_harian) {
                        foreach ($value->uang_harian as $key => $value) {
                            $value->delete();
                        }
                    }
                    if ($value->representatif) {
                        foreach ($value->representatif as $key => $value) {
                            $value->delete();
                        }
                    }
                    $value->delete();
                }
                foreach ($umum->detail as $key => $detail) {
                    $details = PerjadinDetail::create([
                        'perjadin_id' => $perjadin->id,
                        'tanggal_sppd' => Carbon::parse($umum->tanggal_st)->format('Y-m-d'),
                        'nip' => $detail->nip,
                        'nama' => $detail->nama,
                        'jabatan' => $detail->jabatan,
                        'pangkat' => $detail->pangkat,
                        'unit' => $detail->unit,
                        'peran' => $detail->peran,
                        'nominatif_hotel_id' => $detail->nominatif_hotel ? $detail->nominatif_hotel->id : null,
                        'nominatif_pesawat_id' => $detail->nominatif_pesawat ? $detail->nominatif_pesawat->id : null,
                        'nominatif_uh_id' => $detail->nominatif_uh ? $detail->nominatif_uh->id : null,
                        'nominatif_transport_id' =>
                        $detail->nominatif_transport ? $detail->nominatif_transport->id : null,
                        'nominatif_taksi_jakarta_id' => $detail->nominatif_taksi_jakarta ? $detail->nominatif_taksi_jakarta->id : null,
                        'nominatif_taksi_tujuan_id' =>
                        $detail->nominatif_taksi_tujuan ? $detail->nominatif_taksi_tujuan->id : null,
                        'nominatif_representatif_id' => $detail->nominatif_representatif ? $detail->nominatif_representatif->id : null,
                        'tanggal_awal' => Carbon::parse($detail->tanggal_awal)->format('Y-m-d'),
                        'tanggal_akhir' => Carbon::parse($detail->tanggal_akhir)->format('Y-m-d'),
                        'jumlah_hari' => $detail->jumlah_hari ?? 0,
                    ]);

                    $total_hotel = 0;
                    $total_uh = 0;
                    $total_pesawat = 0;
                    $total_rep = 0;
                    $total_taksi_jakarta = 0;
                    $total_taksi_tujuan = 0;
                    $total_transport = 0;
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
                        $total_hotel += $hotel->biaya * $hotel->hari;
                    }

                    if ($detail->nominatif_hotel) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_hotel->id,
                            'kegiatan_id' => $details->id,
                            'jumlah' => $total_hotel,
                            'status_realisasi' => 'BELUM'
                        ]);
                    }


                    // STORE PESAWAT
                    foreach ($detail->pesawat as $key => $value) {
                        $pes = PerjadinDetailPesawat::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $value->keterangan,
                            'biaya' => $value->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                        $total_pesawat += $value->biaya;
                    }

                    if ($detail->nominatif_pesawat) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_pesawat->id,
                            'kegiatan_id' => $details->id,
                            'jumlah' => $total_pesawat,
                            'status_realisasi' => 'BELUM'
                        ]);
                    }

                    // STORE TAKSI JAKARTA
                    foreach ($detail->taksi_jakarta as $key => $value) {
                        $val = PerjadinDetailTaksiJakarta::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $value->keterangan,
                            'biaya' => $value->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                        $total_taksi_jakarta += $value->biaya;
                    }

                    if ($detail->nominatif_taksi_jakarta) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_taksi_jakarta->id,
                            'kegiatan_id' => $details->id,
                            'jumlah' => $total_taksi_jakarta,
                            'status_realisasi' => 'BELUM'
                        ]);
                    }

                    // STORE TAKSI TUJUAN
                    foreach ($detail->taksi_tujuan as $key => $value) {
                        $val = PerjadinDetailTaksiTujuan::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $value->keterangan,
                            'biaya' => $value->biaya,
                            'realisasi_biaya' => 0,
                        ]);

                        $total_taksi_tujuan += $value->biaya;
                    }

                    if ($detail->nominatif_taksi_tujuan) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_taksi_tujuan->id,
                            'kegiatan_id' => $details->id,
                            'jumlah' => $total_taksi_tujuan,
                            'status_realisasi' => 'BELUM'
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
                        $total_transport += $transport->biaya;
                    }


                    if ($detail->nominatif_transport) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_transport->id,
                            'kegiatan_id' => $details->id,
                            'jumlah' => $total_transport,
                            'status_realisasi' => 'BELUM'
                        ]);
                    }

                    // STORE UH
                    foreach ($detail->uang_harian as $key => $uang_harian) {
                        $dar = PerjadinDetailUh::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $uang_harian->keterangan,
                            'hari' => $uang_harian->hari,
                            'realisasi_hari' => $uang_harian->hari,
                            'biaya' => $uang_harian->biaya,
                            'realisasi_biaya' => 0,
                        ]);
                        $total_uh += $uang_harian->biaya * $uang_harian->hari;
                    }

                    if ($detail->nominatif_uh) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_uh->id,
                            'kegiatan_id' => $details->id,
                            'jumlah' => $total_uh,
                            'status_realisasi' => 'BELUM'
                        ]);
                    }

                    // STORE REPRESENTATIF
                    foreach ($detail->representatif as $key => $representatif) {
                        $rep = PerjadinDetailRep::create([
                            'perjadin_detail_id' => $details->id,
                            'keterangan' => $representatif->keterangan,
                            'hari' => $representatif->hari,
                            'realisasi_hari' => $representatif->hari,
                            'biaya' => $representatif->biaya,
                            'realisasi_biaya' => 0,
                        ]);

                        $total_rep += $representatif->biaya * $representatif->hari;
                    }
                    if ($detail->nominatif_representatif) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_representatif->id,
                            'kegiatan_id' => $details->id,
                            'jumlah' => $total_rep,
                            'status_realisasi' => 'BELUM'
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

            $result = Perjadin::where('id', $id)->with('mak.nominatif.detail', 'log', 'detail.hotel', 'detail.transport', 'detail.pesawat', 'detail.taksi_jakarta',  'detail.taksi_tujuan', 'detail.uang_harian', 'detail.representatif', 'detail.ppk', 'detail.bendahara', 'lampiran', 'provinsi', 'detail.nominatif_hotel.detail')->first();
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

                $catatan = $request->catatan;
            } else  if ($request->status == 'RAB') {
                $perjadin = Perjadin::findOrFail($id);
                $perjadin->update([
                    'pengusul' => $request->pengusul,
                    'kapokja' => $request->kapokja,
                    'nip_pengusul' => $request->nip_pengusul,
                    'nip_kapokja' => $request->nip_kapokja,
                    'tanggal_rab' => Carbon::parse($request->tanggal_rab)->format('Y-m-d'),
                ]);

                $catatan = 'melakukan perubahan Data pada RAB';
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
                $catatan = $request->catatan;
            } else if ($data->status == 'SELESAI') {
                $perjadin = Perjadin::with('detail')->where('id', $id)->first();
                $perjadin->update([
                    'status' => $data->status,
                    'total_realisasi' => $data->total_realisasi,

                ]);

                $mak = MakDetail::where('TYPE', 'PERJADIN')->where('kegiatan_id', $id)->first();
                if ($mak) {
                    $mak->update([
                        'status_realisasi' => 'SUDAH',
                        'total_realisasi' => $data->total_realisasi,

                    ]);
                }
                $catatan = 'SPD telah selesai di pertanggung jawabkan';
            }

            $result = Perjadin::where('id', $id)->with('mak.nominatif.detail', 'log', 'detail.hotel', 'detail.transport', 'detail.pesawat', 'detail.taksi_jakarta',  'detail.taksi_tujuan', 'detail.uang_harian', 'detail.representatif', 'detail.ppk', 'detail.bendahara', 'lampiran', 'provinsi', 'detail.nominatif_hotel.detail')->first();

            PerjadinLogController::createLogPerjadin($perjadin->id, $request->status, $catatan);

            DB::commit();
            return $this->sendResponse($result, 'Data berhasil di perbaharui');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), 'Error');
        }
    }


    public function updateLampiran(Request $request, $id)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {
            $perjadin = Perjadin::findOrFail($id);
            $catatan = 'Lampiran telah ditambahkan';
            $status = 'Update';

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

            PerjadinLogController::createLogPerjadin($perjadin->id, $status, $catatan);
            DB::commit();
            $result = Perjadin::where('id', $id)->with('mak.nominatif.detail', 'log', 'detail.hotel', 'detail.transport', 'detail.pesawat', 'detail.taksi_jakarta',  'detail.taksi_tujuan', 'detail.uang_harian', 'detail.representatif', 'detail.ppk', 'detail.bendahara', 'lampiran', 'provinsi', 'detail.nominatif_hotel.detail')->first();

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
            $result = Perjadin::where('id', $id)->with('mak.nominatif.detail', 'log', 'detail.catatan', 'detail.lampiran', 'detail.hotel', 'detail.transport', 'detail.pesawat', 'detail.taksi_jakarta',  'detail.taksi_tujuan', 'detail.uang_harian', 'detail.representatif', 'detail.ppk', 'detail.bendahara', 'lampiran', 'provinsi', 'detail.nominatif_hotel.detail')->first();
            if ($result) {
                if (count($result->lampiran) > 0) {
                    foreach ($result->lampiran as $key => $lampiran) {
                        Storage::disk('public')->delete($lampiran->lampiran);
                        $lampiran->delete();
                    }
                }
                if (count($result->log) > 0) {
                    foreach ($result->log as $key => $value) {
                        $value->delete();
                    }
                }
                if (count($result->detail) > 0) {
                    foreach ($result->detail as $key => $detail) {
                        if ($detail->catatan) {
                            foreach ($detail->lampiran as $key => $value) {
                                Storage::disk('public')->delete($value->lampiran);
                                $value->delete();
                            }
                        }
                        if ($detail->catatan) {
                            foreach ($detail->catatan as $key => $value) {
                                $value->delete();
                            }
                        }
                        if ($detail->hotel) {
                            foreach ($detail->hotel as $key => $value) {
                                $value->delete();
                            }
                        }
                        if ($detail->pesawat) {
                            foreach ($detail->pesawat as $key => $value) {
                                $value->delete();
                            }
                        }
                        if ($detail->taksi_tujuan) {
                            foreach ($detail->taksi_tujuan as $key => $value) {
                                $value->delete();
                            }
                        }
                        if ($detail->taksi_jakarta) {
                            foreach ($detail->taksi_jakarta as $key => $value) {
                                $value->delete();
                            }
                        }

                        if ($detail->transport) {
                            foreach ($detail->transport as $key => $transport) {
                                $transport->delete();
                            }
                        }
                        if ($detail->uang_harian) {
                            foreach ($detail->uh as $key => $value) {
                                $value->delete();
                            }
                        }
                        if ($detail->representatif) {
                            foreach ($detail->rep as $key => $value) {
                                $value->delete();
                            }
                        }
                        $detail->delete();
                    }
                }

                $mak = MakDetail::where('type', 'PERJADIN')->where('kegiatan_id', $id)->first();
                if ($mak) {
                    $mak->delete();
                }
                $nominatifDetail = MakNominatifDetail::where('kegiatan_id', $id)->get();
                if ($nominatifDetail) {
                    foreach ($nominatifDetail as $key => $value) {
                        $value->delete();
                    }
                }
                $result->delete();
            }

            // Berikan respons sukses
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
