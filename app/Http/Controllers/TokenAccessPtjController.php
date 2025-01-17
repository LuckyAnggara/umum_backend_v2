<?php

namespace App\Http\Controllers;

use App\Models\Perjadin;
use App\Models\PerjadinDetail;
use App\Models\TokenAccessPtj;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TokenAccessPtjController extends BaseController
{
    public function generateToken(Request $request)
    {

        $id = $request->id;
        $token = rand(100000, 999999); // Generate 6 digit OTP
        $expiresAt = Carbon::parse($request->expire_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i');
        try {
            DB::beginTransaction();

            $result = TokenAccessPtj::where('perjadin_id', $request->id)->first();

            if ($result) {
                $result->token = $token;
                $result->expires_at = $expiresAt;
                $result->valid = true;
                $result->save();
            } else {
                // Mengambil data inventaris dengan paginasi
                $result = TokenAccessPtj::create([
                    'perjadin_id' => $request->id,
                    'token' => $token,
                    'valid' => true,
                    'expires_at' => $expiresAt,
                ]);
            }

            DB::commit();
            return $this->sendResponse($result, 'Data berhasil dibuat');
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function validateToken(Request $request)
    {
        $perjadin_detail_id = $request->id;
        $token = $request->token;

        try {
            $detail = PerjadinDetail::where('id', $perjadin_detail_id)->with('master')->first();
            if ($detail) {
                $code = TokenAccessPtj::where('perjadin_id', $detail->master->id)->where('token', $token)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();

                if (!$code) {
                    return response()->json(['message' => 'Invalid or expired access code'], 401);
                }

                $result = PerjadinDetail::where('id', $perjadin_detail_id)->with('hotel', 'transport', 'uang_harian', 'pesawat', 'taksi_jakarta', 'taksi_tujuan', 'representatif', 'master.mak', 'ppk', 'bendahara', 'lampiran')->first();

                return $this->sendResponse($result, 'Data tersedia');
            } else {
                return response()->json(['message' => 'Data tidak ada'], 204);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function showMaster($id)
    {
        // try {
        //     $exist = TokenAccessPtj::where('perjadin_id', $id)
        //         ->where('expires_at', '>', Carbon::now())
        //         ->first();

        //     if ($exist) {
        //         $result = Perjadin::where('id', $id)->with('detail')->first();
        //     } else {
        //         return response()->json(['message' => 'Link telah expire'], 204);
        //     }
        // } catch (\Exception $e) {
        //     return response()->json(['message' => $e->getMessage()], 500);
        // }

        try {
            // $exist = TokenAccessPtj::where('perjadin_id', $id)
            //     ->where('expires_at', '>', Carbon::now())
            //     ->first();

            $result = Perjadin::where('id', $id)->with('detail')->first();

            if ($result) {
                $result = Perjadin::where('id', $id)->with('detail')->first();
                return $this->sendResponse($result, 'Data tersedia');
            } else {

                return response()->json(['message' => 'Link telah expire'], 204);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
