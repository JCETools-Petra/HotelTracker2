<?php

namespace App\Exports;

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
    protected $miceBookings; // <-- [PERBAIKAN] Properti baru untuk menampung data MICE

    // [PERBAIKAN] Constructor diubah untuk menerima $miceBookings
    public function __construct($kpiData, Collection $dailyData, ?Property $selectedProperty, Collection $filteredIncomes, Collection $miceBookings)
    {
        $this->kpiData = $kpiData;
        $this->dailyData = $dailyData;
        $this->selectedProperty = $selectedProperty;
        $this->filteredIncomes = $filteredIncomes;
        $this->miceBookings = $miceBookings; // <-- Simpan data MICE
    }

    public function sheets(): array
    {
        $sheets = [];
    
        $incomesByMonth = $this->filteredIncomes->groupBy(function ($income) {
            return Carbon::parse($income->date)->format('Y-m');
        });
    
        foreach ($incomesByMonth as $month => $monthlyIncomes) {
            $firstDayOfMonth = Carbon::parse($month . '-01')->startOfMonth();
            $lastDayOfMonth  = Carbon::parse($month . '-01')->endOfMonth();
            $monthName       = $firstDayOfMonth->isoFormat('MMMM YYYY');
    
            $monthlyKpiData = $this->calculateMonthlyKpi($monthlyIncomes, $firstDayOfMonth, $lastDayOfMonth);
    
            $rawMonthlyDaily = $this->dailyData->filter(function ($item) use ($month) {
                // Perbaiki format parsing tanggal agar konsisten
                return Carbon::createFromFormat('d M Y', $item['date'])->format('Y-m') === $month;
            })->values();

            $monthlyDailyData = $this->padMonthlyDailyData($rawMonthlyDaily, $firstDayOfMonth, $lastDayOfMonth);

            // [PERBAIKAN] Filter data MICE khusus untuk bulan ini
            $monthlyMiceBookings = $this->miceBookings->filter(function ($booking) use ($month) {
                return Carbon::parse($booking->event_date)->format('Y-m') === $month;
            });
            
            // [PERBAIKAN] Kirim data MICE yang sudah difilter ke class Sheet
            $sheets[] = new \App\Exports\Sheets\KpiAnalysisMonthlySheet(
                $monthName,
                $monthlyDailyData,
                $monthlyKpiData,
                $this->selectedProperty,
                $monthlyMiceBookings // <-- Argumen baru
            );
        }
    
        return $sheets;
    }

    private function calculateMonthlyKpi(Collection $monthlyIncomes, Carbon $firstDayOfMonth, Carbon $lastDayOfMonth)
    {
        $totalRoomsSold = $monthlyIncomes->sum('total_rooms_sold');
        $totalRoomRevenue = $monthlyIncomes->sum('total_rooms_revenue');

        $avgOccupancy = $monthlyIncomes->avg('occupancy');
        $avgArr = ($totalRoomsSold > 0) ? ($totalRoomRevenue / $totalRoomsSold) : 0;
        
        $totalLunchAndDinnerRevenue = $monthlyIncomes->sum('lunch_income') + $monthlyIncomes->sum('dinner_income');
        $restoRevenuePerRoom = ($totalRoomsSold > 0) ? ($totalLunchAndDinnerRevenue / $totalRoomsSold) : 0;
        
        $numberOfDays = $firstDayOfMonth->diffInDays($lastDayOfMonth) + 1;
        $totalAvailableRooms = 0;
        if ($this->selectedProperty) {
            $totalAvailableRooms = $this->selectedProperty->total_rooms * $numberOfDays;
        } else {
            $totalAvailableRooms = Property::sum('total_rooms') * $numberOfDays;
        }
        $revPar = ($totalAvailableRooms > 0) ? ($totalRoomRevenue / $totalAvailableRooms) : 0;

        return [
            'totalRevenue' => $monthlyIncomes->sum('total_revenue'),
            'totalRoomsSold' => $totalRoomsSold,
            'avgOccupancy' => $avgOccupancy,
            'avgArr' => $avgArr,
            'revPar' => $revPar,
            'restoRevenuePerRoom' => $restoRevenuePerRoom,
            'totalRoomRevenue' => $totalRoomRevenue,
            'totalFbRevenue' => $monthlyIncomes->sum('total_fb_revenue'),
            'totalOtherRevenue' => $monthlyIncomes->sum('others_income'),
            'totalBreakfastRevenue' => $monthlyIncomes->sum('breakfast_income'),
            'totalLunchRevenue' => $monthlyIncomes->sum('lunch_income'),
            'totalDinnerRevenue' => $monthlyIncomes->sum('dinner_income'),
            'revenueBreakdown' => [
                'Offline' => $monthlyIncomes->sum('offline_room_income'), 'Online' => $monthlyIncomes->sum('online_room_income'),
                'Travel Agent' => $monthlyIncomes->sum('ta_income'), 'Government' => $monthlyIncomes->sum('gov_income'),
                'Corporate' => $monthlyIncomes->sum('corp_income'), 'Afiliasi' => $monthlyIncomes->sum('afiliasi_room_income'),
                'MICE/Event' => $monthlyIncomes->sum('mice_room_income'),
            ],
            'roomsSoldBreakdown' => [
                'Offline' => $monthlyIncomes->sum('offline_rooms'), 'Online' => $monthlyIncomes->sum('online_rooms'),
                'Travel Agent' => $monthlyIncomes->sum('ta_rooms'), 'Government' => $monthlyIncomes->sum('gov_rooms'),
                'Corporate' => $monthlyIncomes->sum('corp_rooms'), 'Afiliasi' => $monthlyIncomes->sum('afiliasi_rooms'),
                'House Use' => $monthlyIncomes->sum('house_use_rooms'), 'Compliment' => $monthlyIncomes->sum('compliment_rooms'),
            ],
        ];
    }
    
    private function padMonthlyDailyData(Collection $daily, Carbon $firstDay, Carbon $lastDay): Collection
    {
        $byYmd = $daily->keyBy(function ($row) {
            // Perbaiki format parsing tanggal agar konsisten
            return Carbon::createFromFormat('d M Y', $row['date'])->format('Y-m-d');
        });
    
        $filled = collect();
        $period = CarbonPeriod::create($firstDay, $lastDay);
    
        foreach ($period as $d) {
            $key = $d->format('Y-m-d');
    
            if (isset($byYmd[$key])) {
                $row = $byYmd[$key];
                $row['date'] = $d->format('d M Y');
                $filled->push($row);
            } else {
                $filled->push([
                    'date'       => $d->format('d M Y'),
                    'revenue'    => 0,
                    'occupancy'  => 0,
                    'arr'        => 0,
                    'rooms_sold' => 0,
                ]);
            }
        }
    
        return $filled->values();
    }
}