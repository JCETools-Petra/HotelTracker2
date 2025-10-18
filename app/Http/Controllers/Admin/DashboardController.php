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
use Illuminate\Support\Collection; // Pastikan ini di-import

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

    // --- [AWAL] BLOK KODE YANG DIREVISI ---

    /**
     * Menampilkan halaman analisis KPI dengan data yang sudah dihitung.
     */
    public function kpiAnalysis(Request $request)
    {
        $properties = Property::orderBy('name')->get();
        // Menggunakan fungsi terpusat untuk mengambil dan menghitung data
        $data = $this->getKpiAnalysisData($request);

        return view('admin.kpi_analysis', array_merge($data, compact('properties')));
    }

    /**
     * Memproses dan memicu unduhan file Excel untuk laporan analisis KPI.
     */
    public function exportKpiAnalysis(Request $request)
    {
        // Menggunakan fungsi terpusat yang sama untuk konsistensi data
        $data = $this->getKpiAnalysisData($request);
        $fileName = 'laporan_kpi_' . (optional($data['selectedProperty'])->name ?? 'semua-properti') . '_' . now()->format('Ymd') . '.xlsx';
        
        // Mengirim data yang sudah benar ke Class Export
        return Excel::download(new KpiAnalysisExport(
            $data['kpiData'], 
            $data['dailyData'], 
            $data['selectedProperty'],
            $data['filteredIncomes'] // Data mentah tetap dikirim untuk pengelompokan per bulan
        ), $fileName);
    }

    /**
     * Fungsi terpusat untuk mengambil dan menghitung semua data yang diperlukan
     * untuk halaman analisis KPI dan proses ekspor.
     */
    private function getKpiAnalysisData(Request $request): array
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $propertyId = $request->input('property_id');

        // 1. Mengambil data mentah dari database
        $query = DailyIncome::whereBetween('date', [$startDate, $endDate]);
        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }
        $filteredIncomes = $query->get();

        $selectedProperty = $propertyId ? Property::find($propertyId) : null;
        $kpiData = null;
        $dailyData = collect();

        // 2. Lakukan kalkulasi hanya jika ada data
        if ($filteredIncomes->isNotEmpty()) {
            // Kalkulasi data agregat (KPI utama)
            $kpiData = $this->calculateKpi($filteredIncomes, $selectedProperty, $startDate, $endDate);
            
            // Menyiapkan rincian data harian
            if ($selectedProperty) {
                // Jika satu properti, data sudah per hari
                $dailyData = $filteredIncomes->sortBy('date')->map(fn ($income) => $this->formatDailyData($income));
            } else {
                // Jika semua properti, gabungkan data pada tanggal yang sama
                $dailyData = $filteredIncomes->groupBy('date')->map(function ($dailyIncomes, $date) {
                    $totalRoomsSold = $dailyIncomes->sum('total_rooms_sold');
                    $totalRoomRevenue = $dailyIncomes->sum('total_rooms_revenue');
                    return [
                        'date' => Carbon::parse($date)->format('d M Y'),
                        'revenue' => $dailyIncomes->sum('total_revenue'),
                        'occupancy' => $dailyIncomes->avg('occupancy'),
                        'arr' => $totalRoomsSold > 0 ? $totalRoomRevenue / $totalRoomsSold : 0,
                        'rooms_sold' => $totalRoomsSold,
                    ];
                })->sortBy('date')->values();
            }
        }

        return compact('selectedProperty', 'kpiData', 'dailyData', 'startDate', 'endDate', 'propertyId', 'filteredIncomes');
    }

    /**
     * Fungsi inti untuk menghitung semua metrik KPI agregat dari koleksi data pendapatan.
     */
    private function calculateKpi(Collection $incomes, ?Property $property, string $startDate, string $endDate): array
    {
        $totalRoomsSold = $incomes->sum('total_rooms_sold');
        $totalRoomRevenue = $incomes->sum('total_rooms_revenue');

        // Menggunakan formula yang benar dan standar untuk KPI
        $avgOccupancy = $incomes->avg('occupancy');
        $avgArr = ($totalRoomsSold > 0) ? ($totalRoomRevenue / $totalRoomsSold) : 0;
        
        $totalLunchAndDinnerRevenue = $incomes->sum('lunch_income') + $incomes->sum('dinner_income');
        $restoRevenuePerRoom = ($totalRoomsSold > 0) ? ($totalLunchAndDinnerRevenue / $totalRoomsSold) : 0;

        $numberOfDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $totalAvailableRooms = 0;
        if ($property) {
            $totalAvailableRooms = $property->total_rooms * $numberOfDays;
        } else {
            $totalAvailableRooms = Property::sum('total_rooms') * $numberOfDays;
        }
        $revPar = ($totalAvailableRooms > 0) ? ($totalRoomRevenue / $totalAvailableRooms) : 0;

        return [
            'totalRevenue' => $incomes->sum('total_revenue'),
            'totalRoomsSold' => $totalRoomsSold,
            'avgOccupancy' => $avgOccupancy,
            'avgArr' => $avgArr,
            'revPar' => $revPar,
            'restoRevenuePerRoom' => $restoRevenuePerRoom,
            'totalRoomRevenue' => $totalRoomRevenue,
            'totalFbRevenue' => $incomes->sum('total_fb_revenue'),
            'totalOtherRevenue' => $incomes->sum('others_income'),
            'totalBreakfastRevenue' => $incomes->sum('breakfast_income'),
            'totalLunchRevenue' => $incomes->sum('lunch_income'),
            'totalDinnerRevenue' => $incomes->sum('dinner_income'),
            'revenueBreakdown' => [
                'Offline' => $incomes->sum('offline_room_income'), 'Online' => $incomes->sum('online_room_income'),
                'Travel Agent' => $incomes->sum('ta_income'), 'Government' => $incomes->sum('gov_income'),
                'Corporate' => $incomes->sum('corp_income'), 'Afiliasi' => $incomes->sum('afiliasi_room_income'),
                'MICE/Event' => $incomes->sum('mice_room_income'),
            ],
            'roomsSoldBreakdown' => [
                'Offline' => $incomes->sum('offline_rooms'), 'Online' => $incomes->sum('online_rooms'),
                'Travel Agent' => $incomes->sum('ta_rooms'), 'Government' => $incomes->sum('gov_rooms'),
                'Corporate' => $incomes->sum('corp_rooms'), 'Afiliasi' => $incomes->sum('afiliasi_rooms'),
                'House Use' => $incomes->sum('house_use_rooms'), 'Compliment' => $incomes->sum('compliment_rooms'),
            ],
        ];
    }
    
    /**
     * Memformat satu baris data pendapatan harian.
     */
    private function formatDailyData($income): array
    {
        return [
            'date' => Carbon::parse($income->date)->format('d M Y'),
            'revenue' => $income->total_revenue,
            'occupancy' => round($income->occupancy, 2),
            'arr' => $income->arr,
            'rooms_sold' => $income->total_rooms_sold
        ];
    }

    // --- [AKHIR] BLOK KODE YANG DIREVISI ---

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
                    $endDate = Carbon::now()->endOfYear();
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