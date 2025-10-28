<?php

namespace App\Http\Traits;

use App\Models\Property;
use App\Models\RoomType;
use App\Models\DailyOccupancy;

trait CalculatesBarPrices
{
    /**
     * Menentukan level BAR yang aktif berdasarkan jumlah kamar terisi.
     *
     * Logika baru (berdasarkan rentang yang diatur Admin):
     * Admin mendefinisikan nilai MAKSIMUM untuk setiap level BAR.
     * Diasumsikan: bar_1 < bar_2 < bar_3 < bar_4 < bar_5
     *
     * Contoh:
     * bar_1 = 5  (Rentang BAR 1: 0-5)
     * bar_2 = 20 (Rentang BAR 2: 6-20)
     * bar_3 = 30 (Rentang BAR 3: 21-30)
     * bar_4 = 40 (Rentang BAR 4: 31-40)
     * bar_5 = 50 (Rentang BAR 5: 41-50, atau lebih jika > 50)
     *
     * @param int $occupiedRooms Jumlah kamar terisi.
     * @param Property $property Model Properti (diasumsikan memiliki bar_1 s/d bar_5).
     * @return int Level BAR yang aktif (1-5).
     */
    private function getActiveBarLevel(int $occupiedRooms, Property $property): int
    {
        // $property->bar_X adalah nilai MAKSIMUM untuk level BAR tersebut.

        // ======================================================
        // BARIS DEBUG: HENTIKAN DAN TAMPILKAN NILAI
        // ======================================================
        dd($occupiedRooms, $property);
        // ======================================================


        // Level 1: (0 s/d bar_1)
        // Jika 0 atau kurang, tetap anggap BAR 1
        if ($occupiedRooms <= $property->bar_1) {
            return 1;
        }
        
        // Level 2: (bar_1 + 1 s/d bar_2)
        if ($occupiedRooms <= $property->bar_2) {
            return 2;
        }
        
        // Level 3: (bar_2 + 1 s/d bar_3)
        if ($occupiedRooms <= $property->bar_3) {
            return 3;
        }
        
        // Level 4: (bar_3 + 1 s/d bar_4)
        if ($occupiedRooms <= $property->bar_4) {
            return 4;
        }

        // Level 5: (bar_4 + 1 s/d bar_5 ATAU lebih)
        // Kita periksa apakah kolom bar_5 ada dan di-set di properti
        if (isset($property->bar_5)) {
            // Jika okupansi masih di bawah atau sama dengan bar_5
            if ($occupiedRooms <= $property->bar_5) {
                return 5;
            }
            // Jika okupansi di atas bar_5, tetap BAR 5 (level tertinggi)
            return 5;
        }

        // Fallback jika kolom bar_5 belum ada di database (logika lama)
        // (occupiedRooms > bar_4)
        return 5;
    }

    /**
     * Menghitung harga BAR yang aktif untuk satu tipe kamar.
     * (Fungsi ini TIDAK PERLU DIUBAH)
     */
    private function calculateActiveBarPrice(RoomType $roomType, int $activeBarLevel)
    {
        $rule = $roomType->pricingRule;
        if (!$rule || !$rule->starting_bar) {
            return $roomType->bottom_rate;
        }

        if ($activeBarLevel < $rule->starting_bar) {
            return $rule->bottom_rate;
        }

        $price = $rule->bottom_rate;
        $increaseFactor = 1 + ($rule->percentage_increase / 100);

        for ($i = 0; $i < ($activeBarLevel - $rule->starting_bar); $i++) {
            $price *= $increaseFactor;
        }
        
        return $price;
    }
}