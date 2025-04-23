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
                'name' => 'Bahan Pokok',
                'code' => 'BPK',
                'description' => 'Bahan pokok seperti beras, tepung, minyak, dll',
                'is_active' => true,
            ],
            [
                'name' => 'Bumbu Dapur',
                'code' => 'BMB',
                'description' => 'Bumbu dapur seperti garam, gula, merica, dll',
                'is_active' => true,
            ],
            [
                'name' => 'Sayuran',
                'code' => 'SYR',
                'description' => 'Sayuran segar seperti bayam, kangkung, wortel, dll',
                'is_active' => true,
            ],
            [
                'name' => 'Daging & Seafood',
                'code' => 'DGS',
                'description' => 'Daging dan seafood seperti ayam, sapi, ikan, udang, dll',
                'is_active' => true,
            ],
            [
                'name' => 'Minuman',
                'code' => 'MNM',
                'description' => 'Bahan minuman seperti teh, kopi, sirup, dll',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
