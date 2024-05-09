<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'password'=> Hash::make( env('SYSTEM_PASSWORD'))
        ]);

        if(env('APP_ENV') != 'production'){

            $this->call([
                UserSeeder::class,
                StoreSeeder::class,
            ]);
        }

    }
}
