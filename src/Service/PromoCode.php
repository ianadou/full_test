<?php

declare(strict_types=1);

namespace App\Service;
final class PromoCode{
    public function __construct(
        public readonly string $code,
        public readonly string $type,
        public readonly float $value,
        public readonly float $minOrder,
        public readonly string $expiresAt,

    ) {
    }
}