<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            BranchSeeder::class,
            WarehouseSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            // ProductSeeder::class,
            UserSeeder::class,
            // StockSeeder::class,
            // StockMovementSeeder::class,
        ]);
    }
}
