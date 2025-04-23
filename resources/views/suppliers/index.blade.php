{{-- resources/views/suppliers/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Supplier')

@section('actions')
    @can('create suppliers')
        <a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Supplier
        </a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="suppliers-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Kode</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Contact Person</th>
                            {{-- <th>Jumlah Produk</th> --}}
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
            $('#suppliers-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('suppliers.data') }}',
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
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'contact_person',
                        name: 'contact_person'
                    },
                    // {
                    //     data: 'product_count',
                    //     name: 'product_count',
                    //     searchable: false,
                    //     orderable: false
                    // },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function(data) {
                            if (data === 'Aktif') {
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
