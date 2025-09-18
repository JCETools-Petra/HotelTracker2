<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\HotelRoom;
use App\Models\HkAssignment;
use App\Http\Traits\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InventoryController extends Controller
{
    use LogActivity;

    public function index()
    {
        $propertyId = Auth::user()->property_id;
        $rooms = HotelRoom::where('property_id', $propertyId)
            ->whereHas('roomType', function ($query) {
                $query->where('type', 'hotel');
            })
            ->with('roomType')
            ->get();

        return view('housekeeping.inventory.index', compact('rooms'));
    }

    public function selectRoom(Request $request)
    {
        $validated = $request->validate(['room_id' => 'required|exists:hotel_rooms,id']);
        $roomId = $validated['room_id'];

        $assignmentsCount = HkAssignment::where('room_id', $roomId)
                                          ->whereDate('created_at', Carbon::today())
                                          ->count();

        if ($assignmentsCount >= 2) {
            return redirect()->route('housekeeping.inventory.index')
                             ->with('error', 'Anda sudah mencapai batas maksimal 2 kali input untuk kamar ini hari ini.');
        }

        return redirect()->route('housekeeping.inventory.assign', $roomId);
    }

    public function assign(HotelRoom $room)
    {
        if (Auth::user()->property_id !== $room->property_id) {
            abort(403, 'Anda tidak diizinkan untuk mengakses kamar ini.');
        }

        $assignmentsCount = HkAssignment::where('room_id', $room->id)
                                          ->whereDate('created_at', Carbon::today())
                                          ->count();
        
        if ($assignmentsCount >= 2) {
            return redirect()->route('housekeeping.inventory.index')
                             ->with('error', 'Anda sudah mencapai batas maksimal 2 kali input untuk kamar ini hari ini.');
        }

        $inventories = Inventory::where('category', 'ROOM AMENITIES')
                                  ->orderBy('name')
                                  ->get();
        
        $currentAmenities = $room->amenities()->get()->keyBy('id');

        return view('housekeeping.inventory.assign', compact('room', 'inventories', 'currentAmenities'));
    }

    public function updateInventory(Request $request, HotelRoom $room)
    {
        if (Auth::user()->property_id !== $room->property_id) {
            abort(403, 'Anda tidak diizinkan untuk memperbarui kamar ini.');
        }

        $assignmentsCount = HkAssignment::where('room_id', $room->id)
                                          ->whereDate('created_at', Carbon::today())
                                          ->where('user_id', Auth::id())
                                          ->count();
        
        if ($assignmentsCount >= 2) {
            return back()->with('error', 'Anda sudah mencapai batas maksimal 2 kali input untuk kamar ini hari ini.');
        }

        $request->validate([
            'amenities' => 'required|array',
            'amenities.*.quantity' => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $room) {
                $amenitiesToSync = [];
                $inputAmenities = $request->input('amenities', []);
                $currentAmenities = $room->amenities()->get()->keyBy('id');

                foreach ($inputAmenities as $inventoryId => $data) {
                    $quantity = (int) $data['quantity'];
                    $currentQuantity = $currentAmenities->get($inventoryId)->pivot->quantity ?? 0;
                    $quantityDifference = $quantity - $currentQuantity;
                    
                    if ($quantityDifference !== 0) { // Hanya proses jika ada perubahan
                        $inventoryItem = Inventory::findOrFail($inventoryId);

                        // Kurangi stok jika ada penambahan amenities di kamar
                        if ($quantityDifference > 0) {
                            if ($inventoryItem->quantity < $quantityDifference) {
                                throw ValidationException::withMessages([
                                    'amenities.' . $inventoryId . '.quantity' => "Stok untuk {$inventoryItem->name} tidak mencukupi. Sisa: {$inventoryItem->quantity}",
                                ]);
                            }
                            $inventoryItem->decrement('quantity', $quantityDifference);
                        } 
                        // Tambah stok jika ada pengurangan amenities di kamar
                        elseif ($quantityDifference < 0) {
                            $inventoryItem->increment('quantity', abs($quantityDifference));
                        }

                        // Catat setiap item yang digunakan ke dalam HkAssignment
                        HkAssignment::create([
                            'user_id' => Auth::id(),
                            'room_id' => $room->id,
                            'property_id' => $room->property_id,
                            'inventory_id' => $inventoryId,
                            'quantity_used' => $quantityDifference, // Catat selisih penggunaan
                        ]);
                    }
                    
                    if ($quantity > 0) {
                        $amenitiesToSync[$inventoryId] = ['quantity' => $quantity];
                    }
                }

                // Update status amenities terkini di kamar
                $room->amenities()->sync($amenitiesToSync);

                // Catat aktivitas ke log utama
                $this->logActivity(
                    'Memperbarui amenities untuk kamar ' . $room->room_number,
                    $request,
                    $room->property_id 
                );
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->route('housekeeping.inventory.index')->with('success', 'Inventaris untuk kamar ' . $room->room_number . ' berhasil diperbarui.');
    }

    public function history()
    {
        $userId = Auth::id();
        $history = HkAssignment::where('user_id', $userId)
                                ->with(['room' => function ($query) {
                                    $query->with('amenities');
                                }])
                                ->latest()
                                ->get();

        return view('housekeeping.history.index', compact('history'));
    }
}