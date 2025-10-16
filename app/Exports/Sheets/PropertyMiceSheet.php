<?php

namespace App\Exports\Sheets;

use App\Models\Booking;
use App\Models\Property;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PropertyMiceSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithColumnWidths, WithEvents, WithCustomStartCell
{
    private $startDate;
    private $endDate;
    private $property;
    private $data;

    public function __construct($startDate, $endDate, Property $property)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->property = $property;

        $this->data = Booking::where('property_id', $this->property->id)
            ->whereBetween('event_date', [$this->startDate, $this->endDate])
            ->with('miceCategory') // Eager load relasi
            ->orderBy('event_date', 'asc')
            ->get();
    }

    public function title(): string
    {
        $cleanName = preg_replace('/[\\\\*[:\/\\?]/', '', $this->property->name);
        return substr($cleanName, 0, 24) . ' - MICE'; // Judul sheet (misal: "Hotel Akat - MICE")
    }

    public function collection()
    {
        return $this->data;
    }

    public function startCell(): string
    {
        return 'A5'; // Mulai tabel data di baris 5
    }

    public function headings(): array
    {
        return [
            'Tanggal Event',
            'Nama Pemesan',
            'Kategori MICE',
            'Status',
            'Jumlah Pax',
            'Nilai (Rp)',
        ];
    }

    /**
     * @param Booking $booking
     */
    public function map($booking): array
    {
        return [
            \Carbon\Carbon::parse($booking->event_date)->toDateString(),
            $booking->client_name,
            $booking->miceCategory->name ?? 'N/A',
            $booking->status,
            $booking->pax,
            $booking->total_price,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, 'B' => 30, 'C' => 20, 'D' => 20, 'E' => 15, 'F' => 25,
        ];
    }

    public function registerEvents(): array
    {
        $rowCount = $this->data->count();
        $totalRevenue = $this->data->sum('total_price');

        return [
            AfterSheet::class => function (AfterSheet $event) use ($rowCount, $totalRevenue) {
                $sheet = $event->sheet->getDelegate();

                // === Header Laporan ===
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'Laporan Event MICE');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A2', $this->property->name);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergeCells('A3:F3');
                $sheet->setCellValue('A3', 'Periode: ' . $this->startDate->isoFormat('D MMM YYYY') . ' - ' . $this->endDate->isoFormat('D MMMM YYYY'));
                $sheet->getStyle('A3')->getFont()->setItalic(true);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // === Styling Header Tabel (Baris 5) ===
                $sheet->getStyle('A5:F5')->getFont()->setBold(true);
                $sheet->getStyle('A5:F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');
                $sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // === Baris Total ===
                $firstDataRow = 6;
                $lastDataRow = $rowCount + $firstDataRow - 1;
                $totalRow = $lastDataRow + 1;
                
                if ($rowCount > 0) {
                    $sheet->setCellValue("A{$totalRow}", 'TOTAL PENDAPATAN MICE');
                    $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
                    $sheet->setCellValue("F{$totalRow}", "=SUM(F{$firstDataRow}:F{$lastDataRow})");

                    $sheet->getStyle("A{$totalRow}:F{$totalRow}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$totalRow}:F{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');

                    // Format Angka & Tanggal
                    $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")->getNumberFormat()->setFormatCode('d mmm yyyy');
                    $sheet->getStyle("F{$firstDataRow}:F{$totalRow}")->getNumberFormat()->setFormatCode('"Rp"#,##0');
                    $sheet->getStyle("A5:F{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }

                $sheet->freezePane('A6');
            },
        ];
    }
}