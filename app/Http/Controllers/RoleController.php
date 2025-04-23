<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view roles', ['only' => ['index', 'show', 'getData']]);
        $this->middleware('permission:create roles', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit roles', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete roles', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('roles.index');
    }

    public function getData(Request $request)
    {
        $roles = Role::with('permissions');

        return DataTables::of($roles)
            ->addColumn('permissions_count', function ($role) {
                return $role->permissions->count();
            })
            ->addColumn('action', function ($role) {
                $actions = '';
                
                if (auth()->user()->can('view roles')) {
                    $actions .= '<a href="' . route('roles.show', $role->id) . '" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a> ';
                }
                
                if (auth()->user()->can('edit roles')) {
                    $actions .= '<a href="' . route('roles.edit', $role->id) . '" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a> ';
                }
                
                if (auth()->user()->can('delete roles') && $role->name !== 'super-admin') {
                    $actions .= '<form action="' . route('roles.destroy', $role->id) . '" method="POST" style="display:inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Apakah Anda yakin ingin menghapus peran ini?\')"><i class="bi bi-trash"></i></button>
                    </form>';
                }
                
                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $permissions = Permission::all();
        $permissionGroups = $this->groupPermissions($permissions);
        
        return view('roles.create', compact('permissionGroups'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')
            ->with('success', 'Peran berhasil ditambahkan');
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        $permissionGroups = $this->groupPermissions($role->permissions);
        
        return view('roles.show', compact('role', 'permissionGroups'));
    }

    public function edit(Role $role)
    {
        if ($role->name === 'super-admin') {
            return redirect()->route('roles.index')
                ->with('error', 'Peran Super Admin tidak dapat diubah');
        }
        
        $role->load('permissions');
        $permissions = Permission::all();
        $permissionGroups = $this->groupPermissions($permissions);
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return view('roles.edit', compact('role', 'permissionGroups', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'super-admin') {
            return redirect()->route('roles.index')
                ->with('error', 'Peran Super Admin tidak dapat diubah');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')
            ->with('success', 'Peran berhasil diperbarui');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return redirect()->route('roles.index')
                ->with('error', 'Peran Super Admin tidak dapat dihapus');
        }

        try {
            $role->delete();
            return redirect()->route('roles.index')
                ->with('success', 'Peran berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('roles.index')
                ->with('error', 'Peran tidak dapat dihapus karena masih digunakan');
        }
    }
    
    // Helper untuk mengelompokkan permission berdasarkan nama
    private function groupPermissions($permissions)
    {
        $groups = [];
        
        foreach ($permissions as $permission) {
            $parts = explode(' ', $permission->name);
            $action = $parts[0]; // view, create, edit, delete
            $resource = implode(' ', array_slice($parts, 1)); // users, roles, etc.
            
            if (!isset($groups[$resource])) {
                $groups[$resource] = [];
            }
            
            $groups[$resource][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'action' => $action
            ];
        }
        
        return $groups;
    }
}

