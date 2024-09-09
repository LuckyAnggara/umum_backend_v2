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
        'user_id'
    ];

    protected $appends = ['title', 'start', 'end', 'duration', 'tipe'];

    public function getTitleAttribute()
    {
        return $this->kegiatan;
    }

    public function getTipeAttribute()
    {
        return  'AGENDA';
    }


    public function getStartAttribute()
    {
        return $this->tanggal . ' ' . $this->jam_mulai;
    }

    public function getEndAttribute()
    {
        return $this->tanggal . ' ' . $this->jam_akhir;
    }

    public function getDurationAttribute()
    {
        return "02:00";
    }
    public function lampiran()
    {
        return  $this->hasMany(AgendaLampiran::class, 'agenda_id', 'id');
    }
}
