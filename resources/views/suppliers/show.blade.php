{{-- resources/views/suppliers/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Supplier')

@section('actions')
    @can('edit suppliers')
        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
    @endcan

    <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Supplier</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">ID</th>
                            <td>{{ $supplier->id }}</td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td>{{ $supplier->name }}</td>
                        </tr>
                        <tr>
                            <th>Kode</th>
                            <td>{{ $supplier->code }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $supplier->address ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>{{ $supplier->phone ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $supplier->email ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Contact Person</th>
                            <td>{{ $supplier->contact_person ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if ($supplier->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $supplier->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diperbarui Pada</th>
                            <td>{{ $supplier->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Produk dari Supplier Ini</h5>
                    @can('view products')
                        <a href="{{ route('products.index', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-info">
                            <i class="bi bi-box-seam"></i> Lihat Semua Produk
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if ($supplier->products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Produk</th>
                                        <th>SKU</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($supplier->products as $product)
                                        <tr>
                                            <td>
                                                @can('view products')
                                                    <a
                                                        href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a>
                                                @else
                                                    {{ $product->name }}
                                                @endcan
                                            </td>
                                            <td>{{ $product->sku }}</td>
                                            <td>{{ $product->category->name ?? '-' }}</td>
                                            <td>{{ number_format($product->price, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> Belum ada produk dari supplier ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
