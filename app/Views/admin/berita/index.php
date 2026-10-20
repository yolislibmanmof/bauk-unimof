<?php
use App\Helpers\Auth;
$canDelete = Auth::check();

$query = function (array $override = []) use ($pagination) {
    $params = [
        'q' => $pagination['search'], 'status' => $pagination['status'],
        'category' => $pagination['category'], 'page' => $pagination['page'],
    ];
    $params = array_filter(array_merge($params, $override), static fn($v) => $v !== '' && $v !== null && $v !== 0);
    return url('admin/berita') . ($params ? '?' . http_build_query($params) : '');
};

$statusMap = [
    'published' => ['badge-success', 'Published', 'bi-eye-fill', '#10b981'],
    'draft'     => ['badge-warning', 'Draft', 'bi-eye-slash-fill', '#f59e0b'],
];

$categoryMap = [
    'berita'     => ['Berita', 'bi-newspaper', '#1a5276'],
    'pengumuman' => ['Pengumuman', 'bi-megaphone-fill', '#e67e22'],
    'kegiatan'   => ['Kegiatan', 'bi-calendar-event-fill', '#27ae60'],
    'pengadaan'  => ['Pengadaan', 'bi-cart-fill', '#8e44ad'],
    'regulasi'   => ['Regulasi', 'bi-file-earmark-text-fill', '#c0392b'],
];

// ===== STATS =====
$db = \App\Helpers\Database::getInstance();
try {
    $statTotal     = $db->count('berita', 'deleted_at IS NULL');
    $statPublished = $db->count('berita', "is_published = 1 AND deleted_at IS NULL");
    $statDraft     = $db->count('berita', "is_published = 0 AND deleted_at IS NULL");
    $statViews     = $db->fetch("SELECT COALESCE(SUM(views),0) AS t FROM berita WHERE deleted_at IS NULL")['t'] ?? 0;
} catch (\Throwable $e) {
    $statTotal = $statPublished = $statDraft = $statViews = 0;
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
.ult-berita-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-berita-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-berita-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#3b82f6);}
.ult-berita-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-berita-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-berita-stat-info{flex:1;min-width:0;}
.ult-berita-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-berita-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-berita-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-berita-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-berita-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-berita-dropdown{position:relative;display:inline-block;}
.ult-berita-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:150px;justify-content:space-between;}
.ult-berita-dropdown-trigger:hover{border-color:var(--admin-info);background:var(--admin-bg);}
.ult-berita-dropdown-trigger.has-value{border-color:var(--admin-info);background:rgba(59,130,246,.05);}
.ult-berita-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-berita-dropdown.open .ult-berita-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-berita-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:300px;overflow-y:auto;}
.ult-berita-dropdown.open .ult-berita-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-berita-dropdown-menu.right{left:auto;right:0;}
.ult-berita-dd-search{padding:.625rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);}
.ult-berita-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;font-size:.825rem;background:var(--admin-bg);}
.ult-berita-dd-search input:focus{outline:none;border-color:var(--admin-info);}
.ult-berita-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-berita-dd-item:last-child{border-bottom:none;}
.ult-berita-dd-item:hover{background:var(--admin-bg);}
.ult-berita-dd-item.selected{background:rgba(59,130,246,.08);color:var(--admin-info);font-weight:600;}
.ult-berita-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-info);opacity:0;}
.ult-berita-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-berita-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-berita-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== FILTER CHIPS ===== */
.ult-berita-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);align-items:center;}
.ult-berita-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-berita-chip i{font-size:.7rem;}
.ult-berita-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-berita-chip-close:hover{color:var(--admin-danger);}
.ult-berita-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;}
.ult-berita-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== VIEW TOGGLE ===== */
.ult-berita-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-berita-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-berita-view-toggle button.active{background:var(--admin-info);color:#fff;}
.ult-berita-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-berita-table-wrap{overflow-x:auto;}
.ult-berita-cell{display:flex;align-items:center;gap:.75rem;min-width:280px;}
.ult-berita-thumb{width:60px;height:45px;border-radius:6px;object-fit:cover;flex-shrink:0;border:1px solid var(--admin-border);background:var(--admin-bg);}
.ult-berita-thumb-placeholder{width:60px;height:45px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--admin-info);flex-shrink:0;background:linear-gradient(135deg,#dbeafe,#bfdbfe);}
.ult-berita-info{flex:1;min-width:0;}
.ult-berita-title{font-weight:700;color:var(--admin-text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.9rem;}
.ult-berita-title:hover{color:var(--admin-info);}
.ult-berita-author{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;}
.ult-berita-category{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;border-radius:50px;font-size:.75rem;font-weight:600;color:#fff;}
.ult-berita-status{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
.ult-berita-views{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:var(--admin-bg);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text-secondary);}
.ult-berita-views i{color:var(--admin-info);}

/* ===== ROW ACTION ===== */
.ult-berita-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-berita-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-berita-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-berita-row-btn.view:hover{color:var(--admin-info);background:rgba(59,130,246,.08);}
.ult-berita-row-btn.edit:hover{color:#f59e0b;background:rgba(245,158,11,.08);}
.ult-berita-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-berita-row-menu{min-width:200px;}
.ult-berita-row-menu .ult-berita-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-berita-row-menu .ult-berita-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-berita-row-menu .ult-berita-dd-item.danger{color:var(--admin-danger);}
.ult-berita-row-menu .ult-berita-dd-item.danger:hover{background:rgba(239,68,68,.08);}

/* ===== GRID VIEW ===== */
.ult-berita-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;padding:1.5rem;}
.ult-berita-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;transition:all .3s;position:relative;}
.ult-berita-card:hover{transform:translateY(-4px);box-shadow:0 12px 35px rgba(0,0,0,.1);border-color:rgba(59,130,246,.3);}
.ult-berita-card-image{aspect-ratio:16/10;background:linear-gradient(135deg,#dbeafe,#bfdbfe);position:relative;overflow:hidden;}
.ult-berita-card-image img{width:100%;height:100%;object-fit:cover;}
.ult-berita-card-image-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--admin-info);opacity:.3;}
.ult-berita-card-badge{position:absolute;top:.75rem;right:.75rem;}
.ult-berita-card-category{position:absolute;top:.75rem;left:.75rem;}
.ult-berita-card-body{padding:1.25rem;}
.ult-berita-card-meta{display:flex;gap:.75rem;font-size:.75rem;color:var(--admin-text-muted);margin-bottom:.5rem;flex-wrap:wrap;}
.ult-berita-card-meta span{display:flex;align-items:center;gap:.25rem;}
.ult-berita-card-title{font-size:1rem;font-weight:700;color:var(--admin-text);margin-bottom:.5rem;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.ult-berita-card-excerpt{font-size:.825rem;color:var(--admin-text-secondary);line-height:1.6;margin-bottom:1rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.ult-berita-card-footer{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.25rem;border-top:1px solid var(--admin-border);}
.ult-berita-card-author{display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:var(--admin-text-secondary);}
.ult-berita-card-author-avatar{width:28px;height:28px;border-radius:50%;background:var(--admin-info);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;}
.ult-berita-card-actions{display:flex;gap:.375rem;}
.ult-berita-card-btn{width:32px;height:32px;border-radius:6px;background:var(--admin-bg);border:1px solid var(--admin-border);display:flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);cursor:pointer;transition:all .2s;text-decoration:none!important;}
.ult-berita-card-btn:hover{background:var(--admin-info);color:#fff!important;border-color:var(--admin-info);}
.ult-berita-card-btn.danger:hover{background:var(--admin-danger);border-color:var(--admin-danger);}
.ult-berita-card-actions-wrap{position:absolute;top:.75rem;right:.75rem;}

/* ===== EMPTY STATE ===== */
.ult-berita-empty{text-align:center;padding:4rem 2rem;}
.ult-berita-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:var(--admin-info);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-berita-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(59,130,246,.3);border-radius:50%;animation:ult-berita-spin 20s linear infinite;}
@keyframes ult-berita-spin{to{transform:rotate(360deg);}}
.ult-berita-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-berita-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== PAGINATION ===== */
.ult-berita-pagination{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;border-top:1px solid var(--admin-border);flex-wrap:wrap;gap:1rem;}
.ult-berita-page-info{font-size:.85rem;color:var(--admin-text-secondary);}
.ult-berita-page-info strong{color:var(--admin-text);}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-berita-stats{grid-template-columns:repeat(2,1fr);}
    .ult-berita-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-berita-stats{grid-template-columns:1fr;}
    .ult-berita-grid{grid-template-columns:1fr;padding:1rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-berita-stats">
    <div class="ult-berita-stat" style="--ult-color:#3b82f6;">
        <div class="ult-berita-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-newspaper"></i></div>
        <div class="ult-berita-stat-info">
            <div class="ult-berita-stat-label">Total Berita</div>
            <div class="ult-berita-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-berita-stat-sub"><i class="bi bi-database-fill"></i> Keseluruhan</div>
        </div>
    </div>
    <div class="ult-berita-stat" style="--ult-color:#10b981;">
        <div class="ult-berita-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-eye-fill"></i></div>
        <div class="ult-berita-stat-info">
            <div class="ult-berita-stat-label">Published</div>
            <div class="ult-berita-stat-value"><?= number_format($statPublished) ?></div>
            <div class="ult-berita-stat-sub"><i class="bi bi-arrow-up-short"></i> <?= $statTotal > 0 ? round(($statPublished / $statTotal) * 100) : 0 ?>% dari total</div>
        </div>
    </div>
    <div class="ult-berita-stat" style="--ult-color:#f59e0b;">
        <div class="ult-berita-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="bi bi-pencil-square"></i></div>
        <div class="ult-berita-stat-info">
            <div class="ult-berita-stat-label">Draft</div>
            <div class="ult-berita-stat-value"><?= number_format($statDraft) ?></div>
            <div class="ult-berita-stat-sub"><i class="bi bi-clock-history"></i> Belum dipublikasi</div>
        </div>
    </div>
    <div class="ult-berita-stat" style="--ult-color:#8b5cf6;">
        <div class="ult-berita-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-bar-chart-fill"></i></div>
        <div class="ult-berita-stat-info">
            <div class="ult-berita-stat-label">Total Views</div>
            <div class="ult-berita-stat-value"><?= number_format($statViews) ?></div>
            <div class="ult-berita-stat-sub"><i class="bi bi-graph-up"></i> Semua artikel</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Berita</h1>
        <p class="page-subtitle">Publikasi berita & informasi</p>
    </div>
    <div class="ult-berita-header-actions">
        <a href="<?= url('admin/berita/tambah') ?>" class="btn btn-primary" style="background:var(--admin-info);border-color:var(--admin-info);">
            <i class="bi bi-plus-lg"></i> Tulis Berita
        </a>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-berita-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-newspaper"></i> Daftar Berita</h3>
                    <span class="pgn-result-info">
                        Menampilkan <strong><?= $fromRow ?>-<?= $toRow ?></strong> dari <strong><?= number_format($pagination['total']) ?></strong> berita
                    </span>
                </div>
                <div class="ult-berita-header-actions">
                    <div class="ult-berita-view-toggle" id="ultBeritaViewToggle">
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
        <form method="GET" action="<?= url('admin/berita') ?>" class="pgn-toolbar" style="padding:1rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="toolbar-search" style="flex:2;min-width:220px;">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($pagination['search']) ?>" placeholder="Cari judul atau konten berita...">
            </div>
            
            <!-- Custom Kategori Dropdown -->
            <div class="ult-berita-dropdown" data-dropdown>
                <input type="hidden" name="category" id="ultBeritaCatValue" value="<?= htmlspecialchars($pagination['category']) ?>">
                <button type="button" class="ult-berita-dropdown-trigger <?= $pagination['category'] !== '' ? 'has-value' : '' ?>" id="ultBeritaCatTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                        <?php if ($pagination['category'] !== '' && isset($categoryMap[$pagination['category']])): 
                            [$lbl, $icn, $clr] = $categoryMap[$pagination['category']]; ?>
                            <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i>
                            <span><?= $lbl ?></span>
                        <?php else: ?>
                            <i class="bi bi-tag" style="color:var(--admin-info);"></i>
                            <span>Semua Kategori</span>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-berita-dropdown-menu" style="min-width:220px;">
                    <div class="ult-berita-dd-item <?= $pagination['category'] === '' ? 'selected' : '' ?>" data-value="">
                        <i class="bi bi-tag" style="color:var(--admin-text-muted);"></i><span>Semua Kategori</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-berita-dd-divider"></div>
                    <?php foreach ($categoryMap as $key => [$lbl, $icn, $clr]): ?>
                    <div class="ult-berita-dd-item <?= $pagination['category'] === $key ? 'selected' : '' ?>" data-value="<?= $key ?>">
                        <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i><span><?= $lbl ?></span><i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Custom Status Dropdown -->
            <div class="ult-berita-dropdown" data-dropdown>
                <input type="hidden" name="status" id="ultBeritaStatusValue" value="<?= htmlspecialchars($pagination['status']) ?>">
                <button type="button" class="ult-berita-dropdown-trigger <?= $pagination['status'] !== '' ? 'has-value' : '' ?>" id="ultBeritaStatusTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;">
                        <?php if ($pagination['status'] !== '' && isset($statusMap[$pagination['status']])): 
                            [$cls, $lbl, $icn, $clr] = $statusMap[$pagination['status']]; ?>
                            <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i>
                            <span><?= $lbl ?></span>
                        <?php else: ?>
                            <i class="bi bi-funnel" style="color:var(--admin-info);"></i>
                            <span>Semua Status</span>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-berita-dropdown-menu" style="min-width:180px;">
                    <div class="ult-berita-dd-item <?= $pagination['status'] === '' ? 'selected' : '' ?>" data-value="">
                        <i class="bi bi-funnel" style="color:var(--admin-text-muted);"></i><span>Semua Status</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-berita-dd-divider"></div>
                    <?php foreach ($statusMap as $key => [$cls, $lbl, $icn, $clr]): ?>
                    <div class="ult-berita-dd-item <?= $pagination['status'] === $key ? 'selected' : '' ?>" data-value="<?= $key ?>">
                        <i class="bi <?= $icn ?>" style="color:<?= $clr ?>;"></i><span><?= $lbl ?></span><i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="background:var(--admin-info);border-color:var(--admin-info);"><i class="bi bi-funnel-fill"></i> Terapkan</button>
            <?php if ($pagination['search'] !== '' || $pagination['status'] !== '' || $pagination['category'] !== ''): ?>
                <a href="<?= url('admin/berita') ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
            <?php endif; ?>
        </form>
        
        <!-- Filter Chips -->
        <?php if ($pagination['search'] !== '' || $pagination['status'] !== '' || $pagination['category'] !== ''): ?>
        <div class="ult-berita-chips">
            <span class="ult-berita-chips-label"><i class="bi bi-funnel-fill"></i> Filter Aktif:</span>
            <?php if ($pagination['search'] !== ''): ?>
                <span class="ult-berita-chip">
                    <i class="bi bi-search"></i> Pencarian: "<strong><?= htmlspecialchars($pagination['search']) ?></strong>"
                    <a href="<?= $query(['q' => '', 'page' => 1]) ?>" class="ult-berita-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($pagination['category'] !== '' && isset($categoryMap[$pagination['category']])): ?>
                <span class="ult-berita-chip">
                    <i class="bi <?= $categoryMap[$pagination['category']][1] ?>"></i> Kategori: <strong><?= $categoryMap[$pagination['category']][0] ?></strong>
                    <a href="<?= $query(['category' => '', 'page' => 1]) ?>" class="ult-berita-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($pagination['status'] !== '' && isset($statusMap[$pagination['status']])): ?>
                <span class="ult-berita-chip">
                    <i class="bi <?= $statusMap[$pagination['status']][2] ?>"></i> Status: <strong><?= $statusMap[$pagination['status']][1] ?></strong>
                    <a href="<?= $query(['status' => '', 'page' => 1]) ?>" class="ult-berita-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <a href="<?= url('admin/berita') ?>" class="ult-berita-chips-clear"><i class="bi bi-x-circle"></i> Hapus Semua</a>
        </div>
        <?php endif; ?>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultBeritaTableView" class="ult-berita-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width:280px;">Judul Berita</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Tanggal</th>
                        <th style="width:140px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($berita as $b):
                    [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $statusMap[$b['is_published'] ? 'published' : 'draft'] ?? ['badge-info', $b['is_published'] ? 'Published' : 'Draft', 'bi-circle-fill', '#64748b'];
                    [$catLbl, $catIcn, $catClr] = $categoryMap[$b['category']] ?? ['Berita', 'bi-newspaper', '#1a5276'];
                    $imgUrl = !empty($b['featured_image']) ? url('uploads/berita/' . $b['featured_image']) : null;
                ?>
                    <tr>
                        <td>
                            <div class="ult-berita-cell">
                                <?php if ($imgUrl): ?>
                                    <img src="<?= $imgUrl ?>" alt="" class="ult-berita-thumb">
                                <?php else: ?>
                                    <div class="ult-berita-thumb-placeholder"><i class="bi bi-newspaper"></i></div>
                                <?php endif; ?>
                                <div class="ult-berita-info">
                                    <div class="ult-berita-title"><?= htmlspecialchars($b['title']) ?></div>
                                    <div class="ult-berita-author">
                                        <i class="bi bi-person-fill"></i> <?= htmlspecialchars($b['author_name'] ?? 'Admin') ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="ult-berita-category" style="background:<?= $catClr ?>;">
                                <i class="bi <?= $catIcn ?>"></i> <?= $catLbl ?>
                            </span>
                        </td>
                        <td>
                            <span class="ult-berita-status" style="background:<?= $badgeColor ?>20;color:<?= $badgeColor ?>;">
                                <i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                            </span>
                        </td>
                        <td>
                            <span class="ult-berita-views">
                                <i class="bi bi-eye-fill"></i> <?= number_format($b['views']) ?>
                            </span>
                        </td>
                        <td style="font-size:.85rem;color:var(--admin-text-secondary);">
                            <?= date('d M Y', strtotime($b['created_at'])) ?>
                        </td>
                        <td>
                            <div class="ult-berita-row-actions">
                                <a href="<?= url('berita/' . $b['slug']) ?>" target="_blank" class="ult-berita-row-btn view" title="Lihat"><i class="bi bi-box-arrow-up-right"></i></a>
                                <a href="<?= url('admin/berita/edit/' . $b['id']) ?>" class="ult-berita-row-btn edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                
                                <div class="ult-berita-dropdown" data-dropdown>
                                    <button type="button" class="ult-berita-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="ult-berita-dropdown-menu right ult-berita-row-menu">
                                        <div class="ult-berita-dd-header">Konten</div>
                                        <a href="<?= url('berita/' . $b['slug']) ?>" target="_blank" class="ult-berita-dd-item">
                                            <i class="bi bi-box-arrow-up-right"></i><span>Lihat di Website</span>
                                        </a>
                                        <a href="<?= url('admin/berita/edit/' . $b['id']) ?>" class="ult-berita-dd-item">
                                            <i class="bi bi-pencil-square"></i><span>Edit Berita</span>
                                        </a>
                                        <a href="#" class="ult-berita-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Duplikat berita segera hadir','info');return false;">
                                            <i class="bi bi-files"></i><span>Duplikat Berita</span>
                                        </a>
                                        <div class="ult-berita-dd-divider"></div>
                                        <div class="ult-berita-dd-header">Statistik</div>
                                        <a href="#" class="ult-berita-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Statistik views segera hadir','info');return false;">
                                            <i class="bi bi-bar-chart-fill"></i><span>Lihat Statistik</span>
                                        </a>
                                        <a href="#" class="ult-berita-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Export PDF segera hadir','info');return false;">
                                            <i class="bi bi-file-earmark-pdf-fill"></i><span>Export PDF</span>
                                        </a>
                                        <?php if ($canDelete): ?>
                                        <div class="ult-berita-dd-divider"></div>
                                        <a href="<?= url('admin/berita/hapus/' . $b['id']) ?>" class="ult-berita-dd-item danger" onclick="return confirm('Hapus berita <?= htmlspecialchars(addslashes($b['title'])) ?>?');">
                                            <i class="bi bi-trash3-fill"></i><span>Hapus Berita</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$berita): ?>
                    <tr><td colspan="6">
                        <div class="ult-berita-empty">
                            <div class="ult-berita-empty-icon"><i class="bi bi-newspaper"></i></div>
                            <h3>Tidak Ada Berita</h3>
                            <p>
                                <?php if ($pagination['search'] !== '' || $pagination['status'] !== '' || $pagination['category'] !== ''): ?>
                                    Tidak ada berita yang cocok dengan filter Anda. Coba ubah kriteria pencarian.
                                <?php else: ?>
                                    Belum ada berita dalam sistem. Mulai tulis berita pertama Anda!
                                <?php endif; ?>
                            </p>
                            <div style="display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;">
                                <?php if ($pagination['search'] !== '' || $pagination['status'] !== '' || $pagination['category'] !== ''): ?>
                                    <a href="<?= url('admin/berita') ?>" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset Filter</a>
                                <?php endif; ?>
                                <a href="<?= url('admin/berita/tambah') ?>" class="btn btn-primary" style="background:var(--admin-info);border-color:var(--admin-info);"><i class="bi bi-plus-lg"></i> Tulis Berita Pertama</a>
                            </div>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== GRID VIEW ===== -->
        <div id="ultBeritaGridView" class="ult-berita-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php foreach ($berita as $b):
                [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $statusMap[$b['is_published'] ? 'published' : 'draft'] ?? ['badge-info', $b['is_published'] ? 'Published' : 'Draft', 'bi-circle-fill', '#64748b'];
                [$catLbl, $catIcn, $catClr] = $categoryMap[$b['category']] ?? ['Berita', 'bi-newspaper', '#1a5276'];
                $imgUrl = !empty($b['featured_image']) ? url('uploads/berita/' . $b['featured_image']) : null;
            ?>
            <div class="ult-berita-card">
                <div class="ult-berita-card-actions-wrap">
                    <div class="ult-berita-dropdown" data-dropdown>
                        <button type="button" class="ult-berita-row-btn" title="Aksi" style="background:rgba(255,255,255,.9);"><i class="bi bi-three-dots-vertical"></i></button>
                        <div class="ult-berita-dropdown-menu right ult-berita-row-menu">
                            <a href="<?= url('admin/berita/edit/' . $b['id']) ?>" class="ult-berita-dd-item"><i class="bi bi-pencil-square"></i> Edit</a>
                            <?php if ($canDelete): ?>
                            <a href="<?= url('admin/berita/hapus/' . $b['id']) ?>" class="ult-berita-dd-item danger" onclick="return confirm('Hapus?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="ult-berita-card-image">
                    <?php if ($imgUrl): ?>
                        <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($b['title']) ?>">
                    <?php else: ?>
                        <div class="ult-berita-card-image-placeholder"><i class="bi bi-newspaper"></i></div>
                    <?php endif; ?>
                    <div class="ult-berita-card-category">
                        <span class="ult-berita-category" style="background:<?= $catClr ?>;font-size:.7rem;">
                            <i class="bi <?= $catIcn ?>"></i> <?= $catLbl ?>
                        </span>
                    </div>
                    <div class="ult-berita-card-badge">
                        <span class="ult-berita-status" style="background:<?= $badgeColor ?>ee;color:#fff;font-size:.7rem;">
                            <i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                        </span>
                    </div>
                </div>
                
                <div class="ult-berita-card-body">
                    <div class="ult-berita-card-meta">
                        <span><i class="bi bi-calendar"></i> <?= date('d M Y', strtotime($b['created_at'])) ?></span>
                        <span><i class="bi bi-eye-fill"></i> <?= number_format($b['views']) ?> views</span>
                    </div>
                    <div class="ult-berita-card-title"><?= htmlspecialchars($b['title']) ?></div>
                    <div class="ult-berita-card-excerpt"><?= htmlspecialchars(mb_substr($b['excerpt'] ?? $b['content'] ?? '', 0, 100)) ?>...</div>
                </div>
                
                <div class="ult-berita-card-footer">
                    <div class="ult-berita-card-author">
                        <div class="ult-berita-card-author-avatar"><?= strtoupper(substr($b['author_name'] ?? 'A', 0, 1)) ?></div>
                        <span><?= htmlspecialchars($b['author_name'] ?? 'Admin') ?></span>
                    </div>
                    <div class="ult-berita-card-actions">
                        <a href="<?= url('berita/' . $b['slug']) ?>" target="_blank" class="ult-berita-card-btn" title="Lihat"><i class="bi bi-box-arrow-up-right"></i></a>
                        <a href="<?= url('admin/berita/edit/' . $b['id']) ?>" class="ult-berita-card-btn" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- ===== PAGINATION ===== -->
        <?php if ($pagination['totalPages'] > 1): ?>
        <div class="ult-berita-pagination">
            <div class="ult-berita-page-info">
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
    document.querySelectorAll('.ult-berita-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-berita-dropdown-trigger, .ult-berita-row-btn');
        const menu = dd.querySelector('.ult-berita-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-berita-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
            if(dd.classList.contains('open')){
                const s = menu.querySelector('[data-search]');
                if(s) setTimeout(() => s.focus(), 50);
            }
        });
        menu.querySelectorAll('.ult-berita-dd-item[data-value]').forEach(function(item){
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
                menu.querySelectorAll('.ult-berita-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' && val !== '0' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
                if(hidden && (hidden.name === 'category' || hidden.name === 'status')){
                    const form = dd.closest('form');
                    if(form) form.submit();
                }
            });
        });
        const s = menu.querySelector('[data-search]');
        if(s){
            s.addEventListener('input', function(){
                const q = this.value.toLowerCase();
                menu.querySelectorAll('.ult-berita-dd-item').forEach(i => {
                    if(i.hasAttribute('data-value')) i.style.display = i.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-berita-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultBeritaViewToggle');
    const tv = document.getElementById('ultBeritaTableView');
    const gv = document.getElementById('ultBeritaGridView');
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
                try { localStorage.setItem('ult_berita_view', v); } catch(e){}
            });
        });
    }
})();
</script>