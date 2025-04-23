<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\StockAdjustmentController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::middleware(['permission:view users'])->group(function () {
        Route::get('/users/data', [UserController::class, 'getData'])->name('users.data');
        Route::resource('users', UserController::class);
    });

    // Role Management
    Route::middleware(['permission:view roles'])->group(function () {
        Route::get('/roles/data', [RoleController::class, 'getData'])->name('roles.data');
        Route::resource('roles', RoleController::class);
    });

    // Branch Management
    Route::middleware(['permission:view branches'])->group(function () {
        Route::get('/branches/data', [BranchController::class, 'getData'])->name('branches.data');
        Route::resource('branches', BranchController::class);
    });

    // Warehouse Management
    Route::middleware(['permission:view warehouses'])->group(function () {
        Route::get('/warehouses/data', [WarehouseController::class, 'getData'])->name('warehouses.data');
        Route::resource('warehouses', WarehouseController::class);
    });

    // Category Management
    Route::middleware(['permission:view categories'])->group(function () {
        Route::get('/categories/data', [CategoryController::class, 'getData'])->name('categories.data');
        Route::resource('categories', CategoryController::class);
    });

    // Supplier Management
    Route::middleware(['permission:view suppliers'])->group(function () {
        Route::get('/suppliers/data', [SupplierController::class, 'getData'])->name('suppliers.data');
        Route::resource('suppliers', SupplierController::class);
    });

    // Product Management
    Route::middleware(['permission:view products'])->group(function () {
        Route::get('/products/count-by-category/{categoryId}', [ProductController::class, 'countByCategory'])
            ->name('products.count-by-category');
        Route::get('/products/data', [ProductController::class, 'getData'])->name('products.data');
        Route::resource('products', ProductController::class);
    });

    // Stock Management
    Route::middleware(['permission:view stocks'])->group(function () {
        Route::get('/stocks/data', [StockController::class, 'getData'])->name('stocks.data');
        Route::get('/stocks/{stock}', [StockController::class, 'show'])->name('stocks.show');
        Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');

        Route::middleware(['permission:adjust stocks'])->group(function () {
            Route::get('/stocks/{stock}/adjust', [StockController::class, 'adjust'])->name('stocks.adjust');
            Route::post('/stocks/{stock}/adjust', [StockController::class, 'saveAdjustment'])->name('stocks.save-adjustment');
        });

        Route::middleware(['permission:transfer stocks'])->group(function () {
            Route::get('/stocks/{stock}/transfer', [StockController::class, 'transfer'])->name('stocks.transfer');
            Route::post('/stocks/{stock}/transfer', [StockController::class, 'saveTransfer'])->name('stocks.save-transfer');
        });
    });

    // Stock Adjustment
    Route::middleware(['permission:adjust stocks'])->group(function () {
        Route::get('/stock-adjustments/data', [StockAdjustmentController::class, 'getData'])->name('stock-adjustments.data');
        Route::resource('stock-adjustments', StockAdjustmentController::class);
    });

    // Reports
    Route::middleware(['permission:view reports'])->group(function () {
        Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('/reports/stock-movement', [ReportController::class, 'stockMovement'])->name('reports.stock-movement');
        Route::get('/reports/stock-valuation', [ReportController::class, 'stockValuation'])->name('reports.stock-valuation');

        // Generate reports
        Route::post('/reports/generate-stock', [ReportController::class, 'generateStockReport'])->name('reports.generate-stock');
        Route::post('/reports/generate-stock-movement', [ReportController::class, 'generateStockMovementReport'])->name('reports.generate-stock-movement');
        Route::post('/reports/generate-stock-valuation', [ReportController::class, 'generateStockValuationReport'])->name('reports.generate-stock-valuation');

        // Export reports
        Route::get('/reports/export-stock', [ReportController::class, 'exportStockReport'])->name('reports.export-stock');
        Route::get('/reports/export-stock-movement', [ReportController::class, 'exportStockMovementReport'])->name('reports.export-stock-movement');
        Route::get('/reports/export-stock-valuation', [ReportController::class, 'exportStockValuationReport'])->name('reports.export-stock-valuation');
    });

    // Profile & Settings
    Route::get('/profile', [HomeController::class, 'profile'])->name('profile');
    Route::post('/profile', [HomeController::class, 'updateProfile'])->name('profile.update');
    Route::get('/change-password', [HomeController::class, 'changePassword'])->name('change-password');
    Route::post('/change-password', [HomeController::class, 'updatePassword'])->name('change-password.update');
});
