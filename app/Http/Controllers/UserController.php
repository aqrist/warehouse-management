<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view users', ['only' => ['index', 'show', 'getData']]);
        $this->middleware('permission:create users', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit users', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete users', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('users.index');
    }

    public function getData(Request $request)
    {
        $users = User::with('roles');

        return DataTables::of($users)
            ->addColumn('roles', function ($user) {
                return $user->roles->pluck('name')->implode(', ');
            })
            ->addColumn('branches', function ($user) {
                return $user->branches->pluck('name')->implode(', ');
            })
            ->addColumn('action', function ($user) {
                $actions = '';

                if (auth()->user()->can('view users')) {
                    $actions .= '<a href="' . route('users.show', $user->id) . '" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a> ';
                }

                if (auth()->user()->can('edit users')) {
                    $actions .= '<a href="' . route('users.edit', $user->id) . '" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a> ';
                }

                if (auth()->user()->can('delete users') && auth()->id() !== $user->id) {
                    $actions .= '<form action="' . route('users.destroy', $user->id) . '" method="POST" style="display:inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Apakah Anda yakin ingin menghapus pengguna ini?\')"><i class="bi bi-trash"></i></button>
                    </form>';
                }

                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $roles = Role::pluck('name', 'id');
        $branches = Branch::where('is_active', true)->pluck('name', 'id');

        return view('users.create', compact('roles', 'branches'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'branches' => 'required|array',
            'branches.*' => 'exists:branches,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);
        $user->branches()->attach($request->branches);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan');
    }

    public function show(User $user)
    {
        $user->load('roles', 'branches');

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'id');
        $branches = Branch::where('is_active', true)->pluck('name', 'id');
        $userBranches = $user->branches->pluck('id')->toArray();

        return view('users.edit', compact('user', 'roles', 'branches', 'userBranches'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
            'branches' => 'required|array',
            'branches.*' => 'exists:branches,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        // Sync roles and branches
        $user->syncRoles([$request->role]);
        $user->branches()->sync($request->branches);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        if (Auth::user()->id === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus diri Anda sendiri');
        }

        try {
            $user->delete();
            return redirect()->route('users.index')
                ->with('success', 'Pengguna berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Pengguna tidak dapat dihapus: ' . $e->getMessage());
        }
    }
}
