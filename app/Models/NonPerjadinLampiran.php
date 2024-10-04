<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonPerjadinLampiran extends Model
{
    use HasFactory;

    protected $fillable = [
        'perjadin_id',
        'file_name',
        'lampiran',
    ];
}
