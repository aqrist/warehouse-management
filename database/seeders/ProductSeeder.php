<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::all();
        $suppliers = Supplier::all();

        // Bahan Pokok
        $bahanPokok = $categories->where('code', 'BPK')->first();
        $bahanPokokSupplier = $suppliers->where('code', 'SUP-001')->first();

        $bahanPokokProducts = [
            [
                'name' => 'Beras Pandan Wangi',
                'sku' => 'BPK001',
                'description' => 'Beras pandan wangi premium, kemasan 5kg',
                'cost' => 65000,
                'price' => 75000,
                'unit' => 'karung',
            ],
            [
                'name' => 'Tepung Terigu',
                'sku' => 'BPK002',
                'description' => 'Tepung terigu protein sedang, kemasan 1kg',
                'cost' => 12000,
                'price' => 15000,
                'unit' => 'kg',
            ],
            [
                'name' => 'Minyak Goreng',
                'sku' => 'BPK003',
                'description' => 'Minyak goreng kelapa sawit, kemasan 5 liter',
                'cost' => 70000,
                'price' => 85000,
                'unit' => 'jerigen',
            ],
        ];

        foreach ($bahanPokokProducts as $product) {
            $newProduct = Product::create(array_merge($product, [
                'category_id' => $bahanPokok->id,
                'is_active' => true,
            ]));

            $newProduct->suppliers()->attach($bahanPokokSupplier->id);
        }

        // Bumbu Dapur
        $bumbu = $categories->where('code', 'BMB')->first();
        $bumbuSupplier = $suppliers->where('code', 'SUP-002')->first();

        $bumbuProducts = [
            [
                'name' => 'Garam Dapur',
                'sku' => 'BMB001',
                'description' => 'Garam dapur beryodium, kemasan 1kg',
                'cost' => 10000,
                'price' => 12000,
                'unit' => 'kg',
            ],
            [
                'name' => 'Gula Pasir',
                'sku' => 'BMB002',
                'description' => 'Gula pasir putih, kemasan 1kg',
                'cost' => 14000,
                'price' => 16000,
                'unit' => 'kg',
            ],
            [
                'name' => 'Bumbu Rendang',
                'sku' => 'BMB003',
                'description' => 'Bumbu rendang siap pakai, kemasan 250gr',
                'cost' => 25000,
                'price' => 30000,
                'unit' => 'pack',
            ],
        ];

        foreach ($bumbuProducts as $product) {
            $newProduct = Product::create(array_merge($product, [
                'category_id' => $bumbu->id,
                'is_active' => true,
            ]));

            $newProduct->suppliers()->attach($bumbuSupplier->id);
        }

        // Sayuran
        $sayuran = $categories->where('code', 'SYR')->first();
        $sayuranSupplier = $suppliers->where('code', 'SUP-003')->first();

        $sayuranProducts = [
            [
                'name' => 'Kangkung',
                'sku' => 'SYR001',
                'description' => 'Kangkung segar, per ikat',
                'cost' => 3000,
                'price' => 5000,
                'unit' => 'ikat',
            ],
            [
                'name' => 'Bayam',
                'sku' => 'SYR002',
                'description' => 'Bayam segar, per ikat',
                'cost' => 3000,
                'price' => 5000,
                'unit' => 'ikat',
            ],
            [
                'name' => 'Kol/Kubis',
                'sku' => 'SYR003',
                'description' => 'Kol/kubis segar, per kg',
                'cost' => 8000,
                'price' => 12000,
                'unit' => 'kg',
            ],
        ];

        foreach ($sayuranProducts as $product) {
            $newProduct = Product::create(array_merge($product, [
                'category_id' => $sayuran->id,
                'is_active' => true,
            ]));

            $newProduct->suppliers()->attach($sayuranSupplier->id);
        }

        // Daging & Seafood
        $daging = $categories->where('code', 'DGS')->first();
        $dagingSupplier = $suppliers->where('code', 'SUP-004')->first();

        $dagingProducts = [
            [
                'name' => 'Ayam Potong',
                'sku' => 'DGS001',
                'description' => 'Ayam potong segar, per ekor',
                'cost' => 45000,
                'price' => 55000,
                'unit' => 'ekor',
            ],
            [
                'name' => 'Daging Sapi',
                'sku' => 'DGS002',
                'description' => 'Daging sapi segar, per kg',
                'cost' => 120000,
                'price' => 140000,
                'unit' => 'kg',
            ],
            [
                'name' => 'Udang Segar',
                'sku' => 'DGS003',
                'description' => 'Udang segar ukuran sedang, per kg',
                'cost' => 80000,
                'price' => 95000,
                'unit' => 'kg',
            ],
        ];

        foreach ($dagingProducts as $product) {
            $newProduct = Product::create(array_merge($product, [
                'category_id' => $daging->id,
                'is_active' => true,
            ]));

            $newProduct->suppliers()->attach($dagingSupplier->id);
        }

        // Minuman
        $minuman = $categories->where('code', 'MNM')->first();
        $minumanSupplier = $suppliers->where('code', 'SUP-005')->first();

        $minumanProducts = [
            [
                'name' => 'Teh Celup',
                'sku' => 'MNM001',
                'description' => 'Teh celup, isi 25 sachet',
                'cost' => 8000,
                'price' => 12000,
                'unit' => 'box',
            ],
            [
                'name' => 'Kopi Bubuk',
                'sku' => 'MNM002',
                'description' => 'Kopi bubuk robusta, kemasan 250gr',
                'cost' => 15000,
                'price' => 20000,
                'unit' => 'pack',
            ],
            [
                'name' => 'Sirup Gula Aren',
                'sku' => 'MNM003',
                'description' => 'Sirup gula aren, botol 500ml',
                'cost' => 25000,
                'price' => 35000,
                'unit' => 'botol',
            ],
        ];

        foreach ($minumanProducts as $product) {
            $newProduct = Product::create(array_merge($product, [
                'category_id' => $minuman->id,
                'is_active' => true,
            ]));

            $newProduct->suppliers()->attach($minumanSupplier->id);
        }
    }
}
