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

        $this->call(BendaharaSeeder::class);
        $this->call(PpkSeeder::class);
        $this->call(UnitSeeder::class);
        $this->call(MakSeeder::class);
        // $this->call(InventorySeeder::class);
        // $this->call(BmnSeeder::class);
        // \App\Models\User::factory(1)->create(
        //     [
        //         'nip' => '1',
        //         'unit' => 'Bagian Umum',
        //         'role' => 'ADMIN',
        //         'password' => Hash::make('123456'),
        //         'email_verified_at' => null,
        //     ]
        // );
        //         \App\Models\User::factory(1)->create(
        //     [
        //         'nip' => '2',
        //         'unit' => 'Bagian PPL',
        //         'role' => 'USER',
        //         'password' => Hash::make('123456'),
        //         'email_verified_at' => null,
        //     ]
        // );
    }
}
