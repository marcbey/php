<?php

// Stark typisierte Task-Entitaet mit Row-Hydration und immutable Update-Helpern.

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

final class Task
{
    /**
     * Erstellt ein Task-Domainobjekt aus bereits validierten Werten.
     * Wird direkt nach DB-Leseoperationen oder bei Create-Resultaten verwendet.
     * Beispiel: `new Task($id, $title, $description, $status, $now, $now)` im Repository.
     */
    public function __construct(
        private ?int $id,
        private string $title,
        private ?string $description,
        private string $status,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    /**
     * Hydriert ein Task-Objekt aus einer Datenbankzeile.
     * Kapselt das Mapping von SQL-Spaltennamen auf typsichere Objektfelder.
     * Beispiel: `Task::fromRow($row)` in `TaskRepository::findAll()`.
     */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['title'],
            $row['description'] !== null ? (string) $row['description'] : null,
            (string) $row['status'],
            new DateTimeImmutable((string) $row['created_at']),
            new DateTimeImmutable((string) $row['updated_at'])
        );
    }

    /**
     * Gibt die primäre Task-ID zurueck (oder null fuer noch nicht persistierte Objekte).
     * Beispiel: `$task->id()` beim Erzeugen von Edit/Delete-Links in der View.
     */
    public function id(): ?int
    {
        return $this->id;
    }

    /**
     * Gibt den Task-Titel zurueck.
     * Beispiel: `<?= e($task->title()) ?>` in `src/View/tasks/list.php`.
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Gibt die optionale Beschreibung zurueck.
     * Beispiel: `if ($task->description()) { ... }` beim Rendern der Liste.
     */
    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * Gibt den aktuellen Status (`todo`, `in_progress`, `done`) zurueck.
     * Beispiel: CSS-Badge-Klasse `badge-<?= e($task->status()) ?>`.
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * Gibt den Erstellzeitpunkt zurueck.
     * Beispiel: kann fuer Audit-Ansichten oder Sortierung genutzt werden.
     */
    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Gibt den letzten Aktualisierungszeitpunkt zurueck.
     * Beispiel: `<?= e($task->updatedAt()->format('d.m.Y H:i')) ?>` in der Listenansicht.
     */
    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Liefert eine neue Task-Instanz mit geaenderten fachlichen Werten.
     * Das bestehende Objekt bleibt unveraendert; `updatedAt` wird automatisch auf `now` gesetzt.
     * Beispiel: `$updated = $task->withChanges(...); $repository->update($updated);`.
     */
    public function withChanges(string $title, ?string $description, string $status): self
    {
        return new self(
            $this->id,
            $title,
            $description,
            $status,
            $this->createdAt,
            new DateTimeImmutable('now')
        );
    }
}
