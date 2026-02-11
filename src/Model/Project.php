<?php

// File purpose: Project module in the src layer for the CRUD demo app.

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

final class Project
{
    public function __construct(
        private ?int $id,
        private string $name,
        private ?string $clientName,
        private ?string $budget,
        private string $status,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

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

    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function clientName(): ?string { return $this->clientName; }
    public function budget(): ?string { return $this->budget; }
    public function status(): string { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }

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
