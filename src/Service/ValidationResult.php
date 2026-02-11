<?php

// Immutable validation DTO carrying normalized data plus user-facing error messages.

declare(strict_types=1);

namespace App\Service;

final class ValidationResult
{
    /** @param string[] $errors */
    public function __construct(
        public readonly bool $ok,
        public readonly array $errors,
        public readonly array $data
    ) {}
}
