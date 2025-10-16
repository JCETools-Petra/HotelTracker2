<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Pastikan ini ada
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Fungsi untuk membuat kode kategori dari nama.
     */
    private function generateCategoryCode(string $name): string
    {
        $words = explode(' ', str_replace('&', '', $name));
        $code = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $code .= strtoupper(substr($word, 0, 1));
            }
        }
        return $code;
    }

    /**
     * Menampilkan daftar semua kategori master.
     */
    public function index()
    {
        $categories = Category::latest()->paginate(10);
        return view('inventory.categories.index', compact('categories'));
    }

    /**
     * Menampilkan form untuk membuat kategori baru.
     */
    public function create()
    {
        return view('inventory.categories.create');
    }

    /**
     * Menyimpan kategori baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')],
        ]);
        
        $validated['category_code'] = $this->generateCategoryCode($validated['name']);

        Category::create($validated);

        return redirect()->route('inventory.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit kategori.
     */
    public function edit(Category $category)
    {
        // [PERBAIKAN] Pengecekan property_id dihapus dari sini.
        return view('inventory.categories.edit', compact('category'));
    }

    /**
     * Memperbarui kategori yang ada di database.
     */
    public function update(Request $request, Category $category)
    {
        // [PERBAIKAN] Pengecekan property_id dihapus dari sini.
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
        ]);
        
        // Buat ulang kode jika nama berubah
        $validated['category_code'] = $this->generateCategoryCode($validated['name']);

        $category->update($validated);

        return redirect()->route('inventory.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Menghapus kategori dari database.
     */
    public function destroy(Category $category)
    {
        // [PERBAIKAN] Pengecekan property_id dihapus dari sini.
        
        if ($category->inventories()->count() > 0) {
            return redirect()->route('inventory.categories.index')->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh beberapa item.');
        }

        $category->delete();
        return redirect()->route('inventory.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}