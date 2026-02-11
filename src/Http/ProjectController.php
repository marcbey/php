<?php

// Handles project CRUD flow: validate input, enforce CSRF, call repository, render views.

declare(strict_types=1);

namespace App\Http;

use App\Repository\ProjectRepository;
use App\Service\ProjectService;
use App\View\View;

final class ProjectController
{
    public function __construct(
        private ProjectRepository $repository,
        private ProjectService $service
    ) {}

    public function index(Request $request): string
    {
        $projects = $this->repository->findAll();

        return View::render('projects/list', [
            'projects' => $projects,
            'flash' => $this->pullFlash(),
            'csrf' => $request->csrfToken(),
        ]);
    }

    public function create(Request $request): string
    {
        return View::render('projects/form', [
            'mode' => 'create',
            'project' => null,
            'errors' => [],
            'input' => [
                'name' => '',
                'client_name' => '',
                'budget_input' => '',
                'status' => 'planned',
            ],
            'statuses' => $this->service->statuses(),
            'csrf' => $request->csrfToken(),
        ]);
    }

    public function edit(Request $request): string
    {
        $id = $request->id();
        if ($id === null) {
            $this->flash('Ungültige ID.');
            Response::redirect('/?entity=projects');
        }

        $project = $this->repository->findById($id);
        if ($project === null) {
            $this->flash('Eintrag nicht gefunden.');
            Response::redirect('/?entity=projects');
        }

        return View::render('projects/form', [
            'mode' => 'edit',
            'project' => $project,
            'errors' => [],
            'input' => [
                'name' => $project->name(),
                'client_name' => $project->clientName() ?? '',
                'budget_input' => $project->budget() ?? '',
                'status' => $project->status(),
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
            $content = View::render('projects/form', [
                'mode' => 'create',
                'project' => null,
                'errors' => $result->errors,
                'input' => $result->data,
                'statuses' => $this->service->statuses(),
                'csrf' => $request->csrfToken(),
            ]);
            echo View::render('layout', ['content' => $content, 'entity' => 'projects']);
            exit;
        }

        $this->repository->create(
            $result->data['name'],
            $result->data['client_name'],
            $result->data['budget'],
            $result->data['status']
        );

        $this->flash('Projekt erstellt.');
        Response::redirect('/?entity=projects');
    }

    public function update(Request $request): void
    {
        $this->assertCsrf($request);

        $id = $request->id();
        if ($id === null) {
            $this->flash('Ungültige ID.');
            Response::redirect('/?entity=projects');
        }

        $project = $this->repository->findById($id);
        if ($project === null) {
            $this->flash('Eintrag nicht gefunden.');
            Response::redirect('/?entity=projects');
        }

        $result = $this->service->validate($_POST);
        if (!$result->ok) {
            $content = View::render('projects/form', [
                'mode' => 'edit',
                'project' => $project,
                'errors' => $result->errors,
                'input' => $result->data,
                'statuses' => $this->service->statuses(),
                'csrf' => $request->csrfToken(),
            ]);
            echo View::render('layout', ['content' => $content, 'entity' => 'projects']);
            exit;
        }

        $updated = $project->withChanges(
            $result->data['name'],
            $result->data['client_name'],
            $result->data['budget'],
            $result->data['status']
        );

        $this->repository->update($updated);
        $this->flash('Projekt aktualisiert.');
        Response::redirect('/?entity=projects');
    }

    public function delete(Request $request): void
    {
        $this->assertCsrf($request);

        $id = $request->id();
        if ($id === null) {
            $this->flash('Ungültige ID.');
            Response::redirect('/?entity=projects');
        }

        $this->repository->delete($id);
        $this->flash('Projekt gelöscht.');
        Response::redirect('/?entity=projects');
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
        $_SESSION['projects_flash'] = $message;
    }

    private function pullFlash(): ?string
    {
        $message = $_SESSION['projects_flash'] ?? null;
        unset($_SESSION['projects_flash']);
        return is_string($message) ? $message : null;
    }
}
