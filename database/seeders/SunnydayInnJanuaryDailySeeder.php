<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\DailyIncome;
use App\Models\User;
use Carbon\Carbon;

class SunnydayInnJanuaryDailySeeder extends Seeder
{
    /**
     * Helper function to distribute a total value over a number of days.
     * It handles integer division and distributes the remainder.
     *
     * @param int $total The total value to distribute.
     * @param int $days The number of days to distribute over.
     * @return array An array of daily values.
     */
    private function distribute(int $total, int $days): array
    {
        if ($days === 0) {
            return [];
        }

        $perDay = intdiv($total, $days);
        $remainder = $total % $days;
        
        $dailyValues = array_fill(0, $days, $perDay);
        
        for ($i = 0; $i < $remainder; $i++) {
            $dailyValues[$i]++;
        }
        
        return $dailyValues;
    }

    public function run()
    {
        // Ganti 'Sunnyday Inn' dengan nama properti yang sesuai di database Anda
        $property = Property::where('name', 'Sunnyday Inn')->first();
        if (!$property) {
            $this->command->error('Property "Sunnyday Inn" not found.');
            return;
        }

        $user = User::first();
        if (!$user) {
            $this->command->error('No users found in the database.');
            return;
        }

        // === Data Total Bulanan untuk Januari 2025 (DATA DITUKAR) ===
        $monthlyTotals = [
            // Nilai offline_rooms sekarang menggunakan nilai online_rooms sebelumnya
            'offline_rooms' => 471, 
            'offline_room_income' => 261722500,

            // Nilai online_rooms sekarang menggunakan nilai offline_rooms sebelumnya
            'online_rooms' => 13,
            'online_room_income' => 6500000,

            // Data lainnya tetap sama
            'breakfast_income' => 20150000,
            'lunch_income' => 15500000,
            'dinner_income' => 12500000,
            'ta_rooms' => 223,
            'ta_income' => 122290000,
            'gov_rooms' => 21,
            'gov_income' => 10700000,
            'corp_rooms' => 5,
            'corp_income' => 1900000,
        ];
        
        $daysInMonth = 31; // Jumlah hari di Januari
        $dailyData = [];

        // Lakukan distribusi untuk setiap metrik
        foreach ($monthlyTotals as $key => $total) {
            $dailyData[$key] = $this->distribute($total, $daysInMonth);
        }

        $this->command->info('Seeding January 2025 daily data for Sunnyday Inn (with swapped online/offline data)...');

        DB::transaction(function () use ($property, $user, $dailyData, $daysInMonth) {
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create(2025, 1, $day)->toDateString();
                $index = $day - 1; // Array index is 0-based

                $income = DailyIncome::firstOrNew([
                    'property_id' => $property->id,
                    'date'        => $date,
                ]);

                $income->user_id = $user->id;

                // Assign a daily distributed value to each metric
                foreach ($dailyData as $key => $values) {
                    $income->$key = $values[$index];
                }
                
                // Set fields not in our source data to 0
                $income->others_income = 0;
                $income->compliment_rooms = 0;
                $income->compliment_income = 0;
                $income->house_use_rooms = 0;
                $income->house_use_income = 0;
                $income->afiliasi_rooms = 0;
                $income->afiliasi_room_income = 0;
                $income->mice_rooms = 0;
                $income->mice_room_income = 0;

                // Recalculate summary fields based on the daily data
                $income->recalculateTotals();
                
                $income->save();
            }
        });
        
        // Verifikasi Total
        $totalRevenue = DailyIncome::where('property_id', $property->id)->whereYear('date', 2025)->whereMonth('date', 1)->sum('total_revenue');
        $this->command->info('Seeding complete.');
        $this->command->info('Original monthly total revenue: 309,777,893');
        $this->command->info('Sum of daily total revenue after seeding: ' . number_format($totalRevenue));
    }
}