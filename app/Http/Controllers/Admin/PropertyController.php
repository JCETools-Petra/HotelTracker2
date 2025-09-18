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
            'address' => 'nullable|string',
            // Tambahkan validasi untuk total_rooms dan bar
            'total_rooms' => 'required|integer|min:0',
            'bar_1' => 'nullable|integer',
            'bar_2' => 'nullable|integer',
            'bar_3' => 'nullable|integer',
            'bar_4' => 'nullable|integer',
            'bar_5' => 'nullable|integer',
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
        return view('admin.properties.compare_form', compact('properties'));
    }

    public function showComparisonResults(Request $request)
    {
        $validated = $request->validate([
            'property_ids'   => 'required|array|min:1',
            'property_ids.*' => 'exists:properties,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
        ]);

        $propertyIds = $validated['property_ids'];
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $properties = Property::whereIn('id', $propertyIds)->get();

        $results = DailyIncome::whereIn('property_id', $propertyIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('property_id')
            ->select(
                'property_id',
                DB::raw('SUM(offline_room_income) as offline_revenue, SUM(offline_rooms) as offline_rooms'),
                DB::raw('SUM(online_room_income) as online_revenue, SUM(online_rooms) as online_rooms'),
                DB::raw('SUM(ta_income) as ta_revenue, SUM(ta_rooms) as ta_rooms'),
                DB::raw('SUM(gov_income) as gov_revenue, SUM(gov_rooms) as gov_rooms'),
                DB::raw('SUM(corp_income) as corp_revenue, SUM(corp_rooms) as corp_rooms'),
                DB::raw('SUM(afiliasi_room_income) as afiliasi_revenue, SUM(afiliasi_rooms) as afiliasi_rooms'),
                DB::raw('SUM(total_rooms_revenue) as total_room_revenue, SUM(total_rooms_sold) as total_rooms_sold'),
                DB::raw('SUM(total_fb_revenue) as total_fb_revenue'),
                DB::raw('SUM(mice_room_income) as total_mice_revenue'),
                DB::raw('SUM(others_income) as total_others_revenue'),
                DB::raw('SUM(total_revenue) as total_overall_revenue'),
                DB::raw('AVG(occupancy) as average_occupancy'),
                DB::raw('SUM(total_rooms_revenue) / NULLIF(SUM(total_rooms_sold), 0) as average_arr')
            )
            ->get()
            ->keyBy('property_id');

        // ======================= PERSIAPAN DATA GRAFIK YANG DIPERBAIKI =======================
        $chartData = $properties->map(function ($property) use ($results) {
            // Cek apakah ada hasil untuk properti ini
            $result = $results->get($property->id);
            
            return [
                'label' => $property->name,
                // Jika tidak ada hasil ($result), anggap pendapatannya 0
                'revenue' => $result ? $result->total_overall_revenue : 0,
                'color' => $property->chart_color ?? sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
            ];
        });
        // =================================================================================

        return view('admin.properties.compare_results', compact(
            'properties', 
            'results', 
            'startDate', 
            'endDate',
            'chartData'
        ));
    }

}