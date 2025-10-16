<?php

namespace App\Exports\Sheets;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryDataSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents
{
    private $propertyId;

    public function __construct(int $propertyId)
    {
        $this->propertyId = $propertyId;
    }

    public function collection()
    {
        return Inventory::where('property_id', $this->propertyId)->with('category')->get();
    }

    public function headings(): array
    {
        return ['Kode Item', 'Nama Item', 'Kategori', 'Stok', 'Unit', 'Kondisi', 'Harga Satuan', 'Tgl. Pembelian'];
    }

    public function map($item): array
    {
        return [
            $item->item_code, $item->name, $item->category->name ?? 'N/A',
            $item->stock, $item->unit, ucfirst($item->condition),
            $item->unit_price, $item->purchase_date ? $item->purchase_date->format('d-m-Y') : '-',
        ];
    }

    public function title(): string
    {
        return 'Data';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dataRange = 'A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow();

                $event->sheet->getParent()->addNamedRange(
                    new \PhpOffice\PhpSpreadsheet\NamedRange('InventoryData', $sheet, $dataRange)
                );

                $sheet->setSheetState(Worksheet::SHEETSTATE_VERYHIDDEN);
            },
        ];
    }
}