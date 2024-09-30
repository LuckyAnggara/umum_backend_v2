<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MakDetail extends Model
{
    use HasFactory;


    protected $fillable = [
        'mak_id',
        'type',
        'kegiatan_id',
        'nama_kegiatan',
        'total_anggaran',
        'total_realisasi',
        'status_realisasi',
    ];
}
