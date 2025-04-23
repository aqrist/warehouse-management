{{-- resources/views/branches/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Cabang')

@section('actions')
    @can('edit branches')
        <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
    @endcan

    <a href="{{ route('branches.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Cabang</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">ID</th>
                            <td>{{ $branch->id }}</td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td>{{ $branch->name }}</td>
                        </tr>
                        <tr>
                            <th>Kode</th>
                            <td>{{ $branch->code }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $branch->address ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>{{ $branch->phone ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $branch->email ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if ($branch->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $branch->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diperbarui Pada</th>
                            <td>{{ $branch->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Gudang di Cabang Ini</h5>
                    @can('create warehouses')
                        <a href="{{ route('warehouses.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah Gudang
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if ($branch->warehouses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Kode</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($branch->warehouses as $warehouse)
                                        <tr>
                                            <td>{{ $warehouse->name }}</td>
                                            <td>{{ $warehouse->code }}</td>
                                            <td>
                                                @if ($warehouse->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger">Tidak Aktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                @can('view warehouses')
                                                    <a href="{{ route('warehouses.show', $warehouse->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> Belum ada gudang di cabang ini.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pengguna yang Memiliki Akses ke Cabang Ini</h5>
                </div>
                <div class="card-body">
                    @if ($branch->users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Peran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($branch->users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @foreach ($user->roles as $role)
                                                    <span class="badge bg-info">{{ $role->name }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> Belum ada pengguna yang memiliki akses ke cabang ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
