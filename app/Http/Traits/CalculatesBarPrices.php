<?php

namespace App\Http\Traits;

use App\Models\Property;
use App\Models\RoomType;
use App\Models\DailyOccupancy;

trait CalculatesBarPrices
{
    /**
     * Menentukan level BAR yang aktif berdasarkan jumlah kamar terisi.
     */
    private function getActiveBarLevel(int $occupiedRooms, Property $property): int
    {
        if ($occupiedRooms <= $property->bar_1) return 1;
        if ($occupiedRooms <= $property->bar_2) return 2;
        if ($occupiedRooms <= $property->bar_3) return 3;
        if ($occupiedRooms <= $property->bar_4) return 4;
        return 5;
    }

    /**
     * Menghitung harga BAR yang aktif untuk satu tipe kamar.
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