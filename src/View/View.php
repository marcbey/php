<?php

// Rendert View-Templates mit uebergebenen Daten.
// Beispielaufruf: `echo View::render('layout', ['content' => $content, 'entity' => $entity]);`

declare(strict_types=1);

namespace App\View;

final class View
{
    public static function render(string $template, array $data = []): string
    {
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
