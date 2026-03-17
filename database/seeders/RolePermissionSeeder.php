<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Verticals
            'verticals.view', 'verticals.create', 'verticals.edit', 'verticals.delete',
            // Stages
            'stages.view', 'stages.create', 'stages.edit', 'stages.delete',
            // Clients
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            // Deals
            'deals.view', 'deals.view-all', 'deals.create', 'deals.edit',
            'deals.delete', 'deals.restore', 'deals.force-delete',
            // Reports
            'reports.view', 'reports.export',
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin - all permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // Manager - most permissions except destructive admin actions
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions(
            Permission::whereNotIn('name', [
                'users.delete',
                'verticals.delete',
                'stages.delete',
                'deals.force-delete',
            ])->get()
        );

        // Vendedor - limited to own deals and client management
        $vendedor = Role::firstOrCreate(['name' => 'vendedor']);
        $vendedor->syncPermissions([
            'clients.view', 'clients.create', 'clients.edit',
            'deals.view', 'deals.create', 'deals.edit',
            'verticals.view', 'stages.view',
        ]);

        // Viewer - read-only
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'verticals.view', 'stages.view', 'clients.view',
            'deals.view', 'reports.view',
        ]);
    }
}
