<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MakNominatif extends Model
{
    use HasFactory;

    public function detail()
    {
        return $this->hasMany(MakNominatifDetail::class, 'mak_nominatif_id', 'id');
    }
}
