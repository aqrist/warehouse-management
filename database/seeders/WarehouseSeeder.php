<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // For each branch, create 2 warehouses
        Branch::all()->each(function ($branch) {
            Warehouse::create([
                'branch_id' => $branch->id,
                'name' => $branch->name . ' - Gudang Utama',
                'code' => $branch->code . '-G01',
                'address' => $branch->address,
                'phone' => $branch->phone,
                'manager_name' => 'Manager ' . $branch->name . ' Utama',
                'is_active' => true,
            ]);
            
            Warehouse::create([
                'branch_id' => $branch->id,
                'name' => $branch->name . ' - Gudang Cabang',
                'code' => $branch->code . '-G02',
                'address' => 'Dekat ' . $branch->address,
                'phone' => $branch->phone,
                'manager_name' => 'Manager ' . $branch->name . ' Cabang',
                'is_active' => true,
            ]);
        });
    }
}