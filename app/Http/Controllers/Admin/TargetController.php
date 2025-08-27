<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Target;
use App\Models\Property; // Pastikan di-import
use Illuminate\Http\Request;
use Carbon\Carbon;


class TargetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Target::with('property')->orderBy('year', 'desc')->orderBy('month', 'desc');

        // Opsional: Filter berdasarkan tahun
        if ($request->has('filter_year') && $request->filter_year != '') {
            $query->where('year', $request->filter_year);
        }

        // Opsional: Filter berdasarkan properti
        if ($request->has('filter_property_id') && $request->filter_property_id != '') {
            $query->where('property_id', $request->filter_property_id);
        }

        $targets = $query->paginate(15);
        $properties = Property::orderBy('name')->get(); // Untuk dropdown filter
        $years = Target::select('year')->distinct()->orderBy('year', 'desc')->pluck('year'); // Ambil tahun unik untuk filter

        return view('admin.targets.index', compact('targets', 'properties', 'years'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $properties = Property::orderBy('name')->get(); // Ambil semua properti untuk dropdown

        if ($properties->isEmpty()) {
            return redirect()->route('admin.targets.index')
                             ->with('error', 'Tidak ada properti yang tersedia. Harap tambahkan properti terlebih dahulu sebelum menetapkan target.');
        }

        // Siapkan data untuk pilihan tahun dan bulan
        $currentYear = Carbon::now()->year;
        $years = range($currentYear - 2, $currentYear + 5); // Rentang tahun, misal: 2 tahun lalu s.d. 5 tahun ke depan
        
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = Carbon::create()->month($m)->isoFormat('MMMM');
        }

        return view('admin.targets.create', compact('properties', 'years', 'months'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('manage-data');
        // Anda menggunakan Rule::unique() di sini, jadi class Rule perlu diimpor.
        $validatedData = $request->validate([
            'property_id' => 'required|integer|exists:properties,id',
            'year' => 'required|integer|digits:4',
            'month' => 'required|integer|between:1,12',
            'target_amount' => 'required|numeric|min:0',
            // Validasi unique untuk kombinasi property_id, year, dan month
            // Rule::unique('targets')->where(function ($query) use ($request) { // Baris ini dikomentari karena ada pengecekan manual di bawah
            //     return $query->where('property_id', $request->property_id)
            //                  ->where('year', $request->year)
            //                  ->where('month', $request->month);
            // }),
        ],[
            'property_id.required' => 'Properti harus dipilih.',
            'year.required' => 'Tahun target harus diisi.',
            'month.required' => 'Bulan target harus diisi.',
            'target_amount.required' => 'Jumlah target harus diisi.',
            'target_amount.numeric' => 'Jumlah target harus berupa angka.',
        ]);
        
        // Cek manual untuk keunikan kombinasi
        $existingTarget = Target::where('property_id', $request->property_id)
                                  ->where('year', $request->year)
                                  ->where('month', $request->month)
                                  ->first();

        if ($existingTarget) {
            return back()->withErrors([
                'month' => 'Target untuk properti, tahun, dan bulan yang dipilih sudah ada. Silakan edit target yang ada atau pilih kombinasi lain.'
            ])->withInput(); // withInput() untuk mengisi kembali form dengan data sebelumnya
        }

        Target::create([
            'property_id' => $validatedData['property_id'],
            'year' => $validatedData['year'],
            'month' => $validatedData['month'],
            'target_amount' => $validatedData['target_amount'],
        ]);

        return redirect()->route('admin.targets.index')->with('success', 'Target pendapatan baru berhasil ditetapkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Target $target)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Target $target)
    {
        $properties = Property::orderBy('name')->get();
        $currentYear = Carbon::now()->year;
        $years = range($currentYear - 5, $currentYear + 5);
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = Carbon::create()->month($m)->isoFormat('MMMM');
        }
        return view('admin.targets.edit', compact('target', 'properties', 'years', 'months'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Target $target)
    {
        // Anda juga menggunakan Rule::unique() di sini
        $validatedData = $request->validate([
            'year' => 'required|integer|digits:4',
            'month' => 'required|integer|between:1,12',
            'target_amount' => 'required|numeric|min:0',
            // Rule::unique('targets')->ignore($target->id)->where(function ($query) use ($request, $target) { // Dikomentari karena ada pengecekan manual
            //     return $query->where('property_id', $target->property_id)
            //                  ->where('year', $request->year)
            //                  ->where('month', $request->month);
            // }),
        ],[
            'year.required' => 'Tahun target harus diisi.',
            'month.required' => 'Bulan target harus diisi.',
            'target_amount.required' => 'Jumlah target harus diisi.',
            'target_amount.numeric' => 'Jumlah target harus berupa angka.',
        ]);

        $existingTarget = Target::where('property_id', $target->property_id)
                                  ->where('year', $request->year)
                                  ->where('month', $request->month)
                                  ->where('id', '!=', $target->id)
                                  ->first();

        if ($existingTarget) {
            return back()->withErrors([
                'unique_target' => 'Target untuk properti ini pada tahun dan bulan yang dipilih sudah ada.'
            ])->withInput();
        }

        $target->update([
            'year' => $validatedData['year'],
            'month' => $validatedData['month'],
            'target_amount' => $validatedData['target_amount'],
        ]);

        return redirect()->route('admin.targets.index')->with('success', 'Target pendapatan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Target $target) // Menggunakan Route Model Binding
    {
        // Simpan beberapa info untuk pesan sebelum dihapus, jika perlu
        $propertyName = $target->property->name ?? 'Properti tidak diketahui';
        $targetPeriod = $target->year . '-' . $target->month_name; // Menggunakan accessor month_name

        $target->delete(); // Ini akan melakukan hard delete karena model Target tidak menggunakan SoftDeletes

        return redirect()->route('admin.targets.index')
                         ->with('success', "Target untuk properti '{$propertyName}' periode {$targetPeriod} berhasil dihapus.");
    }
}
