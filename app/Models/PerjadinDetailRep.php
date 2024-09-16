<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjadinDetailRep extends Model
{
    use HasFactory;
    protected $fillable = [
        'perjadin_detail_id',
        'keterangan',
        'hari',
        'realisasi_hari',

        'biaya',
        'realisasi_biaya',
        'notes',
        'bukti',
    ];

    protected $casts = [
        'bukti' => 'boolean',
    ];
}
