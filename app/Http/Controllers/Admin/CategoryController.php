<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name', // Validasi nama unik
        ]);

        $name = $request->input('name');
        $words = explode(' ', $name);
        $categoryCode = '';

        if (count($words) >= 3) {
            foreach ($words as $word) {
                $categoryCode .= strtoupper(substr($word, 0, 1));
            }
        } else {
            $vowels = preg_replace('/[^aeiouAEIOU]/', '', $name);
            $categoryCode = strtoupper(substr($vowels, 0, 3));
        }
        
        // Tambahkan angka acak jika kode sudah ada untuk memastikan keunikan
        if (Category::where('category_code', $categoryCode)->exists()) {
            $categoryCode .= rand(10, 99);
        }

        Category::create([
            'name' => $name,
            'category_code' => $categoryCode,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);
        
        // Kode kategori tidak diubah saat update, hanya nama
        $category->update($request->only('name'));

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}