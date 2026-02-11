<?php

// Autoloader fuer dieses Projekt ohne Composer:
// 1) PHP uebergibt den angeforderten Klassennamen an die Callback-Funktion.
// 2) Es werden nur Klassen mit Prefix `App\` verarbeitet; alles andere wird ignoriert.
// 3) Der Namespace-Teil hinter `App\` wird in einen Dateipfad umgewandelt.
// 4) Aus `App\Http\ProjectController` wird so `src/Http/ProjectController.php`.
// 5) Existiert die Datei, wird sie mit `require` geladen.
// Beispiel im Projekt:
// - In `public/index.php` wird `use App\Http\ProjectController;` verwendet.
// - Beim ersten Zugriff auf `ProjectController` laedt dieser Autoloader automatisch
//   die Datei `src/Http/ProjectController.php`.

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require $path;
    }
});
