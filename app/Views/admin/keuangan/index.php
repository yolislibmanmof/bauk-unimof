<?php
use App\Helpers\Auth;
$canApprove = Auth::hasRole(['super_admin', 'admin_bauk', 'admin_keuangan']);

// ===== QUERY BUILDER =====
$query = function (array $override = []) use ($pagination) {
    $params = [
        'q' => $pagination['search'], 'type' => $pagination['type'],
        'status' => $pagination['status'], 'page' => $pagination['page'],
    ];
    $params = array_filter(array_merge($params, $override), static fn($v) => $v !== '' && $v !== null && $v !== 0);
    return url('admin/keuangan') . ($params ? '?' . http_build_query($params) : '');
};

// ===== TYPE & STATUS MAP =====
$typeMap = [
    'pemasukan'   => ['badge-success', 'Pemasukan', 'bi-arrow-down-circle-fill', '#10b981'],
    'pengeluaran' => ['badge-danger', 'Pengeluaran', 'bi-arrow-up-circle-fill', '#ef4444'],
];

$statusMap = [
    'pending'  => ['badge-warning', 'Pending', 'bi-hourglass-split', '#f59e0b'],
    'approved' => ['badge-success', 'Approved', 'bi-check-circle-fill', '#10b981'],
    'rejected' => ['badge-danger', 'Rejected', 'bi-x-circle-fill', '#ef4444'],
    'cancelled' => ['badge-secondary', 'Cancelled', 'bi-ban-fill', '#64748b'],
];

// ===== CATEGORY MAP =====
$catMap = [];
foreach ($categories as $c) $catMap[$c['id']] = ['name' => $c['name'], 'type' => $c['type']];

// ===== STATS =====
$db = \App\Helpers\Database::getInstance();
try {
    $statTotal      = $db->count('transaksi_keuangan', 'deleted_at IS NULL');
    $statPemasukan  = $db->fetch("SELECT COALESCE(SUM(amount),0) AS t FROM transaksi_keuangan WHERE type='pemasukan' AND status='approved' AND deleted_at IS NULL")['t'] ?? 0;
    $statPengeluaran = $db->fetch("SELECT COALESCE(SUM(amount),0) AS t FROM transaksi_keuangan WHERE type='pengeluaran' AND status='approved' AND deleted_at IS NULL")['t'] ?? 0;
    $statPending    = $db->count('transaksi_keuangan', "status='pending' AND deleted_at IS NULL");
    $saldoBersih    = (float)$statPemasukan - (float)$statPengeluaran;
} catch (\Throwable $e) {
    $statTotal = $statPemasukan = $statPengeluaran = $statPending = $saldoBersih = 0;
}

$startPage = max(1, $pagination['page'] - 2);
$endPage   = min($pagination['totalPages'], $pagination['page'] + 2);
$fromRow   = $pagination['total'] === 0 ? 0 : (($pagination['page'] - 1) * PER_PAGE) + 1;
$toRow     = min($pagination['total'], $pagination['page'] * PER_PAGE);
$currentView = $_GET['view'] ?? 'table';
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-keu-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-keu-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-keu-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#10b981);}
.ult-keu-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-keu-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-keu-stat-info{flex:1;min-width:0;}
.ult-keu-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-keu-stat-value{font-size:1.3rem;font-weight:700;color:var(--admin-text);line-height:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-keu-stat-value.money{font-size:1.1rem;}
.ult-keu-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-keu-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-keu-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-keu-dropdown{position:relative;display:inline-block;}
.ult-keu-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:160px;justify-content:space-between;}
.ult-keu-dropdown-trigger:hover{border-color:var(--admin-primary);background:var(--admin-bg);}
.ult-keu-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.05);}
.ult-keu-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-keu-dropdown.open .ult-keu-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-keu-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:300px;overflow-y:auto;}
.ult-keu-dropdown.open .ult-keu-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-keu-dropdown-menu.right{left:auto;right:0;}
.ult-keu-dd-search{padding:.625rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);}
.ult-keu-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;font-size:.825rem;background:var(--admin-bg);}
.ult-keu-dd-search input:focus{outline:none;border-color:var(--admin-primary);}
.ult-keu-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-keu-dd-item:last-child{border-bottom:none;}
.ult-keu-dd-item:hover{background:var(--admin-bg);}
.ult-keu-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-keu-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-keu-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-keu-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-keu-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== FILTER CHIPS ===== */
.ult-keu-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);align-items:center;}
.ult-keu-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-keu-chip i{font-size:.7rem;}
.ult-keu-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-keu-chip-close:hover{color:var(--admin-danger);}
.ult-keu-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;}
.ult-keu-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== VIEW TOGGLE ===== */
.ult-keu-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-keu-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-keu-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-keu-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-keu-table-wrap{overflow-x:auto;}
.ult-keu-cell{display:flex;align-items:center;gap:.75rem;min-width:180px;}
.ult-keu-trx-icon{width:40px;height:40px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1rem;color:#fff;flex-shrink:0;}
.ult-keu-trx-info{flex:1;min-width:0;}
.ult-keu-trx-number{font-weight:700;color:var(--admin-text);font-size:.85rem;font-family:monospace;}
.ult-keu-trx-desc{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-keu-date{display:flex;flex-direction:column;gap:.125rem;}
.ult-keu-date-main{font-weight:600;font-size:.875rem;color:var(--admin-text);}
.ult-keu-date-sub{font-size:.7rem;color:var(--admin-text-muted);display:flex;align-items:center;gap:.25rem;}
.ult-keu-amount{font-weight:700;font-size:.9rem;}
.ult-keu-amount.income{color:#10b981;}
.ult-keu-amount.expense{color:#ef4444;}
.ult-keu-category{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:var(--admin-bg);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text-secondary);}
.ult-keu-category i{font-size:.7rem;color:var(--admin-primary);}
.ult-keu-status{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}

/* ===== ROW ACTION ===== */
.ult-keu-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-keu-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-keu-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-keu-row-btn.approve:hover{color:#10b981;background:rgba(16,185,129,.08);}
.ult-keu-row-btn.reject:hover{color:#f59e0b;background:rgba(245,158,11,.08);}
.ult-keu-row-btn.edit:hover{color:#f59e0b;background:rgba(245,158,11,.08);}
.ult-keu-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-keu-row-menu{min-width:220px;}
.ult-keu-row-menu .ult-keu-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-keu-row-menu .ult-keu-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-keu-row-menu .ult-keu-dd-item.danger{color:var(--admin-danger);}
.ult-keu-row-menu .ult-keu-dd-item.danger:hover{background:rgba(239,68,68,.08);}
.ult-keu-row-menu .ult-keu-dd-item.success{color:#10b981;}
.ult-keu-row-menu .ult-keu-dd-item.success:hover{background:rgba(16,185,129,.08);}

/* ===== GRID VIEW ===== */
.ult-keu-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.25rem;padding:1.5rem;}
.ult-keu-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;transition:all .3s;position:relative;}
.ult-keu-card:hover{transform:translateY(-4px);box-shadow:0 12px 35px rgba(0,0,0,.1);border-color:var(--admin-primary);}
.ult-keu-card-accent{height:4px;}
.ult-keu-card-accent.income{background:linear-gradient(90deg,#10b981,#059669);}
.ult-keu-card-accent.expense{background:linear-gradient(90deg,#ef4444,#dc2626);}
.ult-keu-card-body{padding:1.25rem;}
.ult-keu-card-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;}
.ult-keu-card-icon{width:44px;height:44px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff;flex-shrink:0;}
.ult-keu-card-status{margin-left:auto;}
.ult-keu-card-trx{font-family:monospace;font-size:.75rem;font-weight:700;color:var(--admin-text-muted);margin-bottom:.25rem;}
.ult-keu-card-title{font-size:1rem;font-weight:700;color:var(--admin-text);margin-bottom:.5rem;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.ult-keu-card-meta{display:flex;flex-direction:column;gap:.375rem;margin-bottom:1rem;}
.ult-keu-card-meta span{display:flex;align-items:center;gap:.375rem;font-size:.8rem;color:var(--admin-text-secondary);}
.ult-keu-card-meta i{font-size:.7rem;color:var(--admin-primary);}
.ult-keu-card-amount{font-size:1.25rem;font-weight:700;margin-bottom:1rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
.ult-keu-card-amount.income{color:#10b981;}
.ult-keu-card-amount.expense{color:#ef4444;}
.ult-keu-card-footer{display:flex;gap:.375rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
.ult-keu-card-btn{flex:1;padding:.5rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:6px;font-size:.78rem;font-weight:600;color:var(--admin-text);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.375rem;transition:all .2s;text-decoration:none!important;}
.ult-keu-card-btn:hover{background:var(--admin-primary);color:#fff!important;border-color:var(--admin-primary);}
.ult-keu-card-btn.approve:hover{background:#10b981;border-color:#10b981;}
.ult-keu-card-btn.reject:hover{background:#f59e0b;border-color:#f59e0b;}
.ult-keu-card-btn.danger:hover{background:var(--admin-danger);border-color:var(--admin-danger);}
.ult-keu-card-actions{position:absolute;top:.75rem;right:.75rem;}

/* ===== EMPTY STATE ===== */
.ult-keu-empty{text-align:center;padding:4rem 2rem;}
.ult-keu-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:var(--admin-primary);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-keu-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(26,82,118,.3);border-radius:50%;animation:ult-keu-spin 20s linear infinite;}
@keyframes ult-keu-spin{to{transform:rotate(360deg);}}
.ult-keu-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-keu-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== PAGINATION ===== */
.ult-keu-pagination{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;border-top:1px solid var(--admin-border);flex-wrap:wrap;gap:1rem;}
.ult-keu-page-info{font-size:.85rem;color:var(--admin-text-secondary);}
.ult-keu-page-info strong{color:var(--admin-text);}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-keu-stats{grid-template-columns:repeat(2,1fr);}
    .ult-keu-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-keu-stats{grid-template-columns:1fr;}
    .ult-keu-grid{grid-template-columns:1fr;padding:1rem;}
    .ult-keu-stat-value{font-size:1rem;}
    .ult-keu-stat-value.money{font-size:.85rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-keu-stats">
    <div class="ult-keu-stat" style="--ult-color:#3b82f6;">
        <div class="ult-keu-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-receipt"></i></div>
        <div class="ult-keu-stat-info">
            <div class="ult-keu-stat-label">Total Transaksi</div>
            <div class="ult-keu-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-keu-stat-sub"><i class="bi bi-database-fill"></i> Keseluruhan</div>
        </div>
    </div>
    <div class="ult-keu-stat" style="--ult-color:#10b981;">
        <div class="ult-keu-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-arrow-down-circle-fill"></i></div>
        <div class="ult-keu-stat-info">
            <div class="ult-keu-stat-label">Total Pemasukan</div>
            <div class="ult-keu-stat-value money">Rp <?= number_format((float)$statPemasukan, 0, ',', '.') ?></div>
            <div class="ult-keu-stat-sub"><i class="bi bi-graph-up"></i> Approved only</div>
        </div>
    </div>
    <div class="ult-keu-stat" style="--ult-color:#ef4444;">
        <div class="ult-keu-stat-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="bi bi-arrow-up-circle-fill"></i></div>
        <div class="ult-keu-stat-info">
            <div class="ult-keu-stat-label">Total Pengeluaran</div>
            <div class="ult-keu-stat-value money">Rp <?= number_format((float)$statPengeluaran, 0, ',', '.') ?></div>
            <div class="ult-keu-stat-sub"><i class="bi bi-graph-down"></i> Approved only</div>
        </div>
    </div>
    <div class="ult-keu-stat" style="--ult-color:<?= $saldoBersih >= 0 ? '#10b981' : '#ef4444' ?>;">
        <div class="ult-keu-stat-icon" style="background:linear-gradient(135deg,<?= $saldoBersih >= 0 ? '#10b981,#059669' : '#ef4444,#dc2626' ?>);"><i class="bi bi-<?= $saldoBersih >= 0 ? 'piggy-bank-fill' : 'exclamation-triangle-fill' ?>"></i></div>
        <div class="ult-keu-stat-info">
            <div class="ult-keu-stat-label">Saldo Bersih</div>
            <div class="ult-keu-stat-value money" style="color:<?= $saldoBersih >= 0 ? '#10b981' : '#ef4444' ?>;">Rp <?= number_format($saldoBersih, 0, ',', '.') ?></div>
            <div class="ult-keu-stat-sub"><i class="bi bi-<?= $statPending > 0 ? 'hourglass-split' : 'check-circle-fill' ?>"></i> <?= $statPending > 0 ? $statPending . ' pending' : 'Semua selesai' ?></div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Transaksi Keuangan</h1>
        <p class="page-subtitle">Pencatatan pemasukan & pengeluaran</p>
    </div>
    <div class="ult-keu-header-actions">
        <a href="<?= url('admin/keuangan/export') ?>" class="btn btn-secondary"><i class="bi bi-file-earmark-spreadsheet"></i> Export CSV</a>
        <div class="ult-keu-view-toggle" id="ultKeuViewToggle">
            <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
            </button>
            <button type="button" class="<?= $currentView === 'grid' ? 'active' : '' ?>" data-view="grid" title="Tampilan Kartu">
                <i class="bi bi-grid-3x3-gap"></i> <span class="d-none-mobile">Kartu</span>
            </button>
        </div>
        <a href="<?= url('admin/keuangan/tambah') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Input Transaksi</a>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-keu-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-wallet2"></i> Daftar Transaksi</h3>
                    <span class="pgn-result-info">
                        Menampilkan <strong><?= $fromRow ?>-<?= $toRow ?></strong> dari <strong><?= number_format($pagination['total']) ?></strong> transaksi
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Toolbar -->
        <form method="GET" action="<?= url('admin/keuangan') ?>" class="pgn-toolbar" style="padding:1rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="toolbar-search" style="flex:2;min-width:220px;">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($pagination['search']) ?>" placeholder="Cari no. transaksi atau deskripsi...">
            </div>
            
            <!-- Custom Tipe Dropdown -->
            <div class="ult-keu-dropdown" data-dropdown>
                <input type="hidden" name="type" id="ultKeuTypeValue" value="<?= htmlspecialchars($pagination['type']) ?>">
                <button type="button" class="ult-keu-dropdown-trigger <?= $pagination['type'] !== '' ? 'has-value' : '' ?>" id="ultKeuTypeTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;">
                        <?php if ($pagination['type'] !== '' && isset($typeMap[$pagination['type']])): 
                            [$cls, $lbl, $icn, $clr] = $typeMap[$pagination['type']]; ?>
                            <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i>
                            <span><?= $lbl ?></span>
                        <?php else: ?>
                            <i class="bi bi-funnel" style="color:var(--admin-primary);"></i>
                            <span>Semua Tipe</span>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-keu-dropdown-menu" style="min-width:180px;">
                    <div class="ult-keu-dd-item <?= $pagination['type'] === '' ? 'selected' : '' ?>" data-value="">
                        <i class="bi bi-funnel" style="color:var(--admin-text-muted);"></i><span>Semua Tipe</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-keu-dd-divider"></div>
                    <?php foreach ($typeMap as $key => [$cls, $lbl, $icn, $clr]): ?>
                    <div class="ult-keu-dd-item <?= $pagination['type'] === $key ? 'selected' : '' ?>" data-value="<?= $key ?>">
                        <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i><span><?= $lbl ?></span><i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Custom Status Dropdown -->
            <div class="ult-keu-dropdown" data-dropdown>
                <input type="hidden" name="status" id="ultKeuStatusValue" value="<?= htmlspecialchars($pagination['status']) ?>">
                <button type="button" class="ult-keu-dropdown-trigger <?= $pagination['status'] !== '' ? 'has-value' : '' ?>" id="ultKeuStatusTrigger">
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
                <div class="ult-keu-dropdown-menu" style="min-width:180px;">
                    <div class="ult-keu-dd-item <?= $pagination['status'] === '' ? 'selected' : '' ?>" data-value="">
                        <i class="bi bi-funnel" style="color:var(--admin-text-muted);"></i><span>Semua Status</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-keu-dd-divider"></div>
                    <?php foreach ($statusMap as $key => [$cls, $lbl, $icn, $clr]): ?>
                    <div class="ult-keu-dd-item <?= $pagination['status'] === $key ? 'selected' : '' ?>" data-value="<?= $key ?>">
                        <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i><span><?= $lbl ?></span><i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Terapkan</button>
            <?php if ($pagination['search'] !== '' || $pagination['type'] !== '' || $pagination['status'] !== ''): ?>
                <a href="<?= url('admin/keuangan') ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
            <?php endif; ?>
        </form>
        
        <!-- Filter Chips -->
        <?php if ($pagination['search'] !== '' || $pagination['type'] !== '' || $pagination['status'] !== ''): ?>
        <div class="ult-keu-chips">
            <span class="ult-keu-chips-label"><i class="bi bi-funnel-fill"></i> Filter Aktif:</span>
            <?php if ($pagination['search'] !== ''): ?>
                <span class="ult-keu-chip">
                    <i class="bi bi-search"></i> Pencarian: "<strong><?= htmlspecialchars($pagination['search']) ?></strong>"
                    <a href="<?= $query(['q' => '', 'page' => 1]) ?>" class="ult-keu-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($pagination['type'] !== '' && isset($typeMap[$pagination['type']])): ?>
                <span class="ult-keu-chip">
                    <i class="bi <?= $typeMap[$pagination['type']][2] ?>"></i> Tipe: <strong><?= $typeMap[$pagination['type']][1] ?></strong>
                    <a href="<?= $query(['type' => '', 'page' => 1]) ?>" class="ult-keu-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($pagination['status'] !== '' && isset($statusMap[$pagination['status']])): ?>
                <span class="ult-keu-chip">
                    <i class="bi <?= $statusMap[$pagination['status']][2] ?>"></i> Status: <strong><?= $statusMap[$pagination['status']][1] ?></strong>
                    <a href="<?= $query(['status' => '', 'page' => 1]) ?>" class="ult-keu-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <a href="<?= url('admin/keuangan') ?>" class="ult-keu-chips-clear"><i class="bi bi-x-circle"></i> Hapus Semua</a>
        </div>
        <?php endif; ?>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultKeuTableView" class="ult-keu-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width:200px;">No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th style="width:160px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($transaksi as $t):
                    [$typeBadge, $typeLabel, $typeIcon, $typeColor] = $typeMap[$t['type']] ?? ['badge-info', $t['type'], 'bi-circle-fill', '#64748b'];
                    [$statusBadge, $statusLabel, $statusIcon, $statusColor] = $statusMap[$t['status']] ?? ['badge-info', $t['status'], 'bi-circle-fill', '#64748b'];
                    $catInfo = $catMap[$t['category_id'] ?? 0] ?? null;
                ?>
                    <tr>
                        <td>
                            <div class="ult-keu-cell">
                                <div class="ult-keu-trx-icon" style="background:linear-gradient(135deg,<?= $typeColor ?>,<?= $typeColor ?>cc);">
                                    <i class="bi <?= $typeIcon ?>"></i>
                                </div>
                                <div class="ult-keu-trx-info">
                                    <div class="ult-keu-trx-number"><?= htmlspecialchars($t['transaction_number']) ?></div>
                                    <div class="ult-keu-trx-desc"><?= htmlspecialchars(mb_substr($t['description'], 0, 50)) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="ult-keu-date">
                                <span class="ult-keu-date-main"><?= date('d M Y', strtotime($t['transaction_date'])) ?></span>
                                <span class="ult-keu-date-sub"><i class="bi bi-clock"></i> <?= date('H:i', strtotime($t['created_at'])) ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="ult-keu-status" style="background:<?= $typeColor ?>20;color:<?= $typeColor ?>;">
                                <i class="bi <?= $typeIcon ?>"></i> <?= $typeLabel ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($catInfo): ?>
                            <span class="ult-keu-category"><i class="bi bi-bookmark-fill"></i> <?= htmlspecialchars($catInfo['name']) ?></span>
                            <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                        </td>
                        <td>
                            <span class="ult-keu-amount <?= $t['type'] === 'pemasukan' ? 'income' : 'expense' ?>">
                                <?= $t['type'] === 'pemasukan' ? '+' : '-' ?> Rp <?= number_format((float)$t['amount'], 0, ',', '.') ?>
                            </span>
                        </td>
                        <td>
                            <span class="ult-keu-status" style="background:<?= $statusColor ?>20;color:<?= $statusColor ?>;">
                                <i class="bi <?= $statusIcon ?>"></i> <?= $statusLabel ?>
                            </span>
                        </td>
                        <td>
                            <div class="ult-keu-row-actions">
                                <?php if ($t['status'] === 'pending' && $canApprove): ?>
                                <a href="<?= url('admin/keuangan/approve/' . $t['id']) ?>" class="ult-keu-row-btn approve" title="Approve"
                                   onclick="return confirm('Setujui transaksi <?= htmlspecialchars(addslashes($t['transaction_number'])) ?>?');"><i class="bi bi-check-circle-fill"></i></a>
                                <a href="<?= url('admin/keuangan/reject/' . $t['id']) ?>?reason=Tidak+memenuhi+persyaratan" class="ult-keu-row-btn reject" title="Reject"
                                   onclick="return confirm('Tolak transaksi <?= htmlspecialchars(addslashes($t['transaction_number'])) ?>?');"><i class="bi bi-x-circle-fill"></i></a>
                                <?php endif; ?>
                                
                                <div class="ult-keu-dropdown" data-dropdown>
                                    <button type="button" class="ult-keu-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="ult-keu-dropdown-menu right ult-keu-row-menu">
                                        <div class="ult-keu-dd-header">Transaksi</div>
                                        <a href="#" class="ult-keu-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Detail transaksi segera hadir','info');return false;">
                                            <i class="bi bi-eye-fill"></i><span>Lihat Detail</span>
                                        </a>
                                        <a href="<?= url('admin/keuangan/edit/' . $t['id']) ?>" class="ult-keu-dd-item">
                                            <i class="bi bi-pencil-square"></i><span>Edit Transaksi</span>
                                        </a>
                                        <?php if ($t['status'] === 'pending' && $canApprove): ?>
                                        <div class="ult-keu-dd-divider"></div>
                                        <div class="ult-keu-dd-header">Approval</div>
                                        <a href="<?= url('admin/keuangan/approve/' . $t['id']) ?>" class="ult-keu-dd-item success" onclick="return confirm('Setujui transaksi ini?');">
                                            <i class="bi bi-check-circle-fill"></i><span>Approve Transaksi</span>
                                        </a>
                                        <a href="<?= url('admin/keuangan/reject/' . $t['id']) ?>?reason=Tidak+memenuhi+persyaratan" class="ult-keu-dd-item danger" onclick="return confirm('Tolak transaksi ini?');">
                                            <i class="bi bi-x-circle-fill"></i><span>Reject Transaksi</span>
                                        </a>
                                        <?php endif; ?>
                                        <div class="ult-keu-dd-divider"></div>
                                        <div class="ult-keu-dd-header">Dokumen</div>
                                        <?php if (!empty($t['attachment'])): ?>
                                        <a href="<?= url('uploads/keuangan/' . $t['attachment']) ?>" target="_blank" class="ult-keu-dd-item">
                                            <i class="bi bi-paperclip"></i><span>Lihat Lampiran</span>
                                        </a>
                                        <?php endif; ?>
                                        <a href="#" class="ult-keu-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Cetak bukti segera hadir','info');return false;">
                                            <i class="bi bi-printer-fill"></i><span>Cetak Bukti</span>
                                        </a>
                                        <?php if ($canApprove): ?>
                                        <div class="ult-keu-dd-divider"></div>
                                        <a href="<?= url('admin/keuangan/hapus/' . $t['id']) ?>" class="ult-keu-dd-item danger" onclick="return confirm('Pindahkan transaksi <?= htmlspecialchars(addslashes($t['transaction_number'])) ?> ke sampah?');">
                                            <i class="bi bi-trash3-fill"></i><span>Pindahkan ke Sampah</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$transaksi): ?>
                    <tr><td colspan="7">
                        <div class="ult-keu-empty">
                            <div class="ult-keu-empty-icon"><i class="bi bi-wallet2"></i></div>
                            <h3>Tidak Ada Transaksi</h3>
                            <p>
                                <?php if ($pagination['search'] !== '' || $pagination['type'] !== '' || $pagination['status'] !== ''): ?>
                                    Tidak ada transaksi yang cocok dengan filter Anda. Coba ubah kriteria pencarian.
                                <?php else: ?>
                                    Belum ada transaksi dalam sistem. Mulai catat transaksi keuangan pertama Anda!
                                <?php endif; ?>
                            </p>
                            <div style="display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;">
                                <?php if ($pagination['search'] !== '' || $pagination['type'] !== '' || $pagination['status'] !== ''): ?>
                                    <a href="<?= url('admin/keuangan') ?>" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset Filter</a>
                                <?php endif; ?>
                                <a href="<?= url('admin/keuangan/tambah') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Input Transaksi Pertama</a>
                            </div>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== GRID VIEW ===== -->
        <div id="ultKeuGridView" class="ult-keu-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php foreach ($transaksi as $t):
                [$typeBadge, $typeLabel, $typeIcon, $typeColor] = $typeMap[$t['type']] ?? ['badge-info', $t['type'], 'bi-circle-fill', '#64748b'];
                [$statusBadge, $statusLabel, $statusIcon, $statusColor] = $statusMap[$t['status']] ?? ['badge-info', $t['status'], 'bi-circle-fill', '#64748b'];
                $catInfo = $catMap[$t['category_id'] ?? 0] ?? null;
            ?>
            <div class="ult-keu-card">
                <div class="ult-keu-card-accent <?= $t['type'] === 'pemasukan' ? 'income' : 'expense' ?>"></div>
                <div class="ult-keu-card-actions">
                    <div class="ult-keu-dropdown" data-dropdown>
                        <button type="button" class="ult-keu-row-btn" title="Aksi" style="background:var(--admin-surface);"><i class="bi bi-three-dots-vertical"></i></button>
                        <div class="ult-keu-dropdown-menu right ult-keu-row-menu">
                            <a href="<?= url('admin/keuangan/edit/' . $t['id']) ?>" class="ult-keu-dd-item"><i class="bi bi-pencil-square"></i> Edit</a>
                            <?php if ($t['status'] === 'pending' && $canApprove): ?>
                            <a href="<?= url('admin/keuangan/approve/' . $t['id']) ?>" class="ult-keu-dd-item success"><i class="bi bi-check-circle-fill"></i> Approve</a>
                            <a href="<?= url('admin/keuangan/reject/' . $t['id']) ?>" class="ult-keu-dd-item danger"><i class="bi bi-x-circle-fill"></i> Reject</a>
                            <?php endif; ?>
                            <?php if ($canApprove): ?>
                            <a href="<?= url('admin/keuangan/hapus/' . $t['id']) ?>" class="ult-keu-dd-item danger" onclick="return confirm('Hapus?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="ult-keu-card-body">
                    <div class="ult-keu-card-header">
                        <div class="ult-keu-card-icon" style="background:linear-gradient(135deg,<?= $typeColor ?>,<?= $typeColor ?>cc);">
                            <i class="bi <?= $typeIcon ?>"></i>
                        </div>
                        <div class="ult-keu-card-status">
                            <span class="ult-keu-status" style="background:<?= $statusColor ?>20;color:<?= $statusColor ?>;font-size:.7rem;">
                                <i class="bi <?= $statusIcon ?>"></i> <?= $statusLabel ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="ult-keu-card-trx"><?= htmlspecialchars($t['transaction_number']) ?></div>
                    <div class="ult-keu-card-title"><?= htmlspecialchars($t['description']) ?></div>
                    
                    <div class="ult-keu-card-meta">
                        <?php if ($catInfo): ?><span><i class="bi bi-bookmark-fill"></i> <?= htmlspecialchars($catInfo['name']) ?></span><?php endif; ?>
                        <span><i class="bi bi-calendar-event"></i> <?= date('d M Y', strtotime($t['transaction_date'])) ?></span>
                        <?php if (!empty($t['dept_name'])): ?><span><i class="bi bi-building-fill"></i> <?= htmlspecialchars($t['dept_name']) ?></span><?php endif; ?>
                    </div>
                    
                    <div class="ult-keu-card-amount <?= $t['type'] === 'pemasukan' ? 'income' : 'expense' ?>">
                        <?= $t['type'] === 'pemasukan' ? '+' : '-' ?> Rp <?= number_format((float)$t['amount'], 0, ',', '.') ?>
                    </div>
                    
                    <div class="ult-keu-card-footer">
                        <?php if ($t['status'] === 'pending' && $canApprove): ?>
                        <a href="<?= url('admin/keuangan/approve/' . $t['id']) ?>" class="ult-keu-card-btn approve" onclick="return confirm('Approve?');"><i class="bi bi-check-circle-fill"></i> Approve</a>
                        <a href="<?= url('admin/keuangan/reject/' . $t['id']) ?>" class="ult-keu-card-btn reject" onclick="return confirm('Reject?');"><i class="bi bi-x-circle-fill"></i> Reject</a>
                        <?php else: ?>
                        <a href="<?= url('admin/keuangan/edit/' . $t['id']) ?>" class="ult-keu-card-btn"><i class="bi bi-pencil-fill"></i> Edit</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- ===== PAGINATION ===== -->
        <?php if ($pagination['totalPages'] > 1): ?>
        <div class="ult-keu-pagination">
            <div class="ult-keu-page-info">
                Halaman <strong><?= $pagination['page'] ?></strong> dari <strong><?= $pagination['totalPages'] ?></strong>
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
        </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function(){
    'use strict';
    
    // ===== CUSTOM DROPDOWN =====
    document.querySelectorAll('.ult-keu-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-keu-dropdown-trigger, .ult-keu-row-btn');
        const menu = dd.querySelector('.ult-keu-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-keu-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
            if(dd.classList.contains('open')){
                const s = menu.querySelector('[data-search]');
                if(s) setTimeout(() => s.focus(), 50);
            }
        });
        menu.querySelectorAll('.ult-keu-dd-item[data-value]').forEach(function(item){
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
                menu.querySelectorAll('.ult-keu-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' && val !== '0' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
                if(hidden && (hidden.name === 'type' || hidden.name === 'status')){
                    const form = dd.closest('form');
                    if(form) form.submit();
                }
            });
        });
        const s = menu.querySelector('[data-search]');
        if(s){
            s.addEventListener('input', function(){
                const q = this.value.toLowerCase();
                menu.querySelectorAll('.ult-keu-dd-item').forEach(i => {
                    if(i.hasAttribute('data-value')) i.style.display = i.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-keu-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultKeuViewToggle');
    const tv = document.getElementById('ultKeuTableView');
    const gv = document.getElementById('ultKeuGridView');
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
                try { localStorage.setItem('ult_keu_view', v); } catch(e){}
            });
        });
    }
})();
</script>