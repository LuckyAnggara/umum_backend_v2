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
        Schema::create('maks', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_anggaran');
            $table->integer('unit_id');
            $table->string('kode_mak');
            $table->string('keterangan');
            $table->double('anggaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maks');
    }
};
