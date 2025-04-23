<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => 'Elektronik',
                'code' => 'ELK',
                'description' => 'Produk elektronik seperti TV, radio, dll',
                'is_active' => true,
            ],
            [
                'name' => 'Pakaian',
                'code' => 'PKN',
                'description' => 'Produk pakaian seperti baju, celana, dll',
                'is_active' => true,
            ],
            [
                'name' => 'Makanan',
                'code' => 'MKN',
                'description' => 'Produk makanan seperti snack, minuman, dll',
                'is_active' => true,
            ],
            [
                'name' => 'Perabotan',
                'code' => 'PRB',
                'description' => 'Produk perabotan seperti meja, kursi, dll',
                'is_active' => true,
            ],
            [
                'name' => 'Kesehatan',
                'code' => 'KSH',
                'description' => 'Produk kesehatan seperti obat, vitamin, dll',
                'is_active' => true,
            ],
        ];
        
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}