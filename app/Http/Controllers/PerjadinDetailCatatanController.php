<?php

namespace App\Http\Controllers;

use App\Models\PerjadinDetailCatatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerjadinDetailCatatanController extends Controller
{
    public function store(Request $request)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {
            $result = PerjadinDetailCatatan::create([
                'catatan' => $data->catatan,
                'perjadin_detail_id' => $data->perjadin_detail_id,
                'user_id' => Auth::id(),
            ]);
            DB::commit();
            $result = PerjadinDetailCatatan::with('user')->where('id', $result->id)->first();
            return response()->json(['data' => $result], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        DB::beginTransaction();
        try {
            $result = PerjadinDetailCatatan::with('user')->where('perjadin_detail_id', $id)->get();
            return response()->json(['data' => $result], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
