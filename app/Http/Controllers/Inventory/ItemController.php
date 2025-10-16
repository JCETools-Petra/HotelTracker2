<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventory;
use App\Models\Category; // <-- Pastikan ini ada
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    private function getUserPropertyId()
    {
        return Auth::user()->property_id;
    }

    public function index(Request $request)
    {
        $propertyId = $this->getUserPropertyId();
        if (!$propertyId) {
            abort(403, 'Anda tidak ditugaskan ke properti manapun.');
        }

        $query = Inventory::where('property_id', $propertyId)->with('category');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('item_code', 'like', '%' . $search . '%')
                  ->orWhereHas('category', fn($cq) => $cq->where('name', 'like', '%' . $search . '%'));
            });
        }
        
        // [PERBAIKAN 1] Ubah paginasi menjadi 10
        $items = $query->latest()->paginate(10)->withQueryString();
        $property = Auth::user()->property;

        // [LOGIKA BARU] Ambil semua kategori untuk legenda
        $categories = Category::orderBy('name')->get();

        // [LOGIKA BARU] Cek jika ini adalah request AJAX
        if ($request->ajax()) {
            return view('inventory.items._table_data', compact('items'))->render();
        }

        return view('inventory.items.index', compact('items', 'property', 'search', 'categories'));
    }
    
    // ... sisa method (create, store, edit, dll) tidak perlu diubah ...
    
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $categoriesJson = $categories->mapWithKeys(fn($cat) => [$cat->id => $cat->category_code]);
        return view('inventory.items.create', compact('categories', 'categoriesJson'));
    }

    public function store(Request $request)
    {
        $propertyId = $this->getUserPropertyId();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'condition' => 'required|in:baik,rusak',
            'unit_price' => 'nullable|numeric|min:0',
            'minimum_standard_quantity' => 'nullable|integer|min:0',
            'purchase_date' => 'nullable|date',
        ]);

        $category = Category::find($validated['category_id']);
        $categoryCode = $category->category_code;
        
        do {
            $randomPart = strtoupper(Str::random(5));
            $itemCode = "{$categoryCode}-{$randomPart}";
        } while (Inventory::where('item_code', $itemCode)->where('property_id', $propertyId)->exists());

        $validated['item_code'] = $itemCode;
        $validated['property_id'] = $propertyId;
        
        Inventory::create($validated);

        return redirect()->route('inventory.dashboard')->with('success', 'Item baru berhasil ditambahkan.');
    }

    public function edit(Inventory $item)
    {
        if ($item->property_id !== $this->getUserPropertyId()) {
            abort(403);
        }
        $categories = Category::orderBy('name')->get();
        return view('inventory.items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Inventory $item)
    {
        if ($item->property_id !== $this->getUserPropertyId()) {
            abort(403);
        }

        $propertyId = $this->getUserPropertyId();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'condition' => 'required|in:baik,rusak',
            'unit_price' => 'nullable|numeric|min:0',
            'minimum_standard_quantity' => 'nullable|integer|min:0',
            'purchase_date' => 'nullable|date',
        ]);

        $item->update($validated);

        return redirect()->route('inventory.dashboard')->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Inventory $item)
    {
        if ($item->property_id !== $this->getUserPropertyId()) {
            abort(403);
        }
        $item->delete();
        return redirect()->route('inventory.dashboard')->with('success', 'Item berhasil dihapus.');
    }
}