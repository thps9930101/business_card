<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


         \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => env('SYSTEM_ADMIN'),
            'is_admin'=>1,
            'password'=>env('SYSTEM_PASSWORD')
        ]);

        \App\Models\User::factory(10)->create();
    }
}
