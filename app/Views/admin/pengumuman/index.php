<?php
use App\Helpers\Auth;
$canEdit = Auth::hasRole(['super_admin', 'admin_bauk']);

// ===== PRIORITY & AUDIENCE MAP =====
$priorityMap = [
    'urgent' => ['badge-danger', 'Urgent', 'bi-exclamation-diamond-fill', '#ef4444'],
    'high'   => ['badge-warning', 'High', 'bi-exclamation-triangle-fill', '#f59e0b'],
    'medium' => ['badge-info', 'Medium', 'bi-info-circle-fill', '#3b82f6'],
    'low'    => ['badge-secondary', 'Low', 'bi-arrow-down-circle-fill', '#64748b'],
];

$audienceMap = [
    'all'      => ['Semua', 'bi-people-fill', '#3b82f6'],
    'pegawai'  => ['Pegawai', 'bi-person-badge-fill', '#10b981'],
    'mahasiswa' => ['Mahasiswa', 'bi-mortarboard-fill', '#8b5cf6'],
    'dosen'    => ['Dosen', 'bi-journal-bookmark-fill', '#f59e0b'],
];

// ===== STATS =====
try {
    $db = \App\Helpers\Database::getInstance();
    $statTotal     = count($pengumuman);
    $statActive    = count(array_filter($pengumuman, fn($p) => $p['is_active']));
    $statInactive  = count(array_filter($pengumuman, fn($p) => !$p['is_active']));
    $statUrgent    = count(array_filter($pengumuman, fn($p) => $p['priority'] === 'urgent'));
} catch (\Throwable $e) {
    $statTotal = $statActive = $statInactive = $statUrgent = 0;
}

$currentView = $_GET['view'] ?? 'table';
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-peng-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-peng-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-peng-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#3b82f6);}
.ult-peng-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-peng-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-peng-stat-info{flex:1;min-width:0;}
.ult-peng-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-peng-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-peng-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-peng-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-peng-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-peng-dropdown{position:relative;display:inline-block;}
.ult-peng-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:150px;justify-content:space-between;}
.ult-peng-dropdown-trigger:hover{border-color:var(--admin-primary);background:var(--admin-bg);}
.ult-peng-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.05);}
.ult-peng-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-peng-dropdown.open .ult-peng-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-peng-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:300px;overflow-y:auto;}
.ult-peng-dropdown.open .ult-peng-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-peng-dropdown-menu.right{left:auto;right:0;}
.ult-peng-dd-search{padding:.625rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);}
.ult-peng-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;font-size:.825rem;background:var(--admin-bg);}
.ult-peng-dd-search input:focus{outline:none;border-color:var(--admin-primary);}
.ult-peng-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-peng-dd-item:last-child{border-bottom:none;}
.ult-peng-dd-item:hover{background:var(--admin-bg);}
.ult-peng-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-peng-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-peng-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-peng-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-peng-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== FILTER CHIPS ===== */
.ult-peng-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);align-items:center;}
.ult-peng-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-peng-chip i{font-size:.7rem;}
.ult-peng-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-peng-chip-close:hover{color:var(--admin-danger);}
.ult-peng-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;}
.ult-peng-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== VIEW TOGGLE ===== */
.ult-peng-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-peng-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-peng-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-peng-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-peng-table-wrap{overflow-x:auto;}
.ult-peng-cell{display:flex;align-items:center;gap:.75rem;min-width:240px;}
.ult-peng-icon{width:44px;height:44px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff;flex-shrink:0;}
.ult-peng-info{flex:1;min-width:0;}
.ult-peng-title{font-weight:700;color:var(--admin-text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-peng-date{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;display:flex;align-items:center;gap:.375rem;}
.ult-peng-date i{font-size:.7rem;}
.ult-peng-priority{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
.ult-peng-audience{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:var(--admin-bg);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text-secondary);}
.ult-peng-audience i{font-size:.7rem;}
.ult-peng-status{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}

/* ===== ROW ACTION ===== */
.ult-peng-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-peng-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-peng-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-peng-row-btn.view:hover{color:var(--admin-primary);background:rgba(26,82,118,.08);}
.ult-peng-row-btn.edit:hover{color:#f59e0b;background:rgba(245,158,11,.08);}
.ult-peng-row-btn.toggle:hover{color:#10b981;background:rgba(16,185,129,.08);}
.ult-peng-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-peng-row-menu{min-width:220px;}
.ult-peng-row-menu .ult-peng-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-peng-row-menu .ult-peng-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-peng-row-menu .ult-peng-dd-item.danger{color:var(--admin-danger);}
.ult-peng-row-menu .ult-peng-dd-item.danger:hover{background:rgba(239,68,68,.08);}
.ult-peng-row-menu .ult-peng-dd-item.success{color:#10b981;}
.ult-peng-row-menu .ult-peng-dd-item.success:hover{background:rgba(16,185,129,.08);}

/* ===== GRID VIEW ===== */
.ult-peng-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.25rem;padding:1.5rem;}
.ult-peng-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;transition:all .3s;position:relative;}
.ult-peng-card:hover{transform:translateY(-4px);box-shadow:0 12px 35px rgba(0,0,0,.1);border-color:var(--admin-primary);}
.ult-peng-card-accent{height:4px;}
.ult-peng-card-accent.urgent{background:linear-gradient(90deg,#ef4444,#dc2626);}
.ult-peng-card-accent.high{background:linear-gradient(90deg,#f59e0b,#d97706);}
.ult-peng-card-accent.medium{background:linear-gradient(90deg,#3b82f6,#2563eb);}
.ult-peng-card-accent.low{background:linear-gradient(90deg,#64748b,#475569);}
.ult-peng-card-body{padding:1.25rem;}
.ult-peng-card-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;gap:.75rem;}
.ult-peng-card-icon{width:44px;height:44px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff;flex-shrink:0;}
.ult-peng-card-status{margin-left:auto;}
.ult-peng-card-title{font-size:1.05rem;font-weight:700;color:var(--admin-text);margin-bottom:.5rem;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.ult-peng-card-content{font-size:.825rem;color:var(--admin-text-secondary);line-height:1.6;margin-bottom:1rem;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;min-height:60px;}
.ult-peng-card-meta{display:flex;flex-direction:column;gap:.375rem;margin-bottom:1rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
.ult-peng-card-meta span{display:flex;align-items:center;gap:.375rem;font-size:.8rem;color:var(--admin-text-secondary);}
.ult-peng-card-meta i{font-size:.7rem;color:var(--admin-primary);}
.ult-peng-card-footer{display:flex;gap:.375rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
.ult-peng-card-btn{flex:1;padding:.5rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:6px;font-size:.78rem;font-weight:600;color:var(--admin-text);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.375rem;transition:all .2s;text-decoration:none!important;}
.ult-peng-card-btn:hover{background:var(--admin-primary);color:#fff!important;border-color:var(--admin-primary);}
.ult-peng-card-btn.toggle:hover{background:#10b981;border-color:#10b981;}
.ult-peng-card-btn.danger:hover{background:var(--admin-danger);border-color:var(--admin-danger);}
.ult-peng-card-actions{position:absolute;top:.75rem;right:.75rem;}

/* ===== EMPTY STATE ===== */
.ult-peng-empty{text-align:center;padding:4rem 2rem;}
.ult-peng-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#fef3c7,#fde68a);color:#f59e0b;display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-peng-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(245,158,11,.3);border-radius:50%;animation:ult-peng-spin 20s linear infinite;}
@keyframes ult-peng-spin{to{transform:rotate(360deg);}}
.ult-peng-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-peng-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-peng-stats{grid-template-columns:repeat(2,1fr);}
    .ult-peng-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-peng-stats{grid-template-columns:1fr;}
    .ult-peng-grid{grid-template-columns:1fr;padding:1rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-peng-stats">
    <div class="ult-peng-stat" style="--ult-color:#3b82f6;">
        <div class="ult-peng-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-megaphone-fill"></i></div>
        <div class="ult-peng-stat-info">
            <div class="ult-peng-stat-label">Total Pengumuman</div>
            <div class="ult-peng-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-peng-stat-sub"><i class="bi bi-database-fill"></i> Keseluruhan</div>
        </div>
    </div>
    <div class="ult-peng-stat" style="--ult-color:#10b981;">
        <div class="ult-peng-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-broadcast-pin"></i></div>
        <div class="ult-peng-stat-info">
            <div class="ult-peng-stat-label">Pengumuman Aktif</div>
            <div class="ult-peng-stat-value"><?= number_format($statActive) ?></div>
            <div class="ult-peng-stat-sub"><i class="bi bi-arrow-up-short"></i> <?= $statTotal > 0 ? round(($statActive / $statTotal) * 100) : 0 ?>% dari total</div>
        </div>
    </div>
    <div class="ult-peng-stat" style="--ult-color:#64748b;">
        <div class="ult-peng-stat-icon" style="background:linear-gradient(135deg,#64748b,#475569);"><i class="bi bi-pause-circle-fill"></i></div>
        <div class="ult-peng-stat-info">
            <div class="ult-peng-stat-label">Nonaktif</div>
            <div class="ult-peng-stat-value"><?= number_format($statInactive) ?></div>
            <div class="ult-peng-stat-sub"><i class="bi bi-clock-history"></i> Tidak ditampilkan</div>
        </div>
    </div>
    <div class="ult-peng-stat" style="--ult-color:#ef4444;">
        <div class="ult-peng-stat-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="bi bi-exclamation-diamond-fill"></i></div>
        <div class="ult-peng-stat-info">
            <div class="ult-peng-stat-label">Prioritas Urgent</div>
            <div class="ult-peng-stat-value"><?= number_format($statUrgent) ?></div>
            <div class="ult-peng-stat-sub"><i class="bi bi-exclamation-triangle-fill"></i> Perlu perhatian</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Pengumuman</h1>
        <p class="page-subtitle">Pengumuman internal kampus</p>
    </div>
    <div class="ult-peng-header-actions">
        <div class="ult-peng-view-toggle" id="ultPengViewToggle">
            <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
            </button>
            <button type="button" class="<?= $currentView === 'grid' ? 'active' : '' ?>" data-view="grid" title="Tampilan Kartu">
                <i class="bi bi-grid-3x3-gap"></i> <span class="d-none-mobile">Kartu</span>
            </button>
        </div>
        <?php if ($canEdit): ?>
        <a href="<?= url('admin/pengumuman/tambah') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Buat Pengumuman</a>
        <?php endif; ?>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-peng-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-megaphone-fill"></i> Daftar Pengumuman</h3>
                    <span class="pgn-result-info">
                        Total <strong><?= number_format($statTotal) ?></strong> pengumuman
                        <?php if ($statActive > 0): ?>
                            <span style="color:var(--admin-success);font-weight:700;">• <?= $statActive ?> aktif</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultPengTableView" class="ult-peng-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width:280px;">Judul</th>
                        <th>Prioritas</th>
                        <th>Audience</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th style="width:160px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pengumuman as $p): 
                    [$pBadge, $pLabel, $pIcon, $pColor] = $priorityMap[$p['priority']] ?? ['badge-info', $p['priority'], 'bi-info-circle-fill', '#64748b'];
                    [$aLabel, $aIcon, $aColor] = $audienceMap[$p['target_audience']] ?? ['Semua', 'bi-people-fill', '#3b82f6'];
                    $isActive = $p['is_active'];
                ?>
                    <tr>
                        <td>
                            <div class="ult-peng-cell">
                                <div class="ult-peng-icon" style="background:linear-gradient(135deg,<?= $pColor ?>,<?= $pColor ?>cc);">
                                    <i class="bi <?= $pIcon ?>"></i>
                                </div>
                                <div class="ult-peng-info">
                                    <div class="ult-peng-title"><?= htmlspecialchars($p['title']) ?></div>
                                    <div class="ult-peng-date">
                                        <i class="bi bi-calendar-event"></i>
                                        Dibuat: <?= date('d M Y', strtotime($p['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="ult-peng-priority" style="background:<?= $pColor ?>20;color:<?= $pColor ?>;">
                                <i class="bi <?= $pIcon ?>"></i> <?= $pLabel ?>
                            </span>
                        </td>
                        <td>
                            <span class="ult-peng-audience" style="color:<?= $aColor ?>;">
                                <i class="bi <?= $aIcon ?>"></i> <?= $aLabel ?>
                            </span>
                        </td>
                        <td style="font-size:.825rem;color:var(--admin-text-secondary);">
                            <?php if (!empty($p['start_date']) || !empty($p['end_date'])): ?>
                                <div><i class="bi bi-calendar-range"></i> <?= !empty($p['start_date']) ? date('d M Y', strtotime($p['start_date'])) : '-' ?></div>
                                <div style="font-size:.7rem;color:var(--admin-text-muted);">s/d <?= !empty($p['end_date']) ? date('d M Y', strtotime($p['end_date'])) : '∞' ?></div>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="ult-peng-status" style="background:<?= $isActive ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.15)' ?>;color:<?= $isActive ? '#10b981' : '#f59e0b' ?>;">
                                <i class="bi bi-<?= $isActive ? 'check-circle-fill' : 'pause-circle-fill' ?>"></i> <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td>
                            <div class="ult-peng-row-actions">
                                <a href="<?= url('admin/pengumuman/edit/' . $p['id']) ?>" class="ult-peng-row-btn edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                
                                <div class="ult-peng-dropdown" data-dropdown>
                                    <button type="button" class="ult-peng-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="ult-peng-dropdown-menu right ult-peng-row-menu">
                                        <div class="ult-peng-dd-header">Pengumuman</div>
                                        <a href="<?= url('admin/pengumuman/edit/' . $p['id']) ?>" class="ult-peng-dd-item">
                                            <i class="bi bi-pencil-square"></i><span>Edit Pengumuman</span>
                                        </a>
                                        <a href="#" class="ult-peng-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Preview pengumuman segera hadir','info');return false;">
                                            <i class="bi bi-eye-fill"></i><span>Preview Tampilan</span>
                                        </a>
                                        <div class="ult-peng-dd-divider"></div>
                                        <div class="ult-peng-dd-header">Status</div>
                                        <a href="#" class="ult-peng-dd-item <?= $isActive ? 'danger' : 'success' ?>" onclick="if(typeof showToast!=='undefined')showToast('Toggle status segera hadir','info');return false;">
                                            <i class="bi bi-<?= $isActive ? 'pause-circle-fill' : 'check-circle-fill' ?>"></i>
                                            <span><?= $isActive ? 'Nonaktifkan' : 'Aktifkan' ?></span>
                                        </a>
                                        <div class="ult-peng-dd-divider"></div>
                                        <div class="ult-peng-dd-header">Dokumen</div>
                                        <?php if (!empty($p['attachment'])): ?>
                                        <a href="<?= url('uploads/pengumuman/' . $p['attachment']) ?>" target="_blank" class="ult-peng-dd-item">
                                            <i class="bi bi-paperclip"></i><span>Lihat Lampiran</span>
                                        </a>
                                        <?php endif; ?>
                                        <a href="#" class="ult-peng-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Cetak pengumuman segera hadir','info');return false;">
                                            <i class="bi bi-printer-fill"></i><span>Cetak Pengumuman</span>
                                        </a>
                                        <?php if ($canEdit): ?>
                                        <div class="ult-peng-dd-divider"></div>
                                        <a href="<?= url('admin/pengumuman/hapus/' . $p['id']) ?>" class="ult-peng-dd-item danger" onclick="return confirm('Hapus pengumuman <?= htmlspecialchars(addslashes($p['title'])) ?>?');">
                                            <i class="bi bi-trash3-fill"></i><span>Hapus Pengumuman</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pengumuman): ?>
                    <tr><td colspan="6">
                        <div class="ult-peng-empty">
                            <div class="ult-peng-empty-icon"><i class="bi bi-megaphone"></i></div>
                            <h3>Belum Ada Pengumuman</h3>
                            <p>Belum ada pengumuman yang dibuat. Mulai buat pengumuman pertama untuk menyampaikan informasi penting kepada civitas akademika.</p>
                            <?php if ($canEdit): ?>
                            <a href="<?= url('admin/pengumuman/tambah') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Buat Pengumuman Pertama</a>
                            <?php endif; ?>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== GRID VIEW ===== -->
        <div id="ultPengGridView" class="ult-peng-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php foreach ($pengumuman as $p): 
                [$pBadge, $pLabel, $pIcon, $pColor] = $priorityMap[$p['priority']] ?? ['badge-info', $p['priority'], 'bi-info-circle-fill', '#64748b'];
                [$aLabel, $aIcon, $aColor] = $audienceMap[$p['target_audience']] ?? ['Semua', 'bi-people-fill', '#3b82f6'];
                $isActive = $p['is_active'];
                $priorityClass = $p['priority'] ?? 'low';
            ?>
            <div class="ult-peng-card">
                <div class="ult-peng-card-accent <?= $priorityClass ?>"></div>
                <div class="ult-peng-card-actions">
                    <div class="ult-peng-dropdown" data-dropdown>
                        <button type="button" class="ult-peng-row-btn" title="Aksi" style="background:var(--admin-surface);"><i class="bi bi-three-dots-vertical"></i></button>
                        <div class="ult-peng-dropdown-menu right ult-peng-row-menu">
                            <a href="<?= url('admin/pengumuman/edit/' . $p['id']) ?>" class="ult-peng-dd-item"><i class="bi bi-pencil-square"></i> Edit</a>
                            <?php if ($canEdit): ?>
                            <a href="<?= url('admin/pengumuman/hapus/' . $p['id']) ?>" class="ult-peng-dd-item danger" onclick="return confirm('Hapus?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="ult-peng-card-body">
                    <div class="ult-peng-card-header">
                        <div class="ult-peng-card-icon" style="background:linear-gradient(135deg,<?= $pColor ?>,<?= $pColor ?>cc);">
                            <i class="bi <?= $pIcon ?>"></i>
                        </div>
                        <div class="ult-peng-card-status">
                            <span class="ult-peng-status" style="background:<?= $isActive ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.15)' ?>;color:<?= $isActive ? '#10b981' : '#f59e0b' ?>;font-size:.7rem;">
                                <i class="bi bi-<?= $isActive ? 'check-circle-fill' : 'pause-circle-fill' ?>"></i> <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="ult-peng-card-title"><?= htmlspecialchars($p['title']) ?></div>
                    <div class="ult-peng-card-content"><?= htmlspecialchars(mb_substr($p['content'] ?? '', 0, 150)) ?><?= strlen($p['content'] ?? '') > 150 ? '...' : '' ?></div>
                    
                    <div class="ult-peng-card-meta">
                        <span><i class="bi bi-calendar-event-fill"></i> <?= date('d M Y', strtotime($p['created_at'])) ?></span>
                        <span><i class="bi <?= $aIcon ?>"></i> <?= $aLabel ?></span>
                        <span style="color:<?= $pColor ?>;"><i class="bi <?= $pIcon ?>"></i> <?= $pLabel ?></span>
                    </div>
                    
                    <div class="ult-peng-card-footer">
                        <a href="<?= url('admin/pengumuman/edit/' . $p['id']) ?>" class="ult-peng-card-btn"><i class="bi bi-pencil-fill"></i> Edit</a>
                        <a href="#" class="ult-peng-card-btn toggle" onclick="if(typeof showToast!=='undefined')showToast('Toggle status segera hadir','info');return false;">
                            <i class="bi bi-<?= $isActive ? 'pause-circle' : 'check-circle' ?>"></i> <?= $isActive ? 'Nonaktifkan' : 'Aktifkan' ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (!$pengumuman): ?>
            <div class="ult-peng-empty" style="grid-column:1/-1;">
                <div class="ult-peng-empty-icon"><i class="bi bi-megaphone"></i></div>
                <h3>Belum Ada Pengumuman</h3>
                <p>Belum ada pengumuman yang dibuat.</p>
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
    document.querySelectorAll('.ult-peng-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-peng-dropdown-trigger, .ult-peng-row-btn');
        const menu = dd.querySelector('.ult-peng-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-peng-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
        });
        menu.querySelectorAll('.ult-peng-dd-item[data-value]').forEach(function(item){
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
                menu.querySelectorAll('.ult-peng-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' && val !== '0' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
            });
        });
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-peng-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultPengViewToggle');
    const tv = document.getElementById('ultPengTableView');
    const gv = document.getElementById('ultPengGridView');
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
                try { localStorage.setItem('ult_peng_view', v); } catch(e){}
            });
        });
    }
})();
</script>