{{-- resources/views/reports/stock.blade.php --}}
@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Filter Laporan Stok</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.generate-stock') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="branch_id" class="form-label">Cabang</label>
                            <select class="form-select select2" id="branch_id" name="branch_id">
                                <option value="">Semua Cabang</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="warehouse_id" class="form-label">Gudang</label>
                            <select class="form-select select2" id="warehouse_id" name="warehouse_id">
                                <option value="">Semua Gudang</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}
                                        ({{ $warehouse->branch->name }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-select select2" id="category_id" name="category_id">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="stock_status" class="form-label">Status Stok</label>
                            <select class="form-select" id="stock_status" name="stock_status">
                                <option value="all">Semua Status</option>
                                <option value="in_stock">Stok Tersedia</option>
                                <option value="low_stock">Stok Menipis</option>
                                <option value="out_of_stock">Stok Habis</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="search" class="form-label">Cari Produk</label>
                            <input type="text" class="form-control" id="search" name="search"
                                placeholder="Nama atau SKU produk">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-text"></i> Generate Laporan
                    </button>
                </div>
            </form>
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

            // Handle branch selection to filter warehouses
            $('#branch_id').on('change', function() {
                var branchId = $(this).val();
                var warehouseSelect = $('#warehouse_id');

                // Reset warehouse selection
                warehouseSelect.val('').trigger('change');

                if (!branchId) {
                    // Show all warehouses
                    warehouseSelect.find('option').show();
                } else {
                    // Hide warehouses not in selected branch
                    warehouseSelect.find('option').each(function() {
                        var option = $(this);
                        var optionText = option.text();
                        var branchMatch = optionText.indexOf('(' + $('#branch_id option:selected')
                            .text() + ')') !== -1;

                        if (option.val() === '' || branchMatch) {
                            option.show();
                        } else {
                            option.hide();
                        }
                    });
                }
            });
        });
    </script>
@endpush
