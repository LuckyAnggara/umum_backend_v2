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
        Schema::create('perjadins', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tahun_anggaran');
            $table->string('no_st');
            $table->date('tanggal_st');
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->string('nama_kegiatan');
            $table->string('tempat_kegiatan');
            $table->integer('provinsi_id');
            $table->integer('mak_id');
            $table->double('total_anggaran')->default(0);
            $table->double('total_realisasi')->default(0);
            $table->date('tanggal_verifikasi')->nullable();
            $table->date('tanggal_verifikasi_ptj')->nullable();
            $table->boolean('ptj')->default(0);
            $table->enum('status', ['PERENCANAAN', 'VERIFIKASI', 'PERTANGGUNG JAWABAN', 'VERFIKASI PTJ', 'SELESAI']);
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perjadins');
    }
};
