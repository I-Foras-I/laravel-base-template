<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.export',
            
            // Role management
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            
            // Permission management
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            
            // Settings
            'settings.view',
            'settings.edit',
            
            // Activity Logs
            'activity-logs.view',
            'activity-logs.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - all permissions
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - most permissions except some critical ones
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.export',
            'roles.view',
            'roles.edit',
            'permissions.view',
            'settings.view',
            'settings.edit',
            'activity-logs.view',
        ]);

        // Moderator - limited permissions
        $moderator = Role::create(['name' => 'moderator']);
        $moderator->givePermissionTo([
            'users.view',
            'users.edit',
            'activity-logs.view',
        ]);

        // User - basic permissions
        $user = Role::create(['name' => 'user']);
        // No special permissions for regular users
    }
}
