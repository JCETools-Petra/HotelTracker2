<?php

namespace App\Exports;

use App\Models\DailyIncome;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Untuk lebar kolom otomatis
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\PropertyIncomesExport;

class PropertyIncomesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $propertyId;
    protected $startDate;
    protected $endDate;

    public function __construct($propertyId, $startDate, $endDate)
    {
        $this->propertyId = $propertyId;
        $this->startDate = $startDate ? Carbon::parse($startDate) : null;
        $this->endDate = $endDate ? Carbon::parse($endDate) : null;
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    
    public function query()
    {
        $query = DailyIncome::query()
            ->where('property_id', $this->propertyId)
            ->select( // Pilih kolom yang benar-benar dibutuhkan
                'date',
                'mice_income',
                'fnb_income',
                'offline_room_income',
                'online_room_income',
                'others_income'
            );

        if ($this->startDate) {
            $query->whereDate('date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('date', '<=', $this->endDate);
        }

        return $query->orderBy('date', 'asc'); // Urutkan berdasarkan tanggal terlama untuk ekspor
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'Tanggal',
            'Pendapatan MICE (Rp)',
            'Pendapatan F&B (Rp)',
            'Pendapatan Kamar Offline (Rp)',
            'Pendapatan Kamar Online (Rp)',
            'Pendapatan Lainnya (Rp)',
            'Total Pendapatan Harian (Rp)',
        ];
    }

    /**
    * @param mixed $income // DailyIncome instance
    * @return array
    */
    public function map($income): array
    {
        $totalDailyIncome = $income->mice_income +
                            $income->fnb_income +
                            $income->offline_room_income +
                            $income->online_room_income +
                            $income->others_income;

        return [
            Carbon::parse($income->date)->isoFormat('D MMMM YYYY'),
            $income->mice_income,
            $income->fnb_income,
            $income->offline_room_income,
            $income->online_room_income,
            $income->others_income,
            $totalDailyIncome,
        ];
    }

}