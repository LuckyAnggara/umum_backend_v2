<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Agenda;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\MutasiPersediaan;
use App\Models\Mutation;
use App\Models\ProductionOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class ReportController extends BaseController
{
    public function reportInventory(Request $request)
    {
        $date = $request->input('date');

        $result = Inventory::get();

        if (!is_null($date)) {
            $date = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
            $result->each(function ($value) use ($date) {
                $value->balance = 0;
                $mutation = MutasiPersediaan::where('inventory_id', $value->id)
                    ->whereDate('created_at', $date)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($mutation) {
                    return $value->balance = $mutation->saldo;
                } else {
                    return $value->balance = $value->saldo;
                }
            });
        }

        return view('reportitem', [
            'data' => $result,
            'date' => Carbon::parse($date)->format('d F Y'),
        ]);

        // $pdf = PDF::loadView('item.report', [
        //     'data' => $result,
        //     'from_date' => Carbon::parse($fromDate)->format('d F Y'),
        //     'to_date' => Carbon::parse($toDate)->format('d F Y'),
        //     'warehouse' => $warehouse,
        //     'warehouseShow' => $warehouseShow,
        // ]);

        // return $pdf->download('laporan persediaan.pdf');
    }

    public function reportAgenda(Request $request)
    {
        $fromDate = $request->input('start');
        $toDate = $request->input('end');

        if ($fromDate && $toDate) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDate)->startOfDay();
            $toDate = Carbon::createFromFormat('Y-m-d', $toDate)->endOfDay();
        } else {
            $fromDate = Carbon::now()->startOfMonth();
            $toDate = Carbon::now();
        }
        $agenda = Agenda::whereBetween('tanggal', [$fromDate, $toDate])->get();
        $dateArray = [];
        $currentDate = $fromDate->copy();
        while ($currentDate->lte($toDate)) {
            $dateArray[] = $currentDate->toDateString();
            $currentDate->addDay();
        }

        $transformed = collect($dateArray)->map(function ($date) use ($agenda) {
            $details = $agenda
                ->filter(function ($item) use ($date) {
                    return $item->tanggal == $date;
                })
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'kegiatan' => $item->kegiatan,
                        'jam_mulai' => $item->jam_mulai,
                        'jam_akhir' => $item->jam_akhir,
                        'pimpinan' => $item->pimpinan,
                        'tempat' => $item->tempat,
                        'status' => $item->status,
                        'title' => $item->title,
                        'start' => $item->start,
                        'end' => $item->end,
                        'duration' => $item->duration,
                        'tipe' => $item->tipe,
                    ];
                })
                ->values()
                ->toArray(); // Ensures `details` is an array

            return [
                'tanggal' => Carbon::parse($date)->format('d F Y'),
                'detail' => $details,
            ];
        });

        $transformed = $transformed->map(function ($item) {
            // Group kegiatan berdasarkan pimpinan
            $groupedDetails = collect($item['detail'])
                ->groupBy('pimpinan')
                ->map(function ($activities, $pimpinan) {
                    return [
                        'pimpinan' => $pimpinan,
                        'kegiatan' => $activities
                            ->map(function ($activity) {
                                return [
                                    'id' => $activity['id'],
                                    'kegiatan' => $activity['kegiatan'],
                                    'pimpinan' => $activity['pimpinan'],
                                    'jam_mulai' => $activity['jam_mulai'],
                                    'jam_akhir' => $activity['jam_akhir'],
                                    'tempat' => $activity['tempat'],
                                    'status' => $activity['status'],
                                    'title' => $activity['title'],
                                    'start' => $activity['start'],
                                    'end' => $activity['end'],
                                    'duration' => $activity['duration'],
                                    'tipe' => $activity['tipe'],
                                ];
                            })
                            ->toArray(),
                    ];
                });

            // Pastikan pimpinan 1 sampai 8 selalu ada, meskipun tidak ada kegiatan
            for ($i = 1; $i <= 8; $i++) {
                if (!$groupedDetails->has($i)) {
                    $groupedDetails->put($i, [
                        'pimpinan' => $i,
                        'kegiatan' => [],
                    ]);
                }
            }

    // Mengurutkan hasil berdasarkan pimpinan dari 1 hingga 8
    $sortedDetails = $groupedDetails->sortBy('pimpinan')->values()->toArray();

    $item['detail'] = $sortedDetails;

    return $item;
        });

        // return collect($transformed)->toJson();
        return view('reportagenda', [
            'data' => collect($transformed),
            'fromDate' => Carbon::parse($fromDate)->format('d F Y'),
            'toDate' => Carbon::parse($toDate)->format('d F Y'),
        ]);
    }

      public function reportTextAgenda(Request $request)
    {
        $fromDate = $request->input('start');
        $toDate = $request->input('end');

        if ($fromDate && $toDate) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDate)->startOfDay();
            $toDate = Carbon::createFromFormat('Y-m-d', $toDate)->endOfDay();
        } else {
            $fromDate = Carbon::now()->startOfMonth();
            $toDate = Carbon::now();
        }
        $agenda = Agenda::whereBetween('tanggal', [$fromDate, $toDate])->get();
        $dateArray = [];
        $currentDate = $fromDate->copy();
        while ($currentDate->lte($toDate)) {
            $dateArray[] = $currentDate->toDateString();
            $currentDate->addDay();
        }

        $transformed = collect($dateArray)->map(function ($date) use ($agenda) {
            $details = $agenda
                ->filter(function ($item) use ($date) {
                    return $item->tanggal == $date;
                })
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'kegiatan' => $item->kegiatan,
                        'jam_mulai' => $item->jam_mulai,
                        'jam_akhir' => $item->jam_akhir,
                        'pimpinan' => $item->pimpinan,
                        'tempat' => $item->tempat,
                        'status' => $item->status,
                        'title' => $item->title,
                        'start' => $item->start,
                        'end' => $item->end,
                        'duration' => $item->duration,
                        'tipe' => $item->tipe,
                    ];
                })
                ->values()
                ->toArray(); // Ensures `details` is an array

            return [
                'tanggal' => Carbon::parse($date)->format('d F Y'),
                'detail' => $details,
            ];
        });

        $transformed = $transformed->map(function ($item) {
            // Group kegiatan berdasarkan pimpinan
            $groupedDetails = collect($item['detail'])
                ->groupBy('pimpinan')
                ->map(function ($activities, $pimpinan) {
                    return [
                        'pimpinan' => $pimpinan,
                        'kegiatan' => $activities
                            ->map(function ($activity) {
                                return [
                                    'id' => $activity['id'],
                                    'kegiatan' => $activity['kegiatan'],
                                    'pimpinan' => $activity['pimpinan'],
                                    'jam_mulai' => $activity['jam_mulai'],
                                    'jam_akhir' => $activity['jam_akhir'],
                                    'tempat' => $activity['tempat'],
                                    'status' => $activity['status'],
                                    'title' => $activity['title'],
                                    'start' => $activity['start'],
                                    'end' => $activity['end'],
                                    'duration' => $activity['duration'],
                                    'tipe' => $activity['tipe'],
                                ];
                            })
                            ->toArray(),
                    ];
                });

            // Pastikan pimpinan 1 sampai 8 selalu ada, meskipun tidak ada kegiatan
            for ($i = 1; $i <= 8; $i++) {
                if (!$groupedDetails->has($i)) {
                    $groupedDetails->put($i, [
                        'pimpinan' => $i,
                        'kegiatan' => [],
                    ]);
                }
            }

    // Mengurutkan hasil berdasarkan pimpinan dari 1 hingga 8
    $sortedDetails = $groupedDetails->sortBy('pimpinan')->values()->toArray();

    $item['detail'] = $sortedDetails;

    return $item;
        });
        return $transformed->toJson();

    }
}
