<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Perjadin extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'tahun_anggaran',
        'no_st',
        'tanggal_st',
        'tanggal_awal',
        'tanggal_akhir',
        'jenis_kegiatan',
        'nama_kegiatan',
        'jenis_perjalanan_dinas',
        'tujuan_pdln',
        'provinsi_id',
        'tempat_kegiatan',
        'tempat_kedudukan',
        'mak_id',
        'total_anggaran',
        'total_realisasi',
        'status',
        'ptj',
        'tanggal_verifikasi',
        'user_id',
        'unit_id',
        'pengusul',
        'kapokja',
        'nip_pengusul',
        'nip_kapokja',
        'tanggal_rab',
    ];

    protected $casts = [
        'created_at' => 'datetime:d F Y',
        'tanggal_st' => 'datetime:d F Y',
        'tanggal_awal' => 'datetime:d F Y',
        'tanggal_akhir' => 'datetime:d F Y',
        'tanggal_rab' => 'datetime:d F Y',
        'ptj' => 'boolean',
    ];

    // protected $keyType = 'string';
    // public $incrementing = false;

    // public static function booted()
    // {
    //     static::creating(function ($model) {
    //         $model->id = Str::ulid();
    //     });
    // }

    public function detail()
    {
        return  $this->hasMany(PerjadinDetail::class, 'perjadin_id', 'id');
    }


    public function lampiran()
    {
        return  $this->hasMany(PerjadinLampiran::class, 'perjadin_id', 'id');
    }
    public function log()
    {
        return  $this->hasMany(PerjadinLog::class, 'perjadin_id', 'id');
    }

    public function mak()
    {
        return $this->hasOne(Mak::class, 'id', 'mak_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
    }
    public function provinsi()
    {
        return $this->hasOne(Provinsi::class, 'id', 'provinsi_id');
    }

    public function calculateTotalAnggaran()
    {
        $total = 0;

        foreach ($this->detail as $detail) {
            // For models with both biaya and hari columns (hotel, uang_harian, representatif)
            $total += $detail->hotel ? $detail->hotel->sum(fn($item) => $item->biaya * $item->hari) : 0;
            $total += $detail->uang_harian ? $detail->uang_harian->sum(fn($item) => $item->biaya * $item->hari) : 0;
            $total += $detail->representatif ? $detail->representatif->sum(fn($item) => $item->biaya * $item->hari) : 0;

            // For models with only biaya column (transport, pesawat, taksi_jakarta, taksi_tujuan)
            $total += $detail->transport ? $detail->transport->sum('biaya') : 0;
            $total += $detail->pesawat ? $detail->pesawat->sum('biaya') : 0;
            $total += $detail->taksi_jakarta ? $detail->taksi_jakarta->sum('biaya') : 0;
            $total += $detail->taksi_tujuan ? $detail->taksi_tujuan->sum('biaya') : 0;
        }

        return $total;
    }

    public function calculateTotalRealisasi()
    {
        $total = 0;

        foreach ($this->detail as $detail) {
            // For models with both biaya and hari columns (hotel, uang_harian, representatif)
            $total += $detail->hotel ? $detail->hotel->sum(fn($item) => $item->realisasi_biaya * $item->realisasi_hari) : 0;
            $total += $detail->uang_harian ? $detail->uang_harian->sum(fn($item) => $item->realisasi_biaya * $item->realisasi_hari) : 0;
            $total += $detail->representatif ? $detail->representatif->sum(fn($item) => $item->realisasi_biaya * $item->realisasi_hari) : 0;

            // For models with only biaya column (transport, pesawat, taksi_jakarta, taksi_tujuan)
            $total += $detail->transport ? $detail->transport->sum('realisasi_biaya') : 0;
            $total += $detail->pesawat ? $detail->pesawat->sum('realisasi_biaya') : 0;
            $total += $detail->taksi_jakarta ? $detail->taksi_jakarta->sum('realisasi_biaya') : 0;
            $total += $detail->taksi_tujuan ? $detail->taksi_tujuan->sum('realisasi_biaya') : 0;
        }

        return $total;
    }
}
