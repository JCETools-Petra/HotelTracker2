<?php

namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class KpiAnalysisMonthlySheet implements FromView, WithTitle, WithStyles, WithCharts
{
    private $monthlyData;
    private $kpiData;
    private $monthName;
    private $selectedProperty;

    public function __construct(string $monthName, $monthlyData, $kpiData, $selectedProperty)
    {
        $this->monthName = $monthName;
        $this->monthlyData = $monthlyData;
        $this->kpiData = $kpiData;
        $this->selectedProperty = $selectedProperty;
    }

    public function title(): string
    {
        return $this->monthName;
    }

    public function view(): View
    {
        return view('exports.kpi_analysis_monthly', [
            'dailyData' => $this->monthlyData,
            'kpiData' => $this->kpiData,
            'monthName' => $this->monthName,
            'selectedProperty' => $this->selectedProperty,
        ]);
    }

    /**
     * ======================= GANTI SELURUH FUNGSI INI =======================
     * Menambahkan styling profesional ke worksheet dengan format angka yang presisi.
     */
    public function styles(Worksheet $sheet)
    {
        // 1. Style untuk Judul Laporan
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('2F5597');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('A2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('2F5597');
    
        // 2. Style untuk Header bagian Ringkasan
        $sheet->getStyle('A4:F4')->getFont()->setBold(true);
    
        // 3. Definisikan format angka yang akan digunakan
        $currencyFormat = '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"??_);_(@_)';
        // PERBAIKAN: Format ini akan menampilkan angka apa adanya dan menambahkan simbol % di belakang.
        $percentFormat = '0.00"%"';
        $numberFormat = '#,##0';
    
        // 4. Terapkan format secara spesifik pada sel ringkasan
        $sheet->getStyle('B5')->getNumberFormat()->setFormatCode($currencyFormat); // Total Pendapatan
        $sheet->getStyle('B6')->getNumberFormat()->setFormatCode($percentFormat);  // Okupansi
        $sheet->getStyle('B7')->getNumberFormat()->setFormatCode($currencyFormat); // ARR
        $sheet->getStyle('B8')->getNumberFormat()->setFormatCode($currencyFormat); // RevPAR
        $sheet->getStyle('B9')->getNumberFormat()->setFormatCode($numberFormat);   // Total Kamar Terjual
        $sheet->getStyle('D5:D11')->getNumberFormat()->setFormatCode($currencyFormat); // Rincian Pendapatan
        $sheet->getStyle('F5:F12')->getNumberFormat()->setFormatCode($numberFormat);   // Rincian Kamar Terjual
    
        // 5. Terapkan format pada tabel data harian
        $headerRow = 15;
        $firstDataRow = 16;
        $lastDataRow = $headerRow + $this->monthlyData->count();
        
        $sheet->getStyle('A'.$headerRow.':E'.$headerRow)->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('A'.$headerRow.':E'.$headerRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4F81BD');
    
        if ($this->monthlyData->isNotEmpty()) {
            $sheet->getStyle('B'.$firstDataRow.':C'.$lastDataRow)->getNumberFormat()->setFormatCode($currencyFormat); // Pendapatan & ARR
            $sheet->getStyle('D'.$firstDataRow.':D'.$lastDataRow)->getNumberFormat()->setFormatCode($percentFormat);  // Okupansi
            $sheet->getStyle('E'.$firstDataRow.':E'.$lastDataRow)->getNumberFormat()->setFormatCode($numberFormat);   // Kamar Terjual
        }
        
        // 6. Auto-size kolom
        foreach (range('A', 'O') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    public function charts()
    {
        // Method charts sudah benar, tidak perlu diubah
        if ($this->monthlyData->isEmpty()) {
            return [];
        }
        $dataRowCount = $this->monthlyData->count();
        $headerRow = 15;
        $firstDataRow = 16;
        $lastDataRow = $firstDataRow + $dataRowCount - 1;
        $sheetName = $this->title();
        $chartLabels = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'{$sheetName}'!\$A\${$firstDataRow}:\$A\${$lastDataRow}", null, $dataRowCount)];
        $chartCategories = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'{$sheetName}'!\$B\${$headerRow}", null, 1)];
        $chartValues = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'{$sheetName}'!\$B\${$firstDataRow}:\$B\${$lastDataRow}", null, $dataRowCount)];
        $series = new DataSeries(DataSeries::TYPE_BARCHART, DataSeries::GROUPING_CLUSTERED, range(0, count($chartValues) - 1), $chartLabels, $chartCategories, $chartValues);
        $plotArea = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        $title = new Title('Pendapatan Harian');
        $yAxisLabel = new Title('Pendapatan (Rp)');
        $chart = new Chart('chart_'.str_replace(' ', '_', $this->monthName), $title, $legend, $plotArea, true, 0, null, $yAxisLabel);
        $chart->setTopLeftPosition('G2');
        $chart->setBottomRightPosition('O20');
        return $chart;
    }
}