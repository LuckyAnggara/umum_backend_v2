<?php

namespace App\Http\Controllers;

use App\Models\NonPerjadinLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NonPerjadinLogController extends Controller
{
    static function createLog($id, $status, $catatan)
    {
        // Membuat objek LogPermintaanPersediaan
        $log = NonPerjadinLog::create([
            'non_perjadin_id' => $id,
            'status' => $status,
            'catatan' => $catatan,
            'user_id' => Auth::id(),
        ]);

        // Jika perlu, Anda dapat mengembalikan objek yang telah dibuat
        return $log;
    }
}
