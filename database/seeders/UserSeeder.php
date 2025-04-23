<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Super Admin (already created in RoleAndPermissionSeeder)
        $superAdmin = User::where('email', 'admin@admin.com')->first();

        // Assign all branches to super admin
        $branches = Branch::all();
        $superAdmin->branches()->attach($branches->pluck('id')->toArray());

        // Create Branch Managers (one for each branch)
        $branches->each(function ($branch) {
            $branchManager = User::create([
                'name' => 'Manager ' . $branch->name,
                'email' => 'manager.' . strtolower(str_replace(' ', '', $branch->name)) . '@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            $branchManager->assignRole('branch-manager');
            $branchManager->branches()->attach($branch->id);
        });

        // Create Warehouse Managers (one for each warehouse)
        $branches->each(function ($branch) {
            $branch->warehouses->each(function ($warehouse) {
                $warehouseManager = User::create([
                    'name' => 'WH Manager ' . $warehouse->name,
                    'email' => 'wh.' . strtolower(str_replace(' ', '', $warehouse->name)) . '@example.com',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);

                $warehouseManager->assignRole('warehouse-manager');
                $warehouseManager->branches()->attach($warehouse->branch_id);
            });
        });

        // Create Staff (3 for each branch)
        $branches->each(function ($branch, $index) {
            for ($i = 1; $i <= 3; $i++) {
                $staff = User::create([
                    'name' => 'Staff ' . $i . ' ' . $branch->name,
                    'email' => 'staff' . $i . '.' . strtolower(str_replace(' ', '', $branch->name)) . '@example.com',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);

                $staff->assignRole('staff');
                $staff->branches()->attach($branch->id);
            }
        });
    }
}
