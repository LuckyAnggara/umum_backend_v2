<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonPerjadin extends Model
{
    use HasFactory,  HasUlids;

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

    public function lampiran()
    {
        return  $this->hasMany(PerjadinLampiran::class, 'perjadin_id', 'id');
    }
    public function log()
    {
        return  $this->hasMany(PerjadinLog::class, 'perjadin_id', 'id');
    }

    public function mak()
    {
        return $this->hasOne(Mak::class, 'id', 'mak_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }
    public function provinsi()
    {
        return $this->hasOne(Provinsi::class, 'id', 'provinsi_id');
    }
    public function bendahara()
    {
        return $this->hasOne(Bendahara::class, 'id', 'bendahara');
    }
    public function ppk()
    {
        return $this->hasOne(Ppk::class, 'id', 'ppk');
    }
}
