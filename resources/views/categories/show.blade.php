{{-- resources/views/categories/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Kategori')

@section('actions')
    @can('edit categories')
        <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
    @endcan

    <a href="{{ route('categories.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Kategori</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">ID</th>
                            <td>{{ $category->id }}</td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td>{{ $category->name }}</td>
                        </tr>
                        <tr>
                            <th>Kode</th>
                            <td>{{ $category->code }}</td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>{{ $category->description ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if ($category->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Jumlah Produk</th>
                            <td>{{ $category->products->count() }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Diperbarui Pada</th>
                            <td>{{ $category->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Produk dalam Kategori Ini</h5>
                    @can('view products')
                        <a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="btn btn-sm btn-info">
                            <i class="bi bi-box-seam"></i> Lihat Semua Produk
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if ($category->products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Produk</th>
                                        <th>SKU</th>
                                        <th>Harga</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($category->products as $product)
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
                                            <td>{{ number_format($product->price, 0, ',', '.') }}</td>
                                            <td>
                                                @if ($product->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger">Tidak Aktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> Belum ada produk dalam kategori ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
