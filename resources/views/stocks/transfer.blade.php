{{-- resources/views/stocks/transfer.blade.php --}}
@extends('layouts.app')

@section('title', 'Transfer Stok')

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Stok Sumber</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Produk</th>
                            <td>{{ $stock->product->name }}</td>
                        </tr>
                        <tr>
                            <th>SKU</th>
                            <td>{{ $stock->product->sku }}</td>
                        </tr>
                        <tr>
                            <th>Gudang Sumber</th>
                            <td>{{ $stock->warehouse->name }}</td>
                        </tr>
                        <tr>
                            <th>Cabang</th>
                            <td>{{ $stock->warehouse->branch->name }}</td>
                        </tr>
                        <tr>
                            <th>Stok Tersedia</th>
                            <td>{{ number_format($stock->quantity, 2) }} {{ $stock->product->unit }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Transfer</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('stocks.save-transfer', $stock->id) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="target_warehouse_id" class="form-label">Gudang Tujuan <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select select2 @error('target_warehouse_id') is-invalid @enderror"
                                        id="target_warehouse_id" name="target_warehouse_id" required>
                                        <option value="">-- Pilih Gudang Tujuan --</option>
                                        @foreach ($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}"
                                                {{ old('target_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }} - {{ $warehouse->branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('target_warehouse_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="quantity" class="form-label">Jumlah Transfer <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0.01" max="{{ $stock->quantity }}"
                                            class="form-control @error('quantity') is-invalid @enderror" id="quantity"
                                            name="quantity" value="{{ old('quantity') }}" required>
                                        <span class="input-group-text">{{ $stock->product->unit }}</span>
                                    </div>
                                    <small class="form-text text-muted">Maksimal {{ number_format($stock->quantity, 2) }}
                                        {{ $stock->product->unit }}</small>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes" class="form-label">Keterangan <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                                        required>{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('stocks.show', $stock->id) }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Proses Transfer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pratinjau Hasil</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Gudang Sumber:</h6>
                            <p class="mb-1">Stok Saat Ini:</p>
                            <h5>{{ number_format($stock->quantity, 2) }} {{ $stock->product->unit }}</h5>
                            <p class="mb-1 mt-3">Stok Setelah Transfer:</p>
                            <h5 id="source-after">{{ number_format($stock->quantity, 2) }} {{ $stock->product->unit }}
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <h6>Gudang Tujuan:</h6>
                            <p class="mb-1">Stok Sebelum Transfer:</p>
                            <h5 id="target-before">- {{ $stock->product->unit }}</h5>
                            <p class="mb-1 mt-3">Stok Setelah Transfer:</p>
                            <h5 id="target-after">- {{ $stock->product->unit }}</h5>
                        </div>
                    </div>
                </div>
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

            const currentStock = {{ $stock->quantity }};
            const unit = '{{ $stock->product->unit }}';

            function updatePreview() {
                const transferQuantity = parseFloat($('#quantity').val()) || 0;
                const targetWarehouseId = $('#target_warehouse_id').val();

                if (transferQuantity > 0) {
                    // Calculate source warehouse stock after transfer
                    const sourceAfter = currentStock - transferQuantity;
                    $('#source-after').text(sourceAfter.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' ' + unit);

                    // If target warehouse is selected, fetch current stock
                    if (targetWarehouseId) {
                        $.ajax({
                            url: '/api/check-stock',
                            type: 'GET',
                            data: {
                                warehouse_id: targetWarehouseId,
                                product_id: {{ $stock->product_id }}
                            },
                            success: function(data) {
                                const targetBefore = data.stock || 0;
                                const targetAfter = targetBefore + transferQuantity;

                                $('#target-before').text(targetBefore.toLocaleString('id-ID', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) + ' ' + unit);

                                $('#target-after').text(targetAfter.toLocaleString('id-ID', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) + ' ' + unit);
                            }
                        });
                    } else {
                        $('#target-before').text('- ' + unit);
                        $('#target-after').text('- ' + unit);
                    }
                } else {
                    $('#source-after').text(currentStock.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' ' + unit);
                    $('#target-before').text('- ' + unit);
                    $('#target-after').text('- ' + unit);
                }
            }

            // Update preview on input changes
            $('#target_warehouse_id, #quantity').on('change input', function() {
                updatePreview();
            });

            // Initial update
            updatePreview();
        });
    </script>
@endpush
