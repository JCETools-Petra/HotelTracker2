<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Http\Request;

class BarDisplayController extends Controller
{
    public function index(Request $request)
    {
        $properties = Property::orderBy('name')->get();
        $selectedPropertyId = $request->input('property_id');
        $selectedProperty = null;
        $roomTypePrices = collect();

        if ($selectedPropertyId) {
            $selectedProperty = Property::find($selectedPropertyId);

            // Ambil tipe kamar beserta satu aturan harganya
            $roomTypes = RoomType::where('property_id', $selectedPropertyId)
                ->with('pricingRule') // Gunakan relasi hasOne yang baru
                ->get();

            $roomTypePrices = $roomTypes->map(function ($roomType) {
                $prices = [];
                $rule = $roomType->pricingRule;

                // Jika tidak ada aturan harga, tampilkan strip
                if (!$rule) {
                    return [
                        'name' => $roomType->name,
                        'prices' => array_fill(0, 5, null), // Array berisi 5 nilai null
                    ];
                }

                // === LOGIKA PERHITUNGAN BARU ===
                $currentPrice = $rule->bottom_rate;
                $increaseFactor = 1 + ($rule->percentage_increase / 100);

                for ($i = 1; $i <= 5; $i++) {
                    // Jika level BAR saat ini di bawah 'starting_bar', gunakan harga dasar
                    if ($i < $rule->starting_bar) {
                        $prices[] = $rule->bottom_rate;
                    } else {
                        // Jika sudah mencapai atau melewati 'starting_bar', gunakan harga saat ini
                        // lalu naikkan untuk level berikutnya
                        $prices[] = $currentPrice;
                        $currentPrice *= $increaseFactor;
                    }
                }
                // === AKHIR LOGIKA PERHITUNGAN BARU ===

                return [
                    'name' => $roomType->name,
                    'prices' => $prices,
                ];
            });
        }

        return view('ecommerce.bar_prices.index', compact('properties', 'selectedProperty', 'roomTypePrices', 'selectedPropertyId'));
    }
}