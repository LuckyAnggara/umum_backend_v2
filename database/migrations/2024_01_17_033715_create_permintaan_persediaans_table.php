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
        Schema::create('permintaan_persediaans', function (Blueprint $table) {
            $table->id();
            $table->string('tiket');
            $table->string('nama');
            $table->string('unit');
            $table->string('nip')->nullable();
            $table->string('status')->default('ORDER');
            $table->string('catatan')->nullable();
            $table->string('penerima')->nullable();
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
        Schema::dropIfExists('permintaan_persediaans');
    }
};
