<?php
use App\Helpers\Auth;
$canDelete = Auth::hasRole('super_admin');

// ===== ACTION MAP =====
$actionMap = [
    'login'            => ['Login', 'bi-box-arrow-in-right', '#10b981'],
    'logout'           => ['Logout', 'bi-box-arrow-right', '#64748b'],
    'create'           => ['Create', 'bi-plus-circle-fill', '#3b82f6'],
    'update'           => ['Update', 'bi-pencil-fill', '#f59e0b'],
    'delete'           => ['Delete', 'bi-trash3-fill', '#ef4444'],
    'delete_permanent' => ['Hapus Permanen', 'bi-x-octagon-fill', '#dc2626'],
    'restore'          => ['Restore', 'bi-arrow-counterclockwise', '#8b5cf6'],
    'approve'          => ['Approve', 'bi-check-circle-fill', '#10b981'],
    'reject'           => ['Reject', 'bi-x-circle-fill', '#ef4444'],
    'export'           => ['Export', 'bi-file-earmark-spreadsheet', '#06b6d4'],
    'backup'           => ['Backup', 'bi-database-fill', '#8b5cf6'],
];

// ===== USER MAP =====
$userMap = [];
foreach ($logs as $l) {
    $uid = $l['user_id'] ?? 0;
    $uname = $l['full_name'] ?? $l['username'] ?? 'System';
    if ($uid > 0 && !isset($userMap[$uid])) {
        $userMap[$uid] = $uname;
    }
}

// ===== STATS =====
try {
    $statTotal       = $pagination['total'] ?? count($logs);
    $statToday       = count(array_filter($logs, fn($l) => date('Y-m-d', strtotime($l['created_at'])) === date('Y-m-d')));
    $statUniqueUsers = count(array_unique(array_column($logs, 'user_id')));
    $statCritical    = count(array_filter($logs, fn($l) => in_array($l['action'], ['delete', 'delete_permanent', 'login'])));
} catch (\Throwable $e) {
    $statTotal = $statToday = $statUniqueUsers = $statCritical = 0;
}

// ===== FILTER PARAMS =====
$searchQuery = $_GET['q'] ?? '';
$activeAction = $_GET['action'] ?? '';
$activeUser = $_GET['user'] ?? '';
$activeDate = $_GET['date'] ?? '';

// Filter logs
$filteredLogs = $logs;
if ($activeAction !== '') {
    $filteredLogs = array_filter($filteredLogs, fn($l) => $l['action'] === $activeAction);
}
if ($activeUser !== '') {
    $filteredLogs = array_filter($filteredLogs, fn($l) => (string)($l['user_id'] ?? '') === $activeUser);
}
if ($activeDate !== '') {
    $filteredLogs = array_filter($filteredLogs, fn($l) => date('Y-m-d', strtotime($l['created_at'])) === $activeDate);
}
if ($searchQuery !== '') {
    $sq = strtolower($searchQuery);
    $filteredLogs = array_filter($filteredLogs, function($l) use ($sq) {
        return stripos($l['description'] ?? '', $sq) !== false ||
               stripos($l['action'] ?? '', $sq) !== false ||
               stripos($l['full_name'] ?? $l['username'] ?? '', $sq) !== false ||
               stripos($l['ip_address'] ?? '', $sq) !== false;
    });
}

$currentView = $_GET['view'] ?? 'table';
$startPage = max(1, ($pagination['page'] ?? 1) - 2);
$endPage = min($pagination['totalPages'] ?? 1, ($pagination['page'] ?? 1) + 2);
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-log-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-log-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-log-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#3b82f6);}
.ult-log-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-log-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-log-stat-info{flex:1;min-width:0;}
.ult-log-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-log-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-log-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-log-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-log-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-log-dropdown{position:relative;display:inline-block;}
.ult-log-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:160px;justify-content:space-between;}
.ult-log-dropdown-trigger:hover{border-color:var(--admin-primary);background:var(--admin-bg);}
.ult-log-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.05);}
.ult-log-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-log-dropdown.open .ult-log-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-log-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:300px;overflow-y:auto;}
.ult-log-dropdown.open .ult-log-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-log-dropdown-menu.right{left:auto;right:0;}
.ult-log-dd-search{padding:.625rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);}
.ult-log-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;font-size:.825rem;background:var(--admin-bg);}
.ult-log-dd-search input:focus{outline:none;border-color:var(--admin-primary);}
.ult-log-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-log-dd-item:last-child{border-bottom:none;}
.ult-log-dd-item:hover{background:var(--admin-bg);}
.ult-log-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-log-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-log-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-log-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-log-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== FILTER CHIPS ===== */
.ult-log-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);align-items:center;}
.ult-log-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-log-chip i{font-size:.7rem;}
.ult-log-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-log-chip-close:hover{color:var(--admin-danger);}
.ult-log-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;}
.ult-log-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== VIEW TOGGLE ===== */
.ult-log-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-log-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-log-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-log-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-log-table-wrap{overflow-x:auto;}
.ult-log-cell{display:flex;align-items:center;gap:.75rem;min-width:180px;}
.ult-log-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--admin-primary),var(--admin-primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;flex-shrink:0;}
.ult-log-avatar.system{background:linear-gradient(135deg,#64748b,#475569);}
.ult-log-info{flex:1;min-width:0;}
.ult-log-name{font-weight:700;color:var(--admin-text);font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-log-username{font-size:.7rem;color:var(--admin-text-muted);font-family:monospace;}
.ult-log-date{display:flex;flex-direction:column;gap:.125rem;}
.ult-log-date-main{font-weight:600;font-size:.85rem;color:var(--admin-text);}
.ult-log-date-sub{font-size:.7rem;color:var(--admin-text-muted);display:flex;align-items:center;gap:.25rem;}
.ult-log-date-sub i{font-size:.65rem;}
.ult-log-action{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
.ult-log-desc{font-size:.825rem;color:var(--admin-text-secondary);line-height:1.5;max-width:300px;}
.ult-log-ip{font-family:monospace;font-size:.78rem;color:var(--admin-text-muted);background:var(--admin-bg);padding:.25rem .5rem;border-radius:4px;display:inline-block;}

/* ===== ROW ACTION ===== */
.ult-log-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-log-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-log-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-log-row-btn.view:hover{color:var(--admin-primary);background:rgba(26,82,118,.08);}
.ult-log-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-log-row-menu{min-width:240px;}
.ult-log-row-menu .ult-log-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-log-row-menu .ult-log-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-log-row-menu .ult-log-dd-item.danger{color:var(--admin-danger);}
.ult-log-row-menu .ult-log-dd-item.danger:hover{background:rgba(239,68,68,.08);}

/* ===== TIMELINE VIEW ===== */
.ult-log-timeline{padding:1.5rem;}
.ult-log-timeline-wrap{position:relative;padding-left:2rem;}
.ult-log-timeline-wrap::before{content:'';position:absolute;left:19px;top:0;bottom:0;width:2px;background:linear-gradient(180deg,var(--admin-primary),var(--admin-primary-light));}
.ult-log-timeline-item{position:relative;margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px dashed var(--admin-border);}
.ult-log-timeline-item:last-child{margin-bottom:0;padding-bottom:0;border-bottom:none;}
.ult-log-timeline-dot{position:absolute;left:-2rem;top:0;width:40px;height:40px;border-radius:50%;background:var(--admin-surface);border:3px solid var(--admin-primary);display:flex;align-items:center;justify-content:center;font-size:1rem;color:var(--admin-primary);z-index:2;}
.ult-log-timeline-content{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);padding:1.25rem;transition:all .3s;}
.ult-log-timeline-content:hover{box-shadow:0 8px 25px rgba(0,0,0,.08);border-color:var(--admin-primary);}
.ult-log-timeline-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:.75rem;flex-wrap:wrap;}
.ult-log-timeline-user{display:flex;align-items:center;gap:.75rem;}
.ult-log-timeline-user-info h4{font-size:.95rem;font-weight:700;color:var(--admin-text);margin:0;}
.ult-log-timeline-user-info small{font-size:.75rem;color:var(--admin-text-muted);font-family:monospace;}
.ult-log-timeline-time{font-size:.8rem;color:var(--admin-text-secondary);display:flex;align-items:center;gap:.375rem;}
.ult-log-timeline-time i{font-size:.75rem;color:var(--admin-primary);}
.ult-log-timeline-body{margin-top:.75rem;}
.ult-log-timeline-desc{font-size:.875rem;color:var(--admin-text);line-height:1.6;margin-bottom:.75rem;}
.ult-log-timeline-meta{display:flex;gap:1rem;flex-wrap:wrap;font-size:.75rem;color:var(--admin-text-muted);}
.ult-log-timeline-meta span{display:flex;align-items:center;gap:.25rem;}
.ult-log-timeline-meta i{font-size:.7rem;}
.ult-log-timeline-actions{display:flex;gap:.375rem;margin-top:1rem;}
.ult-log-timeline-btn{padding:.375rem .75rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:6px;font-size:.75rem;font-weight:600;color:var(--admin-text);cursor:pointer;display:inline-flex;align-items:center;gap:.375rem;transition:all .2s;text-decoration:none!important;}
.ult-log-timeline-btn:hover{background:var(--admin-primary);color:#fff!important;border-color:var(--admin-primary);}
.ult-log-timeline-btn.danger:hover{background:var(--admin-danger);border-color:var(--admin-danger);}

/* ===== JSON VIEWER MODAL ===== */
.ult-log-modal{position:fixed;inset:0;z-index:10000;display:none;align-items:center;justify-content:center;padding:1rem;}
.ult-log-modal.active{display:flex;}
.ult-log-modal-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);}
.ult-log-modal-content{position:relative;background:var(--admin-surface);border-radius:var(--radius-xl);max-width:600px;width:100%;max-height:85vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,.3);animation:ult-log-modalIn .3s ease;}
@keyframes ult-log-modalIn{from{opacity:0;transform:translateY(-25px) scale(.96);}to{opacity:1;transform:translateY(0) scale(1);}}
.ult-log-modal-header{padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);display:flex;justify-content:space-between;align-items:center;}
.ult-log-modal-header h3{font-size:1.1rem;font-weight:700;display:flex;align-items:center;gap:.5rem;}
.ult-log-modal-close{background:var(--admin-bg);border:none;width:32px;height:32px;border-radius:50%;font-size:1rem;cursor:pointer;color:var(--admin-text-secondary);}
.ult-log-modal-close:hover{background:var(--admin-danger);color:#fff;}
.ult-log-modal-body{padding:1.5rem;}
.ult-log-json{background:#1e293b;color:#e2e8f0;padding:1rem;border-radius:var(--radius-md);font-family:monospace;font-size:.8rem;line-height:1.6;overflow-x:auto;white-space:pre-wrap;}
.ult-log-json-key{color:#93c5fd;}
.ult-log-json-string{color:#86efac;}
.ult-log-json-number{color:#fca5a5;}
.ult-log-json-null{color:#94a3b8;}

/* ===== EMPTY STATE ===== */
.ult-log-empty{text-align:center;padding:4rem 2rem;}
.ult-log-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#8b5cf6;display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-log-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(139,92,246,.3);border-radius:50%;animation:ult-log-spin 20s linear infinite;}
@keyframes ult-log-spin{to{transform:rotate(360deg);}}
.ult-log-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-log-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== PAGINATION ===== */
.ult-log-pagination{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;border-top:1px solid var(--admin-border);flex-wrap:wrap;gap:1rem;}
.ult-log-page-info{font-size:.85rem;color:var(--admin-text-secondary);}
.ult-log-page-info strong{color:var(--admin-text);}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-log-stats{grid-template-columns:repeat(2,1fr);}
    .ult-log-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-log-stats{grid-template-columns:1fr;}
    .ult-log-timeline{padding:1rem;}
    .ult-log-timeline-wrap{padding-left:1.5rem;}
    .ult-log-timeline-wrap::before{left:14px;}
    .ult-log-timeline-dot{left:-1.5rem;width:32px;height:32px;font-size:.85rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-log-stats">
    <div class="ult-log-stat" style="--ult-color:#3b82f6;">
        <div class="ult-log-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-clock-history"></i></div>
        <div class="ult-log-stat-info">
            <div class="ult-log-stat-label">Total Log</div>
            <div class="ult-log-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-log-stat-sub"><i class="bi bi-database-fill"></i> Halaman <?= $pagination['page'] ?? 1 ?> dari <?= $pagination['totalPages'] ?? 1 ?></div>
        </div>
    </div>
    <div class="ult-log-stat" style="--ult-color:#10b981;">
        <div class="ult-log-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-calendar-day"></i></div>
        <div class="ult-log-stat-info">
            <div class="ult-log-stat-label">Hari Ini</div>
            <div class="ult-log-stat-value"><?= number_format($statToday) ?></div>
            <div class="ult-log-stat-sub"><i class="bi bi-clock-fill"></i> <?= date('d M Y') ?></div>
        </div>
    </div>
    <div class="ult-log-stat" style="--ult-color:#8b5cf6;">
        <div class="ult-log-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-people-fill"></i></div>
        <div class="ult-log-stat-info">
            <div class="ult-log-stat-label">User Unik</div>
            <div class="ult-log-stat-value"><?= number_format($statUniqueUsers) ?></div>
            <div class="ult-log-stat-sub"><i class="bi bi-person-badge"></i> Aktif di halaman ini</div>
        </div>
    </div>
    <div class="ult-log-stat" style="--ult-color:#ef4444;">
        <div class="ult-log-stat-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="ult-log-stat-info">
            <div class="ult-log-stat-label">Aksi Kritis</div>
            <div class="ult-log-stat-value"><?= number_format($statCritical) ?></div>
            <div class="ult-log-stat-sub"><i class="bi bi-shield-fill-exclamation"></i> Delete/Login</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Log Aktivitas</h1>
        <p class="page-subtitle">Audit trail sistem BAUK UNIMOF</p>
    </div>
    <div class="ult-log-header-actions">
        <div class="ult-log-view-toggle" id="ultLogViewToggle">
            <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
            </button>
            <button type="button" class="<?= $currentView === 'grid' ? 'active' : '' ?>" data-view="grid" title="Tampilan Timeline">
                <i class="bi bi-diagram-3-fill"></i> <span class="d-none-mobile">Timeline</span>
            </button>
        </div>
        <button type="button" class="btn btn-secondary" onclick="if(typeof showToast!=='undefined')showToast('Export log segera hadir','info');">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export Log
        </button>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-log-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-clock-history"></i> Riwayat Aktivitas</h3>
                    <span class="pgn-result-info">
                        Menampilkan <strong><?= count($filteredLogs) ?></strong> dari <strong><?= number_format($statTotal) ?></strong> log
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Toolbar -->
        <form method="GET" action="<?= url('admin/aktivitas') ?>" class="pgn-toolbar" style="padding:1rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="toolbar-search" style="flex:2;min-width:220px;">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Cari deskripsi, aksi, user, atau IP...">
            </div>
            
            <!-- Custom Action Dropdown -->
            <div class="ult-log-dropdown" data-dropdown>
                <input type="hidden" name="action" id="ultLogActionValue" value="<?= htmlspecialchars($activeAction) ?>">
                <button type="button" class="ult-log-dropdown-trigger <?= $activeAction !== '' ? 'has-value' : '' ?>" id="ultLogActionTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;">
                        <?php if ($activeAction !== '' && isset($actionMap[$activeAction])): 
                            [$lbl, $icn, $clr] = $actionMap[$activeAction]; ?>
                            <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i>
                            <span><?= $lbl ?></span>
                        <?php else: ?>
                            <i class="bi bi-lightning-fill" style="color:var(--admin-primary);"></i>
                            <span>Semua Aksi</span>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-log-dropdown-menu" style="min-width:220px;">
                    <div class="ult-log-dd-search"><input type="text" placeholder="Cari aksi..." data-search></div>
                    <div class="ult-log-dd-item <?= $activeAction === '' ? 'selected' : '' ?>" data-value="">
                        <i class="bi bi-lightning-fill" style="color:var(--admin-text-muted);"></i><span>Semua Aksi</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-log-dd-divider"></div>
                    <div class="ult-log-dd-header">Autentikasi</div>
                    <?php foreach (['login', 'logout'] as $act): 
                        [$lbl, $icn, $clr] = $actionMap[$act]; ?>
                    <div class="ult-log-dd-item <?= $activeAction === $act ? 'selected' : '' ?>" data-value="<?= $act ?>">
                        <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i><span><?= $lbl ?></span><i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                    <div class="ult-log-dd-header">CRUD</div>
                    <?php foreach (['create', 'update', 'delete', 'delete_permanent', 'restore'] as $act): 
                        [$lbl, $icn, $clr] = $actionMap[$act]; ?>
                    <div class="ult-log-dd-item <?= $activeAction === $act ? 'selected' : '' ?>" data-value="<?= $act ?>">
                        <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i><span><?= $lbl ?></span><i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                    <div class="ult-log-dd-header">Lainnya</div>
                    <?php foreach (['approve', 'reject', 'export', 'backup'] as $act): 
                        [$lbl, $icn, $clr] = $actionMap[$act]; ?>
                    <div class="ult-log-dd-item <?= $activeAction === $act ? 'selected' : '' ?>" data-value="<?= $act ?>">
                        <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i><span><?= $lbl ?></span><i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Custom User Dropdown -->
            <div class="ult-log-dropdown" data-dropdown>
                <input type="hidden" name="user" id="ultLogUserValue" value="<?= htmlspecialchars($activeUser) ?>">
                <button type="button" class="ult-log-dropdown-trigger <?= $activeUser !== '' ? 'has-value' : '' ?>" id="ultLogUserTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                        <i class="bi bi-person-fill" style="color:var(--admin-primary);"></i>
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= $activeUser !== '' ? htmlspecialchars($userMap[$activeUser] ?? 'User #' . $activeUser) : 'Semua User' ?>
                        </span>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-log-dropdown-menu" style="min-width:240px;">
                    <div class="ult-log-dd-search"><input type="text" placeholder="Cari user..." data-search></div>
                    <div class="ult-log-dd-item <?= $activeUser === '' ? 'selected' : '' ?>" data-value="">
                        <i class="bi bi-person-fill" style="color:var(--admin-text-muted);"></i><span>Semua User</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-log-dd-divider"></div>
                    <?php foreach ($userMap as $uid => $uname): ?>
                    <div class="ult-log-dd-item <?= $activeUser === (string)$uid ? 'selected' : '' ?>" data-value="<?= $uid ?>" data-label="<?= htmlspecialchars($uname) ?>">
                        <div class="ult-log-avatar" style="width:24px;height:24px;font-size:.65rem;"><?= strtoupper(substr($uname, 0, 1)) ?></div>
                        <span><?= htmlspecialchars($uname) ?></span>
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Date Filter -->
            <input type="date" name="date" value="<?= htmlspecialchars($activeDate) ?>" style="padding:.625rem .875rem;border:1px solid var(--admin-border);border-radius:var(--radius-md);font-size:.85rem;background:var(--admin-surface);">
            
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Terapkan</button>
            <?php if ($searchQuery !== '' || $activeAction !== '' || $activeUser !== '' || $activeDate !== ''): ?>
                <a href="<?= url('admin/aktivitas') ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
            <?php endif; ?>
        </form>
        
        <!-- Filter Chips -->
        <?php if ($searchQuery !== '' || $activeAction !== '' || $activeUser !== '' || $activeDate !== ''): ?>
        <div class="ult-log-chips">
            <span class="ult-log-chips-label"><i class="bi bi-funnel-fill"></i> Filter Aktif:</span>
            <?php if ($searchQuery !== ''): ?>
                <span class="ult-log-chip">
                    <i class="bi bi-search"></i> Pencarian: "<strong><?= htmlspecialchars($searchQuery) ?></strong>"
                    <a href="<?= url('admin/aktivitas') . '?action=' . $activeAction . '&user=' . $activeUser . '&date=' . $activeDate ?>" class="ult-log-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($activeAction !== '' && isset($actionMap[$activeAction])): ?>
                <span class="ult-log-chip">
                    <i class="bi <?= $actionMap[$activeAction][1] ?>"></i> Aksi: <strong><?= $actionMap[$activeAction][0] ?></strong>
                    <a href="<?= url('admin/aktivitas') . '?q=' . $searchQuery . '&user=' . $activeUser . '&date=' . $activeDate ?>" class="ult-log-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($activeUser !== '' && isset($userMap[$activeUser])): ?>
                <span class="ult-log-chip">
                    <i class="bi bi-person-fill"></i> User: <strong><?= htmlspecialchars($userMap[$activeUser]) ?></strong>
                    <a href="<?= url('admin/aktivitas') . '?q=' . $searchQuery . '&action=' . $activeAction . '&date=' . $activeDate ?>" class="ult-log-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($activeDate !== ''): ?>
                <span class="ult-log-chip">
                    <i class="bi bi-calendar-fill"></i> Tanggal: <strong><?= date('d M Y', strtotime($activeDate)) ?></strong>
                    <a href="<?= url('admin/aktivitas') . '?q=' . $searchQuery . '&action=' . $activeAction . '&user=' . $activeUser ?>" class="ult-log-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <a href="<?= url('admin/aktivitas') ?>" class="ult-log-chips-clear"><i class="bi bi-x-circle"></i> Hapus Semua</a>
        </div>
        <?php endif; ?>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultLogTableView" class="ult-log-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th style="min-width:180px;">Pengguna</th>
                        <th>Aksi</th>
                        <th style="min-width:250px;">Keterangan</th>
                        <th>IP Address</th>
                        <th style="width:100px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filteredLogs as $l): 
                    [$aLbl, $aIcn, $aClr] = $actionMap[$l['action']] ?? [$l['action'], 'bi-circle-fill', '#64748b'];
                    $uname = $l['full_name'] ?? $l['username'] ?? 'System';
                    $isSystem = empty($l['user_id']);
                ?>
                    <tr>
                        <td>
                            <div class="ult-log-date">
                                <span class="ult-log-date-main"><?= date('d M Y', strtotime($l['created_at'])) ?></span>
                                <span class="ult-log-date-sub"><i class="bi bi-clock-fill"></i> <?= date('H:i:s', strtotime($l['created_at'])) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="ult-log-cell">
                                <div class="ult-log-avatar <?= $isSystem ? 'system' : '' ?>">
                                    <?= strtoupper(substr($uname, 0, 1)) ?>
                                </div>
                                <div class="ult-log-info">
                                    <div class="ult-log-name"><?= htmlspecialchars($uname) ?></div>
                                    <?php if (!empty($l['username'])): ?>
                                    <div class="ult-log-username">@<?= htmlspecialchars($l['username']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="ult-log-action" style="background:<?= $aClr ?>20;color:<?= $aClr ?>;">
                                <i class="bi <?= $aIcn ?>"></i> <?= $aLbl ?>
                            </span>
                        </td>
                        <td class="ult-log-desc"><?= htmlspecialchars($l['description'] ?? '-') ?></td>
                        <td><span class="ult-log-ip"><?= htmlspecialchars($l['ip_address'] ?? '-') ?></span></td>
                        <td>
                            <div class="ult-log-row-actions">
                                <button type="button" class="ult-log-row-btn view" title="Lihat Detail JSON" onclick='ultShowLogDetail(<?= json_encode([
                                    "id" => $l['id'],
                                    "user" => $uname,
                                    "action" => $aLbl,
                                    "action_raw" => $l['action'],
                                    "description" => $l['description'] ?? '',
                                    "ip" => $l['ip_address'] ?? '-',
                                    "time" => date('d M Y H:i:s', strtotime($l['created_at'])),
                                    "table" => $l['table_name'] ?? '-',
                                    "record" => $l['record_id'] ?? '-',
                                    "old" => $l['old_values'] ?? null,
                                    "new" => $l['new_values'] ?? null,
                                    "ua" => $l['user_agent'] ?? '-',
                                ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>)'>
                                    <i class="bi bi-code-square"></i>
                                </button>
                                
                                <div class="ult-log-dropdown" data-dropdown>
                                    <button type="button" class="ult-log-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="ult-log-dropdown-menu right ult-log-row-menu">
                                        <div class="ult-log-dd-header">Detail Log</div>
                                        <button type="button" class="ult-log-dd-item" style="width:100%;text-align:left;border:none;background:none;cursor:pointer;" onclick='ultShowLogDetail(<?= json_encode([
                                            "id" => $l['id'],
                                            "user" => $uname,
                                            "action" => $aLbl,
                                            "action_raw" => $l['action'],
                                            "description" => $l['description'] ?? '',
                                            "ip" => $l['ip_address'] ?? '-',
                                            "time" => date('d M Y H:i:s', strtotime($l['created_at'])),
                                            "table" => $l['table_name'] ?? '-',
                                            "record" => $l['record_id'] ?? '-',
                                            "old" => $l['old_values'] ?? null,
                                            "new" => $l['new_values'] ?? null,
                                            "ua" => $l['user_agent'] ?? '-',
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>)'>
                                            <i class="bi bi-code-square"></i><span>Lihat Detail JSON</span>
                                        </button>
                                        <a href="<?= url('admin/pengguna') ?>?q=<?= urlencode($l['username'] ?? '') ?>" class="ult-log-dd-item">
                                            <i class="bi bi-person-fill"></i><span>Lihat Profil User</span>
                                        </a>
                                        <div class="ult-log-dd-divider"></div>
                                        <div class="ult-log-dd-header">Informasi</div>
                                        <div class="ult-log-dd-item" style="cursor:default;">
                                            <i class="bi bi-table"></i>
                                            <span>Tabel: <?= htmlspecialchars($l['table_name'] ?? '-') ?></span>
                                        </div>
                                        <div class="ult-log-dd-item" style="cursor:default;">
                                            <i class="bi bi-hash"></i>
                                            <span>Record ID: <?= htmlspecialchars($l['record_id'] ?? '-') ?></span>
                                        </div>
                                        <div class="ult-log-dd-item" style="cursor:default;">
                                            <i class="bi bi-globe"></i>
                                            <span>IP: <?= htmlspecialchars($l['ip_address'] ?? '-') ?></span>
                                        </div>
                                        <?php if ($canDelete): ?>
                                        <div class="ult-log-dd-divider"></div>
                                        <a href="#" class="ult-log-dd-item danger" onclick="if(typeof showToast!=='undefined')showToast('Hapus log segera hadir','warning');return false;">
                                            <i class="bi bi-trash3-fill"></i><span>Hapus Log</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$filteredLogs): ?>
                    <tr><td colspan="6">
                        <div class="ult-log-empty">
                            <div class="ult-log-empty-icon"><i class="bi bi-clock-history"></i></div>
                            <h3>Tidak Ada Log Aktivitas</h3>
                            <p>
                                <?php if ($searchQuery !== '' || $activeAction !== '' || $activeUser !== '' || $activeDate !== ''): ?>
                                    Tidak ada log yang cocok dengan filter Anda. Coba ubah kriteria pencarian.
                                <?php else: ?>
                                    Belum ada aktivitas yang tercatat dalam sistem.
                                <?php endif; ?>
                            </p>
                            <?php if ($searchQuery !== '' || $activeAction !== '' || $activeUser !== '' || $activeDate !== ''): ?>
                                <a href="<?= url('admin/aktivitas') ?>" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset Filter</a>
                            <?php endif; ?>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== TIMELINE VIEW ===== -->
        <div id="ultLogGridView" class="ult-log-timeline" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php if ($filteredLogs): ?>
            <div class="ult-log-timeline-wrap">
                <?php foreach ($filteredLogs as $l): 
                    [$aLbl, $aIcn, $aClr] = $actionMap[$l['action']] ?? [$l['action'], 'bi-circle-fill', '#64748b'];
                    $uname = $l['full_name'] ?? $l['username'] ?? 'System';
                    $isSystem = empty($l['user_id']);
                ?>
                <div class="ult-log-timeline-item">
                    <div class="ult-log-timeline-dot" style="border-color:<?= $aClr ?>;color:<?= $aClr ?>;">
                        <i class="bi <?= $aIcn ?>"></i>
                    </div>
                    <div class="ult-log-timeline-content">
                        <div class="ult-log-timeline-header">
                            <div class="ult-log-timeline-user">
                                <div class="ult-log-avatar <?= $isSystem ? 'system' : '' ?>" style="width:40px;height:40px;font-size:.85rem;">
                                    <?= strtoupper(substr($uname, 0, 1)) ?>
                                </div>
                                <div class="ult-log-timeline-user-info">
                                    <h4><?= htmlspecialchars($uname) ?></h4>
                                    <small><?= htmlspecialchars($l['username'] ?? 'system') ?></small>
                                </div>
                            </div>
                            <div class="ult-log-timeline-time">
                                <i class="bi bi-clock-fill"></i>
                                <?= date('d M Y H:i:s', strtotime($l['created_at'])) ?>
                            </div>
                        </div>
                        <div class="ult-log-timeline-body">
                            <span class="ult-log-action" style="background:<?= $aClr ?>20;color:<?= $aClr ?>;margin-bottom:.5rem;display:inline-flex;">
                                <i class="bi <?= $aIcn ?>"></i> <?= $aLbl ?>
                            </span>
                            <div class="ult-log-timeline-desc"><?= htmlspecialchars($l['description'] ?? '-') ?></div>
                            <div class="ult-log-timeline-meta">
                                <span><i class="bi bi-table"></i> Tabel: <?= htmlspecialchars($l['table_name'] ?? '-') ?></span>
                                <span><i class="bi bi-hash"></i> ID: <?= htmlspecialchars($l['record_id'] ?? '-') ?></span>
                                <span><i class="bi bi-globe"></i> IP: <?= htmlspecialchars($l['ip_address'] ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="ult-log-timeline-actions">
                            <button type="button" class="ult-log-timeline-btn" onclick='ultShowLogDetail(<?= json_encode([
                                "id" => $l['id'],
                                "user" => $uname,
                                "action" => $aLbl,
                                "action_raw" => $l['action'],
                                "description" => $l['description'] ?? '',
                                "ip" => $l['ip_address'] ?? '-',
                                "time" => date('d M Y H:i:s', strtotime($l['created_at'])),
                                "table" => $l['table_name'] ?? '-',
                                "record" => $l['record_id'] ?? '-',
                                "old" => $l['old_values'] ?? null,
                                "new" => $l['new_values'] ?? null,
                                "ua" => $l['user_agent'] ?? '-',
                            ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>)'>
                                <i class="bi bi-code-square"></i> Detail JSON
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="ult-log-empty">
                <div class="ult-log-empty-icon"><i class="bi bi-clock-history"></i></div>
                <h3>Tidak Ada Log Aktivitas</h3>
                <p>Belum ada aktivitas yang tercatat.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- ===== PAGINATION ===== -->
        <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
        <div class="ult-log-pagination">
            <div class="ult-log-page-info">
                Halaman <strong><?= $pagination['page'] ?? 1 ?></strong> dari <strong><?= $pagination['totalPages'] ?? 1 ?></strong>
            </div>
            <div style="display:flex;gap:.375rem;">
                <?php 
                $baseUrl = url('admin/aktivitas') . '?q=' . $searchQuery . '&action=' . $activeAction . '&user=' . $activeUser . '&date=' . $activeDate;
                $currentPage = $pagination['page'] ?? 1;
                $totalPages = $pagination['totalPages'] ?? 1;
                ?>
                <a href="<?= $baseUrl . '&page=' . max(1, $currentPage - 1) ?>" class="pgn-pagination a <?= $currentPage <= 1 ? 'disabled' : '' ?>"><i class="bi bi-chevron-left"></i></a>
                <?php if ($startPage > 1): ?><a href="<?= $baseUrl . '&page=1' ?>" class="pgn-pagination a">1</a><span style="align-self:center;padding:0 .25rem;">...</span><?php endif; ?>
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="<?= $baseUrl . '&page=' . $i ?>" class="pgn-pagination a <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($endPage < $totalPages): ?><span style="align-self:center;padding:0 .25rem;">...</span><a href="<?= $baseUrl . '&page=' . $totalPages ?>" class="pgn-pagination a"><?= $totalPages ?></a><?php endif; ?>
                <a href="<?= $baseUrl . '&page=' . min($totalPages, $currentPage + 1) ?>" class="pgn-pagination a <?= $currentPage >= $totalPages ? 'disabled' : '' ?>"><i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- ===== JSON DETAIL MODAL ===== -->
<div class="ult-log-modal" id="ultLogModal">
    <div class="ult-log-modal-backdrop" onclick="ultCloseLogDetail()"></div>
    <div class="ult-log-modal-content">
        <div class="ult-log-modal-header">
            <h3><i class="bi bi-code-square"></i> Detail Log #<span id="ultLogModalId">-</span></h3>
            <button class="ult-log-modal-close" onclick="ultCloseLogDetail()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="ult-log-modal-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                <div>
                    <div style="font-size:.7rem;text-transform:uppercase;color:var(--admin-text-muted);font-weight:700;margin-bottom:.25rem;">Pengguna</div>
                    <div style="font-weight:700;" id="ultLogModalUser">-</div>
                </div>
                <div>
                    <div style="font-size:.7rem;text-transform:uppercase;color:var(--admin-text-muted);font-weight:700;margin-bottom:.25rem;">Aksi</div>
                    <div id="ultLogModalAction">-</div>
                </div>
                <div>
                    <div style="font-size:.7rem;text-transform:uppercase;color:var(--admin-text-muted);font-weight:700;margin-bottom:.25rem;">Waktu</div>
                    <div style="font-size:.85rem;" id="ultLogModalTime">-</div>
                </div>
                <div>
                    <div style="font-size:.7rem;text-transform:uppercase;color:var(--admin-text-muted);font-weight:700;margin-bottom:.25rem;">IP Address</div>
                    <div style="font-family:monospace;font-size:.85rem;" id="ultLogModalIp">-</div>
                </div>
                <div>
                    <div style="font-size:.7rem;text-transform:uppercase;color:var(--admin-text-muted);font-weight:700;margin-bottom:.25rem;">Tabel</div>
                    <div style="font-family:monospace;font-size:.85rem;" id="ultLogModalTable">-</div>
                </div>
                <div>
                    <div style="font-size:.7rem;text-transform:uppercase;color:var(--admin-text-muted);font-weight:700;margin-bottom:.25rem;">Record ID</div>
                    <div style="font-family:monospace;font-size:.85rem;" id="ultLogModalRecord">-</div>
                </div>
            </div>
            <div style="margin-bottom:1rem;">
                <div style="font-size:.7rem;text-transform:uppercase;color:var(--admin-text-muted);font-weight:700;margin-bottom:.375rem;">Deskripsi</div>
                <div style="background:var(--admin-bg);padding:.875rem;border-radius:var(--radius-md);font-size:.875rem;line-height:1.6;" id="ultLogModalDesc">-</div>
            </div>
            <div id="ultLogModalOldWrap" style="margin-bottom:1rem;display:none;">
                <div style="font-size:.7rem;text-transform:uppercase;color:var(--admin-danger);font-weight:700;margin-bottom:.375rem;"><i class="bi bi-arrow-left"></i> Nilai Lama (Old Values)</div>
                <div class="ult-log-json" id="ultLogModalOld">-</div>
            </div>
            <div id="ultLogModalNewWrap" style="margin-bottom:1rem;display:none;">
                <div style="font-size:.7rem;text-transform:uppercase;color:var(--admin-success);font-weight:700;margin-bottom:.375rem;"><i class="bi bi-arrow-right"></i> Nilai Baru (New Values)</div>
                <div class="ult-log-json" id="ultLogModalNew">-</div>
            </div>
            <div id="ultLogModalUaWrap" style="display:none;">
                <div style="font-size:.7rem;text-transform:uppercase;color:var(--admin-text-muted);font-weight:700;margin-bottom:.375rem;">User Agent</div>
                <div style="background:var(--admin-bg);padding:.75rem;border-radius:var(--radius-md);font-size:.75rem;font-family:monospace;color:var(--admin-text-secondary);word-break:break-all;" id="ultLogModalUa">-</div>
            </div>
        </div>
    </div>
</div>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function(){
    'use strict';
    
    // ===== CUSTOM DROPDOWN =====
    document.querySelectorAll('.ult-log-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-log-dropdown-trigger, .ult-log-row-btn');
        const menu = dd.querySelector('.ult-log-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-log-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
            if(dd.classList.contains('open')){
                const s = menu.querySelector('[data-search]');
                if(s) setTimeout(() => s.focus(), 50);
            }
        });
        menu.querySelectorAll('.ult-log-dd-item[data-value]').forEach(function(item){
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
                menu.querySelectorAll('.ult-log-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
                if(hidden && (hidden.name === 'action' || hidden.name === 'user')){
                    const form = dd.closest('form');
                    if(form) form.submit();
                }
            });
        });
        const s = menu.querySelector('[data-search]');
        if(s){
            s.addEventListener('input', function(){
                const q = this.value.toLowerCase();
                menu.querySelectorAll('.ult-log-dd-item').forEach(i => {
                    if(i.hasAttribute('data-value')) i.style.display = i.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-log-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultLogViewToggle');
    const tv = document.getElementById('ultLogTableView');
    const gv = document.getElementById('ultLogGridView');
    if(vt){
        vt.querySelectorAll('button[data-view]').forEach(btn => {
            btn.addEventListener('click', function(){
                const v = this.getAttribute('data-view');
                vt.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                if(tv && gv){
                    if(v === 'grid'){ tv.style.display = 'none'; gv.style.display = 'block'; }
                    else { tv.style.display = ''; gv.style.display = 'none'; }
                }
                try { localStorage.setItem('ult_log_view', v); } catch(e){}
            });
        });
    }
})();

// ===== JSON DETAIL MODAL =====
function ultShowLogDetail(data) {
    document.getElementById('ultLogModalId').textContent = data.id;
    document.getElementById('ultLogModalUser').textContent = data.user;
    document.getElementById('ultLogModalAction').innerHTML = '<span class="ult-log-action" style="background:var(--admin-primary);color:#fff;font-size:.7rem;"><i class="bi bi-lightning-fill"></i> ' + data.action + '</span>';
    document.getElementById('ultLogModalTime').textContent = data.time;
    document.getElementById('ultLogModalIp').textContent = data.ip;
    document.getElementById('ultLogModalTable').textContent = data.table;
    document.getElementById('ultLogModalRecord').textContent = data.record;
    document.getElementById('ultLogModalDesc').textContent = data.description;
    
    const oldWrap = document.getElementById('ultLogModalOldWrap');
    const newWrap = document.getElementById('ultLogModalNewWrap');
    const uaWrap = document.getElementById('ultLogModalUaWrap');
    
    if (data.old) {
        try {
            const parsed = typeof data.old === 'string' ? JSON.parse(data.old) : data.old;
            document.getElementById('ultLogModalOld').innerHTML = ultFormatJSON(parsed);
            oldWrap.style.display = 'block';
        } catch(e) {
            document.getElementById('ultLogModalOld').textContent = data.old;
            oldWrap.style.display = 'block';
        }
    } else {
        oldWrap.style.display = 'none';
    }
    
    if (data.new) {
        try {
            const parsed = typeof data.new === 'string' ? JSON.parse(data.new) : data.new;
            document.getElementById('ultLogModalNew').innerHTML = ultFormatJSON(parsed);
            newWrap.style.display = 'block';
        } catch(e) {
            document.getElementById('ultLogModalNew').textContent = data.new;
            newWrap.style.display = 'block';
        }
    } else {
        newWrap.style.display = 'none';
    }
    
    if (data.ua && data.ua !== '-') {
        document.getElementById('ultLogModalUa').textContent = data.ua;
        uaWrap.style.display = 'block';
    } else {
        uaWrap.style.display = 'none';
    }
    
    document.getElementById('ultLogModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function ultCloseLogDetail() {
    document.getElementById('ultLogModal').classList.remove('active');
    document.body.style.overflow = '';
}

function ultFormatJSON(obj, indent) {
    indent = indent || 0;
    const spacing = '  '.repeat(indent);
    if (obj === null) return '<span class="ult-log-json-null">null</span>';
    if (typeof obj === 'string') return '<span class="ult-log-json-string">"' + obj.replace(/</g, '&lt;') + '"</span>';
    if (typeof obj === 'number') return '<span class="ult-log-json-number">' + obj + '</span>';
    if (typeof obj === 'boolean') return '<span class="ult-log-json-number">' + obj + '</span>';
    if (Array.isArray(obj)) {
        if (obj.length === 0) return '[]';
        let result = '[\n';
        obj.forEach(function(item, i) {
            result += spacing + '  ' + ultFormatJSON(item, indent + 1);
            if (i < obj.length - 1) result += ',';
            result += '\n';
        });
        result += spacing + ']';
        return result;
    }
    if (typeof obj === 'object') {
        const keys = Object.keys(obj);
        if (keys.length === 0) return '{}';
        let result = '{\n';
        keys.forEach(function(key, i) {
            result += spacing + '  <span class="ult-log-json-key">"' + key + '"</span>: ' + ultFormatJSON(obj[key], indent + 1);
            if (i < keys.length - 1) result += ',';
            result += '\n';
        });
        result += spacing + '}';
        return result;
    }
    return String(obj);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') ultCloseLogDetail();
});
</script>