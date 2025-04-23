<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Stock;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view products', ['only' => ['index', 'show', 'getData']]);
        $this->middleware('permission:create products', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit products', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete products', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)->pluck('name', 'id');
        $suppliers = Supplier::where('is_active', true)->pluck('name', 'id');

        return view('products.index', compact('categories', 'suppliers'));
    }

    public function getData(Request $request)
    {
        $products = Product::with(['category', 'suppliers']);

        // Filter by category if provided
        if ($request->has('category_id') && $request->category_id) {
            $products->where('category_id', $request->category_id);
        }

        // Filter by supplier if provided
        if ($request->has('supplier_id') && $request->supplier_id) {
            $products->whereHas('suppliers', function ($q) use ($request) {
                $q->where('suppliers.id', $request->supplier_id);
            });
        }

        return DataTables::of($products)
            ->addColumn('category_name', function ($product) {
                return $product->category ? $product->category->name : '-';
            })
            ->addColumn('suppliers_list', function ($product) {
                return $product->suppliers->pluck('name')->implode(', ');
            })
            ->addColumn('stock_total', function ($product) {
                return $product->stocks->sum('quantity');
            })
            ->addColumn('action', function ($product) {
                $actions = '';

                if (auth()->user()->can('view products')) {
                    $actions .= '<a href="' . route('products.show', $product->id) . '" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a> ';
                }

                if (auth()->user()->can('edit products')) {
                    $actions .= '<a href="' . route('products.edit', $product->id) . '" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a> ';
                }

                if (auth()->user()->can('delete products')) {
                    $actions .= '<form action="' . route('products.destroy', $product->id) . '" method="POST" style="display:inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')"><i class="bi bi-trash"></i></button>
                    </form>';
                }

                return $actions;
            })
            ->editColumn('price', function ($product) {
                return number_format($product->price, 0, ',', '.');
            })
            ->editColumn('cost', function ($product) {
                return number_format($product->cost, 0, ',', '.');
            })
            ->editColumn('is_active', function ($product) {
                return $product->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>';
            })
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->pluck('name', 'id');
        $suppliers = Supplier::where('is_active', true)->pluck('name', 'id');
        $units = ['pcs' => 'Pieces', 'box' => 'Box', 'unit' => 'Unit', 'kg' => 'Kilogram', 'lt' => 'Liter', 'pack' => 'Pack'];

        return view('products.create', compact('categories', 'suppliers', 'units'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products',
            'barcode' => 'nullable|string|max:50|unique:products',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'suppliers' => 'required|array',
            'suppliers.*' => 'exists:suppliers,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $data['image'] = $imagePath;
        }

        $product = Product::create($data);

        // Sync suppliers
        $product->suppliers()->sync($request->suppliers);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'suppliers', 'stocks.warehouse.branch']);

        // Calculate total stock across all warehouses
        $totalStock = $product->stocks->sum('quantity');

        // Get stock movements for this product
        $stockMovements = $product->stockMovements()
            ->with(['warehouse.branch', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('products.show', compact('product', 'totalStock', 'stockMovements'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->pluck('name', 'id');
        $suppliers = Supplier::where('is_active', true)->pluck('name', 'id');
        $units = ['pcs' => 'Pieces', 'box' => 'Box', 'unit' => 'Unit', 'kg' => 'Kilogram', 'lt' => 'Liter', 'pack' => 'Pack'];

        $selectedSuppliers = $product->suppliers->pluck('id')->toArray();

        return view('products.edit', compact('product', 'categories', 'suppliers', 'units', 'selectedSuppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'suppliers' => 'required|array',
            'suppliers.*' => 'exists:suppliers,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $imagePath = $request->file('image')->store('products', 'public');
            $data['image'] = $imagePath;
        }

        $product->update($data);

        // Sync suppliers
        $product->suppliers()->sync($request->suppliers);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy(Product $product)
    {
        try {
            // Delete image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('products.index')
                ->with('error', 'Produk tidak dapat dihapus karena masih digunakan dalam transaksi');
        }
    }

    public function countByCategory($categoryId)
    {
        $count = Product::where('category_id', $categoryId)->count();

        return response()->json([
            'count' => $count
        ]);
    }
}
