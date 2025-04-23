<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\StockAdjustment;
use App\Helpers\WarehouseHelper;
use Illuminate\Support\Facades\DB;
use App\Models\StockAdjustmentItem;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class StockAdjustmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:adjust stocks');
    }

    public function index()
    {
        $warehouses = WarehouseHelper::getAccessibleWarehouses();

        return view('stock-adjustments.index', compact('warehouses'));
    }

    public function getData(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = [];

        if (!$user->hasRole('super-admin')) {
            // Get warehouses from user's assigned branches
            $branchIds = $user->branches->pluck('id')->toArray();
            $warehouseIds = Warehouse::whereIn('branch_id', $branchIds)->pluck('id')->toArray();
        }

        $adjustments = StockAdjustment::with(['warehouse', 'warehouse.branch', 'user', 'items']);

        if (!empty($warehouseIds)) {
            $adjustments->whereIn('warehouse_id', $warehouseIds);
        }

        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $adjustments->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('start_date') && $request->start_date) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $adjustments->where('date', '>=', $startDate);
        }

        if ($request->has('end_date') && $request->end_date) {
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $adjustments->where('date', '<=', $endDate);
        }

        return DataTables::of($adjustments)
            ->addColumn('warehouse_name', function ($adjustment) {
                return $adjustment->warehouse->name;
            })
            ->addColumn('branch_name', function ($adjustment) {
                return $adjustment->warehouse->branch->name;
            })
            ->addColumn('items_count', function ($adjustment) {
                return $adjustment->items->count();
            })
            ->addColumn('action', function ($adjustment) {
                $actions = '';

                $actions .= '<a href="' . route('stock-adjustments.show', $adjustment->id) . '" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a> ';

                $actions .= '<form action="' . route('stock-adjustments.destroy', $adjustment->id) . '" method="POST" style="display:inline">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')"><i class="bi bi-trash"></i></button>
                </form>';

                return $actions;
            })
            ->editColumn('date', function ($adjustment) {
                return $adjustment->date->format('d/m/Y');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $warehouses = WarehouseHelper::getAccessibleWarehouses();

        // If warehouse_id is provided from a previous screen
        $selectedWarehouseId = $request->warehouse_id ?? null;

        // If product_id is provided from a previous screen
        $selectedProductId = $request->product_id ?? null;
        $selectedProduct = null;

        if ($selectedProductId) {
            $selectedProduct = Product::find($selectedProductId);
        }

        return view('stock-adjustments.create', compact('warehouses', 'selectedWarehouseId', 'selectedProduct'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'notes' => 'required|string',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|numeric|min:0.01',
            'types' => 'required|array',
            'types.*' => 'required|in:addition,subtraction',
            'item_notes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate reference number
        $referenceNo = WarehouseHelper::generateReferenceNumber('ADJ', 'stock_adjustments');

        // Begin transaction
        DB::beginTransaction();

        try {
            // Create stock adjustment header
            $adjustment = StockAdjustment::create([
                'reference_no' => $referenceNo,
                'warehouse_id' => $request->warehouse_id,
                'date' => $request->date,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            $productIds = $request->product_ids;
            $quantities = $request->quantities;
            $types = $request->types;
            $itemNotes = $request->item_notes ?? [];

            // Process each product
            for ($i = 0; $i < count($productIds); $i++) {
                if (!isset($productIds[$i]) || !isset($quantities[$i]) || !isset($types[$i])) {
                    continue;
                }

                $productId = $productIds[$i];
                $quantity = $quantities[$i];
                $type = $types[$i];
                $note = $itemNotes[$i] ?? null;

                if ($quantity <= 0) {
                    continue;
                }

                // Create adjustment item
                $adjustmentItem = StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'type' => $type,
                    'notes' => $note,
                ]);

                // Update stock
                $stock = Stock::firstOrNew([
                    'warehouse_id' => $request->warehouse_id,
                    'product_id' => $productId,
                ]);

                $currentQuantity = $stock->quantity ?? 0;
                $newQuantity = $currentQuantity;

                // Calculate new quantity based on adjustment type
                if ($type === 'addition') {
                    $newQuantity = $currentQuantity + $quantity;
                    $movementQuantity = $quantity;
                } else { // subtraction
                    $newQuantity = $currentQuantity - $quantity;
                    $movementQuantity = -$quantity;
                }

                // If stock is new, set min_quantity
                if (!$stock->exists) {
                    $stock->min_quantity = 0;
                }

                $stock->quantity = $newQuantity;
                $stock->save();

                // Create stock movement
                StockMovement::create([
                    'warehouse_id' => $request->warehouse_id,
                    'product_id' => $productId,
                    'type' => 'adjustment',
                    'quantity' => $movementQuantity,
                    'current_quantity' => $newQuantity,
                    'user_id' => auth()->id(),
                    'reference_id' => $adjustment->id,
                    'reference_type' => StockAdjustment::class,
                    'notes' => "Penyesuaian stok: $referenceNo - " . ($note ?? $request->notes),
                ]);
            }

            DB::commit();

            return redirect()->route('stock-adjustments.show', $adjustment->id)
                ->with('success', 'Penyesuaian stok berhasil disimpan dengan nomor referensi ' . $referenceNo);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['warehouse', 'warehouse.branch', 'user', 'items.product']);

        return view('stock-adjustments.show', compact('stockAdjustment'));
    }

    public function edit(StockAdjustment $stockAdjustment)
    {
        return redirect()->route('stock-adjustments.show', $stockAdjustment->id)
            ->with('warning', 'Penyesuaian stok yang sudah disimpan tidak dapat diubah. Silakan buat penyesuaian baru jika diperlukan.');
    }

    public function update(Request $request, StockAdjustment $stockAdjustment)
    {
        return redirect()->route('stock-adjustments.show', $stockAdjustment->id)
            ->with('warning', 'Penyesuaian stok yang sudah disimpan tidak dapat diubah. Silakan buat penyesuaian baru jika diperlukan.');
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        // Begin transaction
        DB::beginTransaction();

        try {
            // Find all related stock movements
            $stockMovements = StockMovement::where('reference_id', $stockAdjustment->id)
                ->where('reference_type', StockAdjustment::class)
                ->get();

            // Revert stock movements (update stock quantities)
            foreach ($stockMovements as $movement) {
                $stock = Stock::where('warehouse_id', $movement->warehouse_id)
                    ->where('product_id', $movement->product_id)
                    ->first();

                if ($stock) {
                    // Reverse the movement
                    $stock->quantity -= $movement->quantity;
                    $stock->save();
                }

                // Delete the movement
                $movement->delete();
            }

            // Delete adjustment items
            $stockAdjustment->items()->delete();

            // Delete adjustment
            $stockAdjustment->delete();

            DB::commit();

            return redirect()->route('stock-adjustments.index')
                ->with('success', 'Penyesuaian stok berhasil dihapus dan stok telah dikembalikan ke nilai sebelumnya');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('stock-adjustments.index')
                ->with('error', 'Terjadi kesalahan saat menghapus penyesuaian stok: ' . $e->getMessage());
        }
    }

    public function getProductsByWarehouse(Request $request)
    {
        $warehouseId = $request->warehouse_id;

        if (!$warehouseId) {
            return response()->json([
                'products' => []
            ]);
        }

        // Get existing stocks
        $stocks = Stock::where('warehouse_id', $warehouseId)
            ->with('product')
            ->get()
            ->keyBy('product_id');

        // Get all active products
        $products = Product::where('is_active', true)
            ->with('category')
            ->get()
            ->map(function ($product) use ($stocks) {
                $stock = $stocks->get($product->id);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category ? $product->category->name : '-',
                    'unit' => $product->unit,
                    'current_stock' => $stock ? $stock->quantity : 0,
                ];
            });

        return response()->json([
            'products' => $products
        ]);
    }
}
