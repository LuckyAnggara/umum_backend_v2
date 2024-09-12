<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerjadinDetailCatatan extends Model
{
    use HasFactory;


    protected $fillable = [
        'perjadin_detail_id',
        'catatan',
        'user_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:d F Y',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
