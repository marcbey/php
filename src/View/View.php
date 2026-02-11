<?php

// Rendert View-Templates mit uebergebenen Daten.
// Beispielaufruf: `echo View::render('layout', ['content' => $content, 'entity' => $entity]);`

// Erzwingt strikte Typpruefung in dieser Datei (keine impliziten Typumwandlungen).
declare(strict_types=1);

// Definiert den Namespace der View-Schicht fuer saubere Klassenorganisation und Imports.
namespace App\View;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        // Baut den absoluten Dateipfad zum gewuenschten Template auf.
        // Beispiel: `tasks/list` wird zu `src/View/tasks/list.php`.
        $path = __DIR__ . '/' . $template . '.php';
        if (!is_file($path)) {
            return 'Template nicht gefunden.';
        }

        // Macht die uebergebenen Daten als Variablen im Template verfuegbar.
        // EXTR_SKIP verhindert, dass bestehende Variablen ueberschrieben werden.
        extract($data, EXTR_SKIP);

        // Startet einen Output-Buffer, damit die Template-Ausgabe als String
        // zurueckgegeben werden kann statt direkt an den Browser zu gehen.
        ob_start();
        
        // Fuehrt die eigentliche Template-Datei aus.
        include $path;
        
        // Liefert den gepufferten Inhalt als gerenderten HTML-String zurueck.
        return (string) ob_get_clean();
    }
}
