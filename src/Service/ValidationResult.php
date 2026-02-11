<?php

// Unveraenderliches Validierungs-DTO mit normalisierten Daten und nutzerbezogenen Fehlermeldungen.

declare(strict_types=1);

namespace App\Service;

final class ValidationResult
{
    /**
     * Baut ein standardisiertes Ergebnisobjekt fuer Formularvalidierung in Tasks/Projects.
     * `ok` steuert den Controller-Flow, `errors` wird im Formular gerendert,
     * `data` enthaelt normalisierte Werte fuer Persistenz oder Re-Rendering.
     * Beispiel: `$result = $service->validate($_POST); if (!$result->ok) { ... }`.
     *
     * @param string[] $errors
     */
    public function __construct(
        public readonly bool $ok,
        public readonly array $errors,
        public readonly array $data
    ) {}
}
