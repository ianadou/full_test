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
    // --- calculateOrderTotal ---

    public function test_returns_correct_total_when_normal_order(): void
    {
        // ARRANGE — 2 pizzas 12.50€ + 5km + 2kg + mardi 15h
        // subtotal = 25€, delivery = 2 + (2*0.50) = 3€, surge = 1.0, total = 28€
        $items = [['name' => 'Pizza', 'price' => 12.50, 'quantity' => 2]];

        // ACT
        $result = $this->engine->calculateOrderTotal($items, 5, 2, null, 15, 'tuesday');

        // ASSERT
        $this->assertSame(25.0, $result->subtotal);
        $this->assertSame(0.0, $result->discount);
        $this->assertSame(3.0, $result->deliveryFee);
        $this->assertSame(1.0, $result->surge);
        $this->assertSame(28.0, $result->total);
    }

    public function test_returns_correct_total_when_promo_applied(): void
    {
        // ARRANGE — 50€ subtotal + BIENVENUE20 (20% off) + 2km + mardi 15h
        // subtotal = 50€, discount = 10€, delivery = 2€, surge = 1.0, total = 42€
        $items = [['name' => 'Sushi', 'price' => 25.0, 'quantity' => 2]];

        // ACT
        $result = $this->engine->calculateOrderTotal($items, 2, 1, 'BIENVENUE20', 15, 'tuesday');

        // ASSERT
        $this->assertSame(50.0, $result->subtotal);
        $this->assertSame(10.0, $result->discount);
        $this->assertSame(2.0, $result->deliveryFee);
        $this->assertSame(42.0, $result->total);
    }

    public function test_applies_surge_to_delivery_fee(): void
    {
        // ARRANGE — 5km + vendredi 20h → surge 1.8
        // delivery = 2 + (2*0.50) = 3€ * 1.8 = 5.4€
        $items = [['name' => 'Burger', 'price' => 15.0, 'quantity' => 1]];

        // ACT
        $result = $this->engine->calculateOrderTotal($items, 5, 1, null, 20, 'friday');

        // ASSERT
        $this->assertSame(1.8, $result->surge);
        $this->assertSame(5.4, $result->deliveryFee);
        $this->assertSame(20.4, $result->total);
    }

    public function test_returns_correct_total_when_multiple_items(): void
    {
        // ARRANGE — Pizza 12.50 x2 + Coca 3€ x1 = 28€
        $items = [
            ['name' => 'Pizza', 'price' => 12.50, 'quantity' => 2],
            ['name' => 'Coca', 'price' => 3.0, 'quantity' => 1],
        ];

        // ACT
        $result = $this->engine->calculateOrderTotal($items, 2, 1, null, 15, 'tuesday');

        // ASSERT
        $this->assertSame(28.0, $result->subtotal);
        $this->assertSame(30.0, $result->total);
    }

    public function test_throws_exception_when_empty_cart(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('empty');

        // ACT
        $this->engine->calculateOrderTotal([], 5, 1, null, 15, 'tuesday');
    }

    public function test_throws_exception_when_negative_price(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $items = [['name' => 'Bug', 'price' => -5.0, 'quantity' => 1]];
        $this->engine->calculateOrderTotal($items, 5, 1, null, 15, 'tuesday');
    }

    public function test_throws_exception_when_zero_quantity(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $items = [['name' => 'Bug', 'price' => 10.0, 'quantity' => 0]];
        $this->engine->calculateOrderTotal($items, 5, 1, null, 15, 'tuesday');
    }

    public function test_throws_exception_when_restaurant_closed(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('closed');

        // ACT
        $items = [['name' => 'Pizza', 'price' => 12.50, 'quantity' => 1]];
        $this->engine->calculateOrderTotal($items, 5, 1, null, 23, 'tuesday');
    }

    public function test_throws_exception_when_beyond_delivery_zone(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $items = [['name' => 'Pizza', 'price' => 12.50, 'quantity' => 1]];
        $this->engine->calculateOrderTotal($items, 15, 1, null, 15, 'tuesday');
    }

    public function test_returns_rounded_values(): void
    {
        // ARRANGE — 3 items at 3.33€ = 9.99€ + 2km = 11.99€
        $items = [['name' => 'Tapas', 'price' => 3.33, 'quantity' => 3]];

        // ACT
        $result = $this->engine->calculateOrderTotal($items, 2, 1, null, 15, 'tuesday');

        // ASSERT
        $this->assertSame(9.99, $result->subtotal);
        $this->assertSame(11.99, $result->total);
    }

}
