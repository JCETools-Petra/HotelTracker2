<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\DailyIncome;
use App\Models\User;

class HotelAkatMayIncomeSeeder extends Seeder
{
    public function run()
    {
        $property = Property::where('name', 'Hotel Akat')->first();
        if (!$property) { $this->command->error('Property "Hotel Akat" not found.'); return; }

        $user = User::first();
        if (!$user) { $this->command->error('No users found in the database.'); return; }

        // === Data final dari "Akat - Mei.xlsx" ===
        // Format: 'YYYY-MM-DD' => [rooms_sold, cash, tf_bank, cl_ota]
        $dailyData = [
            '2025-05-01' => [7, 1540000, 0, 504000],
            '2025-05-02' => [6, 970000, 650000, 252000],
            '2025-05-03' => [13, 3430000, 770000, 0],
            '2025-05-04' => [12, 3130000, 600000, 0],
            '2025-05-05' => [7, 1860000, 320000, 0],
            '2025-05-06' => [8, 1860000, 640000, 0],
            '2025-05-07' => [7, 2190000, 0, 0],
            '2025-05-08' => [8, 1840000, 530000, 0],
            '2025-05-09' => [5, 1200000, 300000, 0],
            '2025-05-10' => [12, 3380000, 0, 249600],
            '2025-05-11' => [11, 2790000, 640000, 0],
            '2025-05-12' => [6, 1860000, 0, 0],
            '2025-05-13' => [5, 1520000, 0, 0],
            '2025-05-14' => [5, 1540000, 0, 0],
            '2025-05-15' => [9, 2800000, 0, 0],
            '2025-05-16' => [4, 1200000, 0, 0],
            '2025-05-17' => [9, 2740000, 0, 0],
            '2025-05-18' => [7, 1800000, 300000, 0],
            '2025-05-19' => [6, 1840000, 0, 0],
            '2025-05-20' => [8, 2400000, 0, 0],
            '2025-05-21' => [7, 2140000, 0, 0],
            '2025-05-22' => [7, 2140000, 0, 0],
            '2025-05-23' => [5, 1240000, 300000, 0],
            '2025-05-24' => [8, 1840000, 640000, 0],
            '2025-05-25' => [6, 900000, 970000, 0],
            '2025-05-26' => [11, 2500000, 940000, 0],
            '2025-05-27' => [11, 2480000, 1020000, 0],
            '2025-05-28' => [5, 1320000, 250000, 0],
            '2025-05-29' => [3, 900000, 0, 0],
            '2025-05-30' => [5, 1245000, 0, 303560],
            '2025-05-31' => [5, 1500000, 0, 0],
        ];

        // === CASH PAKET BULANAN (MEI) = 0 ===
        $monthlyCashTotal = 0;
        $days = count($dailyData);
        $perDay = $days ? intdiv($monthlyCashTotal, $days) : 0; // tetap 0
        $remainder = $monthlyCashTotal - ($perDay * $days);     // tetap 0

        // Map porsi harian (semua 0 untuk Mei)
        $dailyMonthlyAdd = [];
        $i = 0;
        foreach (array_keys($dailyData) as $date) {
            $extra = ($i < $remainder) ? 1 : 0; // tidak terpakai
            $dailyMonthlyAdd[$date] = $perDay + $extra;
            $i++;
        }

        $this->command->info('Seeding May 2025 (Hotel Akat)…');

        DB::transaction(function () use ($property, $user, $dailyData, $dailyMonthlyAdd) {
            foreach ($dailyData as $date => $vals) {
                [$roomsSold, $cash, $tfBank, $clOta] = $vals;

                // Korporasi = cash + tf + porsi cash bulanan (0 di Mei)
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

        $this->command->info('Done. Totals should match Excel: CASH 60,095,000; TF 8,870,000; CL 1,309,160; TOTAL 70,274,160.');
    }
}
