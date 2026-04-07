<?php

declare(strict_types=1);

namespace App\Service;

final class MathHelper
{
    /**
     * @param float[]|null $numbers
     */
    public function calculateAverage(?array $numbers): float
    {
        if (null === $numbers || [] === $numbers) {
            return 0.0;
        }

        return round(array_sum($numbers) / count($numbers), 2);
    }

    public function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}
