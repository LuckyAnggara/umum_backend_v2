<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArsipLampiran extends Model
{
    use HasFactory;

    protected $fillable = [
        'arsip_id',
        'file_name',
        'lampiran',
    ];
}
