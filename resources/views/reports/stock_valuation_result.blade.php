{{-- resources/views/reports/stock_valuation_result.blade.php --}}
@extends('layouts.app')

@section('title', 'Hasil Laporan Valuasi Stok')

@section('actions')
    <div class="dropdown">
        <button class="btn btn-sm btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="bi bi-download"></i> Export
        </button>
        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
            <li><a class="dropdown-item" href="{{ route('reports.export-stock-valuation', ['format' => 'xlsx']) }}">Export
                    ke Excel</a></li>
            <li><a class="dropdown-item" href="{{ route('reports.export-stock-valuation', ['format' => 'pdf']) }}">Export ke
                    PDF</a></li>
        </ul>
    </div>

    <a href="{{ route('reports.stock-valuation') }}" class="btn btn-sm btn-secondary ms-2">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Laporan Valuasi Stok</h5>
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
                    <p class="mb-1"><strong>Dikelompokkan berdasarkan:</strong>
                        @if ($group_by == 'warehouse')
                            Gudang
                        @elseif($group_by == 'category')
                            Kategori
                        @else
                            Produk
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

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5 class="alert-heading">Total Nilai Stok: Rp {{ number_format($totalValue, 0, ',', '.') }}</h5>
                        <p class="mb-0">Laporan ini hanya menampilkan stok dengan kuantitas lebih dari 0.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (count($groupedResults) > 0)
        @foreach ($groupedResults as $group)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $group['name'] }}</h5>
                    <span class="badge bg-light text-dark">Nilai: Rp
                        {{ number_format($group['total_value'], 0, ',', '.') }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    @if ($group_by != 'product')
                                        <th>Produk</th>
                                        <th>SKU</th>
                                    @endif

                                    @if ($group_by != 'category')
                                        <th>Kategori</th>
                                    @endif

                                    @if ($group_by != 'warehouse')
                                        <th>Gudang</th>
                                        <th>Cabang</th>
                                    @endif

                                    <th>Stok</th>
                                    <th>Harga Modal</th>
                                    <th>Nilai Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group['items'] as $stock)
                                    <tr>
                                        @if ($group_by != 'product')
                                            <td>{{ $stock->product_name }}</td>
                                            <td>{{ $stock->sku }}</td>
                                        @endif

                                        @if ($group_by != 'category')
                                            <td>{{ $stock->category_name }}</td>
                                        @endif

                                        @if ($group_by != 'warehouse')
                                            <td>{{ $stock->warehouse_name }}</td>
                                            <td>{{ $stock->branch_name }}</td>
                                        @endif

                                        <td>{{ number_format($stock->quantity, 2) }} {{ $stock->unit }}</td>
                                        <td>Rp {{ number_format($stock->cost, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($stock->total_value, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    @if ($group_by != 'product')
                                        <th colspan="2">Subtotal</th>
                                        @if ($group_by != 'category')
                                            <th colspan="{{ $group_by != 'warehouse' ? 3 : 1 }}"></th>
                                        @endif
                                    @else
                                        <th>Subtotal</th>
                                        @if ($group_by != 'category')
                                            <th colspan="{{ $group_by != 'warehouse' ? 3 : 1 }}"></th>
                                        @endif
                                    @endif
                                    <th colspan="2">Rp {{ number_format($group['total_value'], 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i> Tidak ada data stok yang sesuai dengan filter yang dipilih.
        </div>
    @endif
@endsection
