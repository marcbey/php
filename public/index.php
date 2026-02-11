<?php

// Front Controller: initialisiert Security/Session, verdrahtet Abhaengigkeiten und routet Task/Project-CRUD-Aktionen.

declare(strict_types=1);

use App\Config\Database;
use App\Config\Env;
use App\Http\ProjectController;
use App\Http\TaskController;
use App\Http\Request;
use App\Http\Security;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Service\ProjectService;
use App\Service\TaskService;
use App\View\View;

require __DIR__ . '/../src/Support/autoload.php';

Env::load(__DIR__ . '/../.env');
Security::bootSession();
Security::applyHeaders();

$request = Request::capture();
if ($request->isPost()) {
    Security::assertSameOrigin($request->server());
}

$pdo = Database::pdo();
$taskController = new TaskController(new TaskRepository($pdo), new TaskService());
$projectController = new ProjectController(new ProjectRepository($pdo), new ProjectService());

$entity = $request->entity();
$action = $request->action();
$controller = $entity === 'projects' ? $projectController : $taskController;

if ($request->isPost()) {
    $method = strtoupper((string) ($_POST['_method'] ?? 'POST'));
    if ($method === 'DELETE') {
        $controller->delete($request);
    } elseif ($action === 'update') {
        $controller->update($request);
    } else {
        $controller->store($request);
    }
} else {
    if ($action === 'create') {
        $content = $controller->create($request);
    } elseif ($action === 'edit') {
        $content = $controller->edit($request);
    } else {
        $content = $controller->index($request);
    }

    echo View::render('layout', ['content' => $content, 'entity' => $entity]);
}
