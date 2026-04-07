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

    public function test_returns_true_when_valid_email(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('user@example.com');

        // ASSERT
        $this->assertTrue($result);
    }

    public function test_returns_true_when_email_with_tag(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('user.name+tag@domain.co');

        // ASSERT
        $this->assertTrue($result);
    }

    public function test_returns_false_when_no_at_sign(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('invalid');

        // ASSERT
        $this->assertFalse($result);
    }

    public function test_returns_false_when_no_local_part(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('@domain.com');

        // ASSERT
        $this->assertFalse($result);
    }

    public function test_returns_false_when_no_domain(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('user@');

        // ASSERT
        $this->assertFalse($result);
    }

    public function test_returns_false_when_empty_email(): void
    {
        // ACT
        $result = $this->validator->isValidEmail('');

        // ASSERT
        $this->assertFalse($result);
    }

    public function test_returns_false_when_null_email(): void
    {
        // ACT
        $result = $this->validator->isValidEmail(null);

        // ASSERT
        $this->assertFalse($result);
    }
        // --- isValidPassword ---

    public function test_returns_valid_when_strong_password(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('Passw0rd!');

        // ASSERT
        $this->assertTrue($result->valid);
        $this->assertSame([], $result->errors);
    }

    public function test_returns_all_errors_when_short_password(): void
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

    public function test_returns_uppercase_error_when_no_uppercase(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('alllowercase1!');

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertContains('Must contain at least one uppercase letter', $result->errors);
    }

    public function test_returns_lowercase_error_when_no_lowercase(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('ALLUPPERCASE1!');

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertContains('Must contain at least one lowercase letter', $result->errors);
    }

    public function test_returns_digit_error_when_no_digit(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('NoDigits!here');

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertContains('Must contain at least one digit', $result->errors);
    }

    public function test_returns_special_error_when_no_special_char(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('NoSpecial1here');

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertContains('Must contain at least one special character (!@#$%^&*)', $result->errors);
    }

    public function test_returns_required_error_when_empty_password(): void
    {
        // ACT
        $result = $this->validator->isValidPassword('');

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertSame(['Password is required'], $result->errors);
    }

    public function test_returns_required_error_when_null_password(): void
    {
        // ACT
        $result = $this->validator->isValidPassword(null);

        // ASSERT
        $this->assertFalse($result->valid);
        $this->assertSame(['Password is required'], $result->errors);
    }
        // --- isValidAge ---

    public function test_returns_true_when_valid_age(): void
    {
        // ACT
        $result = $this->validator->isValidAge(25);

        // ASSERT
        $this->assertTrue($result);
    }

    public function test_returns_true_when_age_is_zero(): void
    {
        // ACT
        $result = $this->validator->isValidAge(0);

        // ASSERT
        $this->assertTrue($result);
    }

    public function test_returns_true_when_age_is_max(): void
    {
        // ACT
        $result = $this->validator->isValidAge(150);

        // ASSERT
        $this->assertTrue($result);
    }

    public function test_returns_false_when_age_is_negative(): void
    {
        // ACT
        $result = $this->validator->isValidAge(-1);

        // ASSERT
        $this->assertFalse($result);
    }

    public function test_returns_false_when_age_exceeds_max(): void
    {
        // ACT
        $result = $this->validator->isValidAge(151);

        // ASSERT
        $this->assertFalse($result);
    }

    public function test_returns_false_when_age_is_float(): void
    {
        // ACT
        $result = $this->validator->isValidAge(25.5);

        // ASSERT
        $this->assertFalse($result);
    }

    public function test_returns_false_when_age_is_string(): void
    {
        // ACT
        $result = $this->validator->isValidAge('25');

        // ASSERT
        $this->assertFalse($result);
    }

    public function test_returns_false_when_age_is_null(): void
    {
        // ACT
        $result = $this->validator->isValidAge(null);

        // ASSERT
        $this->assertFalse($result);
    }


}
