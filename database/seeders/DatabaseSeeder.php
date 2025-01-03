<?php

namespace Database\Seeders;

use App\Models\User;
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
        // User::factory(10)->create();
        
        // Admin user
        if (!User::where('email', 'admin@cc.nl')->exists()) {
            // Create an Admin user
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@cc.nl',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ]);
        }

        // Editor user - not required in finished product
        /* if (!User::where('email', 'editor@cc.nl')->exists()) {
            // Create an Editor user
            User::factory()->create([
                'name' => 'Editor User',
                'email' => 'editor@cc.nl',
                'password' => Hash::make('password'),
                'role' => 'editor'
            ]);
        } */
    }
}
