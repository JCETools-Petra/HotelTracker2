<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SunnydayInnJuneSeeder extends Seeder
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

        $days = 30; // Juni 2025

        // ===== Target TOTAL NON-MICE (daily_incomes) =====
        // Note: others_income dinaikkan +1 agar total MTD match persis 718.908.895
        $totals = [
            'corp_income'         => 209_095_040, // Corporate
            'offline_room_income' => 83_384_298,  // Walk-in
            'ta_income'           => 33_770_879,  // OTA/TA
            'breakfast_income'    => 54_743_802,  // Breakfast
            'lunch_income'        => 6_800_826,   // Restaurant + Room Service
            'others_income'       => 33_882_645,  // Other + Wellness + Laundry (+1 rupiah lock)
        ];
        $targetNonMice = array_sum($totals); // 421,677,490

        $this->command->info("→ Menulis NON-MICE ke daily_incomes (Jun 2025), target: " . number_format($targetNonMice, 0, ',', '.'));

        // Bersihkan Juni
        DB::table('daily_incomes')
            ->where('property_id', $propertyId)
            ->whereBetween('date', ['2025-06-01', '2025-06-30'])
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
            $date = Carbon::create(2025, 6, $d)->toDateString();
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

                'offline_rooms'        => rand(14, 28),
                'offline_room_income'  => number_format($walkin, 2, '.', ''),
                'ta_rooms'             => rand(2, 9),
                'ta_income'            => number_format($ota, 2, '.', ''),
                'corp_rooms'           => rand(6, 14),
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

                'total_rooms_sold'     => rand(24, 40),
                'total_rooms_revenue'  => number_format($totalRooms, 2, '.', ''),
                'total_fb_revenue'     => number_format($totalFB, 2, '.', ''),
                'total_revenue'        => number_format($totalAll, 2, '.', ''),
                'arr'                  => number_format(rand(350000, 460000), 2, '.', ''),
                'occupancy'            => number_format(rand(55, 95), 2, '.', ''),
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // ===== MICE → bookings (total 297,231,405) =====
        $this->command->info("→ Menulis MICE ke bookings (Jun 2025)...");
        DB::table('bookings')
            ->where('property_id', $propertyId)
            ->whereBetween('event_date', ['2025-06-01', '2025-06-30'])
            ->delete();

        // 4 event realistis; total tepat 297,231,405
        $bookings = [
            [
                'client_name'   => 'PT. Agro Papua',
                'event_type'    => 'Raker Semester',
                'event_date'    => '2025-06-06',
                'participants'  => 200,
                'total_price'   => 85_000_000,
                'notes'         => 'Full-day + coffee break.',
            ],
            [
                'client_name'   => 'Dinas Pertanian',
                'event_type'    => 'Sosialisasi Program',
                'event_date'    => '2025-06-13',
                'participants'  => 150,
                'total_price'   => 72_231_405,
                'notes'         => 'Include projector & backdrop.',
            ],
            [
                'client_name'   => 'Komunitas Dagang Merauke',
                'event_type'    => 'Expo UMKM',
                'event_date'    => '2025-06-21',
                'participants'  => 300,
                'total_price'   => 60_000_000,
                'notes'         => 'Hall + sound system.',
            ],
            [
                'client_name'   => 'PT. Logistik Timur',
                'event_type'    => 'Pelatihan Operasional',
                'event_date'    => '2025-06-27',
                'participants'  => 120,
                'total_price'   => 80_000_000,
                'notes'         => 'Full-day + snack.',
            ],
        ];

        foreach ($bookings as $i => $b) {
            DB::table('bookings')->insert([
                'booking_number'   => sprintf('MICE-202506-%04d', $i + 1),
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

        // Verifikasi akhir
        $sumDaily = (float) DB::table('daily_incomes')
            ->where('property_id', $propertyId)
            ->whereBetween('date', ['2025-06-01', '2025-06-30'])
            ->sum('total_revenue');

        $sumMice = (float) DB::table('bookings')
            ->where('property_id', $propertyId)
            ->whereBetween('event_date', ['2025-06-01', '2025-06-30'])
            ->sum('total_price');

        $this->command->info('✅ NON-MICE: ' . number_format($sumDaily, 0, ',', '.') .
            ' | MICE: ' . number_format($sumMice, 0, ',', '.') .
            ' | TOTAL: ' . number_format($sumDaily + $sumMice, 0, ',', '.'));
    }
}
