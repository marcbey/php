<?php

// Applies session hardening, security headers, and same-origin checks for state-changing requests.

declare(strict_types=1);

namespace App\Http;

final class Security
{
    public static function bootSession(): void
    {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function applyHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: same-origin');
        header('X-Frame-Options: DENY');
        header("Content-Security-Policy: default-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; style-src 'self' 'unsafe-inline'");
    }

    public static function assertSameOrigin(array $server): void
    {
        $host = (string) ($server['HTTP_HOST'] ?? '');
        if ($host === '') {
            return;
        }

        $origin = (string) ($server['HTTP_ORIGIN'] ?? '');
        $referer = (string) ($server['HTTP_REFERER'] ?? '');

        $originHost = self::extractHost($origin);
        $refererHost = self::extractHost($referer);

        if (($originHost !== null && !hash_equals($host, $originHost))
            || ($refererHost !== null && !hash_equals($host, $refererHost))) {
            http_response_code(403);
            echo 'Ungültige Request-Origin.';
            exit;
        }
    }

    private static function extractHost(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host)) {
            return null;
        }

        $port = parse_url($url, PHP_URL_PORT);
        if (is_int($port)) {
            return $host . ':' . $port;
        }

        return $host;
    }
}
