<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $branches = [
            [
                'name' => 'Cabang Jakarta',
                'code' => 'JKT',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'phone' => '021-5551234',
                'email' => 'jakarta@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Cabang Surabaya',
                'code' => 'SBY',
                'address' => 'Jl. Pemuda No. 45, Surabaya',
                'phone' => '031-5552345',
                'email' => 'surabaya@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Cabang Bandung',
                'code' => 'BDG',
                'address' => 'Jl. Asia Afrika No. 67, Bandung',
                'phone' => '022-5553456',
                'email' => 'bandung@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Cabang Medan',
                'code' => 'MDN',
                'address' => 'Jl. Diponegoro No. 89, Medan',
                'phone' => '061-5554567',
                'email' => 'medan@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Cabang Makassar',
                'code' => 'MKS',
                'address' => 'Jl. Urip Sumoharjo No. 101, Makassar',
                'phone' => '0411-5555678',
                'email' => 'makassar@example.com',
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
