<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $warehouses = Warehouse::all();
        $products = Product::all();
        
        // For each warehouse, add all products with random stock levels
        foreach ($warehouses as $warehouse) {
            foreach ($products as $product) {
                // Main warehouses have more stock
                $isMainWarehouse = strpos($warehouse->name, 'Utama') !== false;
                
                // Set min quantity based on product type
                $minQuantity = 5;
                if ($product->category->code === 'ELK' || $product->category->code === 'PRB') {
                    $minQuantity = 3;
                } elseif ($product->category->code === 'MKN') {
                    $minQuantity = 20;
                }
                
                // Set initial quantity (some products will be low stock for demo purposes)
                $quantity = rand($minQuantity - 2, $minQuantity * 5);
                if ($isMainWarehouse) {
                    $quantity *= 2;
                }
                
                // Make sure quantity is never negative
                if ($quantity < 0) {
                    $quantity = 0;
                }
                
                Stock::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'min_quantity' => $minQuantity,
                ]);
            }
        }
    }
}
