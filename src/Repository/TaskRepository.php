<?php

// Encapsulates task persistence queries using PDO prepared statements.

declare(strict_types=1);

namespace App\Repository;

use App\Model\Task;
use DateTimeImmutable;
use PDO;

final class TaskRepository
{
    public function __construct(private PDO $pdo) {}

    /** @return Task[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM tasks ORDER BY updated_at DESC');
        $rows = $stmt->fetchAll();

        return array_map(static fn (array $row): Task => Task::fromRow($row), $rows);
    }

    public function findById(int $id): ?Task
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Task::fromRow($row) : null;
    }

    public function create(string $title, ?string $description, string $status): Task
    {
        $now = new DateTimeImmutable('now');

        $stmt = $this->pdo->prepare(
            'INSERT INTO tasks (title, description, status, created_at, updated_at)
             VALUES (:title, :description, :status, :created_at, :updated_at)'
        );

        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return new Task($id, $title, $description, $status, $now, $now);
    }

    public function update(Task $task): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE tasks
             SET title = :title, description = :description, status = :status, updated_at = :updated_at
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $task->id(),
            'title' => $task->title(),
            'description' => $task->description(),
            'status' => $task->status(),
            'updated_at' => $task->updatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
