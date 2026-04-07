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
}
