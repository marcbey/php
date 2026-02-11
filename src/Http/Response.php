<?php

// Provides minimal HTTP response helpers used by controllers (currently redirects).

declare(strict_types=1);

namespace App\Http;

final class Response
{
    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
