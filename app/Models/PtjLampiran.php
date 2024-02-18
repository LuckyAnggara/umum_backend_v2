<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtjLampiran extends Model
{
    use HasFactory;

    protected $fillable = [
        'ptj_id',
        'file_name',
        'lampiran',

    ];



      
}
