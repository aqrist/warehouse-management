{{-- resources/views/stock-adjustments/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Buat Penyesuaian Stok')

@section('content')
    <form action="{{ route('stock-adjustments.store') }}" method="POST" id="adjustment-form">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Penyesuaian</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="warehouse_id" class="form-label">Gudang <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('warehouse_id') is-invalid @enderror"
                                id="warehouse_id" name="warehouse_id" required>
                                <option value="">-- Pilih Gudang --</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ old('warehouse_id', $selectedWarehouseId) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} ({{ $warehouse->branch->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" id="date"
                                name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                                required>{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Cari Produk</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="product_search" class="form-label">Cari Produk</label>
                            <select class="form-select select2" id="product_search" disabled>
                                <option value="">-- Pilih Gudang Terlebih Dahulu --</option>
                            </select>
                        </div>

                        <div id="product-info" class="d-none">
                            <div class="border rounded p-3 mb-3">
                                <h6 id="selected-product-name">Nama Produk</h6>
                                <p class="mb-1">SKU: <span id="selected-product-sku"></span></p>
                                <p class="mb-1">Kategori: <span id="selected-product-category"></span></p>
                                <p class="mb-1">Stok Saat Ini: <span id="selected-product-stock"></span></p>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="add-product-type" class="form-label">Tipe</label>
                                            <select class="form-select" id="add-product-type">
                                                <option value="addition">Penambahan</option>
                                                <option value="subtraction">Pengurangan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="add-product-quantity" class="form-label">Jumlah</label>
                                            <input type="number" step="0.01" min="0.01" class="form-control"
                                                id="add-product-quantity">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-2">
                                    <label for="add-product-notes" class="form-label">Catatan</label>
                                    <input type="text" class="form-control" id="add-product-notes">
                                </div>

                                <div class="d-grid mt-3">
                                    <button type="button" class="btn btn-primary" id="btn-add-product">
                                        <i class="bi bi-plus-circle"></i> Tambahkan ke Daftar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Daftar Produk</h5>
                        <span class="badge bg-primary" id="items-count">0</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="items-table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>SKU</th>
                                        <th>Stok Saat Ini</th>
                                        <th>Tipe</th>
                                        <th>Jumlah</th>
                                        <th>Catatan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($selectedProduct)
                                        <tr data-id="{{ $selectedProduct->id }}">
                                            <td>{{ $selectedProduct->name }}
                                                <input type="hidden" name="product_ids[]"
                                                    value="{{ $selectedProduct->id }}">
                                            </td>
                                            <td>{{ $selectedProduct->sku }}</td>
                                            <td class="current-stock">0</td>
                                            <td>
                                                <select class="form-select" name="types[]">
                                                    <option value="addition" selected>Penambahan</option>
                                                    <option value="subtraction">Pengurangan</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0.01" class="form-control"
                                                    name="quantities[]" value="1">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="item_notes[]"
                                                    placeholder="Catatan">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger btn-remove-item">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot class="table-secondary" id="items-empty"
                                    style="{{ $selectedProduct ? 'display: none;' : '' }}">
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada produk ditambahkan</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @error('product_ids')
                            <div class="alert alert-danger mt-3">{{ $message }}</div>
                        @enderror

                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('stock-adjustments.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" id="btn-save">Simpan Penyesuaian</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Handle warehouse change
            $('#warehouse_id').on('change', function() {
                const warehouseId = $(this).val();

                if (warehouseId) {
                    // Enable product search and load products for the selected warehouse
                    $('#product_search').prop('disabled', false);

                    // Clear and reload product search options
                    $('#product_search').empty().append('<option value="">-- Cari Produk --</option>');

                    // Fetch products for the selected warehouse
                    $.ajax({
                        url: '{{ route('stock-adjustments.get-products-by-warehouse') }}',
                        type: 'GET',
                        data: {
                            warehouse_id: warehouseId
                        },
                        success: function(data) {
                            // Load products into dropdown
                            data.products.forEach(function(product) {
                                $('#product_search').append(
                                    `<option value="${product.id}" 
                                    data-name="${product.name}" 
                                    data-sku="${product.sku}" 
                                    data-category="${product.category}" 
                                    data-unit="${product.unit}"
                                    data-stock="${product.current_stock}">
                                    ${product.name} (${product.sku})
                                </option>`
                                );
                            });

                            $('#product_search').trigger('change');

                            // Update current stock for existing items
                            updateExistingItemsStock(data.products);
                        }
                    });
                } else {
                    // Disable product search if no warehouse selected
                    $('#product_search').prop('disabled', true);
                    $('#product_search').empty().append(
                        '<option value="">-- Pilih Gudang Terlebih Dahulu --</option>');
                    $('#product-info').addClass('d-none');
                }
            });

            // Handle product selection
            $('#product_search').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const productId = selectedOption.val();

                if (productId) {
                    // Show product info
                    $('#product-info').removeClass('d-none');

                    // Fill product details
                    $('#selected-product-name').text(selectedOption.data('name'));
                    $('#selected-product-sku').text(selectedOption.data('sku'));
                    $('#selected-product-category').text(selectedOption.data('category'));
                    $('#selected-product-stock').text(
                        `${selectedOption.data('stock')} ${selectedOption.data('unit')}`
                    );

                    // Reset quantity input
                    $('#add-product-quantity').val('1');
                } else {
                    // Hide product info
                    $('#product-info').addClass('d-none');
                }
            });

            // Add product to list
            $('#btn-add-product').on('click', function() {
                const selectedOption = $('#product_search').find('option:selected');
                const productId = selectedOption.val();

                if (!productId) {
                    alert('Silakan pilih produk terlebih dahulu');
                    return;
                }

                const quantity = parseFloat($('#add-product-quantity').val());
                if (!quantity || quantity <= 0) {
                    alert('Jumlah harus lebih dari 0');
                    return;
                }

                // Check if product already in list
                const existingRow = $(`#items-table tbody tr[data-id="${productId}"]`);
                if (existingRow.length > 0) {
                    alert('Produk ini sudah ada dalam daftar. Silakan edit atau hapus yang ada.');
                    return;
                }

                // Add product to list
                const type = $('#add-product-type').val();
                const notes = $('#add-product-notes').val();
                const productName = selectedOption.data('name');
                const productSku = selectedOption.data('sku');
                const currentStock = selectedOption.data('stock');

                const newRow = `
                <tr data-id="${productId}">
                    <td>${productName}
                        <input type="hidden" name="product_ids[]" value="${productId}">
                    </td>
                    <td>${productSku}</td>
                    <td class="current-stock">${currentStock}</td>
                    <td>
                       <select class="form-select" name="types[]">
                            <option value="addition" ${type === 'addition' ? 'selected' : ''}>Penambahan</option>
                            <option value="subtraction" ${type === 'subtraction' ? 'selected' : ''}>Pengurangan</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0.01" class="form-control" name="quantities[]" value="${quantity}">
                    </td>
                    <td>
                        <input type="text" class="form-control" name="item_notes[]" value="${notes}" placeholder="Catatan">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btn-remove-item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

                $('#items-table tbody').append(newRow);
                $('#items-empty').hide();
                updateItemsCount();

                // Reset product selection
                $('#product_search').val('').trigger('change');
                $('#product-info').addClass('d-none');
            });

            // Remove item from list
            $(document).on('click', '.btn-remove-item', function() {
                $(this).closest('tr').remove();
                if ($('#items-table tbody tr').length === 0) {
                    $('#items-empty').show();
                }
                updateItemsCount();
            });

            // Update items count badge
            function updateItemsCount() {
                const count = $('#items-table tbody tr').length;
                $('#items-count').text(count);
            }

            // Update existing items' stock when warehouse changes
            function updateExistingItemsStock(products) {
                if (!products || products.length === 0) return;

                const productsMap = {};
                products.forEach(product => {
                    productsMap[product.id] = product;
                });

                $('#items-table tbody tr').each(function() {
                    const productId = $(this).data('id');
                    if (productsMap[productId]) {
                        $(this).find('.current-stock').text(productsMap[productId].current_stock);
                    }
                });
            }

            // Form validation before submit
            $('#adjustment-form').on('submit', function(e) {
                if ($('#items-table tbody tr').length === 0) {
                    e.preventDefault();
                    alert('Silakan tambahkan minimal satu produk untuk penyesuaian stok');
                    return false;
                }

                return true;
            });

            // Initialize by triggering warehouse change if one is already selected
            if ($('#warehouse_id').val()) {
                $('#warehouse_id').trigger('change');
            }
        });
    </script>
@endpush
