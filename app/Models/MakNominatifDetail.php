<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MakNominatifDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'mak_nominatif_id',
        'kegiatan_id',
        'jumlah',
        'status_realisasi',
    ];
}
