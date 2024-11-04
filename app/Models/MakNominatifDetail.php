<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MakNominatifDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mak_nominatif_id',
        'kegiatan_id',
        'jumlah',
        'status_realisasi',
    ];
}
