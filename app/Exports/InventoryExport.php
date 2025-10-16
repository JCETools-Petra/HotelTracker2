<?php

namespace App\Exports;

use App\Models\Inventory;
use App\Models\Property;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $propertyId;
    protected $propertyName;

    public function __construct(int $propertyId)
    {
        $this->propertyId = $propertyId;
        $property = Property::find($propertyId);
        $this->propertyName = $property ? $property->name : 'Unknown Property';
    }

    public function collection()
    {
        return Inventory::where('property_id', $this->propertyId)->with('category')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Item', 'Nama Item', 'Kategori', 'Stok', 'Unit',
            'Kondisi', 'Harga Satuan', 'Tgl. Pembelian',
        ];
    }

    public function map($item): array
    {
        return [
            $item->item_code,
            $item->name,
            $item->category->name ?? 'N/A',
            $item->stock,
            $item->unit,
            ucfirst($item->condition),
            $item->unit_price,
            $item->purchase_date ? $item->purchase_date->format('d-m-Y') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setTitle('Inventaris ' . $this->propertyName);
        
        $headerStyle = $sheet->getStyle('A1:H1');
        $headerStyle->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $headerStyle->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FF4F46E5');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Menambahkan AutoFilter ke seluruh baris header (A1 sampai H1)
                $event->sheet->getDelegate()->setAutoFilter('A1:H1');
            },
        ];
    }
}