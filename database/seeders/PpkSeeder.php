<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PpkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id' => 1, 'nama' => 'Baneriama','nip'=>'197306231994032001'],
            ['id' => 2, 'nama' => 'Lusi Handayani','nip'=>'198601112009122008'],
        ];

        DB::table('ppks')->insert($data);
    }
}
