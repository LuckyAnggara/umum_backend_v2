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
        Schema::create('permintaan_layanan_bmns', function (Blueprint $table) {
            $table->id();
            $table->string('tiket');
            $table->string('nup');
            $table->string('jenis_layanan');
            $table->string('nip')->nullable();
            $table->string('nama_peminta')->nullable();
            $table->string('unit')->nullable();
            $table->string('catatan')->nullable();
            $table->string('status')->default('ORDER');
            $table->string('penerima')->nullable();
            $table->string('no_wa');
            $table->date('tanggal_diterima')->nullable();
            $table->text('ttd')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_layanan_bmns');
    }
};
