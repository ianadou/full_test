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
        /** @var PromoCode[] */
    private array $promoCodes = [];

    public function __construct()
    {
        $this->promoCodes = [
            new PromoCode('BIENVENUE20', 'percentage', 20, 15.00, '2026-12-31'),
            new PromoCode('FIXE5', 'fixed', 5, 10.00, '2026-12-31'),
            new PromoCode('EXPIRE', 'percentage', 10, 0.00, '2020-01-01'),
        ];
    }

    public function applyPromoCode(float $subtotal, ?string $promoCode): float
    {
        if ($subtotal < 0) {
            throw new \InvalidArgumentException('Subtotal must be positive');
        }

        if ($promoCode === null || $promoCode === '') {
            return $subtotal;
        }

        $found = null;
        foreach ($this->promoCodes as $promo) {
            if ($promo->code === $promoCode) {
                $found = $promo;
                break;
            }
        }

        if ($found === null) {
            throw new \InvalidArgumentException('Unknown promo code');
        }

        if ($found->expiresAt < date('Y-m-d')) {
            throw new \InvalidArgumentException('Promo code has expired');
        }

        if ($subtotal < $found->minOrder) {
            throw new \InvalidArgumentException('Order does not meet minimum amount');
        }

        $discounted = match ($found->type) {
            'percentage' => $subtotal * (1 - $found->value / 100),
            'fixed' => $subtotal - $found->value,
            default => $subtotal,
        };

        return round(max(0.0, $discounted), 2);
    }

        public function calculateSurge(float $hour, string $dayOfWeek): float
    {
        $day = strtolower($dayOfWeek);

        if ($hour < 10 || $hour >= 22) {
            return 0.0;
        }

        if ($day === 'sunday') {
            return 1.2;
        }

        $isWeekend = in_array($day, ['friday', 'saturday'], true);

        if ($isWeekend && $hour >= 19) {
            return 1.8;
        }

        $isWeekday = in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday'], true);

        if ($isWeekday) {
            if ($hour >= 12 && $hour < 13.5) {
                return 1.3;
            }

            if ($hour >= 19 && $hour < 21) {
                return 1.5;
            }
        }

        return 1.0;
    }

        /**
     * @param array<array{name: string, price: float, quantity: int}> $items
     */
    public function calculateOrderTotal(
        array $items,
        float $distance,
        float $weight,
        ?string $promoCode,
        float $hour,
        string $dayOfWeek,
    ): OrderResult {
        if ($items === []) {
            throw new \InvalidArgumentException('Cart cannot be empty');
        }

        foreach ($items as $item) {
            if ($item['price'] < 0) {
                throw new \InvalidArgumentException('Item price cannot be negative');
            }
            if ($item['quantity'] <= 0) {
                throw new \InvalidArgumentException('Item quantity must be positive');
            }
        }

        $surge = $this->calculateSurge($hour, $dayOfWeek);

        if ($surge === 0.0) {
            throw new \InvalidArgumentException('Restaurant is closed');
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $subtotal = round($subtotal, 2);

        $discountedSubtotal = $this->applyPromoCode($subtotal, $promoCode);
        $discount = round($subtotal - $discountedSubtotal, 2);

        $deliveryFee = $this->calculateDeliveryFee($distance, $weight);
        $deliveryFee = round($deliveryFee * $surge, 2);

        $total = round($discountedSubtotal + $deliveryFee, 2);

        return new OrderResult($subtotal, $discount, $deliveryFee, $surge, $total);
    }


}
