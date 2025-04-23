{{-- resources/views/users/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('actions')
    @can('edit users')
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
    @endcan

    <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Pengguna</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">ID</th>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Peran</th>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge bg-primary">{{ $role->name }}</span>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>Email Terverifikasi</th>
                            <td>{{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'Belum Terverifikasi' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diperbarui Pada</th>
                            <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Cabang yang Dapat Diakses</h5>
                </div>
                <div class="card-body">
                    @if ($user->branches->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Cabang</th>
                                        <th>Kode</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($user->branches as $branch)
                                        <tr>
                                            <td>{{ $branch->name }}</td>
                                            <td>{{ $branch->code }}</td>
                                            <td>
                                                @if ($branch->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger">Tidak Aktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> Pengguna ini belum memiliki akses ke cabang mana pun.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hak Akses</h5>
                </div>
                <div class="card-body">
                    @if ($user->roles->isNotEmpty() && $user->roles->first()->permissions->isNotEmpty())
                        <div class="row">
                            @foreach ($user->roles->first()->permissions->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return isset($parts[1]) ? $parts[1] : 'other';
        }) as $group => $permissions)
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted text-uppercase">{{ ucfirst($group) }}</h6>
                                    <ul class="list-unstyled">
                                        @foreach ($permissions as $permission)
                                            <li><i class="bi bi-check-circle text-success me-2"></i>
                                                {{ ucfirst($permission->name) }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> Pengguna ini belum memiliki hak akses apa pun.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
