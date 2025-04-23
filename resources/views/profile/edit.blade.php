{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Pengguna</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Akun</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Peran</th>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge bg-primary">{{ $role->name }}</span>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>Cabang</th>
                            <td>
                                @foreach ($user->branches as $branch)
                                    <span class="badge bg-secondary">{{ $branch->name }}</span>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>Email Terverifikasi</th>
                            <td>{{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'Belum Terverifikasi' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Bergabung Sejak</th>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <a href="{{ route('change-password') }}" class="btn btn-secondary">
                            <i class="bi bi-key"></i> Ubah Password
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hak Akses</h5>
                </div>
                <div class="card-body">
                    @if ($user->roles->isNotEmpty() && $user->roles->first()->permissions->isNotEmpty())
                        <div class="row">
                            @php
                                $permissions = $user->roles->first()->permissions;
                                $groupedPermissions = $permissions->groupBy(function ($permission) {
                                    $parts = explode(' ', $permission->name);
                                    return isset($parts[1]) ? $parts[1] : 'other';
                                });
                            @endphp

                            @foreach ($groupedPermissions as $group => $permissions)
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
                            <i class="bi bi-info-circle me-2"></i> Anda belum memiliki hak akses apa pun.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
