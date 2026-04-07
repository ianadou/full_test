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

    public function testReturnsAverageWhenMultipleNumbers(): void
    {
        // ACT
        $result = $this->helper->calculateAverage([10, 12, 14]);

        // ASSERT
        $this->assertSame(12.0, $result);
    }

    public function testReturnsNumberWhenSingleElement(): void
    {
        // ACT
        $result = $this->helper->calculateAverage([15]);

        // ASSERT
        $this->assertSame(15.0, $result);
    }

    public function testReturnsZeroWhenEmptyArray(): void
    {
        // ACT
        $result = $this->helper->calculateAverage([]);

        // ASSERT
        $this->assertSame(0.0, $result);
    }

    public function testReturnsZeroWhenNullArray(): void
    {
        // ACT
        $result = $this->helper->calculateAverage(null);

        // ASSERT
        $this->assertSame(0.0, $result);
    }

    public function testReturnsRoundedAverageWhenDecimals(): void
    {
        // ACT
        $result = $this->helper->calculateAverage([10, 11, 12]);

        // ASSERT
        $this->assertSame(11.0, $result);
    }

    // --- clamp ---

    public function testReturnsValueWhenWithinRange(): void
    {
        // ACT
        $result = $this->helper->clamp(5, 0, 10);

        // ASSERT
        $this->assertSame(5.0, $result);
    }

    public function testReturnsMinWhenValueBelowRange(): void
    {
        // ACT
        $result = $this->helper->clamp(-5, 0, 10);

        // ASSERT
        $this->assertSame(0.0, $result);
    }

    public function testReturnsMaxWhenValueAboveRange(): void
    {
        // ACT
        $result = $this->helper->clamp(15, 0, 10);

        // ASSERT
        $this->assertSame(10.0, $result);
    }

    public function testReturnsValueWhenAllBoundsEqual(): void
    {
        // ACT
        $result = $this->helper->clamp(0, 0, 0);

        // ASSERT
        $this->assertSame(0.0, $result);
    }
}
