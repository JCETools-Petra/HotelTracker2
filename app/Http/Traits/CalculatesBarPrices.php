<?php

namespace App\Http\Traits;

use App\Models\Property;
use App\Models\RoomType; // Pastikan ini ditambahkan

trait CalculatesBarPrices
{
    /**
     * Menentukan level BAR (integer 1-5) berdasarkan JUMLAH KAMAR.
     * Logika ini dipindahkan dari DashboardController agar bisa dipakai di mana saja.
     *
     * @param int $occupiedRooms
     * @param Property $property
     * @return int
     */
    public function getActiveBarLevel(int $occupiedRooms, Property $property): int
    {
        // Membandingkan jumlah kamar terisi dengan ambang batas bar
        // Asumsi: bar_1 s/d bar_5 berisi angka integer (ambang batas jumlah kamar)
        
        // Cek dari BAR terendah ke tertinggi
        if ($occupiedRooms <= $property->bar_1) return 1;
        if ($occupiedRooms <= $property->bar_2) return 2;
        if ($occupiedRooms <= $property->bar_3) return 3;
        if ($occupiedRooms <= $property->bar_4) return 4;
        
        // Jika di atas bar_4, atau bar_5 (jika ada)
        // Logika di DashboardController sebelumnya adalah "return 5" jika di atas bar_4
        return 5;
    }

    /**
     * Menentukan NAMA BAR (string) berdasarkan level BAR.
     * Ini yang akan kita simpan di $property->bar_active
     *
     * @param int $barLevel
     * @return string
     */
    public function getActiveBarName(int $barLevel): string
    {
        // Formatnya "bar_1", "bar_2", dst.
        return 'bar_' . $barLevel;
    }

    /**
     * Menghitung harga BAR yang aktif untuk satu tipe kamar.
     * Logika ini dipindahkan dari DashboardController.
     *
     * @param RoomType $roomType
     * @param int $activeBarLevel
     * @return float|int
     */
    public function calculateActiveBarPrice(RoomType $roomType, int $activeBarLevel)
    {
        $rule = $roomType->pricingRule;
        
        // Jika tidak ada aturan harga, pakai harga dasar
        if (!$rule || !$rule->starting_bar) {
            return $roomType->bottom_rate;
        }

        // Jika level BAR aktif di bawah starting_bar, pakai harga dasar
        if ($activeBarLevel < $rule->starting_bar) {
            return $rule->bottom_rate;
        }

        $price = $rule->bottom_rate;
        $increaseFactor = 1 + ($rule->percentage_increase / 100);

        // Hitung kenaikan harga berdasarkan level
        for ($i = 0; $i < ($activeBarLevel - $rule->starting_bar); $i++) {
            $price *= $increaseFactor;
        }
        
        return $price;
    }
}