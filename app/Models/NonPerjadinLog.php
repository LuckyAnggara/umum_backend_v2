<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonPerjadinLog extends Model
{
    use HasFactory;


    protected $fillable = [
        'non_perjadin_id',
        'status',
        'catatan',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime:d F Y',
    ];
}
