<?php

namespace App\Exports\Sheets;

use App\Models\DailyIncome;
use App\Models\Property;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Color;

class PropertyIncomeSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithColumnWidths, WithEvents, WithCustomStartCell
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
        
        $this->data = DailyIncome::query()
            ->where('property_id', $this->property->id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderBy('date', 'asc')
            ->get();
    }

    public function title(): string
    {
        $cleanName = preg_replace('/[\\\\*[:\/\\?]/', '', $this->property->name);
        return substr($cleanName, 0, 31);
    }

    public function collection()
    {
        return $this->data;
    }

    public function startCell(): string
    {
        return 'A9';
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kamar Terjual',
            'Okupansi (%)',
            'ARR (Rp)',
            'Pendapatan Kamar (Rp)',
            'Pendapatan F&B (Rp)',
            'Pendapatan MICE (Rp)',
            'Pendapatan Lainnya (Rp)',
            'TOTAL PENDAPATAN (Rp)',
        ];
    }

    /**
     * @var DailyIncome $income
     */
    public function map($income): array
    {
        return [
            \Carbon\Carbon::parse($income->date)->toDateString(),
            $income->total_rooms_sold,
            $income->occupancy,
            $income->arr,
            $income->total_rooms_revenue,
            $income->total_fb_revenue,
            $income->mice_room_income,
            $income->others_income,
            $income->total_revenue,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, 'B' => 15, 'C' => 15, 'D' => 20, 'E' => 25,
            'F' => 25, 'G' => 25, 'H' => 25, 'I' => 30,
        ];
    }

    public function registerEvents(): array
    {
        // Variabel ini akan diteruskan ke dalam fungsi 'use' di bawah
        $property = $this->property;
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $data = $this->data;
        $rowCount = $this->data->count();

        return [
            BeforeSheet::class => function(BeforeSheet $event) use ($property, $startDate, $endDate, $data) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', 'Laporan Pendapatan Harian');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue('A2', $property->name);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergeCells('A3:I3');
                $sheet->setCellValue('A3', 'Periode: ' . $startDate->isoFormat('D MMM YYYY') . ' - ' . $endDate->isoFormat('D MMMM YYYY'));
                $sheet->getStyle('A3')->getFont()->setItalic(true);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $totalRevenue = $data->sum('total_revenue');
                $totalRoomsSold = $data->sum('total_rooms_sold');
                $avgOccupancy = $data->avg('occupancy');
                $avgArr = ($totalRoomsSold > 0) ? $data->sum('total_rooms_revenue') / $totalRoomsSold : 0;
                
                $sheet->setCellValue('G5', 'Total Pendapatan:');
                $sheet->setCellValue('H5', $totalRevenue);
                $sheet->setCellValue('G6', 'Avg. Okupansi:');
                $sheet->setCellValue('H6', $avgOccupancy / 100);
                $sheet->setCellValue('G7', 'Avg. ARR:');
                $sheet->setCellValue('H7', $avgArr);
                $sheet->setCellValue('G8', 'Total Kamar Terjual:');
                $sheet->setCellValue('H8', $totalRoomsSold);
                
                $sheet->getStyle('G5:H8')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
                $sheet->getStyle('G5:H8')->getFont()->setBold(true);
                
                // --- KODE YANG DIPERBAIKI ---
                $sheet->getStyle('G5:G8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('H5:H8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                // --- AKHIR KODE YANG DIPERBAIKI ---

                $sheet->getStyle('H5')->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheet->getStyle('H6')->getNumberFormat()->setFormatCode('0.00%');
                $sheet->getStyle('H7')->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheet->mergeCells('G8:H8');
            },

            AfterSheet::class => function(AfterSheet $event) use ($rowCount, $property) {
                $sheet = $event->sheet->getDelegate();
                $firstDataRow = 9;
                $lastDataRow = $rowCount + $firstDataRow;
                $totalRow = $lastDataRow + 1;

                $sheet->getStyle("A{$firstDataRow}:I{$firstDataRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$firstDataRow}:I{$firstDataRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');
                $sheet->getStyle("A{$firstDataRow}:I{$firstDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue("A{$totalRow}", 'TOTAL KESELURUHAN');
                $sheet->mergeCells("A{$totalRow}:B{$totalRow}");
                
                $sheet->setCellValue("C{$totalRow}", "=IFERROR(SUM(B{$firstDataRow}:B{$lastDataRow})/({$property->total_rooms}*COUNT(A{$firstDataRow}:A{$lastDataRow})),0)");
                $sheet->setCellValue("D{$totalRow}", "=IFERROR(SUM(E{$firstDataRow}:E{$lastDataRow})/SUM(B{$firstDataRow}:B{$lastDataRow}),0)");
                $sheet->setCellValue("E{$totalRow}", "=SUM(E{$firstDataRow}:E{$lastDataRow})");
                $sheet->setCellValue("F{$totalRow}", "=SUM(F{$firstDataRow}:F{$lastDataRow})");
                $sheet->setCellValue("G{$totalRow}", "=SUM(G{$firstDataRow}:G{$lastDataRow})");
                $sheet->setCellValue("H{$totalRow}", "=SUM(H{$firstDataRow}:H{$lastDataRow})");
                $sheet->setCellValue("I{$totalRow}", "=SUM(I{$firstDataRow}:I{$lastDataRow})");

                $sheet->getStyle("A{$totalRow}:I{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$totalRow}:I{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');

                $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")->getNumberFormat()->setFormatCode('d mmm yyyy');
                $sheet->getStyle("C{$firstDataRow}:C{$lastDataRow}")->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle("C{$totalRow}")->getNumberFormat()->setFormatCode('0.00%');
                $sheet->getStyle("D{$firstDataRow}:I{$totalRow}")->getNumberFormat()->setFormatCode('"Rp"#,##0');

                $sheet->getStyle("A{$firstDataRow}:I{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $lowConditional = new Conditional();
                $lowConditional->setConditionType(Conditional::CONDITION_CELLIS)->setOperatorType(Conditional::OPERATOR_LESSTHAN)->addCondition('40');
                $lowConditional->getStyle()->getFont()->getColor()->setARGB(Color::COLOR_RED);

                $highConditional = new Conditional();
                $highConditional->setConditionType(Conditional::CONDITION_CELLIS)->setOperatorType(Conditional::OPERATOR_GREATERTHANOREQUAL)->addCondition('80');
                $highConditional->getStyle()->getFont()->getColor()->setARGB(Color::COLOR_DARKGREEN);
                
                $sheet->getStyle("C{$firstDataRow}:C{$lastDataRow}")->setConditionalStyles([$lowConditional, $highConditional]);

                $sheet->freezePane('A10');

                $footerRow = $totalRow + 2;
                $sheet->setCellValue("A{$footerRow}", 'Laporan ini dibuat secara otomatis pada: ' . now()->isoFormat('D MMMM YYYY, HH:mm:ss'));
                $sheet->getStyle("A{$footerRow}")->getFont()->setItalic(true)->setSize(8);
            },
        ];
    }
}