<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Perjadin extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'tahun_anggaran',
        'no_st',
        'tanggal_st',
        'tanggal_awal',
        'tanggal_akhir',
        'nama_kegiatan',
        'tempat_kegiatan',
        'mak_id',
        'total_anggaran',
        'total_realisasi',
        'status',
        'ptj',
        'tanggal_verifikasi',
        'user_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:d F Y',
        'tanggal_st' => 'datetime:d F Y',
        'tanggal_awal' => 'datetime:d F Y',
        'tanggal_akhir' => 'datetime:d F Y',
        'ptj' => 'boolean',
    ];

    // protected $keyType = 'string';
    // public $incrementing = false;

    // public static function booted()
    // {
    //     static::creating(function ($model) {
    //         $model->id = Str::ulid();
    //     });
    // }

    public function detail()
    {
        return  $this->hasMany(PerjadinDetail::class, 'perjadin_id', 'id');
    }

    public function lampiran()
    {
        return  $this->hasMany(PerjadinLampiran::class, 'perjadin_id', 'id');
    }

    public function mak()
    {
        return $this->hasOne(Mak::class, 'id', 'mak_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
