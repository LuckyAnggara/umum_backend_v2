<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['tahun_anggaran' => 2024, 'unit_id' => '8', 'kode_mak' => '1569.1.a.2.23.23', 'keterangan' => 'Perjalanan Dinas Luar Kota', 'anggaran' => 10000000],
            ['tahun_anggaran' => 2024, 'unit_id' => '8', 'kode_mak' => '1569.asdasd.asdasd3', 'keterangan' => 'Perjalanan Dinas Dalam Kota', 'anggaran' => 5000000],
        ];

        DB::table('maks')->insert($data);
    }
}
