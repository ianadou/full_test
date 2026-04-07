<?php

declare(strict_types=1);

namespace App\Service;

final class Validator
{
    public function isValidEmail(?string $email): bool
    {
        if ($email === null || $email === '') {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isValidPassword(?string $password): PasswordValidationResult
    {
        $errors = [];

        if ($password === null || $password === '') {
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

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Must contain at least one digit';
        }

        if (!preg_match('/[!@#$%^&*]/', $password)) {
            $errors[] = 'Must contain at least one special character (!@#$%^&*)';
        }

        return new PasswordValidationResult($errors === [], $errors);
    }

    public function isValidAge(mixed $age): bool
    {
        if (!is_int($age)) {
            return false;
        }

        return $age >= 0 && $age <= 150;
    }
}
