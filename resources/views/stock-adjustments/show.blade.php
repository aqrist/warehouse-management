{{-- resources/views/stock-adjustments/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Penyesuaian Stok')

@section('actions')
    <a href="{{ route('stock-adjustments.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Penyesuaian</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">No. Referensi</th>
                            <td>{{ $stockAdjustment->reference_no }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ $stockAdjustment->date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Gudang</th>
                            <td>
                                @can('view warehouses')
                                    <a href="{{ route('warehouses.show', $stockAdjustment->warehouse_id) }}">
                                        {{ $stockAdjustment->warehouse->name }}
                                    </a>
                                @else
                                    {{ $stockAdjustment->warehouse->name }}
                                @endcan
                            </td>
                        </tr>
                        <tr>
                            <th>Cabang</th>
                            <td>{{ $stockAdjustment->warehouse->branch->name }}</td>
                        </tr>
                        <tr>
                            <th>Catatan</th>
                            <td>{{ $stockAdjustment->notes }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat Oleh</th>
                            <td>{{ $stockAdjustment->user->name }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $stockAdjustment->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Item Penyesuaian</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Produk</th>
                                    <th>SKU</th>
                                    <th>Tipe</th>
                                    <th>Jumlah</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stockAdjustment->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @can('view products')
                                                <a href="{{ route('products.show', $item->product_id) }}">
                                                    {{ $item->product->name }}
                                                </a>
                                            @else
                                                {{ $item->product->name }}
                                            @endcan
                                        </td>
                                        <td>{{ $item->product->sku }}</td>
                                        <td>
                                            @if ($item->type == 'addition')
                                                <span class="badge bg-success">Penambahan</span>
                                            @else
                                                <span class="badge bg-danger">Pengurangan</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($item->quantity, 2) }} {{ $item->product->unit }}</td>
                                        <td>{{ $item->notes ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <form action="{{ route('stock-adjustments.destroy', $stockAdjustment->id) }}" method="POST"
                            class="d-inline"
                            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan penyesuaian stok ini? Tindakan ini akan mengembalikan stok ke nilai sebelumnya.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Batalkan Penyesuaian
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
