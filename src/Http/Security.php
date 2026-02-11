<?php

// Wendet Session-Haertung, Security-Header und Same-Origin-Pruefungen fuer schreibende Requests an.

declare(strict_types=1);

namespace App\Http;

final class Security
{
    /**
     * Initialisiert eine gehaertete Session-Konfiguration inkl. CSRF-Token.
     * Setzt sichere Cookie-Flags und regeneriert die Session-ID einmalig.
     * Beispiel: `Security::bootSession();` direkt nach `Env::load(...)` im Front Controller.
     */
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

    /**
     * Sendet Sicherheitsheader gegen typische Browser-Angriffsvektoren.
     * Enthalten sind CSP, Clickjacking-Schutz und MIME-Sniffing-Schutz.
     * Beispiel: `Security::applyHeaders();` einmal pro Request in `/public/index.php`.
     */
    public static function applyHeaders(): void
    {
        // Verhindert MIME-Sniffing:
        // Browser duerfen Dateitypen nicht "erraten". So wird z. B. eine als Text gelieferte
        // Antwort nicht als Script ausgefuehrt (Schutz gegen Content-Type-Verwirrung/XSS-Pfade).
        header('X-Content-Type-Options: nosniff');
        
        // Reduziert Referrer-Datenlecks:
        // Der Referer wird nur bei Requests zur gleichen Origin gesendet. Externe Ziele erhalten
        // keine internen Pfade/Query-Parameter (z. B. IDs, Tokens in URLs).
        header('Referrer-Policy: same-origin');
        
        // Schuetzt vor Clickjacking:
        // Die Seite darf gar nicht in iframes eingebettet werden (DENY), damit Angreifer
        // keine unsichtbaren Overlays fuer Fehlklicks auf sicherheitsrelevante Buttons bauen.
        header('X-Frame-Options: DENY');
        
        // Content Security Policy (CSP) als zweite Verteidigungslinie gegen XSS/Injection:
        // - default-src 'self': Ressourcen nur von eigener Origin
        // - form-action 'self': Formulare duerfen nur an eigene Origin senden
        // - frame-ancestors 'none': zusaetzlicher Frame/Clickjacking-Schutz via CSP
        // - base-uri 'self': verhindert Missbrauch eines manipulierten <base>-Tags
        // - style-src 'self' 'unsafe-inline': erlaubt eigene Styles + Inline-CSS (Trade-off)
        header("Content-Security-Policy: default-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'; style-src 'self' 'unsafe-inline'");
    }

    /**
     * Prueft bei POST-Requests, ob Origin/Referer zum eigenen Host passen.
     * Verstaerkt den CSRF-Schutz um eine Same-Origin-Validierung auf HTTP-Ebene.
     * Beispiel: `Security::assertSameOrigin($request->server());` vor Controller-Dispatch.
     */
    public static function assertSameOrigin(array $server): void
    {
        $host = (string) ($_ENV['HOST']);
        $origin = (string) ($server['HTTP_ORIGIN'] ?? '');
        $referer = (string) ($server['HTTP_REFERER'] ?? '');

        $originHost = self::extractHost($origin);
        $refererHost = self::extractHost($referer);

        // Wenn ein Origin-Header vorhanden ist, muss er exakt dem erwarteten Host entsprechen.
        if ($originHost !== null && !hash_equals($host, $originHost)) {
            http_response_code(403);
            echo 'Ungültige Request-Origin.';
            exit;
        }

        // Wenn ein Referer-Header vorhanden ist, muss auch dieser zur gleichen Origin gehören.
        if ($refererHost !== null && !hash_equals($host, $refererHost)) {
            http_response_code(403);
            echo 'Ungültige Request-Origin.';
            exit;
        }
    }

    /**
     * Extrahiert Host (optional inkl. Port) aus Origin/Referer-URL.
     * Isoliert den Vergleichswert fuer `assertSameOrigin()`.
     * Beispiel: `self::extractHost('https://example.com:8443/path')` ergibt `example.com:8443`.
     */
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
