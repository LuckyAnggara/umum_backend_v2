<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenAccessPtj extends Model
{
    use HasFactory;


    protected $fillable = ['perjadin_id', 'token', 'valid', 'expires_at'];
}
