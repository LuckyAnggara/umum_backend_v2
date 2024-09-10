<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class PerjadinDetail extends Model
{
    use HasFactory, HasUlids;
    // protected $keyType = 'string';
    // public $incrementing = false;

    protected $fillable = ['perjadin_id', 'tanggal_sppd', 'no_sppd', 'nip', 'nama', 'jabatan', 'pangkat', 'unit', 'peran', 'tanggal_awal', 'tanggal_akhir', 'jumlah_hari', 'status', 'ppk', 'bendahara', 'tanggal_kuitansi'];


    // public static function booted()
    // {
    //     static::creating(function ($model) {
    //         $model->id = Str::ulid();
    //     });
    // }

    public function hotel()
    {
        return $this->hasMany(PerjadinDetailHotel::class, 'perjadin_detail_id', 'id');
    }

    public function uang_harian()
    {
        return $this->hasMany(PerjadinDetailUh::class, 'perjadin_detail_id', 'id');
    }

    public function master()
    {
        return $this->hasOne(Perjadin::class, 'id', 'perjadin_id');
    }

    public function bendahara()
    {
        return $this->hasOne(Bendahara::class, 'id', 'bendahara');
    }

    public function ppk()
    {
        return $this->hasOne(Ppk::class, 'id', 'ppk');
    }

    // public function darat()
    // {
    //     return  $this->hasMany(PerjadinDetailDarat::class, 'perjadin_detail_id', 'id');
    // }

    // public function pesawat()
    // {
    //     return  $this->hasMany(PerjadinDetailPesawat::class, 'perjadin_detail_id', 'id');
    // }
    public function transport()
    {
        return $this->hasMany(PerjadinDetailTransport::class, 'perjadin_detail_id', 'id');
    }
    public function representatif()
    {
        return $this->hasMany(PerjadinDetailRep::class, 'perjadin_detail_id', 'id');
    }
    public function lampiran()
    {
        return  $this->hasMany(PerjadinDetailLampiran::class, 'perjadin_detail_id', 'id');
    }
}
