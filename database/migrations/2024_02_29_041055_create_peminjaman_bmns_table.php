<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peminjaman_bmns', function (Blueprint $table) {
            $table->id();
            $table->string('tiket');
            $table->string('nup');
            $table->string('jenis_layanan');
            $table->string('nip')->nullable();
            $table->date('tanggal_pengembalian')->nullable();
            $table->string('status_pengembalian')->default('BELUM KEMBALI');
            $table->string('status')->default('VERIFIKASI ADMIN');
            $table->string('nama_peminta')->nullable();
            $table->string('unit')->nullable();
            $table->string('catatan')->nullable();
            $table->string('penerima')->nullable();
            $table->string('no_wa')->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->text('ttd')->nullable();
            $table->date('tanggal_terima_pengembalian')->nullable();
            $table->text('ttd_pengembalian')->nullable();
            $table->string('penerima_pengembalian')->nullable();
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_bmns');
    }
};
