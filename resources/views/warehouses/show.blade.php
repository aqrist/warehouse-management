{{-- resources/views/warehouses/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Gudang')

@section('actions')
    @can('edit warehouses')
        <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
    @endcan

    <a href="{{ route('warehouses.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Gudang</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">ID</th>
                            <td>{{ $warehouse->id }}</td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td>{{ $warehouse->name }}</td>
                        </tr>
                        <tr>
                            <th>Kode</th>
                            <td>{{ $warehouse->code }}</td>
                        </tr>
                        <tr>
                            <th>Cabang</th>
                            <td>
                                @can('view branches')
                                    <a href="{{ route('branches.show', $warehouse->branch_id) }}">
                                        {{ $warehouse->branch->name }} ({{ $warehouse->branch->code }})
                                    </a>
                                @else
                                    {{ $warehouse->branch->name }} ({{ $warehouse->branch->code }})
                                @endcan
                            </td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $warehouse->address ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>{{ $warehouse->phone ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Manager</th>
                            <td>{{ $warehouse->manager_name ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if ($warehouse->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $warehouse->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diperbarui Pada</th>
                            <td>{{ $warehouse->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Ringkasan Stok</h5>
                    @can('view stocks')
                        <a href="{{ route('stocks.index', ['warehouse_id' => $warehouse->id]) }}" class="btn btn-sm btn-info">
                            <i class="bi bi-box-seam"></i> Lihat Semua Stok
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @php
                        $stockSummary = App\Models\Stock::where('warehouse_id', $warehouse->id)
                            ->selectRaw('COUNT(*) as total_products')
                            ->selectRaw('SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock')
                            ->selectRaw(
                                'SUM(CASE WHEN quantity > 0 AND quantity <= min_quantity THEN 1 ELSE 0 END) as low_stock',
                            )
                            ->selectRaw('SUM(CASE WHEN quantity > min_quantity THEN 1 ELSE 0 END) as in_stock')
                            ->first();
                    @endphp

                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body py-3">
                                    <h3 class="mb-0">{{ $stockSummary->total_products ?? 0 }}</h3>
                                    <small class="text-muted">Total Produk</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-success bg-opacity-10">
                                <div class="card-body py-3">
                                    <h3 class="mb-0 text-success">{{ $stockSummary->in_stock ?? 0 }}</h3>
                                    <small class="text-muted">Stok Tersedia</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-warning bg-opacity-10">
                                <div class="card-body py-3">
                                    <h3 class="mb-0 text-warning">{{ $stockSummary->low_stock ?? 0 }}</h3>
                                    <small class="text-muted">Stok Menipis</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-danger bg-opacity-10">
                                <div class="card-body py-3">
                                    <h3 class="mb-0 text-danger">{{ $stockSummary->out_of_stock ?? 0 }}</h3>
                                    <small class="text-muted">Stok Habis</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @can('view stocks')
                        <div class="mt-3">
                            <h6>Produk dengan Stok Menipis</h6>
                            @php
                                $lowStocks = App\Models\Stock::where('warehouse_id', $warehouse->id)
                                    ->whereRaw('quantity <= min_quantity AND quantity > 0')
                                    ->with('product')
                                    ->limit(5)
                                    ->get();
                            @endphp

                            @if ($lowStocks->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>SKU</th>
                                                <th>Stok</th>
                                                <th>Min. Stok</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lowStocks as $stock)
                                                <tr>
                                                    <td>{{ $stock->product->name }}</td>
                                                    <td>{{ $stock->product->sku }}</td>
                                                    <td>{{ number_format($stock->quantity, 2) }}</td>
                                                    <td>{{ number_format($stock->min_quantity, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-2"></i> Tidak ada produk dengan stok menipis.
                                </div>
                            @endif
                        </div>
                    @endcan
                </div>
            </div>

            @can('view stocks')
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $recentMovements = App\Models\StockMovement::where('warehouse_id', $warehouse->id)
                                ->with(['product', 'user'])
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp

                        @if ($recentMovements->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Produk</th>
                                            <th>Tipe</th>
                                            <th>Jumlah</th>
                                            <th>Pengguna</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentMovements as $movement)
                                            <tr>
                                                <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $movement->product->name }}</td>
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
                                                <td>{{ $movement->user->name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> Belum ada aktivitas stok di gudang ini.
                            </div>
                        @endif
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection
