<?php
declare(strict_types= 1);

namespace App\Tests\Unit\Service;
use App\Service\StringHelper;
use PHPUnit\Framework\TestCase;

final class StringHelperTest extends TestCase {
    private StringHelper $helper;
    protected function setUp(): void {
        $this->helper = new StringHelper();
    }
    public function test_returns_capitalized_string_when_all_lowercase(): void {
        // Arrange
        $input = 'hello';

        // Act
        $result = $this->helper->capitalize($input);

        // Assert
        $this->assertSame('Hello', $result);
    }

    public function test_returns_capitalized_string_when_all_uppercase(): void {
        // Arrange
        $input = 'WORLD';

        // Act
        $result = $this->helper->capitalize($input);

        // Assert
        $this->assertSame('World', $result);
    }

    public function test_returns_capitalized_string_when_null_input(): void {
        // Arrange
        $input = null;

        // Act
        $result = $this->helper->capitalize($input);

        // Assert
        $this->assertSame('', $result);
    }
        public function test_returns_empty_string_when_empty_input(): void
    {
        // ACT
        $result = $this->helper->capitalize('');

        // ASSERT
        $this->assertSame('', $result);
    }

        public function test_returns_slug_when_simple_string(): void
    {
        // ACT
        $result = $this->helper->slugify('Hello World');

        // ASSERT
        $this->assertSame('hello-world', $result);
    }

    public function test_returns_trimmed_slug_when_spaces_around(): void
    {
        // ACT
        $result = $this->helper->slugify(' Spaces Everywhere ');

        // ASSERT
        $this->assertSame('spaces-everywhere', $result);
    }

    public function test_returns_slug_without_special_chars(): void
    {
        // ACT
        $result = $this->helper->slugify("C'est l'ete !");

        // ASSERT
        $this->assertSame('cest-lete', $result);
    }

    public function test_returns_empty_string_when_slugify_empty_input(): void
    {
        // ACT
        $result = $this->helper->slugify('');

        // ASSERT
        $this->assertSame('', $result);
    }


}