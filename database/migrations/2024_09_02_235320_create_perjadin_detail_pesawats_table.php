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
        Schema::create('perjadin_detail_pesawats', function (Blueprint $table) {
            $table->id();
            $table->integer('perjadin_detail_id');
            $table->string('keterangan');
            $table->double('biaya');
            $table->double('realisasi_biaya');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perjadin_detail_pesawats');
    }
};
