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
        Schema::create('ptjs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan');
            $table->double('realisasi')->default(0);
            $table->date('tanggal');
            $table->string('nip')->nullable();
            $table->string('nama');
            $table->string('unit');
            $table->string('no_wa')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ptjs');
    }
};
