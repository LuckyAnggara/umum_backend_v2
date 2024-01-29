<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\MutasiPersediaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MutasiPersediaanController extends Controller
{
    static function createMutasi($inventory_id, $tipe, $jumlah, $keterangan)
    {
        // Cari saldo terakhir dari MutasiPersediaan berdasarkan inventory_id
        $lastMutasi = MutasiPersediaan::where('inventory_id', $inventory_id)
            ->orderBy('created_at', 'desc')
            ->first();

        $produk = Inventory::find($inventory_id);

        // Hitung saldo baru
        if ($lastMutasi) {
            $saldo = $lastMutasi->saldo + ($tipe == 'DEBIT' ? $jumlah : - ($jumlah));
            $produk->saldo = $saldo;
        } else {
            // Jika ini merupakan mutasi pertama untuk inventory_id tertentu
            $saldo = $tipe == 'DEBIT' ? $jumlah : 0;
            $produk->saldo = $saldo;
        }
        
         $produk->save();

        // Buat mutasi baru
        $mutasi = MutasiPersediaan::create([
            'inventory_id' => $inventory_id,
            'debit' => $tipe == 'DEBIT' ? $jumlah : 0,
            'kredit' => $tipe == 'KREDIT' ? $jumlah : 0,
            'saldo' => $saldo,
            'keterangan' => $keterangan,
        ]);

     

        return $mutasi;
    }

    public function index(Request $request)
    {
        $id = $request->input('id');
        $perPage = $request->input('limit', 5);
        $name = $request->input('query');

        try {
            // Mengambil data inventaris dengan paginasi
            $result = MutasiPersediaan::where('inventory_id', $id)->when($name, function ($query, $name) {
                return $query->where('keterangan', 'like', '%' . $name . '%')->where('debit', 'like', '%' . $name . '%')->where('kredit', 'like', '%' . $name . '%')->where('saldo', 'like', '%' . $name . '%');
            })
                ->orderBy('created_at', 'desc')
                ->latest()
                ->paginate($perPage);

            return response()->json(['data' => $result], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $data = json_decode($request->getContent());
        DB::beginTransaction();
        try {

        $produk = Inventory::find($data->id);
             $lastMutasi = MutasiPersediaan::where('inventory_id', $data->id)
            ->orderBy('created_at', 'desc')
            ->first();

              // Hitung saldo baru
        if ($lastMutasi) {
            $saldo = $lastMutasi->saldo +  ($data->tipe == 'DEBIT' ? $data->jumlah : - ($data->jumlah));
            $produk->saldo = $saldo;
        } else {
            // Jika ini merupakan mutasi pertama untuk inventory_id tertentu
            $saldo = $data->tipe == 'DEBIT' ? $data->jumlah : 0;
            $produk->saldo = $saldo;
        }

         $produk->save();

            // Buat mutasi baru
            $mutasi = MutasiPersediaan::create([
                'inventory_id' => $data->id,
                'debit' => $data->tipe == 'DEBIT' ? $data->jumlah : 0,
                'kredit' => $data->tipe == 'KREDIT' ? $data->jumlah : 0,
                'saldo' => $saldo,
                'keterangan' => $data->catatan,
            ]);
  

            DB::commit();
            return response()->json(['data' => $saldo], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }



      
    }
}
