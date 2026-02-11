<?php

declare(strict_types=1);

namespace App\Service;

final class TaskService
{
    private const STATUS_VALUES = ['todo', 'in_progress', 'done'];

    public function validate(array $input): ValidationResult
    {
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $status = (string) ($input['status'] ?? 'todo');

        $errors = [];

        if ($title === '') {
            $errors[] = 'Titel ist erforderlich.';
        } elseif (mb_strlen($title) > 150) {
            $errors[] = 'Titel darf maximal 150 Zeichen haben.';
        }

        if ($description !== '' && mb_strlen($description) > 1000) {
            $errors[] = 'Beschreibung darf maximal 1000 Zeichen haben.';
        }

        if (!in_array($status, self::STATUS_VALUES, true)) {
            $errors[] = 'Ungültiger Status.';
        }

        return new ValidationResult(
            $errors === [],
            $errors,
            [
                'title' => $title,
                'description' => $description === '' ? null : $description,
                'status' => $status,
            ]
        );
    }

    /** @return string[] */
    public function statuses(): array
    {
        return self::STATUS_VALUES;
    }
}
