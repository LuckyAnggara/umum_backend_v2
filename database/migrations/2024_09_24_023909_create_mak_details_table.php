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
        Schema::create('mak_details', function (Blueprint $table) {
            $table->id();
            $table->integer('mak_id');
            $table->enum('type', ['PERJADIN', 'NON PERJADIN']);
            $table->foreignUlid('kegiatan_id')->nullable();
            $table->string('nama_kegiatan')->nullable();
            $table->double('total_anggaran')->default(0);
            $table->double('total_realisasi')->default(0);
            $table->enum('status_realisasi', ['BELUM', 'SUDAH']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mak_details');
    }
};
