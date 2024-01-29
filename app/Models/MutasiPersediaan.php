<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiPersediaan extends Model
{
    use HasFactory;


    protected $fillable = [
        'inventory_id',
        'debit',
        'kredit',
        'saldo',
        'keterangan'
    ];

        protected $casts = [
        'created_at' => 'datetime:d F Y',
    ];
}
