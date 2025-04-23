@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Cabang</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $branchCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-diagram-3 fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Gudang</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $warehouseCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Produk</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $productCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-box-seam fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Stok Menipis</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lowStockCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Stock Alert -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Peringatan Stok Menipis</h6>
                        <a href="{{ route('stocks.index') }}" class="btn btn-sm btn-primary">
                            Lihat Semua
                        </a>
                    </div>
                    <div class="card-body">
                        @if (count($lowStocks) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Gudang</th>
                                            <th>Cabang</th>
                                            <th>Stok</th>
                                            <th>Min. Stok</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lowStocks as $stock)
                                            <tr>
                                                <td>{{ $stock->product->name }}</td>
                                                <td>{{ $stock->warehouse->name }}</td>
                                                <td>{{ $stock->warehouse->branch->name }}</td>
                                                <td>{{ number_format($stock->quantity, 2) }}</td>
                                                <td>{{ number_format($stock->min_quantity, 2) }}</td>
                                                <td>
                                                    @if ($stock->quantity <= 0)
                                                        <span class="badge bg-danger">Habis</span>
                                                    @else
                                                        <span class="badge bg-warning">Hampir Habis</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill me-2"></i> Tidak ada stok yang hampir habis.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Stock Movements -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Pergerakan Stok Terbaru</h6>
                    </div>
                    <div class="card-body">
                        @if (count($recentMovements) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Produk</th>
                                            <th>Gudang</th>
                                            <th>Tipe</th>
                                            <th>Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentMovements as $movement)
                                            <tr>
                                                <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $movement->product->name }}</td>
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
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle-fill me-2"></i> Belum ada pergerakan stok.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
