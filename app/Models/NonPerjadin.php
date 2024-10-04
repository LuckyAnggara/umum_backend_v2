<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonPerjadin extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun_anggaran',
        'nomor_transaksi',
        'tanggal_transaksi',
        'uraian',
        'mak_id',
        'total_anggaran',
        'total_realisasi',
        'tanggal_verifikasi',
        'tanggal_verifikasi_ptj',
        'status',
        'user_id',
        'unit_id',
        'ppk',
        'bendahara',
        'penerima',
        'nip_penerima',
    ];

    protected $casts = [
        'created_at' => 'datetime:d F Y',
        'tanggal_verifikasi' => 'datetime:d F Y',
        'tanggal_verifikasi_ptj' => 'datetime:d F Y',
        'tanggal_transaksi' => 'datetime:d F Y',
        'ptj' => 'boolean',
    ];
}
