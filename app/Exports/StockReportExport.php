<?php

namespace App\Exports;

use App\Models\Stock;
use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Carbon;

class StockReportExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $params;
    protected $titles;

    public function __construct($params, $titles)
    {
        $this->params = $params;
        $this->titles = $titles;
    }

    public function query()
    {
        $query = Stock::with(['product.category', 'warehouse.branch'])
            ->select('stocks.*')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('branches', 'warehouses.branch_id', '=', 'branches.id');

        // Apply filters
        if (!empty($this->params['branch_id'])) {
            $query->where('branches.id', $this->params['branch_id']);
        }

        if (!empty($this->params['warehouse_id'])) {
            $query->where('warehouses.id', $this->params['warehouse_id']);
        }

        if (!empty($this->params['category_id'])) {
            $query->whereHas('product', function ($q) {
                $q->where('category_id', $this->params['category_id']);
            });
        }

        if (!empty($this->params['stock_status']) && $this->params['stock_status'] != 'all') {
            if ($this->params['stock_status'] == 'in_stock') {
                $query->whereRaw('stocks.quantity > stocks.min_quantity');
            } elseif ($this->params['stock_status'] == 'low_stock') {
                $query->whereRaw('stocks.quantity > 0 AND stocks.quantity <= stocks.min_quantity');
            } elseif ($this->params['stock_status'] == 'out_of_stock') {
                $query->where('stocks.quantity', '<=', 0);
            }
        }

        if (!empty($this->params['search'])) {
            $search = $this->params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.sku', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('warehouses.id')->orderBy('products.name');
    }

    public function headings(): array
    {
        return [
            'No.',
            'Gudang',
            'Cabang',
            'Produk',
            'SKU',
            'Kategori',
            'Stok',
            'Min. Stok',
            'Satuan',
            'Status'
        ];
    }

    public function map($stock): array
    {
        $status = 'Tersedia';
        if ($stock->quantity <= 0) {
            $status = 'Habis';
        } elseif ($stock->quantity <= $stock->min_quantity) {
            $status = 'Hampir Habis';
        }

        static $i = 0;
        $i++;

        return [
            $i,
            $stock->warehouse->name,
            $stock->warehouse->branch->name,
            $stock->product->name,
            $stock->product->sku,
            $stock->product->category->name,
            number_format($stock->quantity, 2),
            number_format($stock->min_quantity, 2),
            $stock->product->unit,
            $status
        ];
    }

    public function title(): string
    {
        return 'Laporan Stok';
    }

    public function styles(Worksheet $sheet)
    {
        // Add report headers at the top
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'LAPORAN STOK');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A3', 'Cabang:');
        $sheet->setCellValue('B3', $this->titles['branch']);

        $sheet->setCellValue('A4', 'Gudang:');
        $sheet->setCellValue('B4', $this->titles['warehouse']);

        $sheet->setCellValue('A5', 'Kategori:');
        $sheet->setCellValue('B5', $this->titles['category']);

        $sheet->setCellValue('A6', 'Status:');
        $sheet->setCellValue('B6', $this->titles['status']);

        $sheet->setCellValue('A7', 'Tanggal:');
        $sheet->setCellValue('B7', now()->format('d/m/Y H:i'));

        // Start the actual table at row 9
        $startRow = 9;

        // Style the header row
        $sheet->getStyle('A' . $startRow . ':J' . $startRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $startRow . ':J' . $startRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDDDDD');

        return [
            // Style all cells
            'A' . $startRow . ':J' . ($sheet->getHighestRow()) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }
}
