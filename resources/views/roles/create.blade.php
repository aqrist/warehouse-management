{{-- resources/views/roles/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Tambah Peran Baru')

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="form-label">Nama Peran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" required>
                            <small class="form-text text-muted">Gunakan nama yang deskriptif (contoh: manager-gudang,
                                staff-admin)</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Hak Akses <span class="text-danger">*</span></label>

                        @error('permissions')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <div class="row">
                            @foreach ($permissionGroups as $resource => $permissions)
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <div class="form-check">
                                                <input class="form-check-input group-checkbox" type="checkbox"
                                                    id="group_{{ $resource }}" data-resource="{{ $resource }}">
                                                <label class="form-check-label fw-bold" for="group_{{ $resource }}">
                                                    {{ ucfirst($resource) }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @foreach ($permissions as $permission)
                                                <div class="form-check">
                                                    <input class="form-check-input permission-checkbox" type="checkbox"
                                                        name="permissions[]" id="permission_{{ $permission['id'] }}"
                                                        value="{{ $permission['name'] }}"
                                                        data-resource="{{ $resource }}"
                                                        {{ in_array($permission['name'], old('permissions', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="permission_{{ $permission['id'] }}">
                                                        {{ ucfirst($permission['action']) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Handle group checkboxes
            $('.group-checkbox').change(function() {
                var resource = $(this).data('resource');
                var isChecked = $(this).prop('checked');

                // Check/uncheck all permission checkboxes in this group
                $('.permission-checkbox[data-resource="' + resource + '"]').prop('checked', isChecked);
            });

            // Handle individual permission checkboxes
            $('.permission-checkbox').change(function() {
                var resource = $(this).data('resource');
                var allChecked = true;

                // Check if all permissions in this group are checked
                $('.permission-checkbox[data-resource="' + resource + '"]').each(function() {
                    if (!$(this).prop('checked')) {
                        allChecked = false;
                        return false;
                    }
                });

                // Update group checkbox
                $('#group_' + resource).prop('checked', allChecked);
            });

            // Initialize group checkboxes based on selected permissions
            $('.group-checkbox').each(function() {
                var resource = $(this).data('resource');
                var allChecked = true;

                // Check if all permissions in this group are checked
                $('.permission-checkbox[data-resource="' + resource + '"]').each(function() {
                    if (!$(this).prop('checked')) {
                        allChecked = false;
                        return false;
                    }
                });

                // Update group checkbox
                $(this).prop('checked', allChecked);
            });
        });
    </script>
@endpush
