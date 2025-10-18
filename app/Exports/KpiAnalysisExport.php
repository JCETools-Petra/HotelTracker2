<?php

namespace App\Exports;

use App\Exports\Sheets\KpiAnalysisMonthlySheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Property;
use App\Models\HotelRoom;

class KpiAnalysisExport implements WithMultipleSheets
{
    use Exportable;

    protected $filteredIncomes;
    protected $selectedProperty;

    public function __construct(Collection $filteredIncomes, $selectedProperty)
    {
        $this->filteredIncomes = $filteredIncomes;
        $this->selectedProperty = $selectedProperty;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        $incomesByMonth = $this->filteredIncomes->groupBy(function ($income) {
            return Carbon::parse($income->date)->format('Y-m');
        });

        foreach ($incomesByMonth as $month => $monthlyIncomes) {
            $monthName = Carbon::parse($month . '-01')->isoFormat('MMMM YYYY');
            
            $kpiData = $this->calculateMonthlyKpi($monthlyIncomes);
            
            $dailyData = $monthlyIncomes->sortBy('date')->map(function ($income) {
                return [
                    'date' => Carbon::parse($income->date)->format('d M Y'),
                    'revenue' => $income->total_revenue,
                    'arr' => $income->arr,
                    'occupancy' => round($income->occupancy, 2),
                    'rooms_sold' => $income->total_rooms_sold
                ];
            });

            $sheets[] = new KpiAnalysisMonthlySheet($monthName, $dailyData, $kpiData, $this->selectedProperty);
        }

        return $sheets;
    }

    private function calculateMonthlyKpi(Collection $monthlyIncomes)
    {
        $totalRoomsSold = $monthlyIncomes->sum('total_rooms_sold');
        $totalRoomRevenue = $monthlyIncomes->sum('total_rooms_revenue');
        
        // Menghitung Resto Revenue (Hanya Lunch & Dinner) per Kamar Terjual
        $totalLunchAndDinnerRevenue = $monthlyIncomes->sum('lunch_income') + $monthlyIncomes->sum('dinner_income');
        $restoRevenuePerRoom = ($totalRoomsSold > 0) ? ($totalLunchAndDinnerRevenue / $totalRoomsSold) : 0;
        
        $firstDayOfMonth = Carbon::parse($monthlyIncomes->first()->date)->startOfMonth();
        $lastDayOfMonth = Carbon::parse($monthlyIncomes->first()->date)->endOfMonth();
        $numberOfDays = $firstDayOfMonth->diffInDays($lastDayOfMonth) + 1;

        if ($this->selectedProperty) {
            $totalAvailableRooms = $this->selectedProperty->total_rooms * $numberOfDays;
        } else {
            $totalAvailableRooms = Property::sum('total_rooms') * $numberOfDays;
        }

        return [
            'totalRevenue' => $monthlyIncomes->sum('total_revenue'),
            'totalRoomsSold' => $totalRoomsSold,
            'avgOccupancy' => $monthlyIncomes->avg('occupancy'),
            'avgArr' => ($totalRoomsSold > 0 ? ($totalRoomRevenue / $totalRoomsSold) : 0),
            'revPar' => ($totalAvailableRooms > 0 ? ($totalRoomRevenue / $totalAvailableRooms) : 0),
            'restoRevenuePerRoom' => $restoRevenuePerRoom,

            // Data Tambahan untuk Rincian di Excel
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
}