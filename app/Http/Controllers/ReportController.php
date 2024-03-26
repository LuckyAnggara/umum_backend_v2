<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
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
                $mutation = MutasiPersediaan::where('inventory_id', $value->id)->whereDate('created_at', $date)->orderBy('id', 'desc')->first();
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
}
