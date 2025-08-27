<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricePackage;
use Illuminate\Http\Request;

class PricePackageController extends Controller
{
    public function index()
    {
        $packages = PricePackage::latest()->paginate(10);
        return view('admin.price_packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.price_packages.create');
    }

    public function store(Request $request)
    {
        $this->authorize('manage-data');
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        PricePackage::create($request->all());
        return redirect()->route('admin.price-packages.index')->with('success', 'Paket harga berhasil dibuat.');
    }

    public function edit(PricePackage $pricePackage)
    {
        return view('admin.price_packages.edit', compact('pricePackage'));
    }

    public function update(Request $request, PricePackage $pricePackage)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        // Pastikan 'is_active' punya nilai jika tidak dicentang
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $pricePackage->update($data);
        return redirect()->route('admin.price-packages.index')->with('success', 'Paket harga berhasil diperbarui.');
    }

    public function destroy(PricePackage $pricePackage)
    {
        $pricePackage->delete();
        return redirect()->route('admin.price-packages.index')->with('success', 'Paket harga berhasil dihapus.');
    }
}