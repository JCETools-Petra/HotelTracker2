<?php

namespace App\Exports\Sheets;

use App\Models\Property;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\PivotTable; // <-- [PERBAIKAN PENTING]

class InventoryReportSheet implements WithTitle, WithEvents
{
    private $propertyName;

    public function __construct(int $propertyId)
    {
        $property = Property::find($propertyId);
        $this->propertyName = $property ? $property->name : 'Unknown';
    }

    public function title(): string
    {
        return 'Laporan Inventaris';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->setCellValue('A1', 'Laporan Inventaris Interaktif: ' . $this->propertyName);
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

                $pivotTable = new PivotTable('InventoryData', 'A3', $sheet);
                $pivotTable->addFieldToPage(2); // Indeks kolom 'Kategori'

                $pivotTable->addFieldToRow(0); // Kode Item
                $pivotTable->addFieldToRow(1); // Nama Item
                $pivotTable->addFieldToRow(3); // Stok
                $pivotTable->addFieldToRow(4); // Unit
                $pivotTable->addFieldToRow(5); // Kondisi
                $pivotTable->addFieldToRow(6); // Harga Satuan
                $pivotTable->addFieldToRow(7); // Tgl Beli

                $pivotTable->setRowGrandTotals(false);
                $pivotTable->setColumnGrandTotals(false);

                $sheet->addPivotTable($pivotTable);
            }
        ];
    }
}