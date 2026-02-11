<?php

// Steuert den Task-CRUD-Flow: validieren, CSRF erzwingen, Repository aufrufen, Views rendern.

declare(strict_types=1);

namespace App\Http;

use App\Repository\TaskRepository;
use App\Service\TaskService;
use App\View\View;

final class TaskController
{
    /**
     * Verdrahtet Task-Persistenz und Task-Validierungslogik fuer alle Task-Aktionen.
     * Beispiel: `new TaskController(new TaskRepository($pdo), new TaskService())` in `index.php`.
     */
    public function __construct(
        private TaskRepository $repository,
        private TaskService $service
    ) {}

    /**
     * Rendert die Task-Uebersicht mit allen Datensaetzen und optionaler Flash-Nachricht.
     * Beispiel: GET `/?entity=tasks` fuehrt in `index.php` zu `TaskController::index()`.
     */
    public function index(Request $request): string
    {
        $tasks = $this->repository->findAll();

        return View::render('tasks/list', [
            'tasks' => $tasks,
            'flash' => $this->pullFlash(),
            'csrf' => $request->csrfToken(),
        ]);
    }

    /**
     * Rendert das Formular fuer das Erstellen einer neuen Aufgabe mit Initialwerten.
     * Beispiel: GET `/?entity=tasks&action=create`.
     */
    public function create(Request $request): string
    {
        return View::render('tasks/form', [
            'mode' => 'create',
            'task' => null,
            'errors' => [],
            'input' => ['title' => '', 'description' => '', 'status' => 'todo'],
            'statuses' => $this->service->statuses(),
            'csrf' => $request->csrfToken(),
        ]);
    }

    /**
     * Rendert das Bearbeitungsformular fuer eine vorhandene Aufgabe.
     * Prueft vorher, ob die ID gueltig ist und der Datensatz existiert.
     * Beispiel: GET `/?entity=tasks&action=edit&id=3`.
     */
    public function edit(Request $request): string
    {
        $id = $request->id();
        if ($id === null) {
            $this->flash('Ungültige ID.');
            Response::redirect('/?entity=tasks');
        }

        $task = $this->repository->findById($id);
        if ($task === null) {
            $this->flash('Eintrag nicht gefunden.');
            Response::redirect('/?entity=tasks');
        }

        return View::render('tasks/form', [
            'mode' => 'edit',
            'task' => $task,
            'errors' => [],
            'input' => [
                'title' => $task->title(),
                'description' => $task->description() ?? '',
                'status' => $task->status(),
            ],
            'statuses' => $this->service->statuses(),
            'csrf' => $request->csrfToken(),
        ]);
    }

    /**
     * Erstellt eine neue Aufgabe nach CSRF- und Validierungspruefung.
     * Bei Fehlern wird das Formular inkl. Fehlermeldungen erneut gerendert.
     * Beispiel: POST `/?entity=tasks&action=store`.
     */
    public function store(Request $request): void
    {
        Security::assertCsrf($request);

        $result = $this->service->validate($_POST);
        if (!$result->ok) {
            $content = View::render('tasks/form', [
                'mode' => 'create',
                'task' => null,
                'errors' => $result->errors,
                'input' => $result->data,
                'statuses' => $this->service->statuses(),
                'csrf' => $request->csrfToken(),
            ]);
            echo View::render('layout', ['content' => $content, 'entity' => 'tasks']);
            exit;
        }

        $this->repository->create(
            $result->data['title'],
            $result->data['description'],
            $result->data['status']
        );

        $this->flash('Eintrag erstellt.');
        Response::redirect('/?entity=tasks');
    }

    /**
     * Aktualisiert eine bestehende Aufgabe nach CSRF-, ID- und Validierungspruefung.
     * Nutzt das immutable `withChanges()` des Task-Models fuer konsistente Datenuebergabe.
     * Beispiel: POST `/?entity=tasks&action=update&id=3`.
     */
    public function update(Request $request): void
    {
        Security::assertCsrf($request);

        $id = $request->id();
        if ($id === null) {
            $this->flash('Ungültige ID.');
            Response::redirect('/?entity=tasks');
        }

        $task = $this->repository->findById($id);
        if ($task === null) {
            $this->flash('Eintrag nicht gefunden.');
            Response::redirect('/?entity=tasks');
        }

        $result = $this->service->validate($_POST);
        if (!$result->ok) {
            $content = View::render('tasks/form', [
                'mode' => 'edit',
                'task' => $task,
                'errors' => $result->errors,
                'input' => $result->data,
                'statuses' => $this->service->statuses(),
                'csrf' => $request->csrfToken(),
            ]);
            echo View::render('layout', ['content' => $content, 'entity' => 'tasks']);
            exit;
        }

        $updated = $task->withChanges(
            $result->data['title'],
            $result->data['description'],
            $result->data['status']
        );

        $this->repository->update($updated);
        $this->flash('Eintrag aktualisiert.');
        Response::redirect('/?entity=tasks');
    }

    /**
     * Loescht eine Aufgabe nach CSRF- und ID-Pruefung.
     * Wird ueber Method-Override (`_method=DELETE`) aus der Listenansicht aufgerufen.
     * Beispiel: POST `/?entity=tasks&action=delete&id=3` mit `_method=DELETE`.
     */
    public function delete(Request $request): void
    {
        Security::assertCsrf($request);

        $id = $request->id();
        if ($id === null) {
            $this->flash('Ungültige ID.');
            Response::redirect('/?entity=tasks');
        }

        $this->repository->delete($id);
        $this->flash('Eintrag gelöscht.');
        Response::redirect('/?entity=tasks');
    }

    /**
     * Speichert eine einmalige Benutzer-Nachricht in der Session (Flash-Message).
     * Beispiel: `$this->flash('Eintrag erstellt.');` vor Redirect.
     */
    private function flash(string $message): void
    {
        $_SESSION['tasks_flash'] = $message;
    }

    /**
     * Holt und entfernt die Flash-Nachricht fuer die naechste Darstellung.
     * Beispiel: `'flash' => $this->pullFlash()` in `index()`.
     */
    private function pullFlash(): ?string
    {
        $message = $_SESSION['tasks_flash'] ?? null;
        unset($_SESSION['tasks_flash']);
        return is_string($message) ? $message : null;
    }
}
