<?php

// Stellt minimale HTTP-Response-Helper fuer Controller bereit (aktuell Redirects).

declare(strict_types=1);

namespace App\Http;

final class Response
{
    /**
     * Fuehrt eine HTTP-Weiterleitung aus und beendet den Request sofort.
     * Wird in Controllern nach erfolgreichem Create/Update/Delete genutzt,
     * um Post/Redirect/Get umzusetzen.
     * Beispiel: `Response::redirect('/?entity=tasks');` in `TaskController::store()`.
     */
    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
