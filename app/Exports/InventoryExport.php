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
    protected $property; // <-- [PERBAIKAN] Menyimpan seluruh objek properti

    public function __construct(int $propertyId)
    {
        $this->propertyId = $propertyId;
        // Mengambil seluruh objek properti untuk mengakses chart_color
        $this->property = Property::find($propertyId);
    }

    /**
     * Mengonversi kode warna HEX (misal: #FF0000) menjadi ARGB (misal: FFFF0000).
     */
    private function hexToArgb(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return 'FF' . strtoupper($hex);
    }

    public function collection()
    {
        return Inventory::where('property_id', $this->propertyId)->with('category')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Item', 'Nama Item', 'Kategori', 'Stok', 'Unit', 
            'MSQ', 'Kondisi', 'Harga Satuan', 'Tgl. Pembelian',
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
            $item->minimum_standard_quantity,
            ucfirst($item->condition),
            $item->unit_price,
            $item->purchase_date ? $item->purchase_date->format('d-m-Y') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $propertyName = $this->property ? $this->property->name : 'Unknown';
        $sheet->setTitle('Inventaris ' . $propertyName);
        
        // [PERBAIKAN] Mengambil warna dari properti, dengan warna default jika tidak ada
        $headerColorHex = ($this->property && !empty($this->property->chart_color)) 
                          ? $this->property->chart_color 
                          : '#4F46E5'; // Warna default

        $headerStyle = $sheet->getStyle('A1:I1');
        $headerStyle->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $headerStyle->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB($this->hexToArgb($headerColorHex)); // Menerapkan warna dinamis
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->setAutoFilter('A1:I1');
            },
        ];
    }
}