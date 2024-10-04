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
        Schema::create('non_perjadins', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tahun_anggaran');
            $table->date('nomor_transaksi');
            $table->date('tanggal_transaksi');
            $table->string('uraian');
            $table->integer('mak_id');
            $table->double('total_anggaran')->default(0);
            $table->double('total_realisasi')->default(0);
            $table->date('tanggal_verifikasi')->nullable();
            $table->date('tanggal_verifikasi_ptj')->nullable();
            $table->enum('status', ['PERENCANAAN', 'VERIFIKASI', 'PERTANGGUNG JAWABAN', 'VERFIKASI PTJ', 'SELESAI']);
            $table->integer('user_id');
            $table->integer('unit_id')->nullable();
            $table->integer('ppk')->nullable();
            $table->integer('bendahara')->nullable();
            $table->string('penerima')->nullable();
            $table->string('nip_penerima')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_perjadins');
    }
};
