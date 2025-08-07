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
        $tahun = $request->input('tahun');
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
                ->when($tahun, function ($query, $tahun) {
                    return $query->where('tahun_anggaran', $tahun);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('tanggal', [$startDate, $endDate]);
                })
                ->when($isAdmin, function ($query) {
                    return $query->where('user_id', Auth::id());
                })
                ->orderBy(DB::raw('CAST(no_st AS UNSIGNED)'), 'asc')
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
                'jenis_kegiatan' => $umum->jenis_kegiatan,
                'jenis_perjalanan_dinas' => $umum->jenis_perjalanan_dinas,
                'tempat_kegiatan' => $umum->tempat_kegiatan,
                'tempat_kedudukan' => $umum->tempat_kedudukan,
                'provinsi_id' => $umum->jenis_perjalanan_dinas == 'DALAM NEGERI' ? $umum->provinsi_id : null,
                'tujuan_pdln' => $umum->jenis_perjalanan_dinas == 'LUAR NEGERI' ? $umum->tujuan_pdln : null,
                'mak_id' => $umum->mak->id,
                'total_anggaran' => $umum->total_anggaran,
                'total_realisasi' => 0,
                'status' => 'PERENCANAAN',
                'user_id' => Auth::id(),
                'unit_id' => Auth::user()->unit_id,
            ]);

            if ($result) {


                foreach ($umum->detail as $key => $detail) {
                    $tanggal_awal = Carbon::parse($detail->tanggal_awal);
                    $tanggal_akhir = Carbon::parse($detail->tanggal_akhir);
                    $jumlah_hari = $tanggal_awal->diffInDays($tanggal_akhir) + 1;

                    $details = PerjadinDetail::create([
                        'perjadin_id' => $result->id,
                        'tanggal_sppd' => Carbon::parse($umum->tanggal_st)->format('Y-m-d'),
                        'nip' => $detail->nip,
                        'nama' => $detail->nama,
                        'jabatan' => $detail->jabatan,
                        'pangkat' => $detail->pangkat,
                        'unit' => $detail->unit,
                        'peran' => $detail->peran,
                        'nominatif_hotel_id' => $detail->nominatif_hotel->id ?? null,
                        'nominatif_pesawat_id' => $detail->nominatif_pesawat->id ?? null,
                        'nominatif_uh_id' => $detail->nominatif_uh->id ?? null,
                        'nominatif_transport_id' => $detail->nominatif_transport->id ?? null,
                        'nominatif_taksi_jakarta_id' => $detail->nominatif_taksi_jakarta->id ?? null,
                        'nominatif_taksi_tujuan_id' => $detail->nominatif_taksi_tujuan->id ?? null,
                        'nominatif_representatif_id' => $detail->nominatif_representatif->id ?? null,
                        'tanggal_awal' => Carbon::parse($detail->tanggal_awal)->format('Y-m-d'),
                        'tanggal_akhir' => Carbon::parse($detail->tanggal_akhir)->format('Y-m-d'),
                        'jumlah_hari' => $jumlah_hari
                    ]);


                    $categories = [
                        'hotel' => PerjadinDetailHotel::class,
                        'pesawat' => PerjadinDetailPesawat::class,
                        'taksi_jakarta' => PerjadinDetailTaksiJakarta::class,
                        'taksi_tujuan' => PerjadinDetailTaksiTujuan::class,
                        'transport' => PerjadinDetailTransport::class,
                        'uang_harian' => PerjadinDetailUh::class, // Gunakan 'uang_harian'
                        'representatif' => PerjadinDetailRep::class,
                    ];

                    $totals = [];
                    foreach ($categories as $dataKey => $model) {
                        $totals[$dataKey] = 0;

                        // Periksa apakah properti data tersedia
                        if (!isset($detail->$dataKey)) {
                            continue;
                        }

                        foreach ($detail->$dataKey as $item) {
                            $data = [
                                'perjadin_detail_id' => $details->id,
                                'keterangan' => $item->keterangan,
                                'biaya' => $item->biaya,
                                'realisasi_biaya' => 0,
                                'hari' => $item->hari ?? null,
                                'realisasi_hari' => $item->hari ?? null,
                            ];

                            // Tambahkan field tipe khusus untuk kategori transport
                            if ($dataKey === 'transport') {
                                $data['tipe'] = $item->tipe; // Pastikan tipe ada dalam $item
                            }

                            $model::create($data);

                            // Kalkulasi total biaya
                            $totals[$dataKey] += $item->biaya * ($item->hari ?? 1);
                        }

                        // Periksa apakah nominatif ada sebelum diproses
                        $nominatifKey = "nominatif_$dataKey";
                        if (isset($detail->$nominatifKey)) {
                            MakNominatifDetail::create([
                                'mak_nominatif_id' => $detail->$nominatifKey->id,
                                'kegiatan_id' => $result->id,
                                'jumlah' => $totals[$dataKey],
                                'status_realisasi' => 'BELUM',
                            ]);
                        }

                        if (isset($detail->nominatif_uh)) {
                            MakNominatifDetail::create([
                                'mak_nominatif_id' => $detail->nominatif_uh->id,
                                'kegiatan_id' => $result->id,
                                'jumlah' => $totals[$dataKey],
                                'status_realisasi' => 'BELUM',
                            ]);
                        }
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
            $result = Perjadin::where('id', $id)->with('mak.nominatif.detail', 'log', 'log.user', 'detail.catatan', 'detail.hotel', 'detail.transport', 'detail.pesawat', 'detail.taksi_jakarta',  'detail.taksi_tujuan', 'detail.uang_harian', 'detail.representatif', 'detail.ppk', 'detail.bendahara', 'lampiran', 'provinsi', 'detail.nominatif_hotel.detail', 'detail.nominatif_uh.detail', 'detail.nominatif_transport.detail', 'detail.nominatif_pesawat.detail', 'detail.nominatif_taksi_jakarta.detail', 'detail.nominatif_taksi_tujuan.detail', 'detail.nominatif_representatif.detail')->first();
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
                'tanggal_awal' => Carbon::parse($umum->tanggal_awal)->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                'tanggal_akhir' => Carbon::parse($umum->tanggal_akhir)->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                'nama_kegiatan' => $umum->nama_kegiatan,
                'jenis_kegiatan' => $umum->jenis_kegiatan,
                'jenis_perjalanan_dinas' => $umum->jenis_perjalanan_dinas,
                'tempat_kegiatan' => $umum->tempat_kegiatan,
                'tempat_kedudukan' => $umum->tempat_kedudukan,
                'provinsi_id' => $umum->jenis_perjalanan_dinas == 'DALAM NEGERI' ? $umum->provinsi_id : null,
                'tujuan_pdln' => $umum->jenis_perjalanan_dinas == 'LUAR NEGERI' ? $umum->tujuan_pdln : null,
                'mak_id' => $umum->mak->id,
                'total_anggaran' => $umum->total_anggaran,
                'total_realisasi' => 0,
                'status' => 'PERENCANAAN',
            ]);

            if (Auth::user()->role !== 'ADMIN') {
                $perjadin->update([
                    'user_id' => Auth::id(),
                ]);
            }
            // UBAH DETAIL MAK

            $mak = MakDetail::where('TYPE', 'PERJADIN')->where('kegiatan_id', $id)->first();
            if($mak){
                $mak->delete();
            }
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
                        foreach ($value->pesawat as $key => $pesawat) {
                            $pesawat->delete();
                        }
                    }
                    if ($value->taksi_tujuan) {
                        foreach ($value->taksi_tujuan as $key => $taksi_tujuan) {
                            $taksi_tujuan->delete();
                        }
                    }
                    if ($value->taksi_jakarta) {
                        foreach ($value->taksi_jakarta as $key => $taksi_jakarta) {
                            $taksi_jakarta->delete();
                        }
                    }
                    if ($value->transport) {
                        foreach ($value->transport as $key => $transport) {
                            $transport->delete();
                        }
                    }
                    if ($value->uang_harian) {
                        foreach ($value->uang_harian as $key => $uang_harian) {
                            $uang_harian->delete();
                        }
                    }
                    if ($value->representatif) {
                        foreach ($value->representatif as $key => $representatif) {
                            $representatif->delete();
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
                        // 'nominatif_hotel_id' => $detail->nominatif_hotel ? $detail->nominatif_hotel->id : null,
                        // 'nominatif_pesawat_id' => $detail->nominatif_pesawat ? $detail->nominatif_pesawat->id : null,
                        // 'nominatif_uh_id' => $detail->nominatif_uh ? $detail->nominatif_uh->id : null,
                        // 'nominatif_transport_id' =>
                        // $detail->nominatif_transport ? $detail->nominatif_transport->id : null,
                        // 'nominatif_taksi_jakarta_id' => $detail->nominatif_taksi_jakarta ? $detail->nominatif_taksi_jakarta->id : null,
                        // 'nominatif_taksi_tujuan_id' =>
                        // $detail->nominatif_taksi_tujuan ? $detail->nominatif_taksi_tujuan->id : null,
                        // 'nominatif_representatif_id' => $detail->nominatif_representatif ? $detail->nominatif_representatif->id : null,
                        'tanggal_awal' => Carbon::parse($detail->tanggal_awal)->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                        'tanggal_akhir' => Carbon::parse($detail->tanggal_akhir)->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                        'jumlah_hari' => $detail->jumlah_hari ?? 0,
                        'tahun_anggaran' => $umum->tahun_anggaran,
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

                    if ($detail->nominatif_hotel_id) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_hotel_id,
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

                    if ($detail->nominatif_pesawat_id) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_pesawat_id,
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

                    if ($detail->nominatif_taksi_jakarta_id) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_taksi_jakarta_id,
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

                    if ($detail->nominatif_taksi_tujuan_id) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_taksi_tujuan_id,
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

                    if ($detail->nominatif_transport_id) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_transport_id,
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

                    if ($detail->nominatif_uh_id) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_uh_id,
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
                    if ($detail->nominatif_representatif_id) {
                        MakNominatifDetail::create([
                            'mak_nominatif_id' => $detail->nominatif_representatif_id,
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

            $result = Perjadin::where('id', $id)->with('mak.nominatif.detail', 'log', 'log.user', 'detail.catatan', 'detail.hotel', 'detail.transport', 'detail.pesawat', 'detail.taksi_jakarta',  'detail.taksi_tujuan', 'detail.uang_harian', 'detail.representatif', 'detail.ppk', 'detail.bendahara', 'lampiran', 'provinsi', 'detail.nominatif_hotel.detail', 'detail.nominatif_uh.detail', 'detail.nominatif_transport.detail', 'detail.nominatif_pesawat.detail', 'detail.nominatif_taksi_jakarta.detail', 'detail.nominatif_taksi_tujuan.detail', 'detail.nominatif_representatif.detail')->first();


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
                    $no_sppd = $this->assignNoSppd();
                    // Update the detail with the new `no_sppd` and other details
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
                        if (count($detail->catatan) > 0) {
                            foreach ($detail->catatan as $key => $value) {
                                $value->delete();
                            }
                        }
                        if (count($detail->hotel) > 0) {
                            foreach ($detail->hotel as $key => $value) {
                                $value->delete();
                            }
                        }
                        if (count($detail->pesawat) > 0) {
                            foreach ($detail->pesawat as $key => $value) {
                                $value->delete();
                            }
                        }
                        if (count($detail->taksi_tujuan) > 0) {
                            foreach ($detail->taksi_tujuan as $key => $value) {
                                $value->delete();
                            }
                        }
                        if (count($detail->taksi_jakarta) > 0) {
                            foreach ($detail->taksi_jakarta as $key => $value) {
                                $value->delete();
                            }
                        }

                        if (count($detail->transport) > 0) {
                            foreach ($detail->transport as $key => $transport) {
                                $transport->delete();
                            }
                        }
                        if (count($detail->uang_harian) > 0) {
                            foreach ($detail->uang_harian as $key => $value) {
                                $value->delete();
                            }
                        }
                        if (count($detail->representatif) > 0) {
                            foreach ($detail->representatif as $key => $value) {
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

    function setTotalAnggaran()
    {

        // $value = Perjadin::with('mak.nominatif.detail', 'log', 'detail.catatan', 'detail.hotel', 'detail.transport', 'detail.pesawat', 'detail.taksi_jakarta',  'detail.taksi_tujuan', 'detail.uang_harian', 'detail.representatif')->where('id', 1)->first();

        // // $data = Perjadin::with([
        // //     'detail.hotel',
        // //     'detail.transport',
        // //     'detail.pesawat',
        // //     'detail.taksi_jakarta',
        // //     'detail.taksi_tujuan',
        // //     'detail.uang_harian',
        // //     'detail.representatif'
        // // ])->get();

        // // foreach ($data as $perjadin) {
        // //     if ($perjadin->total_realisasi > 0) {
        // //         $perjadin->status = "SELESAI";
        // //     }
        // //     // $perjadin->total_anggaran = $perjadin->calculateTotalAnggaran();
        // //     // $perjadin->total_realisasi = $perjadin->calculateTotalRealisasi();
        // //     $perjadin->save();
        // // }

        // return response()->json(['message' => 'Total anggaran updated successfully']);
    }

    public function assignNoSppd()
    {
        // // Step 1: Get all existing `no_sppd` values in ascending order
        // $existingNos = PerjadinDetail::whereNotNull('no_sppd')->orderBy('no_sppd')->pluck('no_sppd')->toArray();
        // $no_sppd = null;
        // if (empty($existingNos)) {
        //     // If there are no existing numbers, start from 1
        //     $no_sppd = 1;
        // } else {
        //     // Step 2: Define expected range based on the min and max values
        //     $minNo = min($existingNos);
        //     $maxNo = max($existingNos);
        //     $expectedNos = range($minNo, $maxNo);

        //     // Step 3: Find the first missing number in the range
        //     $missingNos = array_diff($expectedNos, $existingNos);
        //     $firstMissingNo = reset($missingNos); // Get the first missing number, if any

        //     if ($firstMissingNo) {
        //         // Step 4: Assign the first missing number if there's a gap
        //         $no_sppd = $firstMissingNo;
        //     } else {
        //         // Step 5: Otherwise, assign the next number in the sequence
        //         $no_sppd  = $maxNo + 1;
        //     }
        // }
        // return $no_sppd;



        // // Step 1: Retrieve and sort existing numbers
        // $existingNos = PerjadinDetail::whereNotNull('no_sppd')->orderBy('no_sppd')->pluck('no_sppd')->toArray();

        // if (empty($existingNos)) {
        //     return response()->json(['message' => 'No records found']);
        // }

        // // Step 2: Define the expected range based on the minimum and maximum values
        // $minNo = min($existingNos);
        // $maxNo = max($existingNos);
        // $expectedNos = range($minNo, $maxNo);

        // // Step 3: Find the first missing number
        // $missingNos = array_diff($expectedNos, $existingNos);
        // $firstMissingNo = reset($missingNos); // Gets the first missing number

        // return response()->json([
        //     'first_missing_number' => $firstMissingNo,
        //     'total_missing' => array_slice($missingNos, 0, 5),
        // ]);

        // Nomor awal yang ingin digunakan

        $startNo = 4500;

        $lastNo = PerjadinDetail::where('no_sppd', '>=', $startNo)->max('no_sppd');
        if ($lastNo == null || $lastNo < $startNo) {
            $no_sppd = $startNo;
        } else {
            $no_sppd = $lastNo + 1;
        }

        return $no_sppd;
    }
}















// $total_hotel = 0;
// $total_uh = 0;
// $total_pesawat = 0;
// $total_rep = 0;
// $total_taksi_jakarta = 0;
// $total_taksi_tujuan = 0;
// $total_transport = 0;

// // // STORE HOTEL
// foreach ($detail->hotel as $key => $hotel) {
//     $hot = PerjadinDetailHotel::create([
//         'perjadin_detail_id' => $details->id,
//         'keterangan' => $hotel->keterangan,
//         'hari' => $hotel->hari,
//         'realisasi_hari' => $hotel->hari,
//         'biaya' => $hotel->biaya,
//         'realisasi_biaya' => 0,
//     ]);
//     $total_hotel += $hotel->biaya * $hotel->hari;
// }

// if (!isset($detail->nominatif_hotel)) {
//     MakNominatifDetail::create([
//         'mak_nominatif_id' => $detail->nominatif_hotel->id,
//         'kegiatan_id' => $result->id,
//         'jumlah' => $total_hotel,
//         'status_realisasi' => 'BELUM'
//     ]);
// }


// // STORE PESAWAT
// foreach ($detail->pesawat as $key => $value) {
//     $pes = PerjadinDetailPesawat::create([
//         'perjadin_detail_id' => $details->id,
//         'keterangan' => $value->keterangan,
//         'biaya' => $value->biaya,
//         'realisasi_biaya' => 0,
//     ]);
//     $total_pesawat += $value->biaya;
// }

// if ($detail->nominatif_pesawat) {
//     MakNominatifDetail::create([
//         'mak_nominatif_id' => $detail->nominatif_pesawat->id,
//         'kegiatan_id' => $result->id,
//         'jumlah' => $total_pesawat,
//         'status_realisasi' => 'BELUM'
//     ]);
// }

// // STORE TAKSI JAKARTA
// foreach ($detail->taksi_jakarta as $key => $value) {
//     $val = PerjadinDetailTaksiJakarta::create([
//         'perjadin_detail_id' => $details->id,
//         'keterangan' => $value->keterangan,
//         'biaya' => $value->biaya,
//         'realisasi_biaya' => 0,
//     ]);
//     $total_taksi_jakarta += $value->biaya;
// }

// if ($detail->nominatif_taksi_jakarta) {
//     MakNominatifDetail::create([
//         'mak_nominatif_id' => $detail->nominatif_taksi_jakarta->id,
//         'kegiatan_id' => $result->id,
//         'jumlah' => $total_taksi_jakarta,
//         'status_realisasi' => 'BELUM'
//     ]);
// }

// // STORE TAKSI TUJUAN
// foreach ($detail->taksi_tujuan as $key => $value) {
//     $val = PerjadinDetailTaksiTujuan::create([
//         'perjadin_detail_id' => $details->id,
//         'keterangan' => $value->keterangan,
//         'biaya' => $value->biaya,
//         'realisasi_biaya' => 0,
//     ]);

//     $total_taksi_tujuan += $value->biaya;
// }

// if ($detail->nominatif_taksi_tujuan) {
//     MakNominatifDetail::create([
//         'mak_nominatif_id' => $detail->nominatif_taksi_tujuan->id,
//         'kegiatan_id' => $result->id,
//         'jumlah' => $total_taksi_tujuan,
//         'status_realisasi' => 'BELUM'
//     ]);
// }

// // STORE TRANSPORT
// foreach ($detail->transport as $key => $transport) {
//     $pes = PerjadinDetailTransport::create([
//         'perjadin_detail_id' => $details->id,
//         'keterangan' => $transport->keterangan,
//         'tipe' => $transport->tipe,
//         'biaya' => $transport->biaya,
//         'realisasi_biaya' => 0,
//     ]);
//     $total_transport += $transport->biaya;
// }


// if ($detail->nominatif_transport) {
//     MakNominatifDetail::create([
//         'mak_nominatif_id' => $detail->nominatif_transport->id,
//         'kegiatan_id' => $result->id,
//         'jumlah' => $total_transport,
//         'status_realisasi' => 'BELUM'
//     ]);
// }


// // STORE UH
// foreach ($detail->uang_harian as $key => $uang_harian) {
//     $dar = PerjadinDetailUh::create([
//         'perjadin_detail_id' => $details->id,
//         'keterangan' => $uang_harian->keterangan,
//         'hari' => $uang_harian->hari,
//         'realisasi_hari' => $uang_harian->hari,
//         'biaya' => $uang_harian->biaya,
//         'realisasi_biaya' => 0,
//     ]);
//     $total_uh += $uang_harian->biaya * $uang_harian->hari;
// }

// if ($detail->nominatif_uh) {
//     MakNominatifDetail::create([
//         'mak_nominatif_id' => $detail->nominatif_uh->id,
//         'kegiatan_id' => $result->id,
//         'jumlah' => $total_uh,
//         'status_realisasi' => 'BELUM'
//     ]);
// }


// // STORE REPRESENTATIF
// foreach ($detail->representatif as $key => $representatif) {
//     $rep = PerjadinDetailRep::create([
//         'perjadin_detail_id' => $details->id,
//         'keterangan' => $representatif->keterangan,
//         'hari' => $representatif->hari,
//         'realisasi_hari' => $representatif->hari,
//         'biaya' => $representatif->biaya,
//         'realisasi_biaya' => 0,
//     ]);

//     $total_rep += $representatif->biaya * $representatif->hari;
// }


// if ($detail->nominatif_representatif) {
//     MakNominatifDetail::create([
//         'mak_nominatif_id' => $detail->nominatif_representatif->id,
//         'kegiatan_id' => $result->id,
//         'jumlah' => $total_rep,
//         'status_realisasi' => 'BELUM'
//     ]);
// }