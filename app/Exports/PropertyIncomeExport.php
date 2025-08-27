<?php

namespace App\Exports;

use App\Models\DailyIncome;
use Maatwebsite\Excel\Concerns\FromCollection;

class PropertyIncomeExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DailyIncome::all();
    }
}
