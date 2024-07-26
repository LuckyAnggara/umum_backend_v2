<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'kegiatan',
        'tanggal',
        'jam_mulai',
        'jam_akhir',
        'pimpinan',
        'tempat',
        'status',
    ];

    protected $appends = ['title', 'start', 'end'];

    public function getTitleAttribute()
    {
        return $this->kegiatan;
    }

    public function getStartAttribute()
    {
        return $this->tanggal . ' ' . $this->jam_mulai;
    }

    public function getEndAttribute()
    {
        return $this->tanggal . ' ' . $this->jam_akhir;
    }
    public function lampiran()
    {
        return  $this->hasMany(AgendaLampiran::class, 'agenda_id', 'id');
    }
}
