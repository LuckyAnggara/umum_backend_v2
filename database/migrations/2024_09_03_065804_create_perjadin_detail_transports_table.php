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
        Schema::create('perjadin_detail_transports', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('perjadin_detail_id');
            $table->string('tipe');
            $table->string('keterangan')->nullable();
            $table->double('biaya');
            $table->double('realisasi_biaya');
            $table->boolean('bukti')->default(false);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perjadin_detail_transports');
    }
};
