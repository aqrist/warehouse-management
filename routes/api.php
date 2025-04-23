<?php

use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/check-stock', function (Request $request) {
    $warehouseId = $request->input('warehouse_id');
    $productId = $request->input('product_id');

    $stock = Stock::where('warehouse_id', $warehouseId)
        ->where('product_id', $productId)
        ->first();

    return response()->json([
        'stock' => $stock ? $stock->quantity : 0
    ]);
});
