<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Branch management
            'view branches',
            'create branches',
            'edit branches',
            'delete branches',

            // Warehouse management
            'view warehouses',
            'create warehouses',
            'edit warehouses',
            'delete warehouses',

            // Product management
            'view products',
            'create products',
            'edit products',
            'delete products',

            // Category management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Supplier management
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',

            // Stock management
            'view stocks',
            'adjust stocks',
            'transfer stocks',

            // Reports
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        // Super Admin
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Admin
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'view roles',
            'view branches',
            'edit branches',
            'view warehouses',
            'create warehouses',
            'edit warehouses',
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',
            'view stocks',
            'adjust stocks',
            'transfer stocks',
            'view reports',
        ]);

        // Branch Manager
        $branchManagerRole = Role::create(['name' => 'branch-manager']);
        $branchManagerRole->givePermissionTo([
            'view users',
            'view branches',
            'view warehouses',
            'edit warehouses',
            'view products',
            'create products',
            'edit products',
            'view categories',
            'create categories',
            'edit categories',
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'view stocks',
            'adjust stocks',
            'transfer stocks',
            'view reports',
        ]);

        // Warehouse Manager
        $warehouseManagerRole = Role::create(['name' => 'warehouse-manager']);
        $warehouseManagerRole->givePermissionTo([
            'view warehouses',
            'view products',
            'view categories',
            'view suppliers',
            'view stocks',
            'adjust stocks',
            'transfer stocks',
            'view reports',
        ]);

        // Staff
        $staffRole = Role::create(['name' => 'staff']);
        $staffRole->givePermissionTo([
            'view products',
            'view categories',
            'view suppliers',
            'view stocks',
            'view reports',
        ]);

        // Create a super admin user
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole('super-admin');
    }
}
