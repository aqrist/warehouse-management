{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Produk')

@section('actions')
    @can('create products')
        <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Produk
        </a>
    @endcan
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filter</h5>
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="category_id" class="form-label">Kategori</label>
                        <select class="form-select select2" id="category_id" name="category_id">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select class="form-select select2" id="supplier_id" name="supplier_id">
                            <option value="">Semua Supplier</option>
                            @foreach ($suppliers as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end mb-3">
                        <button type="button" id="btn-filter" class="btn btn-primary me-2">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                        <button type="button" id="btn-reset" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="products-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>SKU</th>
                            <th>Kategori</th>
                            <th>Supplier</th>
                            <th>Harga Modal</th>
                            <th>Harga Jual</th>
                            <th>Unit</th>
                            <th>Stok Total</th>
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
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            var table = $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('products.data') }}',
                    data: function(d) {
                        d.category_id = $('#category_id').val();
                        d.supplier_id = $('#supplier_id').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'sku',
                        name: 'sku'
                    },
                    {
                        data: 'category_name',
                        name: 'category.name'
                    },
                    {
                        data: 'suppliers_list',
                        name: 'suppliers_list'
                    },
                    {
                        data: 'cost',
                        name: 'cost'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'unit',
                        name: 'unit'
                    },
                    {
                        data: 'stock_total',
                        name: 'stock_total'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active'
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

            // Filter button click
            $('#btn-filter').click(function() {
                table.draw();
            });

            // Reset button click
            $('#btn-reset').click(function() {
                $('#category_id').val('').trigger('change');
                $('#supplier_id').val('').trigger('change');
                table.draw();
            });
        });
    </script>
@endpush
