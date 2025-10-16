<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\DailyIncome;
use App\Models\User;

class HotelAkatFebruaryIncomeSeeder extends Seeder
{
    public function run()
    {
        $property = Property::where('name', 'Hotel Akat')->first();
        if (!$property) {
            $this->command->error('Property with name "Hotel Akat" not found.');
            return;
        }

        $user = User::first();
        if (!$user) {
            $this->command->error('No users found in the database. Please create a user first.');
            return;
        }

        // === Data harian dari sheet "AKAT HOTEL FEBRUARI 2025" ===
        // Format: 'YYYY-MM-DD' => [rooms_sold_today, cash, tf_bank, cl_ota]
        $dailyData = [
            '2025-02-01' => [ 8, 2160000, 0,       0],
            '2025-02-02' => [ 9, 2160000, 300000,  0],
            '2025-02-03' => [12, 3450000, 0,       0],
            '2025-02-04' => [ 4,  950000, 0,       0],
            '2025-02-05' => [ 9, 2580000, 0,       0],
            '2025-02-06' => [ 9, 2230000, 350000,  0],
            '2025-02-07' => [ 6, 1650000, 0,       0],
            '2025-02-08' => [15, 3540000, 670000,  0],
            '2025-02-09' => [10, 1920000, 640000,  0],
            '2025-02-10' => [10, 1290000, 1240000, 304200],
            '2025-02-11' => [11, 3220000, 0,       0],
            '2025-02-12' => [ 6, 1570000, 0,       0],
            '2025-02-13' => [ 7, 1890000, 0,       0],
            '2025-02-14' => [ 8, 1900000, 350000,  0],
            '2025-02-15' => [17, 3530000, 1590000, 0],
            '2025-02-16' => [ 6, 1340000, 350000,  0],
            '2025-02-17' => [ 7, 1750000, 300000,  0],
            '2025-02-18' => [ 7, 1000000, 650000,  304200],
            '2025-02-19' => [11, 2560000, 670000,  0],
            '2025-02-20' => [11, 2800000, 400000,  0],
            '2025-02-21' => [10, 2460000, 400000,  0],
            '2025-02-22' => [11, 2890000, 400000,  0],
            '2025-02-23' => [11, 2170000, 720000,  0],
            '2025-02-24' => [12, 2780000, 650000,  0],
            '2025-02-25' => [12, 3170000, 350000,  0],
            '2025-02-26' => [ 8, 1610000, 650000,  0],
            '2025-02-27' => [12, 3140000, 350000,  0],
            // Catatan: di sheet "Today Room Revenue" tertulis 5.180.000, namun kolom CASH+TF=3.180.000
            // Kita tetap konsisten ambil CASH+TF dari "Room" dan CL dari "OTA".
            '2025-02-28' => [11, 2530000, 650000,  0],
        ];

        // === Cash paket bulanan: Rp 2.000.000 (dibagi rata per hari) ===
        $monthly_cash_total = 2_000_000;
        $days_in_month = count($dailyData);
        $daily_portion = $days_in_month > 0 ? ($monthly_cash_total / $days_in_month) : 0;

        $this->command->info('Seeding Hotel Akat - February 2025 (daily_incomes)...');

        DB::transaction(function () use ($property, $user, $dailyData, $daily_portion) {
            // Bersihkan data Februari lama untuk property ini
            DailyIncome::where('property_id', $property->id)
                ->whereBetween('date', ['2025-02-01', '2025-02-28'])
                ->delete();

            foreach ($dailyData as $date => $data) {
                [$rooms_sold, $cash, $tf_bank, $cl_ota] = $data;

                // Pendapatan corporate = CASH + TF BANK + porsi cash bulanan
                $corp_revenue = (int)$cash + (int)$tf_bank + (int)round($daily_portion);
                $ota_revenue  = (int)$cl_ota;

                // Sesuai instruksi "CL jangan dipecah": semua kamar dicatat sebagai corp
                $corp_rooms = (int)$rooms_sold;
                $ota_rooms  = 0;

                $income = DailyIncome::firstOrNew([
                    'property_id' => $property->id,
                    'date'        => $date,
                ]);

                $income->user_id            = $user->id;
                $income->corp_rooms         = $corp_rooms;
                $income->corp_income        = $corp_revenue;
                $income->online_rooms       = $ota_rooms;
                $income->online_room_income = $ota_revenue;

                // Kolom lain 0
                $income->offline_rooms = 0;           $income->offline_room_income = 0;
                $income->ta_rooms = 0;                $income->ta_income = 0;
                $income->gov_rooms = 0;               $income->gov_income = 0;
                $income->compliment_rooms = 0;        $income->compliment_income = 0;
                $income->house_use_rooms = 0;         $income->house_use_income = 0;
                $income->afiliasi_rooms = 0;          $income->afiliasi_room_income = 0;
                $income->mice_rooms = 0;              $income->mice_room_income = 0;
                $income->breakfast_income = 0;        $income->lunch_income = 0;  $income->dinner_income = 0;
                $income->others_income = 0;

                // Hitung total (pastikan model menjumlah integer kolom2, tanpa rounding ADR)
                $income->recalculateTotals();
                $income->save();
            }
        });

        $this->command->info('✅ Selesai seeding Hotel Akat – February 2025.');
    }
}
