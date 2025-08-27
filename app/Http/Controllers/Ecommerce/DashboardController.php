<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Traits\LogActivity;
use App\Services\ReservationPriceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\DailyOccupancy; // Pastikan ini ada
use App\Models\Reservation;    // Pastikan ini ada
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Traits\CalculatesBarPrices;

class DashboardController extends Controller
{
    use LogActivity, CalculatesBarPrices;

    protected $priceService;

    public function __construct(ReservationPriceService $priceService)
    {
        $this->priceService = $priceService;
    }

    // app/Http/Controllers/Ecommerce/DashboardController.php

    public function index(Request $request)
    {
        $properties = Property::orderBy('name')->get();
        $selectedPropertyId = $request->input('property_id');
        $selectedProperty = null;
        $roomTypePrices = collect();
        $currentOccupancyInfo = null;

        if ($selectedPropertyId) {
            $selectedProperty = Property::with('roomTypes.pricingRule')->find($selectedPropertyId);

            // 1. Ambil data okupansi hari ini
            $occupancyToday = DailyOccupancy::where('property_id', $selectedPropertyId)
                ->where('date', today()->toDateString())
                ->first();

            $occupiedRooms = $occupancyToday ? $occupancyToday->occupied_rooms : 0;
            
            // 2. Tentukan level BAR yang aktif berdasarkan JUMLAH KAMAR
            $activeBarLevel = $this->getActiveBarLevel($occupiedRooms, $selectedProperty);
            
            // Siapkan info untuk ditampilkan di view
            $currentOccupancyInfo = [
                'occupied_rooms' => $occupiedRooms,
                'active_bar' => $activeBarLevel,
            ];

            // 3. Hitung harga yang berlaku untuk setiap tipe kamar
            $roomTypePrices = $selectedProperty->roomTypes->map(function ($roomType) use ($activeBarLevel) {
                return [
                    'name' => $roomType->name,
                    'active_price' => $this->calculateActiveBarPrice($roomType, $activeBarLevel),
                ];
            });
        }
        
        $this->logActivity('Melihat dashboard ecommerce dan harga BAR.', $request);

        return view('ecommerce.dashboard', compact('properties', 'selectedProperty', 'roomTypePrices', 'selectedPropertyId', 'currentOccupancyInfo'));
    }

    /**
     * PERBAIKAN: Menentukan level BAR berdasarkan JUMLAH KAMAR.
     */
    private function getActiveBarLevel(int $occupiedRooms, Property $property): int
    {
        // Membandingkan jumlah kamar terisi dengan ambang batas bar
        if ($occupiedRooms <= $property->bar_1) return 1;
        if ($occupiedRooms <= $property->bar_2) return 2;
        if ($occupiedRooms <= $property->bar_3) return 3;
        if ($occupiedRooms <= $property->bar_4) return 4;
        
        // Jika di atas bar_4, dianggap BAR 5
        return 5;
    }

    /**
     * Menghitung harga BAR yang aktif untuk satu tipe kamar.
     */
    private function calculateActiveBarPrice(RoomType $roomType, int $activeBarLevel)
    {
        $rule = $roomType->pricingRule;
        if (!$rule || !$rule->starting_bar) {
            return $roomType->bottom_rate;
        }

        if ($activeBarLevel < $rule->starting_bar) {
            return $rule->bottom_rate;
        }

        $price = $rule->bottom_rate;
        $increaseFactor = 1 + ($rule->percentage_increase / 100);

        for ($i = 0; $i < ($activeBarLevel - $rule->starting_bar); $i++) {
            $price *= $increaseFactor;
        }
        
        return $price;
    }


    public function calendar()
    {
        $properties = Property::orderBy('name')->get();
        return view('ecommerce.calendar.index', compact('properties'));
    }

    public function getCalendarData(Request $request)
    {
        $propertyId = $request->query('property_id');
        $range = $request->query('range', 'month');

        $eventsQuery = Reservation::query();
        if ($propertyId && $propertyId !== 'all') {
            $eventsQuery->where('property_id', $propertyId);
        }
        $events = $eventsQuery->select('id', 'guest_name as title', 'checkin_date as start', 'checkout_date as end')->get();

        $startDate = $range === 'year' ? Carbon::now()->subYear() : Carbon::now()->subDays(30);
        $chartQuery = DailyOccupancy::where('date', '>=', $startDate);
        if ($propertyId && $propertyId !== 'all') {
            $chartQuery->where('property_id', $propertyId);
        }
        $chartOccupancyData = $chartQuery->select('date', DB::raw('SUM(occupied_rooms) as total_occupied'))
            ->groupBy('date')->orderBy('date', 'asc')->get();
        
        $chartData = [
            'labels' => $chartOccupancyData->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('d M')),
            'data' => $chartOccupancyData->pluck('total_occupied'),
        ];
        
        return response()->json([
            'events' => $events,
            'chartData' => $chartData,
        ]);
    }
}