<?php

namespace App\Services;

use App\Models\Property;
use App\Models\DailyOccupancy;
use Carbon\Carbon;

class ReservationPriceService
{
    public function getCurrentPricesForProperty(int $propertyId, string $checkinDate)
    {
        $property = Property::with('roomTypes.pricingRule')->find($propertyId);

        if (!$property) {
            return collect();
        }

        $occupancyToday = DailyOccupancy::where('property_id', $propertyId)
                                        ->where('date', Carbon::parse($checkinDate)->toDateString())
                                        ->first();

        $roomsSoldCount = $occupancyToday ? $occupancyToday->occupied_rooms : 0;

        $barCapacities = [
            (int)$property->bar_1, (int)$property->bar_2, (int)$property->bar_3,
            (int)$property->bar_4, (int)$property->bar_5,
        ];

        $thresholds = [];
        $cumulativeCapacity = 0;
        foreach ($barCapacities as $capacity) {
            if ($capacity > 0) {
                $cumulativeCapacity += $capacity;
                $thresholds[] = $cumulativeCapacity;
            }
        }

        $actualTiersPassed = 0;
        foreach ($thresholds as $threshold) {
            if ($roomsSoldCount > $threshold) {
                $actualTiersPassed++;
            }
        }

        return $property->roomTypes->map(function ($roomType) use ($actualTiersPassed, $thresholds) {
            $rule = $roomType->pricingRule;

            if (!$rule) {
                return [
                    'name' => $roomType->name,
                    'price_ota' => 0,
                    'price_affiliate_breakdown' => null
                ];
            }

            // 1. Hitung harga OTA seperti biasa
            $priceOta = $this->calculateOtaPrice($rule, $actualTiersPassed, $thresholds);

            // 2. Hitung harga Afiliasi berdasarkan harga OTA
            $affiliateBreakdown = $this->calculateAffiliatePrice($priceOta);

            return [
                'name' => $roomType->name,
                'price_ota' => round($priceOta),
                'price_affiliate_breakdown' => $affiliateBreakdown,
            ];
        });
    }

    private function calculateOtaPrice($rule, $actualTiersPassed, $thresholds)
    {
        $startingBar = (int)$rule->starting_bar;
        $chargeableIncreaseSteps = 0;
        if ($actualTiersPassed >= $startingBar) {
            $chargeableIncreaseSteps = $actualTiersPassed - ($startingBar - 1);
        }

        if (!empty($thresholds) && $actualTiersPassed >= count($thresholds)) {
            return (float)$rule->publish_rate;
        }

        $currentPrice = (float)$rule->bottom_rate;
        if ($rule->percentage_increase > 0) {
            $percentage = 1 + ((float)$rule->percentage_increase / 100);
            $currentPrice *= pow($percentage, $chargeableIncreaseSteps);
        }

        return $currentPrice;
    }

    /**
     * Fungsi helper baru untuk menghitung harga Afiliasi.
     */
    private function calculateAffiliatePrice(float $otaPrice)
    {
        $discountPercentage = 0.05; // 5%
        $commissionPercentage = 0.10; // 10%

        $priceAfterDiscount = $otaPrice * (1 - $discountPercentage);
        $commissionAmount = $priceAfterDiscount * $commissionPercentage;
        $finalAffiliatePrice = $priceAfterDiscount - $commissionAmount;

        return [
            'initial_ota_price' => round($otaPrice),
            'discount_amount' => round($otaPrice * $discountPercentage),
            'price_after_discount' => round($priceAfterDiscount),
            'commission_amount' => round($commissionAmount),
            'final_price' => round($finalAffiliatePrice),
        ];
    }
}