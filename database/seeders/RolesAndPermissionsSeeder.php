<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // Admin user
        // Create admin user if it doesn't exist
        if (!User::where('email', 'admin@cc.nl')->exists()) {
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@cc.nl',
                'password' => Hash::make('password'),
            ]);

            $admin->assignRole('admin');
        }

        // Editor user - not required in finished product
        // Create editor user if it doesn't exist
        /* if (!User::where('email', 'editor@cc.nl')->exists()) {
            // Create an Editor user
            $editor = User::create([
                'name' => 'Editor User',
                'email' => 'editor@cc.nl',
                'password' => Hash::make('password'),
            ]);

            $editor->assignRole('editor');
        } */
    }
}
