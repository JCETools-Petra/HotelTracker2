<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\DailyIncome;
use App\Models\User;

class HotelAkatJuneIncomeSeeder extends Seeder
{
    public function run()
    {
        $property = Property::where('name', 'Hotel Akat')->first();
        if (!$property) { $this->command->error('Property "Hotel Akat" not found.'); return; }

        $user = User::first();
        if (!$user) { $this->command->error('No users found in the database.'); return; }

        // === Data final dari "Akat - Juni.xlsx" ===
        // Format: 'YYYY-MM-DD' => [rooms_sold, cash, tf_bank, cl_ota]
        $dailyData = [
            '2025-06-01' => [9, 1800000, 940000, 0],
            '2025-06-02' => [7, 2100000, 0, 0],
            '2025-06-03' => [5, 1500000, 0, 0],
            '2025-06-04' => [6, 1500000, 0, 227407],
            '2025-06-05' => [7, 2100000, 0, 0],
            '2025-06-06' => [7, 1800000, 300000, 0],
            '2025-06-07' => [9, 1800000, 620000, 303560],
            '2025-06-08' => [11, 1800000, 920000, 580967],
            '2025-06-09' => [7, 1520000, 700000, 0],
            '2025-06-10' => [7, 2140000, 0, 0],
            '2025-06-11' => [7, 2120000, 0, 0],
            '2025-06-12' => [10, 2400000, 600000, 0],
            '2025-06-13' => [5, 1520000, 0, 0],
            '2025-06-14' => [4, 1200000, 0, 0],
            '2025-06-15' => [5, 1220000, 300000, 0],
            '2025-06-16' => [4, 900000, 300000, 0],
            '2025-06-17' => [10, 2700000, 300000, 0],
            '2025-06-18' => [5, 1200000, 300000, 0],
            '2025-06-19' => [7, 1520000, 300000, 277407],
            '2025-06-20' => [8, 2370000, 0, 0],
            '2025-06-21' => [11, 2710000, 320000, 277407],
            '2025-06-22' => [11, 2370000, 550000, 308230],
            '2025-06-23' => [8, 1740000, 550000, 0],
            '2025-06-24' => [8, 2020000, 250000, 0],
            '2025-06-25' => [11, 2640000, 550000, 0],
            '2025-06-26' => [10, 2940000, 0, 0],
            '2025-06-27' => [12, 2600000, 620000, 303560],
            '2025-06-28' => [9, 2360000, 300000, 0],
            '2025-06-29' => [9, 2350000, 250000, 0],
            '2025-06-30' => [10, 3030000, 0, 0],
        ];

        // === CASH PAKET BULANAN (Juni) = 0, dibagi integer-safe (tetap 0) ===
        $monthlyCashTotal = 0;
        $days            = count($dailyData); // 30
        $perDay          = $days ? intdiv($monthlyCashTotal, $days) : 0; // 0
        $remainder       = $monthlyCashTotal - ($perDay * $days);        // 0

        $dailyMonthlyAdd = [];
        $i = 0;
        foreach (array_keys($dailyData) as $date) {
            $extra = ($i < $remainder) ? 1 : 0; // tidak terpakai (0)
            $dailyMonthlyAdd[$date] = $perDay + $extra; // selalu 0 di Juni
            $i++;
        }

        $this->command->info('Seeding June 2025 (Hotel Akat)…');

        DB::transaction(function () use ($property, $user, $dailyData, $dailyMonthlyAdd) {
            foreach ($dailyData as $date => $vals) {
                [$roomsSold, $cash, $tfBank, $clOta] = $vals;

                // Korporasi = cash + tf_bank + porsi cash bulanan (0 di Juni)
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
                $income->corp_income           = $corpRevenue;   // simpan apa adanya (integer)
                $income->online_rooms          = $otaRooms;
                $income->online_room_income    = $otaRevenue;    // simpan apa adanya (integer)

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

        $this->command->info('Done. Totals should match Excel: CASH 59,970,000; TF 8,970,000; CL 2,278,538; TOTAL 71,218,538.');
    }
}
