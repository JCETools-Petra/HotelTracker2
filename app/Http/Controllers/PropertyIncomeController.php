<?php

namespace App\Http\Controllers;

// ===== BAGIAN USE STATEMENT (PASTIKAN SEMUA INI ADA) =====
use App\Models\DailyIncome;
use App\Models\Property;
use App\Http\Traits\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PropertyIncomesExport;
use Illuminate\Support\Str;
use App\Services\ReservationPriceService;
use App\Models\DailyOccupancy;
use App\Models\Reservation;
use App\Events\OccupancyUpdated;

class PropertyIncomeController extends Controller
{
    use LogActivity;
    
    protected $priceService;

    public function __construct(ReservationPriceService $priceService)
    {
        $this->priceService = $priceService;
    }
    
    public function calendar()
    {
        $property = auth()->user()->property;
        return view('property.calendar.index', compact('property'));
    }
    
    public function getCalendarData(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            return response()->json(['events' => [], 'chartData' => []]);
        }
        
        $events = Reservation::where('property_id', $property->id)
            ->select('id', 'guest_name as title', 'checkin_date as start', 'checkout_date as end')
            ->get();

        $startDate = Carbon::now()->subDays(30);
        $chartOccupancyData = DailyOccupancy::where('property_id', $property->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date', 'asc')
            ->get(['date', 'occupied_rooms']);

        $chartData = [
            'labels' => $chartOccupancyData->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('d M')),
            'data' => $chartOccupancyData->pluck('occupied_rooms'),
        ];
        
        return response()->json([
            'events' => $events,
            'chartData' => $chartData,
        ]);
    }

    public function dashboard()
    {
        $user = Auth::user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat pada properti manapun.');
        }

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $incomesThisMonthQuery = DailyIncome::where('property_id', $property->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth]);

        $totalRevenue = (clone $incomesThisMonthQuery)->sum('total_revenue');
        $totalRoomRevenue = (clone $incomesThisMonthQuery)->sum('total_rooms_revenue');
        $totalFbRevenue = (clone $incomesThisMonthQuery)->sum('total_fb_revenue');
        $totalOthersIncome = (clone $incomesThisMonthQuery)->sum('others_income');

        $occupancyToday = DailyOccupancy::where('property_id', $property->id)
            ->where('date', today()->toDateString())
            ->first();

        $latestIncomes = $property->dailyIncomes()
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        return view('property.dashboard', [
            'property' => $property,
            'totalRevenue' => $totalRevenue,
            'totalRoomRevenue' => $totalRoomRevenue,
            'totalFbRevenue' => $totalFbRevenue,
            'totalOthersIncome' => $totalOthersIncome,
            'occupancyToday' => $occupancyToday,
            'latestIncomes' => $latestIncomes,
        ]);
    }


    public function updateOccupancy(Request $request)
    {
        $user = Auth::user();
        $property = $user->property;
    
        if (!$property) {
            return redirect()->back()->with('error', 'Akun Anda tidak terikat dengan properti manapun.');
        }
    
        $validated = $request->validate([
            'occupied_rooms' => 'required|integer|min:0',
            'date' => 'required|date_format:Y-m-d',
        ]);
    
        $manualRooms = $validated['occupied_rooms'];
    
        $dailyOccupancy = DailyOccupancy::firstOrCreate(
            [
                'property_id' => $property->id,
                'date' => $validated['date'],
            ],
            [
                'occupied_rooms' => 0,
                'reservasi_ota' => 0,
                'reservasi_properti' => 0,
            ]
        );
    
        $dailyOccupancy->reservasi_properti = $manualRooms;
        $dailyOccupancy->occupied_rooms = $dailyOccupancy->reservasi_ota + $manualRooms;
        $dailyOccupancy->save();
    
        OccupancyUpdated::dispatch($property, $dailyOccupancy);
        
        if (in_array(LogActivity::class, class_uses($this))) {
            $this->logActivity('Memperbarui okupansi properti manual menjadi ' . $manualRooms . ' kamar untuk tanggal ' . $validated['date'], $request);
        }
        
        return redirect()->route('property.dashboard')->with('success', 'Jumlah okupansi properti berhasil diperbarui.');
    }

    public function createOtaReservation()
    {
        $user = Auth::user();
        $property = $user->property;
        $sources = ['Traveloka', 'Booking.com', 'Agoda', 'Tiket.com'];

        return view('property.reservations.create', compact('property', 'sources'));
    }

    public function storeOtaReservation(Request $request)
    {
        $user = Auth::user();
        $property = $user->property;

        $validated = $request->validate([
            'source' => 'required|string',
            'guest_name' => 'required|string|max:255',
            'checkin_date' => 'required|date',
            'checkout_date' => 'required|date|after_or_equal:checkin_date',
        ]);

        $finalPrice = $this->priceService->getCurrentPricesForProperty($property->id, $validated['checkin_date'])
                                        ->firstWhere('name', $request->room_type_name)['price_ota'] ?? 0;

        Reservation::create($validated + [
            'final_price' => $finalPrice,
            'property_id' => $property->id
        ]);

        $this->logActivity('Menambahkan reservasi OTA baru untuk tamu: ' . $validated['guest_name'], $request);

        return redirect()->route('property.reservations.create')->with('success', 'Reservasi OTA berhasil ditambahkan.');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $property = $user->property;

        if (!$property) {
            return redirect('/')->with('error', 'Anda tidak terkait dengan properti manapun.');
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DailyIncome::where('property_id', $property->id);

        if ($startDate) {
            try {
                $query->whereDate('date', '>=', Carbon::parse($startDate));
            } catch (\Exception $e) {}
        }
        if ($endDate) {
            try {
                $query->whereDate('date', '<=', Carbon::parse($endDate));
            } catch (\Exception $e) {}
        }

        $incomes = $query->orderBy('date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('property.income.index', compact('incomes', 'property', 'startDate', 'endDate'));
    }


    public function create()
    {
        $user = Auth::user();
        $property = $user->property;

        if (!$property) {
            return redirect('/')->with('error', 'Anda tidak terkait dengan properti manapun.');
        }

        return view('property.income.create', [
            'property' => $property,
            'date' => old('date', Carbon::today()->toDateString())
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $property = $user->property;
        if (!$property) {
            abort(403);
        }
    
        $validatedData = $request->validate([
            'date' => 'required|date|unique:daily_incomes,date,NULL,id,property_id,' . $property->id,
            'offline_rooms' => 'required|integer|min:0',
            'offline_room_income' => 'required|numeric|min:0',
            'online_rooms' => 'required|integer|min:0',
            'online_room_income' => 'required|numeric|min:0',
            'ta_rooms' => 'required|integer|min:0',
            'ta_income' => 'required|numeric|min:0',
            'gov_rooms' => 'required|integer|min:0',
            'gov_income' => 'required|numeric|min:0',
            'corp_rooms' => 'required|integer|min:0',
            'corp_income' => 'required|numeric|min:0',
            'compliment_rooms' => 'required|integer|min:0',
            'compliment_income' => 'required|numeric|min:0',
            'house_use_rooms' => 'required|integer|min:0',
            'house_use_income' => 'required|numeric|min:0',
            'afiliasi_rooms' => 'required|integer|min:0',
            'afiliasi_room_income' => 'required|numeric|min:0',
            'breakfast_income' => 'required|numeric|min:0',
            'lunch_income' => 'required|numeric|min:0',
            'dinner_income' => 'required|numeric|min:0',
            'others_income' => 'required|numeric|min:0',
        ], [
            'date.unique' => 'Pendapatan untuk tanggal ini sudah pernah dicatat.',
        ]);
    
        // Hapus semua perhitungan manual dari sini
        $incomeData = array_merge($validatedData, [
            'property_id' => $property->id,
            'user_id' => $user->id,
        ]);
    
        $income = DailyIncome::create($incomeData);
    
        // Panggil method kalkulasi terpusat dari model
        $income->recalculateTotals();
        $income->save(); // Simpan setelah kalkulasi
    
        $formattedDate = Carbon::parse($income->date)->isoFormat('D MMMM YYYY');
        $this->logActivity('Mencatat pendapatan harian baru untuk tanggal ' . $formattedDate, $request, $property->id);

        return redirect()->route('property.income.index')->with('success', 'Pendapatan harian berhasil dicatat.');
    }

    // ==========================================================
    // >> AWAL PERUBAHAN <<
    // ==========================================================
    public function edit(DailyIncome $income)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->property_id != $income->property_id) {
            abort(403, 'Akses tidak diizinkan untuk mengedit data ini.');
        }
        $property = $user->property;
        return view('property.income.edit', compact('income', 'property'));
    }
    
    public function update(Request $request, DailyIncome $income)
    {
        $user = Auth::user();
        $this->authorize('update', $income);
    
        $validatedData = $request->validate([
            'date' => 'required|date|unique:daily_incomes,date,' . $income->id . ',id,property_id,' . $income->property_id,
            'offline_rooms' => 'required|integer|min:0', 'offline_room_income' => 'required|numeric|min:0',
            'online_rooms' => 'required|integer|min:0', 'online_room_income' => 'required|numeric|min:0',
            'ta_rooms' => 'required|integer|min:0', 'ta_income' => 'required|numeric|min:0',
            'gov_rooms' => 'required|integer|min:0', 'gov_income' => 'required|numeric|min:0',
            'corp_rooms' => 'required|integer|min:0', 'corp_income' => 'required|numeric|min:0',
            'compliment_rooms' => 'required|integer|min:0', 'compliment_income' => 'required|numeric|min:0',
            'house_use_rooms' => 'required|integer|min:0', 'house_use_income' => 'required|numeric|min:0',
            'afiliasi_rooms' => 'required|integer|min:0', 'afiliasi_room_income' => 'required|numeric|min:0',
            'breakfast_income' => 'required|numeric|min:0',
            'lunch_income' => 'required|numeric|min:0',
            'dinner_income' => 'required|numeric|min:0',
            'others_income' => 'required|numeric|min:0',
        ], [
            'date.unique' => 'Pendapatan untuk tanggal ini sudah ada.',
        ]);
    
        // Hapus semua perhitungan manual dari sini
        $income->update($validatedData);

        // Panggil method kalkulasi terpusat dari model
        $income->recalculateTotals();
        $income->save(); // Simpan setelah kalkulasi
    
        $formattedDate = Carbon::parse($income->date)->isoFormat('D MMMM YYYY');
        $this->logActivity('Memperbarui data pendapatan harian untuk tanggal ' . $formattedDate, $request, $income->property_id);
    
        if ($user->role === 'admin') {
            return redirect()->route('admin.properties.show', $income->property_id)->with('success', 'Data pendapatan berhasil diperbarui.');
        }
    
        return redirect()->route('property.income.index')->with('success', 'Data pendapatan berhasil diperbarui.');
    }

    public function destroy(DailyIncome $income)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Menggunakan Policy untuk otorisasi
        $this->authorize('delete', $income);

        $originalDate = $income->date;
        $income->delete();

        $formattedDate = Carbon::parse($originalDate)->isoFormat('D MMMM YYYY');
        $this->logActivity('Menghapus data pendapatan harian untuk tanggal ' . $formattedDate, request());

        if ($user->role === 'admin') {
            return back()->with('success', 'Data pendapatan untuk tanggal ' . $formattedDate . ' berhasil dihapus.');
        }

        return redirect()->route('property.income.index')->with('success', 'Data pendapatan untuk tanggal ' . $formattedDate . ' berhasil dihapus.');
    }
    // ==========================================================
    // >> AKHIR PERUBAHAN <<
    // ==========================================================

    public function exportIncomesExcel(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->property_id) {
            return redirect()->back()->with('error', 'Tidak dapat mengekspor data, properti tidak ditemukan.');
        }

        $propertyId = $user->property_id;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $fileName = 'laporan_pendapatan_' . Str::slug($user->property->name) . '_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new PropertyIncomesExport($propertyId, $startDate, $endDate), $fileName);
    }

    public function exportIncomesCsv(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->property_id) {
            return redirect()->back()->with('error', 'Tidak dapat mengekspor data, properti tidak ditemukan.');
        }

        $propertyId = $user->property_id;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $fileName = 'laporan_pendapatan_' . Str::slug($user->property->name) . '_' . Carbon::now()->format('Ymd_His') . '.csv';

        return Excel::download(new PropertyIncomesExport($propertyId, $startDate, $endDate), $fileName, \Maatwebsite\Excel\Excel::CSV);
    }
}