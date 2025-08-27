<?php

namespace App\Exports;

use App\Models\DailyIncome;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
// =============================================
// >> AWAL PERUBAHAN 1: Tambahkan use statement ini <<
// =============================================
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
// =============================================
// >> AKHIR PERUBAHAN 1 <<
// =============================================

// =================================================================
// >> AWAL PERUBAHAN 2: Tambahkan "WithColumnFormatting" di sini <<
// =================================================================
class AdminPropertiesSummaryExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents, WithColumnFormatting
// =================================================================
// >> AKHIR PERUBAHAN 2 <<
// =================================================================
{
    protected $startDate;
    protected $endDate;
    protected $propertyId;
    protected $miceRevenues;

    public function __construct($startDate, $endDate, $propertyId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->propertyId = $propertyId;

        $this->miceRevenues = Booking::where('status', 'Booking Pasti')
            ->whereBetween('event_date', [$this->startDate, $this->endDate])
            ->when($this->propertyId, function ($query, $propertyId) {
                return $query->where('property_id', $propertyId);
            })
            ->select('property_id', DB::raw('DATE(event_date) as date'), DB::raw('SUM(total_price) as total_mice'))
            ->groupBy('property_id', 'date')
            ->get()
            ->keyBy(function ($item) {
                return $item->property_id . '_' . Carbon::parse($item->date)->toDateString();
            });
    }

    public function query()
    {
        $query = DailyIncome::query()
            ->with('property:id,name')
            ->whereBetween('date', [$this->startDate, $this->endDate]);

        if ($this->propertyId) {
            $query->where('property_id', $this->propertyId);
        }

        return $query->orderBy('property_id', 'asc')->orderBy('date', 'asc');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Properti',
            'Kamar (Walk-In)',
            'Pendapatan (Walk-In)',
            'Kamar (OTA)',
            'Pendapatan (OTA)',
            'Kamar (TA)',
            'Pendapatan (TA)',
            'Kamar (Gov)',
            'Pendapatan (Gov)',
            'Kamar (Corp)',
            'Pendapatan (Corp)',
            'Kamar (Compliment)',
            'Pendapatan (Compliment)',
            'Kamar (House Use)',
            'Pendapatan (House Use)',
            'Pendapatan (MICE)',
            'Pendapatan (Sarapan)',
            'Pendapatan (Makan Siang)',
            'Pendapatan (Makan Malam)',
            'Pendapatan (Lainnya)',
            'TOTAL PENDAPATAN HARIAN'
        ];
    }

    public function map($income): array
    {
        $dateString = Carbon::parse($income->date)->toDateString();
        
        $miceKey = $income->property_id . '_' . $dateString;
        $miceRevenue = $this->miceRevenues->get($miceKey)->total_mice ?? 0;

        $totalHarian = 
            ($income->offline_room_income ?? 0) +
            ($income->online_room_income ?? 0) +
            ($income->ta_income ?? 0) +
            ($income->gov_income ?? 0) +
            ($income->corp_income ?? 0) +
            ($income->compliment_income ?? 0) +
            ($income->house_use_income ?? 0) +
            $miceRevenue +
            ($income->breakfast_income ?? 0) +
            ($income->lunch_income ?? 0) +
            ($income->dinner_income ?? 0) +
            ($income->others_income ?? 0);

        return [
            $dateString,
            $income->property->name ?? 'N/A',
            $income->offline_rooms ?? 0,
            (float) ($income->offline_room_income ?? 0),
            $income->online_rooms ?? 0,
            (float) ($income->online_room_income ?? 0),
            $income->ta_rooms ?? 0,
            (float) ($income->ta_income ?? 0),
            $income->gov_rooms ?? 0,
            (float) ($income->gov_income ?? 0),
            $income->corp_rooms ?? 0,
            (float) ($income->corp_income ?? 0),
            $income->compliment_rooms ?? 0,
            (float) ($income->compliment_income ?? 0),
            $income->house_use_rooms ?? 0,
            (float) ($income->house_use_income ?? 0),
            (float) $miceRevenue,
            (float) ($income->breakfast_income ?? 0),
            (float) ($income->lunch_income ?? 0),
            (float) ($income->dinner_income ?? 0),
            (float) ($income->others_income ?? 0),
            (float) $totalHarian
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
    
    // =================================================================
    // >> AWAL PERUBAHAN 3: Tambahkan method baru ini untuk format mata uang <<
    // =================================================================
    public function columnFormats(): array
    {
        // Format Rupiah untuk semua kolom pendapatan
        $rupiahFormat = '"Rp"#,##0.00';

        return [
            'D' => $rupiahFormat, // Pendapatan (Walk-In)
            'F' => $rupiahFormat, // Pendapatan (OTA)
            'H' => $rupiahFormat, // Pendapatan (TA)
            'J' => $rupiahFormat, // Pendapatan (Gov)
            'L' => $rupiahFormat, // Pendapatan (Corp)
            'N' => $rupiahFormat, // Pendapatan (Compliment)
            'P' => $rupiahFormat, // Pendapatan (House Use)
            'Q' => $rupiahFormat, // Pendapatan (MICE)
            'R' => $rupiahFormat, // Pendapatan (Sarapan)
            'S' => $rupiahFormat, // Pendapatan (Makan Siang)
            'T' => $rupiahFormat, // Pendapatan (Makan Malam)
            'U' => $rupiahFormat, // Pendapatan (Lainnya)
            'V' => $rupiahFormat, // TOTAL PENDAPATAN HARIAN
        ];
    }
    // =================================================================
    // >> AKHIR PERUBAHAN 3 <<
    // =================================================================

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Mengaktifkan AutoFilter pada seluruh kolom yang digunakan
                $event->sheet->getDelegate()->setAutoFilter($event->sheet->calculateWorksheetDimension());
            },
        ];
    }
}