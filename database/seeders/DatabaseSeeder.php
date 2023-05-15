<?php

namespace Database\Seeders;
use Illuminate\Support\Str;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory()->create([
            'name' => fake()->name(),
            'email' => "simo@gamil.com",
            'email_verified_at' => now(),
            'password' => '1234', // password
            'remember_token' => Str::random(10),
        ]);
        // \App\Models\Category::factory()->create([
        //    "description" => 'simo nice discription',
        //     "name" => 'summer',
            
        // ]);
       
    }
}
