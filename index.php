<?php
/**
 * =====================================================
 * BAUK UNIMOF - FRONT CONTROLLER (FINAL EDITION)
 * Sistem Informasi Biro Administrasi Umum dan Keuangan
 * Universitas Muhammadiyah Maumere
 * =====================================================
 * PHP 8.2+ | MySQL/MariaDB | Bootstrap Icons
 * Versi: 1.0.0 | Tanggal: 2026-10-20
 * =====================================================
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Asia/Makassar');

// ===== SESSION =====
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// ===== BASE URL DETECTION (support subfolder) =====
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$scriptDir = rtrim($scriptDir, '/');
define('BASE_URL', $scriptDir === '/' ? '' : $scriptDir);

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}
if (!function_exists('asset')) {
    function asset(string $path = ''): string {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }
}
if (!function_exists('current_path')) {
    function current_path(): string {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        if (BASE_URL !== '' && strncmp($uri, BASE_URL, strlen(BASE_URL)) === 0) {
            $uri = substr($uri, strlen(BASE_URL));
        }
        $uri = '/' . ltrim($uri, '/');
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }
}

// ===== AUTOLOADER =====
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = __DIR__ . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

require_once __DIR__ . '/app/Helpers/Database.php';
require_once __DIR__ . '/app/Helpers/Security.php';
require_once __DIR__ . '/app/Helpers/ActivityLog.php';
require_once __DIR__ . '/app/Helpers/Auth.php';
require_once __DIR__ . '/app/Helpers/Router.php';
require_once __DIR__ . '/app/Helpers/View.php';
// ===== NEW ULTIMATE HELPERS =====
require_once __DIR__ . '/app/Helpers/Dashboard.php';
require_once __DIR__ . '/app/Helpers/Chart.php';
require_once __DIR__ . '/app/Helpers/Export.php';
require_once __DIR__ . '/app/Helpers/Cache.php';
require_once __DIR__ . '/app/Helpers/Notification.php';
require_once __DIR__ . '/app/Helpers/SecurityEnhanced.php';
require_once __DIR__ . '/app/Helpers/Api.php';
require_once __DIR__ . '/app/Helpers/Import.php';
require_once __DIR__ . '/app/Helpers/Calendar.php';

use App\Helpers\{Security, Auth, ActivityLog, Router, View, Database, Dashboard, Chart, Export, Cache, Notification, SecurityEnhanced, Api, Import, Calendar};

Security::setSecurityHeaders();

// ===== CONSTANTS =====
define('PER_PAGE', 10);
define('PER_PAGE_SMALL', 6);
define('UPLOAD_DIR', __DIR__ . '/public/uploads/');

// ===== HELPER FUNCTIONS =====
function old(string $key, $default = '') {
    $old = $_SESSION['form_old'] ?? [];
    return $old[$key] ?? $default;
}
function clearOld() { unset($_SESSION['form_old']); }

function buildQuery(string $base, array $params): string {
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null && $v !== 0 && $v !== '0');
    return url($base) . ($params ? '?' . http_build_query($params) : '');
}

function flash(string $type, string $message): void {
    $_SESSION['flash_' . $type] = $message;
}

function flashArray(string $type, array $errors): void {
    $_SESSION['flash_' . $type] = $errors;
}

function safeFilename(string $name): string {
    return Security::generateSecureFilename($name);
}

function ensureUploadDir(string $subdir): string {
    $dir = UPLOAD_DIR . $subdir . '/';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return $dir;
}

// =====================================================
// ██████  ██    ██ ██       ███████  ██████
// ██   ██ ██    ██ ██       ██      ██
// ██████  ██    ██ ██       █████   ██
// ██      ██    ██ ██       ██      ██
// ██       ██████  ███████  ███████  ██████
// =====================================================

Router::get('/', fn() => View::renderWithLayout('public', 'public/home', ['title' => 'Beranda - BAUK UNIMOF']));
Router::get('/tentang', fn() => View::renderWithLayout('public', 'public/about', ['title' => 'Tentang - BAUK UNIMOF']));

Router::get('/layanan/{section}', function (string $section) {
    $allowed = ['kepegawaian', 'keuangan', 'aset', 'k3l'];
    if (!in_array($section, $allowed, true)) { http_response_code(404); echo 'Halaman tidak ditemukan'; return; }
    View::renderWithLayout('public', 'public/service', ['title' => 'Layanan ' . ucfirst($section), 'section' => $section]);
});

Router::get('/berita', function () {
    $db = Database::getInstance();
    $berita = $db->fetchAll("SELECT b.*, u.full_name AS author_name FROM berita b LEFT JOIN users u ON b.author_id = u.id WHERE b.is_published = 1 AND b.deleted_at IS NULL ORDER BY b.published_at DESC LIMIT 24");
    View::renderWithLayout('public', 'public/news', ['title' => 'Berita - BAUK UNIMOF', 'berita' => $berita]);
});

Router::get('/berita/{slug}', function (string $slug) {
    $db = Database::getInstance();
    $article = $db->fetch("SELECT b.*, u.full_name AS author_name FROM berita b LEFT JOIN users u ON b.author_id = u.id WHERE b.slug = ? AND b.is_published = 1 AND b.deleted_at IS NULL", [Security::sanitize($slug)]);
    if (!$article) { http_response_code(404); echo 'Artikel tidak ditemukan'; return; }
    $db->update('berita', ['views' => $article['views'] + 1], 'id = ?', [$article['id']]);
    View::renderWithLayout('public', 'public/news_detail', ['title' => $article['title'], 'article' => $article]);
});

Router::get('/regulasi', function () {
    $db = Database::getInstance();
    $dokumen = $db->fetchAll("SELECT * FROM dokumen_regulasi WHERE status = 'active' ORDER BY effective_date DESC");
    View::renderWithLayout('public', 'public/regulations', ['title' => 'Regulasi - BAUK UNIMOF', 'dokumen' => $dokumen]);
});

Router::get('/kontak', fn() => View::renderWithLayout('public', 'public/contact', ['title' => 'Kontak - BAUK UNIMOF']));

Router::post('/kontak/submit', function () {
    $name = Security::sanitize($_POST['name'] ?? '');
    $email = Security::sanitize($_POST['email'] ?? '');
    $phone = Security::sanitize($_POST['phone'] ?? '');
    $subject = Security::sanitize($_POST['subject'] ?? '');
    $message = Security::cleanInput($_POST['message'] ?? '');
    $errors = [];
    if ($name === '') $errors[] = 'Nama wajib diisi';
    if (!Security::isValidEmail($email)) $errors[] = 'Email tidak valid';
    if ($subject === '') $errors[] = 'Subjek wajib diisi';
    if (strlen($message) < 10) $errors[] = 'Pesan minimal 10 karakter';
    if ($errors) { $_SESSION['flash_error'] = $errors; header('Location: ' . url('kontak')); exit; }
    Database::getInstance()->insert('contact_messages', [
        'uuid' => Security::generateUuid(), 'name' => $name, 'email' => $email,
        'phone' => $phone, 'subject' => $subject, 'message' => $message, 'ip_address' => Security::getClientIp(),
    ]);
    flash('success', 'Pesan berhasil dikirim. Terima kasih!');
    header('Location: ' . url('kontak')); exit;
});

// =====================================================
//  █████  ██    ██ ████████ ██   ██
// ██   ██ ██    ██    ██    ██   ██
// ███████ ██    ██    ██    ███████
// ██   ██ ██    ██    ██    ██   ██
// ██   ██  ██████     ██    ██   ██
// =====================================================

Router::get('/admin/login', function () {
    if (Auth::check()) { header('Location: ' . url('admin')); exit; }
    View::render('admin/login', ['title' => 'Login - Admin BAUK UNIMOF']);
});

Router::post('/admin/login', function () {
    if (Auth::check()) { header('Location: ' . url('admin')); exit; }
    $username = Security::sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        flash('error', 'Username dan password wajib diisi.');
        header('Location: ' . url('admin/login')); exit;
    }
    $result = (new Auth())->attempt($username, $password);
    if (!$result['success']) {
        flash('error', $result['message']);
        header('Location: ' . url('admin/login')); exit;
    }
    header('Location: ' . url('admin')); exit;
});

Router::get('/admin/logout', function () {
    // 1. Catat log logout SEBELUM session dihancurkan
    if (Auth::check()) {
        try {
            ActivityLog::create(Auth::id(), 'logout', 'User logout dari sistem');
        } catch (\Throwable $e) {
            // Abaikan jika gagal logging
        }
    }
    
    // 2. Hancurkan session secara aman
    session_unset();
    session_destroy();
    
    // 3. Hapus cookie session dari browser
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // 4. Mulai session baru KHUSUS untuk flash message
    session_start();
    $_SESSION['flash_success'] = 'Anda telah logout dengan sukses.';
    
    // 5. Redirect ke halaman login
    header('Location: ' . url('admin/login'));
    exit;
});

// =====================================================
// ██      ███████ ██    ██  █████  ██    ██ ██████
// ██      ██      ██    ██ ██   ██ ██    ██ ██   ██
// ██      █████   ██    ██ ███████ ██    ██ ██   ██
// ██      ██       ██  ██  ██   ██ ██    ██ ██   ██
// ███████ ███████   ████   ██   ██  ██████  ██████
// =====================================================

Router::get('/admin', function () {
    Auth::requireAuth();
    
    // Update last_seen untuk tracking user online
    Database::getInstance()->update('users', ['last_seen' => date('Y-m-d H:i:s')], 'id = ?', [Auth::id()]);
    
    // Stats komprehensif dari Dashboard Helper
    $stats = Dashboard::getStats();
    
    // Data untuk charts
    $financialTrend = Dashboard::getFinancialTrend();
    $periodComparison = Dashboard::getPeriodComparison();
    
    // Activity timeline
    $recentActivities = Dashboard::getActivityTimeline(10);
    
    // Upcoming events dari Calendar Helper
    $upcomingEvents = Calendar::getUpcomingEvents(7, 5);
    
    // Notifications unread
    $unreadNotifications = Notification::getUnreadCount(Auth::id());
    $recentNotifications = Notification::getForUser(Auth::id(), 5, true);
    
    View::renderWithLayout('admin', 'admin/dashboard', [
        'title' => 'Dashboard',
        'stats' => $stats,
        'financialTrend' => $financialTrend,
        'periodComparison' => $periodComparison,
        'recentActivities' => $recentActivities,
        'upcomingEvents' => $upcomingEvents,
        'unreadNotifications' => $unreadNotifications,
        'recentNotifications' => $recentNotifications,
    ]);
});

// =====================================================
// ██████  ███████  ██████  ██    ██ ██   ██ ████████
// ██   ██ ██      ██    ██ ██    ██ ██   ██    ██
// ██████  █████   ██    ██ ██    ██ ███████    ██
// ██   ██ ██      ██ ▄██ ██ ██    ██ ██   ██    ██
// ██   ██ ███████  ██████   ██████  ██   ██    ██
// =====================================================

Router::get('/admin/pegawai', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_kepegawaian']);
    $db = Database::getInstance();
    $search = trim(Security::sanitize($_GET['q'] ?? ''));
    $page = max(1, (int)($_GET['page'] ?? 1));
    $sort = $_GET['sort'] ?? 'full_name';
    $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
    $fStatus = $_GET['status'] ?? '';
    $fDept = max(0, (int)($_GET['dept'] ?? 0));
    if (!in_array($sort, ['full_name', 'nip', 'position', 'join_date', 'status'], true)) $sort = 'full_name';
    $where = 'p.deleted_at IS NULL'; $params = [];
    if ($search !== '') { $where .= ' AND (p.full_name LIKE ? OR p.nip LIKE ? OR p.position LIKE ?)'; $like = "%{$search}%"; array_push($params, $like, $like, $like); }
    if (in_array($fStatus, ['active', 'inactive', 'retired', 'resigned'], true)) { $where .= ' AND p.status = ?'; $params[] = $fStatus; }
    if ($fDept > 0) { $where .= ' AND p.department_id = ?'; $params[] = $fDept; }
    $total = (int)($db->fetch("SELECT COUNT(*) AS c FROM pegawai p WHERE {$where}", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total / PER_PAGE));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * PER_PAGE;
    $pegawai = $db->fetchAll("SELECT p.*, d.name AS department_name FROM pegawai p LEFT JOIN departments d ON p.department_id = d.id WHERE {$where} ORDER BY p.{$sort} {$dir} LIMIT " . PER_PAGE . " OFFSET " . $offset, $params);
    View::renderWithLayout('admin', 'admin/pegawai/index', [
        'title' => 'Data Pegawai', 'pegawai' => $pegawai,
        'departments' => $db->fetchAll("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name"),
        'trashCount' => $db->count('pegawai', 'deleted_at IS NOT NULL'),
        'pagination' => ['page' => $page, 'totalPages' => $totalPages, 'total' => $total, 'search' => $search, 'sort' => $sort, 'dir' => strtolower($dir), 'status' => $fStatus, 'dept' => $fDept],
    ]);
});

Router::get('/admin/pegawai/trash', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_kepegawaian']);
    $trash = Database::getInstance()->fetchAll("SELECT p.*, d.name AS department_name FROM pegawai p LEFT JOIN departments d ON p.department_id = d.id WHERE p.deleted_at IS NOT NULL ORDER BY p.deleted_at DESC");
    View::renderWithLayout('admin', 'admin/pegawai/trash', ['title' => 'Tempat Sampah Pegawai', 'trash' => $trash]);
});

Router::get('/admin/pegawai/tambah', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_kepegawaian']);
    View::renderWithLayout('admin', 'admin/pegawai/form', ['title' => 'Tambah Pegawai', 'mode' => 'add', 'data' => null, 'departments' => Database::getInstance()->fetchAll("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name")]);
});

Router::get('/admin/pegawai/edit/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_kepegawaian']);
    $data = Database::getInstance()->fetch("SELECT * FROM pegawai WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
    if (!$data) { flash('error', 'Data pegawai tidak ditemukan.'); header('Location: ' . url('admin/pegawai')); exit; }
    View::renderWithLayout('admin', 'admin/pegawai/form', ['title' => 'Edit Pegawai', 'mode' => 'edit', 'data' => $data, 'departments' => Database::getInstance()->fetchAll("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name")]);
});

Router::get('/admin/pegawai/detail/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_kepegawaian']);
    $data = Database::getInstance()->fetch("SELECT p.*, d.name AS department_name FROM pegawai p LEFT JOIN departments d ON p.department_id = d.id WHERE p.id = ? AND p.deleted_at IS NULL", [(int)$id]);
    if (!$data) { flash('error', 'Data tidak ditemukan.'); header('Location: ' . url('admin/pegawai')); exit; }
    View::renderWithLayout('admin', 'admin/pegawai/detail', ['title' => 'Detail Pegawai', 'data' => $data]);
});

Router::post('/admin/pegawai/simpan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_kepegawaian']);
    $db = Database::getInstance();
    $id = max(0, (int)($_POST['id'] ?? 0));
    $inp = [];
    foreach (['full_name','nip','nik','nuptk','gender','birth_place','religion','marital_status','address','phone','email','position','employment_status','education_level','status','notes'] as $f) {
        $inp[$f] = Security::sanitize($_POST[$f] ?? '');
    }
    $birthDate = Security::sanitize($_POST['birth_date'] ?? '') ?: null;
    $joinDate  = Security::sanitize($_POST['join_date'] ?? '') ?: null;
    $endDate   = Security::sanitize($_POST['end_date'] ?? '') ?: null;
    $deptId    = max(0, (int)($_POST['department_id'] ?? 0)) ?: null;
    $salary    = (float)preg_replace('/[^0-9.]/', '', $_POST['salary'] ?? '0');
    $errors = [];
    if ($inp['full_name'] === '') $errors[] = 'Nama lengkap wajib diisi';
    if ($inp['nip'] === '') $errors[] = 'NIP wajib diisi';
    if (!in_array($inp['gender'], ['L', 'P'], true)) $errors[] = 'Jenis kelamin tidak valid';
    if ($inp['email'] !== '' && !Security::isValidEmail($inp['email'])) $errors[] = 'Format email tidak valid';
    if ($joinDate && $endDate && strtotime($endDate) < strtotime($joinDate)) $errors[] = 'Tanggal keluar tidak boleh sebelum tanggal masuk';
    $dup = $db->fetch("SELECT id FROM pegawai WHERE nip = ? AND deleted_at IS NULL AND id != ?", [$inp['nip'], $id]);
    if ($dup) $errors[] = 'NIP "' . $inp['nip'] . '" sudah digunakan';
    $existingPhoto = $id ? ($db->fetch("SELECT photo FROM pegawai WHERE id = ?", [$id])['photo'] ?? null) : null;
    $photoName = $existingPhoto;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $errs = Security::validateFileUpload($_FILES['photo'], ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
        if ($errs) $errors = array_merge($errors, $errs);
        else {
            $dir = ensureUploadDir('pegawai');
            $new = safeFilename($_FILES['photo']['name']);
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $new)) $photoName = $new;
        }
    }
    if ($errors) { $_SESSION['form_old'] = $_POST; $_SESSION['flash_error'] = $errors; header('Location: ' . url($id ? 'admin/pegawai/edit/' . $id : 'admin/pegawai/tambah')); exit; }
    $payload = [
        'full_name' => $inp['full_name'], 'nip' => $inp['nip'],
        'nik' => $inp['nik'] ?: null, 'nuptk' => $inp['nuptk'] ?: null,
        'gender' => $inp['gender'], 'birth_place' => $inp['birth_place'] ?: null,
        'birth_date' => $birthDate, 'religion' => $inp['religion'] ?: null,
        'marital_status' => $inp['marital_status'] ?: null, 'address' => $inp['address'] ?: null,
        'phone' => $inp['phone'] ?: null, 'email' => $inp['email'] ?: null,
        'department_id' => $deptId, 'position' => $inp['position'] ?: null,
        'employment_status' => $inp['employment_status'], 'education_level' => $inp['education_level'] ?: null,
        'join_date' => $joinDate, 'end_date' => $endDate, 'salary' => $salary,
        'photo' => $photoName, 'status' => $inp['status'], 'notes' => $inp['notes'] ?: null,
        'updated_by' => Auth::id(),
    ];
    if ($id > 0) {
        $db->update('pegawai', $payload, 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'update', 'Mengubah pegawai: ' . $inp['full_name'], 'pegawai', $id);
        flash('success', 'Data pegawai "' . $inp['full_name'] . '" berhasil diperbarui.');
    } else {
        $payload['uuid'] = Security::generateUuid();
        $payload['created_by'] = Auth::id();
        $id = $db->insert('pegawai', $payload);
        ActivityLog::create(Auth::id(), 'create', 'Menambahkan pegawai: ' . $inp['full_name'], 'pegawai', $id);
        flash('success', 'Pegawai "' . $inp['full_name'] . '" berhasil ditambahkan.');
    }
    clearOld();
    header('Location: ' . url('admin/pegawai/detail/' . $id)); exit;
});

Router::get('/admin/pegawai/hapus/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_kepegawaian']);
    $p = Database::getInstance()->fetch("SELECT full_name FROM pegawai WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
    if ($p) {
        Database::getInstance()->update('pegawai', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$id]);
        ActivityLog::create(Auth::id(), 'delete', 'Soft delete pegawai: ' . $p['full_name'], 'pegawai', (int)$id);
        flash('success', 'Pegawai "' . $p['full_name'] . '" dipindahkan ke tempat sampah.');
    }
    header('Location: ' . url('admin/pegawai')); exit;
});

Router::get('/admin/pegawai/pulihkan/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_kepegawaian']);
    $p = Database::getInstance()->fetch("SELECT full_name FROM pegawai WHERE id = ? AND deleted_at IS NOT NULL", [(int)$id]);
    if ($p) {
        Database::getInstance()->update('pegawai', ['deleted_at' => null], 'id = ?', [(int)$id]);
        ActivityLog::create(Auth::id(), 'restore', 'Memulihkan pegawai: ' . $p['full_name'], 'pegawai', (int)$id);
        flash('success', 'Pegawai "' . $p['full_name'] . '" berhasil dipulihkan.');
    }
    header('Location: ' . url('admin/pegawai/trash')); exit;
});

Router::get('/admin/pegawai/hapus-permanen/{id}', function (string $id) {
    Auth::requireRole('super_admin');
    $p = Database::getInstance()->fetch("SELECT full_name, photo FROM pegawai WHERE id = ? AND deleted_at IS NOT NULL", [(int)$id]);
    if ($p) {
        if (!empty($p['photo'])) { $file = UPLOAD_DIR . 'pegawai/' . basename($p['photo']); if (file_exists($file)) @unlink($file); }
        Database::getInstance()->delete('pegawai', 'id = ?', [(int)$id]);
        ActivityLog::create(Auth::id(), 'delete_permanent', 'Hapus permanen pegawai: ' . $p['full_name'], 'pegawai', (int)$id);
        flash('success', 'Pegawai "' . $p['full_name'] . '" dihapus permanen.');
    }
    header('Location: ' . url('admin/pegawai/trash')); exit;
});

Router::get('/admin/pegawai/export', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_kepegawaian']);
    $rows = Database::getInstance()->fetchAll("SELECT p.*, d.name AS dept FROM pegawai p LEFT JOIN departments d ON p.department_id = d.id WHERE p.deleted_at IS NULL ORDER BY p.full_name");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="data-pegawai-' . date('Ymd-His') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['NIP','Nama','NIK','NUPTK','JK','Tempat Lahir','Tanggal Lahir','Agama','Status Nikah','Alamat','Telepon','Email','Departemen','Jabatan','Status Kepegawaian','Pendidikan','Tgl Masuk','Tgl Keluar','Gaji','Status']);
    foreach ($rows as $r) fputcsv($out, [$r['nip'],$r['full_name'],$r['nik'],$r['nuptk'],$r['gender'],$r['birth_place'],$r['birth_date'],$r['religion'],$r['marital_status'],$r['address'],$r['phone'],$r['email'],$r['dept'],$r['position'],$r['employment_status'],$r['education_level'],$r['join_date'],$r['end_date'],$r['salary'],$r['status']]);
    fclose($out);
    ActivityLog::create(Auth::id(), 'export', 'Export CSV data pegawai (' . count($rows) . ' baris)');
    exit;
});

// =====================================================
// ██   ██ ███████ ██    ██ ███████ ██ ███    ██  ██████
// ██  ██  ██      ██    ██ ██      ██ ████   ██ ██
// █████   █████   ██    ██ █████   ██ ██ ██  ██ ██   ███
// ██  ██  ██      ██    ██ ██      ██ ██  ██ ██ ██    ██
// ██   ██ ███████  ██████  ███████ ██ ██   ████  ██████
// =====================================================

Router::get('/admin/keuangan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_keuangan']);
    $db = Database::getInstance();
    $search = trim(Security::sanitize($_GET['q'] ?? ''));
    $page = max(1, (int)($_GET['page'] ?? 1));
    $fType = $_GET['type'] ?? '';
    $fStatus = $_GET['status'] ?? '';
    $where = 't.deleted_at IS NULL'; $params = [];
    if ($search !== '') { $where .= ' AND (t.transaction_number LIKE ? OR t.description LIKE ?)'; $l = "%{$search}%"; array_push($params, $l, $l); }
    if (in_array($fType, ['pemasukan', 'pengeluaran'], true)) { $where .= ' AND t.type = ?'; $params[] = $fType; }
    if (in_array($fStatus, ['pending', 'approved', 'rejected', 'cancelled'], true)) { $where .= ' AND t.status = ?'; $params[] = $fStatus; }
    $total = (int)($db->fetch("SELECT COUNT(*) AS c FROM transaksi_keuangan t WHERE {$where}", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total / PER_PAGE));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * PER_PAGE;
    $transaksi = $db->fetchAll("SELECT t.*, ck.name AS category_name, d.name AS dept_name, u.full_name AS created_by_name FROM transaksi_keuangan t LEFT JOIN categories_keuangan ck ON t.category_id = ck.id LEFT JOIN departments d ON t.department_id = d.id LEFT JOIN users u ON t.created_by = u.id WHERE {$where} ORDER BY t.transaction_date DESC, t.id DESC LIMIT " . PER_PAGE . " OFFSET " . $offset, $params);
    $summary = [
        'pemasukan'   => $db->fetch("SELECT COALESCE(SUM(amount),0) AS t FROM transaksi_keuangan WHERE type='pemasukan' AND status='approved' AND deleted_at IS NULL")['t'] ?? 0,
        'pengeluaran' => $db->fetch("SELECT COALESCE(SUM(amount),0) AS t FROM transaksi_keuangan WHERE type='pengeluaran' AND status='approved' AND deleted_at IS NULL")['t'] ?? 0,
        'pending'     => $db->count('transaksi_keuangan', "status='pending' AND deleted_at IS NULL"),
    ];
    View::renderWithLayout('admin', 'admin/keuangan/index', [
        'title' => 'Transaksi Keuangan', 'transaksi' => $transaksi, 'summary' => $summary,
        'categories' => $db->fetchAll("SELECT * FROM categories_keuangan ORDER BY type, name"),
        'departments' => $db->fetchAll("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name"),
        'pagination' => ['page' => $page, 'totalPages' => $totalPages, 'total' => $total, 'search' => $search, 'type' => $fType, 'status' => $fStatus],
    ]);
});

Router::get('/admin/keuangan/tambah', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_keuangan']);
    $db = Database::getInstance();
    View::renderWithLayout('admin', 'admin/keuangan/form', [
        'title' => 'Input Transaksi', 'mode' => 'add', 'data' => null,
        'categories' => $db->fetchAll("SELECT * FROM categories_keuangan ORDER BY type, name"),
        'departments' => $db->fetchAll("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name"),
    ]);
});

Router::get('/admin/keuangan/edit/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_keuangan']);
    $data = Database::getInstance()->fetch("SELECT * FROM transaksi_keuangan WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
    if (!$data) { flash('error', 'Transaksi tidak ditemukan.'); header('Location: ' . url('admin/keuangan')); exit; }
    $db = Database::getInstance();
    View::renderWithLayout('admin', 'admin/keuangan/form', [
        'title' => 'Edit Transaksi', 'mode' => 'edit', 'data' => $data,
        'categories' => $db->fetchAll("SELECT * FROM categories_keuangan ORDER BY type, name"),
        'departments' => $db->fetchAll("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name"),
    ]);
});

Router::post('/admin/keuangan/simpan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_keuangan']);
    $db = Database::getInstance();
    $id = max(0, (int)($_POST['id'] ?? 0));
    $type = $_POST['type'] ?? '';
    $errors = [];
    if (!in_array($type, ['pemasukan', 'pengeluaran'], true)) $errors[] = 'Tipe transaksi tidak valid';
    $date = Security::sanitize($_POST['transaction_date'] ?? '');
    if (!$date) $errors[] = 'Tanggal transaksi wajib diisi';
    $amount = (float)preg_replace('/[^0-9.]/', '', $_POST['amount'] ?? '0');
    if ($amount <= 0) $errors[] = 'Jumlah harus lebih besar dari 0';
    $desc = Security::cleanInput($_POST['description'] ?? '');
    if ($desc === '') $errors[] = 'Deskripsi wajib diisi';
    $ref = Security::sanitize($_POST['reference_number'] ?? '');
    $catId = max(0, (int)($_POST['category_id'] ?? 0)) ?: null;
    $deptId = max(0, (int)($_POST['department_id'] ?? 0)) ?: null;
    $method = $_POST['payment_method'] ?? 'cash';
    if (!in_array($method, ['cash','transfer','check','giro'], true)) $method = 'cash';
    // Attachment
    $existingAttach = $id ? ($db->fetch("SELECT attachment FROM transaksi_keuangan WHERE id = ?", [$id])['attachment'] ?? null) : null;
    $attachName = $existingAttach;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $errs = Security::validateFileUpload($_FILES['attachment'], ['pdf', 'jpg', 'jpeg', 'png'], 5 * 1024 * 1024);
        if ($errs) $errors = array_merge($errors, $errs);
        else {
            $dir = ensureUploadDir('keuangan');
            $new = safeFilename($_FILES['attachment']['name']);
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dir . $new)) $attachName = $new;
        }
    }
    if ($errors) { $_SESSION['form_old'] = $_POST; $_SESSION['flash_error'] = $errors; header('Location: ' . url($id ? 'admin/keuangan/edit/' . $id : 'admin/keuangan/tambah')); exit; }
    $trxNumber = $id ? $db->fetch("SELECT transaction_number FROM transaksi_keuangan WHERE id = ?", [$id])['transaction_number'] : strtoupper('TRX-' . date('YmdHis') . '-' . mt_rand(100, 999));
    $payload = [
        'transaction_number' => $trxNumber, 'transaction_date' => $date, 'type' => $type,
        'category_id' => $catId, 'department_id' => $deptId, 'amount' => $amount,
        'description' => $desc, 'reference_number' => $ref ?: null, 'payment_method' => $method,
        'attachment' => $attachName, 'updated_by' => Auth::id(),
    ];
    if ($id > 0) {
        $db->update('transaksi_keuangan', $payload, 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'update', 'Update transaksi: ' . $trxNumber, 'transaksi_keuangan', $id);
        flash('success', 'Transaksi "' . $trxNumber . '" berhasil diperbarui.');
    } else {
        $payload['uuid'] = Security::generateUuid();
        $payload['created_by'] = Auth::id();
        $payload['status'] = 'pending';
        $id = $db->insert('transaksi_keuangan', $payload);
        ActivityLog::create(Auth::id(), 'create', 'Transaksi baru: ' . $trxNumber, 'transaksi_keuangan', $id);
        flash('success', 'Transaksi "' . $trxNumber . '" berhasil ditambahkan dan menunggu persetujuan.');
    }
    clearOld();
    header('Location: ' . url('admin/keuangan')); exit;
});

Router::get('/admin/keuangan/approve/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_keuangan']);
    $db = Database::getInstance();
    $id = (int)$id;
    $trx = $db->fetch("SELECT transaction_number FROM transaksi_keuangan WHERE id = ? AND status = 'pending' AND deleted_at IS NULL", [$id]);
    if ($trx) {
        $db->update('transaksi_keuangan', ['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'approve', 'Approve transaksi: ' . $trx['transaction_number'], 'transaksi_keuangan', $id);
        flash('success', 'Transaksi "' . $trx['transaction_number'] . '" berhasil disetujui.');
    }
    header('Location: ' . url('admin/keuangan')); exit;
});

Router::get('/admin/keuangan/reject/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_keuangan']);
    $db = Database::getInstance();
    $id = (int)$id;
    $reason = Security::sanitize($_GET['reason'] ?? 'Tidak memenuhi persyaratan');
    $trx = $db->fetch("SELECT transaction_number FROM transaksi_keuangan WHERE id = ? AND status = 'pending' AND deleted_at IS NULL", [$id]);
    if ($trx) {
        $db->update('transaksi_keuangan', ['status' => 'rejected', 'approved_by' => Auth::id(), 'rejected_reason' => $reason], 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'reject', 'Reject transaksi: ' . $trx['transaction_number'], 'transaksi_keuangan', $id);
        flash('success', 'Transaksi "' . $trx['transaction_number'] . '" ditolak.');
    }
    header('Location: ' . url('admin/keuangan')); exit;
});

Router::get('/admin/keuangan/hapus/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_keuangan']);
    $trx = Database::getInstance()->fetch("SELECT transaction_number FROM transaksi_keuangan WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
    if ($trx) {
        Database::getInstance()->update('transaksi_keuangan', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$id]);
        ActivityLog::create(Auth::id(), 'delete', 'Soft delete transaksi: ' . $trx['transaction_number'], 'transaksi_keuangan', (int)$id);
        flash('success', 'Transaksi "' . $trx['transaction_number'] . '" dipindahkan ke tempat sampah.');
    }
    header('Location: ' . url('admin/keuangan')); exit;
});

Router::get('/admin/keuangan/export', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_keuangan']);
    $rows = Database::getInstance()->fetchAll("SELECT t.*, ck.name AS category, d.name AS dept FROM transaksi_keuangan t LEFT JOIN categories_keuangan ck ON t.category_id = ck.id LEFT JOIN departments d ON t.department_id = d.id WHERE t.deleted_at IS NULL ORDER BY t.transaction_date DESC");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="transaksi-' . date('Ymd-His') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['No. Transaksi','Tanggal','Tipe','Kategori','Departemen','Jumlah','Deskripsi','Metode Bayar','Status']);
    foreach ($rows as $r) fputcsv($out, [$r['transaction_number'],$r['transaction_date'],$r['type'],$r['category'],$r['dept'],$r['amount'],$r['description'],$r['payment_method'],$r['status']]);
    fclose($out);
    ActivityLog::create(Auth::id(), 'export', 'Export CSV transaksi (' . count($rows) . ' baris)');
    exit;
});

// =====================================================
//  █████  ███████ ████████
// ██   ██ ██         ██
// ███████ ███████    ██
// ██   ██      ██    ██
// ██   ██ ███████    ██
// =====================================================

Router::get('/admin/aset', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_aset']);
    $db = Database::getInstance();
    $search = trim(Security::sanitize($_GET['q'] ?? ''));
    $page = max(1, (int)($_GET['page'] ?? 1));
    $fCondition = $_GET['condition'] ?? '';
    $fCategory = max(0, (int)($_GET['cat'] ?? 0));
    $where = 'a.deleted_at IS NULL'; $params = [];
    if ($search !== '') { $where .= ' AND (a.asset_code LIKE ? OR a.name LIKE ? OR a.brand LIKE ?)'; $l = "%{$search}%"; array_push($params, $l, $l, $l); }
    if (in_array($fCondition, ['baik','rusak_ringan','rusak_berat','hilang','dihapuskan'], true)) { $where .= ' AND a.`condition` = ?'; $params[] = $fCondition; }
    if ($fCategory > 0) { $where .= ' AND a.category_id = ?'; $params[] = $fCategory; }
    $total = (int)($db->fetch("SELECT COUNT(*) AS c FROM aset a WHERE {$where}", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total / PER_PAGE));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * PER_PAGE;
    $aset = $db->fetchAll("SELECT a.*, ca.name AS category_name, d.name AS dept_name FROM aset a LEFT JOIN categories_aset ca ON a.category_id = ca.id LEFT JOIN departments d ON a.department_id = d.id WHERE {$where} ORDER BY a.asset_code ASC LIMIT " . PER_PAGE . " OFFSET " . $offset, $params);
    View::renderWithLayout('admin', 'admin/aset/index', [
        'title' => 'Manajemen Aset', 'aset' => $aset,
        'categories' => $db->fetchAll("SELECT id, name FROM categories_aset ORDER BY name"),
        'departments' => $db->fetchAll("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name"),
        'trashCount' => $db->count('aset', 'deleted_at IS NOT NULL'),
        'pagination' => ['page' => $page, 'totalPages' => $totalPages, 'total' => $total, 'search' => $search, 'condition' => $fCondition, 'cat' => $fCategory],
    ]);
});

Router::get('/admin/aset/trash', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_aset']);
    $trash = Database::getInstance()->fetchAll("SELECT a.*, ca.name AS category_name FROM aset a LEFT JOIN categories_aset ca ON a.category_id = ca.id WHERE a.deleted_at IS NOT NULL ORDER BY a.deleted_at DESC");
    View::renderWithLayout('admin', 'admin/aset/trash', ['title' => 'Tempat Sampah Aset', 'trash' => $trash]);
});

Router::get('/admin/aset/tambah', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_aset']);
    $db = Database::getInstance();
    View::renderWithLayout('admin', 'admin/aset/form', [
        'title' => 'Tambah Aset', 'mode' => 'add', 'data' => null,
        'categories' => $db->fetchAll("SELECT id, name FROM categories_aset ORDER BY name"),
        'departments' => $db->fetchAll("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name"),
    ]);
});

Router::get('/admin/aset/edit/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_aset']);
    $data = Database::getInstance()->fetch("SELECT * FROM aset WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
    if (!$data) { flash('error', 'Aset tidak ditemukan.'); header('Location: ' . url('admin/aset')); exit; }
    $db = Database::getInstance();
    View::renderWithLayout('admin', 'admin/aset/form', [
        'title' => 'Edit Aset', 'mode' => 'edit', 'data' => $data,
        'categories' => $db->fetchAll("SELECT id, name FROM categories_aset ORDER BY name"),
        'departments' => $db->fetchAll("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name"),
    ]);
});

Router::post('/admin/aset/simpan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_aset']);
    $db = Database::getInstance();
    $id = max(0, (int)($_POST['id'] ?? 0));
    $errors = [];
    $assetCode = strtoupper(Security::sanitize($_POST['asset_code'] ?? ''));
    $name = Security::sanitize($_POST['name'] ?? '');
    if ($assetCode === '') $errors[] = 'Kode aset wajib diisi';
    if ($name === '') $errors[] = 'Nama aset wajib diisi';
    $condition = $_POST['condition'] ?? 'baik';
    if (!in_array($condition, ['baik','rusak_ringan','rusak_berat','hilang','dihapuskan'], true)) $condition = 'baik';
    $dup = $db->fetch("SELECT id FROM aset WHERE asset_code = ? AND deleted_at IS NULL AND id != ?", [$assetCode, $id]);
    if ($dup) $errors[] = 'Kode aset "' . $assetCode . '" sudah digunakan';
    // Photo
    $existingPhoto = $id ? ($db->fetch("SELECT photo FROM aset WHERE id = ?", [$id])['photo'] ?? null) : null;
    $photoName = $existingPhoto;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $errs = Security::validateFileUpload($_FILES['photo'], ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
        if ($errs) $errors = array_merge($errors, $errs);
        else {
            $dir = ensureUploadDir('aset');
            $new = safeFilename($_FILES['photo']['name']);
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $new)) $photoName = $new;
        }
    }
    if ($errors) { $_SESSION['form_old'] = $_POST; $_SESSION['flash_error'] = $errors; header('Location: ' . url($id ? 'admin/aset/edit/' . $id : 'admin/aset/tambah')); exit; }
    $payload = [
        'asset_code' => $assetCode, 'name' => $name,
        'category_id' => max(0, (int)($_POST['category_id'] ?? 0)) ?: null,
        'department_id' => max(0, (int)($_POST['department_id'] ?? 0)) ?: null,
        'brand' => Security::sanitize($_POST['brand'] ?? '') ?: null,
        'model' => Security::sanitize($_POST['model'] ?? '') ?: null,
        'serial_number' => Security::sanitize($_POST['serial_number'] ?? '') ?: null,
        'purchase_date' => Security::sanitize($_POST['purchase_date'] ?? '') ?: null,
        'purchase_price' => (float)preg_replace('/[^0-9.]/', '', $_POST['purchase_price'] ?? '0'),
        'current_value' => (float)preg_replace('/[^0-9.]/', '', $_POST['current_value'] ?? '0'),
        'location' => Security::sanitize($_POST['location'] ?? '') ?: null,
        'condition' => $condition,
        'quantity' => max(1, (int)($_POST['quantity'] ?? 1)),
        'unit' => Security::sanitize($_POST['unit'] ?? 'unit') ?: 'unit',
        'description' => Security::cleanInput($_POST['description'] ?? '') ?: null,
        'photo' => $photoName,
        'responsible_person' => Security::sanitize($_POST['responsible_person'] ?? '') ?: null,
        'warranty_until' => Security::sanitize($_POST['warranty_until'] ?? '') ?: null,
        'status' => $_POST['status'] ?? 'active',
        'updated_by' => Auth::id(),
    ];
    if ($id > 0) {
        $db->update('aset', $payload, 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'update', 'Update aset: ' . $assetCode, 'aset', $id);
        flash('success', 'Aset "' . $name . '" berhasil diperbarui.');
    } else {
        $payload['uuid'] = Security::generateUuid();
        $payload['created_by'] = Auth::id();
        $id = $db->insert('aset', $payload);
        ActivityLog::create(Auth::id(), 'create', 'Tambah aset: ' . $assetCode, 'aset', $id);
        flash('success', 'Aset "' . $name . '" berhasil ditambahkan.');
    }
    clearOld();
    header('Location: ' . url('admin/aset')); exit;
});

Router::get('/admin/aset/hapus/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_aset']);
    $a = Database::getInstance()->fetch("SELECT name FROM aset WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
    if ($a) {
        Database::getInstance()->update('aset', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$id]);
        ActivityLog::create(Auth::id(), 'delete', 'Soft delete aset: ' . $a['name'], 'aset', (int)$id);
        flash('success', 'Aset "' . $a['name'] . '" dipindahkan ke tempat sampah.');
    }
    header('Location: ' . url('admin/aset')); exit;
});

Router::get('/admin/aset/pulihkan/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_aset']);
    $a = Database::getInstance()->fetch("SELECT name FROM aset WHERE id = ? AND deleted_at IS NOT NULL", [(int)$id]);
    if ($a) {
        Database::getInstance()->update('aset', ['deleted_at' => null], 'id = ?', [(int)$id]);
        flash('success', 'Aset "' . $a['name'] . '" berhasil dipulihkan.');
    }
    header('Location: ' . url('admin/aset/trash')); exit;
});

// =====================================================
// ███    ███  ██████  ██████  ██  █████  ███████ ██  ██████  ███████
// ████  ████ ██   ██ ██   ██ ██ ██   ██ ██      ██ ██       ██
// ██ ████ ██ ███████ ██████  ██ ███████ █████   ██ ██   ███ █████
// ██  ██  ██ ██   ██ ██   ██ ██ ██   ██ ██      ██ ██    ██ ██
// ██      ██ ██   ██ ██   ██ ██ ██   ██ ██      ██  ██████  ███████
// =====================================================

Router::get('/admin/maintenance-aset', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_aset']);
    $maintenances = Database::getInstance()->fetchAll("SELECT m.*, a.asset_code, a.name AS asset_name FROM maintenance_aset m LEFT JOIN aset a ON m.aset_id = a.id ORDER BY m.maintenance_date DESC LIMIT 200");
    View::renderWithLayout('admin', 'admin/maintenance/index', ['title' => 'Pemeliharaan Aset', 'maintenances' => $maintenances]);
});

Router::post('/admin/maintenance-aset/simpan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_aset']);
    $errors = [];
    $asetId = max(0, (int)($_POST['aset_id'] ?? 0));
    if ($asetId === 0) $errors[] = 'Pilih aset yang dipelihara';
    $date = Security::sanitize($_POST['maintenance_date'] ?? '');
    if (!$date) $errors[] = 'Tanggal pemeliharaan wajib diisi';
    $mtype = $_POST['maintenance_type'] ?? 'rutin';
    if (!in_array($mtype, ['rutin','perbaikan','penggantian_part','kalibrasi'], true)) $mtype = 'rutin';
    $desc = Security::cleanInput($_POST['description'] ?? '');
    if ($desc === '') $errors[] = 'Deskripsi wajib diisi';
    $cost = (float)preg_replace('/[^0-9.]/', '', $_POST['cost'] ?? '0');
    if ($errors) { $_SESSION['flash_error'] = $errors; header('Location: ' . url('admin/maintenance-aset')); exit; }
    Database::getInstance()->insert('maintenance_aset', [
        'uuid' => Security::generateUuid(), 'aset_id' => $asetId, 'maintenance_date' => $date,
        'maintenance_type' => $mtype, 'description' => $desc, 'cost' => $cost,
        'performed_by' => Security::sanitize($_POST['performed_by'] ?? '') ?: null,
        'next_maintenance_date' => Security::sanitize($_POST['next_maintenance_date'] ?? '') ?: null,
        'created_by' => Auth::id(),
    ]);
    ActivityLog::create(Auth::id(), 'create', 'Catat pemeliharaan aset ID ' . $asetId, 'maintenance_aset');
    flash('success', 'Data pemeliharaan berhasil dicatat.');
    header('Location: ' . url('admin/maintenance-aset')); exit;
});

// =====================================================
// ██████  ███████ ██████  ██ ████████  █████
// ██   ██ ██      ██   ██ ██    ██    ██   ██
// ██████  █████   ██████  ██    ██    ███████
// ██   ██ ██      ██   ██ ██    ██    ██   ██
// ██████  ███████ ██   ██ ██    ██    ██   ██
// =====================================================

Router::get('/admin/berita', function () {
    Auth::requireAuth();
    $search = trim(Security::sanitize($_GET['q'] ?? ''));
    $page = max(1, (int)($_GET['page'] ?? 1));
    $fStatus = $_GET['status'] ?? '';
    $fCat = $_GET['category'] ?? '';
    $where = 'b.deleted_at IS NULL'; $params = [];
    if ($search !== '') { $where .= ' AND (b.title LIKE ? OR b.content LIKE ?)'; $l = "%{$search}%"; array_push($params, $l, $l); }
    if (in_array($fStatus, ['published', 'draft'], true)) { $where .= ' AND b.is_published = ?'; $params[] = ($fStatus === 'published' ? 1 : 0); }
    if (in_array($fCat, ['pengumuman','berita','kegiatan','pengadaan','regulasi'], true)) { $where .= ' AND b.category = ?'; $params[] = $fCat; }
    $db = Database::getInstance();
    $total = (int)($db->fetch("SELECT COUNT(*) AS c FROM berita b WHERE {$where}", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total / PER_PAGE));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * PER_PAGE;
    $berita = $db->fetchAll("SELECT b.*, u.full_name AS author_name FROM berita b LEFT JOIN users u ON b.author_id = u.id WHERE {$where} ORDER BY b.created_at DESC LIMIT " . PER_PAGE . " OFFSET " . $offset, $params);
    View::renderWithLayout('admin', 'admin/berita/index', [
        'title' => 'Kelola Berita', 'berita' => $berita,
        'pagination' => ['page' => $page, 'totalPages' => $totalPages, 'total' => $total, 'search' => $search, 'status' => $fStatus, 'category' => $fCat],
    ]);
});

Router::get('/admin/berita/tambah', function () {
    Auth::requireAuth();
    View::renderWithLayout('admin', 'admin/berita/form', ['title' => 'Tulis Berita', 'mode' => 'add', 'data' => null]);
});

Router::get('/admin/berita/edit/{id}', function (string $id) {
    Auth::requireAuth();
    $data = Database::getInstance()->fetch("SELECT * FROM berita WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
    if (!$data) { flash('error', 'Berita tidak ditemukan.'); header('Location: ' . url('admin/berita')); exit; }
    View::renderWithLayout('admin', 'admin/berita/form', ['title' => 'Edit Berita', 'mode' => 'edit', 'data' => $data]);
});

Router::post('/admin/berita/simpan', function () {
    Auth::requireAuth();
    $db = Database::getInstance();
    $id = max(0, (int)($_POST['id'] ?? 0));
    $errors = [];
    $title = Security::sanitize($_POST['title'] ?? '');
    $content = Security::cleanInput($_POST['content'] ?? '');
    $excerpt = Security::cleanInput($_POST['excerpt'] ?? '');
    $category = $_POST['category'] ?? 'berita';
    if ($title === '') $errors[] = 'Judul wajib diisi';
    if ($content === '') $errors[] = 'Konten wajib diisi';
    if (!in_array($category, ['pengumuman','berita','kegiatan','pengadaan','regulasi'], true)) $category = 'berita';
    // Generate slug
    $baseSlug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
    $baseSlug = trim($baseSlug, '-');
    $slug = $baseSlug ?: ('berita-' . time());
    $n = 1; $test = $slug;
    while ($db->fetch("SELECT id FROM berita WHERE slug = ? AND id != ?", [$test, $id])) { $test = $slug . '-' . (++$n); }
    $slug = $test;
    // Image
    $existingImg = $id ? ($db->fetch("SELECT featured_image FROM berita WHERE id = ?", [$id])['featured_image'] ?? null) : null;
    $imgName = $existingImg;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $errs = Security::validateFileUpload($_FILES['featured_image'], ['jpg', 'jpeg', 'png', 'webp'], 3 * 1024 * 1024);
        if ($errs) $errors = array_merge($errors, $errs);
        else {
            $dir = ensureUploadDir('berita');
            $new = safeFilename($_FILES['featured_image']['name']);
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $dir . $new)) $imgName = $new;
        }
    }
    if ($errors) { $_SESSION['form_old'] = $_POST; $_SESSION['flash_error'] = $errors; header('Location: ' . url($id ? 'admin/berita/edit/' . $id : 'admin/berita/tambah')); exit; }
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    $payload = [
        'title' => $title, 'slug' => $slug, 'content' => $content, 'excerpt' => $excerpt ?: mb_substr(strip_tags($content), 0, 200),
        'featured_image' => $imgName, 'category' => $category, 'is_published' => $isPublished,
        'published_at' => $isPublished ? date('Y-m-d H:i:s') : null,
        'meta_title' => Security::sanitize($_POST['meta_title'] ?? '') ?: $title,
        'meta_description' => Security::sanitize($_POST['meta_description'] ?? '') ?: mb_substr(strip_tags($content), 0, 150),
        'tags' => Security::sanitize($_POST['tags'] ?? '') ?: null,
        'author_id' => Auth::id(), 'updated_by' => Auth::id(),
    ];
    if ($id > 0) {
        $db->update('berita', $payload, 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'update', 'Update berita: ' . $title, 'berita', $id);
        flash('success', 'Berita "' . $title . '" berhasil diperbarui.');
    } else {
        $payload['uuid'] = Security::generateUuid();
        $payload['created_by'] = Auth::id();
        $id = $db->insert('berita', $payload);
        ActivityLog::create(Auth::id(), 'create', 'Berita baru: ' . $title, 'berita', $id);
        flash('success', 'Berita "' . $title . '" berhasil disimpan.');
    }
    clearOld();
    header('Location: ' . url('admin/berita')); exit;
});

Router::get('/admin/berita/hapus/{id}', function (string $id) {
    Auth::requireAuth();
    $b = Database::getInstance()->fetch("SELECT title FROM berita WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
    if ($b) {
        Database::getInstance()->update('berita', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$id]);
        ActivityLog::create(Auth::id(), 'delete', 'Hapus berita: ' . $b['title'], 'berita', (int)$id);
        flash('success', 'Berita "' . $b['title'] . '" dihapus.');
    }
    header('Location: ' . url('admin/berita')); exit;
});

// =====================================================
// ██████  ██   ██  ██████  ██   ██  ██████  ██████
// ██   ██ ██   ██ ██    ██ ███  ██ ██    ██ ██   ██
// ██████  ███████ ██    ██ ██ ██ ██ ██    ██ ██████
// ██      ██   ██ ██    ██ ██  ██ ██ ██    ██ ██   ██
// ██      ██   ██  ██████  ██   ███  ██████  ██   ██
// =====================================================

Router::get('/admin/pengumuman', function () {
    Auth::requireAuth();
    $pengumuman = Database::getInstance()->fetchAll("SELECT * FROM pengumuman ORDER BY created_at DESC");
    View::renderWithLayout('admin', 'admin/pengumuman/index', ['title' => 'Pengumuman', 'pengumuman' => $pengumuman]);
});

Router::get('/admin/pengumuman/tambah', function () {
    Auth::requireAuth();
    View::renderWithLayout('admin', 'admin/pengumuman/form', ['title' => 'Buat Pengumuman', 'mode' => 'add', 'data' => null]);
});

Router::get('/admin/pengumuman/edit/{id}', function (string $id) {
    Auth::requireAuth();
    $data = Database::getInstance()->fetch("SELECT * FROM pengumuman WHERE id = ?", [(int)$id]);
    if (!$data) { flash('error', 'Pengumuman tidak ditemukan.'); header('Location: ' . url('admin/pengumuman')); exit; }
    View::renderWithLayout('admin', 'admin/pengumuman/form', ['title' => 'Edit Pengumuman', 'mode' => 'edit', 'data' => $data]);
});

Router::post('/admin/pengumuman/simpan', function () {
    Auth::requireAuth();
    $db = Database::getInstance();
    $id = max(0, (int)($_POST['id'] ?? 0));
    $errors = [];
    $title = Security::sanitize($_POST['title'] ?? '');
    $content = Security::cleanInput($_POST['content'] ?? '');
    if ($title === '') $errors[] = 'Judul wajib diisi';
    if ($content === '') $errors[] = 'Isi pengumuman wajib diisi';
    $audience = $_POST['target_audience'] ?? 'all';
    if (!in_array($audience, ['all','pegawai','mahasiswa','dosen'], true)) $audience = 'all';
    $priority = $_POST['priority'] ?? 'medium';
    if (!in_array($priority, ['low','medium','high','urgent'], true)) $priority = 'medium';
    // Attachment
    $existingAttach = $id ? ($db->fetch("SELECT attachment FROM pengumuman WHERE id = ?", [$id])['attachment'] ?? null) : null;
    $attachName = $existingAttach;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $errs = Security::validateFileUpload($_FILES['attachment'], ['pdf','doc','docx'], 5 * 1024 * 1024);
        if ($errs) $errors = array_merge($errors, $errs);
        else {
            $dir = ensureUploadDir('pengumuman');
            $new = safeFilename($_FILES['attachment']['name']);
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dir . $new)) $attachName = $new;
        }
    }
    if ($errors) { $_SESSION['form_old'] = $_POST; $_SESSION['flash_error'] = $errors; header('Location: ' . url($id ? 'admin/pengumuman/edit/' . $id : 'admin/pengumuman/tambah')); exit; }
    $payload = [
        'title' => $title, 'content' => $content,
        'target_audience' => $audience, 'priority' => $priority,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'start_date' => Security::sanitize($_POST['start_date'] ?? '') ?: null,
        'end_date' => Security::sanitize($_POST['end_date'] ?? '') ?: null,
        'attachment' => $attachName,
    ];
    if ($id > 0) {
        $db->update('pengumuman', $payload, 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'update', 'Update pengumuman: ' . $title, 'pengumuman', $id);
        flash('success', 'Pengumuman "' . $title . '" berhasil diperbarui.');
    } else {
        $payload['uuid'] = Security::generateUuid();
        $payload['created_by'] = Auth::id();
        $id = $db->insert('pengumuman', $payload);
        ActivityLog::create(Auth::id(), 'create', 'Pengumuman baru: ' . $title, 'pengumuman', $id);
        flash('success', 'Pengumuman "' . $title . '" berhasil dibuat.');
    }
    clearOld();
    header('Location: ' . url('admin/pengumuman')); exit;
});

Router::get('/admin/pengumuman/hapus/{id}', function (string $id) {
    Auth::requireAuth();
    $p = Database::getInstance()->fetch("SELECT title, attachment FROM pengumuman WHERE id = ?", [(int)$id]);
    if ($p) {
        if (!empty($p['attachment'])) { $f = UPLOAD_DIR . 'pengumuman/' . basename($p['attachment']); if (file_exists($f)) @unlink($f); }
        Database::getInstance()->delete('pengumuman', 'id = ?', [(int)$id]);
        ActivityLog::create(Auth::id(), 'delete', 'Hapus pengumuman: ' . $p['title'], 'pengumuman', (int)$id);
        flash('success', 'Pengumuman "' . $p['title'] . '" dihapus.');
    }
    header('Location: ' . url('admin/pengumuman')); exit;
});

// =====================================================
// ██████  ███████  ██████  ██    ██  █████  ██ ███    ██  ██████
// ██   ██ ██      ██    ██ ██    ██ ██   ██ ██ ████   ██ ██
// ██████  █████   ██    ██ ██    ██ ███████ ██ ██ ██  ██ ██   ███
// ██   ██ ██      ██ ▄██ ██ ██    ██ ██   ██ ██ ██  ██ ██ ██    ██
// ██   ██ ███████  ██████   ██████  ██   ██ ██ ██   ████  ██████
// =====================================================

Router::get('/admin/regulasi', function () {
    Auth::requireAuth();
    $dokumen = Database::getInstance()->fetchAll("SELECT * FROM dokumen_regulasi ORDER BY created_at DESC");
    View::renderWithLayout('admin', 'admin/regulasi/index', ['title' => 'Regulasi', 'dokumen' => $dokumen]);
});

Router::get('/admin/regulasi/tambah', function () {
    Auth::requireAuth();
    View::renderWithLayout('admin', 'admin/regulasi/form', ['title' => 'Upload Regulasi', 'mode' => 'add', 'data' => null]);
});

Router::get('/admin/regulasi/edit/{id}', function (string $id) {
    Auth::requireAuth();
    $data = Database::getInstance()->fetch("SELECT * FROM dokumen_regulasi WHERE id = ?", [(int)$id]);
    if (!$data) { flash('error', 'Dokumen tidak ditemukan.'); header('Location: ' . url('admin/regulasi')); exit; }
    View::renderWithLayout('admin', 'admin/regulasi/form', ['title' => 'Edit Regulasi', 'mode' => 'edit', 'data' => $data]);
});

Router::post('/admin/regulasi/simpan', function () {
    Auth::requireAuth();
    $db = Database::getInstance();
    $id = max(0, (int)($_POST['id'] ?? 0));
    $errors = [];
    $title = Security::sanitize($_POST['title'] ?? '');
    $docNum = Security::sanitize($_POST['document_number'] ?? '');
    $category = $_POST['category'] ?? 'peraturan';
    if (!in_array($category, ['peraturan','keputusan','surat_edaran','sop','pedoman'], true)) $category = 'peraturan';
    if ($title === '') $errors[] = 'Judul wajib diisi';
    $description = Security::cleanInput($_POST['description'] ?? '');
    $effectiveDate = Security::sanitize($_POST['effective_date'] ?? '') ?: null;
    $status = $_POST['status'] ?? 'active';
    if (!in_array($status, ['active','expired','revoked'], true)) $status = 'active';
    // File
    $existingFile = $id ? ($db->fetch("SELECT file_path FROM dokumen_regulasi WHERE id = ?", [$id])['file_path'] ?? null) : null;
    $filePath = $existingFile;
    $fileSize = $id ? ($db->fetch("SELECT file_size FROM dokumen_regulasi WHERE id = ?", [$id])['file_size'] ?? null) : null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $errs = Security::validateFileUpload($_FILES['file'], ['pdf','doc','docx','xls','xlsx'], 10 * 1024 * 1024);
        if ($errs) $errors = array_merge($errors, $errs);
        else {
            $dir = ensureUploadDir('dokumen');
            $new = safeFilename($_FILES['file']['name']);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $dir . $new)) {
                $filePath = $new;
                $fileSize = $_FILES['file']['size'];
            }
        }
    }
    if (!$filePath) $errors[] = 'File dokumen wajib diunggah';
    if ($errors) { $_SESSION['form_old'] = $_POST; $_SESSION['flash_error'] = $errors; header('Location: ' . url($id ? 'admin/regulasi/edit/' . $id : 'admin/regulasi/tambah')); exit; }
    $payload = [
        'title' => $title, 'document_number' => $docNum ?: null, 'category' => $category,
        'description' => $description ?: null, 'file_path' => $filePath, 'file_size' => $fileSize,
        'effective_date' => $effectiveDate, 'status' => $status,
    ];
    if ($id > 0) {
        $db->update('dokumen_regulasi', $payload, 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'update', 'Update regulasi: ' . $title, 'dokumen_regulasi', $id);
        flash('success', 'Regulasi "' . $title . '" berhasil diperbarui.');
    } else {
        $payload['uuid'] = Security::generateUuid();
        $payload['created_by'] = Auth::id();
        $id = $db->insert('dokumen_regulasi', $payload);
        ActivityLog::create(Auth::id(), 'create', 'Regulasi baru: ' . $title, 'dokumen_regulasi', $id);
        flash('success', 'Regulasi "' . $title . '" berhasil diunggah.');
    }
    clearOld();
    header('Location: ' . url('admin/regulasi')); exit;
});

Router::get('/admin/regulasi/hapus/{id}', function (string $id) {
    Auth::requireAuth();
    $d = Database::getInstance()->fetch("SELECT title, file_path FROM dokumen_regulasi WHERE id = ?", [(int)$id]);
    if ($d) {
        if (!empty($d['file_path'])) { $f = UPLOAD_DIR . 'dokumen/' . basename($d['file_path']); if (file_exists($f)) @unlink($f); }
        Database::getInstance()->delete('dokumen_regulasi', 'id = ?', [(int)$id]);
        ActivityLog::create(Auth::id(), 'delete', 'Hapus regulasi: ' . $d['title'], 'dokumen_regulasi', (int)$id);
        flash('success', 'Regulasi "' . $d['title'] . '" dihapus.');
    }
    header('Location: ' . url('admin/regulasi')); exit;
});

// =====================================================
// ██   ██  ██████  ██ ███    ██ ████████
// ██  ██  ██    ██ ██ ████   ██    ██
// █████   ██    ██ ██ ██ ██  ██    ██
// ██  ██  ██    ██ ██ ██  ██ ██    ██
// ██   ██  ██████  ██ ██   ████    ██
// =====================================================

Router::get('/admin/kontak', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $search = trim(Security::sanitize($_GET['q'] ?? ''));
    $fRead = $_GET['read'] ?? '';
    $where = '1=1'; $params = [];
    if ($search !== '') { $where .= ' AND (name LIKE ? OR email LIKE ? OR subject LIKE ?)'; $l = "%{$search}%"; array_push($params, $l, $l, $l); }
    if ($fRead === 'unread') $where .= ' AND is_read = 0';
    if ($fRead === 'read') $where .= ' AND is_read = 1';
    $messages = Database::getInstance()->fetchAll("SELECT * FROM contact_messages WHERE {$where} ORDER BY is_read ASC, created_at DESC LIMIT 100", $params);
    View::renderWithLayout('admin', 'admin/kontak/index', ['title' => 'Pesan Kontak', 'messages' => $messages, 'search' => $search, 'read' => $fRead]);
});

Router::get('/admin/kontak/baca/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $msg = Database::getInstance()->fetch("SELECT * FROM contact_messages WHERE id = ?", [(int)$id]);
    if ($msg) Database::getInstance()->update('contact_messages', ['is_read' => 1], 'id = ?', [(int)$id]);
    View::renderWithLayout('admin', 'admin/kontak/detail', ['title' => 'Detail Pesan', 'msg' => $msg]);
});

Router::get('/admin/kontak/hapus/{id}', function (string $id) {
    Auth::requireRole('super_admin');
    Database::getInstance()->delete('contact_messages', 'id = ?', [(int)$id]);
    ActivityLog::create(Auth::id(), 'delete', 'Hapus pesan kontak ID: ' . $id);
    flash('success', 'Pesan berhasil dihapus');
    header('Location: ' . url('admin/kontak')); exit;
});

// =====================================================
// ██   ██ ███████  █████  ██████  ██ ███    ██ ███████
// ██  ██  ██      ██   ██ ██   ██ ██ ████   ██ ██
// █████   █████   ███████ ██   ██ ██ ██ ██  ██ ███████
// ██  ██  ██      ██   ██ ██   ██ ██ ██  ██ ██      ██
// ██   ██ ███████ ██   ██ ██████  ██ ██   ████ ███████
// =====================================================

Router::get('/admin/kegiatan', function () {
    Auth::requireAuth();
    $kegiatan = Database::getInstance()->fetchAll("SELECT k.*, d.name AS dept_name FROM kegiatan k LEFT JOIN departments d ON k.department_id = d.id ORDER BY k.start_datetime DESC");
    View::renderWithLayout('admin', 'admin/kegiatan/index', ['title' => 'Agenda Kegiatan', 'kegiatan' => $kegiatan]);
});

Router::post('/admin/kegiatan/simpan', function () {
    Auth::requireAuth();
    $errors = [];
    $title = Security::sanitize($_POST['title'] ?? '');
    if ($title === '') $errors[] = 'Judul kegiatan wajib diisi';
    $start = Security::sanitize($_POST['start_datetime'] ?? '');
    if (!$start) $errors[] = 'Tanggal mulai wajib diisi';
    if ($errors) { $_SESSION['flash_error'] = $errors; header('Location: ' . url('admin/kegiatan')); exit; }
    Database::getInstance()->insert('kegiatan', [
        'uuid' => Security::generateUuid(), 'title' => $title,
        'description' => Security::cleanInput($_POST['description'] ?? '') ?: null,
        'location' => Security::sanitize($_POST['location'] ?? '') ?: null,
        'start_datetime' => $start,
        'end_datetime' => Security::sanitize($_POST['end_datetime'] ?? '') ?: null,
        'is_all_day' => isset($_POST['is_all_day']) ? 1 : 0,
        'department_id' => max(0, (int)($_POST['department_id'] ?? 0)) ?: null,
        'organizer' => Security::sanitize($_POST['organizer'] ?? '') ?: null,
        'status' => $_POST['status'] ?? 'planned',
        'created_by' => Auth::id(),
    ]);
    ActivityLog::create(Auth::id(), 'create', 'Agenda baru: ' . $title, 'kegiatan');
    flash('success', 'Agenda "' . $title . '" berhasil ditambahkan.');
    header('Location: ' . url('admin/kegiatan')); exit;
});

Router::get('/admin/kegiatan/hapus/{id}', function (string $id) {
    Auth::requireAuth();
    Database::getInstance()->delete('kegiatan', 'id = ?', [(int)$id]);
    ActivityLog::create(Auth::id(), 'delete', 'Hapus agenda ID: ' . $id);
    flash('success', 'Agenda dihapus.');
    header('Location: ' . url('admin/kegiatan')); exit;
});

// =====================================================
// ██████  ███████ ██████  ███████ ██████  ██ ███    ███
// ██   ██ ██      ██   ██ ██      ██   ██ ██ ████  ████
// ██   ██ █████   ██████  █████   ██████  ██ ██ ████ ██
// ██   ██ ██      ██      ██      ██   ██ ██ ██  ██  ██
// ██████  ███████ ██      ███████ ██   ██ ██ ██      ██
// =====================================================

Router::get('/admin/departemen', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $departemen = Database::getInstance()->fetchAll("SELECT d.*, (SELECT COUNT(*) FROM pegawai WHERE department_id = d.id AND deleted_at IS NULL) AS pegawai_count FROM departments d WHERE d.deleted_at IS NULL ORDER BY d.sort_order ASC, d.name ASC");
    View::renderWithLayout('admin', 'admin/departemen/index', ['title' => 'Departemen', 'departemen' => $departemen]);
});

Router::get('/admin/departemen/tambah', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    View::renderWithLayout('admin', 'admin/departemen/form', ['title' => 'Tambah Departemen', 'mode' => 'add', 'data' => null]);
});

Router::post('/admin/departemen/simpan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $code = strtoupper(Security::sanitize($_POST['code'] ?? ''));
    $name = Security::sanitize($_POST['name'] ?? '');
    $desc = Security::cleanInput($_POST['description'] ?? '');
    $head = Security::sanitize($_POST['head_name'] ?? '');
    if ($code === '' || $name === '') { flash('error', 'Kode dan Nama wajib diisi'); header('Location: ' . url('admin/departemen/tambah')); exit; }
    $db = Database::getInstance();
    if ($db->count('departments', "code = ? AND deleted_at IS NULL", [$code]) > 0) { flash('error', "Kode '{$code}' sudah digunakan"); header('Location: ' . url('admin/departemen/tambah')); exit; }
    $db->insert('departments', ['uuid' => Security::generateUuid(), 'code' => $code, 'name' => $name, 'description' => $desc, 'head_name' => $head, 'status' => 'active']);
    ActivityLog::create(Auth::id(), 'create', "Tambah departemen: {$name}", 'departments');
    flash('success', "Departemen '{$name}' berhasil ditambahkan");
    header('Location: ' . url('admin/departemen')); exit;
});

Router::get('/admin/departemen/hapus/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    Database::getInstance()->update('departments', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$id]);
    ActivityLog::create(Auth::id(), 'delete', 'Hapus departemen ID: ' . $id, 'departments', (int)$id);
    flash('success', 'Departemen dihapus (soft delete)');
    header('Location: ' . url('admin/departemen')); exit;
});

// ===== KATEGORI KEUANGAN =====
Router::get('/admin/kategori-keuangan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_keuangan']);
    $kategori = Database::getInstance()->fetchAll("SELECT * FROM categories_keuangan ORDER BY type, name ASC");
    View::renderWithLayout('admin', 'admin/kategori/index', ['title' => 'Kategori Keuangan', 'kategori' => $kategori, 'tipe' => 'keuangan']);
});

Router::post('/admin/kategori-keuangan/simpan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_keuangan']);
    $code = strtoupper(Security::sanitize($_POST['code'] ?? ''));
    $name = Security::sanitize($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'pengeluaran';
    if ($code === '' || $name === '') { flash('error', 'Kode dan Nama wajib diisi'); header('Location: ' . url('admin/kategori-keuangan')); exit; }
    Database::getInstance()->insert('categories_keuangan', ['uuid' => Security::generateUuid(), 'code' => $code, 'name' => $name, 'type' => $type, 'description' => Security::cleanInput($_POST['description'] ?? '') ?: null, 'status' => 'active']);
    ActivityLog::create(Auth::id(), 'create', "Kategori keuangan: {$name}", 'categories_keuangan');
    flash('success', "Kategori '{$name}' ditambahkan");
    header('Location: ' . url('admin/kategori-keuangan')); exit;
});

// ===== KATEGORI ASET =====
Router::get('/admin/kategori-aset', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_aset']);
    $kategori = Database::getInstance()->fetchAll("SELECT * FROM categories_aset ORDER BY name ASC");
    View::renderWithLayout('admin', 'admin/kategori/index', ['title' => 'Kategori Aset', 'kategori' => $kategori, 'tipe' => 'aset']);
});

Router::post('/admin/kategori-aset/simpan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk', 'admin_aset']);
    $code = strtoupper(Security::sanitize($_POST['code'] ?? ''));
    $name = Security::sanitize($_POST['name'] ?? '');
    $rate = (float)($_POST['depreciation_rate'] ?? 0);
    if ($code === '' || $name === '') { flash('error', 'Kode dan Nama wajib diisi'); header('Location: ' . url('admin/kategori-aset')); exit; }
    Database::getInstance()->insert('categories_aset', ['uuid' => Security::generateUuid(), 'code' => $code, 'name' => $name, 'depreciation_rate' => $rate, 'description' => Security::cleanInput($_POST['description'] ?? '') ?: null, 'status' => 'active']);
    ActivityLog::create(Auth::id(), 'create', "Kategori aset: {$name}", 'categories_aset');
    flash('success', "Kategori aset '{$name}' ditambahkan");
    header('Location: ' . url('admin/kategori-aset')); exit;
});

// =====================================================
// ██       █████   ██████  ██████  ██    ██ ██ ██████
// ██      ██   ██ ██    ██ ██   ██ ██    ██ ██ ██   ██
// ██      ███████ ██    ██ ██████  ██    ██ ██ ██████
// ██      ██   ██ ██    ██ ██      ██    ██ ██ ██
// ███████ ██   ██  ██████  ██       ██████  ██ ██
// =====================================================

Router::get('/admin/laporan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $db = Database::getInstance();
$stats = [
    'total_pegawai' => $db->count('pegawai', "`status` = 'active' AND deleted_at IS NULL"),
    'total_aset'    => $db->count('aset', "`status` = 'active' AND deleted_at IS NULL"),
    'aset_baik'     => $db->count('aset', "`condition` = 'baik' AND deleted_at IS NULL"),
    'aset_rusak'    => $db->count('aset', "`condition` IN ('rusak_ringan','rusak_berat') AND deleted_at IS NULL"),
    'pemasukan'     => $db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM transaksi_keuangan WHERE `type`='pemasukan' AND `status`='approved' AND deleted_at IS NULL")['total'] ?? 0,
    'pengeluaran'   => $db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM transaksi_keuangan WHERE `type`='pengeluaran' AND `status`='approved' AND deleted_at IS NULL")['total'] ?? 0,
    'transaksi_pending' => $db->count('transaksi_keuangan', "`status`='pending' AND deleted_at IS NULL"),
];
    View::renderWithLayout('admin', 'admin/laporan/index', ['title' => 'Laporan & Statistik', 'stats' => $stats]);
});

// =====================================================
// ██████   █████  ██████  ██   ██ ██    ██  █████
// ██   ██ ██   ██ ██   ██ ██  ██  ██    ██ ██   ██
// ██████  ███████ ██████  █████   ██    ██ ███████
// ██   ██ ██   ██ ██      ██  ██  ██    ██ ██   ██
// ██████  ██   ██ ██      ██   ██  ██████  ██   ██
// =====================================================

Router::get('/admin/backup', function () {
    Auth::requireRole('super_admin');
    $backupDir = __DIR__ . '/database/backups/';
    if (!is_dir($backupDir)) @mkdir($backupDir, 0755, true);
    $files = [];
    foreach (glob($backupDir . '*.sql') as $f) $files[] = ['name' => basename($f), 'size' => filesize($f), 'date' => filemtime($f)];
    usort($files, fn($a, $b) => $b['date'] - $a['date']);
    View::renderWithLayout('admin', 'admin/backup/index', ['title' => 'Backup Database', 'files' => $files]);
});

Router::get('/admin/backup/buat', function () {
    Auth::requireRole('super_admin');
    $backupDir = __DIR__ . '/database/backups/';
    if (!is_dir($backupDir)) @mkdir($backupDir, 0755, true);
    $filename = 'backup_' . date('Y-m-d_His') . '.sql';
    $filepath = $backupDir . $filename;
    try {
        $db = Database::getInstance()->getConnection();
        $output = "-- BAUK UNIMOF Database Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $output .= "\n-- Table: {$table}\n";
            $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll();
            foreach ($rows as $row) {
                $cols = array_keys($row);
                $vals = array_map(fn($v) => $v === null ? 'NULL' : "'" . addslashes((string)$v) . "'", array_values($row));
                $output .= "INSERT INTO `{$table}` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ");\n";
            }
        }
        file_put_contents($filepath, $output);
        ActivityLog::create(Auth::id(), 'backup', "Backup DB: {$filename}");
        flash('success', "Backup berhasil: {$filename}");
    } catch (\Throwable $e) {
        flash('error', 'Gagal backup: ' . $e->getMessage());
    }
    header('Location: ' . url('admin/backup')); exit;
});

Router::get('/admin/backup/download/{filename}', function (string $filename) {
    Auth::requireRole('super_admin');
    $file = __DIR__ . '/database/backups/' . basename($filename);
    if (!file_exists($file)) { http_response_code(404); echo 'File tidak ditemukan'; return; }
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file); exit;
});

Router::get('/admin/backup/hapus/{filename}', function (string $filename) {
    Auth::requireRole('super_admin');
    $file = __DIR__ . '/database/backups/' . basename($filename);
    if (file_exists($file)) {
        unlink($file);
        ActivityLog::create(Auth::id(), 'delete', 'Hapus backup: ' . basename($filename));
        flash('success', 'Backup dihapus');
    }
    header('Location: ' . url('admin/backup')); exit;
});

// =====================================================
// ██████  ███    ██ ███████ ██████  ██    ██ ███████
// ██   ██ ████   ██ ██      ██   ██ ██    ██ ██
// ██████  ██ ██  ██ █████   ██   ██ ██    ██ ███████
// ██      ██  ██ ██ ██      ██   ██ ██    ██      ██
// ██      ██   ████ ███████ ██████   ██████  ███████
// =====================================================

Router::get('/admin/pengguna', function () {
    Auth::requireRole('super_admin');
    $users = Database::getInstance()->fetchAll("SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC");
    View::renderWithLayout('admin', 'admin/users/index', ['title' => 'Manajemen Pengguna', 'users' => $users]);
});

Router::get('/admin/pengguna/tambah', function () {
    Auth::requireRole('super_admin');
    View::renderWithLayout('admin', 'admin/users/form', ['title' => 'Tambah Pengguna', 'mode' => 'add', 'data' => null]);
});

Router::get('/admin/pengguna/edit/{id}', function (string $id) {
    Auth::requireRole('super_admin');
    $data = Database::getInstance()->fetch("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
    if (!$data) { flash('error', 'Pengguna tidak ditemukan.'); header('Location: ' . url('admin/pengguna')); exit; }
    View::renderWithLayout('admin', 'admin/users/form', ['title' => 'Edit Pengguna', 'mode' => 'edit', 'data' => $data]);
});

Router::post('/admin/pengguna/simpan', function () {
    Auth::requireRole('super_admin');
    $db = Database::getInstance();
    $id = max(0, (int)($_POST['id'] ?? 0));
    $errors = [];
    $username = Security::sanitize($_POST['username'] ?? '');
    $email = Security::sanitize($_POST['email'] ?? '');
    $fullName = Security::sanitize($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'viewer';
    if ($username === '' || $email === '' || $fullName === '') $errors[] = 'Username, email, dan nama lengkap wajib diisi';
    if (!Security::isValidEmail($email)) $errors[] = 'Format email tidak valid';
    if (!in_array($role, ['super_admin','admin_bauk','admin_kepegawaian','admin_keuangan','admin_aset','staff','viewer'], true)) $role = 'viewer';
    $dupU = $db->fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $id]);
    if ($dupU) $errors[] = 'Username sudah digunakan';
    $dupE = $db->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
    if ($dupE) $errors[] = 'Email sudah digunakan';
    if (!$id && strlen($password) < 8) $errors[] = 'Password minimal 8 karakter untuk pengguna baru';
    if ($errors) { $_SESSION['form_old'] = $_POST; $_SESSION['flash_error'] = $errors; header('Location: ' . url($id ? 'admin/pengguna/edit/' . $id : 'admin/pengguna/tambah')); exit; }
    $payload = ['username' => $username, 'email' => $email, 'full_name' => $fullName, 'role' => $role, 'status' => $_POST['status'] ?? 'active'];
    if ($password !== '') $payload['password'] = Security::hashPassword($password);
    if ($id > 0) {
        $db->update('users', $payload, 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'update', 'Update pengguna: ' . $username, 'users', $id);
        flash('success', 'Pengguna "' . $username . '" berhasil diperbarui.');
    } else {
        $payload['uuid'] = Security::generateUuid();
        $payload['password'] = Security::hashPassword($password);
        $payload['email_verified_at'] = date('Y-m-d H:i:s');
        $payload['created_by'] = Auth::id();
        $id = $db->insert('users', $payload);
        ActivityLog::create(Auth::id(), 'create', 'Pengguna baru: ' . $username, 'users', $id);
        flash('success', 'Pengguna "' . $username . '" berhasil dibuat.');
    }
    clearOld();
    header('Location: ' . url('admin/pengguna')); exit;
});

Router::get('/admin/pengguna/hapus/{id}', function (string $id) {
    Auth::requireRole('super_admin');
    $id = (int)$id;
    if ($id === Auth::id()) { flash('error', 'Tidak dapat menghapus akun sendiri.'); header('Location: ' . url('admin/pengguna')); exit; }
    $u = Database::getInstance()->fetch("SELECT username FROM users WHERE id = ? AND deleted_at IS NULL", [$id]);
    if ($u) {
        Database::getInstance()->update('users', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'delete', 'Hapus pengguna: ' . $u['username'], 'users', $id);
        flash('success', 'Pengguna "' . $u['username'] . '" dihapus.');
    }
    header('Location: ' . url('admin/pengguna')); exit;
});

// =====================================================
//  ██████  ███████ ████████ ███████ ███████ ██████  ████████ ███████ ██████
// ██       ██         ██    ██      ██      ██   ██ ██         ██    ██   ██
// ██   ███ █████      ██    █████   ███████ ██████  █████      ██    ██████
// ██    ██ ██         ██    ██           ██ ██           ██    ██    ██   ██
//  ██████  ███████    ██    ███████ ███████ ██           ██    ██    ██   ██
// =====================================================

Router::get('/admin/pengaturan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $settings = Database::getInstance()->fetchAll("SELECT * FROM settings ORDER BY `group`, `key`");
    View::renderWithLayout('admin', 'admin/settings/index', ['title' => 'Pengaturan', 'settings' => $settings]);
});

Router::post('/admin/pengaturan/simpan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $db = Database::getInstance();

    // 1. Verifikasi CSRF
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Sesi berakhir atau token tidak valid. Silakan ulangi.');
        header('Location: ' . url('admin/pengaturan')); exit;
    }

    $errors = [];
    $saved  = 0;

    // 2. Helper upsert (update jika ada, insert jika belum)
    $upsert = function (string $key, string $value, string $type = 'text', string $group = 'general', string $desc = '') use ($db) {
        $existing = $db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
        if ($existing) {
            $db->update('settings', ['value' => $value], 'id = ?', [$existing['id']]);
        } else {
            $db->insert('settings', ['key' => $key, 'value' => $value, 'type' => $type, 'group' => $group, 'description' => $desc]);
        }
    };

    // 3. Simpan input teks/textarea/boolean/number dari name="settings[key]"
    $submitted = $_POST['settings'] ?? [];
    if (is_array($submitted)) {
        foreach ($submitted as $key => $value) {
            $key = preg_replace('/[^a-z0-9_]/', '_', strtolower((string)$key));
            $row = $db->fetch("SELECT type FROM settings WHERE `key` = ?", [$key]);
            if ($row && $row['type'] === 'file') continue; // file ditangani terpisah
            $upsert($key, Security::sanitize(is_array($value) ? '' : (string)$value));
            $saved++;
        }
    }

    // 4. Kompatibilitas format lama name="key" langsung
    foreach (['site_name','site_description','visi_text','misi_text','tujuan_text','kepala_bauk_name','kepala_bauk_title','contact_address','contact_phone','contact_email'] as $key) {
        if (isset($_POST[$key]) && !isset($submitted[$key])) {
            $upsert($key, Security::sanitize((string)$_POST[$key]));
            $saved++;
        }
    }

    // 5. Upload file branding (logo, favicon, foto kepala, foto struktur)
    $fileKeys = [
        'site_logo'                 => [['jpg','jpeg','png','svg','webp'], 2 * 1024 * 1024, 'general', 'Logo website'],
        'site_favicon'              => [['ico','png','svg'],              1 * 1024 * 1024, 'general', 'Favicon browser'],
        'kepala_bauk_photo'         => [['jpg','jpeg','png','webp'],      2 * 1024 * 1024, 'general', 'Foto Kepala BAUK'],
        'struktur_organisasi_photo' => [['jpg','jpeg','png','webp'],      5 * 1024 * 1024, 'general', 'Bagan struktur organisasi'],
    ];
    foreach ($fileKeys as $key => $cfg) {
        if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) continue;
        [$exts, $max, $group, $desc] = $cfg;
        $errs = Security::validateFileUpload($_FILES[$key], $exts, $max);
        if ($errs) { $errors[] = "{$key}: " . implode(', ', $errs); continue; }
        $dir = ensureUploadDir('settings');
        $newName = safeFilename($_FILES[$key]['name']);
        if (move_uploaded_file($_FILES[$key]['tmp_name'], $dir . $newName)) {
            $old = $db->fetch("SELECT value FROM settings WHERE `key` = ?", [$key]);
            if ($old && !empty($old['value'])) {
                $oldFile = UPLOAD_DIR . 'settings/' . basename($old['value']);
                if (is_file($oldFile)) @unlink($oldFile);
            }
            $upsert($key, $newName, 'file', $group, $desc);
            $saved++;
        } else {
            $errors[] = "{$key}: gagal memindahkan file upload";
        }
    }

    if ($errors) {
        $_SESSION['flash_error'] = $errors;
        header('Location: ' . url('admin/pengaturan')); exit;
    }

    ActivityLog::create(Auth::id(), 'update', "Update pengaturan sistem ({$saved} item)", 'settings');
    flash('success', "Pengaturan berhasil disimpan ({$saved} perubahan).");
    header('Location: ' . url('admin/pengaturan')); exit;
});

// =====================================================
//  █████  ██    ██ ██ ████████ ██ ███    ██  ██████  ███████
// ██   ██ ██    ██ ██    ██    ██ ████   ██ ██       ██
// ███████ ██    ██ ██    ██    ██ ██ ██  ██ ██   ███ ███████
// ██   ██  ██  ██  ██    ██    ██ ██  ██ ██ ██    ██      ██
// ██   ██   ████   ██    ██    ██ ██   ████  ██████  ███████
// =====================================================

Router::get('/admin/aktivitas', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $page = max(1, (int)($_GET['page'] ?? 1));
    $total = Database::getInstance()->count('activity_logs');
    $totalPages = max(1, (int)ceil($total / PER_PAGE));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * PER_PAGE;
    $logs = Database::getInstance()->fetchAll("SELECT al.*, u.full_name, u.username FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT " . PER_PAGE . " OFFSET " . $offset);
    View::renderWithLayout('admin', 'admin/aktivitas/index', ['title' => 'Log Aktivitas', 'logs' => $logs, 'pagination' => ['page' => $page, 'totalPages' => $totalPages, 'total' => $total]]);
});

// =====================================================
// ██████  ██████   ██████  ██████  ██ ███████
// ██   ██ ██   ██ ██    ██ ██   ██ ██ ██
// ██████  ██████  ██    ██ ██   ██ ██ █████
// ██      ██   ██ ██    ██ ██   ██ ██ ██
// ██      ██   ██  ██████  ██████  ██ ██
// =====================================================

Router::get('/admin/profil', function () {
    Auth::requireAuth();
    View::renderWithLayout('admin', 'admin/profil/index', ['title' => 'Profil Saya']);
});

Router::post('/admin/profil/update', function () {
    Auth::requireAuth();
    $errors = [];
    $fullName = Security::sanitize($_POST['full_name'] ?? '');
    $email = Security::sanitize($_POST['email'] ?? '');
    $phone = Security::sanitize($_POST['phone'] ?? '');
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    if ($fullName === '' || $email === '') $errors[] = 'Nama dan email wajib diisi';
    if (!Security::isValidEmail($email)) $errors[] = 'Format email tidak valid';
    $db = Database::getInstance();
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [Auth::id()]);
    if ($newPass !== '') {
        if (!Security::verifyPassword($currentPass, $user['password'])) $errors[] = 'Password saat ini salah';
        if (strlen($newPass) < 8) $errors[] = 'Password baru minimal 8 karakter';
    }
    if ($errors) { $_SESSION['flash_error'] = $errors; header('Location: ' . url('admin/profil')); exit; }
    $payload = ['full_name' => $fullName, 'email' => $email, 'phone' => $phone ?: null];
    if ($newPass !== '') { $payload['password'] = Security::hashPassword($newPass); $payload['password_changed_at'] = date('Y-m-d H:i:s'); }
    // Avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $errs = Security::validateFileUpload($_FILES['avatar'], ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
        if ($errs) $errors = array_merge($errors, $errs);
        else {
            $dir = ensureUploadDir('profil');
            $new = safeFilename($_FILES['avatar']['name']);
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $new)) $payload['avatar'] = $new;
        }
    }
    if ($errors) { $_SESSION['flash_error'] = $errors; header('Location: ' . url('admin/profil')); exit; }
    $db->update('users', $payload, 'id = ?', [Auth::id()]);
    ActivityLog::create(Auth::id(), 'update', 'Update profil pribadi', 'users', Auth::id());
    flash('success', 'Profil berhasil diperbarui.');
    header('Location: ' . url('admin/profil')); exit;
});

// =====================================================
// ██████  ██ ██████  ███████ ████████
// ██   ██ ██ ██   ██ ██         ██
// ██   ██ ██ ██████  ███████    ██
// ██   ██ ██ ██           ██    ██
// ██████  ██ ██      ███████    ██
// =====================================================

// =====================================================
//  █████  ██████  ██ 
// ██   ██ ██   ██ ██ 
// ███████ ██████  ██ 
// ██   ██ ██      ██ 
// ██   ██ ██      ██  API ENDPOINTS
// =====================================================

Router::get('/api/dashboard/stats', function () {
    if (!Api::authenticate()) return;
    if (!Api::rateLimit(Security::getClientIp())) return;
    Api::success(Dashboard::getStats(), 'Dashboard statistics retrieved');
});

Router::get('/api/pegawai', function () {
    if (!Api::authenticate()) return;
    if (!Api::rateLimit(Security::getClientIp())) return;
    $db = Database::getInstance();
    $search = Security::sanitize($_GET['q'] ?? '');
    $where = 'deleted_at IS NULL';
    $params = [];
    if ($search) { $where .= ' AND (full_name LIKE ? OR nip LIKE ?)'; $l = "%{$search}%"; array_push($params, $l, $l); }
    $pegawai = $db->fetchAll("SELECT id, uuid, nip, full_name, position, status, email, phone FROM pegawai WHERE {$where} ORDER BY full_name LIMIT 100", $params);
    Api::success($pegawai, count($pegawai) . ' pegawai ditemukan');
});

Router::get('/api/keuangan/summary', function () {
    if (!Api::authenticate()) return;
    if (!Api::rateLimit(Security::getClientIp())) return;
    $db = Database::getInstance();
    $summary = [
        'pemasukan' => $db->fetch("SELECT COALESCE(SUM(amount),0) AS t FROM transaksi_keuangan WHERE type='pemasukan' AND status='approved' AND deleted_at IS NULL")['t'] ?? 0,
        'pengeluaran' => $db->fetch("SELECT COALESCE(SUM(amount),0) AS t FROM transaksi_keuangan WHERE type='pengeluaran' AND status='approved' AND deleted_at IS NULL")['t'] ?? 0,
        'pending' => $db->count('transaksi_keuangan', "status='pending' AND deleted_at IS NULL"),
    ];
    Api::success($summary, 'Financial summary retrieved');
});

Router::get('/api/kegiatan/upcoming', function () {
    if (!Api::authenticate()) return;
    $days = max(1, min(30, (int)($_GET['days'] ?? 7)));
    Api::success(Calendar::getUpcomingEvents($days), 'Upcoming events retrieved');
});

Router::get('/api/notifications', function () {
    if (!Api::authenticate()) return;
    $userId = $_SESSION['api_user_id'] ?? 0;
    Api::success([
        'unread_count' => Notification::getUnreadCount($userId),
        'notifications' => Notification::getForUser($userId, 20)
    ], 'Notifications retrieved');
});

Router::post('/api/notifications/read-all', function () {
    if (!Api::authenticate()) return;
    $userId = $_SESSION['api_user_id'] ?? 0;
    Notification::markAllAsRead($userId);
    Api::success(null, 'All notifications marked as read');
});

// =====================================================
//  █████  ██████  ██ ████████ ██ ███████
// ██   ██ ██   ██ ██    ██    ██ ██
// ███████ ██████  ██    ██    ██ ███████
// ██   ██ ██      ██    ██    ██      ██
// ██   ██ ██      ██    ██    ██ ███████  PUBLIC: PROFIL
// =====================================================

Router::get('/visi-misi', function () {
    View::renderWithLayout('public', 'public/visi-misi', ['title' => 'Visi & Misi - BAUK UNIMOF']);
});

Router::get('/struktur-organisasi', function () {
    View::renderWithLayout('public', 'public/struktur-organisasi', ['title' => 'Struktur Organisasi - BAUK UNIMOF']);
});

Router::get('/tupoksi', function () {
    View::renderWithLayout('public', 'public/tupoksi', ['title' => 'Tupoksi - BAUK UNIMOF']);
});

// =====================================================
//  █████  ██████  ██ ████████ ██ ███    ███ ██   ██ ███████ ██████
// ██   ██ ██   ██ ██    ██    ██ ████  ████ ██  ██  ██      ██   ██
// ███████ ██████  ██    ██    ██ ██ ████ ██ █████   █████   ██████
// ██   ██ ██   ██ ██    ██    ██ ██  ██  ██ ██  ██  ██      ██   ██
// ██   ██ ██   ██ ██    ██    ██ ██      ██ ██   ██ ███████ ██   ██
// ADMIN: STRUKTUR ORGANISASI (CRUD)
// =====================================================

Router::get('/admin/struktur-organisasi', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $items = Database::getInstance()->fetchAll("
        SELECT * FROM struktur_organisasi 
        WHERE deleted_at IS NULL 
        ORDER BY level ASC, sort_order ASC, id ASC
    ");
    View::renderWithLayout('admin', 'admin/struktur/index', [
        'title' => 'Struktur Organisasi',
        'items' => $items,
    ]);
});

Router::get('/admin/struktur-organisasi/tambah', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $parents = Database::getInstance()->fetchAll("
        SELECT * FROM struktur_organisasi 
        WHERE deleted_at IS NULL 
        ORDER BY level ASC, sort_order ASC
    ");
    View::renderWithLayout('admin', 'admin/struktur/form', [
        'title' => 'Tambah Jabatan',
        'mode' => 'add',
        'data' => null,
        'parents' => $parents,
    ]);
});

Router::get('/admin/struktur-organisasi/edit/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $data = Database::getInstance()->fetch("
        SELECT * FROM struktur_organisasi WHERE id = ? AND deleted_at IS NULL
    ", [(int)$id]);
    if (!$data) {
        flash('error', 'Jabatan tidak ditemukan.');
        header('Location: ' . url('admin/struktur-organisasi'));
        exit;
    }
    $parents = Database::getInstance()->fetchAll("
        SELECT * FROM struktur_organisasi 
        WHERE deleted_at IS NULL AND id != ?
        ORDER BY level ASC, sort_order ASC
    ", [(int)$id]);
    View::renderWithLayout('admin', 'admin/struktur/form', [
        'title' => 'Edit Jabatan',
        'mode' => 'edit',
        'data' => $data,
        'parents' => $parents,
    ]);
});

Router::post('/admin/struktur-organisasi/simpan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Sesi berakhir atau token tidak valid.');
        header('Location: ' . url('admin/struktur-organisasi'));
        exit;
    }

    $db = Database::getInstance();
    $id = max(0, (int)($_POST['id'] ?? 0));
    $errors = [];

    $namaJabatan = Security::sanitize($_POST['nama_jabatan'] ?? '');
    $namaPejabat = Security::sanitize($_POST['nama_pejabat'] ?? '');
    $nip         = Security::sanitize($_POST['nip'] ?? '');
    $parentId    = max(0, (int)($_POST['parent_id'] ?? 0)) ?: null;
    $level       = max(1, min(10, (int)($_POST['level'] ?? 1)));
    $sortOrder   = max(0, (int)($_POST['sort_order'] ?? 0));

    if ($namaJabatan === '') $errors[] = 'Nama jabatan wajib diisi';

    // Auto-hitung level dari parent
    if ($parentId) {
        $parent = $db->fetch("SELECT level FROM struktur_organisasi WHERE id = ? AND deleted_at IS NULL", [$parentId]);
        if ($parent) $level = (int)$parent['level'] + 1;
    } else {
        $level = 1;
    }

    // Upload foto
    $existingFoto = $id ? ($db->fetch("SELECT foto FROM struktur_organisasi WHERE id = ?", [$id])['foto'] ?? null) : null;
    $fotoName = $existingFoto;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $errs = Security::validateFileUpload($_FILES['foto'], ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
        if ($errs) {
            $errors = array_merge($errors, $errs);
        } else {
            $dir = ensureUploadDir('struktur');
            $new = safeFilename($_FILES['foto']['name']);
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $new)) {
                // Hapus foto lama jika ada
                if ($existingFoto) {
                    $oldFile = UPLOAD_DIR . 'struktur/' . basename($existingFoto);
                    if (is_file($oldFile)) @unlink($oldFile);
                }
                $fotoName = $new;
            }
        }
    }

    if ($errors) {
        $_SESSION['form_old'] = $_POST;
        $_SESSION['flash_error'] = $errors;
        header('Location: ' . url($id ? 'admin/struktur-organisasi/edit/' . $id : 'admin/struktur-organisasi/tambah'));
        exit;
    }

    $payload = [
        'nama_jabatan' => $namaJabatan,
        'nama_pejabat' => $namaPejabat ?: null,
        'nip'          => $nip ?: null,
        'parent_id'    => $parentId,
        'level'        => $level,
        'sort_order'   => $sortOrder,
        'foto'         => $fotoName,
        'is_active'    => 1,
    ];

    if ($id > 0) {
        $db->update('struktur_organisasi', $payload, 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'update', "Update struktur: {$namaJabatan}", 'struktur_organisasi', $id);
        flash('success', "Jabatan \"{$namaJabatan}\" berhasil diperbarui.");
    } else {
        $payload['uuid'] = Security::generateUuid();
        $payload['created_by'] = Auth::id();
        $id = $db->insert('struktur_organisasi', $payload);
        ActivityLog::create(Auth::id(), 'create', "Tambah struktur: {$namaJabatan}", 'struktur_organisasi', $id);
        flash('success', "Jabatan \"{$namaJabatan}\" berhasil ditambahkan.");
    }
    clearOld();
    header('Location: ' . url('admin/struktur-organisasi'));
    exit;
});

Router::get('/admin/struktur-organisasi/hapus/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $db = Database::getInstance();
    $item = $db->fetch("SELECT nama_jabatan, foto FROM struktur_organisasi WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
    if ($item) {
        // Cek apakah punya anak
        $childCount = $db->count('struktur_organisasi', "parent_id = ? AND deleted_at IS NULL", [(int)$id]);
        if ($childCount > 0) {
            flash('error', "Tidak dapat menghapus jabatan \"{$item['nama_jabatan']}\" karena masih memiliki {$childCount} jabatan bawahan. Hapus bawahan terlebih dahulu.");
            header('Location: ' . url('admin/struktur-organisasi'));
            exit;
        }
        // Hapus file foto
        if (!empty($item['foto'])) {
            $file = UPLOAD_DIR . 'struktur/' . basename($item['foto']);
            if (is_file($file)) @unlink($file);
        }
        $db->update('struktur_organisasi', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$id]);
        ActivityLog::create(Auth::id(), 'delete', "Hapus struktur: {$item['nama_jabatan']}", 'struktur_organisasi', (int)$id);
        flash('success', "Jabatan \"{$item['nama_jabatan']}\" berhasil dihapus.");
    }
    header('Location: ' . url('admin/struktur-organisasi'));
    exit;
});

// =====================================================
//  █████  ██████  ██ ████████ ██ ████████  ██████  ██ ██   ██
// ██   ██ ██   ██ ██    ██    ██    ██    ██    ██ ██  ██ ██
// ███████ ██████  ██    ██    ██    ██    ██    ██ ██   ███
// ██   ██ ██      ██    ██    ██    ██    ██    ██ ██  ██ ██
// ██   ██ ██      ██    ██    ██    ██     ██████  ██ ██   ██
// ADMIN: TUPOKSI (CRUD)
// =====================================================

Router::get('/admin/tupoksi', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $items = Database::getInstance()->fetchAll("
        SELECT * FROM tupoksi 
        WHERE deleted_at IS NULL 
        ORDER BY sort_order ASC, id ASC
    ");
    View::renderWithLayout('admin', 'admin/tupoksi/index', [
        'title' => 'Tupoksi',
        'items' => $items,
    ]);
});

Router::get('/admin/tupoksi/tambah', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    View::renderWithLayout('admin', 'admin/tupoksi/form', [
        'title' => 'Tambah Unit Kerja',
        'mode' => 'add',
        'data' => null,
    ]);
});

Router::get('/admin/tupoksi/edit/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $data = Database::getInstance()->fetch("
        SELECT * FROM tupoksi WHERE id = ? AND deleted_at IS NULL
    ", [(int)$id]);
    if (!$data) {
        flash('error', 'Unit kerja tidak ditemukan.');
        header('Location: ' . url('admin/tupoksi'));
        exit;
    }
    View::renderWithLayout('admin', 'admin/tupoksi/form', [
        'title' => 'Edit Unit Kerja',
        'mode' => 'edit',
        'data' => $data,
    ]);
});

Router::post('/admin/tupoksi/simpan', function () {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Sesi berakhir atau token tidak valid.');
        header('Location: ' . url('admin/tupoksi'));
        exit;
    }

    $db = Database::getInstance();
    $id = max(0, (int)($_POST['id'] ?? 0));
    $errors = [];

    $unitKerja    = Security::sanitize($_POST['unit_kerja'] ?? '');
    $kodeUnit     = strtoupper(Security::sanitize($_POST['kode_unit'] ?? ''));
    $namaJabatan  = Security::sanitize($_POST['nama_jabatan'] ?? '');
    $tugasPokok   = Security::cleanInput($_POST['tugas_pokok'] ?? '');
    $fungsi       = Security::cleanInput($_POST['fungsi'] ?? '');
    $icon         = preg_replace('/[^a-z0-9\-_]/', '', $_POST['icon'] ?? 'bi-briefcase');
    $warna        = preg_replace('/[^#a-fA-F0-9]/', '', $_POST['warna'] ?? '#1a5276');
    $sortOrder    = max(0, (int)($_POST['sort_order'] ?? 0));
    $isActive     = isset($_POST['is_active']) && (int)$_POST['is_active'] === 1 ? 1 : 0;

    if ($unitKerja === '')   $errors[] = 'Nama unit kerja wajib diisi';
    if ($namaJabatan === '') $errors[] = 'Nama jabatan wajib diisi';
    if ($tugasPokok === '')  $errors[] = 'Tugas pokok wajib diisi';
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $warna)) $warna = '#1a5276';

    if ($errors) {
        $_SESSION['form_old'] = $_POST;
        $_SESSION['flash_error'] = $errors;
        header('Location: ' . url($id ? 'admin/tupoksi/edit/' . $id : 'admin/tupoksi/tambah'));
        exit;
    }

    $payload = [
        'unit_kerja'    => $unitKerja,
        'kode_unit'     => $kodeUnit ?: null,
        'nama_jabatan'  => $namaJabatan,
        'tugas_pokok'   => $tugasPokok,
        'fungsi'        => $fungsi ?: null,
        'icon'          => $icon,
        'warna'         => $warna,
        'sort_order'    => $sortOrder,
        'is_active'     => $isActive,
    ];

    if ($id > 0) {
        $db->update('tupoksi', $payload, 'id = ?', [$id]);
        ActivityLog::create(Auth::id(), 'update', "Update tupoksi: {$unitKerja}", 'tupoksi', $id);
        flash('success', "Unit kerja \"{$unitKerja}\" berhasil diperbarui.");
    } else {
        $payload['uuid'] = Security::generateUuid();
        $payload['created_by'] = Auth::id();
        $id = $db->insert('tupoksi', $payload);
        ActivityLog::create(Auth::id(), 'create', "Tambah tupoksi: {$unitKerja}", 'tupoksi', $id);
        flash('success', "Unit kerja \"{$unitKerja}\" berhasil ditambahkan.");
    }
    clearOld();
    header('Location: ' . url('admin/tupoksi'));
    exit;
});

Router::get('/admin/tupoksi/hapus/{id}', function (string $id) {
    Auth::requireRole(['super_admin', 'admin_bauk']);
    $db = Database::getInstance();
    $item = $db->fetch("SELECT unit_kerja FROM tupoksi WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
    if ($item) {
        $db->update('tupoksi', ['deleted_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$id]);
        ActivityLog::create(Auth::id(), 'delete', "Hapus tupoksi: {$item['unit_kerja']}", 'tupoksi', (int)$id);
        flash('success', "Unit kerja \"{$item['unit_kerja']}\" berhasil dihapus.");
    }
    header('Location: ' . url('admin/tupoksi'));
    exit;
});

// =====================================================
// ROUTER DISPATCH - JANGAN UBAH POSISI INI
// =====================================================
Router::dispatch();