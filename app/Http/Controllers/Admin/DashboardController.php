<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\DailyIncome;
use App\Models\RevenueTarget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\DailyOccupancy;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\HotelRoom;
use App\Models\Booking;
use App\Models\PricePackage;
use App\Exports\AdminPropertiesSummaryExport;
use App\Exports\KpiAnalysisExport;
use App\Exports\DashboardExport;
use Illuminate\Support\Collection;
use Illuminate\Support\Str; // Pastikan 'Str' di-import

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return $this->index($request);
    }

    // =========================================================================
    // FUNGSI INDEX (DASHBOARD UTAMA)
    // =========================================================================
    public function index(Request $request)
    {
        // 1. Pengaturan Filter Tanggal
        $propertyId = $request->input('property_id');
        $period = $request->input('period', 'month');

        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $period = 'custom';
        } else {
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                default:
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
            }
        }

        // 2. Definisi Kategori Pendapatan
        $incomeCategories = [
            'offline_room_income' => 'Walk In', 'online_room_income' => 'OTA', 'ta_income' => 'Travel Agent',
            'gov_income' => 'Government', 'corp_income' => 'Corporation', 'compliment_income' => 'Compliment',
            'house_use_income' => 'House Use', 'afiliasi_room_income' => 'Afiliasi',
            'breakfast_income' => 'Breakfast', 'lunch_income' => 'Lunch', 'dinner_income' => 'Dinner',
            'others_income' => 'Lain-lain',
        ];
        $incomeColumns = array_keys($incomeCategories);
        $roomCountColumns = ['offline_rooms', 'online_rooms', 'ta_rooms', 'gov_rooms', 'corp_rooms', 'compliment_rooms', 'house_use_rooms', 'afiliasi_rooms'];
        
        // ==========================================================
        // >> AWAL LOGIKA (SUNNYDAY INN) <<
        // ==========================================================

        $dateFilter = fn ($query) => $query->whereBetween('date', [$startDate, $endDate]);

        // Definisikan nama properti (SESUAIKAN DENGAN DB ANDA)
        $sunnydayInnName = 'Sunnyday Inn';
        $sourcePropertyNames = ['Hotel Akat', 'Hotel Ermasu', 'Bell Hotel'];

        $allProperties = Property::get(['id', 'name']);
        $sourcePropertyIds = $allProperties->whereIn('name', $sourcePropertyNames)->pluck('id');
        $sunnydayInnId = $allProperties->firstWhere('name', $sunnydayInnName)->id ?? null;

        $totalRedirectedBreakfast = DailyIncome::whereIn('property_id', $sourcePropertyIds)
            ->where($dateFilter)
            ->sum('breakfast_income');

        // ==========================================================
        // >> AKHIR LOGIKA (SUNNYDAY INN) <<
        // ==========================================================


        // 4. Mengambil Data Properti dengan Semua Kalkulasi
        $propertiesQuery = Property::when($propertyId, fn ($q) => $q->where('id', $propertyId))->orderBy('id', 'asc');

        foreach ($incomeColumns as $column) {
            $propertiesQuery->withSum(['dailyIncomes as total_' . $column => $dateFilter], $column);
        }
        foreach ($roomCountColumns as $column) {
            $propertiesQuery->withSum(['dailyIncomes as total_' . $column => $dateFilter], $column);
        }
        $properties = $propertiesQuery->get();

        $miceRevenues = Booking::where('status', 'Booking Pasti')
            ->whereBetween('event_date', [$startDate, $endDate])
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->select('property_id', 'mice_category_id', DB::raw('SUM(total_price) as total_mice_revenue'))
            ->groupBy('property_id', 'mice_category_id')
            ->with('miceCategory:id,name')
            ->get()
            ->groupBy('property_id');

        $totalOverallRevenue = 0;

        foreach ($properties as $property) {
            $dailyRevenue = collect($incomeColumns)->reduce(fn ($carry, $col) => $carry + ($property->{'total_' . $col} ?? 0), 0);

            $propertyMiceRevenues = $miceRevenues->get($property->id);
            if ($propertyMiceRevenues) {
                $miceTotalForProperty = $propertyMiceRevenues->sum('total_mice_revenue');
                $dailyRevenue += $miceTotalForProperty;
                $property->mice_revenue_breakdown = $propertyMiceRevenues;
            } else {
                $property->mice_revenue_breakdown = collect();
            }

            // ==========================================================
            // >> AWAL PERUBAHAN (KALKULASI TOTAL) <<
            // ==========================================================
            
            $property->other_breakfast_revenue = 0;

            if ($sourcePropertyIds->contains($property->id)) {
                // Ini adalah Akat, Ermasu, atau Bell. 
                // 1. Kurangi total revenue mereka dengan breakfast
                $dailyRevenue -= $property->total_breakfast_income;
                
                // 2. [BARIS DIHAPUS] Baris "$property->total_breakfast_income = 0;" 
                //    dihapus agar nilainya tetap tampil di kartu.
            }

            if ($property->id === $sunnydayInnId) {
                // Ini adalah Sunnyday Inn.
                $dailyRevenue += $totalRedirectedBreakfast;
                $property->other_breakfast_revenue = $totalRedirectedBreakfast;
            }
            
            // ==========================================================
            // >> AKHIR PERUBAHAN (KALKULASI TOTAL) <<
            // ==========================================================

            $property->dailyRevenue = $dailyRevenue;
            $totalOverallRevenue += $dailyRevenue;

            $totalArrRevenue = 0;
            $totalArrRoomsSold = 0;
            $arrRevenueCategories = ['offline_room_income', 'online_room_income', 'ta_income', 'gov_income', 'corp_income'];
            $arrRoomsCategories = ['offline_rooms', 'online_rooms', 'ta_rooms', 'gov_rooms', 'corp_rooms'];
            foreach ($arrRevenueCategories as $cat) {
                $totalArrRevenue += $property->{'total_' . $cat} ?? 0;
            }
            foreach ($arrRoomsCategories as $cat) {
                $totalArrRoomsSold += $property->{'total_' . $cat} ?? 0;
            }
            $property->averageRoomRate = ($totalArrRoomsSold > 0) ? ($totalArrRevenue / $totalArrRoomsSold) : 0;
        }
        
        // 5. Menyiapkan Data untuk Chart
        $pieChartCategories = [
            'offline_room_income' => 'Walk In', 'online_room_income' => 'OTA', 'ta_income' => 'Travel Agent',
            'gov_income' => 'Government', 'corp_income' => 'Corporation', 'afiliasi_room_income' => 'Afiliasi',
            'mice_income' => 'MICE', 'fnb_income' => 'F&B', 'others_income' => 'Lain-lain',
            'other_breakfast_revenue' => 'Breakfast Lain',
        ];

        $pieChartDataSource = new \stdClass();
        foreach ($pieChartCategories as $key => $label) {
            $totalKey = 'total_' . $key;
            if ($key === 'mice_income') {
                $pieChartDataSource->$totalKey = $miceRevenues->flatten()->sum('total_mice_revenue');
            } 
            else if ($key === 'fnb_income') {
                // ==========================================================
                // >> PERUBAHAN (PIE CHART F&B) <<
                // Kita kurangi total F&B dengan breakfast yang dialihkan,
                // karena breakfast itu akan dihitung di 'other_breakfast_revenue'
                // ==========================================================
                $original_fnb_total = $properties->sum('total_breakfast_income') + $properties->sum('total_lunch_income') + $properties->sum('total_dinner_income');
                $pieChartDataSource->$totalKey = $original_fnb_total - $totalRedirectedBreakfast; // <-- DIKURANGI
            } 
            else if ($key === 'other_breakfast_revenue') {
                $pieChartDataSource->$totalKey = $totalRedirectedBreakfast;
            } 
            else {
                $pieChartDataSource->$totalKey = $properties->sum($totalKey);
            }
        }

        $recentMiceBookings = Booking::with(['property', 'miceCategory'])
            ->where('status', 'Booking Pasti')
            ->whereBetween('event_date', [$startDate, $endDate])
            ->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))
            ->latest('event_date')->take(10)->get();

        $allPropertiesForFilter = Property::orderBy('name')->get();

        $overallIncomeByProperty = $properties->map(function ($property) {
            return (object)[
                'name' => $property->name,
                'total_revenue' => $property->dailyRevenue,
                'chart_color' => $property->chart_color,
            ];
        });

        // 6. Mengirim Data ke View
        if ($propertyId == $sunnydayInnId) {
            $incomeCategories['other_breakfast_revenue'] = 'Breakfast Lain';
        }

        return view('admin.dashboard', [
            'properties' => $properties,
            'totalOverallRevenue' => $totalOverallRevenue,
            'allPropertiesForFilter' => $allPropertiesForFilter,
            'propertyId' => $propertyId, 'period' => $period,
            'startDate' => $startDate, 'endDate' => $endDate,
            'incomeCategories' => $incomeCategories,
            'recentMiceBookings' => $recentMiceBookings,
            'pieChartDataSource' => $pieChartDataSource,
            'pieChartCategories' => $pieChartCategories,
            'overallIncomeByProperty' => $overallIncomeByProperty,
        ]);
    }

    // =========================================================================
    // FUNGSI SALES ANALYTICS - TIDAK DIUBAH
    // =========================================================================
    public function salesAnalytics()
    {
        // ... (Tidak ada perubahan di sini) ...
        $totalEventRevenue = Booking::where('status', 'Booking Pasti')->sum('total_price');
        $totalBookings = Booking::count();
        $totalConfirmedBookings = Booking::where('status', 'Booking Pasti')->count();
        $totalActivePackages = PricePackage::where('is_active', true)->count();
        $bookingStatusData = Booking::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');
        $pieChartData = [
            'labels' => $bookingStatusData->keys(),
            'data' => $bookingStatusData->values(),
        ];
        $revenueData = Booking::select(
                DB::raw('YEAR(event_date) as year, MONTH(event_date) as month'),
                DB::raw('sum(total_price) as total')
            )
            ->where('status', 'Booking Pasti')
            ->where('event_date', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')->orderBy('month', 'asc')
            ->get();
        $barChartLabels = [];
        $barChartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            $barChartLabels[] = $monthName;
            $found = $revenueData->first(fn($item) => $item->year == $date->year && $item->month == $date->month);
            $barChartData[] = $found ? $found->total : 0;
        }
        $revenueChartData = [
            'labels' => $barChartLabels,
            'data' => $barChartData,
        ];
        return view('admin.sales_analytics', compact(
            'totalEventRevenue',
            'totalBookings',
            'totalConfirmedBookings',
            'totalActivePackages',
            'pieChartData',
            'revenueChartData'
        ));
    }


    // =========================================================================
    // --- [AWAL] BLOK KPI ANALYSIS YANG DIPERBARUI ---
    // (Logika disesuaikan dengan permintaan Sunnyday Inn)
    // =========================================================================

    public function kpiAnalysis(Request $request)
    {
        $properties = Property::orderBy('name')->get();
        $data = $this->getKpiAnalysisData($request);
        $viewData = $data;
        $viewData['properties'] = $properties;
        return view('admin.kpi_analysis', $viewData);
    }

    public function exportKpiAnalysis(Request $request)
    {
        $data = $this->getKpiAnalysisData($request);
        $slug = Str::slug(optional($data['selectedProperty'])->name ?? 'semua-properti');
        $fileName = 'Analisis_Kinerja_' . $slug . '_' . now()->format('YmdHis') . '.xlsx';
        
        return Excel::download(new KpiAnalysisExport(
            $data['kpiData'], 
            $data['dailyData'], 
            $data['selectedProperty'],
            $data['filteredIncomes'],
            $data['miceBookings']
        ), $fileName);
    }

    private function getKpiAnalysisData(Request $request): array
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $propertyId = $request->input('property_id');
        $selectedProperty = $propertyId ? Property::find($propertyId) : null;

        // ==========================================================
        // >> LOGIKA SUNNYDAY INN - KPI <<
        // ==========================================================
        $allProperties = Property::get(['id', 'name']);
        $sunnydayInnName = 'Sunnyday Inn'; // [PERBAIKAN] Sesuaikan dengan data DB Anda
        $sourcePropertyNames = ['Hotel Akat', 'Hotel Ermasu', 'Bell Hotel']; // [PERBAIKAN] Sesuaikan dengan data DB Anda
        $sourcePropertyIds = $allProperties->whereIn('name', $sourcePropertyNames)->pluck('id');
        $sunnydayInnId = $allProperties->firstWhere('name', $sunnydayInnName)->id ?? null;

        $dateFilter = fn ($query) => $query->whereBetween('date', [$startDate, $endDate]);
        
        $totalRedirectedBreakfast = DailyIncome::whereIn('property_id', $sourcePropertyIds)
            ->where($dateFilter)
            ->sum('breakfast_income');
        // ==========================================================
        // >> AKHIR LOGIKA SUNNYDAY INN - KPI <<
        // ==========================================================

        $query = DailyIncome::whereBetween('date', [$startDate, $endDate]);
        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }
        $filteredIncomes = $query->get();

        $miceQuery = Booking::where('status', 'Booking Pasti')
            ->whereBetween('event_date', [$startDate, $endDate]);
        if ($propertyId) {
            $miceQuery->where('property_id', $propertyId);
        }
        
        $miceBookings = $miceQuery->with('property:id,name')->get();
        $totalMiceRevenue = $miceBookings->sum('total_price');

        $kpiData = $this->calculateKpi(
            $filteredIncomes, 
            $selectedProperty, 
            $startDate, 
            $endDate, 
            $totalMiceRevenue,
            $totalRedirectedBreakfast,
            $sunnydayInnId,
            $sourcePropertyIds
        );
        
        $dailyData = $this->prepareDailyDataForChart(
            $filteredIncomes, 
            $selectedProperty, 
            $miceBookings,
            $sunnydayInnId,
            $sourcePropertyIds
        );

        return [
            'selectedProperty'  => $selectedProperty,
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'kpiData'           => $kpiData,
            'dailyData'         => $dailyData,
            'filteredIncomes'   => $filteredIncomes,
            'miceBookings'      => $miceBookings,
            'propertyId'        => $propertyId,
        ];
    }

    private function calculateKpi(
        Collection $incomes, 
        ?Property $property, 
        string $startDate, 
        string $endDate, 
        float $totalMiceRevenue = 0,
        float $totalRedirectedBreakfast = 0,
        ?int $sunnydayInnId = null,
        ?Collection $sourcePropertyIds = null
    ): array
    {
        $totalRoomsSold = $incomes->sum('total_rooms_sold');
        $totalRoomRevenue = $incomes->sum('total_rooms_revenue');
        $totalOtherRevenue = $incomes->sum('others_income');

        // ==========================================================
        // >> AWAL PERUBAHAN (KPI F&B) <<
        // ==========================================================
        
        $breakfast_income = $incomes->sum('breakfast_income');
        $lunch_income = $incomes->sum('lunch_income');
        $dinner_income = $incomes->sum('dinner_income');
        $other_breakfast_revenue = 0;

        if ($property && $sourcePropertyIds && $sourcePropertyIds->contains($property->id)) {
            // Kasus 1: Properti sumber (Akat, Ermasu, Bell) dipilih
            // Total F&B mereka dikurangi breakfast
            $totalFbRevenue = ($breakfast_income + $lunch_income + $dinner_income) - $breakfast_income;
            // [BARIS DIHAPUS] Baris "$breakfast_income = 0;" dihapus agar tetap tampil
        }
        else if ($property && $property->id == $sunnydayInnId) {
            // Kasus 2: Sunnyday Inn dipilih
            $totalFbRevenue = $breakfast_income + $lunch_income + $dinner_income + $totalRedirectedBreakfast;
            $other_breakfast_revenue = $totalRedirectedBreakfast;
        }
        else if ($property == null) {
            // Kasus 3: "Semua Properti" dipilih
            $totalFbRevenue = $breakfast_income + $lunch_income + $dinner_income; // Total F&B asli tetap
            $breakfast_income -= $totalRedirectedBreakfast; // Kurangi dari Bfast
            $other_breakfast_revenue = $totalRedirectedBreakfast; // Tambah ke Bfast Lain
        }
        else {
            // Kasus 4: Properti normal lain dipilih
            $totalFbRevenue = $breakfast_income + $lunch_income + $dinner_income;
        }
        
        // ==========================================================
        // >> AKHIR PERUBAHAN (KPI F&B) <<
        // ==========================================================

        $numberOfDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $totalAvailableRooms = 0;
        if ($property) {
            $totalAvailableRooms = $property->total_rooms * $numberOfDays;
        } else {
            $totalAvailableRooms = Property::sum('total_rooms') * $numberOfDays;
        }

        $avgOccupancy = ($totalAvailableRooms > 0) ? ($totalRoomsSold / $totalAvailableRooms * 100) : 0;
        $avgArr = ($totalRoomsSold > 0) ? ($totalRoomRevenue / $totalRoomsSold) : 0;
        $revPar = ($totalAvailableRooms > 0) ? ($totalRoomRevenue / $totalAvailableRooms) : 0;
        $totalLunchAndDinnerRevenue = $lunch_income + $dinner_income;
        $restoRevenuePerRoom = ($totalRoomsSold > 0) ? ($totalLunchAndDinnerRevenue / $totalRoomsSold) : 0;
        $grandTotalRevenue = $totalRoomRevenue + $totalFbRevenue + $totalOtherRevenue + $totalMiceRevenue;

        return [
            // KPI Utama
            'totalRevenue' => $grandTotalRevenue,
            'totalRoomsSold' => $totalRoomsSold,
            'avgOccupancy' => $avgOccupancy,
            'avgArr' => $avgArr,
            'revPar' => $revPar,
            'restoRevenuePerRoom' => $restoRevenuePerRoom,
            
            'totalRoomRevenue' => $totalRoomRevenue,
            'roomRevenueBreakdown' => [
                'Offline' => $incomes->sum('offline_room_income'),
                'Online' => $incomes->sum('online_room_income'),
                'Travel Agent' => $incomes->sum('ta_income'),
                'Government' => $incomes->sum('gov_income'),
                'Corporate' => $incomes->sum('corp_income'),
                'Afiliasi' => $incomes->sum('afiliasi_room_income'),
            ],
            
            'totalFbRevenue' => $totalFbRevenue,
            'fbRevenueBreakdown' => [
                'Breakfast' => $breakfast_income,
                'Lunch' => $lunch_income,
                'Dinner' => $dinner_income,
                'Breakfast Lain' => $other_breakfast_revenue,
            ],

            'miceRevenue' => $totalMiceRevenue,
            'totalOtherRevenue' => $totalOtherRevenue,
            'grandTotalRevenue' => $grandTotalRevenue, 

            'roomsSoldBreakdown' => [
                'Offline' => $incomes->sum('offline_rooms'), 'Online' => $incomes->sum('online_rooms'),
                'Travel Agent' => $incomes->sum('ta_rooms'), 'Government' => $incomes->sum('gov_rooms'),
                'Corporate' => $incomes->sum('corp_rooms'), 'Afiliasi' => $incomes->sum('afiliasi_rooms'),
                'House Use' => $incomes->sum('house_use_rooms'), 'Compliment' => $incomes->sum('compliment_rooms'),
            ],
        ];
    }
    
    private function prepareDailyDataForChart(
        Collection $incomes, 
        ?Property $selectedProperty, 
        Collection $miceBookings,
        ?int $sunnydayInnId = null,
        ?Collection $sourcePropertyIds = null
    ): Collection
    {
        $dailyGroups = $incomes->groupBy(fn($item) => Carbon::parse($item->date)->toDateString());
        $miceGroups = $miceBookings->groupBy(fn($item) => Carbon::parse($item->event_date)->toDateString());
        $allDates = $dailyGroups->keys()->merge($miceGroups->keys())->unique()->sort();

        $totalAvailableRooms = 0;
        if ($selectedProperty) {
            $totalAvailableRooms = $selectedProperty->total_rooms;
        } else {
            $totalAvailableRooms = Property::sum('total_rooms');
        }

        return $allDates->map(function ($date) use ($dailyGroups, $miceGroups, $totalAvailableRooms, $selectedProperty, $sunnydayInnId, $sourcePropertyIds) {
            $incomeData = $dailyGroups->get($date) ?? collect();
            $miceData = $miceGroups->get($date) ?? collect();

            $totalRoomsSold = $incomeData->sum('total_rooms_sold');
            $totalRoomRevenue = $incomeData->sum('total_rooms_revenue');
            $totalDailyIncomeRevenue = $incomeData->sum('total_revenue');
            $totalMiceRevenue = $miceData->sum('total_price');

            // ==========================================================
            // >> AWAL PERUBAHAN (GRAFIK HARIAN) <<
            // ==========================================================
            
            $breakfastAdjustment = 0;
            
            if ($selectedProperty && $sourcePropertyIds && $sourcePropertyIds->contains($selectedProperty->id)) {
                $dailyBreakfast = $incomeData->sum('breakfast_income');
                $breakfastAdjustment = -1 * $dailyBreakfast;
            }
            else if ($selectedProperty && $selectedProperty->id == $sunnydayInnId) {
                $redirectedBreakfastToday = DailyIncome::whereIn('property_id', $sourcePropertyIds)
                    ->where('date', $date)
                    ->sum('breakfast_income');
                $breakfastAdjustment = $redirectedBreakfastToday;
            }
            // ==========================================================
            // >> AKHIR PERUBAHAN (GRAFIK HARIAN) <<
            // ==========================================================

            $grandTotalRevenue = $totalDailyIncomeRevenue + $totalMiceRevenue + $breakfastAdjustment;

            return [
                'date' => Carbon::parse($date)->format('d M Y'),
                'revenue' => $grandTotalRevenue,
                'occupancy' => $totalAvailableRooms > 0 ? ($totalRoomsSold / $totalAvailableRooms) * 100 : 0,
                'arr' => $totalRoomsSold > 0 ? $totalRoomRevenue / $totalRoomsSold : 0,
                'rooms_sold' => $totalRoomsSold,
            ];
        })->values();
    }

    // --- [AKHIR] BLOK KPI ANALYSIS YANG DIPERBARUI ---
    // =========================================================================


    // =========================================================================
    // FUNGSI LAINNYA - TIDAK DIUBAH
    // =========================================================================

    public function exportPropertiesSummaryExcel(Request $request)
    {
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        } else {
            $period = $request->input('period', 'year');
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                default:
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear(); // Ada typo 'CarbonD' di file Anda, sudah saya perbaiki
                    break;
            }
        }
        
        $propertyId = $request->input('property_id');
        
        $fileName = 'Laporan_Pendapatan_Properti_' . now()->format('d-m-Y_H-i') . '.xlsx';
        
        return Excel::download(new AdminPropertiesSummaryExport($startDate, $endDate, $propertyId), $fileName);
    }

    public function exportPropertiesSummaryCsv(Request $request)
    {
        return Excel::download(new AdminPropertiesSummaryExport($request), 'properties-summary-'.now()->format('Ymd').'.csv');
    }

    public function unifiedCalendar()
    {
        $properties = Property::orderBy('name')->get();
        return view('admin.calendar.unified_index', compact('properties'));
    }

    public function getUnifiedCalendarEvents(Request $request)
    {
        $source = $request->query('source', 'ecommerce');
        $propertyId = $request->query('property_id');
        $response = [];

        if ($source === 'sales') {
            $eventsQuery = Booking::query();
            if ($propertyId && $propertyId !== 'all') {
                $eventsQuery->where('property_id', $propertyId);
            }
            $events = $eventsQuery->select(
                'client_name as title',
                'event_date as start',
                DB::raw('DATE_ADD(event_date, INTERVAL 1 DAY) as end'),
                DB::raw("'#3B82F6' as color")
            )->get();
            $response['events'] = $events;
        } else { // ecommerce
            $eventsQuery = Reservation::query();
            if ($propertyId && $propertyId !== 'all') {
                $eventsQuery->where('property_id', $propertyId);
            }
            $events = $eventsQuery->select(
                'guest_name as title',
                'checkin_date as start',
                'checkout_date as end',
                DB::raw("'#10B981' as color")
            )->get();
            $response['events'] = $events;

            $startDate = Carbon::now()->subDays(30);
            
            $chartQuery = DailyOccupancy::query()
                ->where('date', '>=', $startDate);

            if ($propertyId && $propertyId !== 'all') {
                $chartQuery->where('property_id', $propertyId);
            }

            $chartOccupancyData = $chartQuery->select(
                    'date',
                    DB::raw('SUM(occupied_rooms) as total_occupied')
                )
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $response['chartData'] = [
                'labels' => $chartOccupancyData->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('d M')),
                'data' => $chartOccupancyData->pluck('total_occupied'),
            ];
        }

        return response()->json($response);
    }
    
    public function exportExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : now()->endOfMonth();

        $properties = Property::orderBy('name')->get();
        
        $fileName = 'Laporan_Pendapatan_' . $startDate->format('d-m-Y') . '_-_' . $endDate->format('d-m-Y') . '.xlsx';

        return Excel::download(new DashboardExport($startDate, $endDate, $properties), $fileName);
    }
}