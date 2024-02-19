<?php

namespace App\Http\Controllers;

use App\Models\PtjLampiran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PtjLampiranController extends BaseController
{
    public function download($id)
    {

        $laporan = PtjLampiran::find($id);
        $path = public_path($laporan->link);
        $fileName = $laporan->name;

        return Response::download($path, $fileName);
    }
}
