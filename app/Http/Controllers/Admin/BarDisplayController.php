<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Http\Request;

class BarDisplayController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua properti untuk filter dropdown
        $properties = Property::orderBy('name')->get();

        $selectedPropertyId = $request->input('property_id');
        $selectedProperty = null;
        $roomTypePrices = collect();

        if ($selectedPropertyId) {
            // Jika ada properti yang dipilih, ambil datanya
            $selectedProperty = Property::find($selectedPropertyId);

            // Ambil semua tipe kamar untuk properti ini, beserta aturan harganya (diurutkan)
            $roomTypes = RoomType::where('property_id', $selectedPropertyId)
                ->with(['pricingRules' => function ($query) {
                    $query->orderBy('min_occupancy', 'asc');
                }])
                ->get();

            // Siapkan data untuk ditampilkan di tabel
            $roomTypePrices = $roomTypes->map(function ($roomType) {
                $prices = [];
                // Asumsi ada 5 aturan harga, sesuai dengan 5 level BAR
                foreach ($roomType->pricingRules as $rule) {
                    // Hitung harga final berdasarkan bottom rate dan persentase kenaikan
                    $finalPrice = $roomType->bottom_rate * (1 + ($rule->percentage_increase / 100));
                    $prices[] = $finalPrice;
                }
                return [
                    'name' => $roomType->name,
                    'prices' => $prices,
                ];
            });
        }

        return view('admin.bar_prices.index', compact('properties', 'selectedProperty', 'roomTypePrices', 'selectedPropertyId'));
    }
}