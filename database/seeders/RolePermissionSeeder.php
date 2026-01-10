<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for each resource
        $this->createPermissions();

        // Create roles and assign permissions
        $this->createRoles();
    }

    /**
     * Create all permissions based on backend routes
     */
    private function createPermissions(): void
    {
        $modules = [
            'property-type' => ['list', 'create', 'store', 'edit', 'update', 'delete', 'toggle.status'],
            'units' => ['list', 'create', 'store', 'edit', 'update', 'delete', 'toggle.status'],
            'rooms' => ['list', 'show', 'create', 'store', 'edit', 'update', 'delete', 'toggle.status'],
            'beds' => ['list', 'create', 'store', 'edit', 'update', 'delete', 'toggle.status', 'bulk-delete'],
            'property' => ['list', 'create', 'store', 'edit', 'show', 'update', 'delete', 'toggle.status'],
            'seasons' => ['list', 'create', 'store', 'edit', 'update', 'delete', 'toggle.status'],
            'amenities' => ['list', 'create', 'store', 'edit', 'update', 'delete', 'toggle.status'],
            'dashboard' => ['view'],
            'cms' => ['view', 'update'],
            'cms.home' => ['slider.store', 'slider.update', 'slider.destroy', 'how-it-works.update'],
            // 'cms.gallery' => ['store', 'destroy'],
            // 'cms.property' => ['update'],
            // 'cms.about' => ['update'],
            // 'cms.amenities' => ['update'],
            'profile' => ['view', 'edit', 'update'],
            'settings' => ['view', 'edit', 'update'],
            // 'social' => ['list', 'create', 'store', 'edit', 'update', 'delete', 'status'],
            'user-management.users' => ['list', 'create', 'store', 'show', 'edit', 'update', 'delete'],
            'user-management.roles' => ['list', 'create', 'store', 'show', 'edit', 'update', 'delete'],
            'user-management.permissions' => ['list', 'create', 'store', 'show', 'edit', 'update', 'delete'],
            'lease' => ['list', 'create', 'store', 'edit', 'update', 'delete', 'toggle.status'],
            'lease.template' => ['list', 'create', 'store', 'edit', 'update', 'delete', 'toggle.status'],

        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$module}.{$action}";
                
                // Create readable display name
                $displayName = str_replace(['-', '.'], ' ', $module) . ' - ' . str_replace('-', ' ', ucfirst($action));
                
                Permission::firstOrCreate(
                    ['name' => $permissionName],
                    ['guard_name' => 'web', 'display_name' => $displayName]
                );
            }
        }
    }

    /**
     * Create roles and assign permissions
     */
    private function createRoles(): void
    {
        $admin = Role::firstOrCreate(
            ['name' => 'super admin'],
            ['display_name' => 'Super Admin', 'description' => 'Has access to all system features and settings']
        );
        // Admin Role - Full access
        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Admin', 'description' => 'Full access to all features']
        );
        // Assign all permissions to admin
        $admin->syncPermissions(Permission::all());

        // Manager Role - Property + Room management
        $manager = Role::firstOrCreate(
            ['name' => 'manager'],
            ['display_name' => 'Manager', 'description' => 'Property and Room management']
        );
        $managerPermissions = [
            // Dashboard
            'dashboard.view',
            
            // Property Management
            'property.list',
            'property.create',
            'property.store',
            'property.edit',
            'property.show',
            'property.update',
            'property.delete',
            'property.toggle.status',
            
            // Room Management
            'rooms.list',
            'rooms.show',
            'rooms.create',
            'rooms.store',
            'rooms.edit',
            'rooms.update',
            'rooms.delete',
            'rooms.toggle.status',
            
            // // Beds Management
            'beds.list',
            'beds.create',
            'beds.store',
            'beds.edit',
            'beds.update',
            'beds.delete',
            'beds.toggle.status',
            'beds.bulk-delete',
            
            // Units Management
            'units.list',
            'units.create',
            'units.store',
            'units.edit',
            'units.update',
            'units.delete',
            'units.toggle.status',
            
            // Profile
            'profile.view',
            'profile.edit',
            'profile.update',
        ];
        $manager->syncPermissions($managerPermissions);

        // Staff Role - View only
        $staff = Role::firstOrCreate(
            ['name' => 'staff'],
            ['display_name' => 'Staff', 'description' => 'View only access']
        );
        $staffPermissions = [
            // Dashboard
            'dashboard.view',
            
            // View only permissions
            'property.list',
            'property.show',
            // 'rooms.list',
            // 'rooms.show',
            // 'beds.list',
            // 'units.list',
            // 'amenities.list',
            'seasons.list',
            'profile.view',
        ];
        $staff->syncPermissions($staffPermissions);

        // Tenant Role - Limited access
        $tenant = Role::firstOrCreate(
            ['name' => 'tenant'],
            ['display_name' => 'Tenant', 'description' => 'Limited access for tenants']
        );
        $tenantPermissions = [
            // Minimal permissions for tenants
            'profile.view',
            'profile.edit',
            'profile.update',
        ];
        $tenant->syncPermissions($tenantPermissions);
        
    }
}
