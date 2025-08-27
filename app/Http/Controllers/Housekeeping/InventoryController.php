<?php

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\HotelRoom;
use App\Models\HkAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\LogActivity;
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

        $todayAssignmentsCount = HkAssignment::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        $canAssign = $todayAssignmentsCount < 2;

        return view('housekeeping.inventory.index', compact('rooms', 'canAssign'));
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
                                          ->count();
        
        if ($assignmentsCount >= 2) {
            return back()->with('error', 'Anda sudah mencapai batas maksimal 2 kali input untuk kamar ini hari ini.');
        }

        $request->validate([
            'amenities' => 'required|array',
            'amenities.*.quantity' => 'required|integer|min:0',
        ]);

        $inputAmenities = $request->input('amenities', []);
        $currentAmenities = $room->amenities()->get()->keyBy('id');
        
        // Cek stok sebelum memulai transaksi
        foreach ($inputAmenities as $inventoryId => $data) {
            $quantity = (int) $data['quantity'];
            $currentQuantity = $currentAmenities->get($inventoryId)->pivot->quantity ?? 0;
            $quantityDifference = $quantity - $currentQuantity;

            if ($quantityDifference > 0) {
                $inventoryItem = Inventory::findOrFail($inventoryId);
                if ($inventoryItem->quantity < $quantityDifference) {
                    throw ValidationException::withMessages([
                        'amenities.' . $inventoryId . '.quantity' => "Stok untuk {$inventoryItem->name} tidak mencukupi. Sisa stok: {$inventoryItem->quantity}",
                    ]);
                }
            }
        }

        try {
            DB::transaction(function () use ($inputAmenities, $room, $currentAmenities) {
                $amenitiesToSync = [];
                foreach ($inputAmenities as $inventoryId => $data) {
                    $quantity = (int) $data['quantity'];
                    $currentQuantity = $currentAmenities->get($inventoryId)->pivot->quantity ?? 0;
                    $quantityDifference = $quantity - $currentQuantity;
                    
                    if ($quantityDifference > 0) {
                        // Kurangi stok karena sudah divalidasi di atas
                        $inventoryItem = Inventory::findOrFail($inventoryId);
                        $inventoryItem->decrement('quantity', $quantityDifference);
                    } elseif ($quantityDifference < 0) {
                        // Jika jumlah berkurang, kembalikan stok
                        $inventoryItem = Inventory::findOrFail($inventoryId);
                        $inventoryItem->increment('quantity', abs($quantityDifference));
                    }
                    
                    if ($quantity > 0) {
                        $amenitiesToSync[$inventoryId] = ['quantity' => $quantity];
                    }
                }
                $room->amenities()->sync($amenitiesToSync);

                HkAssignment::create([
                    'user_id' => Auth::id(),
                    'room_id' => $room->id,
                    'property_id' => $room->property_id,
                ]);
                $this->logActivity('Memperbarui amenities untuk kamar ' . $room->room_number, $request);
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