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
        Schema::create('mak_nominatif_details', function (Blueprint $table) {
            $table->id();
            $table->integer('mak_nominatif_id');
            $table->foreignUlid('kegiatan_id')->nullable();
            $table->double('jumlah');
            $table->enum('status_realisasi', ['BELUM', 'SUDAH']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mak_nominatif_details');
    }
};
