<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\DailyIncome;
use App\Models\User;
use Carbon\Carbon;

class SunnydayInnJanuaryDailySeederCorrected extends Seeder
{
    /**
     * Fungsi distribusi baru untuk menciptakan fluktuasi yang lebih alami.
     *
     * @param int $total Total yang akan didistribusikan.
     * @param int $days Jumlah hari.
     * @param float $fluctuation Persentase fluktuasi (misal: 0.5 untuk +/- 50%).
     * @return array Nilai harian yang sudah berfluktuasi.
     */
    private function distribute(int $total, int $days, float $fluctuation = 0.5): array
    {
        if ($days <= 1) return [$total];

        $baseAmount = $total / $days;
        $dailyValues = [];
        $runningTotal = 0;

        for ($i = 0; $i < $days - 1; $i++) {
            // Membuat angka acak antara -100 dan 100
            $randomness = (mt_rand(-1000, 1000) / 1000); 
            // Menghitung nilai harian dengan fluktuasi
            $dailyValue = (int)round($baseAmount * (1 + $fluctuation * $randomness));
            // Pastikan tidak negatif
            $dailyValue = max(0, $dailyValue); 
            
            $dailyValues[] = $dailyValue;
            $runningTotal += $dailyValue;
        }

        // Hari terakhir diatur untuk memastikan totalnya 100% akurat
        $lastDayValue = $total - $runningTotal;
        $dailyValues[] = max(0, $lastDayValue); // Pastikan tidak negatif

        return $dailyValues;
    }

    public function run()
    {
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

        $monthlyTotals = [
            'offline_rooms' => 223, 'ta_rooms' => 57, 'corp_rooms' => 213,
            'offline_room_income' => 66118944, 'ta_income' => 16736736, 'corp_income' => 63033783,
            'breakfast_income' => 25719008, 'lunch_income' => 4688430, 'others_income' => 22430579,
        ];

        // Pisahkan MICE untuk penanganan khusus
        $monthlyMiceIncome = 111050413;
        
        $daysInMonth = 31;
        $dailyData = [];
        
        foreach ($monthlyTotals as $key => $total) {
            $dailyData[$key] = $this->distribute($total, $daysInMonth);
        }
        // Distribusikan MICE secara terpisah
        $dailyMiceIncome = $this->distribute($monthlyMiceIncome, $daysInMonth);

        $this->command->info('Seeding CORRECTED January 2025 data for Sunnyday Inn...');

        DB::transaction(function () use ($property, $user, $dailyData, $dailyMiceIncome, $daysInMonth) {
            DailyIncome::where('property_id', $property->id)->whereYear('date', 2025)->whereMonth('date', 1)->delete();

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create(2025, 1, $day)->toDateString();
                $index = $day - 1;

                $income = new DailyIncome();
                $income->property_id = $property->id;
                $income->date = $date;
                $income->user_id = $user->id;

                foreach ($dailyData as $key => $values) {
                    $income->$key = $values[$index];
                }
                
                // Set fields lain ke 0
                $income->online_rooms = 0; $income->online_room_income = 0;
                $income->gov_rooms = 0; $income->gov_income = 0;
                $income->dinner_income = 0;
                $income->compliment_rooms = 0; $income->compliment_income = 0;
                $income->house_use_rooms = 0; $income->house_use_income = 0;
                $income->afiliasi_rooms = 0; $income->afiliasi_room_income = 0;

                // Panggil kalkulasi dengan MICE sebagai parameter untuk memastikan dihitung
                $income->recalculateTotals($dailyMiceIncome[$index]);
                
                $income->save();
            }
        });
        
        $totalRevenue = DailyIncome::where('property_id', $property->id)->whereYear('date', 2025)->whereMonth('date', 1)->sum('total_revenue');
        $totalRooms = DailyIncome::where('property_id', $property->id)->whereYear('date', 2025)->whereMonth('date', 1)->sum('total_rooms_sold');
        
        $this->command->info('Seeding complete.');
        $this->command->info('Target monthly total rooms sold: 493 | Actual: ' . $totalRooms);
        $this->command->info('Target monthly total revenue: 309,777,893 | Actual: ' . number_format($totalRevenue));
    }
}