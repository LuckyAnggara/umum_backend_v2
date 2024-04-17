<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

        $user = User::where('nip', $request->nip)->first();

        $credentials = request(['nip', 'password']);
        if (!Auth::attempt($credentials)) {


            return response([
                'success'   => false,
                'message' => ['These credentials do not match our records.']
            ], 404);
        }

        $user->last_login = Carbon::now();
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
}
