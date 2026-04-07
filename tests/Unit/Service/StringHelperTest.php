<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\StringHelper;
use PHPUnit\Framework\TestCase;

final class StringHelperTest extends TestCase
{
    private StringHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new StringHelper();
    }

    public function testReturnsCapitalizedStringWhenAllLowercase(): void
    {
        // Arrange
        $input = 'hello';

        // Act
        $result = $this->helper->capitalize($input);

        // Assert
        $this->assertSame('Hello', $result);
    }

    public function testReturnsCapitalizedStringWhenAllUppercase(): void
    {
        // Arrange
        $input = 'WORLD';

        // Act
        $result = $this->helper->capitalize($input);

        // Assert
        $this->assertSame('World', $result);
    }

    public function testReturnsCapitalizedStringWhenNullInput(): void
    {
        // Arrange
        $input = null;

        // Act
        $result = $this->helper->capitalize($input);

        // Assert
        $this->assertSame('', $result);
    }

    public function testReturnsEmptyStringWhenEmptyInput(): void
    {
        // ACT
        $result = $this->helper->capitalize('');

        // ASSERT
        $this->assertSame('', $result);
    }

    public function testReturnsSlugWhenSimpleString(): void
    {
        // ACT
        $result = $this->helper->slugify('Hello World');

        // ASSERT
        $this->assertSame('hello-world', $result);
    }

    public function testReturnsTrimmedSlugWhenSpacesAround(): void
    {
        // ACT
        $result = $this->helper->slugify(' Spaces Everywhere ');

        // ASSERT
        $this->assertSame('spaces-everywhere', $result);
    }

    public function testReturnsSlugWithoutSpecialChars(): void
    {
        // ACT
        $result = $this->helper->slugify("C'est l'ete !");

        // ASSERT
        $this->assertSame('cest-lete', $result);
    }

    public function testReturnsEmptyStringWhenSlugifyEmptyInput(): void
    {
        // ACT
        $result = $this->helper->slugify('');

        // ASSERT
        $this->assertSame('', $result);
    }
}
