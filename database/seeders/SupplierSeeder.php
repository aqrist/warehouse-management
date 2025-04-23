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
                'name' => 'PT Elektronik Jaya',
                'code' => 'SUP-001',
                'address' => 'Jl. Industri No. 123, Jakarta',
                'phone' => '021-6661234',
                'email' => 'info@elektronikjaya.com',
                'contact_person' => 'Budi Santoso',
                'is_active' => true,
            ],
            [
                'name' => 'PT Pakaian Indah',
                'code' => 'SUP-002',
                'address' => 'Jl. Tekstil No. 45, Bandung',
                'phone' => '022-6662345',
                'email' => 'sales@pakaianindah.com',
                'contact_person' => 'Siti Nurjanah',
                'is_active' => true,
            ],
            [
                'name' => 'PT Makanan Sehat',
                'code' => 'SUP-003',
                'address' => 'Jl. Pangan No. 67, Surabaya',
                'phone' => '031-6663456',
                'email' => 'order@makanansehat.com',
                'contact_person' => 'Agus Wijaya',
                'is_active' => true,
            ],
            [
                'name' => 'PT Mebel Berkah',
                'code' => 'SUP-004',
                'address' => 'Jl. Furniture No. 89, Semarang',
                'phone' => '024-6664567',
                'email' => 'cs@mebelberkah.com',
                'contact_person' => 'Dewi Susanti',
                'is_active' => true,
            ],
            [
                'name' => 'PT Farmasi Utama',
                'code' => 'SUP-005',
                'address' => 'Jl. Kesehatan No. 101, Medan',
                'phone' => '061-6665678',
                'email' => 'info@farmasiutama.com',
                'contact_person' => 'Dr. Hendra',
                'is_active' => true,
            ],
        ];
        
        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}