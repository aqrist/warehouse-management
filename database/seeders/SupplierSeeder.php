<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suppliers = [
            [
                'name' => 'PT Bahan Pokok Nusantara',
                'code' => 'SUP-001',
                'address' => 'Jl. Pasar Induk No. 123, Jakarta',
                'phone' => '021-5551234',
                'email' => 'info@bahanpokok.co.id',
                'contact_person' => 'Budi Santoso',
                'is_active' => true,
            ],
            [
                'name' => 'CV Rempah Jaya',
                'code' => 'SUP-002',
                'address' => 'Jl. Rempah No. 45, Bandung',
                'phone' => '022-5552345',
                'email' => 'sales@rempahjaya.com',
                'contact_person' => 'Siti Nurjanah',
                'is_active' => true,
            ],
            [
                'name' => 'PT Sayur Segar Indonesia',
                'code' => 'SUP-003',
                'address' => 'Jl. Agrobisnis No. 67, Surabaya',
                'phone' => '031-5553456',
                'email' => 'order@sayursegar.co.id',
                'contact_person' => 'Agus Wijaya',
                'is_active' => true,
            ],
            [
                'name' => 'PT Daging Prima',
                'code' => 'SUP-004',
                'address' => 'Jl. Peternakan No. 89, Semarang',
                'phone' => '024-5554567',
                'email' => 'cs@dagingprima.com',
                'contact_person' => 'Dewi Susanti',
                'is_active' => true,
            ],
            [
                'name' => 'CV Minuman Tradisional',
                'code' => 'SUP-005',
                'address' => 'Jl. Kuliner No. 101, Yogyakarta',
                'phone' => '0274-5555678',
                'email' => 'info@minumantradisional.com',
                'contact_person' => 'Hendra Wijaya',
                'is_active' => true,
            ],
        ];
        
        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}