<?php

namespace Database\Seeders;

use App\Models\Bmn;
use Illuminate\Database\Seeder;

class BmnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Bmn::factory(20)->create();
    }
}
