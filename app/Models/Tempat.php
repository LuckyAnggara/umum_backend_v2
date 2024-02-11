<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tempat extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruangan',
        'tanggal',
        'jam_mulai',
        'jam_akhir',
        'nip',
        'nama',
        'unit',
        'kegiatan',
        'status',
    ];

    protected $appends = ['title','start','end'];

    public function getTitleAttribute()
    {
        return $this->kegiatan;
    }

        public function getStartAttribute()
    {
        return $this->jam_mulai;
    }

        public function getEndAttribute()
    {
        return $this->jam_akhir ;
    }

}
