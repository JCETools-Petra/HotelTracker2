<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\Category;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function select()
    {
        $properties = Property::all();
        return view('admin.inventories.select_property', compact('properties'));
    }

    public function index(Request $request)
    {
        $property_id = $request->query('property_id');
        if (!$property_id) {
            return redirect()->route('admin.inventories.select')->with('error', 'Silakan pilih properti terlebih dahulu.');
        }

        $property = Property::findOrFail($property_id);
        $inventories = Inventory::where('property_id', $property_id)->with('category')->latest()->paginate(10);
        
        // --- PERBAIKAN DI SINI ---
        // Menambahkan kembali logika untuk mengambil semua kategori untuk legenda
        $allCategories = Category::all();
        
        return view('admin.inventories.index', compact('inventories', 'property', 'allCategories'));
    }

    public function create(Request $request)
    {
        $property_id = $request->query('property_id');
        if (!$property_id) {
            return redirect()->route('admin.inventories.select')->with('error', 'Properti tidak valid.');
        }
        
        $property = Property::findOrFail($property_id);
        $inventoryCategories = Category::all(); 
        
        return view('admin.inventories.create', compact('inventoryCategories', 'property'));
    }

    public function store(Request $request)
    {
        // ... (Fungsi store Anda sudah benar)
        $request->validate([
            'name' => 'required|string|max:255',
            'specification' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:255',
            'condition' => 'required|in:baik,rusak',
            'property_id' => 'required|exists:properties,id',
            'unit_price' => 'required|numeric|min:0',
            'minimum_standard_quantity' => 'required|integer|min:0', // <-- TAMBAHKAN INI
            'purchase_date' => 'required|date', 
        ]);

        $category = Category::find($request->input('category_id'));
        if (!$category) {
            return back()->withInput()->with('error', 'Kategori yang dipilih tidak valid.');
        }
        
        $itemCode = $category->category_code . '-' . strtoupper(uniqid());

        $data = $request->all();
        $data['item_code'] = $itemCode;

        Inventory::create($data);

        return redirect()->route('admin.inventories.index', ['property_id' => $request->property_id])->with('success', 'Item inventaris berhasil dibuat.');
    }

    public function show(Inventory $inventory)
    {
        return redirect()->route('admin.inventories.edit', $inventory);
    }

    public function edit(Inventory $inventory)
    {
        $inventoryCategories = Category::all(); 
        $property = $inventory->property;
        return view('admin.inventories.edit', compact('inventory', 'inventoryCategories', 'property'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        // ... (Fungsi update Anda sudah benar)
        $request->validate([
            'name' => 'required|string|max:255',
            'specification' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:255',
            'condition' => 'required|in:baik,rusak',
            'property_id' => 'required|exists:properties,id',
            'unit_price' => 'required|numeric|min:0',
            'minimum_standard_quantity' => 'required|integer|min:0', // <-- TAMBAHKAN INI
            'purchase_date' => 'required|date', 
        ]);

        $inventory->update($request->except('item_code'));

        return redirect()->route('admin.inventories.index', ['property_id' => $inventory->property_id])->with('success', 'Item inventaris berhasil diperbarui.');
    }

    public function destroy(Inventory $inventory)
    {
        // ... (Fungsi destroy Anda sudah benar)
        $inventory->delete();
        return back()->with('success', 'Item inventaris berhasil dihapus.');
    }
}