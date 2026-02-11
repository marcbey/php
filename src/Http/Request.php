<?php

// Kapselt Request-Superglobals und whitelisted Entity/Action/ID fuer sicheres Routing.

declare(strict_types=1);

namespace App\Http;

final class Request
{
    private const ACTIONS = ['index', 'create', 'edit', 'store', 'update', 'delete'];

    /**
     * Konstruktor fuer ein kapseltes Request-Objekt auf Basis von Superglobals.
     * Erleichtert testbare Controller-Methoden ohne direkte globale Zugriffe.
     * Beispiel: intern genutzt in `Request::capture()`.
     */
    public function __construct(
        private array $get,
        private array $post,
        private array $server,
        private array $session
    ) {}

    /**
     * Erzeugt eine Request-Instanz aus `$_GET`, `$_POST`, `$_SERVER`, `$_SESSION`.
     * Zentraler Einstieg fuer Routing im Front Controller.
     * Beispiel: `$request = Request::capture();` in `/public/index.php`.
     */
    public static function capture(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_SESSION);
    }

    /**
     * Liefert die HTTP-Methode in Grossbuchstaben.
     * Unterstuetzt den Controller-Flow fuer GET/POST-Entscheidungen.
     * Beispiel: `if ($request->isPost()) { ... }`.
     */
    public function method(): string
    {
        return strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    /**
     * Liefert die angeforderte Action, aber nur aus einer sicheren Whitelist.
     * Ungueltige Werte fallen auf `index` zurueck, um unerwartete Methodenziele zu vermeiden.
     * Beispiel: `$action = $request->action();` im Router.
     */
    public function action(): string
    {
        $action = (string) ($this->get['action'] ?? 'index');
        if (!in_array($action, self::ACTIONS, true)) {
            return 'index';
        }

        return $action;
    }

    /**
     * Liefert die angeforderte Entitaet aus einer uebergebenen Whitelist.
     * Unbekannte Entitaeten werden auf die Default-Entitaet normalisiert.
     * Beispiel: `$entity = $request->entity(array_keys($controllers), 'tasks');`.
     *
     * @param list<string> $allowedEntities
     */
    public function entity(array $allowedEntities, string $defaultEntity): string
    {
        $entity = (string) ($this->get['entity'] ?? $defaultEntity);
        if (!in_array($entity, $allowedEntities, true)) {
            return $defaultEntity;
        }

        return $entity;
    }

    /**
     * Liest einen Eingabewert aus POST, sonst GET, sonst Default.
     * Hilft bei generischem Zugriff ohne direkte Superglobal-Verwendung.
     * Beispiel: `$request->input('title', '')` in custom Erweiterungen.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    /**
     * Liest `id` aus Requestdaten und validiert sie als positive Integer-ID.
     * Schuetzt Controller und Repository vor invalider Identifikator-Eingabe.
     * Beispiel: `$id = $request->id(); if ($id === null) { ... }`.
     */
    public function id(): ?int
    {
        $value = $this->get['id'] ?? $this->post['id'] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    }

    /**
     * Bequemer Check fuer POST-Requests.
     * Wird fuer CSRF/Origin-Pruefung und CRUD-Schreiboperationen genutzt.
     * Beispiel: `if ($request->isPost()) { Security::assertSameOrigin(...); }`.
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Liefert das in der Session abgelegte CSRF-Token.
     * Controller vergleichen dieses Token mit dem Form-Token bei state-changing Requests.
     * Beispiel: `<input name="csrf_token" value="<?= e($request->csrfToken()) ?>">`.
     */
    public function csrfToken(): string
    {
        return (string) ($this->session['csrf_token'] ?? '');
    }

    /**
     * Gibt die Server-Metadaten fuer Sicherheitspruefungen zurueck.
     * Hauptsaechlich fuer `Security::assertSameOrigin()` gedacht.
     * Beispiel: `Security::assertSameOrigin($request->server());`.
     */
    public function server(): array
    {
        return $this->server;
    }
}
