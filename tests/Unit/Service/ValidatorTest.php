<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    // --- isValidEmail ---

    public function testReturnsTrueWhenValidEmail(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('user@example.com');

        // ASSERT
        $this->assertTrue($result);
    }

    public function testReturnsTrueWhenEmailWithTag(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('user.name+tag@domain.co');

        // ASSERT
        $this->assertTrue($result);
    }

    public function testReturnsFalseWhenNoAtSign(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('invalid');

        // ASSERT
        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenNoLocalPart(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('@domain.com');

        // ASSERT
        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenNoDomain(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('user@');

        // ASSERT
        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenEmptyEmail(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('');

        // ASSERT
        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenNullEmail(): void
    {
        // ACT
        $result = $this->validator->isValidEmail(null);

        // ASSERT
        $this->assertFalse($result);
    }
    // --- isValidPassword ---

    public function testReturnsValidWhenStrongPassword(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('Passw0rd!');

        // ASSERT
        $this->assertTrue($result->valid);
        $this->assertSame([], $result->errors);
    }

    public function testReturnsAllErrorsWhenShortPassword(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('short');

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertContains('Must be at least 8 characters', $result->errors);
        $this->assertContains('Must contain at least one uppercase letter', $result->errors);
        $this->assertContains('Must contain at least one digit', $result->errors);
        $this->assertContains('Must contain at least one special character (!@#$%^&*)', $result->errors);
    }

    public function testReturnsUppercaseErrorWhenNoUppercase(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('alllowercase1!');

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertContains('Must contain at least one uppercase letter', $result->errors);
    }

    public function testReturnsLowercaseErrorWhenNoLowercase(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('ALLUPPERCASE1!');

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertContains('Must contain at least one lowercase letter', $result->errors);
    }

    public function testReturnsDigitErrorWhenNoDigit(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('NoDigits!here');

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertContains('Must contain at least one digit', $result->errors);
    }

    public function testReturnsSpecialErrorWhenNoSpecialChar(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('NoSpecial1here');

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertContains('Must contain at least one special character (!@#$%^&*)', $result->errors);
    }

    public function testReturnsRequiredErrorWhenEmptyPassword(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('');

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertSame(['Password is required'], $result->errors);
    }

    public function testReturnsRequiredErrorWhenNullPassword(): void
    {
        // ACT
        $result = $this->validator->isValidPassword(null);

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertSame(['Password is required'], $result->errors);
    }
    // --- isValidAge ---

    public function testReturnsTrueWhenValidAge(): void
    {
        // ACT
        $result = $this->validator->isValidAge(25);

        // ASSERT
        $this->assertTrue($result);
    }

    public function testReturnsTrueWhenAgeIsZero(): void
    {
        // ACT
        $result = $this->validator->isValidAge(0);

        // ASSERT
        $this->assertTrue($result);
    }

    public function testReturnsTrueWhenAgeIsMax(): void
    {
        // ACT
        $result = $this->validator->isValidAge(150);

        // ASSERT
        $this->assertTrue($result);
    }

    public function testReturnsFalseWhenAgeIsNegative(): void
    {
        // ACT
        $result = $this->validator->isValidAge(-1);

        // ASSERT
        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenAgeExceedsMax(): void
    {
        // ACT
        $result = $this->validator->isValidAge(151);

        // ASSERT
        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenAgeIsFloat(): void
    {
        // ACT
        $result = $this->validator->isValidAge(25.5);

        // ASSERT
        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenAgeIsString(): void
    {
        // ACT
        $result = $this->validator->isValidAge('25');

        // ASSERT
        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenAgeIsNull(): void
    {
        // ACT
        $result = $this->validator->isValidAge(null);

        // ASSERT
        $this->assertFalse($result);
    }
}
