{{-- resources/views/stocks/adjust.blade.php --}}
@extends('layouts.app')

@section('title', 'Penyesuaian Stok')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Stok</h5>
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
                            <th>Gudang</th>
                            <td>{{ $stock->warehouse->name }}</td>
                        </tr>
                        <tr>
                            <th>Cabang</th>
                            <td>{{ $stock->warehouse->branch->name }}</td>
                        </tr>
                        <tr>
                            <th>Stok Saat Ini</th>
                            <td>{{ number_format($stock->quantity, 2) }} {{ $stock->product->unit }}</td>
                        </tr>
                        <tr>
                            <th>Min. Stok</th>
                            <td>{{ number_format($stock->min_quantity, 2) }} {{ $stock->product->unit }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Penyesuaian</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('stocks.save-adjustment', $stock->id) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="type" class="form-label">Tipe Penyesuaian <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type"
                                        name="type" required>
                                        <option value="addition" {{ old('type') == 'addition' ? 'selected' : '' }}>
                                            Penambahan</option>
                                        <option value="subtraction" {{ old('type') == 'subtraction' ? 'selected' : '' }}>
                                            Pengurangan</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="quantity" class="form-label">Jumlah <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0.01"
                                            class="form-control @error('quantity') is-invalid @enderror" id="quantity"
                                            name="quantity" value="{{ old('quantity') }}" required>
                                        <span class="input-group-text">{{ $stock->product->unit }}</span>
                                    </div>
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
                                <button type="submit" class="btn btn-primary">Simpan Penyesuaian</button>
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
                            <p class="mb-1">Stok Saat Ini:</p>
                            <h4 id="current-stock">{{ number_format($stock->quantity, 2) }} {{ $stock->product->unit }}
                            </h4>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">Stok Setelah Penyesuaian:</p>
                            <h4 id="new-stock">{{ number_format($stock->quantity, 2) }} {{ $stock->product->unit }}</h4>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3 d-none" id="stock-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="warning-message"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const currentStock = {{ $stock->quantity }};
            const minStock = {{ $stock->min_quantity }};
            const unit = '{{ $stock->product->unit }}';

            function updatePreview() {
                const adjustmentType = $('#type').val();
                const adjustmentQuantity = parseFloat($('#quantity').val()) || 0;
                let newStock = currentStock;

                if (adjustmentType === 'addition') {
                    newStock = currentStock + adjustmentQuantity;
                } else {
                    newStock = currentStock - adjustmentQuantity;
                }

                // Format for display
                const formattedNewStock = newStock.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                $('#new-stock').text(formattedNewStock + ' ' + unit);

                // Show warning if needed
                if (newStock < 0) {
                    $('#stock-warning').removeClass('d-none').addClass('alert-danger');
                    $('#warning-message').text('Penyesuaian ini akan menyebabkan stok menjadi negatif!');
                } else if (newStock <= minStock) {
                    $('#stock-warning').removeClass('d-none').removeClass('alert-danger').addClass('alert-warning');
                    $('#warning-message').text('Stok akan berada di bawah minimal stok yang direkomendasikan.');
                } else {
                    $('#stock-warning').addClass('d-none');
                }
            }

            // Update preview on input changes
            $('#type, #quantity').on('change input', function() {
                updatePreview();
            });

            // Initial update
            updatePreview();
        });
    </script>
@endpush
