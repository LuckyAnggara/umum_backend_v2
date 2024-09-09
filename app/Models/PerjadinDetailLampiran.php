<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjadinDetailLampiran extends Model
{
    use HasFactory;

    protected $fillable = [
        'perjadin_detail_id',
        'type',
        'file_name',
        'lampiran',

    ];
}
