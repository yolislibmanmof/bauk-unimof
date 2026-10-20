<?php
declare(strict_types=1);

namespace App\Helpers;

class View
{
    private static string $basePath = __DIR__ . '/../Views/';

    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $file = self::$basePath . $view . '.php';
        if (!file_exists($file)) throw new \RuntimeException("View not found: {$view}");
        include $file;
    }

    public static function renderWithLayout(string $layout, string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = self::$basePath . $view . '.php';
        if (!file_exists($viewFile)) throw new \RuntimeException("View not found: {$view}");

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        $layoutFile = self::$basePath . 'layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) throw new \RuntimeException("Layout not found: {$layout}");
        include $layoutFile;
    }

    public static function asset(string $path): string { return asset($path); }
    public static function url(string $path = ''): string { return url($path); }
}