<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view suppliers', ['only' => ['index', 'show', 'getData']]);
        $this->middleware('permission:create suppliers', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit suppliers', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete suppliers', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('suppliers.index');
    }

    public function getData(Request $request)
    {
        $suppliers = Supplier::query();

        return DataTables::of($suppliers)
            ->addColumn('product_count', function ($supplier) {
                return $supplier->products->count();
            })
            ->addColumn('action', function ($supplier) {
                $actions = '';

                if (auth()->user()->can('view suppliers')) {
                    $actions .= '<a href="' . route('suppliers.show', $supplier->id) . '" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a> ';
                }

                if (auth()->user()->can('edit suppliers')) {
                    $actions .= '<a href="' . route('suppliers.edit', $supplier->id) . '" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a> ';
                }

                if (auth()->user()->can('delete suppliers')) {
                    $actions .= '<form action="' . route('suppliers.destroy', $supplier->id) . '" method="POST" style="display:inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')"><i class="bi bi-trash"></i></button>
                    </form>';
                }

                return $actions;
            })
            ->editColumn('is_active', function ($supplier) {
                return $supplier->is_active ? 'Aktif' : 'Tidak Aktif';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:suppliers',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;

        Supplier::create($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('products');
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:suppliers,code,' . $supplier->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;

        $supplier->update($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui');
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Supplier tidak dapat dihapus karena masih digunakan dalam produk');
        }
    }
}
