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
        Schema::create('perjadin_details', function (Blueprint $table) {
            $table->id();
            $table->integer('perjadin_id');
            $table->string('no_sppd')->nullable();
            $table->string('tanggal_sppd')->nullable();
            $table->string('nip');
            $table->string('nama');
            $table->string('jabatan');
            $table->string('pangkat');
            $table->string('unit');
            $table->string('peran')->defaul('-');
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->integer('ppk')->nullable();
            $table->integer('bendahara')->nullable();
            $table->integer('jumlah_hari')->default(0);
             $table->enum('status',['BELUM LENGKAP','LENGKAP'])->default('BELUM LENGKAP');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perjadin_details');
    }
};
