<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view warehouses', ['only' => ['index', 'show', 'getData']]);
        $this->middleware('permission:create warehouses', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit warehouses', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete warehouses', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('warehouses.index');
    }

    public function getData(Request $request)
    {
        $user = auth()->user();
        $warehouses = Warehouse::with('branch');
        
        // Filter based on user's assigned branches unless they're super-admin
        if (!$user->hasRole('super-admin')) {
            $branchIds = $user->branches->pluck('id')->toArray();
            $warehouses->whereIn('branch_id', $branchIds);
        }

        return DataTables::of($warehouses)
            ->addColumn('branch_name', function ($warehouse) {
                return $warehouse->branch->name;
            })
            ->addColumn('action', function ($warehouse) {
                $actions = '';
                
                if (auth()->user()->can('view warehouses')) {
                    $actions .= '<a href="' . route('warehouses.show', $warehouse->id) . '" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a> ';
                }
                
                if (auth()->user()->can('edit warehouses')) {
                    $actions .= '<a href="' . route('warehouses.edit', $warehouse->id) . '" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a> ';
                }
                
                if (auth()->user()->can('delete warehouses')) {
                    $actions .= '<form action="' . route('warehouses.destroy', $warehouse->id) . '" method="POST" style="display:inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')"><i class="bi bi-trash"></i></button>
                    </form>';
                }
                
                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $user = auth()->user();
        
        // If super-admin, show all branches, otherwise only show assigned branches
        if ($user->hasRole('super-admin')) {
            $branches = Branch::where('is_active', true)->pluck('name', 'id');
        } else {
            $branches = $user->branches()->where('is_active', true)->pluck('name', 'id');
        }
        
        return view('warehouses.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Warehouse::create($request->all());

        return redirect()->route('warehouses.index')
            ->with('success', 'Gudang berhasil ditambahkan');
    }

    public function show(Warehouse $warehouse)
    {
        // Check if user has access to this warehouse's branch
        $this->checkWarehouseAccess($warehouse);
        
        return view('warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        // Check if user has access to this warehouse's branch
        $this->checkWarehouseAccess($warehouse);
        
        $user = auth()->user();
        
        // If super-admin, show all branches, otherwise only show assigned branches
        if ($user->hasRole('super-admin')) {
            $branches = Branch::where('is_active', true)->pluck('name', 'id');
        } else {
            $branches = $user->branches()->where('is_active', true)->pluck('name', 'id');
        }
        
        return view('warehouses.edit', compact('warehouse', 'branches'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        // Check if user has access to this warehouse's branch
        $this->checkWarehouseAccess($warehouse);
        
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $warehouse->update($request->all());

        return redirect()->route('warehouses.index')
            ->with('success', 'Gudang berhasil diperbarui');
    }

    public function destroy(Warehouse $warehouse)
    {
        // Check if user has access to this warehouse's branch
        $this->checkWarehouseAccess($warehouse);
        
        try {
            $warehouse->delete();
            return redirect()->route('warehouses.index')
                ->with('success', 'Gudang berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('warehouses.index')
                ->with('error', 'Gudang tidak dapat dihapus karena masih digunakan');
        }
    }
    
    private function checkWarehouseAccess(Warehouse $warehouse)
    {
        $user = auth()->user();
        
        // Super admin has access to all warehouses
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        // Check if user has access to the warehouse's branch
        $hasAccess = $user->branches()->where('branches.id', $warehouse->branch_id)->exists();
        
        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke gudang ini');
        }
        
        return true;
    }
}