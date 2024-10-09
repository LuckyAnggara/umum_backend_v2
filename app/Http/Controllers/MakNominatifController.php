<?php

namespace App\Http\Controllers;

use App\Models\MakNominatif;
use Illuminate\Http\Request;

class MakNominatifController extends BaseController
{
    public function index(Request $request)
    {
        $mak_id = $request->input('mak_id');
        try {
            $result = MakNominatif::where('mak_id', $mak_id)->with('detail')->get();
            return $this->sendResponse($result, 'Ada');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 'Error');
        }
    }
}
