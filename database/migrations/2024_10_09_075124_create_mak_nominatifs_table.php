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
        Schema::create('mak_nominatifs', function (Blueprint $table) {
            $table->id();
            $table->integer('mak_id');
            $table->text('uraian');
            $table->enum('type', ['header', 'detail']);
            $table->double('volume');
            $table->double('harga');
            $table->string('satuan');
            $table->double('jumlah');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mak_nominatifs');
    }
};
