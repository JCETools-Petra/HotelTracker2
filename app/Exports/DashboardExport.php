<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\PropertyIncomeSheet;
use App\Exports\Sheets\PropertyMiceSheet; // <-- Tambahkan ini

class DashboardExport implements WithMultipleSheets
{
    use Exportable;

    protected $startDate;
    protected $endDate;
    protected $properties;

    public function __construct($startDate, $endDate, $properties)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->properties = $properties;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->properties as $property) {
            // Untuk setiap properti, tambahkan DUA sheet: Pendapatan dan MICE
            $sheets[] = new PropertyIncomeSheet($this->startDate, $this->endDate, $property);
            $sheets[] = new PropertyMiceSheet($this->startDate, $this->endDate, $property);
        }

        return $sheets;
    }
}