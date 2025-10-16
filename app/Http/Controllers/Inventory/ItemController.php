<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventory;
use App\Models\Category;
use Illuminate\Validation\Rule;

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
        
        $inventories = $query->latest()->paginate(20)->withQueryString();
        $property = Auth::user()->property;

        return view('inventory.items.index', compact('inventories', 'property', 'search'));
    }

    public function create()
    {
        $categories = Category::where('property_id', $this->getUserPropertyId())->orderBy('name')->get();
        return view('inventory.items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $propertyId = $this->getUserPropertyId();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => ['required', 'string', 'max:50', Rule::unique('inventories')->where('property_id', $propertyId)],
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'condition' => 'required|in:baik,rusak',
            'unit_price' => 'nullable|numeric|min:0',
            'minimum_standard_quantity' => 'nullable|integer|min:0',
            'purchase_date' => 'nullable|date',
        ]);

        $validated['property_id'] = $propertyId;
        Inventory::create($validated);

        return redirect()->route('inventory.dashboard')->with('success', 'Item baru berhasil ditambahkan.');
    }

    public function edit(Inventory $item)
    {
        if ($item->property_id !== $this->getUserPropertyId()) {
            abort(403);
        }
        $categories = Category::where('property_id', $this->getUserPropertyId())->orderBy('name')->get();
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
            'item_code' => ['required', 'string', 'max:50', Rule::unique('inventories')->where('property_id', $propertyId)->ignore($item->id)],
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