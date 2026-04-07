<?php

declare(strict_types=1);

namespace App\Service;

final class OrderResult
{
    public function __construct(
        public readonly float $subtotal,
        public readonly float $discount,
        public readonly float $deliveryFee,
        public readonly float $surge,
        public readonly float $total,
    ) {
    }
}
