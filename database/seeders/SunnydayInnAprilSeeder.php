<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SunnydayInnAprilSeeder extends Seeder
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

        $days = 30; // April 2025

        // ===== Target TOTAL NON-MICE (daily_incomes) =====
        $totals = [
            'corp_income'         => 3_480_779,   // Corporate
            'offline_room_income' => 57_556_198,  // Walk-in
            'ta_income'           => 26_265_130,  // OTA/TA
            'breakfast_income'    => 19_966_942,  // Breakfast
            'lunch_income'        => 4_865_289,   // Restaurant + Room Service
            // Others disesuaikan -1 agar gabungan dengan MICE = 203.996.321 pas
            'others_income'       => 29_630_578,  // (24.597.521 + 4.710.744 + 322.314) - 1
        ];
        $targetNonMice = array_sum($totals); // 141,764,916

        $this->command->info("→ Menulis NON-MICE ke daily_incomes (Apr 2025), target: " . number_format($targetNonMice, 0, ',', '.'));

        // Bersihkan April
        DB::table('daily_incomes')
            ->where('property_id', $propertyId)
            ->whereBetween('date', ['2025-04-01', '2025-04-30'])
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
            $date = Carbon::create(2025, 4, $d)->toDateString();
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
                'arr'                  => number_format(rand(290000, 360000), 2, '.', ''),
                'occupancy'            => number_format(rand(22, 70), 2, '.', ''),
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // ===== MICE → bookings (total 62,231,405) =====
        $this->command->info("→ Menulis MICE ke bookings (Apr 2025)...");
        DB::table('bookings')
            ->where('property_id', $propertyId)
            ->whereBetween('event_date', ['2025-04-01', '2025-04-30'])
            ->delete();

        // 3 event realistis yang totalnya tepat 62,231,405
        $bookings = [
            [
                'client_name'   => 'PT. Perkebunan Timur',
                'event_type'    => 'Rapat Koordinasi',
                'event_date'    => '2025-04-08',
                'participants'  => 110,
                'total_price'   => 22_000_000,
                'notes'         => 'Full-day + coffee break.',
            ],
            [
                'client_name'   => 'Dinas Pariwisata',
                'event_type'    => 'Workshop Layanan',
                'event_date'    => '2025-04-17',
                'participants'  => 85,
                'total_price'   => 18_731_405,
                'notes'         => 'Include projector & backdrop.',
            ],
            [
                'client_name'   => 'Komunitas Kreatif Merauke',
                'event_type'    => 'Expo UMKM',
                'event_date'    => '2025-04-26',
                'participants'  => 180,
                'total_price'   => 21_500_000,
                'notes'         => 'Hall + sound system.',
            ],
        ];

        foreach ($bookings as $i => $b) {
            DB::table('bookings')->insert([
                'booking_number'   => sprintf('MICE-202504-%04d', $i + 1),
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
            ->whereBetween('date', ['2025-04-01', '2025-04-30'])
            ->sum('total_revenue');

        $sumMice = (float) DB::table('bookings')
            ->where('property_id', $propertyId)
            ->whereBetween('event_date', ['2025-04-01', '2025-04-30'])
            ->sum('total_price');

        $this->command->info('✅ NON-MICE: ' . number_format($sumDaily, 0, ',', '.') .
            ' | MICE: ' . number_format($sumMice, 0, ',', '.') .
            ' | TOTAL: ' . number_format($sumDaily + $sumMice, 0, ',', '.'));
    }
}
