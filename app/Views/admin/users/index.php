<?php
use App\Helpers\Auth;
$isSuperAdmin = Auth::hasRole('super_admin');

// ===== ROLE & STATUS MAP =====
$roleMap = [
    'super_admin'         => ['Super Admin', 'bi-shield-fill-check', '#ef4444'],
    'admin_bauk'          => ['Admin BAUK', 'bi-briefcase-fill', '#3b82f6'],
    'admin_kepegawaian'   => ['Admin Kepegawaian', 'bi-person-badge-fill', '#10b981'],
    'admin_keuangan'      => ['Admin Keuangan', 'bi-wallet2', '#f59e0b'],
    'admin_aset'          => ['Admin Aset', 'bi-box-seam-fill', '#8b5cf6'],
    'staff'               => ['Staff', 'bi-person-workspace', '#06b6d4'],
    'viewer'              => ['Viewer', 'bi-eye-fill', '#64748b'],
];

$statusMap = [
    'active'    => ['Aktif', 'bi-check-circle-fill', '#10b981'],
    'inactive'  => ['Nonaktif', 'bi-pause-circle-fill', '#f59e0b'],
    'suspended' => ['Suspended', 'bi-ban-fill', '#ef4444'],
];

// ===== STATS =====
try {
    $statTotal       = count($users);
    $statActive      = count(array_filter($users, fn($u) => $u['status'] === 'active'));
    $statAdmin       = count(array_filter($users, fn($u) => in_array($u['role'], ['super_admin', 'admin_bauk'])));
    $statSuspended   = count(array_filter($users, fn($u) => $u['status'] === 'suspended'));
    $stat2FA         = count(array_filter($users, fn($u) => !empty($u['two_factor_enabled'])));
    $statNewMonth    = count(array_filter($users, fn($u) => strtotime($u['created_at'] ?? 'now') >= strtotime('-30 days')));
} catch (\Throwable $e) {
    $statTotal = $statActive = $statAdmin = $statSuspended = $stat2FA = $statNewMonth = 0;
}

// Role counts
$roleCounts = [];
foreach ($users as $u) {
    $roleCounts[$u['role']] = ($roleCounts[$u['role']] ?? 0) + 1;
}

$currentView = $_GET['view'] ?? 'table';
$activeRole = $_GET['role'] ?? '';
$activeStatus = $_GET['status'] ?? '';
$searchQuery = $_GET['q'] ?? '';

// Filter users
$filteredUsers = $users;
if ($activeRole !== '') $filteredUsers = array_filter($filteredUsers, fn($u) => $u['role'] === $activeRole);
if ($activeStatus !== '') $filteredUsers = array_filter($filteredUsers, fn($u) => $u['status'] === $activeStatus);
if ($searchQuery !== '') {
    $sq = strtolower($searchQuery);
    $filteredUsers = array_filter($filteredUsers, function($u) use ($sq) {
        return stripos($u['username'], $sq) !== false || 
               stripos($u['full_name'], $sq) !== false || 
               stripos($u['email'], $sq) !== false;
    });
}
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-users-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-users-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-users-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#3b82f6);}
.ult-users-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-users-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-users-stat-info{flex:1;min-width:0;}
.ult-users-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-users-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-users-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-users-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-users-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-users-dropdown{position:relative;display:inline-block;}
.ult-users-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:160px;justify-content:space-between;}
.ult-users-dropdown-trigger:hover{border-color:var(--admin-primary);background:var(--admin-bg);}
.ult-users-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.05);}
.ult-users-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-users-dropdown.open .ult-users-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-users-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:300px;overflow-y:auto;}
.ult-users-dropdown.open .ult-users-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-users-dropdown-menu.right{left:auto;right:0;}
.ult-users-dd-search{padding:.625rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);}
.ult-users-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;font-size:.825rem;background:var(--admin-bg);}
.ult-users-dd-search input:focus{outline:none;border-color:var(--admin-primary);}
.ult-users-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-users-dd-item:last-child{border-bottom:none;}
.ult-users-dd-item:hover{background:var(--admin-bg);}
.ult-users-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-users-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-users-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-users-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-users-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== FILTER CHIPS ===== */
.ult-users-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);align-items:center;}
.ult-users-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-users-chip i{font-size:.7rem;}
.ult-users-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-users-chip-close:hover{color:var(--admin-danger);}
.ult-users-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;}
.ult-users-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== VIEW TOGGLE ===== */
.ult-users-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-users-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-users-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-users-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-users-table-wrap{overflow-x:auto;}
.ult-users-cell{display:flex;align-items:center;gap:.875rem;min-width:240px;}
.ult-users-avatar{width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid var(--admin-border);background:var(--admin-bg);}
.ult-users-avatar-initial{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;flex-shrink:0;font-size:1rem;}
.ult-users-info{flex:1;min-width:0;}
.ult-users-name{font-weight:700;color:var(--admin-text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-users-username{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;font-family:monospace;display:flex;align-items:center;gap:.25rem;}
.ult-users-username i{font-size:.65rem;}
.ult-users-email{font-size:.825rem;color:var(--admin-text-secondary);display:flex;align-items:center;gap:.375rem;}
.ult-users-email i{font-size:.7rem;color:var(--admin-primary);}
.ult-users-role{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;color:#fff;}
.ult-users-status{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
.ult-users-lastlogin{font-size:.8rem;color:var(--admin-text-secondary);display:flex;flex-direction:column;gap:.125rem;}
.ult-users-lastlogin-sub{font-size:.7rem;color:var(--admin-text-muted);display:flex;align-items:center;gap:.25rem;}
.ult-users-2fa{display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .5rem;border-radius:4px;font-size:.7rem;font-weight:700;}
.ult-users-2fa.on{background:rgba(16,185,129,.15);color:#10b981;}
.ult-users-2fa.off{background:var(--admin-bg);color:var(--admin-text-muted);}

/* ===== ROW ACTION ===== */
.ult-users-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-users-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-users-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-users-row-btn.view:hover{color:var(--admin-primary);background:rgba(26,82,118,.08);}
.ult-users-row-btn.edit:hover{color:#f59e0b;background:rgba(245,158,11,.08);}
.ult-users-row-btn.toggle:hover{color:#10b981;background:rgba(16,185,129,.08);}
.ult-users-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-users-row-menu{min-width:240px;}
.ult-users-row-menu .ult-users-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-users-row-menu .ult-users-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-users-row-menu .ult-users-dd-item.danger{color:var(--admin-danger);}
.ult-users-row-menu .ult-users-dd-item.danger:hover{background:rgba(239,68,68,.08);}
.ult-users-row-menu .ult-users-dd-item.success{color:#10b981;}
.ult-users-row-menu .ult-users-dd-item.success:hover{background:rgba(16,185,129,.08);}

/* ===== GRID VIEW ===== */
.ult-users-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;padding:1.5rem;}
.ult-users-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;transition:all .3s;position:relative;}
.ult-users-card:hover{transform:translateY(-4px);box-shadow:0 12px 35px rgba(0,0,0,.1);border-color:var(--admin-primary);}
.ult-users-card-accent{height:4px;}
.ult-users-card-header{padding:1.25rem;display:flex;align-items:center;gap:1rem;}
.ult-users-card-avatar{width:60px;height:60px;border-radius:50%;object-fit:cover;border:3px solid var(--admin-border);background:var(--admin-bg);}
.ult-users-card-avatar-initial{width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:1.5rem;}
.ult-users-card-info{flex:1;min-width:0;}
.ult-users-card-name{font-size:1.05rem;font-weight:700;color:var(--admin-text);margin-bottom:.125rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-users-card-username{font-size:.75rem;color:var(--admin-text-muted);font-family:monospace;display:flex;align-items:center;gap:.25rem;}
.ult-users-card-username i{font-size:.65rem;}
.ult-users-card-body{padding:0 1.25rem 1.25rem;}
.ult-users-card-row{display:flex;justify-content:space-between;align-items:center;padding:.375rem 0;font-size:.825rem;}
.ult-users-card-row span:first-child{color:var(--admin-text-muted);font-weight:600;display:flex;align-items:center;gap:.375rem;}
.ult-users-card-row span:first-child i{font-size:.75rem;color:var(--admin-primary);}
.ult-users-card-row span:last-child{color:var(--admin-text);font-weight:500;}
.ult-users-card-footer{display:flex;gap:.375rem;padding:1rem 1.25rem;border-top:1px solid var(--admin-border);background:var(--admin-bg);}
.ult-users-card-btn{flex:1;padding:.5rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:6px;font-size:.78rem;font-weight:600;color:var(--admin-text);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.375rem;transition:all .2s;text-decoration:none!important;}
.ult-users-card-btn:hover{background:var(--admin-primary);color:#fff!important;border-color:var(--admin-primary);}
.ult-users-card-btn.danger:hover{background:var(--admin-danger);border-color:var(--admin-danger);}
.ult-users-card-actions{position:absolute;top:.75rem;right:.75rem;}

/* ===== ROLE DISTRIBUTION BAR ===== */
.ult-users-role-bar{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;margin-bottom:1.5rem;}
.ult-users-role-bar-title{font-size:.85rem;font-weight:700;color:var(--admin-text);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
.ult-users-role-bar-title i{color:var(--admin-primary);}
.ult-users-role-bar-track{display:flex;height:10px;background:var(--admin-bg);border-radius:50px;overflow:hidden;margin-bottom:.75rem;}
.ult-users-role-bar-segment{height:100%;transition:width .5s ease;}
.ult-users-role-bar-legend{display:flex;flex-wrap:wrap;gap:1rem;}
.ult-users-role-legend-item{display:flex;align-items:center;gap:.375rem;font-size:.75rem;color:var(--admin-text-secondary);}
.ult-users-role-legend-dot{width:8px;height:8px;border-radius:50%;}

/* ===== EMPTY STATE ===== */
.ult-users-empty{text-align:center;padding:4rem 2rem;}
.ult-users-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:var(--admin-primary);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-users-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(26,82,118,.3);border-radius:50%;animation:ult-users-spin 20s linear infinite;}
@keyframes ult-users-spin{to{transform:rotate(360deg);}}
.ult-users-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-users-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-users-stats{grid-template-columns:repeat(2,1fr);}
    .ult-users-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-users-stats{grid-template-columns:1fr;}
    .ult-users-grid{grid-template-columns:1fr;padding:1rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-users-stats">
    <div class="ult-users-stat" style="--ult-color:#3b82f6;">
        <div class="ult-users-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-people-fill"></i></div>
        <div class="ult-users-stat-info">
            <div class="ult-users-stat-label">Total Pengguna</div>
            <div class="ult-users-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-users-stat-sub"><i class="bi bi-database-fill"></i> Terdaftar</div>
        </div>
    </div>
    <div class="ult-users-stat" style="--ult-color:#10b981;">
        <div class="ult-users-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-person-check-fill"></i></div>
        <div class="ult-users-stat-info">
            <div class="ult-users-stat-label">Akun Aktif</div>
            <div class="ult-users-stat-value"><?= number_format($statActive) ?></div>
            <div class="ult-users-stat-sub"><i class="bi bi-arrow-up-short"></i> <?= $statTotal > 0 ? round(($statActive / $statTotal) * 100) : 0 ?>% dari total</div>
        </div>
    </div>
    <div class="ult-users-stat" style="--ult-color:#8b5cf6;">
        <div class="ult-users-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-shield-fill-check"></i></div>
        <div class="ult-users-stat-info">
            <div class="ult-users-stat-label">Level Admin</div>
            <div class="ult-users-stat-value"><?= number_format($statAdmin) ?></div>
            <div class="ult-users-stat-sub"><i class="bi bi-key-fill"></i> Hak akses penuh</div>
        </div>
    </div>
    <div class="ult-users-stat" style="--ult-color:#10b981;">
        <div class="ult-users-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-shield-lock-fill"></i></div>
        <div class="ult-users-stat-info">
            <div class="ult-users-stat-label">2FA Aktif</div>
            <div class="ult-users-stat-value"><?= number_format($stat2FA) ?></div>
            <div class="ult-users-stat-sub"><i class="bi bi-lock-fill"></i> <?= $statTotal > 0 ? round(($stat2FA / $statTotal) * 100) : 0 ?>% pengguna</div>
        </div>
    </div>
</div>

<!-- ===== ROLE DISTRIBUTION BAR ===== -->
<?php if (!empty($roleCounts)): ?>
<div class="ult-users-role-bar">
    <div class="ult-users-role-bar-title"><i class="bi bi-pie-chart-fill"></i> Distribusi Role Pengguna</div>
    <div class="ult-users-role-bar-track">
        <?php foreach ($roleCounts as $role => $count): 
            $pct = $statTotal > 0 ? ($count / $statTotal) * 100 : 0;
            $rInfo = $roleMap[$role] ?? ['label' => $role, 'icon' => 'bi-person', 'color' => '#64748b'];
        ?>
        <div class="ult-users-role-bar-segment" style="width:<?= $pct ?>%;background:<?= $rInfo[2] ?>;" title="<?= $rInfo[0] ?>: <?= $count ?>"></div>
        <?php endforeach; ?>
    </div>
    <div class="ult-users-role-bar-legend">
        <?php foreach ($roleCounts as $role => $count): 
            $rInfo = $roleMap[$role] ?? ['label' => $role, 'icon' => 'bi-person', 'color' => '#64748b'];
        ?>
        <div class="ult-users-role-legend-item">
            <span class="ult-users-role-legend-dot" style="background:<?= $rInfo[2] ?>;"></span>
            <span><strong><?= $count ?></strong> <?= $rInfo[0] ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Manajemen Pengguna</h1>
        <p class="page-subtitle">Akun & hak akses sistem</p>
    </div>
    <div class="ult-users-header-actions">
        <div class="ult-users-view-toggle" id="ultUsersViewToggle">
            <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
            </button>
            <button type="button" class="<?= $currentView === 'grid' ? 'active' : '' ?>" data-view="grid" title="Tampilan Kartu">
                <i class="bi bi-grid-3x3-gap"></i> <span class="d-none-mobile">Kartu</span>
            </button>
        </div>
        <?php if ($isSuperAdmin): ?>
        <a href="<?= url('admin/pengguna/tambah') ?>" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Tambah Pengguna</a>
        <?php endif; ?>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-users-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-people-fill"></i> Daftar Pengguna</h3>
                    <span class="pgn-result-info">
                        Menampilkan <strong><?= count($filteredUsers) ?></strong> dari <strong><?= number_format($statTotal) ?></strong> pengguna
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Toolbar -->
        <form method="GET" action="<?= url('admin/pengguna') ?>" class="pgn-toolbar" style="padding:1rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="toolbar-search" style="flex:2;min-width:220px;">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Cari username, nama, atau email...">
            </div>
            
            <!-- Custom Role Dropdown -->
            <div class="ult-users-dropdown" data-dropdown>
                <input type="hidden" name="role" id="ultUsersRoleValue" value="<?= htmlspecialchars($activeRole) ?>">
                <button type="button" class="ult-users-dropdown-trigger <?= $activeRole !== '' ? 'has-value' : '' ?>" id="ultUsersRoleTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                        <?php if ($activeRole !== '' && isset($roleMap[$activeRole])): 
                            [$lbl, $icn, $clr] = $roleMap[$activeRole]; ?>
                            <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i>
                            <span><?= $lbl ?></span>
                        <?php else: ?>
                            <i class="bi bi-shield-fill" style="color:var(--admin-primary);"></i>
                            <span>Semua Role</span>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-users-dropdown-menu" style="min-width:240px;">
                    <div class="ult-users-dd-search"><input type="text" placeholder="Cari role..." data-search></div>
                    <div class="ult-users-dd-item <?= $activeRole === '' ? 'selected' : '' ?>" data-value="">
                        <i class="bi bi-shield-fill" style="color:var(--admin-text-muted);"></i><span>Semua Role</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-users-dd-divider"></div>
                    <?php foreach ($roleMap as $key => [$lbl, $icn, $clr]): 
                        $cnt = $roleCounts[$key] ?? 0;
                    ?>
                    <div class="ult-users-dd-item <?= $activeRole === $key ? 'selected' : '' ?>" data-value="<?= $key ?>">
                        <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i>
                        <span><?= $lbl ?></span>
                        <span style="margin-left:auto;font-size:.7rem;color:var(--admin-text-muted);background:var(--admin-bg);padding:.125rem .5rem;border-radius:50px;"><?= $cnt ?></span>
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Custom Status Dropdown -->
            <div class="ult-users-dropdown" data-dropdown>
                <input type="hidden" name="status" id="ultUsersStatusValue" value="<?= htmlspecialchars($activeStatus) ?>">
                <button type="button" class="ult-users-dropdown-trigger <?= $activeStatus !== '' ? 'has-value' : '' ?>" id="ultUsersStatusTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;">
                        <?php if ($activeStatus !== '' && isset($statusMap[$activeStatus])): 
                            [$lbl, $icn, $clr] = $statusMap[$activeStatus]; ?>
                            <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i>
                            <span><?= $lbl ?></span>
                        <?php else: ?>
                            <i class="bi bi-funnel" style="color:var(--admin-primary);"></i>
                            <span>Semua Status</span>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-users-dropdown-menu" style="min-width:200px;">
                    <div class="ult-users-dd-item <?= $activeStatus === '' ? 'selected' : '' ?>" data-value="">
                        <i class="bi bi-funnel" style="color:var(--admin-text-muted);"></i><span>Semua Status</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-users-dd-divider"></div>
                    <?php foreach ($statusMap as $key => [$lbl, $icn, $clr]): ?>
                    <div class="ult-users-dd-item <?= $activeStatus === $key ? 'selected' : '' ?>" data-value="<?= $key ?>">
                        <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i><span><?= $lbl ?></span><i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Terapkan</button>
            <?php if ($searchQuery !== '' || $activeRole !== '' || $activeStatus !== ''): ?>
                <a href="<?= url('admin/pengguna') ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
            <?php endif; ?>
        </form>
        
        <!-- Filter Chips -->
        <?php if ($searchQuery !== '' || $activeRole !== '' || $activeStatus !== ''): ?>
        <div class="ult-users-chips">
            <span class="ult-users-chips-label"><i class="bi bi-funnel-fill"></i> Filter Aktif:</span>
            <?php if ($searchQuery !== ''): ?>
                <span class="ult-users-chip">
                    <i class="bi bi-search"></i> Pencarian: "<strong><?= htmlspecialchars($searchQuery) ?></strong>"
                    <a href="<?= url('admin/pengguna') . '?role=' . $activeRole . '&status=' . $activeStatus ?>" class="ult-users-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($activeRole !== '' && isset($roleMap[$activeRole])): ?>
                <span class="ult-users-chip">
                    <i class="bi <?= $roleMap[$activeRole][1] ?>"></i> Role: <strong><?= $roleMap[$activeRole][0] ?></strong>
                    <a href="<?= url('admin/pengguna') . '?q=' . $searchQuery . '&status=' . $activeStatus ?>" class="ult-users-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($activeStatus !== '' && isset($statusMap[$activeStatus])): ?>
                <span class="ult-users-chip">
                    <i class="bi <?= $statusMap[$activeStatus][1] ?>"></i> Status: <strong><?= $statusMap[$activeStatus][0] ?></strong>
                    <a href="<?= url('admin/pengguna') . '?q=' . $searchQuery . '&role=' . $activeRole ?>" class="ult-users-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <a href="<?= url('admin/pengguna') ?>" class="ult-users-chips-clear"><i class="bi bi-x-circle"></i> Hapus Semua</a>
        </div>
        <?php endif; ?>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultUsersTableView" class="ult-users-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width:260px;">Pengguna</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Login Terakhir</th>
                        <th>2FA</th>
                        <th>Status</th>
                        <th style="width:140px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filteredUsers as $u): 
                    [$rLbl, $rIcn, $rClr] = $roleMap[$u['role']] ?? [$u['role'], 'bi-person', '#64748b'];
                    [$sLbl, $sIcn, $sClr] = $statusMap[$u['status']] ?? [$u['status'], 'bi-circle-fill', '#64748b'];
                    $avatarUrl = !empty($u['avatar']) ? url('uploads/profil/' . $u['avatar']) : null;
                    $avatarColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899'];
                    $avatarColor = $avatarColors[crc32($u['username'] ?? '') % count($avatarColors)];
                    $lastLogin = $u['last_login_at'] ?? null;
                ?>
                    <tr>
                        <td>
                            <div class="ult-users-cell">
                                <?php if ($avatarUrl): ?>
                                    <img src="<?= $avatarUrl ?>" alt="" class="ult-users-avatar">
                                <?php else: ?>
                                    <div class="ult-users-avatar-initial" style="background:linear-gradient(135deg,<?= $avatarColor ?>,<?= $avatarColor ?>cc);">
                                        <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="ult-users-info">
                                    <div class="ult-users-name"><?= htmlspecialchars($u['full_name']) ?></div>
                                    <div class="ult-users-username"><i class="bi bi-at"></i> <?= htmlspecialchars($u['username']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="ult-users-email">
                                <i class="bi bi-envelope-fill"></i>
                                <span><?= htmlspecialchars($u['email']) ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="ult-users-role" style="background:<?= $rClr ?>;">
                                <i class="bi <?= $rIcn ?>"></i> <?= $rLbl ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($lastLogin): ?>
                            <div class="ult-users-lastlogin">
                                <span><?= date('d M Y', strtotime($lastLogin)) ?></span>
                                <span class="ult-users-lastlogin-sub"><i class="bi bi-clock"></i> <?= date('H:i', strtotime($lastLogin)) ?> WITA</span>
                            </div>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:.825rem;">Belum pernah</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($u['two_factor_enabled'])): ?>
                            <span class="ult-users-2fa on"><i class="bi bi-shield-fill-check"></i> ON</span>
                            <?php else: ?>
                            <span class="ult-users-2fa off"><i class="bi bi-shield"></i> OFF</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="ult-users-status" style="background:<?= $sClr ?>20;color:<?= $sClr ?>;">
                                <i class="bi <?= $sIcn ?>"></i> <?= $sLbl ?>
                            </span>
                        </td>
                        <td>
                            <div class="ult-users-row-actions">
                                <?php if ($isSuperAdmin && $u['id'] != Auth::id()): ?>
                                <a href="<?= url('admin/pengguna/edit/' . $u['id']) ?>" class="ult-users-row-btn edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                <?php endif; ?>
                                
                                <div class="ult-users-dropdown" data-dropdown>
                                    <button type="button" class="ult-users-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="ult-users-dropdown-menu right ult-users-row-menu">
                                        <div class="ult-users-dd-header">Akun</div>
                                        <a href="#" class="ult-users-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Detail pengguna segera hadir','info');return false;">
                                            <i class="bi bi-eye-fill"></i><span>Lihat Detail</span>
                                        </a>
                                        <?php if ($isSuperAdmin && $u['id'] != Auth::id()): ?>
                                        <a href="<?= url('admin/pengguna/edit/' . $u['id']) ?>" class="ult-users-dd-item">
                                            <i class="bi bi-pencil-square"></i><span>Edit Pengguna</span>
                                        </a>
                                        <div class="ult-users-dd-divider"></div>
                                        <div class="ult-users-dd-header">Keamanan</div>
                                        <a href="#" class="ult-users-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Reset password segera hadir','info');return false;">
                                            <i class="bi bi-key-fill"></i><span>Reset Password</span>
                                        </a>
                                        <a href="#" class="ult-users-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Toggle 2FA segera hadir','info');return false;">
                                            <i class="bi bi-shield-lock-fill"></i><span><?= !empty($u['two_factor_enabled']) ? 'Nonaktifkan 2FA' : 'Aktifkan 2FA' ?></span>
                                        </a>
                                        <div class="ult-users-dd-divider"></div>
                                        <div class="ult-users-dd-header">Status</div>
                                        <a href="#" class="ult-users-dd-item <?= $u['status'] === 'active' ? 'danger' : 'success' ?>" onclick="if(typeof showToast!=='undefined')showToast('Toggle status segera hadir','info');return false;">
                                            <i class="bi bi-<?= $u['status'] === 'active' ? 'pause-circle-fill' : 'check-circle-fill' ?>"></i>
                                            <span><?= $u['status'] === 'active' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?></span>
                                        </a>
                                        <?php if ($u['status'] !== 'suspended'): ?>
                                        <a href="#" class="ult-users-dd-item danger" onclick="if(typeof showToast!=='undefined')showToast('Suspend akun segera hadir','warning');return false;">
                                            <i class="bi bi-ban-fill"></i><span>Suspend Akun</span>
                                        </a>
                                        <?php endif; ?>
                                        <div class="ult-users-dd-divider"></div>
                                        <div class="ult-users-dd-header">Administrasi</div>
                                        <a href="<?= url('admin/aktivitas') ?>?user=<?= $u['id'] ?>" class="ult-users-dd-item">
                                            <i class="bi bi-clock-history"></i><span>Riwayat Aktivitas</span>
                                        </a>
                                        <a href="<?= url('admin/pengguna/hapus/' . $u['id']) ?>" class="ult-users-dd-item danger" onclick="return confirm('Hapus pengguna <?= htmlspecialchars(addslashes($u['username'])) ?>? Tindakan ini tidak dapat dibatalkan.');">
                                            <i class="bi bi-trash3-fill"></i><span>Hapus Pengguna</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$filteredUsers): ?>
                    <tr><td colspan="7">
                        <div class="ult-users-empty">
                            <div class="ult-users-empty-icon"><i class="bi bi-people"></i></div>
                            <h3>Tidak Ada Pengguna</h3>
                            <p>
                                <?php if ($searchQuery !== '' || $activeRole !== '' || $activeStatus !== ''): ?>
                                    Tidak ada pengguna yang cocok dengan filter Anda. Coba ubah kriteria pencarian.
                                <?php else: ?>
                                    Belum ada pengguna dalam sistem. Mulai tambahkan pengguna pertama!
                                <?php endif; ?>
                            </p>
                            <?php if ($searchQuery !== '' || $activeRole !== '' || $activeStatus !== ''): ?>
                                <a href="<?= url('admin/pengguna') ?>" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset Filter</a>
                            <?php elseif ($isSuperAdmin): ?>
                                <a href="<?= url('admin/pengguna/tambah') ?>" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Tambah Pengguna Pertama</a>
                            <?php endif; ?>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== GRID VIEW ===== -->
        <div id="ultUsersGridView" class="ult-users-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php foreach ($filteredUsers as $u): 
                [$rLbl, $rIcn, $rClr] = $roleMap[$u['role']] ?? [$u['role'], 'bi-person', '#64748b'];
                [$sLbl, $sIcn, $sClr] = $statusMap[$u['status']] ?? [$u['status'], 'bi-circle-fill', '#64748b'];
                $avatarUrl = !empty($u['avatar']) ? url('uploads/profil/' . $u['avatar']) : null;
                $avatarColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899'];
                $avatarColor = $avatarColors[crc32($u['username'] ?? '') % count($avatarColors)];
                $lastLogin = $u['last_login_at'] ?? null;
            ?>
            <div class="ult-users-card">
                <div class="ult-users-card-accent" style="background:linear-gradient(90deg,<?= $rClr ?>,<?= $rClr ?>cc);"></div>
                <div class="ult-users-card-actions">
                    <div class="ult-users-dropdown" data-dropdown>
                        <button type="button" class="ult-users-row-btn" title="Aksi" style="background:var(--admin-surface);"><i class="bi bi-three-dots-vertical"></i></button>
                        <div class="ult-users-dropdown-menu right ult-users-row-menu">
                            <?php if ($isSuperAdmin && $u['id'] != Auth::id()): ?>
                            <a href="<?= url('admin/pengguna/edit/' . $u['id']) ?>" class="ult-users-dd-item"><i class="bi bi-pencil-square"></i> Edit</a>
                            <a href="<?= url('admin/pengguna/hapus/' . $u['id']) ?>" class="ult-users-dd-item danger" onclick="return confirm('Hapus?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="ult-users-card-header">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= $avatarUrl ?>" alt="" class="ult-users-card-avatar">
                    <?php else: ?>
                        <div class="ult-users-card-avatar-initial" style="background:linear-gradient(135deg,<?= $avatarColor ?>,<?= $avatarColor ?>cc);">
                            <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="ult-users-card-info">
                        <div class="ult-users-card-name"><?= htmlspecialchars($u['full_name']) ?></div>
                        <div class="ult-users-card-username"><i class="bi bi-at"></i> <?= htmlspecialchars($u['username']) ?></div>
                    </div>
                </div>
                
                <div class="ult-users-card-body">
                    <div class="ult-users-card-row">
                        <span><i class="bi bi-envelope-fill"></i> Email</span>
                        <span style="font-size:.78rem;"><?= htmlspecialchars($u['email']) ?></span>
                    </div>
                    <div class="ult-users-card-row">
                        <span><i class="bi bi-shield-fill"></i> Role</span>
                        <span class="ult-users-role" style="background:<?= $rClr ?>;font-size:.7rem;"><i class="bi <?= $rIcn ?>"></i> <?= $rLbl ?></span>
                    </div>
                    <div class="ult-users-card-row">
                        <span><i class="bi bi-clock-history"></i> Login Terakhir</span>
                        <span style="font-size:.78rem;"><?= $lastLogin ? date('d M Y H:i', strtotime($lastLogin)) : 'Belum pernah' ?></span>
                    </div>
                    <div class="ult-users-card-row">
                        <span><i class="bi bi-shield-lock-fill"></i> 2FA</span>
                        <?php if (!empty($u['two_factor_enabled'])): ?>
                        <span class="ult-users-2fa on" style="font-size:.7rem;"><i class="bi bi-shield-fill-check"></i> Aktif</span>
                        <?php else: ?>
                        <span class="ult-users-2fa off" style="font-size:.7rem;"><i class="bi bi-shield"></i> Nonaktif</span>
                        <?php endif; ?>
                    </div>
                    <div class="ult-users-card-row">
                        <span><i class="bi bi-toggle-on"></i> Status</span>
                        <span class="ult-users-status" style="background:<?= $sClr ?>20;color:<?= $sClr ?>;font-size:.7rem;"><i class="bi <?= $sIcn ?>"></i> <?= $sLbl ?></span>
                    </div>
                </div>
                
                <div class="ult-users-card-footer">
                    <?php if ($isSuperAdmin && $u['id'] != Auth::id()): ?>
                    <a href="<?= url('admin/pengguna/edit/' . $u['id']) ?>" class="ult-users-card-btn"><i class="bi bi-pencil-fill"></i> Edit</a>
                    <a href="<?= url('admin/pengguna/hapus/' . $u['id']) ?>" class="ult-users-card-btn danger" onclick="return confirm('Hapus pengguna ini?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                    <?php else: ?>
                    <a href="#" class="ult-users-card-btn" onclick="if(typeof showToast!=='undefined')showToast('Detail pengguna segera hadir','info');return false;"><i class="bi bi-eye-fill"></i> Detail</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (!$filteredUsers): ?>
            <div class="ult-users-empty" style="grid-column:1/-1;">
                <div class="ult-users-empty-icon"><i class="bi bi-people"></i></div>
                <h3>Tidak Ada Pengguna</h3>
                <p>Belum ada pengguna yang cocok dengan filter.</p>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function(){
    'use strict';
    
    // ===== CUSTOM DROPDOWN =====
    document.querySelectorAll('.ult-users-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-users-dropdown-trigger, .ult-users-row-btn');
        const menu = dd.querySelector('.ult-users-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-users-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
            if(dd.classList.contains('open')){
                const s = menu.querySelector('[data-search]');
                if(s) setTimeout(() => s.focus(), 50);
            }
        });
        menu.querySelectorAll('.ult-users-dd-item[data-value]').forEach(function(item){
            item.addEventListener('click', function(e){
                e.preventDefault();
                const val = this.getAttribute('data-value');
                const lbl = this.getAttribute('data-label') || this.querySelector('span').textContent.trim();
                const hidden = dd.querySelector('input[type="hidden"]');
                if(hidden) hidden.value = val;
                const span = trigger.querySelector('span');
                if(span){
                    const icon = trigger.querySelector('i:not(.chevron)');
                    if(icon){
                        span.innerHTML = '';
                        span.appendChild(icon.cloneNode(true));
                        const ts = document.createElement('span');
                        ts.style.cssText = 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
                        ts.textContent = lbl;
                        span.appendChild(ts);
                    } else { span.textContent = lbl; }
                }
                menu.querySelectorAll('.ult-users-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
                if(hidden && (hidden.name === 'role' || hidden.name === 'status')){
                    const form = dd.closest('form');
                    if(form) form.submit();
                }
            });
        });
        const s = menu.querySelector('[data-search]');
        if(s){
            s.addEventListener('input', function(){
                const q = this.value.toLowerCase();
                menu.querySelectorAll('.ult-users-dd-item').forEach(i => {
                    if(i.hasAttribute('data-value')) i.style.display = i.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-users-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultUsersViewToggle');
    const tv = document.getElementById('ultUsersTableView');
    const gv = document.getElementById('ultUsersGridView');
    if(vt){
        vt.querySelectorAll('button[data-view]').forEach(btn => {
            btn.addEventListener('click', function(){
                const v = this.getAttribute('data-view');
                vt.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                if(tv && gv){
                    if(v === 'grid'){ tv.style.display = 'none'; gv.style.display = 'grid'; }
                    else { tv.style.display = ''; gv.style.display = 'none'; }
                }
                try { localStorage.setItem('ult_users_view', v); } catch(e){}
            });
        });
    }
})();
</script>