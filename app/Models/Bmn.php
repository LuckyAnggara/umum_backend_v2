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
        'mobil_dinas',
        'pinjam_id',
        'status',
    ];

    protected $casts = [
        'mobil_dinas' => 'boolean',
        'status' => 'boolean',
    ];

    public function pinjam()
    {
        return  $this->hasOne(PeminjamanBmn::class, 'id', 'pinjam_id');
    }
}
