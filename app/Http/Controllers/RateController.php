<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RateController extends BaseController
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $result = Rate::create([
                'value' => $request->value,
                'saran' => $request->saran,
            ]);
            DB::commit();
            return $this->sendResponse($result, 'Data berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), 'Error');
        }
    }
}
