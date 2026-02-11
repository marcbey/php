<?php

// File purpose: Task module in the src layer for the CRUD demo app.

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

final class Task
{
    public function __construct(
        private ?int $id,
        private string $title,
        private ?string $description,
        private string $status,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

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

    public function id(): ?int { return $this->id; }
    public function title(): string { return $this->title; }
    public function description(): ?string { return $this->description; }
    public function status(): string { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }

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
