<?php
use App\Helpers\Auth;
$canEdit = Auth::hasRole(['super_admin', 'admin_bauk']);

// ===== CATEGORY & STATUS MAP =====
$categoryMap = [
    'peraturan'    => ['Peraturan', 'bi-journal-bookmark-fill', '#1a5276'],
    'keputusan'    => ['Keputusan', 'bi-patch-check-fill', '#27ae60'],
    'surat_edaran' => ['Surat Edaran', 'bi-envelope-paper-fill', '#e67e22'],
    'sop'          => ['SOP', 'bi-list-check', '#8e44ad'],
    'pedoman'      => ['Pedoman', 'bi-book-fill', '#16a085'],
];

$statusMap = [
    'active'  => ['Aktif', 'bi-patch-check-fill', '#10b981'],
    'expired' => ['Kedaluwarsa', 'bi-clock-history', '#f59e0b'],
    'revoked' => ['Dicabut', 'bi-x-circle-fill', '#ef4444'],
];

$fileTypeMap = [
    'pdf'  => ['bi-file-earmark-pdf-fill', '#dc2626'],
    'doc'  => ['bi-file-earmark-word-fill', '#2563eb'],
    'docx' => ['bi-file-earmark-word-fill', '#2563eb'],
    'xls'  => ['bi-file-earmark-excel-fill', '#16a34a'],
    'xlsx' => ['bi-file-earmark-excel-fill', '#16a34a'],
    'ppt'  => ['bi-file-earmark-ppt-fill', '#ea580c'],
    'pptx' => ['bi-file-earmark-ppt-fill', '#ea580c'],
];

function regadmFileIcon(string $path): array {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return $GLOBALS['fileTypeMap'][$ext] ?? ['bi-file-earmark-fill', '#64748b'];
}

function regadmFormatSize(?int $bytes): string {
    if (!$bytes) return '-';
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1024, 0) . ' KB';
}

// ===== STATS =====
try {
    $db = \App\Helpers\Database::getInstance();
    $statTotal      = count($dokumen);
    $statActive     = count(array_filter($dokumen, fn($d) => $d['status'] === 'active'));
    $statExpired    = count(array_filter($dokumen, fn($d) => $d['status'] === 'expired'));
    $statDownloads  = array_sum(array_column($dokumen, 'download_count'));
    $statCategories = count(array_unique(array_column($dokumen, 'category')));
} catch (\Throwable $e) {
    $statTotal = $statActive = $statExpired = $statDownloads = $statCategories = 0;
}

$currentView = $_GET['view'] ?? 'table';
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-regadm-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-regadm-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-regadm-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#1a5276);}
.ult-regadm-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-regadm-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-regadm-stat-info{flex:1;min-width:0;}
.ult-regadm-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-regadm-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-regadm-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-regadm-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-regadm-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-regadm-dropdown{position:relative;display:inline-block;}
.ult-regadm-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:160px;justify-content:space-between;}
.ult-regadm-dropdown-trigger:hover{border-color:var(--admin-primary);background:var(--admin-bg);}
.ult-regadm-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.05);}
.ult-regadm-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-regadm-dropdown.open .ult-regadm-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-regadm-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:300px;overflow-y:auto;}
.ult-regadm-dropdown.open .ult-regadm-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-regadm-dropdown-menu.right{left:auto;right:0;}
.ult-regadm-dd-search{padding:.625rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);}
.ult-regadm-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;font-size:.825rem;background:var(--admin-bg);}
.ult-regadm-dd-search input:focus{outline:none;border-color:var(--admin-primary);}
.ult-regadm-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-regadm-dd-item:last-child{border-bottom:none;}
.ult-regadm-dd-item:hover{background:var(--admin-bg);}
.ult-regadm-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-regadm-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-regadm-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-regadm-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-regadm-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== FILTER CHIPS ===== */
.ult-regadm-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);align-items:center;}
.ult-regadm-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-regadm-chip i{font-size:.7rem;}
.ult-regadm-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-regadm-chip-close:hover{color:var(--admin-danger);}
.ult-regadm-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;}
.ult-regadm-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== VIEW TOGGLE ===== */
.ult-regadm-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-regadm-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-regadm-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-regadm-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-regadm-table-wrap{overflow-x:auto;}
.ult-regadm-cell{display:flex;align-items:center;gap:.875rem;min-width:240px;}
.ult-regadm-file-icon{width:48px;height:48px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:#fff;flex-shrink:0;position:relative;}
.ult-regadm-file-ext{position:absolute;bottom:-4px;right:-4px;background:#fff;color:var(--admin-text);font-size:.6rem;font-weight:800;padding:.0625rem .375rem;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,.15);text-transform:uppercase;}
.ult-regadm-info{flex:1;min-width:0;}
.ult-regadm-title{font-weight:700;color:var(--admin-text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-regadm-title:hover{color:var(--admin-primary);}
.ult-regadm-number{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;display:flex;align-items:center;gap:.25rem;font-family:monospace;}
.ult-regadm-number i{font-size:.65rem;}
.ult-regadm-category{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;color:#fff;}
.ult-regadm-status{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
.ult-regadm-downloads{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:var(--admin-bg);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text-secondary);}
.ult-regadm-downloads i{color:var(--admin-primary);}
.ult-regadm-size{font-size:.75rem;color:var(--admin-text-muted);}

/* ===== ROW ACTION ===== */
.ult-regadm-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-regadm-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-regadm-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-regadm-row-btn.download:hover{color:#10b981;background:rgba(16,185,129,.08);}
.ult-regadm-row-btn.edit:hover{color:#f59e0b;background:rgba(245,158,11,.08);}
.ult-regadm-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-regadm-row-menu{min-width:220px;}
.ult-regadm-row-menu .ult-regadm-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-regadm-row-menu .ult-regadm-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-regadm-row-menu .ult-regadm-dd-item.danger{color:var(--admin-danger);}
.ult-regadm-row-menu .ult-regadm-dd-item.danger:hover{background:rgba(239,68,68,.08);}

/* ===== GRID VIEW ===== */
.ult-regadm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.25rem;padding:1.5rem;}
.ult-regadm-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;transition:all .3s;position:relative;}
.ult-regadm-card:hover{transform:translateY(-4px);box-shadow:0 12px 35px rgba(0,0,0,.1);border-color:var(--admin-primary);}
.ult-regadm-card-accent{height:4px;}
.ult-regadm-card-body{padding:1.25rem;}
.ult-regadm-card-header{display:flex;align-items:center;gap:1rem;margin-bottom:1rem;}
.ult-regadm-card-title{flex:1;min-width:0;}
.ult-regadm-card-title h4{font-size:1rem;font-weight:700;margin:0 0 .25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-regadm-card-title code{font-size:.72rem;font-weight:600;color:var(--admin-text-muted);font-family:monospace;}
.ult-regadm-card-desc{font-size:.825rem;color:var(--admin-text-secondary);line-height:1.6;margin-bottom:1rem;min-height:48px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.ult-regadm-card-meta{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
.ult-regadm-card-meta span{display:inline-flex;align-items:center;gap:.375rem;font-size:.75rem;color:var(--admin-text-secondary);}
.ult-regadm-card-meta i{font-size:.7rem;color:var(--admin-primary);}
.ult-regadm-card-footer{display:flex;gap:.375rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
.ult-regadm-card-btn{flex:1;padding:.5rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:6px;font-size:.78rem;font-weight:600;color:var(--admin-text);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.375rem;transition:all .2s;text-decoration:none!important;}
.ult-regadm-card-btn:hover{background:var(--admin-primary);color:#fff!important;border-color:var(--admin-primary);}
.ult-regadm-card-btn.download:hover{background:#10b981;border-color:#10b981;}
.ult-regadm-card-btn.danger:hover{background:var(--admin-danger);border-color:var(--admin-danger);}
.ult-regadm-card-actions{position:absolute;top:.75rem;right:.75rem;}

/* ===== EMPTY STATE ===== */
.ult-regadm-empty{text-align:center;padding:4rem 2rem;}
.ult-regadm-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#8b5cf6;display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-regadm-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(139,92,246,.3);border-radius:50%;animation:ult-regadm-spin 20s linear infinite;}
@keyframes ult-regadm-spin{to{transform:rotate(360deg);}}
.ult-regadm-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-regadm-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-regadm-stats{grid-template-columns:repeat(2,1fr);}
    .ult-regadm-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-regadm-stats{grid-template-columns:1fr;}
    .ult-regadm-grid{grid-template-columns:1fr;padding:1rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-regadm-stats">
    <div class="ult-regadm-stat" style="--ult-color:#1a5276;">
        <div class="ult-regadm-stat-icon" style="background:linear-gradient(135deg,#1a5276,#2980b9);"><i class="bi bi-file-earmark-text-fill"></i></div>
        <div class="ult-regadm-stat-info">
            <div class="ult-regadm-stat-label">Total Dokumen</div>
            <div class="ult-regadm-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-regadm-stat-sub"><i class="bi bi-database-fill"></i> Keseluruhan</div>
        </div>
    </div>
    <div class="ult-regadm-stat" style="--ult-color:#10b981;">
        <div class="ult-regadm-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-patch-check-fill"></i></div>
        <div class="ult-regadm-stat-info">
            <div class="ult-regadm-stat-label">Dokumen Aktif</div>
            <div class="ult-regadm-stat-value"><?= number_format($statActive) ?></div>
            <div class="ult-regadm-stat-sub"><i class="bi bi-arrow-up-short"></i> <?= $statTotal > 0 ? round(($statActive / $statTotal) * 100) : 0 ?>% berlaku</div>
        </div>
    </div>
    <div class="ult-regadm-stat" style="--ult-color:#8b5cf6;">
        <div class="ult-regadm-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-download"></i></div>
        <div class="ult-regadm-stat-info">
            <div class="ult-regadm-stat-label">Total Unduhan</div>
            <div class="ult-regadm-stat-value"><?= number_format($statDownloads) ?></div>
            <div class="ult-regadm-stat-sub"><i class="bi bi-graph-up"></i> Semua dokumen</div>
        </div>
    </div>
    <div class="ult-regadm-stat" style="--ult-color:#e67e22;">
        <div class="ult-regadm-stat-icon" style="background:linear-gradient(135deg,#e67e22,#d97706);"><i class="bi bi-folder-fill"></i></div>
        <div class="ult-regadm-stat-info">
            <div class="ult-regadm-stat-label">Kategori</div>
            <div class="ult-regadm-stat-value"><?= number_format($statCategories) ?></div>
            <div class="ult-regadm-stat-sub"><i class="bi bi-diagram-3-fill"></i> Jenis dokumen</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Dokumen Regulasi</h1>
        <p class="page-subtitle">Peraturan, SOP, dan keputusan</p>
    </div>
    <div class="ult-regadm-header-actions">
        <div class="ult-regadm-view-toggle" id="ultRegadmViewToggle">
            <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
            </button>
            <button type="button" class="<?= $currentView === 'grid' ? 'active' : '' ?>" data-view="grid" title="Tampilan Kartu">
                <i class="bi bi-grid-3x3-gap"></i> <span class="d-none-mobile">Kartu</span>
            </button>
        </div>
        <?php if ($canEdit): ?>
        <a href="<?= url('admin/regulasi/tambah') ?>" class="btn btn-primary"><i class="bi bi-cloud-upload-fill"></i> Upload Regulasi</a>
        <?php endif; ?>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-regadm-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-archive-fill"></i> Daftar Dokumen Regulasi</h3>
                    <span class="pgn-result-info">
                        Total <strong><?= number_format($statTotal) ?></strong> dokumen
                        <?php if ($statActive > 0): ?>
                            <span style="color:var(--admin-success);font-weight:700;">• <?= $statActive ?> aktif</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultRegadmTableView" class="ult-regadm-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width:280px;">Judul Dokumen</th>
                        <th>Nomor</th>
                        <th>Kategori</th>
                        <th>Berlaku</th>
                        <th>Unduhan</th>
                        <th>Status</th>
                        <th style="width:140px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($dokumen as $d): 
                    [$catLbl, $catIcn, $catClr] = $categoryMap[$d['category']] ?? ['Lainnya', 'bi-file-earmark-fill', '#64748b'];
                    [$stLbl, $stIcn, $stClr] = $statusMap[$d['status']] ?? ['Unknown', 'bi-circle-fill', '#64748b'];
                    [$fileIcn, $fileClr] = regadmFileIcon($d['file_path']);
                    $ext = strtolower(pathinfo($d['file_path'], PATHINFO_EXTENSION)) ?: 'file';
                ?>
                    <tr>
                        <td>
                            <div class="ult-regadm-cell">
                                <div class="ult-regadm-file-icon" style="background:linear-gradient(135deg,<?= $fileClr ?>,<?= $fileClr ?>cc);">
                                    <i class="bi <?= $fileIcn ?>"></i>
                                    <span class="ult-regadm-file-ext"><?= htmlspecialchars($ext) ?></span>
                                </div>
                                <div class="ult-regadm-info">
                                    <div class="ult-regadm-title"><?= htmlspecialchars($d['title']) ?></div>
                                    <div class="ult-regadm-number"><i class="bi bi-hash"></i> <?= htmlspecialchars($d['document_number'] ?? 'Tanpa Nomor') ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-family:monospace;font-size:.825rem;color:var(--admin-text-secondary);"><?= htmlspecialchars($d['document_number'] ?? '-') ?></td>
                        <td>
                            <span class="ult-regadm-category" style="background:<?= $catClr ?>;">
                                <i class="bi <?= $catIcn ?>"></i> <?= $catLbl ?>
                            </span>
                        </td>
                        <td style="font-size:.85rem;color:var(--admin-text-secondary);">
                            <?= !empty($d['effective_date']) ? date('d M Y', strtotime($d['effective_date'])) : '-' ?>
                        </td>
                        <td>
                            <span class="ult-regadm-downloads">
                                <i class="bi bi-download"></i> <?= number_format($d['download_count'] ?? 0) ?>
                            </span>
                        </td>
                        <td>
                            <span class="ult-regadm-status" style="background:<?= $stClr ?>20;color:<?= $stClr ?>;">
                                <i class="bi <?= $stIcn ?>"></i> <?= $stLbl ?>
                            </span>
                        </td>
                        <td>
                            <div class="ult-regadm-row-actions">
                                <a href="<?= url('uploads/dokumen/' . $d['file_path']) ?>" class="ult-regadm-row-btn download" title="Unduh" target="_blank" download>
                                    <i class="bi bi-download"></i>
                                </a>
                                
                                <div class="ult-regadm-dropdown" data-dropdown>
                                    <button type="button" class="ult-regadm-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="ult-regadm-dropdown-menu right ult-regadm-row-menu">
                                        <div class="ult-regadm-dd-header">Dokumen</div>
                                        <a href="<?= url('uploads/dokumen/' . $d['file_path']) ?>" target="_blank" class="ult-regadm-dd-item">
                                            <i class="bi bi-eye-fill"></i><span>Lihat Dokumen</span>
                                        </a>
                                        <a href="<?= url('uploads/dokumen/' . $d['file_path']) ?>" class="ult-regadm-dd-item" download>
                                            <i class="bi bi-download"></i><span>Unduh File</span>
                                        </a>
                                        <a href="<?= url('admin/regulasi/edit/' . $d['id']) ?>" class="ult-regadm-dd-item">
                                            <i class="bi bi-pencil-square"></i><span>Edit Metadata</span>
                                        </a>
                                        <div class="ult-regadm-dd-divider"></div>
                                        <div class="ult-regadm-dd-header">Informasi</div>
                                        <div class="ult-regadm-dd-item" style="cursor:default;">
                                            <i class="bi bi-hdd-fill"></i>
                                            <span>Ukuran: <?= regadmFormatSize($d['file_size'] ?? null) ?></span>
                                        </div>
                                        <div class="ult-regadm-dd-item" style="cursor:default;">
                                            <i class="bi bi-calendar-event"></i>
                                            <span>Diunggah: <?= date('d M Y', strtotime($d['created_at'])) ?></span>
                                        </div>
                                        <?php if ($canEdit): ?>
                                        <div class="ult-regadm-dd-divider"></div>
                                        <a href="<?= url('admin/regulasi/hapus/' . $d['id']) ?>" class="ult-regadm-dd-item danger" onclick="return confirm('Hapus dokumen <?= htmlspecialchars(addslashes($d['title'])) ?>?');">
                                            <i class="bi bi-trash3-fill"></i><span>Hapus Dokumen</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$dokumen): ?>
                    <tr><td colspan="7">
                        <div class="ult-regadm-empty">
                            <div class="ult-regadm-empty-icon"><i class="bi bi-file-earmark-text"></i></div>
                            <h3>Belum Ada Dokumen Regulasi</h3>
                            <p>Belum ada dokumen yang diunggah. Mulai upload peraturan, SOP, atau keputusan untuk membangun pusat regulasi kampus.</p>
                            <?php if ($canEdit): ?>
                            <a href="<?= url('admin/regulasi/tambah') ?>" class="btn btn-primary"><i class="bi bi-cloud-upload-fill"></i> Upload Dokumen Pertama</a>
                            <?php endif; ?>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== GRID VIEW ===== -->
        <div id="ultRegadmGridView" class="ult-regadm-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php foreach ($dokumen as $d): 
                [$catLbl, $catIcn, $catClr] = $categoryMap[$d['category']] ?? ['Lainnya', 'bi-file-earmark-fill', '#64748b'];
                [$stLbl, $stIcn, $stClr] = $statusMap[$d['status']] ?? ['Unknown', 'bi-circle-fill', '#64748b'];
                [$fileIcn, $fileClr] = regadmFileIcon($d['file_path']);
                $ext = strtolower(pathinfo($d['file_path'], PATHINFO_EXTENSION)) ?: 'file';
            ?>
            <div class="ult-regadm-card">
                <div class="ult-regadm-card-accent" style="background:linear-gradient(90deg,<?= $catClr ?>,<?= $catClr ?>cc);"></div>
                <div class="ult-regadm-card-actions">
                    <div class="ult-regadm-dropdown" data-dropdown>
                        <button type="button" class="ult-regadm-row-btn" title="Aksi" style="background:var(--admin-surface);"><i class="bi bi-three-dots-vertical"></i></button>
                        <div class="ult-regadm-dropdown-menu right ult-regadm-row-menu">
                            <a href="<?= url('admin/regulasi/edit/' . $d['id']) ?>" class="ult-regadm-dd-item"><i class="bi bi-pencil-square"></i> Edit</a>
                            <a href="<?= url('uploads/dokumen/' . $d['file_path']) ?>" class="ult-regadm-dd-item" download><i class="bi bi-download"></i> Unduh</a>
                            <?php if ($canEdit): ?>
                            <a href="<?= url('admin/regulasi/hapus/' . $d['id']) ?>" class="ult-regadm-dd-item danger" onclick="return confirm('Hapus?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="ult-regadm-card-body">
                    <div class="ult-regadm-card-header">
                        <div class="ult-regadm-file-icon" style="background:linear-gradient(135deg,<?= $fileClr ?>,<?= $fileClr ?>cc);width:50px;height:50px;font-size:1.3rem;">
                            <i class="bi <?= $fileIcn ?>"></i>
                            <span class="ult-regadm-file-ext"><?= htmlspecialchars($ext) ?></span>
                        </div>
                        <div class="ult-regadm-card-title">
                            <h4><?= htmlspecialchars($d['title']) ?></h4>
                            <code><?= htmlspecialchars($d['document_number'] ?? 'Tanpa Nomor') ?></code>
                        </div>
                    </div>
                    
                    <?php if (!empty($d['description'])): ?>
                    <div class="ult-regadm-card-desc"><?= htmlspecialchars($d['description']) ?></div>
                    <?php endif; ?>
                    
                    <div class="ult-regadm-card-meta">
                        <span class="ult-regadm-category" style="background:<?= $catClr ?>;font-size:.7rem;">
                            <i class="bi <?= $catIcn ?>"></i> <?= $catLbl ?>
                        </span>
                        <span class="ult-regadm-status" style="background:<?= $stClr ?>20;color:<?= $stClr ?>;font-size:.7rem;">
                            <i class="bi <?= $stIcn ?>"></i> <?= $stLbl ?>
                        </span>
                        <span><i class="bi bi-download"></i> <?= number_format($d['download_count'] ?? 0) ?>x</span>
                        <span><i class="bi bi-hdd"></i> <?= regadmFormatSize($d['file_size'] ?? null) ?></span>
                    </div>
                    
                    <div class="ult-regadm-card-footer">
                        <a href="<?= url('uploads/dokumen/' . $d['file_path']) ?>" class="ult-regadm-card-btn download" target="_blank" download>
                            <i class="bi bi-download"></i> Unduh
                        </a>
                        <a href="<?= url('admin/regulasi/edit/' . $d['id']) ?>" class="ult-regadm-card-btn">
                            <i class="bi bi-pencil-fill"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (!$dokumen): ?>
            <div class="ult-regadm-empty" style="grid-column:1/-1;">
                <div class="ult-regadm-empty-icon"><i class="bi bi-file-earmark-text"></i></div>
                <h3>Belum Ada Dokumen</h3>
                <p>Belum ada dokumen regulasi yang diunggah.</p>
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
    document.querySelectorAll('.ult-regadm-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-regadm-dropdown-trigger, .ult-regadm-row-btn');
        const menu = dd.querySelector('.ult-regadm-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-regadm-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
        });
        menu.querySelectorAll('.ult-regadm-dd-item[data-value]').forEach(function(item){
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
                menu.querySelectorAll('.ult-regadm-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' && val !== '0' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
            });
        });
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-regadm-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultRegadmViewToggle');
    const tv = document.getElementById('ultRegadmTableView');
    const gv = document.getElementById('ultRegadmGridView');
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
                try { localStorage.setItem('ult_regadm_view', v); } catch(e){}
            });
        });
    }
})();
</script>