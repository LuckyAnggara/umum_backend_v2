<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bmn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nup',
        'nama',
        'keterangan',
        'penanggung_jawab',
        'ruangan',
        'sewa',
        'tahun_perolehan',
        'image',
    ];

    protected $casts = [
        // 'sewa' => 'boolean',
    ];
}
