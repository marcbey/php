<?php

// Front Controller: initialisiert Security/Session, verdrahtet Abhaengigkeiten und routet Task/Project-CRUD-Aktionen.

declare(strict_types=1);

// Zusammenhang `namespace` + `require_once` + `use`:
// 1) `require_once` laedt die PHP-Datei, damit die Klasse/Funktion ueberhaupt bekannt ist.
// 2) In der geladenen Datei ordnet `namespace App\...` die Klasse logisch ein und verhindert Namenskonflikte.
// 3) `use App\...` importiert diesen vollqualifizierten Namen als Kurzname fuer diese Datei.
// Kurz: `require_once` macht die Definition verfuegbar, `namespace` gibt ihr den eindeutigen Pfad,
// und `use` sorgt dafuer, dass wir sie hier bequem ohne langen Namen verwenden koennen.
use App\Config\Database;
use App\Config\Env;
use App\Http\ProjectController;
use App\Http\Request;
use App\Http\Security;
use App\Http\TaskController;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Service\ProjectService;
use App\Service\TaskService;
use App\View\View;

// require __DIR__ . '/../src/Support/autoload.php';
// Aktuell manuelles Laden statt Autoloader, um den Bootstrap explizit zu halten.
// Diese `require_once`-Zeilen ersetzen hier praktisch den Composer-Autoloader.
require_once __DIR__ . '/../src/Config/Database.php';
require_once __DIR__ . '/../src/Config/Env.php';
require_once __DIR__ . '/../src/Http/ProjectController.php';
require_once __DIR__ . '/../src/Http/Request.php';
require_once __DIR__ . '/../src/Http/Response.php';
require_once __DIR__ . '/../src/Http/Security.php';
require_once __DIR__ . '/../src/Http/TaskController.php';
require_once __DIR__ . '/../src/Model/Project.php';
require_once __DIR__ . '/../src/Model/Task.php';
require_once __DIR__ . '/../src/Repository/ProjectRepository.php';
require_once __DIR__ . '/../src/Repository/TaskRepository.php';
require_once __DIR__ . '/../src/Service/ProjectService.php';
require_once __DIR__ . '/../src/Service/TaskService.php';
require_once __DIR__ . '/../src/Service/ValidationResult.php';
require_once __DIR__ . '/../src/View/View.php';

require_once __DIR__ . '/../src/View/helpers.php';

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

$controllers = [
    'tasks' => $taskController,
    'projects' => $projectController,
];

$defaultEntity = 'tasks';
if (!array_key_exists($defaultEntity, $controllers)) {
    $defaultEntity = (string) array_key_first($controllers);
}


$entity = $request->entity(array_keys($controllers), $defaultEntity);
$action = $request->action();
$controller = $controllers[$entity];

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
