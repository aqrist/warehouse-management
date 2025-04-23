<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Models\Category;
use App\Models\StockMovement;
use App\Helpers\WarehouseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockReportExport;
use App\Exports\StockMovementReportExport;
use App\Exports\StockValuationReportExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view reports');
    }

    /**
     * Stock Report page
     */
    public function stock()
    {
        $warehouses = WarehouseHelper::getAccessibleWarehouses();
        $branches = WarehouseHelper::getAccessibleBranches();
        $categories = Category::where('is_active', true)->get();
        
        return view('reports.stock', compact('warehouses', 'branches', 'categories'));
    }

    /**
     * Generate Stock Report
     */
    public function generateStockReport(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category_id' => 'nullable|exists:categories,id',
            'stock_status' => 'nullable|in:all,in_stock,low_stock,out_of_stock',
        ]);
        
        $user = auth()->user();
        $branch_id = $request->branch_id;
        $warehouse_id = $request->warehouse_id;
        $category_id = $request->category_id;
        $stock_status = $request->stock_status ?: 'all';
        $search = $request->search;
        
        $query = Stock::with(['product.category', 'warehouse.branch'])
            ->select('stocks.*')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('branches', 'warehouses.branch_id', '=', 'branches.id');
        
        // Filter by branch or accessible branches for non-admin users
        if (!$user->hasRole('super-admin')) {
            $branchIds = $user->branches->pluck('id')->toArray();
            $query->whereIn('branches.id', $branchIds);
        }
        
        // Apply filters
        if ($branch_id) {
            $query->where('branches.id', $branch_id);
        }
        
        if ($warehouse_id) {
            $query->where('warehouses.id', $warehouse_id);
        }
        
        if ($category_id) {
            $query->whereHas('product', function($q) use ($category_id) {
                $q->where('category_id', $category_id);
            });
        }
        
        if ($stock_status != 'all') {
            if ($stock_status == 'in_stock') {
                $query->whereRaw('stocks.quantity > stocks.min_quantity');
            } elseif ($stock_status == 'low_stock') {
                $query->whereRaw('stocks.quantity > 0 AND stocks.quantity <= stocks.min_quantity');
            } elseif ($stock_status == 'out_of_stock') {
                $query->where('stocks.quantity', '<=', 0);
            }
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%");
            });
        }
        
        $stocks = $query->orderBy('warehouses.id')->orderBy('products.name')->get();
        
        // Group results by branch and warehouse
        $groupedStocks = [];
        foreach ($stocks as $stock) {
            $branchId = $stock->warehouse->branch_id;
            $warehouseId = $stock->warehouse_id;
            
            if (!isset($groupedStocks[$branchId])) {
                $groupedStocks[$branchId] = [
                    'name' => $stock->warehouse->branch->name,
                    'warehouses' => []
                ];
            }
            
            if (!isset($groupedStocks[$branchId]['warehouses'][$warehouseId])) {
                $groupedStocks[$branchId]['warehouses'][$warehouseId] = [
                    'name' => $stock->warehouse->name,
                    'stocks' => []
                ];
            }
            
            $groupedStocks[$branchId]['warehouses'][$warehouseId]['stocks'][] = $stock;
        }
        
        // Get selected warehouse, branch, and category names for the report title
        $selectedWarehouse = $warehouse_id ? Warehouse::find($warehouse_id)->name : 'Semua Gudang';
        $selectedBranch = $branch_id ? Branch::find($branch_id)->name : 'Semua Cabang';
        $selectedCategory = $category_id ? Category::find($category_id)->name : 'Semua Kategori';
        
        // Store parameters in session for export
        session([
            'stock_report_params' => [
                'branch_id' => $branch_id,
                'warehouse_id' => $warehouse_id,
                'category_id' => $category_id,
                'stock_status' => $stock_status,
                'search' => $search,
            ],
            'stock_report_titles' => [
                'warehouse' => $selectedWarehouse,
                'branch' => $selectedBranch,
                'category' => $selectedCategory,
                'status' => $this->getStatusLabel($stock_status),
            ]
        ]);
        
        return view('reports.stock_result', compact('groupedStocks', 'selectedWarehouse', 'selectedBranch', 'selectedCategory', 'stock_status'));
    }

    /**
     * Export Stock Report to Excel
     */
    public function exportStockReport(Request $request)
    {
        $format = $request->format ?: 'xlsx';
        $params = session('stock_report_params', []);
        $titles = session('stock_report_titles', []);
        
        if (empty($params)) {
            return redirect()->route('reports.stock')
                ->with('error', 'Silakan generate laporan terlebih dahulu sebelum mengekspor');
        }
        
        $filename = 'Laporan_Stok_' . date('Ymd_His');
        
        if ($format == 'pdf') {
            return $this->exportStockReportPDF($params, $titles, $filename);
        }
        
        return Excel::download(new StockReportExport($params, $titles), $filename . '.xlsx');
    }

    /**
     * Export Stock Report to PDF
     */
    private function exportStockReportPDF($params, $titles, $filename)
    {
        $user = auth()->user();
        $branch_id = $params['branch_id'] ?? null;
        $warehouse_id = $params['warehouse_id'] ?? null;
        $category_id = $params['category_id'] ?? null;
        $stock_status = $params['stock_status'] ?? 'all';
        $search = $params['search'] ?? null;
        
        $query = Stock::with(['product.category', 'warehouse.branch'])
            ->select('stocks.*')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('branches', 'warehouses.branch_id', '=', 'branches.id');
        
        // Filter by branch or accessible branches for non-admin users
        if (!$user->hasRole('super-admin')) {
            $branchIds = $user->branches->pluck('id')->toArray();
            $query->whereIn('branches.id', $branchIds);
        }
        
        // Apply filters
        if ($branch_id) {
            $query->where('branches.id', $branch_id);
        }
        
        if ($warehouse_id) {
            $query->where('warehouses.id', $warehouse_id);
        }
        
        if ($category_id) {
            $query->whereHas('product', function($q) use ($category_id) {
                $q->where('category_id', $category_id);
            });
        }
        
        if ($stock_status != 'all') {
            if ($stock_status == 'in_stock') {
                $query->whereRaw('stocks.quantity > stocks.min_quantity');
            } elseif ($stock_status == 'low_stock') {
                $query->whereRaw('stocks.quantity > 0 AND stocks.quantity <= stocks.min_quantity');
            } elseif ($stock_status == 'out_of_stock') {
                $query->where('stocks.quantity', '<=', 0);
            }
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%");
            });
        }
        
        $stocks = $query->orderBy('warehouses.id')->orderBy('products.name')->get();
        
        // Group results by branch and warehouse
        $groupedStocks = [];
        foreach ($stocks as $stock) {
            $branchId = $stock->warehouse->branch_id;
            $warehouseId = $stock->warehouse_id;
            
            if (!isset($groupedStocks[$branchId])) {
                $groupedStocks[$branchId] = [
                    'name' => $stock->warehouse->branch->name,
                    'warehouses' => []
                ];
            }
            
            if (!isset($groupedStocks[$branchId]['warehouses'][$warehouseId])) {
                $groupedStocks[$branchId]['warehouses'][$warehouseId] = [
                    'name' => $stock->warehouse->name,
                    'stocks' => []
                ];
            }
            
            $groupedStocks[$branchId]['warehouses'][$warehouseId]['stocks'][] = $stock;
        }
        
        $data = [
            'groupedStocks' => $groupedStocks,
            'titles' => $titles,
            'generated_at' => Carbon::now()->format('d/m/Y H:i:s'),
            'user' => $user->name,
        ];
        
        $pdf = PDF::loadView('reports.stock_report_pdf', $data);
        
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Stock Movement Report page
     */
    public function stockMovement()
    {
        $warehouses = WarehouseHelper::getAccessibleWarehouses();
        $branches = WarehouseHelper::getAccessibleBranches();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        
        return view('reports.stock_movement', compact('warehouses', 'branches', 'products'));
    }

    /**
     * Generate Stock Movement Report
     */
    public function generateStockMovementReport(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'product_id' => 'nullable|exists:products,id',
            'movement_type' => 'nullable|in:all,in,out,transfer,adjustment',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $user = auth()->user();
        $branch_id = $request->branch_id;
        $warehouse_id = $request->warehouse_id;
        $product_id = $request->product_id;
        $movement_type = $request->movement_type ?: 'all';
        $start_date = Carbon::parse($request->start_date)->startOfDay();
        $end_date = Carbon::parse($request->end_date)->endOfDay();
        
        $query = StockMovement::with(['product', 'warehouse.branch', 'user'])
            ->whereBetween('created_at', [$start_date, $end_date]);
        
        // Filter by branch or accessible branches for non-admin users
        if (!$user->hasRole('super-admin')) {
            $branchIds = $user->branches->pluck('id')->toArray();
            $query->whereHas('warehouse', function($q) use ($branchIds) {
                $q->whereIn('branch_id', $branchIds);
            });
        }
        
        // Apply filters
        if ($branch_id) {
            $query->whereHas('warehouse', function($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            });
        }
        
        if ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        }
        
        if ($product_id) {
            $query->where('product_id', $product_id);
        }
        
        if ($movement_type != 'all') {
            $query->where('type', $movement_type);
        }
        
        $movements = $query->orderBy('created_at', 'desc')->get();
        
        // Group results by date
        $groupedMovements = $movements->groupBy(function($movement) {
            return $movement->created_at->format('Y-m-d');
        });
        
        // Get selected warehouse, branch, and product names for the report title
        $selectedWarehouse = $warehouse_id ? Warehouse::find($warehouse_id)->name : 'Semua Gudang';
        $selectedBranch = $branch_id ? Branch::find($branch_id)->name : 'Semua Cabang';
        $selectedProduct = $product_id ? Product::find($product_id)->name : 'Semua Produk';
        
        // Store parameters in session for export
        session([
            'movement_report_params' => [
                'branch_id' => $branch_id,
                'warehouse_id' => $warehouse_id,
                'product_id' => $product_id,
                'movement_type' => $movement_type,
                'start_date' => $start_date->format('Y-m-d'),
                'end_date' => $end_date->format('Y-m-d'),
            ],
            'movement_report_titles' => [
                'warehouse' => $selectedWarehouse,
                'branch' => $selectedBranch,
                'product' => $selectedProduct,
                'type' => $this->getMovementTypeLabel($movement_type),
                'period' => $start_date->format('d/m/Y') . ' - ' . $end_date->format('d/m/Y'),
            ]
        ]);
        
        return view('reports.stock_movement_result', compact(
            'groupedMovements', 
            'selectedWarehouse', 
            'selectedBranch', 
            'selectedProduct', 
            'movement_type',
            'start_date',
            'end_date'
        ));
    }

    /**
     * Export Stock Movement Report
     */
    public function exportStockMovementReport(Request $request)
    {
        $format = $request->format ?: 'xlsx';
        $params = session('movement_report_params', []);
        $titles = session('movement_report_titles', []);
        
        if (empty($params)) {
            return redirect()->route('reports.stock-movement')
                ->with('error', 'Silakan generate laporan terlebih dahulu sebelum mengekspor');
        }
        
        $filename = 'Laporan_Pergerakan_Stok_' . date('Ymd_His');
        
        if ($format == 'pdf') {
            return $this->exportStockMovementReportPDF($params, $titles, $filename);
        }
        
        return Excel::download(new StockMovementReportExport($params, $titles), $filename . '.xlsx');
    }

    /**
     * Export Stock Movement Report to PDF
     */
    private function exportStockMovementReportPDF($params, $titles, $filename)
    {
        $user = auth()->user();
        $branch_id = $params['branch_id'] ?? null;
        $warehouse_id = $params['warehouse_id'] ?? null;
        $product_id = $params['product_id'] ?? null;
        $movement_type = $params['movement_type'] ?? 'all';
        $start_date = Carbon::parse($params['start_date'])->startOfDay();
        $end_date = Carbon::parse($params['end_date'])->endOfDay();
        
        $query = StockMovement::with(['product', 'warehouse.branch', 'user'])
            ->whereBetween('created_at', [$start_date, $end_date]);
        
        // Filter by branch or accessible branches for non-admin users
        if (!$user->hasRole('super-admin')) {
            $branchIds = $user->branches->pluck('id')->toArray();
            $query->whereHas('warehouse', function($q) use ($branchIds) {
                $q->whereIn('branch_id', $branchIds);
            });
        }
        
        // Apply filters
        if ($branch_id) {
            $query->whereHas('warehouse', function($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            });
        }
        
        if ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        }
        
        if ($product_id) {
            $query->where('product_id', $product_id);
        }
        
        if ($movement_type != 'all') {
            $query->where('type', $movement_type);
        }
        
        $movements = $query->orderBy('created_at', 'desc')->get();
        
        $data = [
            'movements' => $movements,
            'titles' => $titles,
            'generated_at' => Carbon::now()->format('d/m/Y H:i:s'),
            'user' => $user->name,
        ];
        
        $pdf = PDF::loadView('reports.stock_movement_report_pdf', $data);
        
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Stock Valuation Report page
     */
    public function stockValuation()
    {
        $warehouses = WarehouseHelper::getAccessibleWarehouses();
        $branches = WarehouseHelper::getAccessibleBranches();
        $categories = Category::where('is_active', true)->get();
        
        return view('reports.stock_valuation', compact('warehouses', 'branches', 'categories'));
    }

    /**
     * Generate Stock Valuation Report
     */
    public function generateStockValuationReport(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category_id' => 'nullable|exists:categories,id',
            'min_value' => 'nullable|numeric|min:0',
            'group_by' => 'required|in:warehouse,category,product',
        ]);
        
        $user = auth()->user();
        $branch_id = $request->branch_id;
        $warehouse_id = $request->warehouse_id;
        $category_id = $request->category_id;
        $min_value = $request->min_value;
        $group_by = $request->group_by;
        
        $query = Stock::with(['product.category', 'warehouse.branch'])
            ->select(
                'stocks.id',
                'stocks.warehouse_id',
                'stocks.product_id',
                'stocks.quantity',
                'stocks.min_quantity',
                'products.name as product_name',
                'products.sku',
                'products.cost',
                'products.unit',
                'categories.name as category_name',
                'warehouses.name as warehouse_name',
                'branches.name as branch_name',
                DB::raw('(stocks.quantity * products.cost) as total_value')
            )
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('branches', 'warehouses.branch_id', '=', 'branches.id')
            ->where('stocks.quantity', '>', 0); // Only consider positive quantities
        
        // Filter by branch or accessible branches for non-admin users
        if (!$user->hasRole('super-admin')) {
            $branchIds = $user->branches->pluck('id')->toArray();
            $query->whereIn('branches.id', $branchIds);
        }
        
        // Apply filters
        if ($branch_id) {
            $query->where('branches.id', $branch_id);
        }
        
        if ($warehouse_id) {
            $query->where('warehouses.id', $warehouse_id);
        }
        
        if ($category_id) {
            $query->where('categories.id', $category_id);
        }
        
        if ($min_value) {
            $query->having('total_value', '>=', $min_value);
        }
        
        $stocks = $query->orderBy('total_value', 'desc')->get();
        
        // Group the results based on the selected option
        $groupedResults = [];
        $totalValue = 0;
        
        foreach ($stocks as $stock) {
            $totalValue += $stock->total_value;
            
            if ($group_by == 'warehouse') {
                $key = $stock->warehouse_id;
                $name = $stock->warehouse_name . ' (' . $stock->branch_name . ')';
                
                if (!isset($groupedResults[$key])) {
                    $groupedResults[$key] = [
                        'name' => $name,
                        'items' => [],
                        'total_value' => 0
                    ];
                }
                
                $groupedResults[$key]['items'][] = $stock;
                $groupedResults[$key]['total_value'] += $stock->total_value;
                
            } elseif ($group_by == 'category') {
                $key = $stock->product->category_id;
                $name = $stock->category_name;
                
                if (!isset($groupedResults[$key])) {
                    $groupedResults[$key] = [
                        'name' => $name,
                        'items' => [],
                        'total_value' => 0
                    ];
                }
                
                $groupedResults[$key]['items'][] = $stock;
                $groupedResults[$key]['total_value'] += $stock->total_value;
                
            } else { // group_by == 'product'
                $key = $stock->product_id;
                $name = $stock->product_name . ' (' . $stock->sku . ')';
                
                if (!isset($groupedResults[$key])) {
                    $groupedResults[$key] = [
                        'name' => $name,
                        'items' => [],
                        'total_value' => 0
                    ];
                }
                
                $groupedResults[$key]['items'][] = $stock;
                $groupedResults[$key]['total_value'] += $stock->total_value;
            }
        }
        
        // Sort groups by total value
        usort($groupedResults, function($a, $b) {
            return $b['total_value'] <=> $a['total_value'];
        });
        
        // Get selected warehouse, branch, and category names for the report title
        $selectedWarehouse = $warehouse_id ? Warehouse::find($warehouse_id)->name : 'Semua Gudang';
        $selectedBranch = $branch_id ? Branch::find($branch_id)->name : 'Semua Cabang';
        $selectedCategory = $category_id ? Category::find($category_id)->name : 'Semua Kategori';
        
        // Store parameters in session for export
        session([
            'valuation_report_params' => [
                'branch_id' => $branch_id,
                'warehouse_id' => $warehouse_id,
                'category_id' => $category_id,
                'min_value' => $min_value,
                'group_by' => $group_by,
            ],
            'valuation_report_titles' => [
                'warehouse' => $selectedWarehouse,
                'branch' => $selectedBranch,
                'category' => $selectedCategory,
                'min_value' => $min_value ? 'Min. Nilai: Rp ' . number_format($min_value, 0, ',', '.') : 'Semua Nilai',
                'group_by' => $this->getGroupByLabel($group_by),
            ]
        ]);
        
        return view('reports.stock_valuation_result', compact(
            'groupedResults', 
            'totalValue',
            'selectedWarehouse', 
            'selectedBranch', 
            'selectedCategory',
            'group_by'
        ));
    }

    /**
     * Export Stock Valuation Report
     */
    public function exportStockValuationReport(Request $request)
    {
        $format = $request->format ?: 'xlsx';
        $params = session('valuation_report_params', []);
        $titles = session('valuation_report_titles', []);
        
        if (empty($params)) {
            return redirect()->route('reports.stock-valuation')
                ->with('error', 'Silakan generate laporan terlebih dahulu sebelum mengekspor');
        }
        
        $filename = 'Laporan_Valuasi_Stok_' . date('Ymd_His');
        
        if ($format == 'pdf') {
            return $this->exportStockValuationReportPDF($params, $titles, $filename);
        }
        
        return Excel::download(new StockValuationReportExport($params, $titles), $filename . '.xlsx');
    }

    /**
     * Export Stock Valuation Report to PDF
     */
    private function exportStockValuationReportPDF($params, $titles, $filename)
    {
        $user = auth()->user();
        $branch_id = $params['branch_id'] ?? null;
        $warehouse_id = $params['warehouse_id'] ?? null;
        $category_id = $params['category_id'] ?? null;
        $min_value = $params['min_value'] ?? null;
        $group_by = $params['group_by'] ?? 'warehouse';
        
        $query = Stock::with(['product.category', 'warehouse.branch'])
            ->select(
                'stocks.id',
                'stocks.warehouse_id',
                'stocks.product_id',
                'stocks.quantity',
                'stocks.min_quantity',
                'products.name as product_name',
                'products.sku',
                'products.cost',
                'products.unit',
                'categories.name as category_name',
                'warehouses.name as warehouse_name',
                'branches.name as branch_name',
                DB::raw('(stocks.quantity * products.cost) as total_value')
            )
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('branches', 'warehouses.branch_id', '=', 'branches.id')
            ->where('stocks.quantity', '>', 0);
        
        // Filter by branch or accessible branches for non-admin users
        if (!$user->hasRole('super-admin')) {
            $branchIds = $user->branches->pluck('id')->toArray();
            $query->whereIn('branches.id', $branchIds);
        }
        
        // Apply filters
        if ($branch_id) {
            $query->where('branches.id', $branch_id);
        }
        
        if ($warehouse_id) {
            $query->where('warehouses.id', $warehouse_id);
        }
        
        if ($category_id) {
            $query->where('categories.id', $category_id);
        }
        
        if ($min_value) {
            $query->having('total_value', '>=', $min_value);
        }
        
        $stocks = $query->orderBy('total_value', 'desc')->get();
        
        // Group the results based on the selected option
        $groupedResults = [];
        $totalValue = 0;
        
        foreach ($stocks as $stock) {
            $totalValue += $stock->total_value;
            
            if ($group_by == 'warehouse') {
                $key = $stock->warehouse_id;
                $name = $stock->warehouse_name . ' (' . $stock->branch_name . ')';
                
                if (!isset($groupedResults[$key])) {
                    $groupedResults[$key] = [
                        'name' => $name,
                        'items' => [],
                        'total_value' => 0
                    ];
                }
                
                $groupedResults[$key]['items'][] = $stock;
                $groupedResults[$key]['total_value'] += $stock->total_value;
                
            } elseif ($group_by == 'category') {
                $key = $stock->product->category_id;
                $name = $stock->category_name;
                
                if (!isset($groupedResults[$key])) {
                    $groupedResults[$key] = [
                        'name' => $name,
                        'items' => [],
                        'total_value' => 0
                    ];
                }
                
                $groupedResults[$key]['items'][] = $stock;
                $groupedResults[$key]['total_value'] += $stock->total_value;
                
            } else { // group_by == 'product'
                $key = $stock->product_id;
                $name = $stock->product_name . ' (' . $stock->sku . ')';
                
                if (!isset($groupedResults[$key])) {
                    $groupedResults[$key] = [
                        'name' => $name,
                        'items' => [],
                        'total_value' => 0
                    ];
                }
                
                $groupedResults[$key]['items'][] = $stock;
                $groupedResults[$key]['total_value'] += $stock->total_value;
            }
        }
        
        // Sort groups by total value
        usort($groupedResults, function($a, $b) {
            return $b['total_value'] <=> $a['total_value'];
        });
        
        $data = [
            'groupedResults' => $groupedResults,
            'totalValue' => $totalValue,
            'group_by' => $group_by,
            'titles' => $titles,
            'generated_at' => Carbon::now()->format('d/m/Y H:i:s'),
            'user' => $user->name,
        ];
        
        $pdf = PDF::loadView('reports.stock_valuation_report_pdf', $data);
        
        return $pdf->download($filename . '.pdf');
    }
    
    /**
     * Helper to get status label
     */
    private function getStatusLabel($status)
    {
        switch ($status) {
            case 'in_stock':
                return 'Stok Tersedia';
            case 'low_stock':
                return 'Stok Menipis';
            case 'out_of_stock':
                return 'Stok Habis';
            default:
                return 'Semua Status';
        }
    }
    
    /**
     * Helper to get movement type label
     */
    private function getMovementTypeLabel($type)
    {
        switch ($type) {
            case 'in':
                return 'Stok Masuk';
            case 'out':
                return 'Stok Keluar';
            case 'transfer':
                return 'Transfer Stok';
            case 'adjustment':
                return 'Penyesuaian Stok';
            default:
                return 'Semua Tipe';
        }
    }
    
    /**
     * Helper to get group by label
     */
    private function getGroupByLabel($groupBy)
    {
        switch ($groupBy) {
            case 'warehouse':
                return 'Gudang';
            case 'category':
                return 'Kategori';
            case 'product':
                return 'Produk';
            default:
                return 'Gudang';
        }
    }
}