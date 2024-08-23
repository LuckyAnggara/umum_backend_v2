<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\PeminjamanBmn;
use App\Models\PermintaanLayananBmn;
use App\Models\PermintaanPersediaan;
use App\Models\Tempat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nip' => 'required',
            'password' => 'required',
        ]);

        $isAdmin = $request->input('isAdmin', false);

        $user = User::where('nip', $request->nip)->first();

        if ($isAdmin) {
            if ($user->role !== 'ADMIN') {
                return response([
                    'success'   => false,
                    'message' => ['User not admin.']
                ], 404);
            }
        } else {
            if ($user->role !== 'USER') {
                return response([
                    'success'   => false,
                    'message' => ['Not credentials user.']
                ], 404);
            }
        }

        $credentials = request(['nip', 'password']);
        if (!Auth::attempt($credentials)) {
            return response([
                'success'   => false,
                'message' => ['These credentials do not match our records.']
            ], 404);
        }

        $user->last_login = Carbon::now();
        $user->last_ip_login = $request->ip();
        $user->save();

        $token = $user->createToken('Api-token')->plainTextToken;

        $response = [
            'success'   => true,
            'user'      => $user,
            'token'     => $token,
            'message'   => 'Berhasil Login'
        ];
        return response($response, 201);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'nip' => 'required',
            'password' => 'required',
            'confirm-password' => 'required|same:password'
        ]);

        $data = $request->except('confirm-password', 'password');
        $data['password'] = Hash::make($request->password);

        $user = User::create($data);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = [
            'success'   => true,
            'user'      => $user,
            'token'     => $token,
            'message'   => 'Berhasil Register'
        ];
        return response($response, 201);
    }

    public function logout(Request $request)
    {
        Auth::user()->tokens()->delete();
        return [
            'success'   => true,
            'message' => 'user logged out'
        ];
    }

    public function user(Request $request)
    {
        return Auth::user();
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail(Auth::id());
            $user->update([
                'name' => $request->name,
                'unit' =>  $request->unit,
                'role' =>  $request->role,
            ]);
            // Commit transaksi jika berhasil
            DB::commit();
            // Berikan respons sukses
            return response()->json(['message' => 'Data berhasil diperbarui'], 200);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollback();
            // Berikan respons error
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $perPage = $request->input('limit', 5);
        $name = $request->input('query');
        try {
            // Mengambil data inventaris dengan paginasi
            $user = User::when($name, function ($query, $name) {
                return $query->where('nip', 'like', '%' . $name . '%')
                    ->orWhere('name', 'like', '%' . $name . '%');
            })
                ->orderBy('created_at', 'desc')
                ->latest()
                ->paginate($perPage);

            return response()->json(['data' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function cekValidUser(Request $request)
    {
        $name = $request->input('query');
        $user = User::where('nip', $name)->first();
        if ($user) {
            return true;
        }
        return "false";
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'nip' => 'required',
            'unit' => 'required',
            'role' => 'required',
            'password' => 'required',
        ]);

        $password = Hash::make($request->password);

        $user = User::create([
            'nip' => $request->nip,
            'name' =>  $request->name,
            'unit' =>  $request->unit,
            'role' =>  $request->role,
            'password' => $password,

        ]);
        $response = [
            'success'   => true,
            'message'   => 'User berhasil dibuat'
        ];
        return response($response, 200);
    }

    public function destroy($id)
    {
        try {
            // Cari dan hapus data bmn berdasarkan ID
            $result =  User::where('id', $id)->first();
            if ($result) {
                $result->delete();
            }
            // Berikan respons sukses
            return response()->json(['message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            // Berikan respons error jika data tidak ditemukan
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }

    public function layanan()
    {
        try {
            // Mengambil data inventaris dengan paginasi
            $permintaanPersediaan = PermintaanPersediaan::where('user_id', Auth::id())->limit(10)->get();
            $peminjamanBmn = PeminjamanBmn::where('user_id', Auth::id())->limit(10)->get();
            $layananBmn  = PermintaanLayananBmn::where('user_id', Auth::id())->limit(10)->get();
            $tempat = Tempat::where('user_id', Auth::id())->limit(10)->get();

            $data = [
                'persediaan' => $permintaanPersediaan,
                'peminjamanBmn' => $peminjamanBmn,
                'layananBmn' => $layananBmn,
                'tempat' => $tempat
            ];
            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
