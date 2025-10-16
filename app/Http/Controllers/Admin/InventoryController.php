<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\Category; // <-- [PERBAIKAN] Tambahkan baris ini
use Illuminate\Http\Request;
use App\Exports\InventoryExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function select()
    {
        $properties = Property::orderBy('name')->get();
        return view('admin.inventories.select_property', compact('properties'));
    }

    public function index(Request $request)
    {
        $propertyId = $request->query('property_id');
        if (!$propertyId) {
            return redirect()->route('admin.inventories.select')->with('error', 'Silakan pilih properti terlebih dahulu.');
        }

        $property = Property::findOrFail($propertyId);
        $query = Inventory::where('property_id', $propertyId)->with('category');
        
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $inventories = $query->latest()->paginate(10)->withQueryString();
        
        // Baris ini sekarang akan berfungsi dengan benar
        $allCategories = Category::orderBy('name')->get(); 

        if ($request->ajax()) {
            return view('admin.inventories._table_data', ['inventories' => $inventories])->render();
        }

        return view('admin.inventories.index', compact('property', 'inventories', 'allCategories', 'search'));
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
    
    public function exportExcel(Request $request)
    {
        $propertyId = $request->query('property_id');

        if (!$propertyId) {
            return redirect()->back()->with('error', 'Properti tidak ditemukan untuk diekspor.');
        }

        $property = Property::find($propertyId);
        $fileName = 'Inventaris_' . Str::slug($property->name ?? 'property-' . $propertyId) . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new InventoryExport($propertyId), $fileName);
    }
}