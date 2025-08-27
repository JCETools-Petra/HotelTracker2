<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\DailyOccupancy;
use App\Models\Property; // <-- INI ADALAH BARIS PERBAIKANNYA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonPeriod;
use App\Http\Traits\CalculatesBarPrices;
use App\Models\RoomType;

class ReservationController extends Controller
{
    use CalculatesBarPrices;
    /**
     * Menampilkan daftar reservasi.
     */
    public function getActiveBarPrice(RoomType $roomType)
    {
        $property = $roomType->property;

        $occupancyToday = DailyOccupancy::where('property_id', $property->id)
            ->where('date', today()->toDateString())
            ->first();
            
        $occupiedRooms = $occupancyToday ? $occupancyToday->occupied_rooms : 0;
        
        $activeBarLevel = $this->getActiveBarLevel($occupiedRooms, $property);
        $activePrice = $this->calculateActiveBarPrice($roomType, $activeBarLevel);

        return response()->json(['price' => $activePrice]);
    }
    public function index(Request $request)
    {
        // Ambil kata kunci pencarian dari request
        $search = $request->input('search');
    
        $query = Reservation::query();
    
        // Peran 'ecommerce' bisa melihat semua reservasi.
        // Peran lain (jika ada yang mengakses) hanya melihat milik mereka.
        if (Auth::user()->role !== 'online_ecommerce') {
            $query->where('user_id', Auth::id());
        }
    
        // Terapkan filter pencarian jika ada input
        $query->when($search, function ($q, $search) {
            // Cari berdasarkan nama tamu
            return $q->where('guest_name', 'like', "%{$search}%");
        });
        
        // Urutkan dan tampilkan dengan paginasi
        $reservations = $query->orderBy('checkin_date', 'desc')
            ->paginate(10)
            ->withQueryString(); // Agar paginasi tetap membawa query pencarian
    
        // Kirim data ke view
        return view('property.reservations.index', compact('reservations', 'search'));
    }

    /**
     * Menampilkan form untuk membuat reservasi baru.
     */
    public function create()
    {
        // Ambil properti yang terikat pada user
        $property = Auth::user()->property;

        if (!$property) {
            return redirect()->route('property.dashboard')->with('error', 'Akun Anda tidak terikat pada properti manapun.');
        }
        
        // Ambil tipe kamar yang sesuai dengan properti user
        $roomTypes = $property->roomTypes()->orderBy('name')->get();

        return view('property.reservations.create', compact('property', 'roomTypes'));
    }

    /**
     * Menyimpan reservasi baru ke database dan mengupdate okupansi.
     */
    public function store(Request $request)
    {
        $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'checkin_date' => 'required|date',
            'checkout_date' => 'required|date|after_or_equal:checkin_date',
            'property_id' => 'required|integer|exists:properties,id',
            'room_type_id' => 'required|integer|exists:room_types,id', 
            'number_of_rooms' => 'required|integer|min:1',
            'final_price' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $reservation = Reservation::create($data);

        $this->updateDailyOccupancies($reservation, 'increment');

        return redirect()->route('property.reservations.index')
        ->with('success', 'Reservasi untuk ' . $request->guest_name . ' berhasil dibuat.');
    }
    
    public function getRoomTypesByProperty(Property $property)
    {
        // Pastikan hanya mengambil tipe kamar dari properti yang diminta
        $roomTypes = $property->roomTypes()->orderBy('name')->get(['id', 'name']);
        
        return response()->json($roomTypes);
    }

    /**
     * Menampilkan form untuk mengedit reservasi.
     */
   public function edit(Reservation $reservation)
    {
        // Pastikan pengguna hanya bisa mengedit reservasi propertinya
        if (Auth::user()->property_id != $reservation->property_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses reservasi ini.');
        }
    
        $property = Auth::user()->property;
        $roomTypes = $property->roomTypes()->orderBy('name')->get();
    
        // Tampilkan view 'edit' dengan data yang diperlukan
        return view('property.reservations.edit', compact('reservation', 'property', 'roomTypes'));
    }

    /**
     * Mengupdate reservasi di database dan menyesuaikan okupansi.
     */
    public function update(Request $request, Reservation $reservation)
    {
        $oldReservationData = $reservation->replicate();

        $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'checkin_date' => 'required|date',
            'checkout_date' => 'required|date|after_or_equal:checkin_date',
            'property_id' => 'required|integer|exists:properties,id',
            'number_of_rooms' => 'required|integer|min:1',
        ]);
        
        $reservation->update($request->all());

        $this->updateDailyOccupancies($oldReservationData, 'decrement');
        $this->updateDailyOccupancies($reservation, 'increment');

        return redirect()->route('ecommerce.reservations.index')
            ->with('success', 'Reservasi untuk ' . $request->guest_name . ' berhasil diperbarui.');
    }

    /**
     * Menghapus reservasi dan mengurangi okupansi.
     */
    public function destroy(Reservation $reservation)
    {
        $this->updateDailyOccupancies($reservation, 'decrement');
        $reservation->delete();

        return redirect()->route('property.reservations.index')
        ->with('success', 'Reservasi berhasil dihapus.');
    }

    /**
     * Helper method untuk mengupdate data okupansi harian.
     */
    private function updateDailyOccupancies(Reservation $reservation, string $action)
    {
        $period = CarbonPeriod::create($reservation->checkin_date, $reservation->checkout_date->subDay());
        $rooms = $reservation->number_of_rooms;

        foreach ($period as $date) {
            $dailyOccupancy = DailyOccupancy::firstOrCreate(
                [
                    'property_id' => $reservation->property_id,
                    'date' => $date->toDateString(),
                ],
                [
                    'occupied_rooms' => 0,
                    'reservasi_ota' => 0,
                    'reservasi_properti' => 0,
                ]
            );

            if ($action === 'increment') {
                $dailyOccupancy->increment('reservasi_ota', $rooms);
                $dailyOccupancy->increment('occupied_rooms', $rooms);
            } else { // decrement
                $dailyOccupancy->decrement('reservasi_ota', $rooms);
                $dailyOccupancy->decrement('occupied_rooms', $rooms);
            }
        }
    }
}