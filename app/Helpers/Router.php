<?php
declare(strict_types=1);

namespace App\Helpers;

class Router
{
    private static array $routes = [];

    public static function get(string $path, callable $cb): void  { self::$routes['GET'][$path]  = $cb; }
    public static function post(string $path, callable $cb): void { self::$routes['POST'][$path] = $cb; }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = current_path(); // sudah tanpa BASE_URL

        foreach (self::$routes[$method] ?? [] as $path => $callback) {
            $pattern = '#^' . preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $path) . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                if ($method === 'POST' && !Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    http_response_code(403);
                    echo 'CSRF token verification failed.';
                    return;
                }
                call_user_func_array($callback, $matches);
                return;
            }
        }

        http_response_code(404);
        echo '<h1>404</h1><p>Halaman tidak ditemukan.</p><p><a href="' . url() . '">Kembali ke Beranda</a></p>';
    }
}