{{-- resources/views/warehouses/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Gudang')

@section('actions')
    @can('create warehouses')
        <a href="{{ route('warehouses.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Gudang
        </a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="warehouses-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Kode</th>
                            <th>Cabang</th>
                            <th>Telepon</th>
                            <th>Manager</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#warehouses-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('warehouses.data') }}',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'branch_name',
                        name: 'branch_name'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'manager_name',
                        name: 'manager_name'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function(data) {
                            if (data) {
                                return '<span class="badge bg-success">Aktif</span>';
                            } else {
                                return '<span class="badge bg-danger">Tidak Aktif</span>';
                            }
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'desc']
                ]
            });
        });
    </script>
@endpush
