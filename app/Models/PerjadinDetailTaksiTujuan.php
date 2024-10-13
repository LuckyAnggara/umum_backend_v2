<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjadinDetailTaksiTujuan extends Model
{
    use HasFactory;

    protected $fillable = [
        'perjadin_detail_id',
        'keterangan',
        'biaya',
        'realisasi_biaya',
        'notes',
        'bukti',
    ];

    protected $casts = [
        'bukti' => 'boolean',
    ];
}
