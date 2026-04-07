<?php

declare(strict_types=1);

namespace App\Service;

final class PasswordValidationResult
{
    /**
     * @param string[] $errors
     */
    public function __construct(
        public readonly bool $valid,
        public readonly array $errors,
    ) {
    }
}
