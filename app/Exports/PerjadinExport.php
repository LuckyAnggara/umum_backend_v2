<?php

namespace App\Exports;

use App\Models\PerjadinDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PerjadinExport implements FromView
{

    protected $start;
    protected $end;

    // Constructor untuk menerima parameter
    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }


    public function view(): View
    {

        return view('perjadinreport', [
            'data' => PerjadinDetail::with('master.mak')->whereBetween('tanggal_sppd', [$this->start, $this->end])->orderBy('no_sppd')->get()
        ]);
    }
}
