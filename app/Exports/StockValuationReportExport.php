<?php

namespace App\Exports;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockValuationReportExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
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
            ->select(
                'stocks.id',
                'stocks.warehouse_id',
                'stocks.product_id',
                'stocks.quantity',
                'stocks.min_quantity',
                'products.name as product_name',
                'products.sku',
                'products.cost',
                'products.unit',
                'categories.name as category_name',
                'warehouses.name as warehouse_name',
                'branches.name as branch_name',
                DB::raw('(stocks.quantity * products.cost) as total_value')
            )
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('branches', 'warehouses.branch_id', '=', 'branches.id')
            ->where('stocks.quantity', '>', 0);
        
        // Apply filters
        if (!empty($this->params['branch_id'])) {
            $query->where('branches.id', $this->params['branch_id']);
        }
        
        if (!empty($this->params['warehouse_id'])) {
            $query->where('warehouses.id', $this->params['warehouse_id']);
        }
        
        if (!empty($this->params['category_id'])) {
            $query->where('categories.id', $this->params['category_id']);
        }
        
        if (!empty($this->params['min_value'])) {
            $query->having('total_value', '>=', $this->params['min_value']);
        }
        
        return $query->orderBy('total_value', 'desc');
    }
    
    public function headings(): array
    {
        return [
            'No.',
            'Cabang',
            'Gudang',
            'Produk',
            'SKU',
            'Kategori',
            'Stok',
            'Satuan',
            'Harga Modal',
            'Nilai Total'
        ];
    }
    
    public function map($stock): array
    {
        static $i = 0;
        $i++;
        
        return [
            $i,
            $stock->branch_name,
            $stock->warehouse_name,
            $stock->product_name,
            $stock->sku,
            $stock->category_name,
            number_format($stock->quantity, 2),
            $stock->unit,
            number_format($stock->cost, 0, ',', '.'),
            number_format($stock->total_value, 0, ',', '.')
        ];
    }
    
    public function title(): string
    {
        return 'Laporan Valuasi Stok';
    }
    
    public function styles(Worksheet $sheet)
    {
        // Add report headers at the top
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'LAPORAN VALUASI STOK');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        $sheet->setCellValue('A3', 'Cabang:');
        $sheet->setCellValue('B3', $this->titles['branch']);
        
        $sheet->setCellValue('A4', 'Gudang:');
        $sheet->setCellValue('B4', $this->titles['warehouse']);
        
        $sheet->setCellValue('A5', 'Kategori:');
        $sheet->setCellValue('B5', $this->titles['category']);
        
        $sheet->setCellValue('A6', 'Nilai Minimum:');
        $sheet->setCellValue('B6', $this->titles['min_value']);
        
        $sheet->setCellValue('A7', 'Dikelompokkan berdasarkan:');
        $sheet->setCellValue('B7', $this->titles['group_by']);
        
        $sheet->setCellValue('A8', 'Tanggal Laporan:');
        $sheet->setCellValue('B8', now()->format('d/m/Y H:i'));
        
        // Start the actual table at row 10
        $startRow = 10;
        
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
