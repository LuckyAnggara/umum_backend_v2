<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $provinsi = [
            ['id' => 1, 'nama' => 'Aceh'],
            ['id' => 2, 'nama' => 'Sumatera Utara'],
            ['id' => 3, 'nama' => 'Sumatera Barat'],
            ['id' => 4, 'nama' => 'Riau'],
            ['id' => 5, 'nama' => 'Jambi'],
            ['id' => 6, 'nama' => 'Sumatera Selatan'],
            ['id' => 7, 'nama' => 'Bengkulu'],
            ['id' => 8, 'nama' => 'Lampung'],
            ['id' => 9, 'nama' => 'Kepulauan Bangka Belitung'],
            ['id' => 10, 'nama' => 'Kepulauan Riau'],
            ['id' => 11, 'nama' => 'DKI Jakarta'],
            ['id' => 12, 'nama' => 'Jawa Barat'],
            ['id' => 13, 'nama' => 'Jawa Tengah'],
            ['id' => 14, 'nama' => 'DI Yogyakarta'],
            ['id' => 15, 'nama' => 'Jawa Timur'],
            ['id' => 16, 'nama' => 'Banten'],
            ['id' => 17, 'nama' => 'Bali'],
            ['id' => 18, 'nama' => 'Nusa Tenggara Barat'],
            ['id' => 19, 'nama' => 'Nusa Tenggara Timur'],
            ['id' => 20, 'nama' => 'Kalimantan Barat'],
            ['id' => 21, 'nama' => 'Kalimantan Tengah'],
            ['id' => 22, 'nama' => 'Kalimantan Selatan'],
            ['id' => 23, 'nama' => 'Kalimantan Timur'],
            ['id' => 24, 'nama' => 'Kalimantan Utara'],
            ['id' => 25, 'nama' => 'Sulawesi Utara'],
            ['id' => 26, 'nama' => 'Sulawesi Tengah'],
            ['id' => 27, 'nama' => 'Sulawesi Selatan'],
            ['id' => 28, 'nama' => 'Sulawesi Tenggara'],
            ['id' => 29, 'nama' => 'Gorontalo'],
            ['id' => 30, 'nama' => 'Sulawesi Barat'],
            ['id' => 31, 'nama' => 'Maluku'],
            ['id' => 32, 'nama' => 'Maluku Utara'],
            ['id' => 33, 'nama' => 'Papua'],
            ['id' => 34, 'nama' => 'Papua Barat'],
        ];

        DB::table('provinsis')->insert($provinsi);
    }
}
