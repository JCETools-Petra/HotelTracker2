<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    private function getUserPropertyId()
    {
        return Auth::user()->property_id;
    }

    public function index()
    {
        $categories = Category::where('property_id', $this->getUserPropertyId())
            ->latest()
            ->paginate(15);
        
        return view('inventory.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('inventory.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->where('property_id', $this->getUserPropertyId())],
            'category_code' => ['required', 'string', 'max:50', Rule::unique('categories')->where('property_id', $this->getUserPropertyId())],
        ]);

        Category::create([
            'name' => $validated['name'],
            'category_code' => strtoupper($validated['category_code']),
            'property_id' => $this->getUserPropertyId(),
        ]);

        return redirect()->route('inventory.categories.index')->with('success', 'Kategori baru berhasil dibuat.');
    }

    public function edit(Category $category)
    {
        // Pastikan pengguna hanya bisa mengedit kategori dari propertinya sendiri
        if ($category->property_id !== $this->getUserPropertyId()) {
            abort(403);
        }
        return view('inventory.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        if ($category->property_id !== $this->getUserPropertyId()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->where('property_id', $this->getUserPropertyId())->ignore($category->id)],
            'category_code' => ['required', 'string', 'max:50', Rule::unique('categories')->where('property_id', $this->getUserPropertyId())->ignore($category->id)],
        ]);

        $category->update([
            'name' => $validated['name'],
            'category_code' => strtoupper($validated['category_code']),
        ]);

        return redirect()->route('inventory.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->property_id !== $this->getUserPropertyId()) {
            abort(403);
        }
        
        // Cek jika kategori masih digunakan oleh item
        if ($category->inventories()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh beberapa item.');
        }

        $category->delete();

        return redirect()->route('inventory.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}