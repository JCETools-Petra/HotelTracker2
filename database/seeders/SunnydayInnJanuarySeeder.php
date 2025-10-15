<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SunnydayInnJanuarySeeder extends Seeder
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
        $property = DB::table('properties')->where('name', 'Sunnyday Inn')->first();
        if (!$property) { $this->command->error('Property "Sunnyday Inn" tidak ditemukan!'); return; }

        $propertyId = $property->id;
        $userId     = DB::table('users')->value('id');
        $days       = 31;

        // TARGET NON-MICE (harus jadi SUM(total_revenue) Januari)
        $totals = [
            'corp_income'         => 63_033_783,
            'offline_room_income' => 66_118_944, // Walk-in
            'ta_income'           => 16_736_736, // OTA/TA
            'breakfast_income'    => 25_719_008,
            'lunch_income'        => 4_688_430,  // Restaurant (+ Room Service)
            'others_income'       => 22_430_579, // Other + Laundry + Wellness
        ];
        $targetMonth = array_sum($totals); // 198,727,480

        $this->command->info("→ Menulis ke daily_incomes (non-MICE), target: " . number_format($targetMonth, 0, ',', '.'));

        // Bersihkan Januari
        DB::table('daily_incomes')
            ->where('property_id', $propertyId)
            ->whereBetween('date', ['2025-01-01', '2025-01-31'])
            ->delete();

        // Distribusi fluktuatif utk masing2 komponen
        $dist = [];
        foreach ($totals as $k => $v) $dist[$k] = $this->distribute($v, $days);

        // ===== Hard-lock SUM(total_revenue) =====
        // Hitung total_revenue per hari (sementara)
        $tempTotals = [];
        $running = 0;
        for ($i = 0; $i < $days; $i++) {
            $dayTotal =
                $dist['corp_income'][$i] +
                $dist['offline_room_income'][$i] +
                $dist['ta_income'][$i] +
                $dist['breakfast_income'][$i] +
                $dist['lunch_income'][$i] +
                $dist['others_income'][$i];

            $tempTotals[$i] = $dayTotal;
        }

        // Selisih bulan (jika ada drift)
        $sumNow  = array_sum($tempTotals);
        $delta   = $targetMonth - $sumNow; // bisa +/-
        // Tambahkan seluruh delta ke others_income hari terakhir supaya SUM pas
        $dist['others_income'][$days - 1] += $delta;
        // Pastikan tidak negatif
        if ($dist['others_income'][$days - 1] < 0) {
            // kalau negatif (kasus ekstrem), geser ke hari-hari sebelumnya
            $need = -$dist['others_income'][$days - 1];
            $dist['others_income'][$days - 1] = 0;
            for ($i = $days - 2; $i >= 0 && $need > 0; $i--) {
                $take = min($dist['others_income'][$i], $need);
                $dist['others_income'][$i] -= $take;
                $need -= $take;
            }
        }

        // Insert harian non-MICE (total_revenue dihitung dari komponen)
        for ($d = 1; $d <= $days; $d++) {
            $date = Carbon::create(2025, 1, $d)->toDateString();
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

                'offline_rooms'        => rand(10, 20),
                'offline_room_income'  => number_format($walkin, 2, '.', ''),
                'ta_rooms'             => rand(2, 5),
                'ta_income'            => number_format($ota, 2, '.', ''),
                'corp_rooms'           => rand(5, 10),
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

                'total_rooms_sold'     => rand(20, 35),
                'total_rooms_revenue'  => number_format($totalRooms, 2, '.', ''),
                'total_fb_revenue'     => number_format($totalFB, 2, '.', ''),
                'total_revenue'        => number_format($totalAll, 2, '.', ''),

                'arr'                  => number_format(rand(250000, 350000), 2, '.', ''),
                'occupancy'            => number_format(rand(30, 80), 2, '.', ''),
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // ===== MICE tetap ke bookings (sama seperti sebelumnya) =====
        $this->command->info("→ Menulis ke bookings (MICE)...");
        DB::table('bookings')
            ->where('property_id', $propertyId)
            ->whereBetween('event_date', ['2025-01-01', '2025-01-31'])
            ->delete();

        $bookings = [
            ['client_name'=>'Dinas Pendidikan Merauke','event_type'=>'Rapat Kerja Tahunan','event_date'=>'2025-01-10','participants'=>150,'total_price'=>45_500_000,'notes'=>'Termasuk coffee break dan makan siang.'],
            ['client_name'=>'PT. Sinar Papua Sejati','event_type'=>'Product Launching','event_date'=>'2025-01-18','participants'=>80,'total_price'=>28_050_413,'notes'=>'Paket full-day, include backdrop.'],
            ['client_name'=>'Keluarga Besar Situmorang','event_type'=>'Acara Pernikahan','event_date'=>'2025-01-25','participants'=>250,'total_price'=>37_500_000,'notes'=>'Termasuk dekorasi dan band.'],
        ];

        foreach ($bookings as $i => $b) {
            DB::table('bookings')->insert([
                'booking_number'   => sprintf('MICE-202501-%04d', $i + 1),
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

        // Verifikasi
        $sumDaily = (float) DB::table('daily_incomes')
            ->where('property_id', $propertyId)
            ->whereBetween('date', ['2025-01-01', '2025-01-31'])
            ->sum('total_revenue');

        $sumMice = (float) DB::table('bookings')
            ->where('property_id', $propertyId)
            ->whereBetween('event_date', ['2025-01-01', '2025-01-31'])
            ->sum('total_price');

        $this->command->info('✅ NON-MICE: ' . number_format($sumDaily, 0, ',', '.') .
            ' | MICE: ' . number_format($sumMice, 0, ',', '.') .
            ' | TOTAL: ' . number_format($sumDaily + $sumMice, 0, ',', '.'));
    }
}
