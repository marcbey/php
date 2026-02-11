<?php

// Steuert den Project-CRUD-Flow: validieren, CSRF erzwingen, Repository aufrufen, Views rendern.

declare(strict_types=1);

namespace App\Http;

use App\Repository\ProjectRepository;
use App\Service\ProjectService;
use App\View\View;

final class ProjectController
{
    /**
     * Verdrahtet Project-Repository und Project-Service fuer alle Projektaktionen.
     * Beispiel: `new ProjectController(new ProjectRepository($pdo), new ProjectService())`.
     */
    public function __construct(
        private ProjectRepository $repository,
        private ProjectService $service
    ) {}

    /**
     * Rendert die Projekt-Uebersicht mit Datensaetzen und Flash-Nachricht.
     * Beispiel: GET `/?entity=projects`.
     */
    public function index(Request $request): string
    {
        $projects = $this->repository->findAll();

        return View::render('projects/list', [
            'projects' => $projects,
            'flash' => $this->pullFlash(),
            'csrf' => $request->csrfToken(),
        ]);
    }

    /**
     * Rendert das Formular fuer ein neues Projekt mit Defaultwerten.
     * Beispiel: GET `/?entity=projects&action=create`.
     */
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

    /**
     * Rendert das Bearbeitungsformular fuer ein bestehendes Projekt.
     * Gueltigkeits- und Existenzpruefung erfolgen vor dem Rendern.
     * Beispiel: GET `/?entity=projects&action=edit&id=5`.
     */
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

    /**
     * Erstellt ein Projekt nach CSRF- und Validierungspruefung.
     * Bei Validierungsfehlern wird das Formular mit Fehlern erneut ausgegeben.
     * Beispiel: POST `/?entity=projects&action=store`.
     */
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

    /**
     * Aktualisiert ein bestehendes Projekt inkl. Budget-Normalisierung.
     * Nutzt `withChanges()` fuer ein konsistentes immutable Model-Update.
     * Beispiel: POST `/?entity=projects&action=update&id=5`.
     */
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

    /**
     * Loescht ein Projekt nach CSRF- und ID-Pruefung.
     * Aufruf erfolgt aus der Projektliste via POST + `_method=DELETE`.
     * Beispiel: POST `/?entity=projects&action=delete&id=5`.
     */
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

    /**
     * Erzwingt gueltiges CSRF-Token fuer alle schreibenden Projektoperationen.
     * Beispiel: interner Guard in `store()`, `update()`, `delete()`.
     */
    private function assertCsrf(Request $request): void
    {
        $token = (string) ($_POST['csrf_token'] ?? '');
        if (!hash_equals($request->csrfToken(), $token)) {
            http_response_code(419);
            echo 'Ungültiges CSRF-Token.';
            exit;
        }
    }

    /**
     * Speichert eine einmalige Meldung fuer den naechsten Seitenaufruf.
     * Beispiel: `$this->flash('Projekt erstellt.');`.
     */
    private function flash(string $message): void
    {
        $_SESSION['projects_flash'] = $message;
    }

    /**
     * Gibt die gespeicherte Flash-Meldung zurueck und entfernt sie aus der Session.
     * Beispiel: `'flash' => $this->pullFlash()` in `index()`.
     */
    private function pullFlash(): ?string
    {
        $message = $_SESSION['projects_flash'] ?? null;
        unset($_SESSION['projects_flash']);
        return is_string($message) ? $message : null;
    }
}
