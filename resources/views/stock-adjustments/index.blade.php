{{-- resources/views/stock-adjustments/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Penyesuaian Stok')

@section('actions')
    <a href="{{ route('stock-adjustments.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle"></i> Buat Penyesuaian
    </a>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filter</h5>
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="warehouse_id" class="form-label">Gudang</label>
                        <select class="form-select select2" id="warehouse_id" name="warehouse_id">
                            <option value="">Semua Gudang</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->branch->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="end_date" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                    <div class="col-md-3 d-flex align-items-end mb-3">
                        <button type="button" id="btn-filter" class="btn btn-primary me-2">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                        <button type="button" id="btn-reset" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="adjustments-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>No. Referensi</th>
                            <th>Tanggal</th>
                            <th>Gudang</th>
                            <th>Cabang</th>
                            <th>Jumlah Item</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            var table = $('#adjustments-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('stock-adjustments.data') }}',
                    data: function(d) {
                        d.warehouse_id = $('#warehouse_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'reference_no',
                        name: 'reference_no'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'warehouse_name',
                        name: 'warehouse.name'
                    },
                    {
                        data: 'branch_name',
                        name: 'warehouse.branch.name'
                    },
                    {
                        data: 'items_count',
                        name: 'items_count',
                        searchable: false
                    },
                    {
                        data: 'user.name',
                        name: 'user.name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'desc']
                ]
            });

            // Filter button click
            $('#btn-filter').click(function() {
                table.draw();
            });

            // Reset button click
            $('#btn-reset').click(function() {
                $('#warehouse_id').val('').trigger('change');
                $('#start_date').val('');
                $('#end_date').val('');
                table.draw();
            });
        });
    </script>
@endpush
