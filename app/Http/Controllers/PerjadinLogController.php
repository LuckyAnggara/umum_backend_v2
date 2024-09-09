<?php

namespace App\Http\Controllers;

use App\Models\PerjadinLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerjadinLogController extends Controller
{
       static function createLogPerjadin($perjadin_id, $status, $catatan)
    {
        // Membuat objek LogPermintaanPersediaan
        $log = PerjadinLog::create([
            'perjadin_id' => $perjadin_id,
            'status' => $status,
            'catatan' => $catatan,
            'user_id' => Auth::id(),
        ]);

        // Jika perlu, Anda dapat mengembalikan objek yang telah dibuat
        return $log;
    }
}
