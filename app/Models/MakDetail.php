<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MakDetail extends Model
{
    use HasFactory, SoftDeletes;


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
