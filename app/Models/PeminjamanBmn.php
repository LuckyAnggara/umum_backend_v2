<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PeminjamanBmn extends Model
{
    use HasFactory;

    protected $fillable = [
        'tiket',
        'nup',
        'jenis_layanan',
        'unit',
        'nip',
        'nama_peminta',
        'catatan',
        'penerima',
        'ttd',
        'no_wa',
        'tanggal_diterima', 'tanggal_pengembalian',
        'status',
        'tanggal_terima_pengembalian',
        'ttd_pengembalian',
        'penerima_pengembalian',
        'status_pengembalian'
    ];

    protected $casts = [
        'created_at' => 'datetime:d F Y',
        'tanggal_diterima' => 'datetime:d F Y',
        'tanggal_pengembalian' => 'datetime:d F Y',
    ];

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

    public function bmn()
    {
        return  $this->hasOne(Bmn::class, 'nup', 'nup');
    }
}
