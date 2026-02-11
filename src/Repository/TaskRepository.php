<?php

// Kapselt Task-Persistenzabfragen mit PDO Prepared Statements.

declare(strict_types=1);

namespace App\Repository;

use App\Model\Task;
use DateTimeImmutable;
use PDO;

final class TaskRepository
{
    /**
     * Konstruiert das Repository mit einer zentral konfigurierten PDO-Instanz.
     * Beispiel: `new TaskRepository(Database::pdo())` in `public/index.php`.
     */
    public function __construct(private PDO $pdo) {}

    /**
     * Liefert alle Tasks in absteigender Aktualisierungsreihenfolge.
     * Mapping auf Domainobjekte erfolgt via `Task::fromRow()`.
     * Beispiel: `$tasks = $taskRepository->findAll();` in `TaskController::index()`.
     *
     * @return Task[]
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM tasks ORDER BY updated_at DESC');
        $rows = $stmt->fetchAll();

        return array_map(static fn (array $row): Task => Task::fromRow($row), $rows);
    }

    /**
     * Sucht genau eine Task anhand ihrer ID mit Prepared Statement.
     * Beispiel: `$task = $taskRepository->findById($id);` in Edit/Update-Flows.
     */
    public function findById(int $id): ?Task
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Task::fromRow($row) : null;
    }

    /**
     * Persistiert eine neue Task und gibt das erzeugte Domainobjekt zurueck.
     * Nutzt serverseitige Zeitstempel fuer konsistente Audit-Werte.
     * Beispiel: `$taskRepository->create($title, $description, $status);` in `store()`.
     */
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

    /**
     * Aktualisiert eine bestehende Task mit den Werten des uebergebenen Domainobjekts.
     * Beispiel: `$taskRepository->update($updatedTask);` nach `withChanges()` im Controller.
     */
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

    /**
     * Loescht eine Task per ID mit Prepared Statement.
     * Beispiel: `$taskRepository->delete($id);` in `TaskController::delete()`.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
