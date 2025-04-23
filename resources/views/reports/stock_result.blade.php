{{-- resources/views/reports/stock_result.blade.php --}}
@extends('layouts.app')

@section('title', 'Hasil Laporan Stok')

@section('actions')
    <div class="dropdown">
        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="bi bi-download"></i> Export
        </button>
        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
            <li><a class="dropdown-item" href="{{ route('reports.export-stock', ['format' => 'xlsx']) }}">Export ke
                    Excel</a></li>
            <li><a class="dropdown-item" href="{{ route('reports.export-stock', ['format' => 'pdf']) }}">Export ke PDF</a>
            </li>
        </ul>
    </div>

    <a href="{{ route('reports.stock') }}" class="btn btn-sm btn-secondary ms-2">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Laporan Stok</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <p class="mb-1"><strong>Cabang:</strong> {{ $selectedBranch }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Gudang:</strong> {{ $selectedWarehouse }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Kategori:</strong> {{ $selectedCategory }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-1"><strong>Status Stok:</strong>
                        @if ($stock_status == 'in_stock')
                            Stok Tersedia
                        @elseif($stock_status == 'low_stock')
                            Stok Menipis
                        @elseif($stock_status == 'out_of_stock')
                            Stok Habis
                        @else
                            Semua Status
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Tanggal Laporan:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Dibuat Oleh:</strong> {{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </div>

    @if (count($groupedStocks) > 0)
        @foreach ($groupedStocks as $branchId => $branch)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Cabang: {{ $branch['name'] }}</h5>
                </div>

                @foreach ($branch['warehouses'] as $warehouseId => $warehouse)
                    <div class="card-body border-bottom pb-4">
                        <h6 class="mb-3">Gudang: {{ $warehouse['name'] }}</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>SKU</th>
                                        <th>Kategori</th>
                                        <th>Stok</th>
                                        <th>Min. Stok</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($warehouse['stocks'] as $stock)
                                        <tr>
                                            <td>{{ $stock->product->name }}</td>
                                            <td>{{ $stock->product->sku }}</td>
                                            <td>{{ $stock->product->category->name }}</td>
                                            <td>{{ number_format($stock->quantity, 2) }} {{ $stock->product->unit }}</td>
                                            <td>{{ number_format($stock->min_quantity, 2) }} {{ $stock->product->unit }}
                                            </td>
                                            <td>
                                                @if ($stock->quantity <= 0)
                                                    <span class="badge bg-danger">Habis</span>
                                                @elseif($stock->quantity <= $stock->min_quantity)
                                                    <span class="badge bg-warning">Hampir Habis</span>
                                                @else
                                                    <span class="badge bg-success">Tersedia</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i> Tidak ada data stok yang sesuai dengan filter yang dipilih.
        </div>
    @endif
@endsection
