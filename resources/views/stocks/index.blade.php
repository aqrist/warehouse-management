{{-- resources/views/stocks/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Stok')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filter</h5>
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="warehouse_id" class="form-label">Gudang</label>
                        <select class="form-select select2" id="warehouse_id" name="warehouse_id">
                            <option value="">Semua Gudang</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->branch->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="product_name" class="form-label">Produk</label>
                        <input type="text" class="form-control" id="product_name" name="product_name"
                            placeholder="Nama atau SKU produk">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">Status Stok</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="in_stock">Tersedia</option>
                            <option value="low_stock">Hampir Habis</option>
                            <option value="out_of_stock">Habis</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end mb-3">
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
                <table class="table table-bordered table-hover" id="stocks-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Produk</th>
                            <th>SKU</th>
                            <th>Gudang</th>
                            <th>Cabang</th>
                            <th>Stok</th>
                            <th>Min. Stok</th>
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

            var table = $('#stocks-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('stocks.data') }}',
                    data: function(d) {
                        d.warehouse_id = $('#warehouse_id').val();
                        d.product_name = $('#product_name').val();
                        d.status = $('#status').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'product_name',
                        name: 'product.name'
                    },
                    {
                        data: 'product_sku',
                        name: 'product.sku'
                    },
                    {
                        data: 'warehouse_name',
                        name: 'warehouse.name'
                    },
                    {
                        data: 'branch_name',
                        name: 'warehouse.branch.name'
                    },
                    {
                        data: 'quantity',
                        name: 'quantity'
                    },
                    {
                        data: 'min_quantity',
                        name: 'min_quantity'
                    },
                    {
                        data: 'stock_status',
                        name: 'stock_status',
                        searchable: false
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
                $('#warehouse_id').val('').trigger('change');
                $('#product_name').val('');
                $('#status').val('');
                table.draw();
            });
        });
    </script>
@endpush
