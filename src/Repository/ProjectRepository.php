<?php

// Kapselt Projekt-Persistenzabfragen mit PDO Prepared Statements.

declare(strict_types=1);

namespace App\Repository;

use App\Model\Project;
use DateTimeImmutable;
use PDO;

final class ProjectRepository
{
    /**
     * Konstruiert das Projekt-Repository mit zentraler PDO-Verbindung.
     * Beispiel: `new ProjectRepository(Database::pdo())` im Bootstrapping.
     */
    public function __construct(private PDO $pdo) {}

    /**
     * Liefert alle Projekte sortiert nach letzter Aktualisierung (neueste zuerst).
     * Beispiel: `$projects = $projectRepository->findAll();` in `ProjectController::index()`.
     *
     * @return Project[]
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM projects ORDER BY updated_at DESC');
        $rows = $stmt->fetchAll();

        return array_map(static fn (array $row): Project => Project::fromRow($row), $rows);
    }

    /**
     * Sucht ein einzelnes Projekt per ID mit Prepared Statement.
     * Beispiel: `$project = $projectRepository->findById($id);` in Edit-Flow.
     */
    public function findById(int $id): ?Project
    {
        $stmt = $this->pdo->prepare('SELECT * FROM projects WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Project::fromRow($row) : null;
    }

    /**
     * Persistiert ein neues Projekt und gibt das erzeugte Domainobjekt zurueck.
     * Budget wird als vorberechneter DECIMAL-String aus dem Service uebernommen.
     * Beispiel: `$projectRepository->create($name, $client, $budget, $status);`.
     */
    public function create(string $name, ?string $clientName, ?string $budget, string $status): Project
    {
        $now = new DateTimeImmutable('now');

        $stmt = $this->pdo->prepare(
            'INSERT INTO projects (name, client_name, budget, status, created_at, updated_at)
             VALUES (:name, :client_name, :budget, :status, :created_at, :updated_at)'
        );

        $stmt->execute([
            'name' => $name,
            'client_name' => $clientName,
            'budget' => $budget,
            'status' => $status,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ]);

        return new Project((int) $this->pdo->lastInsertId(), $name, $clientName, $budget, $status, $now, $now);
    }

    /**
     * Aktualisiert ein bestehendes Projekt mit den Werten des Domainobjekts.
     * Beispiel: `$projectRepository->update($updatedProject);` in `ProjectController::update()`.
     */
    public function update(Project $project): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE projects
             SET name = :name, client_name = :client_name, budget = :budget, status = :status, updated_at = :updated_at
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $project->id(),
            'name' => $project->name(),
            'client_name' => $project->clientName(),
            'budget' => $project->budget(),
            'status' => $project->status(),
            'updated_at' => $project->updatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Loescht ein Projekt per ID mit Prepared Statement.
     * Beispiel: `$projectRepository->delete($id);` in `ProjectController::delete()`.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM projects WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
