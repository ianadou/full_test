<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\MathHelper;
use PHPUnit\Framework\TestCase;

final class MathHelperTest extends TestCase
{
    private MathHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new MathHelper();
    }

    // --- calculateAverage ---

    public function test_returns_average_when_multiple_numbers(): void
    {
        // ACT
        $result = $this->helper->calculateAverage([10, 12, 14]);

        // ASSERT
        $this->assertSame(12.0, $result);
    }

    public function test_returns_number_when_single_element(): void
    {
        // ACT
        $result = $this->helper->calculateAverage([15]);

        // ASSERT
        $this->assertSame(15.0, $result);
    }

    public function test_returns_zero_when_empty_array(): void
    {
        // ACT
        $result = $this->helper->calculateAverage([]);

        // ASSERT
        $this->assertSame(0.0, $result);
    }

    public function test_returns_zero_when_null_array(): void
    {
        // ACT
        $result = $this->helper->calculateAverage(null);

        // ASSERT
        $this->assertSame(0.0, $result);
    }

    public function test_returns_rounded_average_when_decimals(): void
    {
        // ACT
        $result = $this->helper->calculateAverage([10, 11, 12]);

        // ASSERT
        $this->assertSame(11.0, $result);
    }

    // --- clamp ---

    public function test_returns_value_when_within_range(): void
    {
        // ACT
        $result = $this->helper->clamp(5, 0, 10);

        // ASSERT
        $this->assertSame(5.0, $result);
    }

    public function test_returns_min_when_value_below_range(): void
    {
        // ACT
        $result = $this->helper->clamp(-5, 0, 10);

        // ASSERT
        $this->assertSame(0.0, $result);
    }

    public function test_returns_max_when_value_above_range(): void
    {
        // ACT
        $result = $this->helper->clamp(15, 0, 10);

        // ASSERT
        $this->assertSame(10.0, $result);
    }

    public function test_returns_value_when_all_bounds_equal(): void
    {
        // ACT
        $result = $this->helper->clamp(0, 0, 0);

        // ASSERT
        $this->assertSame(0.0, $result);
    }
}
