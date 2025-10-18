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
        $this->kpiData   = $kpiData;
        $this->property  = $property;
    }

    // ---- Color helpers (for title/header fills only) ----

    // #RRGGBB => FFRRGGBB
    private function hexToArgb(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return 'FF' . strtoupper($hex);
    }

    // brighten/darken
    private function adjustHex(string $hex, int $percent): string
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $factor = max(-100, min(100, $percent)) / 100.0;
        $adjust = function ($c) use ($factor) {
            $target = ($factor >= 0) ? 255 : 0;
            $delta  = ($target - $c) * abs($factor);
            $val    = (int) round($c + $delta);
            return max(0, min(255, $val));
        };

        $r = $adjust($r);
        $g = $adjust($g);
        $b = $adjust($b);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    private function getKpiItems(): array
    {
        return [
            'Total Pendapatan'                    => (float) $this->kpiData['totalRevenue'],
            'Okupansi Rata-rata'                  => ($this->kpiData['avgOccupancy'] > 0) ? (float) $this->kpiData['avgOccupancy'] / 100 : 0,
            'Average Room Rate (ARR)'             => (float) $this->kpiData['avgArr'],
            'Revenue Per Available Room (RevPAR)' => (float) $this->kpiData['revPar'],
            'Resto Revenue Per Room (Sold)'       => (float) $this->kpiData['restoRevenuePerRoom'],
        ];
    }

    private function getRevenueDetails(): array
    {
        return array_merge($this->kpiData['revenueBreakdown'], [
            'Total Pendapatan Kamar' => (float) $this->kpiData['totalRoomRevenue'],
            ' '                      => null, // spacer
            'Breakfast'              => (float) $this->kpiData['totalBreakfastRevenue'],
            'Lunch'                  => (float) $this->kpiData['totalLunchRevenue'],
            'Dinner'                 => (float) $this->kpiData['totalDinnerRevenue'],
            'Total F&B'              => (float) $this->kpiData['totalFbRevenue'],
            'Lain-lain'              => (float) $this->kpiData['totalOtherRevenue'],
        ]);
    }

    private function getRoomsSoldDetails(): array
    {
        $roomsSoldDetails = $this->kpiData['roomsSoldBreakdown'];
        $roomsSoldDetails['Total Kamar Terjual'] = (int) $this->kpiData['totalRoomsSold'];
        return $roomsSoldDetails;
    }

    public function title(): string
    {
        return $this->monthName;
    }

    public function array(): array
    {
        $data = [];

        // Titles
        $data[] = ['Laporan Analisis Kinerja (KPI)'];
        $data[] = ['Properti: ' . ($this->property->name ?? 'Semua Properti')];
        $data[] = ['Bulan: ' . $this->monthName];
        $data[] = []; // spacer

        // 3-block header
        $data[] = ['METRIK UTAMA', null, null, 'RINCIAN PENDAPATAN', null, null, 'RINCIAN KAMAR TERJUAL', null];

        // 3-block details aligned row-wise
        $kpiItems         = $this->getKpiItems();
        $revenueDetails   = $this->getRevenueDetails();
        $roomsSoldDetails = $this->getRoomsSoldDetails();

        $kpiKeys     = array_keys($kpiItems);
        $revenueKeys = array_keys($revenueDetails);
        $roomsKeys   = array_keys($roomsSoldDetails);

        $maxRows = max(count($kpiKeys), count($revenueKeys), count($roomsKeys));

        for ($i = 0; $i < $maxRows; $i++) {
            $data[] = [
                $kpiKeys[$i] ?? null,
                isset($kpiKeys[$i]) ? $kpiItems[$kpiKeys[$i]] : null,
                null,
                $revenueKeys[$i] ?? null,
                isset($revenueKeys[$i]) ? $revenueDetails[$revenueKeys[$i]] : null,
                null,
                $roomsKeys[$i] ?? null,
                isset($roomsKeys[$i]) ? $roomsSoldDetails[$roomsKeys[$i]] : null,
            ];
        }

        // Daily table: 3 empty rows then header
        $data[] = [];
        $data[] = [];
        $data[] = [];
        $data[] = ['Tanggal', 'Pendapatan', 'Okupansi (%)', 'ARR', 'Kamar Terjual'];

        foreach ($this->dailyData as $daily) {
            $data[] = [
                $daily['date'],
                (float) $daily['revenue'],
                ($daily['occupancy'] > 0) ? (float) $daily['occupancy'] / 100 : 0,
                (float) $daily['arr'],
                (int) $daily['rooms_sold'],
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // ===== Formats & base styles =====
        $currencyFormat = '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)';
        $percentFormat  = '0.00%';
        $numberFormat   = '#,##0';

        $borderStyle = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $allBorders  = ['allBorders' => ['borderStyle' => $borderStyle]];

        // Dynamic color from DB (for titles/headers only)
        $baseHex   = ($this->property && !empty($this->property->chart_color)) ? $this->property->chart_color : '#1F4E78';
        $headerHex = $baseHex;
        $titleHex  = $this->adjustHex($baseHex, -25);

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => $this->hexToArgb($headerHex)],
            ],
            'borders' => $allBorders,
        ];
        $mainTitleStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => $this->hexToArgb($titleHex)],
            ],
        ];

        // ===== Title block (rows 1-3)
        $sheet->mergeCells('A1:H1')->getStyle('A1')->applyFromArray($mainTitleStyle)->getFont()->setSize(16);
        $sheet->mergeCells('A2:H2')->getStyle('A2')->applyFromArray($mainTitleStyle);
        $sheet->mergeCells('A3:H3')->getStyle('A3')->applyFromArray($mainTitleStyle);

        // ===== Compute 3-column block positioning
        $kpiItems         = $this->getKpiItems();
        $revenueDetails   = $this->getRevenueDetails();
        $roomsSoldDetails = $this->getRoomsSoldDetails();

        $kpiCount     = count($kpiItems);
        $revenueCount = count($revenueDetails);
        $roomsCount   = count($roomsSoldDetails);
        $maxRows      = max($kpiCount, $revenueCount, $roomsCount);

        $headerRow3Col  = 5;        // header row
        $firstDetailRow = 6;        // first details row
        $lastDetailRow  = 5 + $maxRows;

        // Header bold + box row 5 A:B, D:E, G:H
        $sheet->getStyle("A{$headerRow3Col}:H{$headerRow3Col}")->getFont()->setBold(true);
        $sheet->getStyle('A5:B5')->getBorders()->applyFromArray($allBorders);
        $sheet->getStyle('D5:E5')->getBorders()->applyFromArray($allBorders);
        $sheet->getStyle('G5:H5')->getBorders()->applyFromArray($allBorders);

        // KPI (A:B) – formats & borders only where there is label
        // Default column B to currency
        $sheet->getStyle("B{$firstDetailRow}:B{$lastDetailRow}")
              ->getNumberFormat()->setFormatCode($currencyFormat);
        // Also force B5 as currency if exists
        $sheet->getStyle('B5')->getNumberFormat()->setFormatCode($currencyFormat);

        for ($r = $firstDetailRow; $r <= $lastDetailRow; $r++) {
            $kpiLabel = trim((string) $sheet->getCell("A{$r}")->getValue());
            if ($kpiLabel !== '' && $kpiLabel !== ' ') {
                $sheet->getStyle("A{$r}:B{$r}")->getBorders()->applyFromArray($allBorders);
                if ($kpiLabel === 'Okupansi Rata-rata') {
                    $sheet->getStyle("B{$r}")->getNumberFormat()->setFormatCode($percentFormat);
                }
            }
        }

        // Revenue (D:E) – borders only where there is label
        for ($r = $firstDetailRow; $r <= $lastDetailRow; $r++) {
            $revLabel = trim((string) $sheet->getCell("D{$r}")->getValue());
            if ($revLabel !== '' && $revLabel !== ' ') {
                $sheet->getStyle("D{$r}:E{$r}")->getBorders()->applyFromArray($allBorders);
                $sheet->getStyle("E{$r}")->getNumberFormat()->setFormatCode($currencyFormat);
                if ($revLabel === 'Total Pendapatan Kamar' || $revLabel === 'Total F&B') {
                    $sheet->getStyle("D{$r}:E{$r}")->getFont()->setBold(true);
                }
            }
        }

        // Rooms sold (G:H) – borders only where there is label
        for ($r = $firstDetailRow; $r <= $lastDetailRow; $r++) {
            $roomLabel = trim((string) $sheet->getCell("G{$r}")->getValue());
            if ($roomLabel !== '' && $roomLabel !== ' ') {
                $sheet->getStyle("G{$r}:H{$r}")->getBorders()->applyFromArray($allBorders);
                $sheet->getStyle("H{$r}")->getNumberFormat()->setFormatCode($numberFormat);
                if ($roomLabel === 'Total Kamar Terjual') {
                    $sheet->getStyle("G{$r}:H{$r}")->getFont()->setBold(true);
                }
            }
        }

        // ===== Daily table =====
        // In array(): we added 3 blanks after lastDetailRow, then header row.
        // So header is at lastDetailRow + 4
        $dailyHeaderRow = $lastDetailRow + 0;
        $firstDataRow   = $dailyHeaderRow + 1;
        $dataCount      = (int) $this->dailyData->count();
        $lastDataRow    = $dailyHeaderRow + $dataCount;

        // Header fill (DB color)
        $sheet->getRowDimension($dailyHeaderRow)->setRowHeight(22);
        $headerRange = "A{$dailyHeaderRow}:E{$dailyHeaderRow}";
        $sheet->getStyle($headerRange)->applyFromArray($headerStyle);

        if ($dataCount > 0) {
            // Borders for data rows
            $sheet->getStyle("A{$firstDataRow}:E{$lastDataRow}")
                  ->getBorders()->applyFromArray($allBorders);

            // Numbers
            $sheet->getStyle("B{$firstDataRow}:B{$lastDataRow}")
                  ->getNumberFormat()->setFormatCode($currencyFormat); // Pendapatan
            $sheet->getStyle("C{$firstDataRow}:C{$lastDataRow}")
                  ->getNumberFormat()->setFormatCode($percentFormat);  // Okupansi
            $sheet->getStyle("D{$firstDataRow}:D{$lastDataRow}")
                  ->getNumberFormat()->setFormatCode($currencyFormat); // ARR
            $sheet->getStyle("E{$firstDataRow}:E{$lastDataRow}")
                  ->getNumberFormat()->setFormatCode($numberFormat);   // Kamar Terjual

            for ($r = $firstDataRow; $r <= $lastDataRow; $r++) {
                $sheet->getRowDimension($r)->setRowHeight(20);
            }
        }
    }

    public function charts()
    {
        if ($this->dailyData->isEmpty()) {
            return [];
        }
    
        // Posisi tabel harian (selaras dengan array()/styles())
        $kpiCount     = count($this->getKpiItems());
        $revenueCount = count($this->getRevenueDetails());
        $roomsCount   = count($this->getRoomsSoldDetails());
        $maxRows      = max($kpiCount, $revenueCount, $roomsCount);
    
        $lastDetailRow  = 5 + $maxRows;
        $dailyHeaderRow = $lastDetailRow + 4;   // 3 baris kosong + 1 baris header
        $firstDataRow   = $dailyHeaderRow + 1;
        $dataRowCount   = $this->dailyData->count();
        $lastDataRow    = $dailyHeaderRow + $dataRowCount;
        $sheetName      = $this->title();
    
        // X = tanggal (kolom A), Y = pendapatan (kolom B)
        $categories = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_STRING,
                "'{$sheetName}'!\$A\${$firstDataRow}:\$A\${$lastDataRow}",
                null,
                $dataRowCount
            ),
        ];
        $labels = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_STRING,
                "'{$sheetName}'!\$B\${$dailyHeaderRow}",
                null,
                1
            ),
        ];
        $values = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_NUMBER,
                "'{$sheetName}'!\$B\${$firstDataRow}:\$B\${$lastDataRow}",
                null,
                $dataRowCount
            ),
        ];
    
        $series = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART,
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_CLUSTERED,
            range(0, count($values) - 1),
            $labels,
            $categories,
            $values
        );
    
        $plot   = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$series]);
        $legend = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_BOTTOM, null, false);
        $title  = new \PhpOffice\PhpSpreadsheet\Chart\Title('Pendapatan Harian');
    
        $chart = new \PhpOffice\PhpSpreadsheet\Chart\Chart('chart_' . str_replace(' ', '_', $this->monthName), $title, $legend, $plot);
        $chart->setTopLeftPosition('J5');
        $chart->setBottomRightPosition('T25');
    
        return [$chart];
    }


}
