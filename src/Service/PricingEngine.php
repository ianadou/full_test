<?php

declare(strict_types=1);

namespace App\Service;

final class PricingEngine
{
    public function calculateDeliveryFee(float $distance, float $weight): float
    {
        if ($distance < 0 || $weight < 0) {
            throw new \InvalidArgumentException('Distance and weight must be positive');
        }

        if ($distance > 10) {
            throw new \InvalidArgumentException('Delivery not available beyond 10 km');
        }

        $fee = 2.00;

        if ($distance > 3) {
            $fee += ($distance - 3) * 0.50;
        }

        if ($weight > 5) {
            $fee += 1.50;
        }

        return round($fee, 2);
    }
}
