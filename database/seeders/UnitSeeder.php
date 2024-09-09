<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $data = [
            ['id' => 1, 'nama' => 'Inspektur Jenderal'],
            ['id' => 2, 'nama' => 'Inspektorat Wilayah I'],
            ['id' => 3, 'nama' => 'Inspektorat Wilayah II'],
            ['id' => 4, 'nama' => 'Inspektorat Wilayah III'],
            ['id' => 5, 'nama' => 'Inspektorat Wilayah IV'],
            ['id' => 6, 'nama' => 'Inspektorat Wilayah V'],
            ['id' => 7, 'nama' => 'Inspektorat Wilayah VI'],
            ['id' => 8, 'nama' => 'Bagian Program dan Pelaporan'],
            ['id' => 9, 'nama' => 'Bagian Umum'],
            ['id' => 10, 'nama' => 'Kelompok Sumber Daya Manusia'],
            ['id' => 11, 'nama' => 'Kelompok Keuangan'],
            ['id' => 12, 'nama' => 'Kelompok Humas dan Sistem Informasi Pengawasan'],
        ];

        DB::table('units')->insert($data);
    }
}
