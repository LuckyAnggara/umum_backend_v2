<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BmnController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MutasiPersediaanController;
use App\Http\Controllers\PeminjamanBmnController;
use App\Http\Controllers\PermintaanLayananBmnController;
use App\Http\Controllers\PermintaanPersediaanController;
use App\Http\Controllers\PesanController;
use App\Http\Controllers\PtjController;
use App\Http\Controllers\PtjLampiranController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TempatController;
use App\Models\MutasiPersediaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/show-image/{id}', [InventoryController::class, 'showImage']);

Route::get('/inventory/get', [InventoryController::class, 'index']);
Route::resource('permintaan-persediaan', PermintaanPersediaanController::class)->only([
    'store', 'show',
]);
Route::resource('ptj', PtjController::class)->only([
    'store', 'show',
]);

Route::get('/permintaan-persediaan/get-status/{tiket}', [PermintaanPersediaanController::class, 'getStatus']);
Route::resource('tempat', TempatController::class);
Route::resource('agenda', AgendaController::class);

Route::resource('permintaan-layanan-bmn', PermintaanLayananBmnController::class)->only([
    'store', 'show',
]);
Route::resource('rate-layanan', RateController::class)->only([
    'store',
]);

Route::resource('peminjaman-bmn', PeminjamanBmnController::class)->only([
    'store', 'show',
]);
Route::get('/peminjaman-bmn/get-status/{tiket}', [PeminjamanBmnController::class, 'getStatus']);


Route::get('/permintaan-layanan-bmn/get-status/{tiket}', [PermintaanLayananBmnController::class, 'getStatus']);
Route::get('/bmn/show-nup/{nup}', [BmnController::class, 'showNup']);

Route::put('/permintaan-persediaan/done/{id}', [PermintaanPersediaanController::class, 'updateDone']);
Route::put('/permintaan-layanan-bmn/done-bawa/{id}', [PermintaanLayananBmnController::class, 'updateDoneBawa']);
Route::put('/permintaan-layanan-bmn/done-balik/{id}', [PermintaanLayananBmnController::class, 'updateDoneBalik']);
Route::put('/peminjaman-bmn/done/{id}', [PeminjamanBmnController::class, 'updateDone']);

Route::get('/bmn/cek-nup', [BmnController::class, 'cekNup']);
Route::get('/users/cek-username', [AuthController::class, 'cekValidUser']);

Route::get(
    'ptj-lampiran/{id}',
    [PtjLampiranController::class, 'download']
);



Route::resource('arsip', ArsipController::class);

Route::resource('bmn', BmnController::class);
Route::middleware('auth:sanctum')->group(function () {
    Route::resource('dashboard', DashboardController::class);
    Route::resource('auth/user', AuthController::class)->only([
        'update',
    ]);
    Route::resource('/users', AuthController::class);
    Route::get('/auth/user', [AuthController::class, 'user'])->name('user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/report/inventory', [ReportController::class, 'reportInventory']);
    Route::get('/kirim-pesan', [PesanController::class, 'kirim']);

    Route::resource('permintaan-persediaan', PermintaanPersediaanController::class)->only([
        'index', 'update'
    ]);

    Route::resource('ptj', PtjController::class)->only([
        'index', 'update', 'destroy'
    ]);

    Route::resource('permintaan-layanan-bmn', PermintaanLayananBmnController::class)->only([
        'index', 'update'
    ]);

    Route::resource('peminjaman-bmn', PeminjamanBmnController::class)->only([
        'index', 'update', 'destroy'
    ]);

    Route::resource('bmn', BmnController::class)->only([
        'store', 'update'
    ]);


    Route::put('/permintaan-persediaan/undo/{id}', [PermintaanPersediaanController::class, 'updateUndo']);
    Route::resource('inventory', InventoryController::class);

    Route::resource('/persediaan/mutasi', MutasiPersediaanController::class);

    Route::get('/persediaan/cek-nama', [InventoryController::class, 'cekNama']);
    Route::post(
        'persediaan/upload-image',
        [InventoryController::class, 'imageUpload']
    );
});
