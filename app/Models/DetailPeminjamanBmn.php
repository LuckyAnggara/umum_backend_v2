<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPeminjamanBmn extends Model
{
    use HasFactory;

    protected $fillable = [
        'peminjaman_bmn_id',
        'bmn_id',
        'checked',
        'checked',
    ];

    protected $casts = [
        'created_at' => 'datetime:d F Y',
        'checked' => 'boolean',
    ];

    public function persediaan()
    {
        return $this->hasOne(Bmn::class, 'id', 'bmn_id');
    }
}
