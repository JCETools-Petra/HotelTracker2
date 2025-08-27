<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MiceCategory;
use Illuminate\Http\Request;

class MiceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $miceCategories = MiceCategory::latest()->paginate(10);
        return view('admin.mice_categories.index', compact('miceCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.mice_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('manage-data');
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:mice_categories,name',
            'description' => 'nullable|string',
        ]);

        MiceCategory::create($validated);

        return redirect()->route('admin.mice-categories.index')
                         ->with('success', 'Kategori MICE berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     *
     * Note: We are not using a dedicated show view, redirecting to index.
     */
    public function show(MiceCategory $miceCategory)
    {
        return redirect()->route('admin.mice-categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MiceCategory $miceCategory)
    {
        return view('admin.mice_categories.edit', compact('miceCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MiceCategory $miceCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:mice_categories,name,' . $miceCategory->id,
            'description' => 'nullable|string',
        ]);

        $miceCategory->update($validated);

        return redirect()->route('admin.mice-categories.index')
                         ->with('success', 'Kategori MICE berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MiceCategory $miceCategory)
    {
        try {
            $miceCategory->delete();
            return redirect()->route('admin.mice-categories.index')
                             ->with('success', 'Kategori MICE berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle potential foreign key constraint violation
            return redirect()->route('admin.mice-categories.index')
                             ->with('error', 'Kategori MICE tidak dapat dihapus karena masih digunakan oleh data booking.');
        }
    }
}
