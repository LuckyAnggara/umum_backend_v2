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
        Schema::create('bmns', function (Blueprint $table) {
            $table->id();
            $table->string('nup')->uniqid();
            $table->string('nama');
            $table->text('keterangan')->nullable();
            $table->string('penanggung_jawab')->nullable();
            $table->string('ruangan')->nullable();
            $table->string('tahun_perolehan')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bmns');
    }
};
