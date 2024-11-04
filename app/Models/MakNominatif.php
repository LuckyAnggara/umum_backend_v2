<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MakNominatif extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mak_id',
        'uraian',
        'type',
        'jumlah',
        'volume',
        'harga',
        'satuan',
    ];
    public function detail()
    {
        return $this->hasMany(MakNominatifDetail::class, 'mak_nominatif_id', 'id');
    }
}
