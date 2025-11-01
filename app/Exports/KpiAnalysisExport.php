<?php

namespace App\Exports;

// [PERBAIKAN] Pastikan namespace sheet ini benar
use App\Exports\Sheets\KpiAnalysisMonthlySheet; 
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Property;
use Carbon\CarbonPeriod;

class KpiAnalysisExport implements WithMultipleSheets
{
    use Exportable;

    protected $kpiData;
    protected $dailyData;
    protected $selectedProperty;
    protected $filteredIncomes;
    protected $miceBookings;

    // Constructor ini sudah benar
    public function __construct($kpiData, Collection $dailyData, ?Property $selectedProperty, Collection $filteredIncomes, Collection $miceBookings)
    {
        $this->kpiData = $kpiData;
        $this->dailyData = $dailyData;
        $this->selectedProperty = $selectedProperty;
        $this->filteredIncomes = $filteredIncomes;
        $this->miceBookings = $miceBookings;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // ==========================================================
        // >> AWAL PERUBAHAN <<
        //
        // Logika looping per bulan dihapus. Kita sekarang berasumsi
        // bahwa $kpiData dan $dailyData yang diterima dari DashboardController
        // sudah mencakup seluruh rentang tanggal yang diinginkan.
        //
        // ==========================================================

        // 1. Tentukan Judul Sheet (Bulan)
        $monthName = "Analisis KPI"; // Default
        if ($this->dailyData->isNotEmpty()) {
            // Coba dapatkan rentang tanggal dari data harian
            try {
                $firstDate = Carbon::createFromFormat('d M Y', $this->dailyData->first()['date']);
                $lastDate = Carbon::createFromFormat('d M Y', $this->dailyData->last()['date']);
                
                if ($firstDate->format('Y-m') === $lastDate->format('Y-m')) {
                    $monthName = $firstDate->isoFormat('MMMM YYYY');
                } else {
                    $monthName = $firstDate->isoFormat('MMM YYYY') . ' - ' . $lastDate->isoFormat('MMM YYYY');
                }
            } catch (\Exception $e) {
                // Gunakan nama default jika format tanggal tidak sesuai
                $monthName = "Analisis KPI";
            }
        }

        // 2. Langsung panggil Sheet dengan data yang sudah dihitung (benar)
        $sheets[] = new \App\Exports\Sheets\KpiAnalysisMonthlySheet(
            $monthName,
            $this->dailyData,       // Data harian yang sudah benar
            $this->kpiData,         // Data KPI yang sudah benar
            $this->selectedProperty,
            $this->miceBookings     // Data MICE
        );
        
        // ==========================================================
        // >> AKHIR PERUBAHAN <<
        // ==========================================================

        return $sheets;
    }

    // [FUNGSI DIHAPUS] private function calculateMonthlyKpi(...)
    // [FUNGSI DIHAPUS] private function padMonthlyDailyData(...)
}