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
        
        // Elektronik
        $elektronik = $categories->where('code', 'ELK')->first();
        $elektronikSupplier = $suppliers->where('code', 'SUP-001')->first();
        
        $elektronikProducts = [
            [
                'name' => 'TV LED 32 inch',
                'sku' => 'ELK001',
                'description' => 'TV LED dengan ukuran 32 inch, resolusi HD',
                'cost' => 1500000,
                'price' => 2000000,
                'unit' => 'unit',
            ],
            [
                'name' => 'Kulkas 2 Pintu',
                'sku' => 'ELK002',
                'description' => 'Kulkas 2 pintu dengan kapasitas 200 liter',
                'cost' => 2500000,
                'price' => 3200000,
                'unit' => 'unit',
            ],
            [
                'name' => 'Mesin Cuci 7kg',
                'sku' => 'ELK003',
                'description' => 'Mesin cuci dengan kapasitas 7kg, 1 tabung',
                'cost' => 1800000,
                'price' => 2400000,
                'unit' => 'unit',
            ],
        ];
        
        foreach ($elektronikProducts as $product) {
            $newProduct = Product::create(array_merge($product, [
                'category_id' => $elektronik->id,
                'is_active' => true,
            ]));
            
            $newProduct->suppliers()->attach($elektronikSupplier->id);
        }
        
        // Pakaian
        $pakaian = $categories->where('code', 'PKN')->first();
        $pakaianSupplier = $suppliers->where('code', 'SUP-002')->first();
        
        $pakaianProducts = [
            [
                'name' => 'Kemeja Pria',
                'sku' => 'PKN001',
                'description' => 'Kemeja pria lengan panjang, bahan katun',
                'cost' => 80000,
                'price' => 150000,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Celana Jeans',
                'sku' => 'PKN002',
                'description' => 'Celana jeans slim fit, bahan denim',
                'cost' => 120000,
                'price' => 200000,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Dress Wanita',
                'sku' => 'PKN003',
                'description' => 'Dress wanita casual, bahan rayon',
                'cost' => 100000,
                'price' => 180000,
                'unit' => 'pcs',
            ],
        ];
        
        foreach ($pakaianProducts as $product) {
            $newProduct = Product::create(array_merge($product, [
                'category_id' => $pakaian->id,
                'is_active' => true,
            ]));
            
            $newProduct->suppliers()->attach($pakaianSupplier->id);
        }
        
        // Makanan
        $makanan = $categories->where('code', 'MKN')->first();
        $makananSupplier = $suppliers->where('code', 'SUP-003')->first();
        
        $makananProducts = [
            [
                'name' => 'Biskuit Coklat',
                'sku' => 'MKN001',
                'description' => 'Biskuit dengan rasa coklat, isi 10 pcs',
                'cost' => 5000,
                'price' => 8000,
                'unit' => 'pack',
            ],
            [
                'name' => 'Minuman Soda',
                'sku' => 'MKN002',
                'description' => 'Minuman bersoda, kemasan kaleng 330ml',
                'cost' => 3000,
                'price' => 5000,
                'unit' => 'can',
            ],
            [
                'name' => 'Mie Instan',
                'sku' => 'MKN003',
                'description' => 'Mie instan rasa ayam, isi 5 pcs',
                'cost' => 10000,
                'price' => 15000,
                'unit' => 'pack',
            ],
        ];
        
        foreach ($makananProducts as $product) {
            $newProduct = Product::create(array_merge($product, [
                'category_id' => $makanan->id,
                'is_active' => true,
            ]));
            
            $newProduct->suppliers()->attach($makananSupplier->id);
        }
        
        // Perabotan
        $perabotan = $categories->where('code', 'PRB')->first();
        $perabotanSupplier = $suppliers->where('code', 'SUP-004')->first();
        
        $perabotanProducts = [
            [
                'name' => 'Meja Makan',
                'sku' => 'PRB001',
                'description' => 'Meja makan kayu jati, ukuran 120x80cm',
                'cost' => 1500000,
                'price' => 2300000,
                'unit' => 'unit',
            ],
            [
                'name' => 'Kursi Kantor',
                'sku' => 'PRB002',
                'description' => 'Kursi kantor dengan roda, bahan mesh',
                'cost' => 600000,
                'price' => 900000,
                'unit' => 'unit',
            ],
            [
                'name' => 'Lemari Pakaian',
                'sku' => 'PRB003',
                'description' => 'Lemari pakaian 2 pintu, bahan particle board',
                'cost' => 1200000,
                'price' => 1800000,
                'unit' => 'unit',
            ],
        ];
        
        foreach ($perabotanProducts as $product) {
            $newProduct = Product::create(array_merge($product, [
                'category_id' => $perabotan->id,
                'is_active' => true,
            ]));
            
            $newProduct->suppliers()->attach($perabotanSupplier->id);
        }
        
        // Kesehatan
        $kesehatan = $categories->where('code', 'KSH')->first();
        $kesehatanSupplier = $suppliers->where('code', 'SUP-005')->first();
        
        $kesehatanProducts = [
            [
                'name' => 'Vitamin C',
                'sku' => 'KSH001',
                'description' => 'Vitamin C 1000mg, isi 30 tablet',
                'cost' => 30000,
                'price' => 45000,
                'unit' => 'bottle',
            ],
            [
                'name' => 'Masker Medis',
                'sku' => 'KSH002',
                'description' => 'Masker medis 3 ply, isi 50 pcs',
                'cost' => 25000,
                'price' => 40000,
                'unit' => 'box',
            ],
            [
                'name' => 'Hand Sanitizer',
                'sku' => 'KSH003',
                'description' => 'Hand sanitizer 500ml, kandungan alkohol 70%',
                'cost' => 20000,
                'price' => 35000,
                'unit' => 'bottle',
            ],
        ];
        
        foreach ($kesehatanProducts as $product) {
            $newProduct = Product::create(array_merge($product, [
                'category_id' => $kesehatan->id,
                'is_active' => true,
            ]));
            
            $newProduct->suppliers()->attach($kesehatanSupplier->id);
        }
    }
}