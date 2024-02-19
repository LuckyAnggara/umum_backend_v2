<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ptj extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama_kegiatan',
        'realisasi',
        'tanggal',
        'nama',
        'unit',
        'nip',
        'no_wa',
    ];

    protected $casts = [
        'created_at' => 'datetime:d F Y',
        'tanggal' => 'datetime:d F Y',
    ];

    public function lampiran()
    {
        return  $this->hasMany(PtjLampiran::class, 'ptj_id', 'id');
    }
}
