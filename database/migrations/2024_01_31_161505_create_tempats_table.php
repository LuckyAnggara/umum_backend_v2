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
        Schema::create('tempats', function (Blueprint $table) {
            $table->id();
            $table->string('ruangan');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_akhir');
            $table->string('nip')->nullable();
            $table->string('nama');
            $table->string('unit');
            $table->string('jumlah_peserta')->nullable();
            $table->string('no_wa')->nullable();
            $table->text('kegiatan');
            $table->string('status')->default('BELUM SELESAI');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tempats');
    }
};
