<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\DailyIncome;
use App\Models\RevenueTarget;
use App\Models\Booking;
use App\Models\DailyOccupancy; // <-- Ditambahkan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function __construct()
    {
        // Otorisasi bisa ditambahkan di sini jika perlu
    }

    /**
     * Menampilkan daftar semua properti.
     */
    public function index(Request $request)
    {
        $query = Property::orderBy('id', 'asc');
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $properties = $query->paginate(15);
        return view('admin.properties.index', compact('properties'));
    }

    /**
     * Menampilkan form untuk membuat properti baru.
     */
    public function create()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat melakukan aksi ini.');
        }
        return view('admin.properties.create');
    }

    /**
     * Menyimpan properti baru ke database.
     */
    public function store(Request $request)
    {
        $this->authorize('manage-data');
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat melakukan aksi ini.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:properties,name',
            'chart_color' => 'nullable|string|size:7',
        ]);

        Property::create($validatedData);
        return redirect()->route('admin.properties.index')->with('success', 'Properti baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail sebuah properti.
     */
    public function show(Property $property, Request $request)
    {
        // Logika baru untuk mengambil data okupansi berdasarkan tanggal
        $selectedDate = $request->query('date', today()->toDateString());
        $occupancy = DailyOccupancy::firstOrCreate(
            [
                'property_id' => $property->id,
                'date' => $selectedDate,
            ],
            ['occupied_rooms' => 0]
        );

        // Logika lama Anda untuk pendapatan
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : null;
        $displayStartDate = $startDate ?: Carbon::now()->startOfMonth();
        $displayEndDate = $endDate ?: Carbon::now()->endOfMonth();

        $incomeCategories = [
            'offline_room_income' => 'Walk In Guest', 'online_room_income' => 'OTA', 'ta_income' => 'TA/Travel Agent',
            'gov_income' => 'Gov/Government', 'corp_income' => 'Corp/Corporation', 'compliment_income' => 'Compliment',
            'house_use_income' => 'House Use', 'afiliasi_room_income' => 'Afiliasi',
            'mice_income' => 'MICE', 'fnb_income' => 'F&B', 'others_income' => 'Lainnya',
        ];

        $dbDailyIncomeColumns = [
            'offline_room_income', 'online_room_income', 'ta_income', 'gov_income', 'corp_income', 'compliment_income',
            'house_use_income', 'afiliasi_room_income', 'breakfast_income', 'lunch_income', 'dinner_income', 'others_income',
            'offline_rooms', 'online_rooms', 'ta_rooms', 'gov_rooms', 'corp_rooms', 'compliment_rooms', 'house_use_rooms', 'afiliasi_rooms'
        ];
        
        $dailyIncomesData = DailyIncome::where('property_id', $property->id)
            ->whereBetween('date', [$displayStartDate, $displayEndDate])
            ->get()->keyBy(fn($item) => Carbon::parse($item->date)->toDateString());

        $dailyMiceFromBookings = Booking::where('property_id', $property->id)
            ->where('status', 'Booking Pasti')
            ->whereBetween('event_date', [$displayStartDate, $displayEndDate])
            ->select(DB::raw('DATE(event_date) as date'), DB::raw('SUM(total_price) as total_mice'))
            ->groupBy('date')->get()->keyBy(fn($item) => Carbon::parse($item->date)->toDateString());

        $period = CarbonPeriod::create($displayStartDate, $displayEndDate);
        
        $fullDateRangeData = collect($period)->map(function ($date) use ($dailyIncomesData, $dailyMiceFromBookings, $dbDailyIncomeColumns) {
            $dateString = $date->toDateString();
            $income = $dailyIncomesData->get($dateString);
            $mice = $dailyMiceFromBookings->get($dateString);
            $dayData = new \stdClass();
            $dayData->date = $date->toDateTimeString();
            $dayData->id = $income->id ?? null;
            foreach ($dbDailyIncomeColumns as $column) {
                $dayData->{$column} = $income->{$column} ?? 0;
            }
            $dayData->mice_booking_total = $mice->total_mice ?? 0;
            $dayData->mice_income = $dayData->mice_booking_total;
            return $dayData;
        });

        $totalPropertyRevenueFiltered = $fullDateRangeData->sum(function($day) {
            return ($day->offline_room_income ?? 0) + ($day->online_room_income ?? 0) + ($day->ta_income ?? 0) +
                   ($day->gov_income ?? 0) + ($day->corp_income ?? 0) + ($day->compliment_income ?? 0) +
                   ($day->house_use_income ?? 0) + ($day->afiliasi_room_income ?? 0) +
                   ($day->breakfast_income ?? 0) + ($day->lunch_income ?? 0) + ($day->dinner_income ?? 0) +
                   ($day->others_income ?? 0) + ($day->mice_booking_total ?? 0);
        });
        
        $sourceDistribution = new \stdClass();
        foreach (array_keys($incomeCategories) as $key) {
            $sourceDistribution->{'total_' . $key} = 0;
        }

        $sourceDistribution->total_fnb_income = $fullDateRangeData->sum(fn($day) => ($day->breakfast_income ?? 0) + ($day->lunch_income ?? 0) + ($day->dinner_income ?? 0));
        $sourceDistribution->total_mice_income = $fullDateRangeData->sum('mice_booking_total');
        $sourceDistribution->total_offline_room_income = $fullDateRangeData->sum('offline_room_income');
        $sourceDistribution->total_online_room_income = $fullDateRangeData->sum('online_room_income');
        $sourceDistribution->total_ta_income = $fullDateRangeData->sum('ta_income');
        $sourceDistribution->total_gov_income = $fullDateRangeData->sum('gov_income');
        $sourceDistribution->total_corp_income = $fullDateRangeData->sum('corp_income');
        $sourceDistribution->total_compliment_income = $fullDateRangeData->sum('compliment_income');
        $sourceDistribution->total_house_use_income = $fullDateRangeData->sum('house_use_income');
        $sourceDistribution->total_afiliasi_room_income = $fullDateRangeData->sum('afiliasi_room_income');
        $sourceDistribution->total_others_income = $fullDateRangeData->sum('others_income');
        
        $dailyTrend = $fullDateRangeData->map(function($day) {
            $total = ($day->offline_room_income ?? 0) + ($day->online_room_income ?? 0) + ($day->ta_income ?? 0) +
                     ($day->gov_income ?? 0) + ($day->corp_income ?? 0) + ($day->compliment_income ?? 0) +
                     ($day->house_use_income ?? 0) + ($day->afiliasi_room_income ?? 0) +
                     ($day->breakfast_income ?? 0) + ($day->lunch_income ?? 0) + ($day->dinner_income ?? 0) +
                     ($day->others_income ?? 0) + ($day->mice_booking_total ?? 0);
            return ['date' => $day->date, 'total_daily_income' => $total];
        });
        
        $targetMonth = $displayEndDate->copy()->startOfMonth();
        $revenueTarget = RevenueTarget::where('property_id', $property->id)->where('month_year', $targetMonth->format('Y-m-d'))->first();
        $monthlyTarget = $revenueTarget->target_amount ?? 0;
        $daysInMonth = $displayEndDate->daysInMonth;
        $dailyTarget = $daysInMonth > 0 ? $monthlyTarget / $daysInMonth : 0;
        
        $lastDayData = $fullDateRangeData->sortByDesc('date')->first();
        $lastDayIncome = 0;
        if ($lastDayData) {
            $trendForLastDay = collect($dailyTrend)->firstWhere('date', $lastDayData->date);
            $lastDayIncome = $trendForLastDay ? $trendForLastDay['total_daily_income'] : 0;
        }
        
        $dailyTargetAchievement = $dailyTarget > 0 ? ($lastDayIncome / $dailyTarget) * 100 : 0;
        
        $incomes = $fullDateRangeData;
        
        return view('admin.properties.show', compact(
            'property', 'incomes', 'dailyTrend', 'sourceDistribution', 'totalPropertyRevenueFiltered',
            'startDate', 'endDate', 'displayStartDate', 'displayEndDate', 'incomeCategories',
            'dailyTarget', 'lastDayIncome', 'dailyTargetAchievement',
            'occupancy', 'selectedDate' // <-- Variabel baru ditambahkan
        ));
    }

    /**
     * Method baru untuk update okupansi oleh Admin.
     */
    public function updateOccupancy(Request $request, Property $property)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'occupied_rooms' => 'required|integer|min:0',
        ]);

        DailyOccupancy::updateOrCreate(
            [
                'property_id' => $property->id,
                'date' => $validated['date'],
            ],
            ['occupied_rooms' => $validated['occupied_rooms']]
        );

        return redirect()->route('admin.properties.show', ['property' => $property->id, 'date' => $validated['date']])
                         ->with('success', 'Okupansi berhasil diperbarui.');
    }
    
    public function edit(Property $property)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat melakukan aksi ini.');
        }
        return view('admin.properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        $this->authorize('manage-data');
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat melakukan aksi ini.');
        }
        
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('properties')->ignore($property->id)],
            'chart_color' => 'nullable|string|size:7|starts_with:#',
        ]);

        $property->update($validatedData);
        return redirect()->route('admin.properties.index')->with('success', 'Data properti berhasil diperbarui.');
    }

    public function destroy(Property $property)
    {
        $this->authorize('manage-data');
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat melakukan aksi ini.');
        }
        if ($property->dailyIncomes()->exists()) {
            return redirect()->route('admin.properties.index')
                ->with('error', 'Properti tidak dapat dihapus karena memiliki data pendapatan terkait.');
        }
        $property->delete();
        return redirect()->route('admin.properties.index')
            ->with('success', 'Properti berhasil dihapus.');
    }

    public function showComparisonForm()
    {
        $properties = Property::orderBy('name')->get();
        if ($properties->count() < 2) {
            return redirect()->route('admin.dashboard')->with('info', 'Minimal perlu ada 2 properti untuk dapat dibandingkan.');
        }
        return view('admin.properties.compare_form', compact('properties'));
    }
    
    public function showComparisonResults(Request $request)
    {
        $validated = $request->validate([
            'properties_ids'     => 'required|array|min:2',
            'properties_ids.*'   => 'integer|exists:properties,id',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
        ]);

        $propertyIds = $validated['properties_ids'];
        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        $incomeCategories = [
            'offline_room_income' => 'Walk In Guest', 'online_room_income'  => 'OTA', 'ta_income'           => 'TA/Travel Agent',
            'gov_income'          => 'Gov/Government', 'corp_income'       => 'Corp/Corporation', 'compliment_income'   => 'Compliment',
            'house_use_income'    => 'House Use', 'mice_income'         => 'MICE', 'fnb_income'          => 'F&B',
            'others_income'       => 'Lainnya',
        ];
        $categoryLabels = array_values($incomeCategories);
        $categoryKeysForDisplay = array_keys($incomeCategories);

        $dbCategoryColumns = [
            'offline_room_income', 'online_room_income', 'ta_income', 'gov_income', 'corp_income',
            'compliment_income', 'house_use_income', 'breakfast_income', 'lunch_income', 'dinner_income', 'others_income'
        ];
        
        $comparisonData = [];
        $totalRevenueRaw = implode(' + ', array_map(fn($col) => "IFNULL(`$col`, 0)", $dbCategoryColumns));
        $selectedPropertiesModels = Property::whereIn('id', $propertyIds)->get();

        foreach ($selectedPropertiesModels as $property) {
            $incomeDetails = DailyIncome::where('property_id', $property->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->select(DB::raw("SUM({$totalRevenueRaw}) as total_revenue, " . implode(', ', array_map(fn($col) => "SUM(IFNULL(`{$col}`, 0)) as `{$col}`", $dbCategoryColumns))))
                ->first();
            
            $miceRevenueFromBooking = Booking::where('property_id', $property->id)
                ->where('status', 'Booking Pasti')
                ->whereBetween('event_date', [$startDate, $endDate])
                ->sum('total_price');

            $dataPoint = ['name' => $property->name];
            
            $totalFnb = ($incomeDetails->breakfast_income ?? 0) + ($incomeDetails->lunch_income ?? 0) + ($incomeDetails->dinner_income ?? 0);
            $dataPoint['offline_room_income'] = $incomeDetails->offline_room_income ?? 0;
            $dataPoint['online_room_income'] = $incomeDetails->online_room_income ?? 0;
            $dataPoint['ta_income'] = $incomeDetails->ta_income ?? 0;
            $dataPoint['gov_income'] = $incomeDetails->gov_income ?? 0;
            $dataPoint['corp_income'] = $incomeDetails->corp_income ?? 0;
            $dataPoint['compliment_income'] = $incomeDetails->compliment_income ?? 0;
            $dataPoint['house_use_income'] = $incomeDetails->house_use_income ?? 0;
            $dataPoint['fnb_income'] = $totalFnb;
            $dataPoint['others_income'] = $incomeDetails->others_income ?? 0;
            
            $dataPoint['mice_income'] = $miceRevenueFromBooking;
            
            $totalFromDailyIncome = $incomeDetails->total_revenue ?? 0;
            $dataPoint['total_revenue'] = $totalFromDailyIncome + $miceRevenueFromBooking;
            
            $comparisonData[] = $dataPoint;
        }

        $datasetsForGroupedBar = [];
        $colors = ['rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)'];
        foreach ($selectedPropertiesModels as $index => $property) {
            $propertyData = collect($comparisonData)->firstWhere('name', $property->name);
            $dataValues = [];
            if ($propertyData) {
                foreach($categoryKeysForDisplay as $column){
                    $dataValues[] = $propertyData[$column];
                }
            }
            $datasetsForGroupedBar[] = ['label' => $property->name, 'data' => $dataValues, 'backgroundColor' => $property->chart_color ?? $colors[$index % count($colors)]];
        }
        $chartDataGroupedBar = ['labels' => $categoryLabels, 'datasets' => $datasetsForGroupedBar];

        $period = CarbonPeriod::create($startDate, '1 day', $endDate);
        $dateLabels = collect($period)->map(fn($date) => $date->isoFormat('D MMM'));
        $datasetsForTrend = [];
        
        foreach ($selectedPropertiesModels as $index => $property) {
            $dailyIncomes = DailyIncome::where('property_id', $property->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->select('date', DB::raw("SUM({$totalRevenueRaw}) as daily_total_revenue"))
                ->groupBy('date')->orderBy('date', 'asc')->get()
                ->keyBy(fn($item) => Carbon::parse($item->date)->isoFormat('D MMM'));
            
            $dailyMiceFromBookings = Booking::where('property_id', $property->id)
                ->where('status', 'Booking Pasti')
                ->whereBetween('event_date', [$startDate, $endDate])
                ->select(DB::raw('DATE(event_date) as date'), DB::raw('SUM(total_price) as daily_mice_revenue'))
                ->groupBy('date')
                ->get()
                ->keyBy(fn($item) => Carbon::parse($item->date)->isoFormat('D MMM'));
            
            $trendDataPoints = $dateLabels->map(function($label) use ($dailyIncomes, $dailyMiceFromBookings) {
                $incomeTotal = $dailyIncomes->get($label)->daily_total_revenue ?? 0;
                $miceTotal = $dailyMiceFromBookings->get($label)->daily_mice_revenue ?? 0;
                return $incomeTotal + $miceTotal;
            });

            $datasetsForTrend[] = [
                'label' => $property->name, 
                'data' => $trendDataPoints, 
                'borderColor' => $property->chart_color ?? $colors[$index % count($colors)], 
                'fill' => false, 
                'tension' => 0.1
            ];
        }
        $trendChartData = ['labels' => $dateLabels, 'datasets' => $datasetsForTrend];

        return view('admin.properties.compare_results', [
            'selectedPropertiesModels' => $selectedPropertiesModels,
            'startDateFormatted' => $startDate->isoFormat('D MMMM YYYY'),
            'endDateFormatted' => $endDate->isoFormat('D MMMM YYYY'),
            'comparisonData' => $comparisonData,
            'chartDataGroupedBar' => $chartDataGroupedBar,
            'trendChartData' => $trendChartData,
            'incomeCategories' => $incomeCategories,
        ]);
    }
}