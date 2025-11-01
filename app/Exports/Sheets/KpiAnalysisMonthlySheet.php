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
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Property;

class KpiAnalysisMonthlySheet implements FromArray, WithTitle, WithStyles, WithCharts, ShouldAutoSize
{
    /** @var \Illuminate\Support\Collection */
    private $dailyData;       // collection of arrays: date, revenue, occupancy, arr, rooms_sold
    private $kpiData;
    private $monthName;       // e.g. "October 2025"
    private $property;
    /** @var \Illuminate\Support\Collection */
    private $miceBookings;

    public function __construct(string $monthName, $dailyData, $kpiData, $property, Collection $miceBookings)
    {
        $this->monthName    = $monthName;
        $this->dailyData    = $dailyData instanceof Collection ? $dailyData : collect($dailyData);
        $this->kpiData      = $kpiData;
        $this->property     = $property;
        $this->miceBookings = $miceBookings;

        // Pastikan data harian lengkap dari tanggal 1 s/d akhir bulan (agar chart mulai 01)
        $this->dailyData = $this->padDailyDataToFullMonth($this->dailyData, $this->monthName);
    }

    /**
     * Memastikan $dailyData berisi semua tanggal 1..akhir bulan berdasarkan $monthName.
     * Jika suatu tanggal tidak ada, diisi nilai 0.
     */
    private function padDailyDataToFullMonth(Collection $dailyData, string $monthName): Collection
    {
        // Contoh monthName: "October 2025" --> aman di-parse Carbon
        $firstDay = Carbon::parse("1 {$monthName}")->startOfMonth();
        $lastDay  = $firstDay->copy()->endOfMonth();

        // Normalisasi index by Y-m-d agar pencarian cepat
        $indexed = collect();
        foreach ($dailyData as $row) {
            // Terima format 'YYYY-MM-DD' atau 'DD MMM YYYY'
            $dateStr = $row['date'];
            try {
                $key = Carbon::parse($dateStr)->format('Y-m-d');
            } catch (\Exception $e) {
                $key = $firstDay->format('Y-m-d'); // fallback agar tidak error
            }
            $indexed[$key] = [
                'date'       => Carbon::parse($key)->isoFormat('DD MMM YYYY'),
                'revenue'    => (float)($row['revenue'] ?? 0),
                'occupancy'  => (float)($row['occupancy'] ?? 0),
                'arr'        => (float)($row['arr'] ?? 0),
                'rooms_sold' => (int)($row['rooms_sold'] ?? 0),
            ];
        }

        // Bangun koleksi lengkap 1..end
        $filled = collect();
        for ($d = $firstDay->copy(); $d->lte($lastDay); $d->addDay()) {
            $key = $d->format('Y-m-d');
            if (isset($indexed[$key])) {
                $filled->push($indexed[$key]);
            } else {
                $filled->push([
                    'date'       => $d->isoFormat('DD MMM YYYY'),
                    'revenue'    => 0.0,
                    'occupancy'  => 0.0, // dalam fraksi (0..1) nanti diformat %
                    'arr'        => 0.0,
                    'rooms_sold' => 0,
                ]);
            }
        }
        return $filled;
    }

    private function hexToArgb(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return 'FF' . strtoupper($hex);
    }

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
        $adj = function ($c) use ($factor) {
            $target = ($factor >= 0) ? 255 : 0;
            $delta  = ($target - $c) * abs($factor);
            $val    = (int) round($c + $delta);
            return max(0, min(255, $val));
        };
        return sprintf('#%02X%02X%02X', $adj($r), $adj($g), $adj($b));
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
        $revenueDetails = $this->kpiData['roomRevenueBreakdown'] ?? [];
        $revenueDetails['Total Pendapatan Kamar'] = (float) ($this->kpiData['totalRoomRevenue'] ?? 0);
        $revenueDetails[' '] = null; // spacer

        $fbBreakdown = $this->kpiData['fbRevenueBreakdown'] ?? [];
        foreach ($fbBreakdown as $key => $value) {
            if ($value > 0 || $key === 'Breakfast Lain') {
                $revenueDetails[$key] = (float) $value;
            }
        }
        $revenueDetails['Total F&B'] = (float) ($this->kpiData['totalFbRevenue'] ?? 0);
        if (!empty($this->kpiData['miceRevenue'])) {
            $revenueDetails['MICE/Event'] = (float) $this->kpiData['miceRevenue'];
        }
        $revenueDetails['Lain-lain'] = (float) ($this->kpiData['totalOtherRevenue'] ?? 0);
        return $revenueDetails;
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

        // Header utama
        $data[] = ['Laporan Analisis Kinerja (KPI)'];
        $data[] = ['Properti: ' . ($this->property->name ?? 'Semua Properti')];
        $data[] = ['Bulan: ' . $this->monthName];
        $data[] = []; // spacer visual
        $data[] = ['METRIK UTAMA', null, null, 'RINCIAN PENDAPATAN', null, null, 'RINCIAN KAMAR TERJUAL', null];

        $kpiItems         = $this->getKpiItems();
        $revenueDetails   = $this->getRevenueDetails();
        $roomsSoldDetails = $this->getRoomsSoldDetails();
        $maxRows = max(count($kpiItems), count($revenueDetails), count($roomsSoldDetails));

        for ($i = 0; $i < $maxRows; $i++) {
            $data[] = [
                array_keys($kpiItems)[$i] ?? null,
                isset(array_keys($kpiItems)[$i]) ? $kpiItems[array_keys($kpiItems)[$i]] : null, null,

                array_keys($revenueDetails)[$i] ?? null,
                isset(array_keys($revenueDetails)[$i]) ? $revenueDetails[array_keys($revenueDetails)[$i]] : null, null,

                array_keys($roomsSoldDetails)[$i] ?? null,
                isset(array_keys($roomsSoldDetails)[$i]) ? $roomsSoldDetails[array_keys($roomsSoldDetails)[$i]] : null,
            ];
        }

        // === Tabel Rincian Harian ===
        $data[] = ['Tabel Rincian Harian'];
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

        // >>> Sisipkan PERSIS 2 baris kosong setelah data harian terakhir
        $data[] = array_fill(0, 5, '');
        $data[] = array_fill(0, 5, '');

        // === Tabel Rincian MICE (opsional) ===
        if ($this->miceBookings->isNotEmpty()) {
            $data[] = ['Rincian MICE/Event']; // judul
            $data[] = ['Nama Klien', 'Properti', 'Tanggal Event', 'Total Harga']; // header
            foreach ($this->miceBookings as $booking) {
                $data[] = [
                    $booking->client_name,
                    $booking->property->name ?? 'N/A',
                    Carbon::parse($booking->event_date)->isoFormat('D MMMM YYYY'),
                    (float) $booking->total_price,
                ];
            }
        }

        return $data;
    }

    /** Cari baris yang berisi $text pada kolom $col (default A). */
    private function findRowByText(Worksheet $sheet, string $text, string $col = 'A'): ?int
    {
        $last = $sheet->getHighestRow();
        for ($r = 1; $r <= $last; $r++) {
            if (trim((string) $sheet->getCell("{$col}{$r}")->getValue()) === $text) {
                return $r;
            }
        }
        return null;
    }

    public function styles(Worksheet $sheet)
    {
        // Format angka
        $currencyFormat = '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)';
        $percentFormat  = '0.00%';
        $numberFormat   = '#,##0';

        // Border & warna
        $borderStyle = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $allBorders  = ['allBorders' => ['borderStyle' => $borderStyle]];
        $baseHex     = ($this->property && !empty($this->property->chart_color)) ? $this->property->chart_color : '#1F4E78';
        $headerHex   = $baseHex;
        $titleHex    = $this->adjustHex($baseHex, -25);

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'fill'      => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => $this->hexToArgb($headerHex)]
            ],
            'borders'   => $allBorders
        ];

        $mainTitleStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => $this->hexToArgb($titleHex)]
            ],
        ];

        // Header besar
        $sheet->mergeCells('A1:H1')->getStyle('A1')->applyFromArray($mainTitleStyle)->getFont()->setSize(16);
        $sheet->mergeCells('A2:H2')->getStyle('A2')->applyFromArray($mainTitleStyle);
        $sheet->mergeCells('A3:H3')->getStyle('A3')->applyFromArray($mainTitleStyle);

        // Format B5 & E5 ke Rupiah
        $sheet->getStyle('B5')->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle('E5')->getNumberFormat()->setFormatCode($currencyFormat);

        // Blok KPI/Details
        $maxRows        = max(count($this->getKpiItems()), count($this->getRevenueDetails()), count($this->getRoomsSoldDetails()));
        $headerRow3Col  = 5;
        $firstDetailRow = 6;
        $lastDetailRow  = 5 + $maxRows;

        $sheet->getStyle("A{$headerRow3Col}:H{$headerRow3Col}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow3Col}:B{$headerRow3Col}")->getBorders()->applyFromArray($allBorders);
        $sheet->getStyle("D{$headerRow3Col}:E{$headerRow3Col}")->getBorders()->applyFromArray($allBorders);
        $sheet->getStyle("G{$headerRow3Col}:H{$headerRow3Col}")->getBorders()->applyFromArray($allBorders);

        $sheet->getStyle("B{$firstDetailRow}:B{$lastDetailRow}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("E{$firstDetailRow}:E{$lastDetailRow}")->getNumberFormat()->setFormatCode($currencyFormat);
        $sheet->getStyle("H{$firstDetailRow}:H{$lastDetailRow}")->getNumberFormat()->setFormatCode($numberFormat);

        for ($r = $firstDetailRow; $r <= $lastDetailRow; $r++) {
            if (trim((string) $sheet->getCell("A{$r}")->getValue()) !== '') {
                $sheet->getStyle("A{$r}:B{$r}")->getBorders()->applyFromArray($allBorders);
                if (trim((string) $sheet->getCell("A{$r}")->getValue()) === 'Okupansi Rata-rata') {
                    $sheet->getStyle("B{$r}")->getNumberFormat()->setFormatCode($percentFormat);
                }
            }
            if (trim((string) $sheet->getCell("D{$r}")->getValue()) !== '' && trim((string) $sheet->getCell("D{$r}")->getValue()) !== ' ') {
                $style = $sheet->getStyle("D{$r}:E{$r}");
                $style->getBorders()->applyFromArray($allBorders);
                if (in_array(trim((string) $sheet->getCell("D{$r}")->getValue()), ['Total Pendapatan Kamar', 'Total F&B'])) {
                    $style->getFont()->setBold(true);
                }
            }
            if (trim((string) $sheet->getCell("G{$r}")->getValue()) !== '') {
                $style = $sheet->getStyle("G{$r}:H{$r}");
                $style->getBorders()->applyFromArray($allBorders);
                if (trim((string) $sheet->getCell("G{$r}")->getValue()) === 'Total Kamar Terjual') {
                    $style->getFont()->setBold(true);
                }
            }
        }

        // === Tabel Harian (deteksi baris dinamis) ===
        $dailyTitleRow = $this->findRowByText($sheet, 'Tabel Rincian Harian', 'A');
        if ($dailyTitleRow !== null) {
            $dailyHeaderRow = $dailyTitleRow + 1;
            $firstDataRow   = $dailyHeaderRow + 1;
            $dataCount      = (int) $this->dailyData->count();
            $lastDataRow    = $firstDataRow + $dataCount - 1;

            // Judul & header
            $sheet->getStyle("A{$dailyTitleRow}:E{$dailyTitleRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$dailyHeaderRow}:E{$dailyHeaderRow}")->applyFromArray($headerStyle);

            // Bodi
            if ($dataCount > 0) {
                $sheet->getStyle("A{$firstDataRow}:E{$lastDataRow}")->getBorders()->applyFromArray($allBorders);
                $sheet->getStyle("B{$firstDataRow}:B{$lastDataRow}")->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle("C{$firstDataRow}:C{$lastDataRow}")->getNumberFormat()->setFormatCode($percentFormat);
                $sheet->getStyle("D{$firstDataRow}:D{$lastDataRow}")->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle("E{$firstDataRow}:E{$lastDataRow}")->getNumberFormat()->setFormatCode($numberFormat);
            }
        }

        // === Tabel MICE/Event (header merah & format) ===
        if ($this->miceBookings->isNotEmpty()) {
            $miceTitleRow = $this->findRowByText($sheet, 'Rincian MICE/Event', 'A');
            if ($miceTitleRow !== null) {
                // Judul bold
                $sheet->getStyle("A{$miceTitleRow}:D{$miceTitleRow}")->getFont()->setBold(true);

                // Header 1 baris di bawah judul
                $miceHeaderRow = $miceTitleRow + 1;
                $sheet->getStyle("A{$miceHeaderRow}:D{$miceHeaderRow}")->applyFromArray($headerStyle);
                $sheet->getStyle("A{$miceHeaderRow}:D{$miceHeaderRow}")->getBorders()->applyFromArray($allBorders);

                // Body
                $miceCount = $this->miceBookings->count();
                if ($miceCount > 0) {
                    $firstMiceRow = $miceHeaderRow + 1;
                    $lastMiceRow  = $firstMiceRow + $miceCount - 1;

                    $sheet->getStyle("A{$firstMiceRow}:D{$lastMiceRow}")->getBorders()->applyFromArray($allBorders);
                    $sheet->getStyle("D{$firstMiceRow}:D{$lastMiceRow}")
                          ->getNumberFormat()->setFormatCode($currencyFormat);
                }
            }
        }
    }

    public function charts()
    {
        if ($this->dailyData->isEmpty()) {
            return [];
        }

        // Posisi berdasarkan struktur array(): Judul -> Header -> Data
        $maxRows       = max(count($this->getKpiItems()), count($this->getRevenueDetails()), count($this->getRoomsSoldDetails()));
        $lastDetailRow = 5 + $maxRows;

        $dailyTitleRow  = $lastDetailRow + 1;         // "Tabel Rincian Harian"
        $dailyHeaderRow = $dailyTitleRow + 1;         // header tabel
        $firstDataRow   = $dailyHeaderRow + 1;        // data pertama = tanggal 01
        $dataRowCount   = $this->dailyData->count();
        $lastDataRow    = $firstDataRow + $dataRowCount - 1;

        $sheetName = $this->title();

        $categories = [
            new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_STRING,
                "'{$sheetName}'!\$A\${$firstDataRow}:\$A\${$lastDataRow}",
                null,
                $dataRowCount
            )
        ];
        $labels = [
            new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_STRING,
                "'{$sheetName}'!\$B\${$dailyHeaderRow}",
                null,
                1
            )
        ];
        $values = [
            new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_NUMBER,
                "'{$sheetName}'!\$B\${$firstDataRow}:\$B\${$lastDataRow}",
                null,
                $dataRowCount
            )
        ];

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($values) - 1),
            $labels,
            $categories,
            $values
        );
        $series->setPlotDirection(DataSeries::DIRECTION_COL);

        $plot   = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $title  = new Title('Pendapatan Harian');

        $chart = new Chart(
            'chart_' . str_replace(' ', '_', $this->monthName),
            $title,
            $legend,
            $plot
        );
        $chart->setTopLeftPosition('J5');
        $chart->setBottomRightPosition('T25');

        return [$chart];
    }
}
