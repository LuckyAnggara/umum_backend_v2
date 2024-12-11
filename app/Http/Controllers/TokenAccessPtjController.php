<?php

namespace App\Http\Controllers;

use App\Models\PerjadinDetail;
use App\Models\TokenAccessPtj;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TokenAccessPtjController extends Controller
{
    public function generateToken(Request $request)
    {

        $id = $request->id;
        $token = rand(100000, 999999); // Generate 6 digit OTP
        $expiresAt = Carbon::now()->addMinutes(10); // Berlaku 10 menit

        // Simpan di database
        TokenAccessPtj::create([
            'perjadin_id' => $request->id,
            'token' => $token,
            'valid' => true,
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'token' => $token,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);
    }

    public function validateToken(Request $request)
    {
        $perjadin_id = $request->perjadin_id;
        $perjadin_detail_id = $request->perjadin_detail_id;
        $token = $request->token;
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $code = TokenAccessPtj::where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$code) {
            return response()->json(['message' => 'Invalid or expired access code'], 401);
        }

        $result = PerjadinDetail::where('id', $perjadin_detail_id)->with('hotel', 'transport', 'uang_harian', 'pesawat', 'taksi_jakarta', 'taksi_tujuan', 'representatif', 'master.mak', 'ppk', 'bendahara', 'lampiran')->first();

        return response()->json(

            [
                'message' => 'Access granted',
                'result' => $result,
            ]
        );
    }
}
