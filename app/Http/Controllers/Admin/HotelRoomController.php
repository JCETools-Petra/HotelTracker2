<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\HotelRoom; // BARIS INI DITAMBAHKAN
use Illuminate\Http\Request;

class HotelRoomController extends Controller
{
    /**
     * Menampilkan daftar kamar hotel untuk properti tertentu.
     */
    public function index(Property $property)
    {
        $rooms = $property->hotelRooms()->whereHas('roomType', function ($query) {
            $query->where('type', 'hotel');
        })->with('roomType')->latest()->paginate(10);

        return view('admin.hotel_rooms.index', compact('property', 'rooms'));
    }

    /**
     * Menampilkan form untuk membuat kamar hotel baru.
     */
    public function create(Property $property)
    {
        $roomTypes = RoomType::where('type', 'hotel')->get();
        return view('admin.hotel_rooms.create', compact('property', 'roomTypes'));
    }

    /**
     * Menyimpan kamar hotel baru.
     */
    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:255|unique:hotel_rooms,room_number,NULL,id,property_id,'.$property->id,
            'room_type_id' => 'required|exists:room_types,id',
            'capacity' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);
        
        $validated['property_id'] = $property->id;

        // MENGGUNAKAN MODEL YANG BENAR UNTUK MENYIMPAN KE TABEL hotel_rooms
        HotelRoom::create($validated);

        return redirect()->route('admin.properties.hotel-rooms.index', $property)
                         ->with('success', 'Kamar hotel berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit kamar hotel.
     */
    public function edit(HotelRoom $hotel_room) // PARAMETER DIUBAH DARI Room KE HotelRoom
    {
        $property = $hotel_room->property;
        $roomTypes = RoomType::where('type', 'hotel')->get();
        return view('admin.hotel_rooms.edit', compact('hotel_room', 'property', 'roomTypes'));
    }

    /**
     * Memperbarui kamar hotel.
     */
    public function update(Request $request, HotelRoom $hotel_room) // PARAMETER DIUBAH DARI Room KE HotelRoom
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:255|unique:hotel_rooms,room_number,'.$hotel_room->id.',id,property_id,'.$hotel_room->property->id,
            'room_type_id' => 'required|exists:room_types,id',
            'capacity' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $hotel_room->update($validated);

        return redirect()->route('admin.properties.hotel-rooms.index', $hotel_room->property)
                         ->with('success', 'Kamar hotel berhasil diperbarui.');
    }

    /**
     * Menghapus kamar hotel.
     */
    public function destroy(HotelRoom $hotel_room) // PARAMETER DIUBAH DARI Room KE HotelRoom
    {
        $property = $hotel_room->property;
        $hotel_room->delete();

        return redirect()->route('admin.properties.hotel-rooms.index', $property)
                         ->with('success', 'Kamar hotel berhasil dihapus.');
    }
}