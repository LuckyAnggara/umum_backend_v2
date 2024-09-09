<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjadinLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'perjadin_id',
        'status',
        'catatan',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime:d F Y',
    ];

}
