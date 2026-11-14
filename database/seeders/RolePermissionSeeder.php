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
                'department.view',
                'department.create',
                'department.update',
                'department.delete',
            ];

            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
            }

            // Create roles
            $viewer = Role::firstOrCreate(['name' => 'department.viewer']);
            $editor = Role::firstOrCreate(['name' => 'department.editor']);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Assign permissions to roles
            $viewer->syncPermissions(['department.view']);
            $editor->syncPermissions([
                'department.view',
                'department.create',
                'department.update',
                'department.delete',
            ]);
        });

    }
}
