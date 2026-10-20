<?php
/**
 * =====================================================
 * BAUK UNIMOF - API HELPER
 * RESTful JSON API + Bearer Token + Rate Limit
 * =====================================================
 */

namespace App\Helpers;

class Api
{
    public static function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success($data = null, string $message = 'Success', int $statusCode = 200): void
    {
        self::json([
            'success'   => true,
            'message'   => $message,
            'data'      => $data,
            'timestamp' => time(),
        ], $statusCode);
    }

    public static function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        self::json([
            'success'   => false,
            'message'   => $message,
            'errors'    => $errors,
            'timestamp' => time(),
        ], $statusCode);
    }

    /**
     * Autentikasi: Bearer token ATAU session admin yang sedang login
     */
    public static function authenticate(): bool
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
        if ($header !== '' && preg_match('/Bearer\s+(\S+)/i', $header, $m)) {
            $token = $m[1];
            $user = Database::getInstance()->fetch(
                "SELECT id, role, status FROM users WHERE api_token = ? AND deleted_at IS NULL", [$token]
            );
            if ($user && $user['status'] === 'active') {
                $_SESSION['api_user_id'] = (int)$user['id'];
                $_SESSION['api_role'] = $user['role'];
                return true;
            }
            self::error('Token tidak valid atau akun nonaktif', 401);
            return false;
        }
        // Fallback: session login admin (memudahkan testing di browser)
        if (Auth::check()) {
            $_SESSION['api_user_id'] = Auth::id();
            return true;
        }
        self::error('Unauthorized: login atau kirim Bearer token', 401);
        return false;
    }

    public static function userId(): int
    {
        return (int)($_SESSION['api_user_id'] ?? 0);
    }

    public static function generateToken(int $userId): string
    {
        $token = self::generateBearerToken();
        Database::getInstance()->update('users', ['api_token' => $token], 'id = ?', [$userId]);
        return $token;
    }

    public static function revokeToken(int $userId): bool
    {
        return Database::getInstance()->update('users', ['api_token' => null], 'id = ?', [$userId]) > 0;
    }

    private static function generateBearerToken(): string
    {
        return 'bauk_' . bin2hex(random_bytes(24));
    }

    /**
     * Rate limit per IP / token: default 60 request per menit
     */
    public static function rateLimit(string $identifier, int $maxRequests = 60, int $windowSeconds = 60): bool
    {
        if (!SecurityEnhanced::rateLimit('api_' . $identifier, $maxRequests, $windowSeconds)) {
            self::error('Rate limit terlampaui. Coba lagi nanti.', 429);
            return false;
        }
        return true;
    }

    public static function input(string $key, $default = null)
    {
        $all = self::all();
        return $all[$key] ?? $default;
    }

    public static function all(): array
    {
        static $cached = null;
        if ($cached === null) {
            $json = json_decode(file_get_contents('php://input') ?: '', true);
            $cached = array_merge($_GET, $_POST, is_array($json) ? $json : []);
        }
        return $cached;
    }

    /**
     * Validasi sederhana: ['field' => ['required','email','min:8','max:100','numeric']]
     */
    public static function validate(array $rules, array $data): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleList) {
            $value = $data[$field] ?? null;
            $label = ucfirst(str_replace('_', ' ', $field));
            if (in_array('required', $ruleList, true) && ($value === null || $value === '')) {
                $errors[$field] = $label . ' wajib diisi';
                continue;
            }
            if ($value === null || $value === '') continue;
            if (in_array('email', $ruleList, true) && !Security::isValidEmail((string)$value)) {
                $errors[$field] = $label . ' harus email valid';
            }
            if (in_array('numeric', $ruleList, true) && !is_numeric($value)) {
                $errors[$field] = $label . ' harus angka';
            }
            foreach ($ruleList as $r) {
                if (str_starts_with($r, 'min:')) {
                    $min = (int)substr($r, 4);
                    if (mb_strlen((string)$value) < $min) $errors[$field] = $label . " minimal {$min} karakter";
                }
                if (str_starts_with($r, 'max:')) {
                    $max = (int)substr($r, 4);
                    if (mb_strlen((string)$value) > $max) $errors[$field] = $label . " maksimal {$max} karakter";
                }
            }
        }
        return $errors;
    }
}