<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType; // Tambahkan ini
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Menampilkan daftar ruangan untuk sebuah properti.
     */
    public function index(Property $property)
    {
        // HANYA TAMPILKAN RUANGAN DENGAN TIPE 'mice'
        $rooms = $property->rooms()->whereHas('roomType', function ($query) {
            $query->where('type', 'mice');
        })->with('roomType')->latest()->paginate(10);
        
        return view('admin.rooms.index', compact('property', 'rooms'));
    }

    /**
     * Menampilkan form untuk membuat ruangan baru.
     */
    public function create(Property $property)
    {
        // HANYA AMBIL TIPE RUANGAN 'mice'
        $roomTypes = RoomType::where('type', 'mice')->get();
        return view('admin.rooms.create', compact('property', 'roomTypes'));
    }

    /**
     * Menyimpan ruangan baru ke database.
     */
    public function store(Request $request, Property $property)
    {
        // $this->authorize('manage-data'); // Pastikan Anda memiliki otorisasi ini

        $validated = $request->validate([
            'room_number' => 'required|string|max:255|unique:rooms,room_number,NULL,id,property_id,'.$property->id,
            'room_type_id' => 'required|exists:room_types,id',
            'capacity' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);
        
        $validated['property_id'] = $property->id;

        $property->rooms()->create($validated);

        return redirect()->route('admin.properties.rooms.index', $property)
                         ->with('success', 'Kamar berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit ruangan.
     */
    public function edit(Room $room)
    {
        $property = $room->property;
        $roomTypes = RoomType::all();
        return view('admin.rooms.edit', compact('room', 'property', 'roomTypes'));
    }

    /**
     * Memperbarui data ruangan di database.
     */
    public function update(Request $request, Room $room)
    {
        // $this->authorize('manage-data'); // Pastikan Anda memiliki otorisasi ini

        $validated = $request->validate([
            'room_number' => 'required|string|max:255|unique:rooms,room_number,'.$room->id.',id,property_id,'.$room->property->id,
            'room_type_id' => 'required|exists:room_types,id',
            'capacity' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $room->update($validated);

        return redirect()->route('admin.properties.rooms.index', $room->property)
                         ->with('success', 'Kamar berhasil diperbarui.');
    }

    /**
     * Menghapus ruangan dari database.
     */
    public function destroy(Room $room)
    {
        // $this->authorize('manage-data'); // Pastikan Anda memiliki otorisasi ini
        $property = $room->property;
        $room->delete();

        return redirect()->route('admin.properties.rooms.index', $property)
                         ->with('success', 'Kamar berhasil dihapus.');
    }
}