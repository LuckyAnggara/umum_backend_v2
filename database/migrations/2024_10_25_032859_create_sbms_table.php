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
        Schema::create('sbms', function (Blueprint $table) {
            $table->id();
            $table->string('biaya')->nullable();
            $table->string('daerah')->nullable();
            $table->string('satuan')->nullable();
            $table->text('nilai')->nullable();
            $table->string('tahun_anggaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbms');
    }
};
