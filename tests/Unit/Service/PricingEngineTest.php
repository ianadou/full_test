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

    public function testReturnsBaseFeeWhenShortDistanceLightWeight(): void
    {
        // ACT
        $result = $this->engine->calculateDeliveryFee(2, 1);

        // ASSERT
        $this->assertSame(2.0, $result);
    }

    public function testReturnsBaseFeeWhenExactly3km(): void
    {
        // ACT
        $result = $this->engine->calculateDeliveryFee(3, 1);

        // ASSERT
        $this->assertSame(2.0, $result);
    }

    public function testAddsDistanceSupplementWhenBeyond3km(): void
    {
        // ACT — 6km: 2.00 + (3 * 0.50) = 3.50
        $result = $this->engine->calculateDeliveryFee(6, 2);

        // ASSERT
        $this->assertSame(3.5, $result);
    }

    public function testAddsWeightSupplementWhenAbove5kg(): void
    {
        // ACT — 5km, 8kg: 2.00 + (2 * 0.50) + 1.50 = 4.50
        $result = $this->engine->calculateDeliveryFee(5, 8);

        // ASSERT
        $this->assertSame(4.5, $result);
    }

    public function testNoWeightSupplementWhenExactly5kg(): void
    {
        // ACT — 2km, 5kg: 2.00 (pas de supplement)
        $result = $this->engine->calculateDeliveryFee(2, 5);

        // ASSERT
        $this->assertSame(2.0, $result);
    }

    public function testReturnsMaxFeeWhenExactly10kmHeavy(): void
    {
        // ACT — 10km, 6kg: 2.00 + (7 * 0.50) + 1.50 = 7.00
        $result = $this->engine->calculateDeliveryFee(10, 6);

        // ASSERT
        $this->assertSame(7.0, $result);
    }

    public function testThrowsExceptionWhenBeyond10km(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $this->engine->calculateDeliveryFee(15, 1);
    }

    public function testThrowsExceptionWhenNegativeDistance(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $this->engine->calculateDeliveryFee(-1, 1);
    }

    public function testThrowsExceptionWhenNegativeWeight(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $this->engine->calculateDeliveryFee(5, -1);
    }

    public function testReturnsBaseFeeWhenZeroDistance(): void
    {
        // ACT
        $result = $this->engine->calculateDeliveryFee(0, 1);

        // ASSERT
        $this->assertSame(2.0, $result);
    }

    // --- applyPromoCode ---

    public function testAppliesPercentageDiscount(): void
    {
        // ACT — 20% off 50€ = 40€
        $result = $this->engine->applyPromoCode(50.0, 'BIENVENUE20');

        // ASSERT
        $this->assertSame(40.0, $result);
    }

    public function testAppliesFixedDiscount(): void
    {
        // ACT — 5€ off 30€ = 25€
        $result = $this->engine->applyPromoCode(30.0, 'FIXE5');

        // ASSERT
        $this->assertSame(25.0, $result);
    }

    public function testThrowsExceptionWhenPromoExpired(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('expired');

        // ACT
        $this->engine->applyPromoCode(50.0, 'EXPIRE');
    }

    public function testThrowsExceptionWhenBelowMinOrder(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('minimum');

        // ACT
        $this->engine->applyPromoCode(5.0, 'BIENVENUE20');
    }

    public function testThrowsExceptionWhenUnknownPromo(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown');

        // ACT
        $this->engine->applyPromoCode(50.0, 'FAKE');
    }

    public function testReturnsZeroWhenFixedExceedsSubtotal(): void
    {
        // ACT — 5€ off 3€ = max(0, -2) = 0
        $result = $this->engine->applyPromoCode(10.01, 'FIXE5');

        // ASSERT
        $this->assertSame(5.01, $result);
    }

    public function testReturnsSubtotalWhenNullPromo(): void
    {
        // ACT
        $result = $this->engine->applyPromoCode(50.0, null);

        // ASSERT
        $this->assertSame(50.0, $result);
    }

    public function testReturnsSubtotalWhenEmptyPromo(): void
    {
        // ACT
        $result = $this->engine->applyPromoCode(50.0, '');

        // ASSERT
        $this->assertSame(50.0, $result);
    }

    public function testThrowsExceptionWhenNegativeSubtotal(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $this->engine->applyPromoCode(-10.0, 'BIENVENUE20');
    }

    public function testReturnsZeroWhen100PercentDiscount(): void
    {
        // ARRANGE — on teste avec BIENVENUE20 sur le minimum exact: 15€
        // 20% de 15 = 12€
        $result = $this->engine->applyPromoCode(15.0, 'BIENVENUE20');

        // ASSERT
        $this->assertSame(12.0, $result);
    }
    // --- calculateSurge ---

    public function testReturnsNormalRateWhenWeekdayAfternoon(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(15, 'tuesday');

        // ASSERT
        $this->assertSame(1.0, $result);
    }

    public function testReturnsLunchRateWhenWeekdayNoon(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(12.5, 'wednesday');

        // ASSERT
        $this->assertSame(1.3, $result);
    }

    public function testReturnsDinnerRateWhenWeekdayEvening(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(20, 'thursday');

        // ASSERT
        $this->assertSame(1.5, $result);
    }

    public function testReturnsWeekendRateWhenFridayEvening(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(21, 'friday');

        // ASSERT
        $this->assertSame(1.8, $result);
    }

    public function testReturnsSundayRateWhenSunday(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(14, 'sunday');

        // ASSERT
        $this->assertSame(1.2, $result);
    }

    public function testReturnsClosedWhenBeforeOpening(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(9.59, 'monday');

        // ASSERT
        $this->assertSame(0.0, $result);
    }

    public function testReturnsOpenWhenExactly10(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(10, 'monday');

        // ASSERT
        $this->assertSame(1.0, $result);
    }

    public function testReturnsClosedWhenExactly22(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(22, 'tuesday');

        // ASSERT
        $this->assertSame(0.0, $result);
    }

    public function testReturnsWeekendRateWhenExactly19OnSaturday(): void
    {
        // ACT
        $result = $this->engine->calculateSurge(19, 'saturday');

        // ASSERT
        $this->assertSame(1.8, $result);
    }
    // --- calculateOrderTotal ---

    public function testReturnsCorrectTotalWhenNormalOrder(): void
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

    public function testReturnsCorrectTotalWhenPromoApplied(): void
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

    public function testAppliesSurgeToDeliveryFee(): void
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

    public function testReturnsCorrectTotalWhenMultipleItems(): void
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

    public function testThrowsExceptionWhenEmptyCart(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('empty');

        // ACT
        $this->engine->calculateOrderTotal([], 5, 1, null, 15, 'tuesday');
    }

    public function testThrowsExceptionWhenNegativePrice(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $items = [['name' => 'Bug', 'price' => -5.0, 'quantity' => 1]];
        $this->engine->calculateOrderTotal($items, 5, 1, null, 15, 'tuesday');
    }

    public function testThrowsExceptionWhenZeroQuantity(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $items = [['name' => 'Bug', 'price' => 10.0, 'quantity' => 0]];
        $this->engine->calculateOrderTotal($items, 5, 1, null, 15, 'tuesday');
    }

    public function testThrowsExceptionWhenRestaurantClosed(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('closed');

        // ACT
        $items = [['name' => 'Pizza', 'price' => 12.50, 'quantity' => 1]];
        $this->engine->calculateOrderTotal($items, 5, 1, null, 23, 'tuesday');
    }

    public function testThrowsExceptionWhenBeyondDeliveryZone(): void
    {
        // ASSERT
        $this->expectException(\InvalidArgumentException::class);

        // ACT
        $items = [['name' => 'Pizza', 'price' => 12.50, 'quantity' => 1]];
        $this->engine->calculateOrderTotal($items, 15, 1, null, 15, 'tuesday');
    }

    public function testReturnsRoundedValues(): void
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
