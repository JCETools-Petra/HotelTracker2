<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SunnydayInnFebruarySeeder extends Seeder
{
    private function distribute(int $total, int $days, float $fluct = 0.5): array
    {
        if ($days <= 1) return [$total];
        $base = $total / $days;
        $out = []; $run = 0;

        for ($i = 0; $i < $days - 1; $i++) {
            $rand = (mt_rand(-1000, 1000) / 1000.0); // -1..1
            $val  = (int) round($base * (1 + $fluct * $rand));
            $val  = max(0, $val);
            $out[] = $val;
            $run  += $val;
        }
        $out[] = max(0, $total - $run); // kunci total komponen
        return $out;
    }

    public function run()
    {
        // ===== Konfigurasi Property/User =====
        $property = DB::table('properties')->where('name', 'Sunnyday Inn')->first();
        if (!$property) { $this->command->error('Property "Sunnyday Inn" tidak ditemukan!'); return; }

        $propertyId = $property->id;
        $userId     = DB::table('users')->value('id');

        $days = 28; // Februari 2025

        // ===== Target TOTAL NON-MICE (daily_incomes) =====
        $totals = [
            'corp_income'         => 4_474_133,   // Corporate
            'offline_room_income' => 57_107_438,  // Walk-in
            'ta_income'           => 28_355_083,  // OTA/TA
            'breakfast_income'    => 18_611_570,  // Breakfast
            'lunch_income'        => 4_991_736,   // Restaurant + Room Service
            'others_income'       => 13_578_512,  // Other + Wellness (+ Laundry 0)
        ];
        $targetNonMice = array_sum($totals); // 127,118,472

        $this->command->info("→ Menulis NON-MICE ke daily_incomes (Feb 2025), target: " . number_format($targetNonMice, 0, ',', '.'));

        // Bersihkan Februari
        DB::table('daily_incomes')
            ->where('property_id', $propertyId)
            ->whereBetween('date', ['2025-02-01', '2025-02-28'])
            ->delete();

        // Distribusikan fluktuatif tiap komponen
        $dist = [];
        foreach ($totals as $k => $v) $dist[$k] = $this->distribute($v, $days);

        // ===== Hard-lock SUM(total_revenue) bulanan supaya pas =====
        $tempTotals = [];
        for ($i = 0; $i < $days; $i++) {
            $tempTotals[$i] =
                $dist['corp_income'][$i] +
                $dist['offline_room_income'][$i] +
                $dist['ta_income'][$i] +
                $dist['breakfast_income'][$i] +
                $dist['lunch_income'][$i] +
                $dist['others_income'][$i];
        }
        $sumNow = array_sum($tempTotals);
        $delta  = $targetNonMice - $sumNow;                 // bisa +/-
        $dist['others_income'][$days - 1] += $delta;        // kunci di hari terakhir
        if ($dist['others_income'][$days - 1] < 0) {        // jaga-jaga
            $need = -$dist['others_income'][$days - 1];
            $dist['others_income'][$days - 1] = 0;
            for ($i = $days - 2; $i >= 0 && $need > 0; $i--) {
                $take = min($dist['others_income'][$i], $need);
                $dist['others_income'][$i] -= $take;
                $need -= $take;
            }
        }

        // Insert harian NON-MICE
        for ($d = 1; $d <= $days; $d++) {
            $date = Carbon::create(2025, 2, $d)->toDateString();
            $i    = $d - 1;

            $corp   = $dist['corp_income'][$i];
            $walkin = $dist['offline_room_income'][$i];
            $ota    = $dist['ta_income'][$i];
            $bfast  = $dist['breakfast_income'][$i];
            $lunch  = $dist['lunch_income'][$i];
            $other  = $dist['others_income'][$i];

            $totalRooms = $corp + $walkin + $ota;
            $totalFB    = $bfast + $lunch;
            $totalAll   = $totalRooms + $totalFB + $other;

            DB::table('daily_incomes')->insert([
                'property_id'          => $propertyId,
                'user_id'              => $userId,
                'date'                 => $date,

                'offline_rooms'        => rand(8, 18),
                'offline_room_income'  => number_format($walkin, 2, '.', ''),
                'ta_rooms'             => rand(1, 6),
                'ta_income'            => number_format($ota, 2, '.', ''),
                'corp_rooms'           => rand(3, 8),
                'corp_income'          => number_format($corp, 2, '.', ''),

                'breakfast_income'     => number_format($bfast, 2, '.', ''),
                'lunch_income'         => number_format($lunch, 2, '.', ''),
                'dinner_income'        => number_format(0, 2, '.', ''),
                'others_income'        => number_format($other, 2, '.', ''),

                'online_rooms'         => 0,
                'online_room_income'   => number_format(0, 2, '.', ''),
                'gov_rooms'            => 0,
                'gov_income'           => number_format(0, 2, '.', ''),
                'compliment_rooms'     => 0,
                'compliment_income'    => number_format(0, 2, '.', ''),
                'house_use_rooms'      => 0,
                'house_use_income'     => number_format(0, 2, '.', ''),
                'afiliasi_rooms'       => 0,
                'afiliasi_room_income' => number_format(0, 2, '.', ''),
                'mice_rooms'           => 0,
                'mice_room_income'     => number_format(0, 2, '.', ''),

                'total_rooms_sold'     => rand(18, 28),
                'total_rooms_revenue'  => number_format($totalRooms, 2, '.', ''),
                'total_fb_revenue'     => number_format($totalFB, 2, '.', ''),
                'total_revenue'        => number_format($totalAll, 2, '.', ''),
                'arr'                  => number_format(rand(250000, 380000), 2, '.', ''),
                'occupancy'            => number_format(rand(25, 70), 2, '.', ''),
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // ===== MICE → bookings (total harus 30.123.967) =====
        $this->command->info("→ Menulis MICE ke bookings (Feb 2025)...");
        DB::table('bookings')
            ->where('property_id', $propertyId)
            ->whereBetween('event_date', ['2025-02-01', '2025-02-28'])
            ->delete();

        // 3 event realistis yang totalnya tepat 30,123,967
        $bookings = [
            [
                'client_name'   => 'PT. Energi Papua',
                'event_type'    => 'Rapat Koordinasi',
                'event_date'    => '2025-02-06',
                'participants'  => 90,
                'total_price'   => 12_500_000,
                'notes'         => 'Full-day + coffee break.',
            ],
            [
                'client_name'   => 'Dinas Kesehatan Merauke',
                'event_type'    => 'Sosialisasi Program',
                'event_date'    => '2025-02-15',
                'participants'  => 120,
                'total_price'   => 9_873_967,
                'notes'         => 'Include LCD projector & backdrop.',
            ],
            [
                'client_name'   => 'Komunitas Wirausaha Muda',
                'event_type'    => 'Workshop Kewirausahaan',
                'event_date'    => '2025-02-23',
                'participants'  => 70,
                'total_price'   => 7_750_000,
                'notes'         => 'Half-day + snack.',
            ],
        ];

        foreach ($bookings as $i => $b) {
            DB::table('bookings')->insert([
                'booking_number'   => sprintf('MICE-202502-%04d', $i + 1),
                'booking_date'     => Carbon::parse($b['event_date'])->subDays(rand(5,10))->toDateString(),
                'client_name'      => $b['client_name'],
                'event_type'       => $b['event_type'],
                'event_date'       => $b['event_date'],
                'start_time'       => '09:00:00',
                'end_time'         => '17:00:00',
                'participants'     => $b['participants'],
                'property_id'      => $propertyId,
                'person_in_charge' => 'Bapak Budi Santoso',
                'status'           => 'Booking Pasti',
                'payment_status'   => 'Paid',
                'total_price'      => number_format($b['total_price'], 2, '.', ''),
                'down_payment'     => number_format($b['total_price'], 2, '.', ''),
                'notes'            => $b['notes'],
                'created_at'       => now(),
                'updated_at'       => now(),
                'mice_category_id' => null,
                'room_id'          => null,
            ]);
        }

        // Verifikasi angka akhir
        $sumDaily = (float) DB::table('daily_incomes')
            ->where('property_id', $propertyId)
            ->whereBetween('date', ['2025-02-01', '2025-02-28'])
            ->sum('total_revenue');

        $sumMice = (float) DB::table('bookings')
            ->where('property_id', $propertyId)
            ->whereBetween('event_date', ['2025-02-01', '2025-02-28'])
            ->sum('total_price');

        $this->command->info('✅ NON-MICE: ' . number_format($sumDaily, 0, ',', '.') .
            ' | MICE: ' . number_format($sumMice, 0, ',', '.') .
            ' | TOTAL: ' . number_format($sumDaily + $sumMice, 0, ',', '.'));
    }
}
