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
        'jumlah_peserta',
        'no_wa',
        'status',
        'user_id'
    ];



    protected $appends = ['title', 'start', 'end', 'tipe    '];

    public function getTitleAttribute()
    {
        return $this->kegiatan;
    }

    public function getTipeAttribute()
    {
        return 'TEMPAT';
    }

    public function getStartAttribute()
    {
        return $this->tanggal . ' ' . $this->jam_mulai;
    }

    public function getEndAttribute()
    {
        return $this->tanggal . ' ' . $this->jam_akhir;
    }
}
