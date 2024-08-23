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
        Schema::create('arsips', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->date('tanggal_surat')->nullable();
            $table->string('klasifikasi');
            $table->string('pencipta_arsip');
            $table->string('pengolah_arsip');
            $table->string('tingkat_perkembangan');
            $table->string('jumlah')->nullable();
            $table->text('uraian')->nullable();
            $table->string('lokasi');
            $table->string('lemari');
            $table->string('rak');
            $table->string('no_box')->nullable();
            $table->string('no_folder')->nullable();
            $table->string('jenis_media');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsips');
    }
};
