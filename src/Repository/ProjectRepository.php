<?php

// File purpose: ProjectRepository module in the src layer for the CRUD demo app.

declare(strict_types=1);

namespace App\Repository;

use App\Model\Project;
use DateTimeImmutable;
use PDO;

final class ProjectRepository
{
    public function __construct(private PDO $pdo) {}

    /** @return Project[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM projects ORDER BY updated_at DESC');
        $rows = $stmt->fetchAll();

        return array_map(static fn (array $row): Project => Project::fromRow($row), $rows);
    }

    public function findById(int $id): ?Project
    {
        $stmt = $this->pdo->prepare('SELECT * FROM projects WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Project::fromRow($row) : null;
    }

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

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM projects WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
