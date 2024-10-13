<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mak extends Model
{
    use HasFactory;


    protected $fillable = [
        'tahun_anggaran',
        'unit_id',
        'kode_mak',
        'keterangan',
        'anggaran',
    ];

    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }

    public function detail()
    {
        return $this->hasMany(MakDetail::class, 'mak_id', 'id');
    }
    public function nominatif()
    {
        return $this->hasMany(MakNominatif::class, 'mak_id', 'id');
    }
}
