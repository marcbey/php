<?php

// Handles task CRUD flow: validate input, enforce CSRF, call repository, render views.

declare(strict_types=1);

namespace App\Http;

use App\Repository\TaskRepository;
use App\Service\TaskService;
use App\View\View;

final class TaskController
{
    public function __construct(
        private TaskRepository $repository,
        private TaskService $service
    ) {}

    public function index(Request $request): string
    {
        $tasks = $this->repository->findAll();

        return View::render('tasks/list', [
            'tasks' => $tasks,
            'flash' => $this->pullFlash(),
            'csrf' => $request->csrfToken(),
        ]);
    }

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

    public function store(Request $request): void
    {
        $this->assertCsrf($request);

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

    public function update(Request $request): void
    {
        $this->assertCsrf($request);

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

    public function delete(Request $request): void
    {
        $this->assertCsrf($request);

        $id = $request->id();
        if ($id === null) {
            $this->flash('Ungültige ID.');
            Response::redirect('/?entity=tasks');
        }

        $this->repository->delete($id);
        $this->flash('Eintrag gelöscht.');
        Response::redirect('/?entity=tasks');
    }

    private function assertCsrf(Request $request): void
    {
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (!hash_equals($request->csrfToken(), $token)) {
            http_response_code(419);
            echo 'Ungültiges CSRF-Token.';
            exit;
        }
    }

    private function flash(string $message): void
    {
        $_SESSION['tasks_flash'] = $message;
    }

    private function pullFlash(): ?string
    {
        $message = $_SESSION['tasks_flash'] ?? null;
        unset($_SESSION['tasks_flash']);
        return is_string($message) ? $message : null;
    }
}
