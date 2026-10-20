<?php
/**
 * BAUK UNIMOF - Admin Layout (ULTIMATE EDITION)
 * Biro Administrasi Umum dan Keuangan
 * Universitas Muhammadiyah Maumere
 * 
 * Fitur: Multimate, Elegant, Professional
 * Kompatibel: PHP 8.2+
 */
use App\Helpers\Auth;
use App\Helpers\Database;

$user = Auth::user();
$cp   = current_path();
$db   = Database::getInstance();

// ===== Counter notifikasi real-time =====
$unreadMessages   = 0;
$activePengumuman = 0;
$pendingTransaksi = 0;

try {
    $unreadMessages   = $db->count('contact_messages', "is_read = 0");
    $activePengumuman = $db->count('pengumuman', "is_active = 1");
    $pendingTransaksi = $db->count('transaksi_keuangan', "status = 'pending' AND deleted_at IS NULL");
} catch (\Throwable $e) {
    // Fail-safe: biarkan 0 jika DB error
}

$totalNotifications = $unreadMessages + $pendingTransaksi;

// ===== Breadcrumb generator otomatis =====
$breadcrumb = [
    'admin'         => 'Dashboard',
    'admin/pegawai' => 'Kepegawaian',
    'admin/keuangan' => 'Keuangan',
    'admin/aset'    => 'Aset',
    'admin/berita'  => 'Berita',
    'admin/pengumuman' => 'Pengumuman',
    'admin/regulasi' => 'Regulasi',
    'admin/departemen' => 'Departemen',
    'admin/kategori-keuangan' => 'Kategori Keuangan',
    'admin/kategori-aset' => 'Kategori Aset',
    'admin/kontak'  => 'Pesan Kontak',
    'admin/kegiatan' => 'Agenda Kegiatan',
    'admin/maintenance-aset' => 'Pemeliharaan Aset',
    'admin/laporan' => 'Laporan',
    'admin/aktivitas' => 'Log Aktivitas',
    'admin/pengguna' => 'Pengguna',
    'admin/pengaturan' => 'Pengaturan',
    'admin/profil'  => 'Profil',
    'admin/backup'  => 'Backup Database',
];

$pageTitle = $title ?? 'BAUK UNIMOF Admin';
$currentPageName = '';
foreach ($breadcrumb as $path => $name) {
    if ($cp === '/' . $path || str_starts_with($cp, '/' . $path . '/')) {
        $currentPageName = $name;
        break;
    }
}

// Format waktu server
$serverTime = date('d F Y, H:i:s');
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= \App\Helpers\Security::generateCsrfToken() ?>">
    <meta name="app-version" content="1.0.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Favicon -->
    <?php
$__admCfg = [];
try {
    foreach (\App\Helpers\Database::getInstance()->fetchAll("SELECT `key`, `value` FROM settings WHERE `key` IN ('site_logo','site_favicon')") as $__s) {
        $__admCfg[$__s['key']] = $__s['value'];
    }
} catch (\Throwable $__e) {}
$__admFav = !empty($__admCfg['site_favicon']) ? url('uploads/settings/' . $__admCfg['site_favicon']) : null;
$__admLogo = !empty($__admCfg['site_logo']) ? url('uploads/settings/' . $__admCfg['site_logo']) : null;
?>
<?php if ($__admFav): ?>
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($__admFav) ?>">
<?php else: ?>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' fill='%231a5276'/%3E%3Ctext x='50' y='68' font-family='Arial' font-size='60' font-weight='bold' text-anchor='middle' fill='white'%3EB%3C/text%3E%3C/svg%3E">
<?php endif; ?>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= asset('css/admin-style.css') ?>">
</head>
<body class="admin-body">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">
        <!-- Brand -->
        <div class="sidebar-header">
            <a href="<?= url('admin') ?>" class="sidebar-brand">
<?php if ($__admLogo): ?>
    <img src="<?= htmlspecialchars($__admLogo) ?>" alt="BAUK" style="height:40px;width:auto;border-radius:8px;">
<?php else: ?>
    <div class="brand-icon">B</div>
<?php endif; ?>
<div class="brand-text">
    <h3>BAUK</h3>
    <span>UNIMOF Admin</span>
</div>
            </a>
            <button class="sidebar-close" id="sidebarClose"><i class="bi bi-x-lg"></i></button>
        </div>
        
        <!-- Navigation -->
        <nav class="sidebar-nav">
            <!-- Menu Utama -->
            <div class="nav-section">
                <span class="nav-title">Menu Utama</span>
                <a href="<?= url('admin') ?>" class="nav-item <?= $cp === '/admin' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            
            <!-- Modul Layanan -->
            <div class="nav-section">
                <span class="nav-title">Modul Layanan</span>
                
                <?php if (Auth::hasRole(['super_admin', 'admin_bauk', 'admin_kepegawaian'])): ?>
                <a href="<?= url('admin/pegawai') ?>" class="nav-item <?= $cp === '/admin/pegawai' ? 'active' : '' ?>">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Kepegawaian</span>
                </a>
                <?php endif; ?>
                
                <?php if (Auth::hasRole(['super_admin', 'admin_bauk', 'admin_keuangan'])): ?>
                <a href="<?= url('admin/keuangan') ?>" class="nav-item <?= $cp === '/admin/keuangan' ? 'active' : '' ?>">
                    <i class="bi bi-wallet2"></i>
                    <span>Keuangan</span>
                    <?php if ($pendingTransaksi > 0): ?>
                        <span class="nav-badge badge-danger"><?= $pendingTransaksi ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
                
                <?php if (Auth::hasRole(['super_admin', 'admin_bauk', 'admin_aset'])): ?>
                <a href="<?= url('admin/aset') ?>" class="nav-item <?= $cp === '/admin/aset' ? 'active' : '' ?>">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>Aset</span>
                </a>
                <a href="<?= url('admin/maintenance-aset') ?>" class="nav-item <?= $cp === '/admin/maintenance-aset' ? 'active' : '' ?>">
                    <i class="bi bi-tools"></i>
                    <span>Pemeliharaan</span>
                </a>
                <?php endif; ?>
            </div>
            
            <!-- Manajemen Data -->
            <div class="nav-section">
                <span class="nav-title">Manajemen Data</span>
                <a href="<?= url('admin/departemen') ?>" class="nav-item <?= $cp === '/admin/departemen' ? 'active' : '' ?>">
                    <i class="bi bi-building"></i>
                    <span>Departemen</span>
                </a>
                <a href="<?= url('admin/kategori-keuangan') ?>" class="nav-item <?= $cp === '/admin/kategori-keuangan' ? 'active' : '' ?>">
                    <i class="bi bi-tags-fill"></i>
                    <span>Kategori Keuangan</span>
                </a>
                <a href="<?= url('admin/kategori-aset') ?>" class="nav-item <?= $cp === '/admin/kategori-aset' ? 'active' : '' ?>">
                    <i class="bi bi-bookmark-fill"></i>
                    <span>Kategori Aset</span>
                </a>
            </div>
            
            <!-- Profil Website (ULTIMATE) -->
<?php if (Auth::hasRole(['super_admin', 'admin_bauk'])): ?>
<div class="nav-section">
    <span class="nav-title">Profil Website</span>
    <a href="<?= url('admin/struktur-organisasi') ?>" class="nav-item <?= $cp === '/admin/struktur-organisasi' ? 'active' : '' ?>">
        <i class="bi bi-diagram-3-fill"></i>
        <span>Struktur Organisasi</span>
    </a>
    <a href="<?= url('admin/tupoksi') ?>" class="nav-item <?= $cp === '/admin/tupoksi' ? 'active' : '' ?>">
        <i class="bi bi-clipboard-check-fill"></i>
        <span>Tupoksi</span>
    </a>
    <a href="<?= url('admin/pengaturan') ?>" class="nav-item <?= $cp === '/admin/pengaturan' ? 'active' : '' ?>">
        <i class="bi bi-palette-fill"></i>
        <span>Branding & Konten</span>
    </a>
</div>
<?php endif; ?>

            <!-- Konten Publikasi -->
            <div class="nav-section">
                <span class="nav-title">Konten Publikasi</span>
                <a href="<?= url('admin/berita') ?>" class="nav-item <?= $cp === '/admin/berita' ? 'active' : '' ?>">
                    <i class="bi bi-newspaper"></i>
                    <span>Berita</span>
                </a>
                <a href="<?= url('admin/pengumuman') ?>" class="nav-item <?= $cp === '/admin/pengumuman' ? 'active' : '' ?>">
                    <i class="bi bi-megaphone-fill"></i>
                    <span>Pengumuman</span>
                    <?php if ($activePengumuman > 0): ?>
                        <span class="nav-badge badge-primary"><?= $activePengumuman ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= url('admin/regulasi') ?>" class="nav-item <?= $cp === '/admin/regulasi' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text-fill"></i>
                    <span>Regulasi</span>
                </a>
                <a href="<?= url('admin/kegiatan') ?>" class="nav-item <?= $cp === '/admin/kegiatan' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-event-fill"></i>
                    <span>Agenda Kegiatan</span>
                </a>
            </div>
            
            <!-- Komunikasi -->
            <?php if (Auth::hasRole(['super_admin', 'admin_bauk'])): ?>
            <div class="nav-section">
                <span class="nav-title">Komunikasi</span>
                <a href="<?= url('admin/kontak') ?>" class="nav-item <?= $cp === '/admin/kontak' ? 'active' : '' ?>">
                    <i class="bi bi-envelope-fill"></i>
                    <span>Pesan Kontak</span>
                    <?php if ($unreadMessages > 0): ?>
                        <span class="nav-badge badge-danger"><?= $unreadMessages ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Laporan -->
            <div class="nav-section">
                <span class="nav-title">Laporan</span>
                <a href="<?= url('admin/laporan') ?>" class="nav-item <?= $cp === '/admin/laporan' ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Laporan & Statistik</span>
                </a>
            </div>
            
            <!-- Administrasi Sistem -->
            <?php if (Auth::hasRole(['super_admin', 'admin_bauk'])): ?>
            <div class="nav-section">
                <span class="nav-title">Administrasi Sistem</span>
                <a href="<?= url('admin/aktivitas') ?>" class="nav-item <?= $cp === '/admin/aktivitas' ? 'active' : '' ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>Log Aktivitas</span>
                </a>
                <?php if (Auth::hasRole('super_admin')): ?>
                <a href="<?= url('admin/pengguna') ?>" class="nav-item <?= $cp === '/admin/pengguna' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Manajemen Pengguna</span>
                </a>
                <a href="<?= url('admin/pengaturan') ?>" class="nav-item <?= $cp === '/admin/pengaturan' ? 'active' : '' ?>">
                    <i class="bi bi-gear-fill"></i>
                    <span>Pengaturan Sistem</span>
                </a>
                <a href="<?= url('admin/backup') ?>" class="nav-item <?= $cp === '/admin/backup' ? 'active' : '' ?>">
                    <i class="bi bi-database-fill-gear"></i>
                    <span>Backup & Restore</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </nav>
        
        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    <span class="status-dot online" title="Online"></span>
                </div>
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($user['full_name']) ?></span>
                    <span class="user-role"><?= ucfirst(str_replace('_', ' ', $user['role'])) ?></span>
                </div>
            </div>
            <a href="<?= url('admin/logout') ?>" class="btn-logout"
               onclick="return confirm('Yakin ingin keluar dari sistem BAUK UNIMOF?');">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>
            <div class="sidebar-version">
                <i class="bi bi-shield-check"></i> BAUK v1.0.0 • PHP <?= PHP_VERSION ?>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN WRAPPER ===== -->
    <div class="main-wrapper">
        
        <!-- ===== TOP HEADER ===== -->
        <header class="admin-header">
            <!-- Sidebar Toggle (Mobile) -->
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                <i class="bi bi-list"></i>
            </button>
            
            <!-- Breadcrumb -->
            <div class="header-breadcrumb">
                <a href="<?= url('admin') ?>"><i class="bi bi-house-fill"></i></a>
                <?php if ($currentPageName && $cp !== '/admin'): ?>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current"><?= htmlspecialchars($currentPageName) ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Global Search -->
            <div class="header-search">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Cari menu, data... (Ctrl+K)" id="globalSearch" autocomplete="off">
            </div>
            
            <!-- Header Actions -->
            <div class="header-actions">
                <!-- Server Time -->
                <div class="header-info d-none-mobile" title="Waktu Server">
                    <i class="bi bi-clock"></i>
                    <span id="serverTime"><?= $serverTime ?></span>
                </div>
                
                <!-- Maintenance Mode Indicator -->
                <?php 
                try {
                    $maintenance = $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_mode'");
                    if (!empty($maintenance) && $maintenance['value'] === '1'):
                ?>
                <div class="header-info maintenance-indicator" title="Mode Pemeliharaan Aktif">
                    <i class="bi bi-cone-striped"></i>
                    <span class="d-none-mobile">Maintenance</span>
                </div>
                <?php endif; } catch (\Throwable $e) {} ?>
                
                <!-- View Website -->
                <a href="<?= url() ?>" target="_blank" class="header-btn" title="Lihat Website Publik" data-tooltip="Website">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
                
                <!-- Dark Mode Toggle -->
                <button class="header-btn" id="darkModeToggle" title="Mode Gelap/Terang">
                    <i class="bi bi-moon-fill"></i>
                </button>
                
                <!-- Fullscreen Toggle -->
                <button class="header-btn d-none-mobile" id="fullscreenToggle" title="Layar Penuh">
                    <i class="bi bi-arrows-fullscreen"></i>
                </button>
                
                <!-- Notifications -->
                <div class="user-menu notifications-menu">
                    <button class="header-btn notification-btn" id="notifBtn">
                        <i class="bi bi-bell-fill"></i>
                        <?php if ($totalNotifications > 0): ?>
                            <span class="badge"><?= $totalNotifications ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="user-dropdown notifications-dropdown">
                        <div class="dropdown-header">
                            <strong>Notifikasi</strong>
                            <a href="#" class="mark-all-read">Tandai semua dibaca</a>
                        </div>
                        <div class="notifications-list">
                            <?php if ($pendingTransaksi > 0): ?>
                            <a href="<?= url('admin/keuangan') ?>" class="notification-item">
                                <div class="notif-icon bg-orange"><i class="bi bi-exclamation-circle"></i></div>
                                <div class="notif-content">
                                    <strong><?= $pendingTransaksi ?> Transaksi</strong> menunggu persetujuan
                                    <small>Perlu ditinjau segera</small>
                                </div>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($unreadMessages > 0): ?>
                            <a href="<?= url('admin/kontak') ?>" class="notification-item">
                                <div class="notif-icon bg-blue"><i class="bi bi-envelope"></i></div>
                                <div class="notif-content">
                                    <strong><?= $unreadMessages ?> Pesan</strong> baru dari pengunjung
                                    <small>Belum dibaca</small>
                                </div>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($activePengumuman > 0): ?>
                            <a href="<?= url('admin/pengumuman') ?>" class="notification-item">
                                <div class="notif-icon bg-green"><i class="bi bi-megaphone"></i></div>
                                <div class="notif-content">
                                    <strong><?= $activePengumuman ?> Pengumuman</strong> aktif
                                    <small>Sedang ditampilkan</small>
                                </div>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($totalNotifications === 0): ?>
                            <div class="notification-empty">
                                <i class="bi bi-bell-slash"></i>
                                <p>Tidak ada notifikasi baru</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-footer">
                            <a href="<?= url('admin/aktivitas') ?>">Lihat semua aktivitas</a>
                        </div>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="user-menu">
                    <button class="user-menu-trigger">
                        <div class="user-avatar-sm">
                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                        </div>
                        <span class="d-none-mobile"><?= htmlspecialchars($user['full_name']) ?></span>
                        <i class="bi bi-chevron-down d-none-mobile"></i>
                    </button>
                    <div class="user-dropdown">
                        <div class="dropdown-user-info">
                            <div class="user-avatar-md"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
                            <div>
                                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                                <small><?= htmlspecialchars($user['email']) ?></small>
                            </div>
                        </div>
                        <hr>
                        <a href="<?= url('admin/profil') ?>"><i class="bi bi-person-fill"></i> Profil Saya</a>
                        <a href="<?= url('admin/pengaturan') ?>"><i class="bi bi-gear-fill"></i> Pengaturan Akun</a>
                        <a href="#" onclick="showKeyboardShortcuts(); return false;"><i class="bi bi-keyboard"></i> Pintasan Keyboard</a>
                        <hr>
                        <a href="<?= url('admin/logout') ?>" class="text-danger"
                           onclick="return confirm('Yakin ingin keluar?');">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- ===== PAGE CONTENT ===== -->
        <main class="page-content">
            
            <!-- Maintenance Mode Banner -->
            <?php if (!empty($maintenance) && $maintenance['value'] === '1'): ?>
            <div class="maintenance-banner">
                <i class="bi bi-cone-striped"></i>
                <span><strong>Mode Pemeliharaan Aktif</strong> — Website publik saat ini tidak dapat diakses oleh pengunjung.</span>
                <a href="<?= url('admin/pengaturan') ?>" class="btn-maintenance">Ubah Pengaturan</a>
            </div>
            <?php endif; ?>
            
            <!-- Flash Success -->
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= htmlspecialchars($_SESSION['flash_success']) ?>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
                <?php unset($_SESSION['flash_success']); endif; ?>
            
            <!-- Flash Error -->
            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div>
                        <?= is_array($_SESSION['flash_error']) 
                            ? implode('<br>', array_map('htmlspecialchars', $_SESSION['flash_error'])) 
                            : htmlspecialchars($_SESSION['flash_error']) ?>
                    </div>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
                <?php unset($_SESSION['flash_error']); endif; ?>
            
            <!-- Dynamic Content -->
            <?= $content ?? '' ?>
            
        </main>
        
        <!-- ===== ADMIN FOOTER ===== -->
        <footer class="admin-footer">
            <div class="footer-left">
                <span>&copy; <?= date('Y') ?> <strong>BAUK UNIMOF</strong></span>
                <span class="footer-separator">•</span>
                <span>Universitas Muhammadiyah Maumere</span>
            </div>
            <div class="footer-right">
                <span class="footer-status">
                    <span class="status-indicator online"></span>
                    Sistem Online
                </span>
                <span class="footer-separator">•</span>
                <span>v1.0.0</span>
                <span class="footer-separator">•</span>
                <span>Eksekusi: <span id="executionTime"><?= round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) ?>ms</span></span>
            </div>
        </footer>
        
    </div>

    <!-- ===== FLOATING ACTION BUTTON (FAB) ===== -->
    <?php if (Auth::check() && $cp !== '/admin'): ?>
    <div class="fab-container">
        <button class="fab-btn fab-main" id="fabMain" title="Aksi Cepat">
            <i class="bi bi-plus-lg"></i>
        </button>
        <div class="fab-menu">
            <?php if (Auth::hasRole(['super_admin', 'admin_bauk', 'admin_kepegawaian']) && $cp === '/admin/pegawai'): ?>
                <a href="<?= url('admin/pegawai/tambah') ?>" class="fab-item" title="Tambah Pegawai">
                    <i class="bi bi-person-plus"></i>
                </a>
            <?php endif; ?>
            <?php if (Auth::hasRole(['super_admin', 'admin_bauk', 'admin_keuangan']) && $cp === '/admin/keuangan'): ?>
                <a href="<?= url('admin/keuangan/tambah') ?>" class="fab-item" title="Input Transaksi">
                    <i class="bi bi-cash-coin"></i>
                </a>
            <?php endif; ?>
            <?php if (Auth::hasRole(['super_admin', 'admin_bauk', 'admin_aset']) && $cp === '/admin/aset'): ?>
                <a href="<?= url('admin/aset/tambah') ?>" class="fab-item" title="Tambah Aset">
                    <i class="bi bi-box-seam"></i>
                </a>
            <?php endif; ?>
            <?php if ($cp === '/admin/berita'): ?>
                <a href="<?= url('admin/berita/tambah') ?>" class="fab-item" title="Tulis Berita">
                    <i class="bi bi-pencil-square"></i>
                </a>
            <?php endif; ?>
            <?php if ($cp === '/admin/pengumuman'): ?>
                <a href="<?= url('admin/pengumuman/tambah') ?>" class="fab-item" title="Buat Pengumuman">
                    <i class="bi bi-megaphone"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== LOADING OVERLAY (untuk operasi AJAX) ===== -->
    <div class="loading-overlay" id="loadingOverlay" style="display:none;">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Memproses...</p>
        </div>
    </div>
    
    <!-- ===== CONFIRMATION MODAL ===== -->
    <div class="modal" id="confirmModal" style="display:none;">
        <div class="modal-backdrop"></div>
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h3 id="confirmTitle">Konfirmasi</h3>
                <button class="modal-close" onclick="closeModal('confirmModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="confirm-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <p id="confirmMessage">Apakah Anda yakin?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('confirmModal')">Batal</button>
                <button class="btn btn-danger" id="confirmOk">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
    
    <!-- ===== KEYBOARD SHORTCUTS MODAL ===== -->
    <div class="modal" id="shortcutsModal" style="display:none;">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="bi bi-keyboard"></i> Pintasan Keyboard</h3>
                <button class="modal-close" onclick="closeModal('shortcutsModal')">&times;</button>
            </div>
            <div class="modal-body">
                <table class="shortcuts-table">
                    <tr><td><kbd>Ctrl</kbd> + <kbd>K</kbd></td><td>Fokus ke pencarian</td></tr>
                    <tr><td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>D</kbd></td><td>Ke Dashboard</td></tr>
                    <tr><td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>P</kbd></td><td>Ke Data Pegawai</td></tr>
                    <tr><td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>F</kbd></td><td>Ke Keuangan</td></tr>
                    <tr><td><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>A</kbd></td><td>Ke Aset</td></tr>
                    <tr><td><kbd>Esc</kbd></td><td>Tutup modal/dialog</td></tr>
                    <tr><td><kbd>F11</kbd></td><td>Layar penuh</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== SESSION TIMEOUT WARNING ===== -->
    <div class="modal" id="timeoutModal" style="display:none;">
        <div class="modal-backdrop"></div>
        <div class="modal-content modal-sm">
            <div class="modal-body text-center">
                <div class="confirm-icon warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <h3>Sesi Hampir Berakhir</h3>
                <p>Sesi Anda akan berakhir dalam <strong id="timeoutCountdown">60</strong> detik.</p>
                <button class="btn btn-primary" onclick="extendSession()">Perpanjang Sesi</button>
            </div>
        </div>
    </div>

    <!-- ===== NOTIFICATION TOAST CONTAINER ===== -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Admin JS -->
    <script src="<?= asset('js/admin-script.js') ?>"></script>
    
    <!-- ===== INLINE SCRIPT (Enhancement, tidak ganggu admin-script.js) ===== -->
    <script>
    (function() {
        'use strict';
        
        // ===== Server Time Real-time =====
        const serverTimeEl = document.getElementById('serverTime');
        if (serverTimeEl) {
            setInterval(() => {
                const now = new Date();
                const options = { day: '2-digit', month: 'short', year: 'numeric', 
                                  hour: '2-digit', minute: '2-digit', second: '2-digit' };
                serverTimeEl.textContent = now.toLocaleString('id-ID', options).replace(',', ',');
            }, 1000);
        }
        
        // ===== Dark Mode Toggle =====
        const darkToggle = document.getElementById('darkModeToggle');
        if (darkToggle) {
            const saved = localStorage.getItem('bauk-theme');
            if (saved === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                darkToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
            }
            darkToggle.addEventListener('click', function() {
                const current = document.documentElement.getAttribute('data-theme');
                const next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('bauk-theme', next);
                this.innerHTML = next === 'dark' 
                    ? '<i class="bi bi-sun-fill"></i>' 
                    : '<i class="bi bi-moon-fill"></i>';
            });
        }
        
        // ===== Fullscreen Toggle =====
        const fsToggle = document.getElementById('fullscreenToggle');
        if (fsToggle) {
            fsToggle.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(()=>{});
                } else {
                    document.exitFullscreen().catch(()=>{});
                }
            });
        }
        
        // ===== FAB Toggle =====
        const fabMain = document.getElementById('fabMain');
        if (fabMain) {
            fabMain.addEventListener('click', function() {
                this.parentElement.classList.toggle('active');
                this.classList.toggle('rotated');
            });
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.fab-container')) {
                    document.querySelector('.fab-container')?.classList.remove('active');
                    fabMain.classList.remove('rotated');
                }
            });
        }
        
        // ===== Notifications Menu =====
        const notifBtn = document.getElementById('notifBtn');
        if (notifBtn) {
            notifBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                this.closest('.notifications-menu').classList.toggle('active');
            });
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.notifications-menu')) {
                    document.querySelector('.notifications-menu')?.classList.remove('active');
                }
            });
        }
        
        // ===== Global Functions =====
        window.closeModal = function(id) {
            const modal = document.getElementById(id);
            if (modal) modal.style.display = 'none';
        };
        
        window.showKeyboardShortcuts = function() {
            document.getElementById('shortcutsModal').style.display = 'block';
        };
        
        window.showLoading = function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        };
        
        window.hideLoading = function() {
            document.getElementById('loadingOverlay').style.display = 'none';
        };
        
        window.showConfirm = function(message, callback) {
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmModal').style.display = 'block';
            document.getElementById('confirmOk').onclick = function() {
                closeModal('confirmModal');
                callback();
            };
        };
        
        window.showToast = function(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            const icons = { success: 'check-circle', error: 'x-circle', warning: 'exclamation-triangle', info: 'info-circle' };
            toast.innerHTML = `<i class="bi bi-${icons[type]}"></i><span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => { toast.classList.add('show'); }, 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        };
        
        window.extendSession = function() {
            closeModal('timeoutModal');
            showToast('Sesi diperpanjang', 'success');
        };
        
        // ===== Keyboard Shortcuts =====
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                document.getElementById('globalSearch')?.focus();
            }
            if (e.ctrlKey && e.shiftKey && e.key === 'P') {
                e.preventDefault();
                window.location.href = '<?= url('admin/pegawai') ?>';
            }
            if (e.ctrlKey && e.shiftKey && e.key === 'F') {
                e.preventDefault();
                window.location.href = '<?= url('admin/keuangan') ?>';
            }
            if (e.ctrlKey && e.shiftKey && e.key === 'A') {
                e.preventDefault();
                window.location.href = '<?= url('admin/aset') ?>';
            }
            if (e.key === '?' && !e.target.closest('input, textarea')) {
                e.preventDefault();
                showKeyboardShortcuts();
            }
        });
        
        // ===== Session Timeout Warning (5 menit sebelum habis) =====
        const SESSION_TIMEOUT = 120 * 60 * 1000; // 120 menit
        const WARNING_BEFORE = 5 * 60 * 1000;    // 5 menit
        let timeoutWarning;
        
        function resetSessionTimer() {
            clearTimeout(timeoutWarning);
            timeoutWarning = setTimeout(() => {
                document.getElementById('timeoutModal').style.display = 'block';
                let countdown = 60;
                const el = document.getElementById('timeoutCountdown');
                const interval = setInterval(() => {
                    countdown--;
                    if (el) el.textContent = countdown;
                    if (countdown <= 0) {
                        clearInterval(interval);
                        window.location.href = '<?= url('admin/logout') ?>';
                    }
                }, 1000);
            }, SESSION_TIMEOUT - WARNING_BEFORE);
        }
        
        ['click', 'keypress', 'mousemove'].forEach(evt => 
            document.addEventListener(evt, resetSessionTimer, { passive: true })
        );
        resetSessionTimer();
        
    })();
    </script>
</body>
</html>