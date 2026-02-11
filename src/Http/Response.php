<?php

// File purpose: Response module in the src layer for the CRUD demo app.

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
