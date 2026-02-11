<?php

// Validiert und normalisiert Projekt-Formulardaten inklusive Budget-Parsing.

declare(strict_types=1);

namespace App\Service;

final class ProjectService
{
    private const STATUS_VALUES = ['planned', 'active', 'completed'];

    /**
     * Validiert Project-Eingaben inklusive Budgetformat und normalisiert Persistenzdaten.
     * Die Methode erzwingt eine Status-Whitelist und bereitet Budget als DECIMAL-String auf,
     * passend zur DB-Spalte `projects.budget`.
     * Beispiel: `$result = $projectService->validate($_POST);` in `ProjectController::update()`.
     */
    public function validate(array $input): ValidationResult
    {
        $name = trim((string) ($input['name'] ?? ''));
        $clientName = trim((string) ($input['client_name'] ?? ''));
        $budgetInput = trim((string) ($input['budget'] ?? ''));
        $status = (string) ($input['status'] ?? 'planned');

        $errors = [];
        $budget = null;

        if ($name === '') {
            $errors[] = 'Projektname ist erforderlich.';
        } elseif (mb_strlen($name) > 150) {
            $errors[] = 'Projektname darf maximal 150 Zeichen haben.';
        }

        if ($clientName !== '' && mb_strlen($clientName) > 120) {
            $errors[] = 'Kundenname darf maximal 120 Zeichen haben.';
        }

        if ($budgetInput !== '') {
            if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $budgetInput)) {
                $errors[] = 'Budget muss numerisch sein (max. 2 Nachkommastellen).';
            } else {
                $budget = number_format((float) $budgetInput, 2, '.', '');
            }
        }

        if (!in_array($status, self::STATUS_VALUES, true)) {
            $errors[] = 'Ungültiger Status.';
        }

        return new ValidationResult(
            $errors === [],
            $errors,
            [
                'name' => $name,
                'client_name' => $clientName === '' ? null : $clientName,
                'budget' => $budget,
                'status' => $status,
                'budget_input' => $budgetInput,
            ]
        );
    }

    /**
     * Liefert erlaubte Projektstatuswerte fuer Select-Felder und Validierung.
     * Haelt UI-Optionen und Serverlogik konsistent.
     * Beispiel: `'statuses' => $projectService->statuses()` in `ProjectController::create()`.
     *
     * @return string[]
     */
    public function statuses(): array
    {
        return self::STATUS_VALUES;
    }
}
