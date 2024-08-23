<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PermintaanPersediaan extends Model
{
    use HasFactory;

    protected $fillable = [
        'tiket',
        'nama',
        'unit',
        'nip',
        'status',
        'catatan',
        'penerima',
        'ttd',
        'no_wa',
        'tanggal_diterima',
        'user_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:d F Y',
        'tanggal_diterima' => 'datetime:d F Y',
    ];
    protected $appends = ['tipe'];

    public static function generateTicketNumber()
    {
        // Mendapatkan timestamp saat ini
        $timestamp = now()->timestamp;
        // Membuat bagian unik dari string acak
        $uniquePart = Str::random(5);
        // Menggabungkan timestamp dan bagian unik untuk membentuk nomor tiket
        $ticketNumber = $timestamp . $uniquePart;
        return $ticketNumber;
    }

    public function getTipeAttribute()
    {
        return  'PERSEDIAAN';
    }

    public function detail()
    {
        return  $this->hasMany(DetailPermintaanPersediaan::class, 'permintaan_persediaan_id', 'id');
    }
    public function log()
    {
        return  $this->hasMany(LogPermintaanPersediaan::class, 'permintaan_persediaan_id', 'id');
    }
}
