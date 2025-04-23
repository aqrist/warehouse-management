<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view categories', ['only' => ['index', 'show', 'getData']]);
        $this->middleware('permission:create categories', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit categories', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete categories', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('categories.index');
    }

    public function getData(Request $request)
    {
        $categories = Category::query();

        return DataTables::of($categories)
            ->addColumn('product_count', function ($category) {
                return $category->products->count();
            })
            ->addColumn('action', function ($category) {
                $actions = '';

                if (auth()->user()->can('view categories')) {
                    $actions .= '<a href="' . route('categories.show', $category->id) . '" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a> ';
                }

                if (auth()->user()->can('edit categories')) {
                    $actions .= '<a href="' . route('categories.edit', $category->id) . '" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a> ';
                }

                if (auth()->user()->can('delete categories')) {
                    $actions .= '<form action="' . route('categories.destroy', $category->id) . '" method="POST" style="display:inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')"><i class="bi bi-trash"></i></button>
                    </form>';
                }

                return $actions;
            })
            ->editColumn('is_active', function ($category) {
                return $category->is_active ? 'Aktif' : 'Tidak Aktif';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:categories',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;

        Category::create($data);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    public function show(Category $category)
    {
        $category->load('products');
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:categories,code,' . $category->id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return redirect()->route('categories.index')
                ->with('success', 'Kategori berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan dalam produk');
        }
    }
}
