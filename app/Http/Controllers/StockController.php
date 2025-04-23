<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\StockMovement;
use App\Helpers\WarehouseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view stocks', ['only' => ['index', 'show', 'getData']]);
        $this->middleware('permission:adjust stocks', ['only' => ['adjust', 'saveAdjustment']]);
        $this->middleware('permission:transfer stocks', ['only' => ['transfer', 'saveTransfer']]);
    }

    public function index()
    {
        $warehouses = WarehouseHelper::getAccessibleWarehouses();

        return view('stocks.index', compact('warehouses'));
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

        $stocks = Stock::with(['product', 'warehouse', 'warehouse.branch']);

        if (!empty($warehouseIds)) {
            $stocks->whereIn('warehouse_id', $warehouseIds);
        }

        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $stocks->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('product_id') && $request->product_id) {
            $stocks->where('product_id', $request->product_id);
        }

        if ($request->has('product_name') && $request->product_name) {
            $stocks->whereHas('product', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->product_name . '%')
                    ->orWhere('sku', 'like', '%' . $request->product_name . '%');
            });
        }

        if ($request->has('status') && $request->status) {
            if ($request->status == 'out_of_stock') {
                $stocks->where('quantity', '<=', 0);
            } elseif ($request->status == 'low_stock') {
                $stocks->whereRaw('quantity > 0 AND quantity <= min_quantity');
            } elseif ($request->status == 'in_stock') {
                $stocks->whereRaw('quantity > min_quantity');
            }
        }

        return DataTables::of($stocks)
            ->addColumn('product_name', function ($stock) {
                return $stock->product->name;
            })
            ->addColumn('product_sku', function ($stock) {
                return $stock->product->sku;
            })
            ->addColumn('warehouse_name', function ($stock) {
                return $stock->warehouse->name;
            })
            ->addColumn('branch_name', function ($stock) {
                return $stock->warehouse->branch->name;
            })
            ->addColumn('stock_status', function ($stock) {
                if ($stock->quantity <= 0) {
                    return '<span class="badge bg-danger">Habis</span>';
                } elseif ($stock->quantity <= $stock->min_quantity) {
                    return '<span class="badge bg-warning">Hampir Habis</span>';
                } else {
                    return '<span class="badge bg-success">Tersedia</span>';
                }
            })
            ->editColumn('quantity', function ($stock) {
                return number_format($stock->quantity, 2) . ' ' . $stock->product->unit;
            })
            ->editColumn('min_quantity', function ($stock) {
                return number_format($stock->min_quantity, 2) . ' ' . $stock->product->unit;
            })
            ->addColumn('action', function ($stock) {
                $actions = '';

                if (auth()->user()->can('view stocks')) {
                    $actions .= '<a href="' . route('stocks.show', $stock->id) . '" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a> ';
                }

                if (auth()->user()->can('adjust stocks')) {
                    $actions .= '<a href="' . route('stocks.adjust', $stock->id) . '" class="btn btn-sm btn-warning"><i class="bi bi-pencil-fill"></i> Penyesuaian</a> ';
                }

                if (auth()->user()->can('transfer stocks')) {
                    $actions .= '<a href="' . route('stocks.transfer', $stock->id) . '" class="btn btn-sm btn-primary"><i class="bi bi-arrow-left-right"></i> Transfer</a> ';
                }

                return $actions;
            })
            ->rawColumns(['stock_status', 'action'])
            ->make(true);
    }

    public function show(Stock $stock)
    {
        // Check if user has access to this stock's warehouse
        $this->checkStockAccess($stock);

        $stock->load('product', 'warehouse', 'warehouse.branch');

        // Get stock movements history
        $movements = StockMovement::where('warehouse_id', $stock->warehouse_id)
            ->where('product_id', $stock->product_id)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('stocks.show', compact('stock', 'movements'));
    }

    public function adjust(Stock $stock)
    {
        // Check if user has access to this stock's warehouse
        $this->checkStockAccess($stock);

        $stock->load('product', 'warehouse', 'warehouse.branch');

        return view('stocks.adjust', compact('stock'));
    }

    public function saveAdjustment(Request $request, Stock $stock)
    {
        // Check if user has access to this stock's warehouse
        $this->checkStockAccess($stock);

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:0.01',
            'type' => 'required|in:addition,subtraction',
            'notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $currentQuantity = $stock->quantity;
        $adjustmentQuantity = $request->quantity;

        if ($request->type === 'addition') {
            $newQuantity = $currentQuantity + $adjustmentQuantity;
            $movementType = 'adjustment';
            $movementQuantity = $adjustmentQuantity;
        } else {
            if ($adjustmentQuantity > $currentQuantity) {
                return redirect()->back()
                    ->with('error', 'Stok tidak dapat dikurangi melebihi jumlah yang tersedia')
                    ->withInput();
            }

            $newQuantity = $currentQuantity - $adjustmentQuantity;
            $movementType = 'adjustment';
            $movementQuantity = -$adjustmentQuantity;
        }

        // Begin transaction
        \DB::beginTransaction();

        try {
            // Update stock
            $stock->update(['quantity' => $newQuantity]);

            // Create stock movement record
            StockMovement::create([
                'warehouse_id' => $stock->warehouse_id,
                'product_id' => $stock->product_id,
                'type' => $movementType,
                'quantity' => $movementQuantity,
                'current_quantity' => $newQuantity,
                'user_id' => auth()->id(),
                'notes' => $request->notes,
            ]);

            \DB::commit();

            return redirect()->route('stocks.show', $stock->id)
                ->with('success', 'Penyesuaian stok berhasil dilakukan');
        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function transfer(Stock $stock)
    {
        // Check if user has access to this stock's warehouse
        $this->checkStockAccess($stock);

        $stock->load('product', 'warehouse', 'warehouse.branch');

        $user = auth()->user();

        // Get warehouses from user's assigned branches
        if ($user->hasRole('super-admin')) {
            $warehouses = Warehouse::where('is_active', true)
                ->where('id', '!=', $stock->warehouse_id)
                ->get();
        } else {
            $branchIds = $user->branches->pluck('id')->toArray();
            $warehouses = Warehouse::whereIn('branch_id', $branchIds)
                ->where('is_active', true)
                ->where('id', '!=', $stock->warehouse_id)
                ->get();
        }

        return view('stocks.transfer', compact('stock', 'warehouses'));
    }

    public function saveTransfer(Request $request, Stock $stock)
    {
        // Check if user has access to this stock's warehouse
        $this->checkStockAccess($stock);

        $validator = Validator::make($request->all(), [
            'target_warehouse_id' => 'required|exists:warehouses,id|different:warehouse_id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $transferQuantity = $request->quantity;
        $currentQuantity = $stock->quantity;

        if ($transferQuantity > $currentQuantity) {
            return redirect()->back()
                ->with('error', 'Jumlah transfer melebihi stok yang tersedia')
                ->withInput();
        }

        $targetWarehouseId = $request->target_warehouse_id;

        // Begin transaction
        \DB::beginTransaction();

        try {
            // Reduce stock at source warehouse
            $newSourceQuantity = $currentQuantity - $transferQuantity;
            $stock->update(['quantity' => $newSourceQuantity]);

            // Create source stock movement
            StockMovement::create([
                'warehouse_id' => $stock->warehouse_id,
                'product_id' => $stock->product_id,
                'type' => 'transfer',
                'quantity' => -$transferQuantity,
                'current_quantity' => $newSourceQuantity,
                'user_id' => auth()->id(),
                'notes' => "Transfer keluar ke gudang #$targetWarehouseId: " . $request->notes,
            ]);

            // Add or update stock at target warehouse
            $targetStock = Stock::firstOrNew([
                'warehouse_id' => $targetWarehouseId,
                'product_id' => $stock->product_id,
            ]);

            $targetCurrentQuantity = $targetStock->quantity ?? 0;
            $targetNewQuantity = $targetCurrentQuantity + $transferQuantity;

            $targetStock->quantity = $targetNewQuantity;
            $targetStock->min_quantity = $targetStock->min_quantity ?? $stock->min_quantity;
            $targetStock->save();

            // Create target stock movement
            StockMovement::create([
                'warehouse_id' => $targetWarehouseId,
                'product_id' => $stock->product_id,
                'type' => 'transfer',
                'quantity' => $transferQuantity,
                'current_quantity' => $targetNewQuantity,
                'user_id' => auth()->id(),
                'notes' => "Transfer masuk dari gudang #{$stock->warehouse_id}: " . $request->notes,
            ]);

            \DB::commit();

            return redirect()->route('stocks.show', $stock->id)
                ->with('success', 'Transfer stok berhasil dilakukan');
        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function checkStockAccess(Stock $stock)
    {
        $user = auth()->user();

        // Super admin has access to all stocks
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Check if user has access to the stock's warehouse branch
        $hasAccess = $user->branches()
            ->whereHas('warehouses', function ($query) use ($stock) {
                $query->where('warehouses.id', $stock->warehouse_id);
            })
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke stok ini');
        }

        return true;
    }
}
