<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::transaction(function (): void {
            // Create permissions
            $permissions = [
                // Permissions for department management
                'department.view',
                'department.create',
                'department.update',
                'department.delete',
                // Permissions for user management
                'user.view',
                'user.create',
                'user.update',
                'user.delete',
            ];

            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
            }

            // Create roles
            $viewer = Role::firstOrCreate(['name' => 'department.viewer']);
            $editor = Role::firstOrCreate(['name' => 'department.editor']);
            $admin = Role::firstOrCreate(['name' => 'user.admin']);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Assign permissions to roles
            $viewer->syncPermissions([
                'department.view',
            ]);
            $editor->syncPermissions([
                'department.view',
                'department.create',
                'department.update',
                'department.delete',
            ]);
            $admin->syncPermissions([
                'department.view',
                'user.view',
                'user.create',
                'user.update',
                'user.delete',
            ]);
        });
    }
}
