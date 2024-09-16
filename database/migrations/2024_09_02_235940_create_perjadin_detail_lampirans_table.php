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
        Schema::create('perjadin_detail_lampirans', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('perjadin_detail_id');
            $table->enum('type', ['HOTEL', 'UH', 'PESAWAT', 'REP', 'DARAT', 'LAINNYA']);
            $table->string('file_name');
            $table->string('lampiran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perjadin_detail_lampirans');
    }
};
