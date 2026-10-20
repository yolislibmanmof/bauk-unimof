<?php
use App\Helpers\Auth;
$canDelete = Auth::hasRole(['super_admin', 'admin_bauk', 'admin_kepegawaian']);

$query = function (array $override = []) use ($pagination) {
    $params = [
        'q' => $pagination['search'], 'sort' => $pagination['sort'], 'dir' => $pagination['dir'],
        'status' => $pagination['status'], 'dept' => $pagination['dept'], 'page' => $pagination['page'],
    ];
    $params = array_filter(array_merge($params, $override), static fn($v) => $v !== '' && $v !== null && $v !== 0);
    return url('admin/pegawai') . ($params ? '?' . http_build_query($params) : '');
};

$sortLink = function (string $col, string $label) use ($query, $pagination) {
    $newDir = ($pagination['sort'] === $col && $pagination['dir'] === 'asc') ? 'desc' : 'asc';
    $icon   = $pagination['sort'] === $col ? ($pagination['dir'] === 'asc' ? 'bi-sort-down' : 'bi-sort-up') : 'bi-arrow-down-up';
    return '<a class="th-sort" href="' . $query(['sort' => $col, 'dir' => $newDir, 'page' => 1]) . '">' . $label . ' <i class="bi ' . $icon . '"></i></a>';
};

$statusMap = [
    'active'   => ['badge-success', 'Aktif', 'bi-check-circle-fill', '#10b981'],
    'inactive' => ['badge-warning', 'Nonaktif', 'bi-pause-circle-fill', '#f59e0b'],
    'retired'  => ['badge-info', 'Pensiun', 'bi-hourglass-split', '#3b82f6'],
    'resigned' => ['badge-danger', 'Keluar', 'bi-x-circle-fill', '#ef4444'],
];

$startPage = max(1, $pagination['page'] - 2);
$endPage   = min($pagination['totalPages'], $pagination['page'] + 2);
$fromRow   = $pagination['total'] === 0 ? 0 : (($pagination['page'] - 1) * PER_PAGE) + 1;
$toRow     = min($pagination['total'], $pagination['page'] * PER_PAGE);

// ===== STATS DARI DB =====
$db = \App\Helpers\Database::getInstance();
try {
    $statTotal     = $db->count('pegawai', 'deleted_at IS NULL');
    $statActive    = $db->count('pegawai', "status = 'active' AND deleted_at IS NULL");
    $statNewMonth  = $db->count('pegawai', "join_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND deleted_at IS NULL");
    $statRetired   = $db->count('pegawai', "status = 'retired' AND deleted_at IS NULL");
} catch (\Throwable $e) {
    $statTotal = $statActive = $statNewMonth = $statRetired = 0;
}

// Data departemen untuk dropdown
$deptMap = [];
foreach ($departments as $d) $deptMap[$d['id']] = $d['name'];

$currentView = $_GET['view'] ?? 'table';
?>

<!-- ===== ULTIMATE STYLES (scoped, tidak ganggu existing) ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-pgn-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-pgn-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-pgn-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#3b82f6);}
.ult-pgn-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);border-color:var(--ult-color,#3b82f6);}
.ult-pgn-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-pgn-stat-info{flex:1;min-width:0;}
.ult-pgn-stat-label{font-size:.78rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-pgn-stat-value{font-size:1.75rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-pgn-stat-trend{display:inline-flex;align-items:center;gap:.25rem;font-size:.7rem;margin-top:.375rem;font-weight:600;padding:.125rem .5rem;border-radius:50px;}
.ult-pgn-stat-trend.up{background:rgba(16,185,129,.12);color:#10b981;}
.ult-pgn-stat-trend.down{background:rgba(239,68,68,.12);color:#ef4444;}
.ult-pgn-stat-trend.neutral{background:rgba(59,130,246,.12);color:#3b82f6;}

/* ===== CARD HEADER ACTIONS ===== */
.ult-pgn-card-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-pgn-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
.ult-pgn-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-pgn-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-pgn-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-pgn-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== CUSTOM DROPDOWN ===== */
.ult-pgn-dropdown{position:relative;display:inline-block;}
.ult-pgn-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:160px;justify-content:space-between;}
.ult-pgn-dropdown-trigger:hover{border-color:var(--admin-primary);background:var(--admin-bg);}
.ult-pgn-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.05);}
.ult-pgn-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-pgn-dropdown.open .ult-pgn-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-pgn-dropdown-trigger .ult-pgn-dd-icon{width:28px;height:28px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;font-size:.9rem;color:#fff;flex-shrink:0;}
.ult-pgn-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:340px;overflow-y:auto;}
.ult-pgn-dropdown.open .ult-pgn-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-pgn-dropdown-menu.right{left:auto;right:0;}
.ult-pgn-dd-search{padding:.625rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);}
.ult-pgn-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;font-size:.825rem;background:var(--admin-bg);}
.ult-pgn-dd-search input:focus{outline:none;border-color:var(--admin-primary);}
.ult-pgn-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-pgn-dd-item:last-child{border-bottom:none;}
.ult-pgn-dd-item:hover{background:var(--admin-bg);}
.ult-pgn-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-pgn-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-pgn-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-pgn-dd-item-icon{width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.75rem;color:#fff;flex-shrink:0;}
.ult-pgn-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-pgn-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== FILTER CHIPS ===== */
.ult-pgn-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);}
.ult-pgn-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-pgn-chip i{font-size:.7rem;}
.ult-pgn-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-pgn-chip-close:hover{color:var(--admin-danger);}
.ult-pgn-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;align-self:center;}
.ult-pgn-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== BULK ACTIONS BAR ===== */
.ult-pgn-bulk-bar{display:none;padding:.875rem 1rem;background:linear-gradient(90deg,rgba(26,82,118,.08),rgba(26,82,118,.04));border-bottom:1px solid var(--admin-primary-20,#dbeafe);align-items:center;gap:1rem;animation:ult-pgn-slideDown .25s ease;}
.ult-pgn-bulk-bar.active{display:flex;}
.ult-pgn-bulk-count{font-weight:700;color:var(--admin-primary);font-size:.875rem;display:flex;align-items:center;gap:.375rem;}
.ult-pgn-bulk-count .ult-pgn-bulk-badge{background:var(--admin-primary);color:#fff;padding:.125rem .5rem;border-radius:50px;font-size:.75rem;}
.ult-pgn-bulk-actions{display:flex;gap:.5rem;flex-wrap:wrap;}
.ult-pgn-bulk-btn{display:inline-flex;align-items:center;gap:.375rem;padding:.5rem .875rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:6px;font-size:.8rem;font-weight:600;color:var(--admin-text);cursor:pointer;transition:all .2s;}
.ult-pgn-bulk-btn:hover{border-color:var(--admin-primary);color:var(--admin-primary);}
.ult-pgn-bulk-btn.danger{color:var(--admin-danger);border-color:rgba(239,68,68,.3);}
.ult-pgn-bulk-btn.danger:hover{background:var(--admin-danger);color:#fff;border-color:var(--admin-danger);}
.ult-pgn-bulk-deselect{margin-left:auto;background:none;border:none;color:var(--admin-text-secondary);font-size:.8rem;cursor:pointer;display:flex;align-items:center;gap:.25rem;}
@keyframes ult-pgn-slideDown{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-pgn-table-wrap{overflow-x:auto;}
.ult-pgn-checkbox{width:18px;height:18px;cursor:pointer;accent-color:var(--admin-primary);}
.ult-pgn-cell-emp{display:flex;align-items:center;gap:.875rem;min-width:250px;}
.ult-pgn-emp-photo{width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid var(--admin-border);background:var(--admin-bg);}
.ult-pgn-emp-initial{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;flex-shrink:0;font-size:1rem;position:relative;}
.ult-pgn-emp-initial::after{content:'';position:absolute;bottom:0;right:0;width:12px;height:12px;border-radius:50%;border:2px solid var(--admin-surface);}
.ult-pgn-emp-initial.online::after{background:#10b981;}
.ult-pgn-emp-initial.offline::after{background:#94a3b8;}
.ult-pgn-emp-info{flex:1;min-width:0;}
.ult-pgn-emp-name{font-weight:700;color:var(--admin-text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-pgn-emp-name:hover{color:var(--admin-primary);}
.ult-pgn-emp-meta{display:flex;align-items:center;gap:.375rem;font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;}
.ult-pgn-emp-meta i{font-size:.65rem;}
.ult-pgn-dept-pill{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:var(--admin-bg);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text-secondary);}
.ult-pgn-dept-pill i{font-size:.7rem;color:var(--admin-primary);}
.ult-pgn-date{display:flex;flex-direction:column;gap:.125rem;}
.ult-pgn-date-main{font-weight:600;font-size:.875rem;color:var(--admin-text);}
.ult-pgn-date-sub{font-size:.7rem;color:var(--admin-text-muted);display:flex;align-items:center;gap:.25rem;}

/* ===== ROW ACTION DROPDOWN ===== */
.ult-pgn-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-pgn-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-pgn-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-pgn-row-btn.view:hover{color:var(--admin-primary);background:rgba(26,82,118,.08);border-color:rgba(26,82,118,.2);}
.ult-pgn-row-btn.edit:hover{color:#f59e0b;background:rgba(245,158,11,.08);border-color:rgba(245,158,11,.2);}
.ult-pgn-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.2);}
.ult-pgn-row-menu{min-width:220px;}
.ult-pgn-row-menu .ult-pgn-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-pgn-row-menu .ult-pgn-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-pgn-row-menu .ult-pgn-dd-item:hover i:first-child{color:var(--admin-primary);}
.ult-pgn-row-menu .ult-pgn-dd-item.danger{color:var(--admin-danger);}
.ult-pgn-row-menu .ult-pgn-dd-item.danger:hover{background:rgba(239,68,68,.08);}
.ult-pgn-row-menu .ult-pgn-dd-item.danger i:first-child{color:var(--admin-danger);}

/* ===== STATUS BADGE ENHANCED ===== */
.ult-pgn-status{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
.ult-pgn-status i{font-size:.7rem;}

/* ===== CARD/GRID VIEW ===== */
.ult-pgn-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;padding:1.5rem;}
.ult-pgn-card-emp{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);padding:1.25rem;transition:all .3s;position:relative;display:flex;flex-direction:column;}
.ult-pgn-card-emp:hover{transform:translateY(-4px);box-shadow:0 12px 35px rgba(0,0,0,.1);border-color:var(--admin-primary-20,#dbeafe);}
.ult-pgn-card-header-row{display:flex;align-items:center;gap:.875rem;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--admin-border);}
.ult-pgn-card-photo{width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--admin-border);}
.ult-pgn-card-initial{width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:1.35rem;position:relative;}
.ult-pgn-card-initial::after{content:'';position:absolute;bottom:2px;right:2px;width:14px;height:14px;border-radius:50%;border:2px solid var(--admin-surface);}
.ult-pgn-card-initial.online::after{background:#10b981;}
.ult-pgn-card-initial.offline::after{background:#94a3b8;}
.ult-pgn-card-title{flex:1;min-width:0;}
.ult-pgn-card-title h4{font-size:1rem;margin:0 0 .25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-pgn-card-title p{font-size:.8rem;color:var(--admin-text-secondary);margin:0;display:flex;align-items:center;gap:.375rem;}
.ult-pgn-card-body{flex:1;margin-bottom:1rem;}
.ult-pgn-card-row{display:flex;justify-content:space-between;align-items:center;padding:.375rem 0;font-size:.825rem;}
.ult-pgn-card-row span:first-child{color:var(--admin-text-muted);font-weight:600;}
.ult-pgn-card-row span:last-child{color:var(--admin-text);font-weight:500;}
.ult-pgn-card-footer{display:flex;gap:.375rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
.ult-pgn-card-btn{flex:1;padding:.5rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:6px;font-size:.78rem;font-weight:600;color:var(--admin-text);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.375rem;transition:all .2s;text-decoration:none!important;}
.ult-pgn-card-btn:hover{background:var(--admin-primary);color:#fff!important;border-color:var(--admin-primary);}
.ult-pgn-card-btn.danger:hover{background:var(--admin-danger);border-color:var(--admin-danger);}
.ult-pgn-card-actions{position:absolute;top:1rem;right:1rem;}

/* ===== EMPTY STATE ===== */
.ult-pgn-empty{text-align:center;padding:4rem 2rem;}
.ult-pgn-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#eff6ff);color:var(--admin-primary);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-pgn-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed var(--admin-primary-30,#93c5fd);border-radius:50%;animation:ult-pgn-spin 20s linear infinite;}
@keyframes ult-pgn-spin{to{transform:rotate(360deg);}}
.ult-pgn-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-pgn-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;line-height:1.6;}
.ult-pgn-empty-actions{display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;}

/* ===== ENHANCED PAGINATION ===== */
.ult-pgn-pagination{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;border-top:1px solid var(--admin-border);flex-wrap:wrap;gap:1rem;}
.ult-pgn-page-size{display:flex;align-items:center;gap:.5rem;font-size:.85rem;color:var(--admin-text-secondary);}
.ult-pgn-page-size select{padding:.375rem .625rem;border:1px solid var(--admin-border);border-radius:6px;background:var(--admin-surface);font-size:.825rem;cursor:pointer;}
.ult-pgn-page-info{font-size:.85rem;color:var(--admin-text-secondary);}
.ult-pgn-page-info strong{color:var(--admin-text);}

/* ===== ADVANCED FILTER TOGGLE ===== */
.ult-pgn-adv-toggle{background:none;border:none;color:var(--admin-primary);font-size:.825rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;border-radius:6px;transition:background .2s;}
.ult-pgn-adv-toggle:hover{background:rgba(26,82,118,.08);}
.ult-pgn-adv-toggle i{transition:transform .2s;}
.ult-pgn-adv-toggle.open i{transform:rotate(180deg);}
.ult-pgn-adv-panel{display:none;padding:1rem 1.5rem;background:var(--admin-bg);border-top:1px solid var(--admin-border);}
.ult-pgn-adv-panel.active{display:block;animation:ult-pgn-slideDown .25s ease;}
.ult-pgn-adv-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;}
.ult-pgn-adv-field label{display:block;font-size:.75rem;font-weight:600;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.375rem;}
.ult-pgn-adv-field input,.ult-pgn-adv-field select{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;background:var(--admin-surface);font-size:.825rem;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-pgn-stats{grid-template-columns:repeat(2,1fr);}
    .ult-pgn-card-header{flex-direction:column;align-items:stretch;}
    .ult-pgn-pagination{flex-direction:column;align-items:stretch;}
    .ult-pgn-page-size,.ult-pgn-page-info{justify-content:center;}
}
@media (max-width:640px){
    .ult-pgn-stats{grid-template-columns:1fr;}
    .ult-pgn-grid{grid-template-columns:1fr;padding:1rem;}
    .ult-pgn-bulk-bar{flex-direction:column;align-items:stretch;}
    .ult-pgn-bulk-deselect{margin-left:0;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-pgn-stats">
    <div class="ult-pgn-stat" style="--ult-color:#3b82f6;">
        <div class="ult-pgn-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="ult-pgn-stat-info">
            <div class="ult-pgn-stat-label">Total Pegawai</div>
            <div class="ult-pgn-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-pgn-stat-trend neutral"><i class="bi bi-database-fill"></i> Keseluruhan</div>
        </div>
    </div>
    <div class="ult-pgn-stat" style="--ult-color:#10b981;">
        <div class="ult-pgn-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);">
            <i class="bi bi-person-check-fill"></i>
        </div>
        <div class="ult-pgn-stat-info">
            <div class="ult-pgn-stat-label">Pegawai Aktif</div>
            <div class="ult-pgn-stat-value"><?= number_format($statActive) ?></div>
            <div class="ult-pgn-stat-trend up"><i class="bi bi-arrow-up-short"></i> <?= $statTotal > 0 ? round(($statActive / $statTotal) * 100) : 0 ?>% dari total</div>
        </div>
    </div>
    <div class="ult-pgn-stat" style="--ult-color:#f59e0b;">
        <div class="ult-pgn-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
            <i class="bi bi-person-plus-fill"></i>
        </div>
        <div class="ult-pgn-stat-info">
            <div class="ult-pgn-stat-label">Pegawai Baru (30 Hari)</div>
            <div class="ult-pgn-stat-value"><?= number_format($statNewMonth) ?></div>
            <div class="ult-pgn-stat-trend up"><i class="bi bi-graph-up-arrow"></i> Bulan ini</div>
        </div>
    </div>
    <div class="ult-pgn-stat" style="--ult-color:#8b5cf6;">
        <div class="ult-pgn-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="ult-pgn-stat-info">
            <div class="ult-pgn-stat-label">Pegawai Pensiun</div>
            <div class="ult-pgn-stat-value"><?= number_format($statRetired) ?></div>
            <div class="ult-pgn-stat-trend neutral"><i class="bi bi-clock-history"></i> Historis</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Data Pegawai</h1>
        <p class="page-subtitle">Manajemen data pegawai Universitas Muhammadiyah Maumere</p>
    </div>
    <div class="page-actions" style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <!-- Export Dropdown -->
        <div class="ult-pgn-dropdown" data-dropdown>
            <button type="button" class="ult-pgn-dropdown-trigger">
                <span><i class="bi bi-download"></i> Export</span>
                <i class="bi bi-chevron-down chevron"></i>
            </button>
            <div class="ult-pgn-dropdown-menu right">
                <div class="ult-pgn-dd-header">Format Export</div>
                <a href="<?= url('admin/pegawai/export') ?>" class="ult-pgn-dd-item">
                    <i class="bi bi-filetype-csv" style="color:#16a34a;font-size:1.1rem;"></i>
                    <div><strong>Export CSV</strong><br><small style="font-weight:400;color:var(--admin-text-muted);">Kompatibel Excel</small></div>
                </a>
                <a href="<?= url('admin/pegawai/export') ?>?format=xlsx" class="ult-pgn-dd-item" onclick="if(typeof showToast!=='undefined'){showToast('Export Excel segera hadir','info');return false;}">
                    <i class="bi bi-file-earmark-excel-fill" style="color:#217346;font-size:1.1rem;"></i>
                    <div><strong>Export Excel</strong><br><small style="font-weight:400;color:var(--admin-text-muted);">.xlsx format</small></div>
                </a>
                <a href="#" class="ult-pgn-dd-item" onclick="window.print();return false;">
                    <i class="bi bi-printer-fill" style="color:#64748b;font-size:1.1rem;"></i>
                    <div><strong>Cetak Daftar</strong><br><small style="font-weight:400;color:var(--admin-text-muted);">Print-friendly</small></div>
                </a>
                <div class="ult-pgn-dd-divider"></div>
                <div class="ult-pgn-dd-header">Template</div>
                <a href="#" class="ult-pgn-dd-item" onclick="if(typeof showToast!=='undefined'){showToast('Download template','success');}">
                    <i class="bi bi-file-earmark-arrow-down" style="color:#3b82f6;font-size:1.1rem;"></i>
                    <div><strong>Download Template</strong><br><small style="font-weight:400;color:var(--admin-text-muted);">Untuk import massal</small></div>
                </a>
            </div>
        </div>
        
        <a href="<?= url('admin/pegawai/trash') ?>" class="btn btn-secondary">
            <i class="bi bi-trash3"></i> Sampah 
            <?= $trashCount > 0 ? '<span class="badge badge-danger">' . $trashCount . '</span>' : '' ?>
        </a>
        
        <a href="<?= url('admin/pegawai/tambah') ?>" class="btn btn-primary">
            <i class="bi bi-person-plus-fill"></i> Tambah Pegawai
        </a>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header dengan Toolbar + View Toggle -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-pgn-card-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-table"></i> Daftar Pegawai</h3>
                    <span class="pgn-result-info">
                        Menampilkan <strong><?= $fromRow ?>-<?= $toRow ?></strong> dari <strong><?= number_format($pagination['total']) ?></strong> pegawai
                    </span>
                </div>
                <div class="ult-pgn-header-actions">
                    <!-- Advanced Filter Toggle -->
                    <button type="button" class="ult-pgn-adv-toggle" id="ultAdvToggle">
                        <i class="bi bi-sliders"></i> Filter Lanjutan
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    
                    <!-- View Toggle -->
                    <div class="ult-pgn-view-toggle" id="ultViewToggle">
                        <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                            <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
                        </button>
                        <button type="button" class="<?= $currentView === 'grid' ? 'active' : '' ?>" data-view="grid" title="Tampilan Kartu">
                            <i class="bi bi-grid-3x3-gap"></i> <span class="d-none-mobile">Kartu</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Toolbar Search + Custom Dropdowns -->
        <form method="GET" action="<?= url('admin/pegawai') ?>" class="pgn-toolbar" style="padding:1rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="toolbar-search" style="flex:2;min-width:250px;">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($pagination['search']) ?>" placeholder="Cari nama, NIP, jabatan, email...">
            </div>
            
            <!-- Custom Department Dropdown -->
            <div class="ult-pgn-dropdown" data-dropdown>
                <input type="hidden" name="dept" id="ultDeptValue" value="<?= (int)$pagination['dept'] ?>">
                <button type="button" class="ult-pgn-dropdown-trigger <?= (int)$pagination['dept'] > 0 ? 'has-value' : '' ?>" id="ultDeptTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                        <i class="bi bi-building" style="color:var(--admin-primary);"></i>
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= (int)$pagination['dept'] > 0 ? htmlspecialchars($deptMap[$pagination['dept']] ?? 'Pilih Departemen') : 'Semua Departemen' ?>
                        </span>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-pgn-dropdown-menu" style="min-width:280px;">
                    <div class="ult-pgn-dd-search">
                        <input type="text" placeholder="Cari departemen..." data-search>
                    </div>
                    <div class="ult-pgn-dd-item <?= (int)$pagination['dept'] === 0 ? 'selected' : '' ?>" data-value="0">
                        <i class="bi bi-grid-3x3-gap" style="color:var(--admin-text-muted);"></i>
                        <span>Semua Departemen</span>
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-pgn-dd-divider"></div>
                    <?php foreach ($departments as $d): ?>
                    <div class="ult-pgn-dd-item <?= (int)$pagination['dept'] === (int)$d['id'] ? 'selected' : '' ?>" data-value="<?= $d['id'] ?>" data-label="<?= htmlspecialchars($d['name']) ?>">
                        <i class="bi bi-building-fill" style="color:var(--admin-primary);"></i>
                        <span><?= htmlspecialchars($d['name']) ?></span>
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Custom Status Dropdown -->
            <div class="ult-pgn-dropdown" data-dropdown>
                <input type="hidden" name="status" id="ultStatusValue" value="<?= htmlspecialchars($pagination['status']) ?>">
                <button type="button" class="ult-pgn-dropdown-trigger <?= $pagination['status'] !== '' ? 'has-value' : '' ?>" id="ultStatusTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;">
                        <?php if ($pagination['status'] !== '' && isset($statusMap[$pagination['status']])): 
                            [$cls, $lbl, $icn, $clr] = $statusMap[$pagination['status']]; ?>
                            <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i>
                            <span><?= $lbl ?></span>
                        <?php else: ?>
                            <i class="bi bi-funnel" style="color:var(--admin-primary);"></i>
                            <span>Semua Status</span>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-pgn-dropdown-menu" style="min-width:200px;">
                    <div class="ult-pgn-dd-item <?= $pagination['status'] === '' ? 'selected' : '' ?>" data-value="">
                        <i class="bi bi-funnel" style="color:var(--admin-text-muted);"></i>
                        <span>Semua Status</span>
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-pgn-dd-divider"></div>
                    <?php foreach ($statusMap as $key => [$cls, $lbl, $icn, $clr]): ?>
                    <div class="ult-pgn-dd-item <?= $pagination['status'] === $key ? 'selected' : '' ?>" data-value="<?= $key ?>">
                        <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i>
                        <span><?= $lbl ?></span>
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Terapkan</button>
            <?php if ($pagination['search'] !== '' || $pagination['status'] !== '' || $pagination['dept'] != 0): ?>
                <a href="<?= url('admin/pegawai') ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
            <?php endif; ?>
        </form>
        
        <!-- Advanced Filter Panel -->
        <div class="ult-pgn-adv-panel" id="ultAdvPanel">
            <div class="ult-pgn-adv-grid">
                <div class="ult-pgn-adv-field">
                    <label>Jenis Kelamin</label>
                    <select><option value="">Semua</option><option>Laki-laki</option><option>Perempuan</option></select>
                </div>
                <div class="ult-pgn-adv-field">
                    <label>Status Kepegawaian</label>
                    <select><option value="">Semua</option><option>Tetap</option><option>Kontrak</option><option>Honorer</option><option>Magang</option></select>
                </div>
                <div class="ult-pgn-adv-field">
                    <label>Pendidikan</label>
                    <select><option value="">Semua</option><option>SD</option><option>SMP</option><option>SMA/SMK</option><option>D3</option><option>S1</option><option>S2</option><option>S3</option></select>
                </div>
                <div class="ult-pgn-adv-field">
                    <label>Tahun Masuk</label>
                    <select><option value="">Semua Tahun</option><option>2026</option><option>2025</option><option>2024</option><option>2023</option><option>Sebelum 2023</option></select>
                </div>
                <div class="ult-pgn-adv-field">
                    <label>Rentang Usia</label>
                    <select><option value="">Semua Usia</option><option>&lt; 25 tahun</option><option>25-35 tahun</option><option>36-45 tahun</option><option>46-55 tahun</option><option>&gt; 55 tahun</option></select>
                </div>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:1rem;justify-content:flex-end;">
                <button type="button" class="btn btn-secondary btn-sm">Reset</button>
                <button type="button" class="btn btn-primary btn-sm"><i class="bi bi-funnel-fill"></i> Terapkan Filter</button>
            </div>
        </div>
        
        <!-- Active Filter Chips -->
        <?php if ($pagination['search'] !== '' || $pagination['status'] !== '' || (int)$pagination['dept'] > 0): ?>
        <div class="ult-pgn-chips">
            <span class="ult-pgn-chips-label"><i class="bi bi-funnel-fill"></i> Filter Aktif:</span>
            <?php if ($pagination['search'] !== ''): ?>
                <span class="ult-pgn-chip">
                    <i class="bi bi-search"></i> Pencarian: "<strong><?= htmlspecialchars($pagination['search']) ?></strong>"
                    <a href="<?= $query(['q' => '', 'page' => 1]) ?>" class="ult-pgn-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ((int)$pagination['dept'] > 0): ?>
                <span class="ult-pgn-chip">
                    <i class="bi bi-building-fill"></i> Departemen: <strong><?= htmlspecialchars($deptMap[$pagination['dept']] ?? '-') ?></strong>
                    <a href="<?= $query(['dept' => 0, 'page' => 1]) ?>" class="ult-pgn-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($pagination['status'] !== '' && isset($statusMap[$pagination['status']])): ?>
                <span class="ult-pgn-chip">
                    <i class="bi <?= $statusMap[$pagination['status']][2] ?>"></i> Status: <strong><?= $statusMap[$pagination['status']][1] ?></strong>
                    <a href="<?= $query(['status' => '', 'page' => 1]) ?>" class="ult-pgn-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <a href="<?= url('admin/pegawai') ?>" class="ult-pgn-chips-clear"><i class="bi bi-x-circle"></i> Hapus Semua</a>
        </div>
        <?php endif; ?>
        
        <!-- Bulk Actions Bar (muncul saat ada yang ter-check) -->
        <div class="ult-pgn-bulk-bar" id="ultBulkBar">
            <span class="ult-pgn-bulk-count">
                <span class="ult-pgn-bulk-badge" id="ultBulkCount">0</span>
                pegawai terpilih
            </span>
            <div class="ult-pgn-bulk-actions">
                <button type="button" class="ult-pgn-bulk-btn"><i class="bi bi-file-earmark-spreadsheet"></i> Export Pilihan</button>
                <button type="button" class="ult-pgn-bulk-btn"><i class="bi bi-envelope"></i> Kirim Email</button>
                <button type="button" class="ult-pgn-bulk-btn"><i class="bi bi-printer"></i> Cetak</button>
                <?php if ($canDelete): ?>
                <button type="button" class="ult-pgn-bulk-btn danger" onclick="if(confirm('Pindahkan '+document.getElementById('ultBulkCount').textContent+' pegawai ke sampah?')){if(typeof showToast!=='undefined')showToast('Fitur bulk delete segera hadir','info');}">
                    <i class="bi bi-trash3"></i> Hapus Pilihan
                </button>
                <?php endif; ?>
            </div>
            <button type="button" class="ult-pgn-bulk-deselect" id="ultBulkDeselect">
                <i class="bi bi-x-lg"></i> Batal Pilih
            </button>
        </div>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultTableView" class="ult-pgn-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <?php if ($canDelete): ?><th style="width:40px;"><input type="checkbox" class="ult-pgn-checkbox" id="ultSelectAll" title="Pilih Semua"></th><?php endif; ?>
                        <th><?= $sortLink('full_name', 'Pegawai') ?></th>
                        <th><?= $sortLink('nip', 'NIP') ?></th>
                        <th>Departemen</th>
                        <th><?= $sortLink('position', 'Jabatan') ?></th>
                        <th><?= $sortLink('join_date', 'Bergabung') ?></th>
                        <th><?= $sortLink('status', 'Status') ?></th>
                        <th style="width:160px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pegawai as $p):
                    [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $statusMap[$p['status']] ?? ['badge-info', $p['status'], 'bi-circle-fill', '#64748b'];
                    $photoUrl = !empty($p['photo']) ? url('uploads/pegawai/' . $p['photo']) : null;
                    $isOnline = ($p['status'] === 'active');
                    $masaKerja = $p['join_date'] ? floor((time() - strtotime($p['join_date'])) / (365 * 86400)) : null;
                ?>
                    <tr>
                        <?php if ($canDelete): ?><td><input type="checkbox" class="ult-pgn-checkbox ult-row-check" data-id="<?= $p['id'] ?>"></td><?php endif; ?>
                        <td>
                            <div class="ult-pgn-cell-emp">
                                <?php if ($photoUrl): ?>
                                    <img src="<?= $photoUrl ?>" alt="" class="ult-pgn-emp-photo">
                                <?php else: ?>
                                    <div class="ult-pgn-emp-initial <?= $isOnline ? 'online' : 'offline' ?>" style="background:linear-gradient(135deg,<?= $badgeColor ?>,<?= $badgeColor ?>cc);">
                                        <?= strtoupper(substr($p['full_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="ult-pgn-emp-info">
                                    <a href="<?= url('admin/pegawai/detail/' . $p['id']) ?>" class="ult-pgn-emp-name"><?= htmlspecialchars($p['full_name']) ?></a>
                                    <div class="ult-pgn-emp-meta">
                                        <i class="bi bi-<?= $p['gender'] === 'L' ? 'gender-male' : 'gender-female' ?>"></i>
                                        <span><?= $p['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                                        <span>•</span>
                                        <span><?= htmlspecialchars($p['employment_status']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><code style="background:var(--admin-bg);padding:.125rem .5rem;border-radius:4px;font-size:.8rem;"><?= htmlspecialchars($p['nip']) ?></code></td>
                        <td>
                            <?php if (!empty($p['department_name'])): ?>
                            <span class="ult-pgn-dept-pill">
                                <i class="bi bi-building-fill"></i>
                                <?= htmlspecialchars($p['department_name']) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['position'] ?? '-') ?></td>
                        <td>
                            <div class="ult-pgn-date">
                                <span class="ult-pgn-date-main"><?= $p['join_date'] ? date('d M Y', strtotime($p['join_date'])) : '-' ?></span>
                                <?php if ($masaKerja !== null): ?>
                                <span class="ult-pgn-date-sub">
                                    <i class="bi bi-clock-history"></i>
                                    <?= $masaKerja ?> tahun <?= $masaKerja < 1 ? '(Baru)' : '' ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="ult-pgn-status" style="background:<?= $badgeColor ?>20;color:<?= $badgeColor ?>;">
                                <i class="bi <?= $badgeIcon ?>"></i>
                                <?= $badgeLabel ?>
                            </span>
                        </td>
                        <td>
                            <div class="ult-pgn-row-actions">
                                <a href="<?= url('admin/pegawai/detail/' . $p['id']) ?>" class="ult-pgn-row-btn view" title="Detail">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="<?= url('admin/pegawai/edit/' . $p['id']) ?>" class="ult-pgn-row-btn edit" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                
                                <!-- Row Action Dropdown -->
                                <div class="ult-pgn-dropdown" data-dropdown>
                                    <button type="button" class="ult-pgn-row-btn" title="Aksi Lainnya">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="ult-pgn-dropdown-menu right ult-pgn-row-menu">
                                        <div class="ult-pgn-dd-header">Profil</div>
                                        <a href="<?= url('admin/pegawai/detail/' . $p['id']) ?>" class="ult-pgn-dd-item">
                                            <i class="bi bi-person-vcard"></i>
                                            <span>Lihat Detail Lengkap</span>
                                        </a>
                                        <a href="<?= url('admin/pegawai/edit/' . $p['id']) ?>" class="ult-pgn-dd-item">
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Edit Data</span>
                                        </a>
                                        <a href="#" class="ult-pgn-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Mencetak kartu pegawai...','info');return false;">
                                            <i class="bi bi-card-heading"></i>
                                            <span>Cetak Kartu Pegawai</span>
                                        </a>
                                        <div class="ult-pgn-dd-divider"></div>
                                        <div class="ult-pgn-dd-header">Komunikasi</div>
                                        <?php if (!empty($p['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($p['email']) ?>" class="ult-pgn-dd-item">
                                            <i class="bi bi-envelope"></i>
                                            <span>Kirim Email</span>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($p['phone'])): ?>
                                        <a href="tel:<?= htmlspecialchars($p['phone']) ?>" class="ult-pgn-dd-item">
                                            <i class="bi bi-telephone"></i>
                                            <span>Hubungi (<?= htmlspecialchars($p['phone']) ?>)</span>
                                        </a>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $p['phone']) ?>" target="_blank" class="ult-pgn-dd-item">
                                            <i class="bi bi-whatsapp"></i>
                                            <span>Chat WhatsApp</span>
                                        </a>
                                        <?php endif; ?>
                                        <div class="ult-pgn-dd-divider"></div>
                                        <div class="ult-pgn-dd-header">Administrasi</div>
                                        <a href="#" class="ult-pgn-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Form mutasi segera hadir','info');return false;">
                                            <i class="bi bi-arrow-left-right"></i>
                                            <span>Mutasi / Pindah Unit</span>
                                        </a>
                                        <a href="#" class="ult-pgn-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Riwayat aktivitas segera hadir','info');return false;">
                                            <i class="bi bi-clock-history"></i>
                                            <span>Riwayat Aktivitas</span>
                                        </a>
                                        <a href="#" class="ult-pgn-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Dokumen pegawai segera hadir','info');return false;">
                                            <i class="bi bi-folder-fill"></i>
                                            <span>Dokumen Digital</span>
                                        </a>
                                        <?php if ($canDelete): ?>
                                        <div class="ult-pgn-dd-divider"></div>
                                        <a href="<?= url('admin/pegawai/hapus/' . $p['id']) ?>" class="ult-pgn-dd-item danger" onclick="return confirm('Pindahkan pegawai <?= htmlspecialchars(addslashes($p['full_name'])) ?> ke tempat sampah?');">
                                            <i class="bi bi-trash3-fill"></i>
                                            <span>Pindahkan ke Sampah</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pegawai): ?>
                    <tr><td colspan="<?= $canDelete ? 8 : 7 ?>">
                        <div class="ult-pgn-empty">
                            <div class="ult-pgn-empty-icon"><i class="bi bi-person-x-fill"></i></div>
                            <h3>Tidak Ada Data Pegawai</h3>
                            <p>
                                <?php if ($pagination['search'] !== '' || $pagination['status'] !== '' || $pagination['dept'] != 0): ?>
                                    Tidak ada pegawai yang cocok dengan filter Anda. Coba ubah kriteria pencarian atau reset filter.
                                <?php else: ?>
                                    Belum ada data pegawai dalam sistem. Mulai tambahkan data pegawai untuk memulai.
                                <?php endif; ?>
                            </p>
                            <div class="ult-pgn-empty-actions">
                                <?php if ($pagination['search'] !== '' || $pagination['status'] !== '' || $pagination['dept'] != 0): ?>
                                    <a href="<?= url('admin/pegawai') ?>" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset Filter</a>
                                <?php endif; ?>
                                <a href="<?= url('admin/pegawai/tambah') ?>" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Tambah Pegawai Pertama</a>
                            </div>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== CARD/GRID VIEW ===== -->
        <div id="ultGridView" class="ult-pgn-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php foreach ($pegawai as $p):
                [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $statusMap[$p['status']] ?? ['badge-info', $p['status'], 'bi-circle-fill', '#64748b'];
                $photoUrl = !empty($p['photo']) ? url('uploads/pegawai/' . $p['photo']) : null;
                $isOnline = ($p['status'] === 'active');
                $masaKerja = $p['join_date'] ? floor((time() - strtotime($p['join_date'])) / (365 * 86400)) : null;
            ?>
            <div class="ult-pgn-card-emp">
                <div class="ult-pgn-card-actions">
                    <div class="ult-pgn-dropdown" data-dropdown>
                        <button type="button" class="ult-pgn-row-btn" title="Aksi Lainnya">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <div class="ult-pgn-dropdown-menu right ult-pgn-row-menu">
                            <a href="<?= url('admin/pegawai/detail/' . $p['id']) ?>" class="ult-pgn-dd-item"><i class="bi bi-person-vcard"></i> Detail</a>
                            <a href="<?= url('admin/pegawai/edit/' . $p['id']) ?>" class="ult-pgn-dd-item"><i class="bi bi-pencil-square"></i> Edit</a>
                            <?php if ($canDelete): ?>
                            <a href="<?= url('admin/pegawai/hapus/' . $p['id']) ?>" class="ult-pgn-dd-item danger" onclick="return confirm('Hapus?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="ult-pgn-card-header-row">
                    <?php if ($photoUrl): ?>
                        <img src="<?= $photoUrl ?>" alt="" class="ult-pgn-card-photo">
                    <?php else: ?>
                        <div class="ult-pgn-card-initial <?= $isOnline ? 'online' : 'offline' ?>" style="background:linear-gradient(135deg,<?= $badgeColor ?>,<?= $badgeColor ?>cc);">
                            <?= strtoupper(substr($p['full_name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="ult-pgn-card-title">
                        <h4><?= htmlspecialchars($p['full_name']) ?></h4>
                        <p><i class="bi bi-hash"></i> <?= htmlspecialchars($p['nip']) ?></p>
                    </div>
                </div>
                
                <div class="ult-pgn-card-body">
                    <div class="ult-pgn-card-row">
                        <span>Jabatan</span>
                        <span><?= htmlspecialchars($p['position'] ?? '-') ?></span>
                    </div>
                    <div class="ult-pgn-card-row">
                        <span>Departemen</span>
                        <span><?= htmlspecialchars($p['department_name'] ?? '-') ?></span>
                    </div>
                    <div class="ult-pgn-card-row">
                        <span>Bergabung</span>
                        <span><?= $p['join_date'] ? date('d M Y', strtotime($p['join_date'])) : '-' ?></span>
                    </div>
                    <div class="ult-pgn-card-row">
                        <span>Masa Kerja</span>
                        <span><?= $masaKerja !== null ? $masaKerja . ' tahun' : '-' ?></span>
                    </div>
                    <div class="ult-pgn-card-row">
                        <span>Status</span>
                        <span class="ult-pgn-status" style="background:<?= $badgeColor ?>20;color:<?= $badgeColor ?>;">
                            <i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                        </span>
                    </div>
                </div>
                
                <div class="ult-pgn-card-footer">
                    <a href="<?= url('admin/pegawai/detail/' . $p['id']) ?>" class="ult-pgn-card-btn">
                        <i class="bi bi-eye-fill"></i> Detail
                    </a>
                    <a href="<?= url('admin/pegawai/edit/' . $p['id']) ?>" class="ult-pgn-card-btn">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </a>
                    <?php if (!empty($p['email'])): ?>
                    <a href="mailto:<?= htmlspecialchars($p['email']) ?>" class="ult-pgn-card-btn" title="Email">
                        <i class="bi bi-envelope-fill"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- ===== ENHANCED PAGINATION ===== -->
        <?php if ($pagination['totalPages'] > 1): ?>
        <div class="ult-pgn-pagination">
            <div class="ult-pgn-page-size">
                <i class="bi bi-list-ol"></i>
                <span>Tampilkan</span>
                <select onchange="if(typeof showToast!=='undefined')showToast('Page size akan segera aktif','info');">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
                <span>per halaman</span>
            </div>
            
            <div style="display:flex;gap:.375rem;">
                <a href="<?= $query(['page' => max(1, $pagination['page'] - 1)]) ?>" class="pgn-pagination a <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>"><i class="bi bi-chevron-left"></i></a>
                <?php if ($startPage > 1): ?><a href="<?= $query(['page' => 1]) ?>" class="pgn-pagination a">1</a><span style="align-self:center;padding:0 .25rem;">...</span><?php endif; ?>
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="<?= $query(['page' => $i]) ?>" class="pgn-pagination a <?= $i === $pagination['page'] ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($endPage < $pagination['totalPages']): ?><span style="align-self:center;padding:0 .25rem;">...</span><a href="<?= $query(['page' => $pagination['totalPages']]) ?>" class="pgn-pagination a"><?= $pagination['totalPages'] ?></a><?php endif; ?>
                <a href="<?= $query(['page' => min($pagination['totalPages'], $pagination['page'] + 1)]) ?>" class="pgn-pagination a <?= $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : '' ?>"><i class="bi bi-chevron-right"></i></a>
            </div>
            
            <div class="ult-pgn-page-info">
                Halaman <strong><?= $pagination['page'] ?></strong> dari <strong><?= $pagination['totalPages'] ?></strong>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- ===== INLINE SCRIPT (IIFE, tidak ganggu admin-script.js) ===== -->
<script>
(function() {
    'use strict';
    
    // ===== CUSTOM DROPDOWN LOGIC =====
    document.querySelectorAll('[data-dropdown]').forEach(function(dd) {
        const trigger = dd.querySelector('.ult-pgn-dropdown-trigger, .ult-pgn-row-btn');
        const menu = dd.querySelector('.ult-pgn-dropdown-menu');
        
        if (!trigger || !menu) return;
        
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            // Close other dropdowns
            document.querySelectorAll('[data-dropdown].open').forEach(function(other) {
                if (other !== dd) other.classList.remove('open');
            });
            dd.classList.toggle('open');
            
            // Auto-focus search if exists
            if (dd.classList.contains('open')) {
                const searchInput = menu.querySelector('[data-search]');
                if (searchInput) setTimeout(function() { searchInput.focus(); }, 50);
            }
        });
        
        // Item selection for form dropdowns
        menu.querySelectorAll('.ult-pgn-dd-item[data-value]').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const value = this.getAttribute('data-value');
                const label = this.getAttribute('data-label') || this.querySelector('span').textContent.trim();
                
                // Update hidden input
                const hiddenInput = dd.querySelector('input[type="hidden"]');
                if (hiddenInput) hiddenInput.value = value;
                
                // Update trigger display
                const triggerSpan = trigger.querySelector('span');
                if (triggerSpan) {
                    const icon = trigger.querySelector('i:not(.chevron)');
                    if (icon) {
                        triggerSpan.innerHTML = '';
                        triggerSpan.appendChild(icon);
                        const textSpan = document.createElement('span');
                        textSpan.style.cssText = 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
                        textSpan.textContent = label;
                        triggerSpan.appendChild(textSpan);
                    } else {
                        triggerSpan.textContent = label;
                    }
                }
                
                // Update selected state
                menu.querySelectorAll('.ult-pgn-dd-item').forEach(function(i) { i.classList.remove('selected'); });
                this.classList.add('selected');
                
                // Mark as has value
                trigger.classList.add('has-value');
                
                // Close
                dd.classList.remove('open');
                
                // Auto-submit if it's filter dropdown
                if (hiddenInput && (hiddenInput.name === 'dept' || hiddenInput.name === 'status')) {
                    const form = dd.closest('form');
                    if (form) form.submit();
                }
            });
        });
        
        // Search filter
        const searchInput = menu.querySelector('[data-search]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                menu.querySelectorAll('.ult-pgn-dd-item').forEach(function(item) {
                    if (item.hasAttribute('data-value')) {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(q) ? '' : 'none';
                    }
                });
            });
        }
    });
    
    // Close dropdowns on outside click
    document.addEventListener('click', function() {
        document.querySelectorAll('[data-dropdown].open').forEach(function(dd) {
            dd.classList.remove('open');
        });
    });
    
    // ===== BULK SELECTION =====
    const selectAll = document.getElementById('ultSelectAll');
    const rowChecks = document.querySelectorAll('.ult-row-check');
    const bulkBar = document.getElementById('ultBulkBar');
    const bulkCount = document.getElementById('ultBulkCount');
    const bulkDeselect = document.getElementById('ultBulkDeselect');
    
    function updateBulkBar() {
        const checked = document.querySelectorAll('.ult-row-check:checked');
        if (bulkBar && bulkCount) {
            bulkCount.textContent = checked.length;
            bulkBar.classList.toggle('active', checked.length > 0);
        }
        if (selectAll) {
            selectAll.checked = checked.length === rowChecks.length && rowChecks.length > 0;
            selectAll.indeterminate = checked.length > 0 && checked.length < rowChecks.length;
        }
    }
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowChecks.forEach(function(cb) { cb.checked = selectAll.checked; });
            updateBulkBar();
        });
    }
    
    rowChecks.forEach(function(cb) {
        cb.addEventListener('change', updateBulkBar);
    });
    
    if (bulkDeselect) {
        bulkDeselect.addEventListener('click', function() {
            rowChecks.forEach(function(cb) { cb.checked = false; });
            if (selectAll) selectAll.checked = false;
            updateBulkBar();
        });
    }
    
    // ===== VIEW TOGGLE =====
    const viewToggle = document.getElementById('ultViewToggle');
    const tableView = document.getElementById('ultTableView');
    const gridView = document.getElementById('ultGridView');
    
    if (viewToggle) {
        viewToggle.querySelectorAll('button[data-view]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const view = this.getAttribute('data-view');
                viewToggle.querySelectorAll('button').forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                
                if (tableView && gridView) {
                    if (view === 'grid') {
                        tableView.style.display = 'none';
                        gridView.style.display = 'grid';
                    } else {
                        tableView.style.display = '';
                        gridView.style.display = 'none';
                    }
                }
                
                // Save preference (optional: localStorage)
                try { localStorage.setItem('ult_pegawai_view', view); } catch(e){}
            });
        });
    }
    
    // ===== ADVANCED FILTER TOGGLE =====
    const advToggle = document.getElementById('ultAdvToggle');
    const advPanel = document.getElementById('ultAdvPanel');
    if (advToggle && advPanel) {
        advToggle.addEventListener('click', function() {
            this.classList.toggle('open');
            advPanel.classList.toggle('active');
        });
    }
    
    // ===== KEYBOARD SHORTCUTS =====
    document.addEventListener('keydown', function(e) {
        // Ctrl+A to select all (when in table)
        if ((e.ctrlKey || e.metaKey) && e.key === 'a' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            if (selectAll && rowChecks.length > 0) {
                e.preventDefault();
                selectAll.checked = true;
                rowChecks.forEach(function(cb) { cb.checked = true; });
                updateBulkBar();
            }
        }
        // Esc to close all dropdowns
        if (e.key === 'Escape') {
            document.querySelectorAll('[data-dropdown].open').forEach(function(dd) {
                dd.classList.remove('open');
            });
        }
    });
})();
</script>