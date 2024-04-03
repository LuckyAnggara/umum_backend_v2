<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arsip extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_surat',
        'tanggal_surat',
        'klasifikasi',
        'pencipta_arsip',
        'pengolah_arsip',
        'tingkat_perkembangan',
        'jumlah',
        'uraian',
        'lokasi',
        'lemari',
        'rak',
        'no_box',
        'no_folder',
        'jenis_media',
    ];

    protected $casts = [
        'tanggal_surat' => 'datetime:d F Y',
        'created_at' => 'datetime:d F Y',
    ];


    public function lampiran()
    {
        return  $this->hasMany(ArsipLampiran::class, 'arsip_id', 'id');
    }
}
