<?php

// Wraps request superglobals and whitelists entity/action/id inputs for safe routing.

declare(strict_types=1);

namespace App\Http;

final class Request
{
    private const ENTITIES = ['tasks', 'projects'];
    private const ACTIONS = ['index', 'create', 'edit', 'store', 'update', 'delete'];

    public function __construct(
        private array $get,
        private array $post,
        private array $server,
        private array $session
    ) {}

    public static function capture(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_SESSION);
    }

    public function method(): string
    {
        return strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function action(): string
    {
        $action = (string) ($this->get['action'] ?? 'index');
        if (!in_array($action, self::ACTIONS, true)) {
            return 'index';
        }

        return $action;
    }

    public function entity(): string
    {
        $entity = (string) ($this->get['entity'] ?? 'tasks');
        if (!in_array($entity, self::ENTITIES, true)) {
            return 'tasks';
        }

        return $entity;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function id(): ?int
    {
        $value = $this->get['id'] ?? $this->post['id'] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function csrfToken(): string
    {
        return (string) ($this->session['csrf_token'] ?? '');
    }

    public function server(): array
    {
        return $this->server;
    }
}
