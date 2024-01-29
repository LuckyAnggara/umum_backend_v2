<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Bmn;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(InventorySeeder::class);
        $this->call(BmnSeeder::class);
        \App\Models\User::factory(1)->create(
            [
                'nip' => '1',
                'password' => Hash::make('123456'),
                'email_verified_at' => null,
            ]
        );
    }
}
