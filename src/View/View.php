<?php

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

        require_once __DIR__ . '/helpers.php';
        extract($data, EXTR_SKIP);

        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}
