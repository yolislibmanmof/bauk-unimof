<?php
use App\Helpers\Auth;
$canDelete = Auth::hasRole(['super_admin', 'admin_bauk', 'admin_aset']);

// ===== QUERY BUILDER =====
$query = function (array $override = []) use ($pagination) {
    $params = [
        'q' => $pagination['search'], 'condition' => $pagination['condition'],
        'cat' => $pagination['cat'], 'page' => $pagination['page'],
    ];
    $params = array_filter(array_merge($params, $override), static fn($v) => $v !== '' && $v !== null && $v !== 0);
    return url('admin/aset') . ($params ? '?' . http_build_query($params) : '');
};

// ===== CONDITION MAP =====
$conditionMap = [
    'baik'          => ['badge-success', 'Baik', 'bi-check-circle-fill', '#10b981'],
    'rusak_ringan'  => ['badge-warning', 'Rusak Ringan', 'bi-exclamation-triangle-fill', '#f59e0b'],
    'rusak_berat'   => ['badge-danger', 'Rusak Berat', 'bi-x-circle-fill', '#ef4444'],
    'hilang'        => ['badge-info', 'Hilang', 'bi-question-circle-fill', '#3b82f6'],
    'dihapuskan'    => ['badge-secondary', 'Dihapuskan', 'bi-trash-fill', '#64748b'],
];

// ===== CATEGORY MAP =====
$catMap = [];
foreach ($categories as $c) $catMap[$c['id']] = $c['name'];

// ===== STATS =====
$db = \App\Helpers\Database::getInstance();
try {
    $statTotal   = $db->count('aset', 'deleted_at IS NULL');
    $statBaik    = $db->count('aset', "`condition` = 'baik' AND deleted_at IS NULL");
    $statRusak   = $db->count('aset', "`condition` IN ('rusak_ringan','rusak_berat') AND deleted_at IS NULL");
    $statHilang  = $db->count('aset', "`condition` = 'hilang' AND deleted_at IS NULL");
    $totalValue  = $db->fetch("SELECT COALESCE(SUM(purchase_price),0) AS t FROM aset WHERE deleted_at IS NULL")['t'] ?? 0;
} catch (\Throwable $e) {
    $statTotal = $statBaik = $statRusak = $statHilang = 0;
    $totalValue = 0;
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
.ult-aset-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-aset-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-aset-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#10b981);}
.ult-aset-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-aset-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-aset-stat-info{flex:1;min-width:0;}
.ult-aset-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-aset-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-aset-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== CARD HEADER ACTIONS ===== */
.ult-aset-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-aset-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-aset-dropdown{position:relative;display:inline-block;}
.ult-aset-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:150px;justify-content:space-between;}
.ult-aset-dropdown-trigger:hover{border-color:var(--admin-success);background:var(--admin-bg);}
.ult-aset-dropdown-trigger.has-value{border-color:var(--admin-success);background:rgba(16,185,129,.05);}
.ult-aset-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-aset-dropdown.open .ult-aset-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-aset-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:300px;overflow-y:auto;}
.ult-aset-dropdown.open .ult-aset-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-aset-dropdown-menu.right{left:auto;right:0;}
.ult-aset-dd-search{padding:.625rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);}
.ult-aset-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;font-size:.825rem;background:var(--admin-bg);}
.ult-aset-dd-search input:focus{outline:none;border-color:var(--admin-success);}
.ult-aset-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-aset-dd-item:last-child{border-bottom:none;}
.ult-aset-dd-item:hover{background:var(--admin-bg);}
.ult-aset-dd-item.selected{background:rgba(16,185,129,.08);color:var(--admin-success);font-weight:600;}
.ult-aset-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-success);opacity:0;}
.ult-aset-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-aset-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-aset-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== FILTER CHIPS ===== */
.ult-aset-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);align-items:center;}
.ult-aset-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-aset-chip i{font-size:.7rem;}
.ult-aset-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-aset-chip-close:hover{color:var(--admin-danger);}
.ult-aset-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;}
.ult-aset-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== VIEW TOGGLE ===== */
.ult-aset-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-aset-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-aset-view-toggle button.active{background:var(--admin-success);color:#fff;}
.ult-aset-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-aset-table-wrap{overflow-x:auto;}
.ult-aset-cell{display:flex;align-items:center;gap:.75rem;min-width:220px;}
.ult-aset-thumb{width:44px;height:44px;border-radius:var(--radius-md);object-fit:cover;flex-shrink:0;border:1px solid var(--admin-border);background:var(--admin-bg);}
.ult-aset-thumb-placeholder{width:44px;height:44px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--admin-text-muted);flex-shrink:0;background:linear-gradient(135deg,#d1fae5,#a7f3d0);}
.ult-aset-info{flex:1;min-width:0;}
.ult-aset-name{font-weight:700;color:var(--admin-text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-asset-code{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:6px;font-size:.78rem;font-weight:700;color:var(--admin-text);font-family:monospace;}
.ult-asset-dept{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:var(--admin-bg);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text-secondary);}
.ult-asset-dept i{font-size:.7rem;color:var(--admin-success);}
.ult-asset-value{font-weight:700;color:var(--admin-success);font-size:.875rem;}
.ult-asset-condition{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}

/* ===== ROW ACTION ===== */
.ult-aset-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-aset-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-aset-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-aset-row-btn.view:hover{color:var(--admin-info);background:rgba(59,130,246,.08);}
.ult-aset-row-btn.edit:hover{color:#f59e0b;background:rgba(245,158,11,.08);}
.ult-aset-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-aset-row-menu{min-width:200px;}
.ult-aset-row-menu .ult-aset-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-aset-row-menu .ult-aset-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-aset-row-menu .ult-aset-dd-item.danger{color:var(--admin-danger);}
.ult-aset-row-menu .ult-aset-dd-item.danger:hover{background:rgba(239,68,68,.08);}

/* ===== GRID VIEW ===== */
.ult-aset-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;padding:1.5rem;}
.ult-aset-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;transition:all .3s;position:relative;}
.ult-asset-card:hover{transform:translateY(-4px);box-shadow:0 12px 35px rgba(0,0,0,.1);border-color:rgba(16,185,129,.3);}
.ult-asset-card-image{aspect-ratio:16/10;background:linear-gradient(135deg,#d1fae5,#a7f3d0);position:relative;overflow:hidden;}
.ult-asset-card-image img{width:100%;height:100%;object-fit:cover;}
.ult-asset-card-image-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--admin-success);opacity:.3;}
.ult-asset-card-badge{position:absolute;top:.75rem;right:.75rem;}
.ult-asset-card-body{padding:1.25rem;}
.ult-asset-card-code{font-family:monospace;font-size:.75rem;font-weight:700;color:var(--admin-success);margin-bottom:.25rem;}
.ult-asset-card-title{font-size:1rem;font-weight:700;color:var(--admin-text);margin-bottom:.5rem;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-asset-card-meta{display:flex;flex-direction:column;gap:.375rem;font-size:.8rem;color:var(--admin-text-secondary);}
.ult-asset-card-meta span{display:flex;align-items:center;gap:.375rem;}
.ult-asset-card-meta i{font-size:.7rem;color:var(--admin-success);}
.ult-asset-card-footer{display:flex;gap:.375rem;padding:1rem 1.25rem;border-top:1px solid var(--admin-border);}
.ult-asset-card-btn{flex:1;padding:.5rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:6px;font-size:.78rem;font-weight:600;color:var(--admin-text);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.375rem;transition:all .2s;text-decoration:none!important;}
.ult-asset-card-btn:hover{background:var(--admin-success);color:#fff!important;border-color:var(--admin-success);}
.ult-asset-card-btn.danger:hover{background:var(--admin-danger);border-color:var(--admin-danger);}
.ult-asset-card-actions{position:absolute;top:.75rem;left:.75rem;}

/* ===== EMPTY STATE ===== */
.ult-aset-empty{text-align:center;padding:4rem 2rem;}
.ult-aset-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:var(--admin-success);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-aset-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(16,185,129,.3);border-radius:50%;animation:ult-aset-spin 20s linear infinite;}
@keyframes ult-aset-spin{to{transform:rotate(360deg);}}
.ult-aset-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-aset-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== PAGINATION ===== */
.ult-aset-pagination{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;border-top:1px solid var(--admin-border);flex-wrap:wrap;gap:1rem;}
.ult-aset-page-info{font-size:.85rem;color:var(--admin-text-secondary);}
.ult-aset-page-info strong{color:var(--admin-text);}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-aset-stats{grid-template-columns:repeat(2,1fr);}
    .ult-aset-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-aset-stats{grid-template-columns:1fr;}
    .ult-aset-grid{grid-template-columns:1fr;padding:1rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-aset-stats">
    <div class="ult-aset-stat" style="--ult-color:#10b981;">
        <div class="ult-aset-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-box-seam-fill"></i></div>
        <div class="ult-aset-stat-info">
            <div class="ult-aset-stat-label">Total Aset</div>
            <div class="ult-aset-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-aset-stat-sub"><i class="bi bi-database-fill"></i> Keseluruhan</div>
        </div>
    </div>
    <div class="ult-aset-stat" style="--ult-color:#10b981;">
        <div class="ult-aset-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-check-circle-fill"></i></div>
        <div class="ult-aset-stat-info">
            <div class="ult-aset-stat-label">Kondisi Baik</div>
            <div class="ult-aset-stat-value"><?= number_format($statBaik) ?></div>
            <div class="ult-aset-stat-sub"><i class="bi bi-arrow-up-short"></i> <?= $statTotal > 0 ? round(($statBaik / $statTotal) * 100) : 0 ?>% dari total</div>
        </div>
    </div>
    <div class="ult-aset-stat" style="--ult-color:#f59e0b;">
        <div class="ult-aset-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="ult-aset-stat-info">
            <div class="ult-aset-stat-label">Perlu Perhatian</div>
            <div class="ult-aset-stat-value"><?= number_format($statRusak) ?></div>
            <div class="ult-aset-stat-sub"><i class="bi bi-tools"></i> Rusak ringan/berat</div>
        </div>
    </div>
    <div class="ult-aset-stat" style="--ult-color:#8b5cf6;">
        <div class="ult-aset-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-cash-stack"></i></div>
        <div class="ult-aset-stat-info">
            <div class="ult-aset-stat-label">Total Nilai Aset</div>
            <div class="ult-aset-stat-value" style="font-size:1.25rem;">Rp <?= number_format((float)$totalValue, 0, ',', '.') ?></div>
            <div class="ult-aset-stat-sub"><i class="bi bi-graph-up"></i> Nilai perolehan</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Manajemen Aset</h1>
        <p class="page-subtitle">Inventaris & sarana prasarana kampus</p>
    </div>
    <div class="ult-aset-header-actions">
        <a href="<?= url('admin/aset/trash') ?>" class="btn btn-secondary">
            <i class="bi bi-trash3"></i> Sampah <?= $trashCount > 0 ? '<span class="badge badge-danger">' . $trashCount . '</span>' : '' ?>
        </a>
        <a href="<?= url('admin/aset/tambah') ?>" class="btn btn-primary" style="background:var(--admin-success);border-color:var(--admin-success);">
            <i class="bi bi-plus-lg"></i> Tambah Aset
        </a>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-aset-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-box-seam"></i> Daftar Aset</h3>
                    <span class="pgn-result-info">
                        Menampilkan <strong><?= $fromRow ?>-<?= $toRow ?></strong> dari <strong><?= number_format($pagination['total']) ?></strong> aset
                    </span>
                </div>
                <div class="ult-aset-header-actions">
                    <div class="ult-aset-view-toggle" id="ultAsetViewToggle">
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
        
        <!-- Toolbar -->
        <form method="GET" action="<?= url('admin/aset') ?>" class="pgn-toolbar" style="padding:1rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="toolbar-search" style="flex:2;min-width:220px;">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($pagination['search']) ?>" placeholder="Cari kode, nama, atau merk aset...">
            </div>
            
            <!-- Custom Kategori Dropdown -->
            <div class="ult-aset-dropdown" data-dropdown>
                <input type="hidden" name="cat" id="ultAsetCatValue" value="<?= (int)$pagination['cat'] ?>">
                <button type="button" class="ult-aset-dropdown-trigger <?= (int)$pagination['cat'] > 0 ? 'has-value' : '' ?>" id="ultAsetCatTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                        <i class="bi bi-bookmark" style="color:var(--admin-success);"></i>
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= (int)$pagination['cat'] > 0 ? htmlspecialchars($catMap[$pagination['cat']] ?? 'Pilih Kategori') : 'Semua Kategori' ?>
                        </span>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-aset-dropdown-menu" style="min-width:240px;">
                    <div class="ult-aset-dd-search"><input type="text" placeholder="Cari kategori..." data-search></div>
                    <div class="ult-aset-dd-item <?= (int)$pagination['cat'] === 0 ? 'selected' : '' ?>" data-value="0">
                        <i class="bi bi-grid-3x3-gap" style="color:var(--admin-text-muted);"></i><span>Semua Kategori</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-aset-dd-divider"></div>
                    <?php foreach ($categories as $c): ?>
                    <div class="ult-aset-dd-item <?= (int)$pagination['cat'] === (int)$c['id'] ? 'selected' : '' ?>" data-value="<?= $c['id'] ?>" data-label="<?= htmlspecialchars($c['name']) ?>">
                        <i class="bi bi-bookmark-fill" style="color:var(--admin-success);"></i><span><?= htmlspecialchars($c['name']) ?></span><i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Custom Kondisi Dropdown -->
            <div class="ult-aset-dropdown" data-dropdown>
                <input type="hidden" name="condition" id="ultAsetCondValue" value="<?= htmlspecialchars($pagination['condition']) ?>">
                <button type="button" class="ult-aset-dropdown-trigger <?= $pagination['condition'] !== '' ? 'has-value' : '' ?>" id="ultAsetCondTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;">
                        <?php if ($pagination['condition'] !== '' && isset($conditionMap[$pagination['condition']])): 
                            [$cls, $lbl, $icn, $clr] = $conditionMap[$pagination['condition']]; ?>
                            <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i>
                            <span><?= $lbl ?></span>
                        <?php else: ?>
                            <i class="bi bi-funnel" style="color:var(--admin-success);"></i>
                            <span>Semua Kondisi</span>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-aset-dropdown-menu" style="min-width:200px;">
                    <div class="ult-aset-dd-item <?= $pagination['condition'] === '' ? 'selected' : '' ?>" data-value="">
                        <i class="bi bi-funnel" style="color:var(--admin-text-muted);"></i><span>Semua Kondisi</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-aset-dd-divider"></div>
                    <?php foreach ($conditionMap as $key => [$cls, $lbl, $icn, $clr]): ?>
                    <div class="ult-aset-dd-item <?= $pagination['condition'] === $key ? 'selected' : '' ?>" data-value="<?= $key ?>">
                        <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i><span><?= $lbl ?></span><i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="background:var(--admin-success);border-color:var(--admin-success);"><i class="bi bi-funnel-fill"></i> Terapkan</button>
            <?php if ($pagination['search'] !== '' || $pagination['condition'] !== '' || $pagination['cat'] != 0): ?>
                <a href="<?= url('admin/aset') ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
            <?php endif; ?>
        </form>
        
        <!-- Filter Chips -->
        <?php if ($pagination['search'] !== '' || $pagination['condition'] !== '' || (int)$pagination['cat'] > 0): ?>
        <div class="ult-aset-chips">
            <span class="ult-aset-chips-label"><i class="bi bi-funnel-fill"></i> Filter Aktif:</span>
            <?php if ($pagination['search'] !== ''): ?>
                <span class="ult-aset-chip">
                    <i class="bi bi-search"></i> Pencarian: "<strong><?= htmlspecialchars($pagination['search']) ?></strong>"
                    <a href="<?= $query(['q' => '', 'page' => 1]) ?>" class="ult-aset-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ((int)$pagination['cat'] > 0): ?>
                <span class="ult-aset-chip">
                    <i class="bi bi-bookmark-fill"></i> Kategori: <strong><?= htmlspecialchars($catMap[$pagination['cat']] ?? '-') ?></strong>
                    <a href="<?= $query(['cat' => 0, 'page' => 1]) ?>" class="ult-aset-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($pagination['condition'] !== '' && isset($conditionMap[$pagination['condition']])): ?>
                <span class="ult-aset-chip">
                    <i class="bi <?= $conditionMap[$pagination['condition']][2] ?>"></i> Kondisi: <strong><?= $conditionMap[$pagination['condition']][1] ?></strong>
                    <a href="<?= $query(['condition' => '', 'page' => 1]) ?>" class="ult-aset-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <a href="<?= url('admin/aset') ?>" class="ult-aset-chips-clear"><i class="bi bi-x-circle"></i> Hapus Semua</a>
        </div>
        <?php endif; ?>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultAsetTableView" class="ult-aset-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode Aset</th>
                        <th>Nama Aset</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Kondisi</th>
                        <th style="width:140px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($aset as $a):
                    [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $conditionMap[$a['condition']] ?? ['badge-info', $a['condition'], 'bi-circle-fill', '#64748b'];
                    $photoUrl = !empty($a['photo']) ? url('uploads/aset/' . $a['photo']) : null;
                ?>
                    <tr>
                        <td><span class="ult-asset-code"><i class="bi bi-hash"></i> <?= htmlspecialchars($a['asset_code']) ?></span></td>
                        <td>
                            <div class="ult-aset-cell">
                                <?php if ($photoUrl): ?>
                                    <img src="<?= $photoUrl ?>" alt="" class="ult-aset-thumb">
                                <?php else: ?>
                                    <div class="ult-aset-thumb-placeholder"><i class="bi bi-box-seam-fill"></i></div>
                                <?php endif; ?>
                                <div class="ult-aset-info">
                                    <div class="ult-aset-name"><?= htmlspecialchars($a['name']) ?></div>
                                    <?php if (!empty($a['brand'])): ?>
                                    <div style="font-size:.75rem;color:var(--admin-text-muted);"><?= htmlspecialchars($a['brand']) ?><?= !empty($a['model']) ? ' • ' . htmlspecialchars($a['model']) : '' ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($a['category_name'])): ?>
                            <span class="ult-asset-dept"><i class="bi bi-bookmark-fill"></i> <?= htmlspecialchars($a['category_name']) ?></span>
                            <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($a['location'] ?? $a['dept_name'] ?? '-') ?></td>
                        <td>
                            <span class="ult-asset-condition" style="background:<?= $badgeColor ?>20;color:<?= $badgeColor ?>;">
                                <i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                            </span>
                        </td>
                        <td>
                            <div class="ult-aset-row-actions">
                                <a href="<?= url('admin/aset/edit/' . $a['id']) ?>" class="ult-aset-row-btn edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                
                                <div class="ult-aset-dropdown" data-dropdown>
                                    <button type="button" class="ult-aset-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="ult-aset-dropdown-menu right ult-aset-row-menu">
                                        <div class="ult-aset-dd-header">Aset</div>
                                        <a href="#" class="ult-aset-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Detail aset segera hadir','info');return false;">
                                            <i class="bi bi-eye-fill"></i><span>Lihat Detail</span>
                                        </a>
                                        <a href="<?= url('admin/aset/edit/' . $a['id']) ?>" class="ult-aset-dd-item">
                                            <i class="bi bi-pencil-square"></i><span>Edit Data</span>
                                        </a>
                                        <div class="ult-aset-dd-divider"></div>
                                        <div class="ult-aset-dd-header">Pemeliharaan</div>
                                        <a href="#" class="ult-aset-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Riwayat maintenance segera hadir','info');return false;">
                                            <i class="bi bi-tools"></i><span>Riwayat Maintenance</span>
                                        </a>
                                        <a href="#" class="ult-aset-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Tambah maintenance segera hadir','info');return false;">
                                            <i class="bi bi-plus-circle"></i><span>Catat Perbaikan</span>
                                        </a>
                                        <div class="ult-aset-dd-divider"></div>
                                        <div class="ult-aset-dd-header">Dokumen</div>
                                        <a href="#" class="ult-aset-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Cetak label QR segera hadir','info');return false;">
                                            <i class="bi bi-qr-code"></i><span>Cetak Label QR</span>
                                        </a>
                                        <a href="#" class="ult-aset-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Cetak berita acara segera hadir','info');return false;">
                                            <i class="bi bi-printer"></i><span>Cetak Berita Acara</span>
                                        </a>
                                        <?php if ($canDelete): ?>
                                        <div class="ult-aset-dd-divider"></div>
                                        <a href="<?= url('admin/aset/hapus/' . $a['id']) ?>" class="ult-aset-dd-item danger" onclick="return confirm('Pindahkan aset <?= htmlspecialchars(addslashes($a['name'])) ?> ke tempat sampah?');">
                                            <i class="bi bi-trash3-fill"></i><span>Pindahkan ke Sampah</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$aset): ?>
                    <tr><td colspan="6">
                        <div class="ult-aset-empty">
                            <div class="ult-aset-empty-icon"><i class="bi bi-box-seam"></i></div>
                            <h3>Tidak Ada Data Aset</h3>
                            <p>
                                <?php if ($pagination['search'] !== '' || $pagination['condition'] !== '' || $pagination['cat'] != 0): ?>
                                    Tidak ada aset yang cocok dengan filter Anda. Coba ubah kriteria pencarian.
                                <?php else: ?>
                                    Belum ada data aset dalam sistem. Mulai tambahkan aset untuk memulai inventarisasi.
                                <?php endif; ?>
                            </p>
                            <div style="display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;">
                                <?php if ($pagination['search'] !== '' || $pagination['condition'] !== '' || $pagination['cat'] != 0): ?>
                                    <a href="<?= url('admin/aset') ?>" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset Filter</a>
                                <?php endif; ?>
                                <a href="<?= url('admin/aset/tambah') ?>" class="btn btn-primary" style="background:var(--admin-success);border-color:var(--admin-success);"><i class="bi bi-plus-lg"></i> Tambah Aset Pertama</a>
                            </div>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== GRID VIEW ===== -->
        <div id="ultAsetGridView" class="ult-aset-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php foreach ($aset as $a):
                [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $conditionMap[$a['condition']] ?? ['badge-info', $a['condition'], 'bi-circle-fill', '#64748b'];
                $photoUrl = !empty($a['photo']) ? url('uploads/aset/' . $a['photo']) : null;
            ?>
            <div class="ult-asset-card">
                <div class="ult-asset-card-actions">
                    <div class="ult-aset-dropdown" data-dropdown>
                        <button type="button" class="ult-aset-row-btn" title="Aksi"><i class="bi bi-three-dots-vertical"></i></button>
                        <div class="ult-aset-dropdown-menu right ult-aset-row-menu">
                            <a href="<?= url('admin/aset/edit/' . $a['id']) ?>" class="ult-aset-dd-item"><i class="bi bi-pencil-square"></i> Edit</a>
                            <?php if ($canDelete): ?>
                            <a href="<?= url('admin/aset/hapus/' . $a['id']) ?>" class="ult-aset-dd-item danger" onclick="return confirm('Hapus?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="ult-asset-card-image">
                    <?php if ($photoUrl): ?>
                        <img src="<?= $photoUrl ?>" alt="<?= htmlspecialchars($a['name']) ?>">
                    <?php else: ?>
                        <div class="ult-asset-card-image-placeholder"><i class="bi bi-box-seam-fill"></i></div>
                    <?php endif; ?>
                    <div class="ult-asset-card-badge">
                        <span class="ult-asset-condition" style="background:<?= $badgeColor ?>ee;color:#fff;font-size:.7rem;">
                            <i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                        </span>
                    </div>
                </div>
                
                <div class="ult-asset-card-body">
                    <div class="ult-asset-card-code"><?= htmlspecialchars($a['asset_code']) ?></div>
                    <div class="ult-asset-card-title" title="<?= htmlspecialchars($a['name']) ?>"><?= htmlspecialchars($a['name']) ?></div>
                    <div class="ult-asset-card-meta">
                        <?php if (!empty($a['category_name'])): ?><span><i class="bi bi-bookmark-fill"></i> <?= htmlspecialchars($a['category_name']) ?></span><?php endif; ?>
                        <?php if (!empty($a['brand'])): ?><span><i class="bi bi-upc-scan"></i> <?= htmlspecialchars($a['brand']) ?></span><?php endif; ?>
                        <?php if (!empty($a['location'])): ?><span><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($a['location']) ?></span><?php endif; ?>
                        <?php if ((float)$a['purchase_price'] > 0): ?><span><i class="bi bi-cash-stack"></i> Rp <?= number_format((float)$a['purchase_price'], 0, ',', '.') ?></span><?php endif; ?>
                    </div>
                </div>
                
                <div class="ult-asset-card-footer">
                    <a href="<?= url('admin/aset/edit/' . $a['id']) ?>" class="ult-asset-card-btn"><i class="bi bi-pencil-fill"></i> Edit</a>
                    <a href="#" class="ult-asset-card-btn" onclick="if(typeof showToast!=='undefined')showToast('Detail segera hadir','info');return false;"><i class="bi bi-eye-fill"></i> Detail</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- ===== PAGINATION ===== -->
        <?php if ($pagination['totalPages'] > 1): ?>
        <div class="ult-aset-pagination">
            <div class="ult-aset-page-info">
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
    document.querySelectorAll('.ult-aset-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-aset-dropdown-trigger, .ult-aset-row-btn');
        const menu = dd.querySelector('.ult-aset-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-aset-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
            if(dd.classList.contains('open')){
                const s = menu.querySelector('[data-search]');
                if(s) setTimeout(() => s.focus(), 50);
            }
        });
        menu.querySelectorAll('.ult-aset-dd-item[data-value]').forEach(function(item){
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
                menu.querySelectorAll('.ult-aset-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' && val !== '0' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
                if(hidden && (hidden.name === 'cat' || hidden.name === 'condition')){
                    const form = dd.closest('form');
                    if(form) form.submit();
                }
            });
        });
        const s = menu.querySelector('[data-search]');
        if(s){
            s.addEventListener('input', function(){
                const q = this.value.toLowerCase();
                menu.querySelectorAll('.ult-aset-dd-item').forEach(i => {
                    if(i.hasAttribute('data-value')) i.style.display = i.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-aset-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultAsetViewToggle');
    const tv = document.getElementById('ultAsetTableView');
    const gv = document.getElementById('ultAsetGridView');
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
                try { localStorage.setItem('ult_aset_view', v); } catch(e){}
            });
        });
    }
})();
</script>