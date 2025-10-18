<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class KpiAnalysisMonthlySheet implements FromArray, WithTitle, WithStyles, WithCharts, ShouldAutoSize
{
    private $dailyData;
    private $kpiData;
    private $monthName;
    private $property;

    public function __construct(string $monthName, $dailyData, $kpiData, $property)
    {
        $this->monthName = $monthName;
        $this->dailyData = $dailyData;
        $this->kpiData = $kpiData;
        $this->property = $property;
    }

    public function title(): string
    {
        return $this->monthName;
    }

    public function array(): array
    {
        $data = [];

        // --- Bagian Judul ---
        $data[] = ['Laporan Analisis Kinerja (KPI)'];
        $data[] = ['Properti: ' . ($this->property->name ?? 'Semua Properti')];
        $data[] = ['Bulan: ' . $this->monthName];
        $data[] = []; // Spasi

        // --- Data untuk 3 Kolom ---
        $kpiItems = [
            'Total Pendapatan' => (float) $this->kpiData['totalRevenue'],
            'Okupansi Rata-rata' => (float) $this->kpiData['avgOccupancy'] / 100,
            'Average Room Rate (ARR)' => (float) $this->kpiData['avgArr'],
            'Revenue Per Available Room (RevPAR)' => (float) $this->kpiData['revPar'],
            'Resto Revenue Per Room (Sold)' => (float) $this->kpiData['restoRevenuePerRoom'],
        ];

        $revenueDetails = array_merge($this->kpiData['revenueBreakdown'], [
            'Total Pendapatan Kamar' => (float) $this->kpiData['totalRoomRevenue'],
            ' ' => null, // Spacer
            'Breakfast' => (float) $this->kpiData['totalBreakfastRevenue'],
            'Lunch' => (float) $this->kpiData['totalLunchRevenue'],
            'Dinner' => (float) $this->kpiData['totalDinnerRevenue'],
            'Total F&B' => (float) $this->kpiData['totalFbRevenue'],
            'Lain-lain' => (float) $this->kpiData['totalOtherRevenue'],
        ]);

        $roomsSoldDetails = $this->kpiData['roomsSoldBreakdown'];
        $roomsSoldDetails['Total Kamar Terjual'] = (int) $this->kpiData['totalRoomsSold'];

        $kpiKeys = array_keys($kpiItems);
        $revenueKeys = array_keys($revenueDetails);
        $roomsKeys = array_keys($roomsSoldDetails);
        $maxRows = max(count($kpiKeys), count($revenueKeys), count($roomsKeys));
        
        $data[] = ['METRIK UTAMA', null, null, 'RINCIAN PENDAPATAN', null, null, 'RINCIAN KAMAR TERJUAL', null];

        for ($i = 0; $i < $maxRows; $i++) {
            $row = [
                $kpiKeys[$i] ?? null,
                isset($kpiKeys[$i]) ? $kpiItems[$kpiKeys[$i]] : null,
                null,
                $revenueKeys[$i] ?? null,
                isset($revenueKeys[$i]) ? $revenueDetails[$revenueKeys[$i]] : null,
                null,
                $roomsKeys[$i] ?? null,
                isset($roomsKeys[$i]) ? $roomsSoldDetails[$roomsKeys[$i]] : null,
            ];
            $data[] = $row;
        }

        $data[] = [];

        $data[] = ['Tabel Rincian Harian'];
        $data[] = ['Tanggal', 'Pendapatan', 'Okupansi (%)', 'ARR', 'Kamar Terjual'];
        foreach ($this->dailyData as $daily) {
            $data[] = [
                $daily['date'],
                (float) $daily['revenue'],
                (float) $daily['occupancy'] / 100,
                (float) $daily['arr'],
                (int) $daily['rooms_sold'],
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $currencyFormat = '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)';
        $percentFormat = '0.00%';
        $numberFormat = '#,##0';
        $borderStyle = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $allBorders = ['allBorders' => ['borderStyle' => $borderStyle]];
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => '4F81BD']],
        ];
        $mainTitleStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => '2F5597']],
        ];

        // 1. Judul Utama dengan Latar Biru Tua
        $sheet->mergeCells('A1:H1')->getStyle('A1')->applyFromArray($mainTitleStyle)->getFont()->setSize(16);
        $sheet->mergeCells('A2:H2')->getStyle('A2')->applyFromArray($mainTitleStyle)->getFont()->setBold(true);
        $sheet->mergeCells('A3:H3')->getStyle('A3')->applyFromArray($mainTitleStyle)->getFont()->setBold(true);
        
        // 2. Header untuk 3 Kolom Rincian
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);

        // 3. Menentukan baris terakhir dari rincian
        $maxRows = max(count($this->kpiData['revenueBreakdown']) + 7, count($this->kpiData['roomsSoldBreakdown']) + 1, 5);
        $lastDetailRow = 5 + $maxRows;
        
        // 4. Format dan Border untuk 3 Kolom
        // Kolom 1: Metrik Utama
        $sheet->getStyle("A6:B{$lastDetailRow}")->getBorders()->applyFromArray($allBorders);
        $sheet->getStyle('B6')->getNumberFormat()->setFormatCode($currencyFormat); // Total Pendapatan
        $sheet->getStyle('B7')->getNumberFormat()->setFormatCode($percentFormat);  // Okupansi
        $sheet->getStyle('B8:B10')->getNumberFormat()->setFormatCode($currencyFormat); // ARR, RevPAR, Resto
        
        // Kolom 2: Rincian Pendapatan
        $sheet->getStyle("D6:E{$lastDetailRow}")->getBorders()->applyFromArray($allBorders);
        $sheet->getStyle('E6:E'.$lastDetailRow)->getNumberFormat()->setFormatCode($currencyFormat);
        
        // Kolom 3: Rincian Kamar Terjual
        $sheet->getStyle("G6:H{$lastDetailRow}")->getBorders()->applyFromArray($allBorders);
        $sheet->getStyle('H6:H'.$lastDetailRow)->getNumberFormat()->setFormatCode($numberFormat);

        // Bold untuk Total
        $totalRoomRevenueRow = 6 + count($this->kpiData['revenueBreakdown']);
        $totalFbRow = $totalRoomRevenueRow + 5;
        $totalRoomsSoldRow = 6 + count($this->kpiData['roomsSoldBreakdown']);
        $sheet->getStyle("D{$totalRoomRevenueRow}:E{$totalRoomRevenueRow}")->getFont()->setBold(true);
        $sheet->getStyle("D{$totalFbRow}:E{$totalFbRow}")->getFont()->setBold(true);
        $sheet->getStyle("G{$totalRoomsSoldRow}:H{$totalRoomsSoldRow}")->getFont()->setBold(true);

        // 5. Tabel Rincian Harian
        $dailyTitleRow = $lastDetailRow + 2;
        $dailyHeaderRow = $lastDetailRow + 3;
        $sheet->getStyle("A{$dailyTitleRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$dailyHeaderRow}:E{$dailyHeaderRow}")->applyFromArray($headerStyle);
        if ($this->dailyData->isNotEmpty()) {
            $lastDataRow = $dailyHeaderRow + $this->dailyData->count();
            $sheet->getStyle("A{$dailyHeaderRow}:E{$lastDataRow}")->getBorders()->applyFromArray($allBorders);
            $sheet->getStyle("B".($dailyHeaderRow+1).":B{$lastDataRow}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("C".($dailyHeaderRow+1).":C{$lastDataRow}")->getNumberFormat()->setFormatCode($percentFormat);
            $sheet->getStyle("D".($dailyHeaderRow+1).":D{$lastDataRow}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("E".($dailyHeaderRow+1).":E{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);
        }
    }

    public function charts()
    {
        if ($this->dailyData->isEmpty()) { return []; }
        
        $maxRows = max(count($this->kpiData['revenueBreakdown']) + 7, count($this->kpiData['roomsSoldBreakdown']) + 1, 5);
        $dailyHeaderRow = 5 + $maxRows + 3;
        $firstDataRow = $dailyHeaderRow + 1;
        $dataRowCount = $this->dailyData->count();
        $lastDataRow = $dailyHeaderRow + $dataRowCount;
        $sheetName = $this->title();

        $labels = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'{$sheetName}'!\$A\${$firstDataRow}:\$A\${$lastDataRow}", null, $dataRowCount)];
        $categories = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'{$sheetName}'!\$B\${$dailyHeaderRow}", null, 1)];
        $values = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'{$sheetName}'!\$B\${$firstDataRow}:\$B\${$lastDataRow}", null, $dataRowCount)];
        
        $series = new DataSeries(DataSeries::TYPE_BARCHART, DataSeries::GROUPING_CLUSTERED, range(0, count($values) - 1), $labels, $categories, $values);
        $plotArea = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $title = new Title('Pendapatan Harian');
        
        $chart = new Chart('chart_'.str_replace(' ', '_', $this->monthName), $title, $legend, $plotArea);
        
        $chart->setTopLeftPosition('J5');
        $chart->setBottomRightPosition('T25');

        return $chart;
    }
}