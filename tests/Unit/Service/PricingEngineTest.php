<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\PricingEngine;
use PHPUnit\Framework\TestCase;

final class PricingEngineTest extends TestCase
{
    private PricingEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new PricingEngine();
    }

    // --- calculateDeliveryFee ---

    public function test_returns_base_fee_when_short_distance_light_weight(): void
    {
        // ACT
        $result = $this->engine->calculateDeliveryFee(2, 1);

        // ASSERT
        $this->assertSame(2.0, $result);
    }

    public function test_returns_base_fee_when_exactly_3km(): void
    {
        // ACT
        $result = $this->engine->calculateDeliveryFee(3, 1);

        // ASSERT
        $this->assertSame(2.0, $result);
    }

    public function test_adds_distance_supplement_when_beyond_3km(): void
    {
        // ACT — 6km: 2.00 + (3 * 0.50) = 3.50
        $result = $this->engine->calculateDeliveryFee(6, 2);

        // ASSERT
        $this->assertSame(3.5, $result);
    }

    public function test_adds_weight_supplement_when_above_5kg(): void
    {
        // ACT — 5km, 8kg: 2.00 + (2 * 0.50) + 1.50 = 4.50
        $result = $this->engine->calculateDeliveryFee(5, 8);

        // ASSERT
        $this->assertSame(4.5, $result);
    }

    public function test_no_weight_supplement_when_exactly_5kg(): void
    {
        // ACT — 2km, 5kg: 2.00 (pas de supplement)
        $result = $this->engine->calculateDeliveryFee(2, 5);

        // ASSERT
        $this->assertSame(2.0, $result);
    }

    public function test_returns_max_fee_when_exactly_10km_heavy(): void
    {
        // ACT — 10km, 6kg: 2.00 + (7 * 0.50) + 1.50 = 7.00
        $result = $this->engine->calculateDeliveryFee(10, 6);

        // ASSERT
        $this->assertSame(7.0, $result);
    }

    public function test_throws_exception_when_beyond_10km(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $this->engine->calculateDeliveryFee(15, 1);
    }

    public function test_throws_exception_when_negative_distance(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $this->engine->calculateDeliveryFee(-1, 1);
    }

    public function test_throws_exception_when_negative_weight(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $this->engine->calculateDeliveryFee(5, -1);
    }

    public function test_returns_base_fee_when_zero_distance(): void
    {
        // ACT
        $result = $this->engine->calculateDeliveryFee(0, 1);

        // ASSERT
        $this->assertSame(2.0, $result);
    }

        // --- applyPromoCode ---

    public function test_applies_percentage_discount(): void
    {
        // ACT — 20% off 50€ = 40€
        $result = $this->engine->applyPromoCode(50.0, 'BIENVENUE20');

        // ASSERT
        $this->assertSame(40.0, $result);
    }

    public function test_applies_fixed_discount(): void
    {
        // ACT — 5€ off 30€ = 25€
        $result = $this->engine->applyPromoCode(30.0, 'FIXE5');

        // ASSERT
        $this->assertSame(25.0, $result);
    }

    public function test_throws_exception_when_promo_expired(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('expired');

        // ACT
        $this->engine->applyPromoCode(50.0, 'EXPIRE');
    }

    public function test_throws_exception_when_below_min_order(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('minimum');

        // ACT
        $this->engine->applyPromoCode(5.0, 'BIENVENUE20');
    }

    public function test_throws_exception_when_unknown_promo(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown');

        // ACT
        $this->engine->applyPromoCode(50.0, 'FAKE');
    }

    public function test_returns_zero_when_fixed_exceeds_subtotal(): void
    {
        // ACT — 5€ off 3€ = max(0, -2) = 0
        $result = $this->engine->applyPromoCode(10.01, 'FIXE5');

        // ASSERT
        $this->assertSame(5.01, $result);
    }

    public function test_returns_subtotal_when_null_promo(): void
    {
        // ACT
        $result = $this->engine->applyPromoCode(50.0, null);

        // ASSERT
        $this->assertSame(50.0, $result);
    }

    public function test_returns_subtotal_when_empty_promo(): void
    {
        // ACT
        $result = $this->engine->applyPromoCode(50.0, '');

        // ASSERT
        $this->assertSame(50.0, $result);
    }

    public function test_throws_exception_when_negative_subtotal(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $this->engine->applyPromoCode(-10.0, 'BIENVENUE20');
    }

    public function test_returns_zero_when_100_percent_discount(): void
    {
        // ARRANGE — on teste avec BIENVENUE20 sur le minimum exact: 15€
        // 20% de 15 = 12€
        $result = $this->engine->applyPromoCode(15.0, 'BIENVENUE20');

        // ASSERT
        $this->assertSame(12.0, $result);
    }
        // --- calculateSurge ---

    public function test_returns_normal_rate_when_weekday_afternoon(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(15, 'tuesday');

        // ASSERT
        $this->assertSame(1.0, $result);
    }

    public function test_returns_lunch_rate_when_weekday_noon(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(12.5, 'wednesday');

        // ASSERT
        $this->assertSame(1.3, $result);
    }

    public function test_returns_dinner_rate_when_weekday_evening(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(20, 'thursday');

        // ASSERT
        $this->assertSame(1.5, $result);
    }

    public function test_returns_weekend_rate_when_friday_evening(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(21, 'friday');

        // ASSERT
        $this->assertSame(1.8, $result);
    }

    public function test_returns_sunday_rate_when_sunday(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(14, 'sunday');

        // ASSERT
        $this->assertSame(1.2, $result);
    }

    public function test_returns_closed_when_before_opening(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(9.59, 'monday');

        // ASSERT
        $this->assertSame(0.0, $result);
    }

    public function test_returns_open_when_exactly_10(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(10, 'monday');

        // ASSERT
        $this->assertSame(1.0, $result);
    }

    public function test_returns_closed_when_exactly_22(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(22, 'tuesday');

        // ASSERT
        $this->assertSame(0.0, $result);
    }

    public function test_returns_weekend_rate_when_exactly_19_on_saturday(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(19, 'saturday');

        // ASSERT
        $this->assertSame(1.8, $result);
    }


}
