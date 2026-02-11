<?php

// Stark typisierte Projekt-Entitaet mit Row-Hydration und immutable Update-Helpern.

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

final class Project
{
    /**
     * Erstellt ein Project-Domainobjekt aus validierten Daten.
     * Wird von Repository-Methoden fuer neue und geladene Datensaetze verwendet.
     * Beispiel: `new Project($id, $name, $client, $budget, $status, $now, $now)`.
     */
    public function __construct(
        private ?int $id,
        private string $name,
        private ?string $clientName,
        private ?string $budget,
        private string $status,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    /**
     * Hydriert ein Project-Objekt aus einer DB-Zeile.
     * Konvertiert Datumsfelder in `DateTimeImmutable` und bewahrt Typkonsistenz.
     * Beispiel: `Project::fromRow($row)` in `ProjectRepository::findById()`.
     */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['name'],
            $row['client_name'] !== null ? (string) $row['client_name'] : null,
            $row['budget'] !== null ? (string) $row['budget'] : null,
            (string) $row['status'],
            new DateTimeImmutable((string) $row['created_at']),
            new DateTimeImmutable((string) $row['updated_at'])
        );
    }

    /**
     * Gibt die Projekt-ID zurueck.
     * Beispiel: `$project->id()` fuer Edit/Delete-URL-Parameter.
     */
    public function id(): ?int
    {
        return $this->id;
    }

    /**
     * Gibt den Projektnamen zurueck.
     * Beispiel: `<?= e($project->name()) ?>` in der Projektliste.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Gibt den optionalen Kundennamen zurueck.
     * Beispiel: Fallback auf `-` in der View, wenn `null`.
     */
    public function clientName(): ?string
    {
        return $this->clientName;
    }

    /**
     * Gibt das optionale Budget als normalisierten Decimal-String zurueck.
     * Beispiel: `<?= e($project->budget() . ' EUR') ?>` in `projects/list.php`.
     */
    public function budget(): ?string
    {
        return $this->budget;
    }

    /**
     * Gibt den Projektstatus (`planned`, `active`, `completed`) zurueck.
     * Beispiel: Badge-Styling ueber `badge-<?= e($project->status()) ?>`.
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * Gibt den Erstellzeitpunkt des Projekts zurueck.
     * Beispiel: kann fuer Timeline- oder Audit-Features genutzt werden.
     */
    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Gibt den letzten Aenderungszeitpunkt des Projekts zurueck.
     * Beispiel: Anzeige in der Tabellen-Spalte "Aktualisiert".
     */
    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Erzeugt eine neue Project-Instanz mit geaenderten Werten.
     * Das Originalobjekt bleibt unveraendert; `updatedAt` wird neu gesetzt.
     * Beispiel: `$updated = $project->withChanges(...);` vor `repository->update($updated)`.
     */
    public function withChanges(string $name, ?string $clientName, ?string $budget, string $status): self
    {
        return new self(
            $this->id,
            $name,
            $clientName,
            $budget,
            $status,
            $this->createdAt,
            new DateTimeImmutable('now')
        );
    }
}
