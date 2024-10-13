<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class PerjadinDetail extends Model
{
    use HasFactory, HasUlids;


    protected $fillable = ['perjadin_id', 'tanggal_sppd', 'no_sppd', 'nip', 'nama', 'jabatan', 'pangkat', 'unit', 'peran', 'tanggal_awal', 'tanggal_akhir', 'jumlah_hari', 'status', 'ppk', 'bendahara', 'tanggal_kuitansi', 'nominatif_hotel_id', 'nominatif_uh_id', 'nominatif_pesawat_id', 'nominatif_transport_id', 'nominatif_taksi_jakarta_id', 'nominatif_taksi_tujuan_id', 'nominatif_representatif_id'];

    protected $casts = [
        'created_at' => 'datetime:d F Y',
        'tanggal_awal' => 'datetime:d F Y',
        'tanggal_akhir' => 'datetime:d F Y',
    ];
    public function hotel()
    {
        return $this->hasMany(PerjadinDetailHotel::class, 'perjadin_detail_id', 'id');
    }

    public function uang_harian()
    {
        return $this->hasMany(PerjadinDetailUh::class, 'perjadin_detail_id', 'id');
    }

    public function master()
    {
        return $this->hasOne(Perjadin::class, 'id', 'perjadin_id');
    }

    public function bendahara()
    {
        return $this->hasOne(Bendahara::class, 'id', 'bendahara');
    }

    public function ppk()
    {
        return $this->hasOne(Ppk::class, 'id', 'ppk');
    }
    public function pesawat()
    {
        return $this->hasMany(PerjadinDetailPesawat::class, 'perjadin_detail_id', 'id');
    }
    public function taksi_jakarta()
    {
        return $this->hasMany(PerjadinDetailTaksiJakarta::class, 'perjadin_detail_id', 'id');
    }
    public function taksi_tujuan()
    {
        return $this->hasMany(PerjadinDetailTaksiTujuan::class, 'perjadin_detail_id', 'id');
    }
    public function transport()
    {
        return $this->hasMany(PerjadinDetailTransport::class, 'perjadin_detail_id', 'id');
    }
    public function representatif()
    {
        return $this->hasMany(PerjadinDetailRep::class, 'perjadin_detail_id', 'id');
    }
    public function lampiran()
    {
        return  $this->hasMany(PerjadinDetailLampiran::class, 'perjadin_detail_id', 'id');
    }
    public function nominatif_hotel()
    {
        return  $this->hasOne(MakNominatif::class, 'id', 'nominatif_hotel_id');
    }
    public function nominatif_uh()
    {
        return  $this->hasOne(MakNominatif::class, 'id', 'nominatif_uh_id');
    }
    public function nominatif_transport()
    {
        return  $this->hasOne(MakNominatif::class, 'id', 'nominatif_transport_id');
    }
    public function nominatif_pesawat()
    {
        return  $this->hasOne(MakNominatif::class, 'id', 'nominatif_pesawat_id');
    }
    public function nominatif_taksi_jakarta()
    {
        return  $this->hasOne(MakNominatif::class, 'id', 'nominatif_taksi_jakarta_id');
    }
    public function nominatif_taksi_tujuan()
    {
        return  $this->hasOne(MakNominatif::class, 'id', 'nominatif_taksi_tujuan_id');
    }
    public function nominatif_representatif()
    {
        return  $this->hasOne(MakNominatif::class, 'id', 'nominatif_representatif_id');
    }
}
