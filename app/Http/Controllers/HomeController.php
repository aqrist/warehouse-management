<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Helpers\WarehouseHelper;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return $this->dashboard();
    }

    /**
     * Show the application dashboard with statistics.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function dashboard()
    {
        $user = Auth::user();

        // For super admin, show all data
        if ($user->hasRole('super-admin')) {
            $branchCount = Branch::count();
            $warehouseCount = Warehouse::count();
            $productCount = Product::count();

            // Get low stock products (where quantity is below min_quantity)
            $lowStocks = Stock::whereRaw('quantity <= min_quantity')
                ->with(['product', 'warehouse', 'warehouse.branch'])
                ->limit(10)
                ->get();

            $lowStockCount = Stock::whereRaw('quantity <= min_quantity')->count();

            // Get recent stock movements
            $recentMovements = StockMovement::with(['product', 'warehouse', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } else {
            // Get the branches that the user has access to
            $branchIds = $user->branches->pluck('id')->toArray();

            $branchCount = count($branchIds);

            // Get warehouses in those branches
            $warehouseIds = Warehouse::whereIn('branch_id', $branchIds)->pluck('id')->toArray();
            $warehouseCount = count($warehouseIds);

            // Get products count in those warehouses
            $productIds = Stock::whereIn('warehouse_id', $warehouseIds)
                ->distinct('product_id')
                ->pluck('product_id')
                ->toArray();
            $productCount = count($productIds);

            // Get low stock products in those warehouses
            $lowStocks = Stock::whereIn('warehouse_id', $warehouseIds)
                ->whereRaw('quantity <= min_quantity')
                ->with(['product', 'warehouse', 'warehouse.branch'])
                ->limit(10)
                ->get();

            $lowStockCount = Stock::whereIn('warehouse_id', $warehouseIds)
                ->whereRaw('quantity <= min_quantity')
                ->count();

            // Get recent stock movements in those warehouses
            $recentMovements = StockMovement::whereIn('warehouse_id', $warehouseIds)
                ->with(['product', 'warehouse', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('home', compact(
            'branchCount',
            'warehouseCount',
            'productCount',
            'lowStocks',
            'lowStockCount',
            'recentMovements'
        ));
    }

    /**
     * Show the user profile form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function profile()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Show the change password form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function changePassword()
    {
        return view('profile.change-password');
    }

    /**
     * Update the user password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('change-password')->with('success', 'Password berhasil diperbarui');
    }

    /**
     * Get accessible branches for the current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccessibleBranches(Request $request)
    {
        $branches = WarehouseHelper::getAccessibleBranches();

        return response()->json([
            'branches' => $branches
        ]);
    }

    /**
     * Get accessible warehouses for the current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccessibleWarehouses(Request $request)
    {
        $branchId = $request->input('branch_id');

        $warehouses = WarehouseHelper::getAccessibleWarehouses();

        if ($branchId) {
            $warehouses = $warehouses->where('branch_id', $branchId);
        }

        return response()->json([
            'warehouses' => $warehouses
        ]);
    }
}
