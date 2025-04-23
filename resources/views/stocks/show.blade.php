{{-- resources/views/stocks/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Stok')

@section('actions')
    @can('adjust stocks')
        <a href="{{ route('stocks.adjust', $stock->id) }}" class="btn btn-sm btn-warning">
            <i class="bi bi-pencil-fill"></i> Penyesuaian
        </a>
    @endcan

    @can('transfer stocks')
        <a href="{{ route('stocks.transfer', $stock->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-arrow-left-right"></i> Transfer
        </a>
    @endcan

    <a href="{{ route('stocks.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Stok</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">ID</th>
                            <td>{{ $stock->id }}</td>
                        </tr>
                        <tr>
                            <th>Produk</th>
                            <td>
                                @can('view products')
                                    <a href="{{ route('products.show', $stock->product_id) }}">
                                        {{ $stock->product->name }}
                                    </a>
                                @else
                                    {{ $stock->product->name }}
                                @endcan
                            </td>
                        </tr>
                        <tr>
                            <th>SKU</th>
                            <td>{{ $stock->product->sku }}</td>
                        </tr>
                        <tr>
                            <th>Gudang</th>
                            <td>
                                @can('view warehouses')
                                    <a href="{{ route('warehouses.show', $stock->warehouse_id) }}">
                                        {{ $stock->warehouse->name }}
                                    </a>
                                @else
                                    {{ $stock->warehouse->name }}
                                @endcan
                            </td>
                        </tr>
                        <tr>
                            <th>Cabang</th>
                            <td>{{ $stock->warehouse->branch->name }}</td>
                        </tr>
                        <tr>
                            <th>Kuantitas</th>
                            <td>{{ number_format($stock->quantity, 2) }} {{ $stock->product->unit }}</td>
                        </tr>
                        <tr>
                            <th>Min. Stok</th>
                            <td>{{ number_format($stock->min_quantity, 2) }} {{ $stock->product->unit }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if ($stock->quantity <= 0)
                                    <span class="badge bg-danger">Stok Habis</span>
                                @elseif($stock->quantity <= $stock->min_quantity)
                                    <span class="badge bg-warning">Stok Hampir Habis</span>
                                @else
                                    <span class="badge bg-success">Stok Tersedia</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Harga Modal</th>
                            <td>Rp {{ number_format($stock->product->cost, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Nilai Stok</th>
                            <td>Rp {{ number_format($stock->quantity * $stock->product->cost, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Riwayat Pergerakan Stok</h5>
                </div>
                <div class="card-body">
                    @if ($movements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Tipe</th>
                                        <th>Kuantitas</th>
                                        <th>Setelah</th>
                                        <th>Pengguna</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movements as $movement)
                                        <tr>
                                            <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if ($movement->type == 'in')
                                                    <span class="badge bg-success">Masuk</span>
                                                @elseif($movement->type == 'out')
                                                    <span class="badge bg-danger">Keluar</span>
                                                @elseif($movement->type == 'transfer')
                                                    @if ($movement->quantity > 0)
                                                        <span class="badge bg-info">Transfer Masuk</span>
                                                    @else
                                                        <span class="badge bg-primary">Transfer Keluar</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-warning">Penyesuaian</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($movement->quantity, 2) }}</td>
                                            <td>{{ number_format($movement->current_quantity, 2) }}</td>
                                            <td>{{ $movement->user->name }}</td>
                                            <td>{{ $movement->notes }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{ $movements->links() }}
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> Belum ada riwayat pergerakan stok.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
