<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\HkAssignment; // 1. Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // 2. Tambahkan ini
use App\Http\Traits\LogActivity; // 1. PANGGIL TRAIT

class InventoryController extends Controller
{
    use LogActivity; // 2. GUNAKAN TRAIT

    public function index(Property $property)
    {
        $inventories = $property->inventories()->paginate(10);
        return view('admin.inventories.index', compact('property', 'inventories'));
    }
    
    public function showPropertySelection()
    {
        $properties = Property::orderBy('name')->get();
        return view('admin.inventories.select_property', compact('properties'));
    }

    public function create(Property $property)
    {
        return view('admin.inventories.create', compact('property'));
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'category' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
        ]);

        $inventory = $property->inventories()->create($validated);

        // 3. TAMBAHKAN LOGGING
        $this->logActivity(
            "Membuat inventaris baru: {$inventory->name}",
            $request,
            $property->id
        );

        return redirect()->route('admin.inventories.index', $property)->with('success', 'Inventory created successfully.');
    }

    public function edit(Inventory $inventory)
    {
        $inventory->load('property');
        return view('admin.inventories.edit', compact('inventory'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'category' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
        ]);

        $inventory->update($validated);

        // 3. TAMBAHKAN LOGGING
        $this->logActivity(
            "Memperbarui inventaris: {$inventory->name}",
            $request,
            $inventory->property_id
        );

        return redirect()->route('admin.inventories.index', $inventory->property_id)->with('success', 'Inventory updated successfully.');
    }

    public function destroy(Request $request, Inventory $inventory)
    {
        $propertyId = $inventory->property_id;
        $inventoryName = $inventory->name;

        $inventory->delete();

        $this->logActivity(
            "Menghapus inventaris: {$inventoryName}",
            $request,
            $propertyId
        );

        return redirect()->route('admin.inventories.index', $propertyId)->with('success', 'Inventory deleted successfully.');
    }
    
    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Query untuk mengambil data penggunaan amenities dari tabel HkAssignment
        $reportData = HkAssignment::join('inventories', 'hk_assignments.inventory_id', '=', 'inventories.id')
            ->join('hotel_rooms', 'hk_assignments.room_id', '=', 'hotel_rooms.id')
            ->join('properties', 'hotel_rooms.property_id', '=', 'properties.id')
            ->where('inventories.category', 'ROOM AMENITIES')
            ->when($request->filled('start_date'), function ($query) use ($request) {
                return $query->whereDate('hk_assignments.created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                return $query->whereDate('hk_assignments.created_at', '<=', $request->end_date);
            })
            ->select(
                'properties.name as property_name',
                'inventories.name as amenity_name',
                DB::raw('SUM(hk_assignments.quantity_used) as total_used')
            )
            ->groupBy('properties.name', 'inventories.name')
            ->orderBy('properties.name')
            ->orderBy('total_used', 'desc')
            ->get();

        return view('admin.reports.amenity_usage', compact('reportData'));
    }
}