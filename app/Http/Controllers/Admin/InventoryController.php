<?php

namespace App\Http\Controllers\Admin; // NAMESPACE YANG BENAR

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = Inventory::orderBy('name')->paginate(15);
        return view('admin.inventories.index', compact('inventories'));
    }

    public function create()
    {
        return view('admin.inventories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        Inventory::create($request->all());

        return redirect()->route('admin.inventories.index')->with('success', 'Item inventaris berhasil ditambahkan.');
    }

    public function edit(Inventory $inventory)
    {
        return view('admin.inventories.edit', compact('inventory'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $inventory->update($request->all());

        return redirect()->route('admin.inventories.index')->with('success', 'Item inventaris berhasil diperbarui.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('admin.inventories.index')->with('success', 'Item inventaris berhasil dihapus.');
    }
    /**
     * Menampilkan laporan penggunaan amenities.
     */
    public function report()
    {
        $roomAmenities = DB::table('room_amenities')
                           ->join('rooms', 'room_amenities.room_id', '=', 'rooms.id')
                           ->join('inventories', 'room_amenities.inventory_id', '=', 'inventories.id')
                           ->select('rooms.room_number as room_number', 'inventories.name as amenity_name', 'room_amenities.quantity')
                           ->orderBy('rooms.room_number')
                           ->get();
        return view('admin.reports.amenity_usage', compact('roomAmenities'));
    }
}