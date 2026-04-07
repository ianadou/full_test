<?php

declare(strict_types=1);

namespace App\Service;

final class Validator
{
    public function isValidEmail(?string $email): bool
    {
        if (null === $email || '' === $email) {
            return false;
        }

        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function isValidPassword(?string $password): PasswordValidationResult
    {
        $errors = [];

        if (null === $password || '' === $password) {
            return new PasswordValidationResult(false, ['Password is required']);
        }

        if (strlen($password) < 8) {
            $errors[] = 'Must be at least 8 characters';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Must contain at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Must contain at least one lowercase letter';
        }

        if (!preg_match('/\d/', $password)) {
            $errors[] = 'Must contain at least one digit';
        }

        if (!preg_match('/[!@#$%^&*]/', $password)) {
            $errors[] = 'Must contain at least one special character (!@#$%^&*)';
        }

        return new PasswordValidationResult([] === $errors, $errors);
    }

    public function isValidAge(mixed $age): bool
    {
        if (!is_int($age)) {
            return false;
        }

        return $age >= 0 && $age <= 150;
    }
}
