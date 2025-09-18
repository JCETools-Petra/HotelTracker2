<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyIncome;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Traits\LogActivity;

class IncomeController extends Controller
{
    use LogActivity;

    public function create(Property $property)
    {
        return view('admin.incomes.create', [
            'property' => $property,
            'date' => now()->toDateString(),
        ]);
    }

    public function store(Request $request, Property $property)
    {
        $this->authorize('manage-data');

        $validatedData = $request->validate([
            'date' => ['required', 'date', Rule::unique('daily_incomes')->where('property_id', $property->id)],
            'offline_rooms' => 'nullable|integer|min:0',
            'online_rooms' => 'nullable|integer|min:0',
            'ta_rooms' => 'nullable|integer|min:0',
            'gov_rooms' => 'nullable|integer|min:0',
            'corp_rooms' => 'nullable|integer|min:0',
            'compliment_rooms' => 'nullable|integer|min:0',
            'house_use_rooms' => 'nullable|integer|min:0',
            'afiliasi_rooms' => 'nullable|integer|min:0',
            'offline_room_income' => 'nullable|numeric|min:0',
            'online_room_income' => 'nullable|numeric|min:0',
            'ta_income' => 'nullable|numeric|min:0',
            'gov_income' => 'nullable|numeric|min:0',
            'corp_income' => 'nullable|numeric|min:0',
            'compliment_income' => 'nullable|numeric|min:0',
            'house_use_income' => 'nullable|numeric|min:0',
            'afiliasi_room_income' => 'nullable|numeric|min:0',
            'mice_room_income' => 'nullable|numeric|min:0', 
            'breakfast_income' => 'nullable|numeric|min:0',
            'lunch_income' => 'nullable|numeric|min:0',
            'dinner_income' => 'nullable|numeric|min:0',
            'others_income' => 'nullable|numeric|min:0',
        ]);
        
        $miceIncomeFromForm = $validatedData['mice_room_income'] ?? 0;
        
        $incomeData = $validatedData;
        $incomeData['property_id'] = $property->id;
        $incomeData['user_id'] = auth()->id();
        
        $income = DailyIncome::create($incomeData);
        
        // Panggil method kalkulasi dari model
        $income->recalculateTotals($miceIncomeFromForm);
        $income->save();
        
        $this->logActivity("Menambahkan data pendapatan untuk tanggal {$income->date}", $request, $property->id);

        return redirect()->route('admin.properties.show', $property)->with('success', 'Data pendapatan berhasil ditambahkan.');
    }

    public function edit(DailyIncome $income)
    {
        $income->load('property');
        return view('admin.incomes.edit', [
            'income' => $income,
            'date' => \Carbon\Carbon::parse($income->date)->toDateString(),
        ]);
    }

    public function update(Request $request, DailyIncome $income)
    {
        $validatedData = $request->validate([
            'date' => ['required', 'date', Rule::unique('daily_incomes')->where('property_id', $income->property_id)->ignore($income->id)],
            'offline_rooms' => 'nullable|integer|min:0',
            'offline_room_income' => 'nullable|numeric|min:0',
            'online_rooms' => 'nullable|integer|min:0',
            'online_room_income' => 'nullable|numeric|min:0',
            'ta_rooms' => 'nullable|integer|min:0',
            'ta_income' => 'nullable|numeric|min:0',
            'gov_rooms' => 'nullable|integer|min:0',
            'gov_income' => 'nullable|numeric|min:0',
            'corp_rooms' => 'nullable|integer|min:0',
            'corp_income' => 'nullable|numeric|min:0',
            'compliment_rooms' => 'nullable|integer|min:0',
            'compliment_income' => 'nullable|numeric|min:0',
            'house_use_rooms' => 'nullable|integer|min:0',
            'house_use_income' => 'nullable|numeric|min:0',
            'afiliasi_rooms' => 'nullable|integer|min:0',
            'afiliasi_room_income' => 'nullable|numeric|min:0',
            'mice_room_income' => 'nullable|numeric|min:0',
            'breakfast_income' => 'nullable|numeric|min:0',
            'lunch_income' => 'nullable|numeric|min:0',
            'dinner_income' => 'nullable|numeric|min:0',
            'others_income' => 'nullable|numeric|min:0',
        ]);

        $miceIncomeFromForm = $validatedData['mice_room_income'] ?? 0;
        
        $income->update($validatedData);

        // Panggil method kalkulasi dari model
        $income->recalculateTotals($miceIncomeFromForm);
        $income->save();
        
        $this->logActivity("Memperbarui data pendapatan untuk tanggal {$income->date}", $request, $income->property_id);

        return redirect()->route('admin.properties.show', $income->property_id)->with('success', 'Data pendapatan berhasil diperbarui.');
    }

    public function destroy(Request $request, DailyIncome $income) // Tambahkan Request $request
    {
        $propertyId = $income->property_id;
        $incomeDate = $income->date;
        $property = $income->property;

        $income->delete();
        
        $this->logActivity(
            "Menghapus data pendapatan untuk tanggal {$incomeDate}",
            $request,
            $propertyId
        );
        
        return redirect()->route('admin.properties.show', $property)
                         ->with('success', 'Data pendapatan berhasil dihapus.');
    }
}