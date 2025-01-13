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

        // Create permissions
        $permissions = [
            // Admin permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            'block users',
            'view roles',
            'assign roles',
            'manage settings',

            // Editor permissions
            'manage news',
            'manage promotions',
            'manage banners',
            'view cms',
            'edit content',
            'manage categories',
            'manage subcategories',
            'manage products'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $roles = [
            'admin' => $permissions,
            'editor' => [
                'view cms',
                'edit content',
                'manage news',
                'manage promotions',
                'manage banners',
                'manage categories',
                'manage subcategories',
                'manage products',
            ],
            'customer' => [
                'view users',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        // Admin user
        // Create admin user if it doesn't exist
        if (!User::where('email', 'admin@cc.nl')->exists()) {
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@cc.nl',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]);

            $admin->assignRole('admin');
        }

        // Editor user
        // Create editor user if it doesn't exist
        if (!User::where('email', 'editor@cc.nl')->exists()) {
            // Create an Editor user
            $editor = User::create([
                'name' => 'Editor User',
                'email' => 'editor@cc.nl',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]);

            $editor->assignRole('editor');
        }
    }
}
