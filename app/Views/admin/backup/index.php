<?php
use App\Helpers\Auth;
$isSuperAdmin = Auth::hasRole('super_admin');

if (!$isSuperAdmin) {
    echo '<div style="padding:2rem;text-align:center;"><h2>Akses Ditolak</h2><p>Hanya Super Administrator yang dapat mengakses halaman ini.</p><a href="' . url('admin') . '" class="btn btn-primary">Kembali</a></div>';
    return;
}

// ===== STATS =====
try {
    $statTotal      = count($files);
    $statTotalSize  = array_sum(array_column($files, 'size'));
    $statLatest     = $statTotal > 0 ? $files[0]['date'] : 0;
    $statOldest     = $statTotal > 0 ? end($files)['date'] : 0;
    $statThisMonth  = count(array_filter($files, fn($f) => date('Y-m', $f['date']) === date('Y-m')));
    
    // Database size estimate
    $db = \App\Helpers\Database::getInstance();
    $dbSize = $db->fetch("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.TABLES WHERE table_schema = DATABASE()")['size_mb'] ?? 0;
} catch (\Throwable $e) {
    $statTotal = $statTotalSize = $statLatest = $statOldest = $statThisMonth = $dbSize = 0;
}

function backupFormatSize($bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    return round($bytes / 1024, 2) . ' KB';
}

function backupTimeAgo($timestamp): string {
    $diff = time() - $timestamp;
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 2592000) return floor($diff / 86400) . ' hari lalu';
    return date('d M Y', $timestamp);
}

$currentView = $_GET['view'] ?? 'table';
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-backup-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-backup-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-backup-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#3b82f6);}
.ult-backup-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-backup-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-backup-stat-info{flex:1;min-width:0;}
.ult-backup-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-backup-stat-value{font-size:1.4rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-backup-stat-value.money{font-size:1.15rem;}
.ult-backup-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-backup-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-backup-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== VIEW TOGGLE ===== */
.ult-backup-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-backup-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-backup-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-backup-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== CUSTOM DROPDOWN ===== */
.ult-backup-dropdown{position:relative;display:inline-block;}
.ult-backup-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:150px;justify-content:space-between;}
.ult-backup-dropdown-trigger:hover{border-color:var(--admin-primary);background:var(--admin-bg);}
.ult-backup-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.05);}
.ult-backup-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-backup-dropdown.open .ult-backup-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-backup-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;}
.ult-backup-dropdown.open .ult-backup-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-backup-dropdown-menu.right{left:auto;right:0;}
.ult-backup-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-backup-dd-item:last-child{border-bottom:none;}
.ult-backup-dd-item:hover{background:var(--admin-bg);}
.ult-backup-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-backup-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-backup-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-backup-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-backup-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== FILTER CHIPS ===== */
.ult-backup-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);align-items:center;}
.ult-backup-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-backup-chip i{font-size:.7rem;}
.ult-backup-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-backup-chip-close:hover{color:var(--admin-danger);}
.ult-backup-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;}
.ult-backup-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-backup-table-wrap{overflow-x:auto;}
.ult-backup-cell{display:flex;align-items:center;gap:.875rem;min-width:260px;}
.ult-backup-file-icon{width:48px;height:48px;border-radius:var(--radius-md);background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;position:relative;}
.ult-backup-file-ext{position:absolute;bottom:-4px;right:-4px;background:#fff;color:var(--admin-text);font-size:.6rem;font-weight:800;padding:.0625rem .375rem;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,.15);}
.ult-backup-info{flex:1;min-width:0;}
.ult-backup-filename{font-weight:700;color:var(--admin-text);font-family:monospace;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-backup-filesize{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;display:flex;align-items:center;gap:.25rem;}
.ult-backup-filesize i{font-size:.65rem;}
.ult-backup-date{display:flex;flex-direction:column;gap:.125rem;}
.ult-backup-date-main{font-weight:600;font-size:.875rem;color:var(--admin-text);}
.ult-backup-date-sub{font-size:.7rem;color:var(--admin-text-muted);display:flex;align-items:center;gap:.25rem;}
.ult-backup-date-sub i{font-size:.65rem;}
.ult-backup-age{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;border-radius:50px;font-size:.75rem;font-weight:600;}
.ult-backup-age.recent{background:rgba(16,185,129,.15);color:#10b981;}
.ult-backup-age.old{background:rgba(245,158,11,.15);color:#f59e0b;}
.ult-backup-age.ancient{background:rgba(239,68,68,.15);color:#ef4444;}

/* ===== ROW ACTION ===== */
.ult-backup-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-backup-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-backup-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-backup-row-btn.download:hover{color:#10b981;background:rgba(16,185,129,.08);}
.ult-backup-row-btn.verify:hover{color:#3b82f6;background:rgba(59,130,246,.08);}
.ult-backup-row-btn.restore:hover{color:#f59e0b;background:rgba(245,158,11,.08);}
.ult-backup-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-backup-row-menu{min-width:220px;}
.ult-backup-row-menu .ult-backup-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-backup-row-menu .ult-backup-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-backup-row-menu .ult-backup-dd-item.danger{color:var(--admin-danger);}
.ult-backup-row-menu .ult-backup-dd-item.danger:hover{background:rgba(239,68,68,.08);}
.ult-backup-row-menu .ult-backup-dd-item.success{color:#10b981;}
.ult-backup-row-menu .ult-backup-dd-item.success:hover{background:rgba(16,185,129,.08);}

/* ===== GRID VIEW ===== */
.ult-backup-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.25rem;padding:1.5rem;}
.ult-backup-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;transition:all .3s;position:relative;}
.ult-backup-card:hover{transform:translateY(-4px);box-shadow:0 12px 35px rgba(0,0,0,.1);border-color:var(--admin-primary);}
.ult-backup-card-accent{height:4px;background:linear-gradient(90deg,#3b82f6,#2563eb);}
.ult-backup-card-body{padding:1.25rem;}
.ult-backup-card-header{display:flex;align-items:center;gap:1rem;margin-bottom:1rem;}
.ult-backup-card-icon{width:56px;height:56px;border-radius:var(--radius-md);background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;position:relative;}
.ult-backup-card-icon .ult-backup-file-ext{font-size:.65rem;}
.ult-backup-card-info{flex:1;min-width:0;}
.ult-backup-card-filename{font-family:monospace;font-size:.85rem;font-weight:700;color:var(--admin-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-backup-card-date{font-size:.75rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.375rem;}
.ult-backup-card-date i{font-size:.7rem;}
.ult-backup-card-meta{display:flex;flex-direction:column;gap:.5rem;margin-bottom:1rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
.ult-backup-card-row{display:flex;justify-content:space-between;align-items:center;font-size:.825rem;}
.ult-backup-card-row span:first-child{color:var(--admin-text-muted);font-weight:600;display:flex;align-items:center;gap:.375rem;}
.ult-backup-card-row span:first-child i{font-size:.75rem;color:var(--admin-primary);}
.ult-backup-card-row span:last-child{color:var(--admin-text);font-weight:500;}
.ult-backup-card-footer{display:flex;gap:.375rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
.ult-backup-card-btn{flex:1;padding:.5rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:6px;font-size:.78rem;font-weight:600;color:var(--admin-text);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.375rem;transition:all .2s;text-decoration:none!important;}
.ult-backup-card-btn:hover{background:var(--admin-primary);color:#fff!important;border-color:var(--admin-primary);}
.ult-backup-card-btn.download:hover{background:#10b981;border-color:#10b981;}
.ult-backup-card-btn.restore:hover{background:#f59e0b;border-color:#f59e0b;}
.ult-backup-card-btn.danger:hover{background:var(--admin-danger);border-color:var(--admin-danger);}
.ult-backup-card-actions{position:absolute;top:.75rem;right:.75rem;}

/* ===== INFO PANELS ===== */
.ult-backup-info-panel{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1.5rem;}
.ult-backup-info-header{padding:1rem 1.25rem;background:linear-gradient(to right, var(--admin-bg), transparent);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-backup-info-header i{font-size:1.1rem;color:var(--admin-primary);}
.ult-backup-info-header h4{font-size:.95rem;font-weight:700;margin:0;}
.ult-backup-info-body{padding:1.25rem;}
.ult-backup-info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;}
.ult-backup-info-item{display:flex;align-items:center;gap:.75rem;padding:.75rem;background:var(--admin-bg);border-radius:var(--radius-md);}
.ult-backup-info-item-icon{width:36px;height:36px;border-radius:var(--radius-sm);background:var(--admin-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;}
.ult-backup-info-item-label{font-size:.7rem;color:var(--admin-text-muted);text-transform:uppercase;letter-spacing:.5px;}
.ult-backup-info-item-value{font-size:.9rem;font-weight:700;color:var(--admin-text);}

/* ===== EMPTY STATE ===== */
.ult-backup-empty{text-align:center;padding:4rem 2rem;}
.ult-backup-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:var(--admin-primary);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-backup-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(26,82,118,.3);border-radius:50%;animation:ult-backup-spin 20s linear infinite;}
@keyframes ult-backup-spin{to{transform:rotate(360deg);}}
.ult-backup-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-backup-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-backup-stats{grid-template-columns:repeat(2,1fr);}
    .ult-backup-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-backup-stats{grid-template-columns:1fr;}
    .ult-backup-grid{grid-template-columns:1fr;padding:1rem;}
    .ult-backup-info-grid{grid-template-columns:1fr;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-backup-stats">
    <div class="ult-backup-stat" style="--ult-color:#3b82f6;">
        <div class="ult-backup-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-archive-fill"></i></div>
        <div class="ult-backup-stat-info">
            <div class="ult-backup-stat-label">Total Backup</div>
            <div class="ult-backup-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-backup-stat-sub"><i class="bi bi-database-fill"></i> File tersimpan</div>
        </div>
    </div>
    <div class="ult-backup-stat" style="--ult-color:#10b981;">
        <div class="ult-backup-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-hdd-fill"></i></div>
        <div class="ult-backup-stat-info">
            <div class="ult-backup-stat-label">Total Ukuran</div>
            <div class="ult-backup-stat-value money"><?= backupFormatSize($statTotalSize) ?></div>
            <div class="ult-backup-stat-sub"><i class="bi bi-graph-up"></i> Semua file</div>
        </div>
    </div>
    <div class="ult-backup-stat" style="--ult-color:#8b5cf6;">
        <div class="ult-backup-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-calendar-check-fill"></i></div>
        <div class="ult-backup-stat-info">
            <div class="ult-backup-stat-label">Backup Bulan Ini</div>
            <div class="ult-backup-stat-value"><?= number_format($statThisMonth) ?></div>
            <div class="ult-backup-stat-sub"><i class="bi bi-clock-history"></i> <?= date('F Y') ?></div>
        </div>
    </div>
    <div class="ult-backup-stat" style="--ult-color:#f59e0b;">
        <div class="ult-backup-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="bi bi-database-fill-gear"></i></div>
        <div class="ult-backup-stat-info">
            <div class="ult-backup-stat-label">Ukuran Database</div>
            <div class="ult-backup-stat-value money"><?= number_format($dbSize, 2) ?> MB</div>
            <div class="ult-backup-stat-sub"><i class="bi bi-activity"></i> Live estimate</div>
        </div>
    </div>
</div>

<!-- ===== INFO PANELS ===== -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="ult-backup-info-panel">
        <div class="ult-backup-info-header">
            <i class="bi bi-info-circle-fill"></i>
            <h4>Informasi Backup</h4>
        </div>
        <div class="ult-backup-info-body">
            <div class="ult-backup-info-grid">
                <div class="ult-backup-info-item">
                    <div class="ult-backup-info-item-icon"><i class="bi bi-folder-fill"></i></div>
                    <div>
                        <div class="ult-backup-info-item-label">Lokasi</div>
                        <div class="ult-backup-info-item-value" style="font-size:.75rem;">database/backups/</div>
                    </div>
                </div>
                <div class="ult-backup-info-item">
                    <div class="ult-backup-info-item-icon" style="background:#10b981;"><i class="bi bi-filetype-sql"></i></div>
                    <div>
                        <div class="ult-backup-info-item-label">Format</div>
                        <div class="ult-backup-info-item-value">SQL (.sql)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="ult-backup-info-panel">
        <div class="ult-backup-info-header">
            <i class="bi bi-clock-fill"></i>
            <h4>Riwayat Backup</h4>
        </div>
        <div class="ult-backup-info-body">
            <div class="ult-backup-info-grid">
                <div class="ult-backup-info-item">
                    <div class="ult-backup-info-item-icon" style="background:#10b981;"><i class="bi bi-calendar-check-fill"></i></div>
                    <div>
                        <div class="ult-backup-info-item-label">Terbaru</div>
                        <div class="ult-backup-info-item-value" style="font-size:.75rem;"><?= $statLatest > 0 ? backupTimeAgo($statLatest) : 'Belum ada' ?></div>
                    </div>
                </div>
                <div class="ult-backup-info-item">
                    <div class="ult-backup-info-item-icon" style="background:#f59e0b;"><i class="bi bi-calendar-x-fill"></i></div>
                    <div>
                        <div class="ult-backup-info-item-label">Terlama</div>
                        <div class="ult-backup-info-item-value" style="font-size:.75rem;"><?= $statOldest > 0 ? backupTimeAgo($statOldest) : 'Belum ada' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Backup & Restore Database</h1>
        <p class="page-subtitle">️ Hanya untuk Super Administrator</p>
    </div>
    <div class="ult-backup-header-actions">
        <div class="ult-backup-view-toggle" id="ultBackupViewToggle">
            <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
            </button>
            <button type="button" class="<?= $currentView === 'grid' ? 'active' : '' ?>" data-view="grid" title="Tampilan Kartu">
                <i class="bi bi-grid-3x3-gap"></i> <span class="d-none-mobile">Kartu</span>
            </button>
        </div>
        <a href="<?= url('admin/backup/buat') ?>" class="btn btn-primary" onclick="if(typeof showLoading==='function')showLoading();">
            <i class="bi bi-database-add"></i> Buat Backup Sekarang
        </a>
    </div>
</div>

<!-- ===== ALERT INFO ===== -->
<div class="alert alert-success" style="background:#d1fae5;color:#065f46;border-color:#6ee7b7;margin-bottom:1.5rem;">
    <i class="bi bi-shield-check" style="font-size:1.25rem;"></i>
    <div>
        <strong>Backup otomatis</strong> menyimpan seluruh data database ke file <code>.sql</code> di folder <code>database/backups/</code>. Pastikan folder memiliki izin tulis. File backup dapat diunduh atau di-restore kapan saja.
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-backup-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-archive-fill"></i> Arsip Backup</h3>
                    <span class="pgn-result-info">
                        Total <strong><?= number_format($statTotal) ?></strong> file backup
                        <?php if ($statTotalSize > 0): ?>
                            <span style="color:var(--admin-text-muted);">• <?= backupFormatSize($statTotalSize) ?></span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultBackupTableView" class="ult-backup-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width:280px;">Nama File</th>
                        <th>Ukuran</th>
                        <th>Tanggal Dibuat</th>
                        <th>Umur</th>
                        <th style="width:180px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($files as $f): 
                    $age = time() - $f['date'];
                    $ageClass = $age < 86400 ? 'recent' : ($age < 604800 ? 'old' : 'ancient');
                    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                ?>
                    <tr>
                        <td>
                            <div class="ult-backup-cell">
                                <div class="ult-backup-file-icon">
                                    <i class="bi bi-file-earmark-code"></i>
                                    <span class="ult-backup-file-ext"><?= strtoupper($ext) ?></span>
                                </div>
                                <div class="ult-backup-info">
                                    <div class="ult-backup-filename"><?= htmlspecialchars($f['name']) ?></div>
                                    <div class="ult-backup-filesize"><i class="bi bi-hdd"></i> <?= backupFormatSize($f['size']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-weight:600;color:var(--admin-text);"><?= backupFormatSize($f['size']) ?></td>
                        <td>
                            <div class="ult-backup-date">
                                <span class="ult-backup-date-main"><?= date('d M Y', $f['date']) ?></span>
                                <span class="ult-backup-date-sub"><i class="bi bi-clock"></i> <?= date('H:i:s', $f['date']) ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="ult-backup-age <?= $ageClass ?>">
                                <i class="bi bi-<?= $age < 86400 ? 'clock-history' : ($age < 604800 ? 'calendar-week' : 'calendar-x') ?>"></i>
                                <?= backupTimeAgo($f['date']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="ult-backup-row-actions">
                                <a href="<?= url('admin/backup/download/' . $f['name']) ?>" class="ult-backup-row-btn download" title="Unduh"><i class="bi bi-download"></i></a>
                                
                                <div class="ult-backup-dropdown" data-dropdown>
                                    <button type="button" class="ult-backup-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="ult-backup-dropdown-menu right ult-backup-row-menu">
                                        <div class="ult-backup-dd-header">File Backup</div>
                                        <a href="<?= url('admin/backup/download/' . $f['name']) ?>" class="ult-backup-dd-item success">
                                            <i class="bi bi-download"></i><span>Unduh File</span>
                                        </a>
                                        <a href="#" class="ult-backup-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Verify integrity segera hadir','info');return false;">
                                            <i class="bi bi-shield-check"></i><span>Verifikasi Integritas</span>
                                        </a>
                                        <div class="ult-backup-dd-divider"></div>
                                        <div class="ult-backup-dd-header">Informasi</div>
                                        <div class="ult-backup-dd-item" style="cursor:default;">
                                            <i class="bi bi-hdd-fill"></i>
                                            <span>Ukuran: <?= backupFormatSize($f['size']) ?></span>
                                        </div>
                                        <div class="ult-backup-dd-item" style="cursor:default;">
                                            <i class="bi bi-calendar-event"></i>
                                            <span>Dibuat: <?= date('d M Y H:i:s', $f['date']) ?></span>
                                        </div>
                                        <div class="ult-backup-dd-item" style="cursor:default;">
                                            <i class="bi bi-clock-history"></i>
                                            <span>Umur: <?= backupTimeAgo($f['date']) ?></span>
                                        </div>
                                        <div class="ult-backup-dd-divider"></div>
                                        <div class="ult-backup-dd-header">Restore</div>
                                        <a href="#" class="ult-backup-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Fitur restore segera hadir','warning');return false;">
                                            <i class="bi bi-arrow-counterclockwise"></i><span>Restore Database</span>
                                        </a>
                                        <div class="ult-backup-dd-divider"></div>
                                        <a href="<?= url('admin/backup/hapus/' . $f['name']) ?>" class="ult-backup-dd-item danger" onclick="return confirm('Hapus file backup <?= htmlspecialchars(addslashes($f['name'])) ?>?\n\nTindakan ini tidak dapat dibatalkan!');">
                                            <i class="bi bi-trash3-fill"></i><span>Hapus Backup</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$files): ?>
                    <tr><td colspan="5">
                        <div class="ult-backup-empty">
                            <div class="ult-backup-empty-icon"><i class="bi bi-archive"></i></div>
                            <h3>Belum Ada Backup</h3>
                            <p>Belum ada file backup yang dibuat. Mulai backup database untuk mengamankan data sistem Anda.</p>
                            <a href="<?= url('admin/backup/buat') ?>" class="btn btn-primary" onclick="if(typeof showLoading==='function')showLoading();">
                                <i class="bi bi-database-add"></i> Buat Backup Pertama
                            </a>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== GRID VIEW ===== -->
        <div id="ultBackupGridView" class="ult-backup-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php foreach ($files as $f): 
                $age = time() - $f['date'];
                $ageClass = $age < 86400 ? 'recent' : ($age < 604800 ? 'old' : 'ancient');
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
            ?>
            <div class="ult-backup-card">
                <div class="ult-backup-card-accent"></div>
                <div class="ult-backup-card-actions">
                    <div class="ult-backup-dropdown" data-dropdown>
                        <button type="button" class="ult-backup-row-btn" title="Aksi" style="background:var(--admin-surface);"><i class="bi bi-three-dots-vertical"></i></button>
                        <div class="ult-backup-dropdown-menu right ult-backup-row-menu">
                            <a href="<?= url('admin/backup/download/' . $f['name']) ?>" class="ult-backup-dd-item success"><i class="bi bi-download"></i> Unduh</a>
                            <a href="<?= url('admin/backup/hapus/' . $f['name']) ?>" class="ult-backup-dd-item danger" onclick="return confirm('Hapus?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                        </div>
                    </div>
                </div>
                
                <div class="ult-backup-card-body">
                    <div class="ult-backup-card-header">
                        <div class="ult-backup-card-icon">
                            <i class="bi bi-file-earmark-code"></i>
                            <span class="ult-backup-file-ext"><?= strtoupper($ext) ?></span>
                        </div>
                        <div class="ult-backup-card-info">
                            <div class="ult-backup-card-filename"><?= htmlspecialchars($f['name']) ?></div>
                            <div class="ult-backup-card-date"><i class="bi bi-calendar-event"></i> <?= date('d M Y', $f['date']) ?></div>
                        </div>
                    </div>
                    
                    <div class="ult-backup-card-meta">
                        <div class="ult-backup-card-row">
                            <span><i class="bi bi-hdd-fill"></i> Ukuran File</span>
                            <span style="font-weight:700;"><?= backupFormatSize($f['size']) ?></span>
                        </div>
                        <div class="ult-backup-card-row">
                            <span><i class="bi bi-clock-fill"></i> Dibuat</span>
                            <span><?= date('H:i:s', $f['date']) ?></span>
                        </div>
                        <div class="ult-backup-card-row">
                            <span><i class="bi bi-hourglass-split"></i> Umur</span>
                            <span class="ult-backup-age <?= $ageClass ?>" style="font-size:.7rem;"><i class="bi bi-<?= $age < 86400 ? 'clock-history' : ($age < 604800 ? 'calendar-week' : 'calendar-x') ?>"></i> <?= backupTimeAgo($f['date']) ?></span>
                        </div>
                    </div>
                    
                    <div class="ult-backup-card-footer">
                        <a href="<?= url('admin/backup/download/' . $f['name']) ?>" class="ult-backup-card-btn download"><i class="bi bi-download"></i> Unduh</a>
                        <a href="<?= url('admin/backup/hapus/' . $f['name']) ?>" class="ult-backup-card-btn danger" onclick="return confirm('Hapus backup ini?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (!$files): ?>
            <div class="ult-backup-empty" style="grid-column:1/-1;">
                <div class="ult-backup-empty-icon"><i class="bi bi-archive"></i></div>
                <h3>Belum Ada Backup</h3>
                <p>Belum ada file backup yang dibuat.</p>
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
    document.querySelectorAll('.ult-backup-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-backup-dropdown-trigger, .ult-backup-row-btn');
        const menu = dd.querySelector('.ult-backup-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-backup-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
        });
        menu.querySelectorAll('.ult-backup-dd-item[data-value]').forEach(function(item){
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
                menu.querySelectorAll('.ult-backup-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' && val !== '0' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
            });
        });
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-backup-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultBackupViewToggle');
    const tv = document.getElementById('ultBackupTableView');
    const gv = document.getElementById('ultBackupGridView');
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
                try { localStorage.setItem('ult_backup_view', v); } catch(e){}
            });
        });
    }
})();
</script>