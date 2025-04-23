{{-- resources/views/categories/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Kategori')

@section('actions')
    @can('create categories')
        <a href="{{ route('categories.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Kategori
        </a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="categories-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Kode</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Produk</th>
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
            $('#categories-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('categories.data') }}',
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
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'product_count',
                        name: 'product_count',
                        searchable: false,
                        orderable: false
                    },
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
