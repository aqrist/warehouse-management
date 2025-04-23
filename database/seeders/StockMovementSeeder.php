<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stocks = Stock::all();
        $users = User::all();
        
        // Generate random movements for the past 30 days
        for ($day = 30; $day >= 1; $day--) {
            $date = Carbon::now()->subDays($day);
            
            // Pick random stocks (about 20% of stocks each day)
            $randomStocks = $stocks->random(ceil($stocks->count() * 0.2));
            
            foreach ($randomStocks as $stock) {
                $user = $users->random();
                
                // Random movement type (in, out, adjustment)
                $types = ['in', 'out', 'adjustment'];
                $type = $types[array_rand($types)];
                
                // Random quantity
                if ($type === 'in') {
                    $quantity = rand(1, 10);
                    $currentQuantity = $stock->quantity + $quantity;
                    $notes = "Stock in from supplier";
                } elseif ($type === 'out') {
                    $maxOut = min(5, $stock->quantity);
                    $quantity = $maxOut > 0 ? -rand(1, $maxOut) : 0;
                    $currentQuantity = $stock->quantity;
                    $notes = "Stock out for sales";
                } else { // adjustment
                    $quantity = rand(-3, 3);
                    if ($stock->quantity + $quantity < 0) {
                        $quantity = 0;
                    }
                    $currentQuantity = $stock->quantity;
                    $notes = "Stock adjustment";
                }
                
                // Create movement
                StockMovement::create([
                    'warehouse_id' => $stock->warehouse_id,
                    'product_id' => $stock->product_id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'current_quantity' => $currentQuantity,
                    'user_id' => $user->id,
                    'notes' => $notes,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
                
                // Update stock quantity (for consistent history)
                if ($day === 1) {
                    $stock->update([
                        'quantity' => $currentQuantity,
                    ]);
                }
            }
        }
    }
}