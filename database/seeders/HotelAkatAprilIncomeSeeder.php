<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\DailyIncome;
use App\Models\User;

class HotelAkatAprilIncomeSeeder extends Seeder
{
    public function run()
    {
        $property = Property::where('name', 'Hotel Akat')->first();
        if (!$property) { $this->command->error('Property "Hotel Akat" not found.'); return; }

        $user = User::first();
        if (!$user) { $this->command->error('No users found in the database.'); return; }

        // === Data final dari "Akat - April.xlsx" ===
        // 'YYYY-MM-DD' => [rooms_sold, cash, tf_bank, cl_ota]
        $dailyData = [
            '2025-04-01' => [9, 2570000, 300000, 0],
            '2025-04-02' => [12, 2900000, 1050000, 0],
            '2025-04-03' => [11, 2260000, 1400000, 0],
            '2025-04-04' => [11, 2550000, 1050000, 0],
            '2025-04-05' => [10, 1930000, 1050000, 273000],
            '2025-04-06' => [9, 1960000, 1050000, 0],
            '2025-04-07' => [7, 1990000, 320000, 0],
            '2025-04-08' => [10, 2600000, 320000, 273000],
            '2025-04-09' => [8, 2210000, 350000, 0],
            '2025-04-10' => [6, 1890000, 0, 0],
            '2025-04-11' => [9, 2510000, 320000, 0],
            '2025-04-12' => [14, 4120000, 0, 273000],
            '2025-04-13' => [10, 3200000, 0, 0],
            '2025-04-14' => [7, 1890000, 300000, 0],
            '2025-04-15' => [8, 2190000, 300000, 0],
            '2025-04-16' => [8, 2530000, 0, 0],
            '2025-04-17' => [10, 3120000, 0, 0],
            '2025-04-18' => [8, 2180000, 300000, 0],
            '2025-04-19' => [12, 2760000, 1120000, 0],
            '2025-04-20' => [7, 2160000, 0, 0],
            '2025-04-21' => [8, 2180000, 300000, 0],
            '2025-04-22' => [11, 3120000, 350000, 0],
            '2025-04-23' => [8, 1860000, 600000, 0],
            '2025-04-24' => [13, 2890000, 850000, 230400],
            '2025-04-25' => [9, 1840000, 640000, 230400],
            '2025-04-26' => [11, 2190000, 960000, 230400],
            '2025-04-27' => [9, 2160000, 350000, 230400],
            '2025-04-28' => [6, 1570000, 500000, 0],
            '2025-04-29' => [7, 1680000, 320000, 0],
            '2025-04-30' => [8, 2550000, 0, 0],
        ];

        // === CASH PAKET BULANAN: Rp 2.000.000, dibagi integer-safe ke 30 hari ===
        $monthlyCashTotal = 0;
        $days            = count($dailyData); // 30
        $perDay          = intdiv($monthlyCashTotal, $days); // 66_666
        $remainder       = $monthlyCashTotal - ($perDay * $days); // 20

        // 20 hari pertama mendapat +1 agar total tepat 2.000.000
        $dailyMonthlyAdd = [];
        $i = 0;
        foreach (array_keys($dailyData) as $date) {
            $extra = ($i < $remainder) ? 1 : 0;
            $dailyMonthlyAdd[$date] = $perDay + $extra; // 66_667 utk 20 hari pertama, 66_666 utk sisanya
            $i++;
        }

        $this->command->info('Seeding April 2025 (Hotel Akat)…');

        DB::transaction(function () use ($property, $user, $dailyData, $dailyMonthlyAdd) {
            foreach ($dailyData as $date => $vals) {
                [$roomsSold, $cash, $tfBank, $clOta] = $vals;

                // Korporasi = cash + tf + porsi cash bulanan
                $corpRevenue = $cash + $tfBank + ($dailyMonthlyAdd[$date] ?? 0);
                $otaRevenue  = $clOta; // CL tidak dipecah

                $total = $corpRevenue + $otaRevenue;

                // Alokasi kamar (tidak memengaruhi revenue)
                $corpRooms = 0; $otaRooms = 0;
                if ($roomsSold > 0 && $total > 0) {
                    if ($otaRevenue > 0) {
                        $corpProp  = $corpRevenue / $total;
                        $corpRooms = (int) round($roomsSold * $corpProp);
                        $otaRooms  = $roomsSold - $corpRooms; // pastikan jumlah tepat
                    } else {
                        $corpRooms = (int) $roomsSold;
                    }
                }

                $income = DailyIncome::firstOrNew([
                    'property_id' => $property->id,
                    'date'        => $date,
                ]);

                $income->user_id               = $user->id;
                $income->corp_rooms            = $corpRooms;
                $income->corp_income           = $corpRevenue;
                $income->online_rooms          = $otaRooms;
                $income->online_room_income    = $otaRevenue;

                // Kategori lain = 0
                $income->offline_rooms = 0; $income->offline_room_income = 0;
                $income->ta_rooms = 0; $income->ta_income = 0;
                $income->gov_rooms = 0; $income->gov_income = 0;
                $income->compliment_rooms = 0; $income->compliment_income = 0;
                $income->house_use_rooms = 0; $income->house_use_income = 0;
                $income->afiliasi_rooms = 0; $income->afiliasi_room_income = 0;
                $income->mice_rooms = 0; $income->mice_room_income = 0;
                $income->breakfast_income = 0; $income->lunch_income = 0; $income->dinner_income = 0;
                $income->others_income = 0;

                $income->recalculateTotals();
                $income->save();
            }
        });

        $this->command->info('Done. Seeder April 2025 telah dimuat.');
    }
}
