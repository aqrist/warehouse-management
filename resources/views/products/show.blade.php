{{-- resources/views/products/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Produk')

@section('actions')
    @can('edit products')
        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
    @endcan

    <a href="{{ route('products.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Produk</h5>
                </div>
                <div class="card-body">
                    @if ($product->image)
                        <div class="text-center mb-3">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    @endif

                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">ID</th>
                            <td>{{ $product->id }}</td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td>{{ $product->name }}</td>
                        </tr>
                        <tr>
                            <th>SKU</th>
                            <td>{{ $product->sku }}</td>
                        </tr>
                        <tr>
                            <th>Barcode</th>
                            <td>{{ $product->barcode ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td>
                                @if ($product->category)
                                    <a href="{{ route('categories.show', $product->category_id) }}">
                                        {{ $product->category->name }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Harga Modal</th>
                            <td>Rp {{ number_format($product->cost, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Harga Jual</th>
                            <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Margin</th>
                            <td>
                                @php
                                    $margin =
                                        $product->price > 0
                                            ? (($product->price - $product->cost) / $product->price) * 100
                                            : 0;
                                @endphp
                                {{ number_format($margin, 2) }}%
                            </td>
                        </tr>
                        <tr>
                            <th>Satuan</th>
                            <td>{{ $product->unit }}</td>
                        </tr>
                        <tr>
                            <th>Stok Total</th>
                            <td>{{ number_format($totalStock, 2) }} {{ $product->unit }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if ($product->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>{{ $product->description ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diperbarui Pada</th>
                            <td>{{ $product->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Supplier</h5>
                </div>
                <div class="card-body">
                    @if ($product->suppliers->count() > 0)
                        <div class="list-group">
                            @foreach ($product->suppliers as $supplier)
                                <a href="{{ route('suppliers.show', $supplier->id) }}"
                                    class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $supplier->name }}</h6>
                                        <small>{{ $supplier->code }}</small>
                                    </div>
                                    <small>{{ $supplier->phone ?: '-' }}</small>
                                    <small class="d-block">{{ $supplier->email ?: '-' }}</small>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> Belum ada supplier untuk produk ini.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aktivitas Stok Terbaru</h5>
                </div>
                <div class="card-body">
                    @if ($stockMovements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Gudang</th>
                                        <th>Tipe</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stockMovements as $movement)
                                        <tr>
                                            <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $movement->warehouse->name }}</td>
                                            <td>
                                                @if ($movement->type == 'in')
                                                    <span class="badge bg-success">Masuk</span>
                                                @elseif($movement->type == 'out')
                                                    <span class="badge bg-danger">Keluar</span>
                                                @elseif($movement->type == 'transfer')
                                                    <span class="badge bg-primary">Transfer</span>
                                                @else
                                                    <span class="badge bg-warning">Penyesuaian</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($movement->quantity, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @can('view stocks')
                            <div class="mt-2 text-end">
                                <a href="{{ route('stocks.index', ['product_id' => $product->id]) }}"
                                    class="btn btn-sm btn-outline-primary">Lihat Semua Pergerakan</a>
                            </div>
                        @endcan
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> Belum ada aktivitas stok untuk produk ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Stok di Gudang</h5>
                    @can('adjust stocks')
                        <a href="{{ route('stock-adjustments.create', ['product_id' => $product->id]) }}"
                            class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Penyesuaian Stok
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if ($product->stocks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Gudang</th>
                                        <th>Cabang</th>
                                        <th>Stok</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($product->stocks as $stock)
                                        <tr>
                                            <td>{{ $stock->warehouse->name }}</td>
                                            <td>{{ $stock->warehouse->branch->name }}</td>
                                            <td>{{ number_format($stock->quantity, 2) }}</td>
                                            <td>
                                                @if ($stock->quantity <= 0)
                                                    <span class="badge bg-danger">Habis</span>
                                                @elseif($stock->quantity <= $stock->min_quantity)
                                                    <span class="badge bg-warning">Menipis</span>
                                                @else
                                                    <span class="badge bg-success">Tersedia</span>
                                                @endif
                                            </td>
                                            <td>
                                                @can('view stocks')
                                                    <a href="{{ route('stocks.show', $stock->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endcan

                                                @can('adjust stocks')
                                                    <a href="{{ route('stocks.adjust', $stock->id) }}"
                                                        class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i> Belum ada stok untuk produk ini.
                        </div>

                        @can('adjust stocks')
                            <div class="mt-3">
                                <a href="{{ route('stock-adjustments.create', ['product_id' => $product->id]) }}"
                                    class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Tambah Stok Awal
                                </a>
                            </div>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
