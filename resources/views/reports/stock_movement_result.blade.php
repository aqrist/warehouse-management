{{-- resources/views/reports/stock_movement_result.blade.php --}}
@extends('layouts.app')

@section('title', 'Hasil Laporan Pergerakan Stok')

@section('actions')
    <div class="dropdown">
        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="bi bi-download"></i> Export
        </button>
        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
            <li><a class="dropdown-item" href="{{ route('reports.export-stock-movement', ['format' => 'xlsx']) }}">Export
                    ke Excel</a></li>
            <li><a class="dropdown-item" href="{{ route('reports.export-stock-movement', ['format' => 'pdf']) }}">Export ke
                    PDF</a></li>
        </ul>
    </div>

    <a href="{{ route('reports.stock-movement') }}" class="btn btn-sm btn-secondary ms-2">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Laporan Pergerakan Stok</h5>
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
                    <p class="mb-1"><strong>Produk:</strong> {{ $selectedProduct }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-1"><strong>Tipe Pergerakan:</strong>
                        @if ($movement_type == 'in')
                            Stok Masuk
                        @elseif($movement_type == 'out')
                            Stok Keluar
                        @elseif($movement_type == 'transfer')
                            Transfer Stok
                        @elseif($movement_type == 'adjustment')
                            Penyesuaian Stok
                        @else
                            Semua Tipe
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Periode:</strong> {{ $start_date->format('d/m/Y') }} -
                        {{ $end_date->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Dibuat Oleh:</strong> {{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </div>

    @if (count($groupedMovements) > 0)
        @foreach ($groupedMovements as $date => $movements)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Produk</th>
                                    <th>SKU</th>
                                    <th>Gudang</th>
                                    <th>Cabang</th>
                                    <th>Tipe</th>
                                    <th>Kuantitas</th>
                                    <th>Stok Akhir</th>
                                    <th>Pengguna</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movements as $movement)
                                    <tr>
                                        <td>{{ $movement->created_at->format('H:i:s') }}</td>
                                        <td>{{ $movement->product->name }}</td>
                                        <td>{{ $movement->product->sku }}</td>
                                        <td>{{ $movement->warehouse->name }}</td>
                                        <td>{{ $movement->warehouse->branch->name }}</td>
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
                                        <td>{{ number_format($movement->quantity, 2) }} {{ $movement->product->unit }}
                                        </td>
                                        <td>{{ number_format($movement->current_quantity, 2) }}
                                            {{ $movement->product->unit }}</td>
                                        <td>{{ $movement->user->name }}</td>
                                        <td>{{ $movement->notes }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i> Tidak ada data pergerakan stok yang sesuai dengan filter yang dipilih.
        </div>
    @endif
@endsection
