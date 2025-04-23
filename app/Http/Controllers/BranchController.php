<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view branches', ['only' => ['index', 'show', 'getData']]);
        $this->middleware('permission:create branches', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit branches', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete branches', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('branches.index');
    }

    public function getData(Request $request)
    {
        $branches = Branch::query();

        return DataTables::of($branches)
            ->addColumn('action', function ($branch) {
                $actions = '';

                if (auth()->user()->can('view branches')) {
                    $actions .= '<a href="' . route('branches.show', $branch->id) . '" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a> ';
                }

                if (auth()->user()->can('edit branches')) {
                    $actions .= '<a href="' . route('branches.edit', $branch->id) . '" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a> ';
                }

                if (auth()->user()->can('delete branches')) {
                    $actions .= '<form action="' . route('branches.destroy', $branch->id) . '" method="POST" style="display:inline">
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
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Branch::create($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil ditambahkan');
    }

    public function show(Branch $branch)
    {
        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $branch->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $branch->update($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil diperbarui');
    }

    public function destroy(Branch $branch)
    {
        try {
            $branch->delete();
            return redirect()->route('branches.index')
                ->with('success', 'Cabang berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('branches.index')
                ->with('error', 'Cabang tidak dapat dihapus karena masih digunakan');
        }
    }
}
