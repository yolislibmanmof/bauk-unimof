<?php
declare(strict_types=1);

namespace App\Helpers;

class Auth
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    public function attempt(string $username, string $password): array
    {
        $ip = Security::getClientIp();
        if (!Security::checkRateLimit('login_' . $ip, 5, 15)) {
            return ['success' => false, 'message' => 'Terlalu banyak percobaan. Coba lagi dalam 15 menit.'];
        }

        $user = $this->db->fetch("SELECT * FROM users WHERE (username = ? OR email = ?) AND deleted_at IS NULL", [$username, $username]);
        if (!$user) return ['success' => false, 'message' => 'Username atau password salah.'];

        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return ['success' => false, 'message' => 'Akun terkunci sementara.'];
        }
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Akun tidak aktif. Hubungi administrator.'];
        }
        if (!Security::verifyPassword($password, $user['password'])) {
            $attempts = (int)$user['login_attempts'] + 1;
            $this->db->update('users', [
                'login_attempts' => $attempts,
                'locked_until'   => $attempts >= 5 ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null,
            ], 'id = ?', [$user['id']]);
            return ['success' => false, 'message' => 'Username atau password salah.'];
        }

        $this->db->update('users', [
            'login_attempts' => 0, 'locked_until' => null,
            'last_login_at'  => date('Y-m-d H:i:s'), 'last_login_ip' => $ip,
        ], 'id = ?', [$user['id']]);

        $this->login($user);
        ActivityLog::create((int)$user['id'], 'login', 'Berhasil masuk ke sistem');
        return ['success' => true, 'user' => $user];
    }

    public function login(array $user): void
    {
        $_SESSION['auth'] = [
            'id' => (int)$user['id'], 'uuid' => $user['uuid'], 'username' => $user['username'],
            'full_name' => $user['full_name'], 'email' => $user['email'], 'role' => $user['role'],
            'avatar' => $user['avatar'], 'logged_in' => true, 'login_time' => time(),
        ];
        session_regenerate_id(true);
    }

    public function logout(): void
    {
        if (self::id()) ActivityLog::create(self::id(), 'logout', 'Keluar dari sistem');
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool   { return ($_SESSION['auth']['logged_in'] ?? false) === true; }
    public static function user(): ?array  { return $_SESSION['auth'] ?? null; }
    
    // [FIXED] Kurung kurawal buka '{' sudah ditambahkan di sini
    public static function id(): ?int { return isset($_SESSION['auth']['id']) ? (int)$_SESSION['auth']['id'] : null; }

    public static function hasRole(string|array $roles): bool
    {
        if (!self::check()) return false;
        $role = $_SESSION['auth']['role'];
        return is_array($roles) ? in_array($role, $roles, true) : $role === $roles;
    }

    public static function isAdmin(): bool { return self::hasRole(['super_admin', 'admin_bauk']); }

    public static function requireAuth(?string $redirectTo = null): void
    {
        if (!self::check()) { header('Location: ' . ($redirectTo ?? url('admin/login'))); exit; }
    }

    public static function requireRole(string|array $roles, ?string $redirectTo = null): void
    {
        self::requireAuth();
        if (!self::hasRole($roles)) { header('Location: ' . ($redirectTo ?? url('admin'))); exit; }
    }
}