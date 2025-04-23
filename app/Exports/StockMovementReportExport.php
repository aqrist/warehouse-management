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

class StockMovementReportExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
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
        $start_date = Carbon::parse($this->params['start_date'])->startOfDay();
        $end_date = Carbon::parse($this->params['end_date'])->endOfDay();
        
        $query = StockMovement::with(['product', 'warehouse.branch', 'user'])
            ->whereBetween('created_at', [$start_date, $end_date]);
        
        // Apply filters
        if (!empty($this->params['branch_id'])) {
            $query->whereHas('warehouse', function($q) {
                $q->where('branch_id', $this->params['branch_id']);
            });
        }
        
        if (!empty($this->params['warehouse_id'])) {
            $query->where('warehouse_id', $this->params['warehouse_id']);
        }
        
        if (!empty($this->params['product_id'])) {
            $query->where('product_id', $this->params['product_id']);
        }
        
        if (!empty($this->params['movement_type']) && $this->params['movement_type'] != 'all') {
            $query->where('type', $this->params['movement_type']);
        }
        
        return $query->orderBy('created_at', 'desc');
    }
    
    public function headings(): array
    {
        return [
            'No.',
            'Tanggal & Waktu',
            'Produk',
            'SKU',
            'Gudang',
            'Cabang',
            'Tipe',
            'Kuantitas',
            'Stok Akhir',
            'Satuan',
            'Pengguna',
            'Keterangan'
        ];
    }
    
    public function map($movement): array
    {
        $type = 'Penyesuaian';
        if ($movement->type == 'in') {
            $type = 'Masuk';
        } elseif ($movement->type == 'out') {
            $type = 'Keluar';
        } elseif ($movement->type == 'transfer') {
            $type = $movement->quantity > 0 ? 'Transfer Masuk' : 'Transfer Keluar';
        }
        
        static $i = 0;
        $i++;
        
        return [
            $i,
            $movement->created_at->format('d/m/Y H:i:s'),
            $movement->product->name,
            $movement->product->sku,
            $movement->warehouse->name,
            $movement->warehouse->branch->name,
            $type,
            number_format($movement->quantity, 2),
            number_format($movement->current_quantity, 2),
            $movement->product->unit,
            $movement->user->name,
            $movement->notes
        ];
    }
    
    public function title(): string
    {
        return 'Laporan Pergerakan Stok';
    }
    
    public function styles(Worksheet $sheet)
    {
        // Add report headers at the top
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'LAPORAN PERGERAKAN STOK');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        $sheet->setCellValue('A3', 'Cabang:');
        $sheet->setCellValue('B3', $this->titles['branch']);
        
        $sheet->setCellValue('A4', 'Gudang:');
        $sheet->setCellValue('B4', $this->titles['warehouse']);
        
        $sheet->setCellValue('A5', 'Produk:');
        $sheet->setCellValue('B5', $this->titles['product']);
        
        $sheet->setCellValue('A6', 'Tipe Pergerakan:');
        $sheet->setCellValue('B6', $this->titles['type']);
        
        $sheet->setCellValue('A7', 'Periode:');
        $sheet->setCellValue('B7', $this->titles['period']);
        
        $sheet->setCellValue('A8', 'Tanggal Laporan:');
        $sheet->setCellValue('B8', now()->format('d/m/Y H:i'));
        
        // Start the actual table at row 10
        $startRow = 10;
        
        // Style the header row
        $sheet->getStyle('A' . $startRow . ':L' . $startRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $startRow . ':L' . $startRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDDDDD');
        
        return [
            // Style all cells
            'A' . $startRow . ':L' . ($sheet->getHighestRow()) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }
}
