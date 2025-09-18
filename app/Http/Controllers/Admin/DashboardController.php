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

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return $this->index($request);
    }

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
        $dateFilter = fn ($query) => $query->whereBetween('date', [$startDate, $endDate]);

        // 3. Mengambil Data Properti dengan Semua Kalkulasi
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
        
        // 4. Menyiapkan Data untuk Chart
        $pieChartCategories = [
            'offline_room_income' => 'Walk In', 'online_room_income' => 'OTA', 'ta_income' => 'Travel Agent',
            'gov_income' => 'Government', 'corp_income' => 'Corporation', 'afiliasi_room_income' => 'Afiliasi',
            'mice_income' => 'MICE', 'fnb_income' => 'F&B', 'others_income' => 'Lain-lain',
        ];

        $pieChartDataSource = new \stdClass();
        foreach ($pieChartCategories as $key => $label) {
            $totalKey = 'total_' . $key;
            if ($key === 'mice_income') {
                $pieChartDataSource->$totalKey = $miceRevenues->flatten()->sum('total_mice_revenue');
            } else if ($key === 'fnb_income') {
                $pieChartDataSource->$totalKey = $properties->sum('total_breakfast_income') + $properties->sum('total_lunch_income') + $properties->sum('total_dinner_income');
            } else {
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

        // 5. Mengirim Data ke View
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

    public function salesAnalytics()
    {
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

    public function kpiAnalysis(Request $request)
    {
        // Method ini sudah benar, kita akan menyalin logikanya ke method export.
        $properties = Property::orderBy('name')->get();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $propertyId = $request->input('property_id');

        $query = DailyIncome::whereBetween('date', [$startDate, $endDate]);

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        $filteredIncomes = $query->get();

        $selectedProperty = $propertyId ? Property::find($propertyId) : null;
        $kpiData = null;
        $dailyData = collect();

        if ($filteredIncomes->isNotEmpty()) {
            $totalRevenue = $filteredIncomes->sum('total_revenue');
            $totalRoomRevenue = $filteredIncomes->sum('total_rooms_revenue');
            $totalFbRevenue = $filteredIncomes->sum('total_fb_revenue');
            $totalOtherRevenue = $filteredIncomes->sum('others_income');
            $totalRoomsSold = $filteredIncomes->sum('total_rooms_sold');
            
            $avgOccupancy = $totalRoomsSold > 0 ? $filteredIncomes->avg('occupancy') : 0;
            $avgArr = $totalRoomsSold > 0 ? ($totalRoomRevenue / $totalRoomsSold) : 0;

            $numberOfDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

            if ($selectedProperty) {
                $totalRoomsInProperty = $selectedProperty->hotelRooms()->count();
                $totalAvailableRooms = $totalRoomsInProperty * $numberOfDays;
            } else {
                $totalRoomsInSystem = HotelRoom::count();
                $totalAvailableRooms = $totalRoomsInSystem * $numberOfDays;
            }

            $revPar = $totalAvailableRooms > 0 ? ($totalRoomRevenue / $totalAvailableRooms) : 0;

            $revenueBreakdown = [
                'Offline' => $filteredIncomes->sum('offline_room_income'), 'Online' => $filteredIncomes->sum('online_room_income'),
                'Travel Agent' => $filteredIncomes->sum('ta_income'), 'Government' => $filteredIncomes->sum('gov_income'),
                'Corporate' => $filteredIncomes->sum('corp_income'), 'Afiliasi' => $filteredIncomes->sum('afiliasi_room_income'),
                'MICE/Event' => $filteredIncomes->sum('mice_room_income'),
            ];
            $roomsSoldBreakdown = [
                'Offline' => $filteredIncomes->sum('offline_rooms'), 'Online' => $filteredIncomes->sum('online_rooms'),
                'Travel Agent' => $filteredIncomes->sum('ta_rooms'), 'Government' => $filteredIncomes->sum('gov_rooms'),
                'Corporate' => $filteredIncomes->sum('corp_rooms'), 'Afiliasi' => $filteredIncomes->sum('afiliasi_rooms'),
                'House Use' => $filteredIncomes->sum('house_use_rooms'), 'Compliment' => $filteredIncomes->sum('compliment_rooms'),
            ];
            
            $kpiData = [
                'totalRevenue' => $totalRevenue, 'totalRoomRevenue' => $totalRoomRevenue,
                'totalFbRevenue' => $totalFbRevenue, 'totalOtherRevenue' => $totalOtherRevenue,
                'totalRoomsSold' => $totalRoomsSold, 'avgOccupancy' => $avgOccupancy,
                'avgArr' => $avgArr, 'revPar' => $revPar,
                'revenueBreakdown' => $revenueBreakdown, 'roomsSoldBreakdown' => $roomsSoldBreakdown,
            ];

            if ($selectedProperty) {
                $dailyData = $filteredIncomes->sortBy('date')->map(function ($income) {
                    return ['date' => Carbon::parse($income->date)->format('d M Y'), 'revenue' => $income->total_revenue, 'occupancy' => round($income->occupancy, 2), 'arr' => $income->arr, 'rooms_sold' => $income->total_rooms_sold];
                });
            } else {
                $dailyData = $filteredIncomes->groupBy('date')->map(function ($dailyIncomes, $date) {
                    $totalRoomsSold = $dailyIncomes->sum('total_rooms_sold'); $totalRoomRevenue = $dailyIncomes->sum('total_rooms_revenue');
                    return ['date' => Carbon::parse($date)->format('d M Y'), 'revenue' => $dailyIncomes->sum('total_revenue'), 'occupancy' => $dailyIncomes->avg('occupancy'), 'arr' => $totalRoomsSold > 0 ? $totalRoomRevenue / $totalRoomsSold : 0, 'rooms_sold' => $totalRoomsSold];
                })->sortBy('date')->values();
            }
        }

        return view('admin.kpi_analysis', compact('properties', 'selectedProperty', 'kpiData', 'dailyData', 'startDate', 'endDate', 'propertyId'));
    }


    public function exportPropertiesSummaryExcel(Request $request)
    {
        // Logika untuk menentukan filter tanggal (sama seperti di method index)
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        } else {
            $period = $request->input('period', 'year'); // Default 'year' jika tidak ada
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
        
        $propertyId = $request->input('property_id');
        
        // Siapkan nama file
        $fileName = 'Laporan_Pendapatan_Properti_' . now()->format('d-m-Y_H-i') . '.xlsx';
        
        // Panggil class export yang sudah ada dengan filter yang sesuai
        return Excel::download(new AdminPropertiesSummaryExport($startDate, $endDate, $propertyId), $fileName);
    }

    /**
     * Menangani ekspor data ringkasan properti ke CSV.
     */
    public function exportPropertiesSummaryCsv(Request $request)
    {
        return Excel::download(new AdminPropertiesSummaryExport($request), 'properties-summary-'.now()->format('Ymd').'.csv');
    }
    public function unifiedCalendar()
    {
        // Ambil semua properti untuk ditampilkan di filter dropdown
        $properties = Property::orderBy('name')->get();

        return view('admin.calendar.unified_index', compact('properties'));
    }

    /**
     * Menyediakan data event untuk kalender terpusat (Ecommerce atau Sales).
     */
    public function getUnifiedCalendarEvents(Request $request)
    {
        $source = $request->query('source', 'ecommerce');
        $propertyId = $request->query('property_id'); // Ambil ID properti dari request
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

            // === LOGIKA CHART DENGAN FILTER PROPERTI ===
            $startDate = Carbon::now()->subDays(30);
            
            $chartQuery = DailyOccupancy::query()
                ->where('date', '>=', $startDate);

            // Terapkan filter jika ada properti yang dipilih
            if ($propertyId && $propertyId !== 'all') {
                $chartQuery->where('property_id', $propertyId);
            }

            $chartOccupancyData = $chartQuery->select(
                    'date',
                    // Gunakan SUM karena jika tidak ada filter, kita menjumlahkan semua properti
                    DB::raw('SUM(occupied_rooms) as total_occupied')
                )
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $response['chartData'] = [
                'labels' => $chartOccupancyData->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('d M')),
                'data' => $chartOccupancyData->pluck('total_occupied'),
            ];
            // ===============================================
        }

        return response()->json($response);
    }
    
    public function exportKpiAnalysis(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $propertyId = $request->input('property_id');
    
        $query = DailyIncome::whereBetween('date', [$startDate, $endDate]);
    
        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }
    
        // Ambil SEMUA data pendapatan dalam rentang tanggal
        $filteredIncomes = $query->get();
        
        $selectedProperty = $propertyId ? Property::find($propertyId) : null;
        
        $fileName = 'laporan_kpi_' . ($selectedProperty->name ?? 'semua-properti') . '_' . now()->format('Ymd') . '.xlsx';
        
        // Panggil class ekspor utama dan kirim semua data mentah
        return Excel::download(new KpiAnalysisExport($filteredIncomes, $selectedProperty), $fileName);
    }
}
