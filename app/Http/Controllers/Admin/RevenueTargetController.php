<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RevenueTarget;
use App\Models\Property; // Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Tambahkan ini
use Carbon\Carbon;


class RevenueTargetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RevenueTarget::with('property')->orderBy('month_year', 'desc')->orderBy('property_id');

        // Filter berdasarkan properti
        if ($request->filled('property_id_filter')) {
            $query->where('property_id', $request->property_id_filter);
        }

        // Filter berdasarkan bulan dan tahun
        if ($request->filled('month_filter')) {
            try {
                $monthYear = Carbon::parse($request->month_filter);
                $query->whereYear('month_year', $monthYear->year)
                      ->whereMonth('month_year', $monthYear->month);
            } catch (\Exception $e) {
                // Tangani input tanggal yang tidak valid jika perlu, atau abaikan filter
            }
        }

        $targets = $query->paginate(15);
        $properties = Property::orderBy('name')->get(); // Untuk dropdown filter

        return view('admin.revenue_targets.index', compact('targets', 'properties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // app\Http\Controllers\Admin\RevenueTargetController.php
    public function create()
    {
        $properties = Property::orderBy('name')->get();
        ($properties); // PASTIKAN INI MASIH ADA UNTUK SEKARANG

        if ($properties->isEmpty()) {
            return redirect()->route('admin.revenue-targets.index')->with('error', 'Tidak ada properti yang tersedia untuk menetapkan target. Silakan tambahkan properti terlebih dahulu.');
        }
        return view('admin.revenue_targets.create', compact('properties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('manage-data');
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'month_year' => [
                'required',
                'date_format:Y-m', // Input bulan dan tahun, misal: 2025-05
                // Validasi unik untuk kombinasi property_id dan month_year
                Rule::unique('revenue_targets')->where(function ($query) use ($request) {
                    // Konversi Y-m menjadi Y-m-01 untuk perbandingan di database
                    $monthStart = Carbon::createFromFormat('Y-m', $request->month_year)->startOfMonth()->toDateString();
                    return $query->where('property_id', $request->property_id)
                                 ->where('month_year', $monthStart);
                })
            ],
            'target_amount' => 'required|numeric|min:0',
        ],[
            'property_id.required' => 'Properti harus dipilih.',
            'property_id.exists' => 'Properti yang dipilih tidak valid.',
            'month_year.required' => 'Bulan dan Tahun target harus diisi.',
            'month_year.date_format' => 'Format Bulan dan Tahun harus YYYY-MM (misal: 2025-05).',
            'month_year.unique' => 'Target untuk properti ini pada bulan dan tahun tersebut sudah ada.',
            'target_amount.required' => 'Jumlah target harus diisi.',
            'target_amount.numeric' => 'Jumlah target harus berupa angka.',
            'target_amount.min' => 'Jumlah target tidak boleh negatif.',
        ]);

        RevenueTarget::create([
            'property_id' => $request->property_id,
            // Mutator di model RevenueTarget akan menangani konversi 'month_year' ke tanggal pertama bulan
            'month_year' => $request->month_year,
            'target_amount' => $request->target_amount,
        ]);

        return redirect()->route('admin.revenue-targets.index')
            ->with('success', 'Target pendapatan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RevenueTarget $revenueTarget)
    {
        $properties = Property::orderBy('name')->get(); // Juga butuh ini untuk edit
        // Format month_year ke YYYY-MM untuk ditampilkan di input type="month"
        $revenueTarget->month_year_form = Carbon::parse($revenueTarget->month_year)->format('Y-m');
        return view('admin.revenue_targets.edit', compact('revenueTarget', 'properties')); // Mengirim variabel $properties ke view
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RevenueTarget $revenueTarget) // Route Model Binding
    {
         $request->validate([
            'property_id' => 'required|exists:properties,id',
            'month_year' => [
                'required',
                'date_format:Y-m',
                 Rule::unique('revenue_targets')->where(function ($query) use ($request, $revenueTarget) {
                    $monthStart = Carbon::createFromFormat('Y-m', $request->month_year)->startOfMonth()->toDateString();
                    return $query->where('property_id', $request->property_id)
                                 ->where('month_year', $monthStart);
                })->ignore($revenueTarget->id) // Abaikan record saat ini saat validasi unik
            ],
            'target_amount' => 'required|numeric|min:0',
        ],[
            // ... (pesan validasi sama seperti store) ...
            'month_year.unique' => 'Target untuk properti ini pada bulan dan tahun tersebut sudah ada.',
        ]);

        $revenueTarget->update([
            'property_id' => $request->property_id,
            'month_year' => $request->month_year, // Mutator akan menangani ini
            'target_amount' => $request->target_amount,
        ]);

        return redirect()->route('admin.revenue-targets.index')
            ->with('success', 'Target pendapatan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RevenueTarget $revenueTarget) // Route Model Binding
    {
        $revenueTarget->delete();
        return redirect()->route('admin.revenue-targets.index')
            ->with('success', 'Target pendapatan berhasil dihapus.');
    }
}
