<?php

// Laedt .env-Key/Value-Paare in Umgebungsvariablen fuer lokale Konfiguration.

declare(strict_types=1);

namespace App\Config;

final class Env
{
    /**
     * Laedt die .env-Datei fuer lokale Laufzeit-Konfiguration in `$_ENV` und `putenv`.
     * Ueberschreibt nur die gelesenen Keys und ignoriert Kommentare/Leerzeilen.
     * Beispiel: `Env::load(__DIR__ . '/../.env');` im Front Controller.
     */
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            $value = self::stripQuotes($value);
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }

    /**
     * Entfernt umschliessende einfache oder doppelte Anfuehrungszeichen aus .env-Werten.
     * So werden Werte wie `"secret"` korrekt als `secret` verarbeitet.
     * Beispiel: `DB_PASS="my-pass"` wird in `DB_PASS=my-pass` transformiert.
     */
    private static function stripQuotes(string $value): string
    {
        if ((str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
