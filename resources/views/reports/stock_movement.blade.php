{{-- resources/views/reports/stock_movement.blade.php --}}
@extends('layouts.app')

@section('title', 'Laporan Pergerakan Stok')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Filter Laporan Pergerakan Stok</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.generate-stock-movement') }}" method="POST">
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
                            <label for="product_id" class="form-label">Produk</label>
                            <select class="form-select select2" id="product_id" name="product_id">
                                <option value="">Semua Produk</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="movement_type" class="form-label">Tipe Pergerakan</label>
                            <select class="form-select" id="movement_type" name="movement_type">
                                <option value="all">Semua Tipe</option>
                                <option value="in">Stok Masuk</option>
                                <option value="out">Stok Keluar</option>
                                <option value="transfer">Transfer Stok</option>
                                <option value="adjustment">Penyesuaian Stok</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date" class="form-label">Tanggal Mulai <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                id="start_date" name="start_date"
                                value="{{ old('start_date', now()->subDays(30)->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date" class="form-label">Tanggal Akhir <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                id="end_date" name="end_date" value="{{ old('end_date', now()->format('Y-m-d')) }}"
                                required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

