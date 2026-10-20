<?php
/**
 * Security Helper - BAUK UNIMOF (LENGKAP)
 * CSRF, XSS, Password, Encryption, Rate Limiting, Upload Validation
 * PHP 8.2+
 */
declare(strict_types=1);

namespace App\Helpers;

class Security
{
    // =====================================================
    // CSRF PROTECTION
    // =====================================================
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || $token === '') {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::generateCsrfToken() . '">';
    }

    // =====================================================
    // INPUT SANITIZATION (Anti XSS)
    // =====================================================
    public static function sanitize(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        if (is_string($input)) {
            $input = trim($input);
            $input = strip_tags($input);
            $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return $input;
    }

    public static function cleanInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // =====================================================
    // UUID & RANDOM STRING
    // =====================================================
    public static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function generateRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes((int) ceil($length / 2)));
    }

    // =====================================================
    // PASSWORD HASHING & VERIFICATION
    // =====================================================
    public static function hashPassword(string $password): string
    {
        // Argon2ID jika tersedia, fallback ke bcrypt (kompatibel semua build PHP)
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost'   => 4,
                'threads'     => 3,
            ]);
        }
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function isStrongPassword(string $password): array
    {
        $errors = [];
        if (strlen($password) < 8)              $errors[] = 'Password minimal 8 karakter';
        if (!preg_match('/[A-Z]/', $password))  $errors[] = 'Wajib mengandung huruf kapital';
        if (!preg_match('/[a-z]/', $password))  $errors[] = 'Wajib mengandung huruf kecil';
        if (!preg_match('/[0-9]/', $password))  $errors[] = 'Wajib mengandung angka';
        if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = 'Wajib mengandung karakter spesial';
        return $errors;
    }

    // =====================================================
    // ENCRYPTION (AES-256-CBC)
    // =====================================================
    public static function encrypt(string $data, string $key): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', hash('sha256', $key, true), OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt(string $data, string $key): string|false
    {
        $raw = base64_decode($data, true);
        if ($raw === false || strlen($raw) < 17) {
            return false;
        }
        return openssl_decrypt(substr($raw, 16), 'AES-256-CBC', hash('sha256', $key, true), OPENSSL_RAW_DATA, substr($raw, 0, 16));
    }

    // =====================================================
    // SECURITY HEADERS
    // =====================================================
    public static function setSecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }

    // =====================================================
    // RATE LIMITING (Anti Brute Force)
    // =====================================================
    public static function checkRateLimit(string $key, int $maxAttempts = 5, int $windowMinutes = 15): bool
    {
        $sessionKey = 'rate_limit_' . md5($key);
        $now = time();

        if (!isset($_SESSION[$sessionKey]) || !is_array($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = ['attempts' => 1, 'window_start' => $now];
            return true;
        }

        $data = $_SESSION[$sessionKey];

        if (($now - $data['window_start']) > ($windowMinutes * 60)) {
            $_SESSION[$sessionKey] = ['attempts' => 1, 'window_start' => $now];
            return true;
        }

        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }

        $_SESSION[$sessionKey]['attempts']++;
        return true;
    }

    // =====================================================
    // CLIENT IP DETECTION
    // =====================================================
    public static function getClientIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    // =====================================================
    // FILE UPLOAD VALIDATION
    // =====================================================
    public static function generateSecureFilename(string $originalName): string
    {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        return self::generateRandomString(16) . '.' . $ext;
    }

    public static function validateFileUpload(array $file, array $allowedExt = [], int $maxSize = 10485760): array
    {
        $errors = [];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errors[] = 'Gagal mengunggah file (kode: ' . ($file['error'] ?? '?') . ')';
            return $errors;
        }

        if ($file['size'] > $maxSize) {
            $errors[] = 'Ukuran file maksimal ' . round($maxSize / 1048576, 1) . ' MB';
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($allowedExt !== [] && !in_array($ext, $allowedExt, true)) {
            $errors[] = 'Ekstensi tidak diizinkan: .' . $ext;
        }

        // Verifikasi MIME type asli (anti file palsu ber-ekstensi ganda)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $allowedMime = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        if (!in_array($mime, $allowedMime, true)) {
            $errors[] = 'Tipe file tidak diizinkan: ' . $mime;
        }

        return $errors;
    }
}