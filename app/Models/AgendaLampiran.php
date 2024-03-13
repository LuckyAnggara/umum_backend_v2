<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaLampiran extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'agenda_id',
        'file_name',
        'lampiran',

    ];
}
