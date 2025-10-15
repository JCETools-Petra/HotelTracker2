<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\DailyIncome;
use App\Models\User;

class HotelAkatMarchIncomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $property = Property::where('name', 'Hotel Akat')->first();
        if (!$property) { $this->command->error('Property "Hotel Akat" not found.'); return; }

        $user = User::first();
        if (!$user) { $this->command->error('No users found in the database.'); return; }

        // === Data final dari "Akat - Maret.xlsx" ===
        // Format: 'YYYY-MM-DD' => [rooms_sold, cash, tf_bank, cl_ota]
        $dailyData = [
            '2025-03-01' => [12, 3170000,  400000,      0],
            '2025-03-02' => [ 7, 1240000,  650000,      0],
            '2025-03-03' => [10, 2535000,  400000,      0],
            '2025-03-04' => [11, 2820000,  350000,      0],
            '2025-03-05' => [15, 4120000,  320000,      0],
            '2025-03-06' => [ 9, 2530000,       0,      0],
            '2025-03-07' => [ 7, 1890000,       0,      0],
            '2025-03-08' => [13, 3400000,  300000,      0],
            '2025-03-09' => [ 9, 2220000,  300000,      0],
            '2025-03-10' => [ 8,  970000,  600000, 546000], // CL hanya di sini
            '2025-03-11' => [ 7, 1520000,  300000,      0],
            '2025-03-12' => [10, 2210000,  640000,      0],
            '2025-03-13' => [13, 3200000,  640000,      0],
            '2025-03-14' => [10, 1910000,  940000,      0],
            '2025-03-15' => [13, 3470000,  300000,      0],
            '2025-03-16' => [12, 3520000,       0,      0],
            '2025-03-17' => [ 9, 2510000,       0,      0],
            '2025-03-18' => [16, 4700000,       0,      0],
            '2025-03-19' => [ 9, 2460000,       0,      0],
            '2025-03-20' => [ 8, 1540000,  620000,      0],
            '2025-03-21' => [11, 2870000,  300000,      0],
            '2025-03-22' => [10, 2510000,  640000,      0],
            '2025-03-23' => [10, 2880000,  350000,      0],
            '2025-03-24' => [10, 3150000,       0,      0],
            '2025-03-25' => [11, 3470000,       0,      0],
            '2025-03-26' => [10, 2500000,  640000,      0],
            '2025-03-27' => [14, 2810000, 1560000,      0],
            '2025-03-28' => [16, 3500000, 1560000,      0],
            '2025-03-29' => [13, 3220000,  920000,      0],
            '2025-03-30' => [ 7, 1260000,  920000,      0],
            '2025-03-31' => [ 6, 1110000,  900000,      0],
        ];

        // === Cash Bulanan: pastikan tepat Rp 2.000.000 (tanpa selisih) ===
        $monthlyCashTotal = 2_000_000;
        $days = count($dailyData); // 31
        $perDay = intdiv($monthlyCashTotal, $days); // 64.516
        $remainder = $monthlyCashTotal - ($perDay * $days); // 4

        // Siapkan map porsi harian: 4 hari pertama +1 agar total tepat 2.000.000
        $dailyMonthlyAdd = [];
        $i = 0;
        foreach (array_keys($dailyData) as $date) {
            $extra = ($i < $remainder) ? 1 : 0;
            $dailyMonthlyAdd[$date] = $perDay + $extra;
            $i++;
        }

        $this->command->info('Seeding March 2025 (Hotel Akat) …');

        DB::transaction(function () use ($property, $user, $dailyData, $dailyMonthlyAdd) {
            foreach ($dailyData as $date => $vals) {
                [$roomsSold, $cash, $tfBank, $clOta] = $vals;

                // Pendapatan korporasi: cash + tf bank + porsi cash bulanan (integer-safe)
                $corpRevenue = $cash + $tfBank + ($dailyMonthlyAdd[$date] ?? 0);
                $otaRevenue  = $clOta; // CL tidak dipecah

                $totalRevenue = $corpRevenue + $otaRevenue;

                // Alokasi kamar (tidak mempengaruhi revenue)
                $corpRooms = 0;
                $otaRooms  = 0;
                if ($roomsSold > 0 && $totalRevenue > 0) {
                    if ($otaRevenue > 0) {
                        $corpProp = $corpRevenue / $totalRevenue;
                        $corpRooms = (int) round($roomsSold * $corpProp);
                        $otaRooms  = $roomsSold - $corpRooms; // pastikan jumlah = roomsSold
                    } else {
                        $corpRooms = (int) $roomsSold;
                    }
                }

                $income = DailyIncome::firstOrNew([
                    'property_id' => $property->id,
                    'date'        => $date,
                ]);

                $income->user_id = $user->id;

                $income->corp_rooms           = $corpRooms;
                $income->corp_income          = $corpRevenue;      // simpan apa adanya (integer)
                $income->online_rooms         = $otaRooms;
                $income->online_room_income   = $otaRevenue;       // simpan apa adanya (integer)

                // Kategori lain nol
                $income->offline_rooms = 0; $income->offline_room_income = 0;
                $income->ta_rooms = 0; $income->ta_income = 0;
                $income->gov_rooms = 0; $income->gov_income = 0;
                $income->compliment_rooms = 0; $income->compliment_income = 0;
                $income->house_use_rooms = 0; $income->house_use_income = 0;
                $income->afiliasi_rooms = 0; $income->afiliasi_room_income = 0;
                $income->mice_rooms = 0; $income->mice_room_income = 0;
                $income->breakfast_income = 0; $income->lunch_income = 0; $income->dinner_income = 0;
                $income->others_income = 0;

                $income->recalculateTotals(); // gunakan logika model kamu
                $income->save();
            }
        });

        $this->command->info('Done. March 2025 totals should now equal Rp 98.311.000 (cash+tf+bulanan+CL).');
    }
}
