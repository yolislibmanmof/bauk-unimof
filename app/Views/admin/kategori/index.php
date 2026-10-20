<?php
$isKeuangan = ($tipe === 'keuangan');
$route = $isKeuangan ? 'kategori-keuangan' : 'kategori-aset';
$moduleTitle = $isKeuangan ? 'Kategori Keuangan' : 'Kategori Aset';
$moduleDesc = $isKeuangan ? 'Klasifikasi untuk transaksi keuangan' : 'Klasifikasi untuk inventaris aset';
$moduleColor = $isKeuangan ? 'var(--admin-accent,#e67e22)' : 'var(--admin-success,#10b981)';
$moduleIcon = $isKeuangan ? 'bi-wallet2' : 'bi-box-seam-fill';

// ===== STATS =====
try {
    $statTotal = count($kategori);
    if ($isKeuangan) {
        $statPemasukan = count(array_filter($kategori, fn($k) => $k['type'] === 'pemasukan'));
        $statPengeluaran = count(array_filter($kategori, fn($k) => $k['type'] === 'pengeluaran'));
        $statActive = count(array_filter($kategori, fn($k) => $k['status'] === 'active'));
    } else {
        $statPemasukan = count(array_filter($kategori, fn($k) => (float)($k['depreciation_rate'] ?? 0) > 0));
        $statPengeluaran = count(array_filter($kategori, fn($k) => (float)($k['depreciation_rate'] ?? 0) === 0));
        $statActive = count(array_filter($kategori, fn($k) => $k['status'] === 'active'));
    }
} catch (\Throwable $e) {
    $statTotal = $statPemasukan = $statPengeluaran = $statActive = 0;
}

$currentView = $_GET['view'] ?? 'table';
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-kat-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-kat-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-kat-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,<?= $moduleColor ?>);}
.ult-kat-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-kat-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-kat-stat-info{flex:1;min-width:0;}
.ult-kat-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-kat-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-kat-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-kat-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-kat-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-kat-dropdown{position:relative;display:inline-block;}
.ult-kat-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:150px;justify-content:space-between;}
.ult-kat-dropdown-trigger:hover{border-color:<?= $moduleColor ?>;background:var(--admin-bg);}
.ult-kat-dropdown-trigger.has-value{border-color:<?= $moduleColor ?>;background:rgba(26,82,118,.05);}
.ult-kat-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-kat-dropdown.open .ult-kat-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-kat-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:300px;overflow-y:auto;}
.ult-kat-dropdown.open .ult-kat-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-kat-dropdown-menu.right{left:auto;right:0;}
.ult-kat-dd-search{padding:.625rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);}
.ult-kat-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;font-size:.825rem;background:var(--admin-bg);}
.ult-kat-dd-search input:focus{outline:none;border-color:<?= $moduleColor ?>;}
.ult-kat-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-kat-dd-item:last-child{border-bottom:none;}
.ult-kat-dd-item:hover{background:var(--admin-bg);}
.ult-kat-dd-item.selected{background:rgba(26,82,118,.08);color:<?= $moduleColor ?>;font-weight:600;}
.ult-kat-dd-item i.bi-check-lg{margin-left:auto;color:<?= $moduleColor ?>;opacity:0;}
.ult-kat-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-kat-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-kat-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== VIEW TOGGLE ===== */
.ult-kat-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-kat-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-kat-view-toggle button.active{background:<?= $moduleColor ?>;color:#fff;}
.ult-kat-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-kat-table-wrap{overflow-x:auto;}
.ult-kat-cell{display:flex;align-items:center;gap:.75rem;min-width:200px;}
.ult-kat-icon{width:44px;height:44px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff;flex-shrink:0;}
.ult-kat-info{flex:1;min-width:0;}
.ult-kat-name{font-weight:700;color:var(--admin-text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-kat-code{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:6px;font-size:.78rem;font-weight:700;color:<?= $moduleColor ?>;font-family:monospace;}
.ult-kat-type{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
.ult-kat-rate{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;background:rgba(139,92,246,.08);border-radius:50px;font-size:.78rem;font-weight:700;color:#8b5cf6;}
.ult-kat-desc{font-size:.825rem;color:var(--admin-text-secondary);line-height:1.5;}

/* ===== ROW ACTION ===== */
.ult-kat-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-kat-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-kat-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-kat-row-btn.edit:hover{color:#f59e0b;background:rgba(245,158,11,.08);}
.ult-kat-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-kat-row-menu{min-width:180px;}
.ult-kat-row-menu .ult-kat-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-kat-row-menu .ult-kat-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-kat-row-menu .ult-kat-dd-item.danger{color:var(--admin-danger);}
.ult-kat-row-menu .ult-kat-dd-item.danger:hover{background:rgba(239,68,68,.08);}

/* ===== GRID VIEW ===== */
.ult-kat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;padding:1.5rem;}
.ult-kat-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;transition:all .3s;position:relative;}
.ult-kat-card:hover{transform:translateY(-4px);box-shadow:0 12px 35px rgba(0,0,0,.1);border-color:<?= $moduleColor ?>;}
.ult-kat-card-accent{height:4px;background:linear-gradient(90deg,<?= $moduleColor ?>,<?= $moduleColor ?>cc);}
.ult-kat-card-body{padding:1.5rem;}
.ult-kat-card-header{display:flex;align-items:center;gap:1rem;margin-bottom:1rem;}
.ult-kat-card-icon{width:50px;height:50px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:#fff;flex-shrink:0;}
.ult-kat-card-title{flex:1;min-width:0;}
.ult-kat-card-title h4{font-size:1rem;font-weight:700;margin:0 0 .25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-kat-card-title code{font-size:.75rem;font-weight:700;color:<?= $moduleColor ?>;background:rgba(26,82,118,.08);padding:.125rem .5rem;border-radius:4px;}
.ult-kat-card-desc{font-size:.85rem;color:var(--admin-text-secondary);line-height:1.6;margin-bottom:1rem;min-height:40px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.ult-kat-card-meta{display:flex;justify-content:space-between;align-items:center;padding-top:1rem;border-top:1px solid var(--admin-border);}

/* ===== FORM CARD ===== */
.ult-kat-form-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;}
.ult-kat-form-header{padding:1.25rem 1.5rem;background:linear-gradient(to right, var(--admin-bg), transparent);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-kat-form-header i{font-size:1.25rem;color:<?= $moduleColor ?>;}
.ult-kat-form-header h3{font-size:1.1rem;font-weight:700;margin:0;}
.ult-kat-form-body{padding:1.5rem;}
.ult-kat-field{display:flex;flex-direction:column;gap:.375rem;margin-bottom:1.25rem;}
.ult-kat-label{font-size:.825rem;font-weight:600;color:var(--admin-text);display:flex;align-items:center;gap:.375rem;}
.ult-kat-label .req{color:var(--admin-danger);}
.ult-kat-input{position:relative;}
.ult-kat-input .icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);font-size:1rem;pointer-events:none;transition:color .2s;}
.ult-kat-input input,.ult-kat-input select,.ult-kat-input textarea{width:100%;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);font-size:.9rem;background:var(--admin-bg);transition:all .2s;font-family:inherit;}
.ult-kat-input textarea{padding-left:1rem;resize:vertical;min-height:80px;}
.ult-kat-input input:focus,.ult-kat-input select:focus,.ult-kat-input textarea:focus{outline:none;border-color:<?= $moduleColor ?>;background:#fff;box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-kat-input input:focus ~ .icon,.ult-kat-input select:focus ~ .icon{color:<?= $moduleColor ?>;}
.ult-kat-hint{font-size:.75rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}
.ult-kat-hint i{font-size:.7rem;}

/* ===== EMPTY STATE ===== */
.ult-kat-empty{text-align:center;padding:4rem 2rem;}
.ult-kat-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,<?= $moduleColor ?>22,<?= $moduleColor ?>11);color:<?= $moduleColor ?>;display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-kat-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed <?= $moduleColor ?>44;border-radius:50%;animation:ult-kat-spin 20s linear infinite;}
@keyframes ult-kat-spin{to{transform:rotate(360deg);}}
.ult-kat-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-kat-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-kat-stats{grid-template-columns:repeat(2,1fr);}
    .ult-kat-header{flex-direction:column;align-items:stretch;}
    .dashboard-grid{grid-template-columns:1fr;}
}
@media (max-width:640px){
    .ult-kat-stats{grid-template-columns:1fr;}
    .ult-kat-grid{grid-template-columns:1fr;padding:1rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-kat-stats">
    <div class="ult-kat-stat" style="--ult-color:<?= $moduleColor ?>;">
        <div class="ult-kat-stat-icon" style="background:linear-gradient(135deg,<?= $moduleColor ?>,<?= $moduleColor ?>cc);"><i class="bi <?= $moduleIcon ?>"></i></div>
        <div class="ult-kat-stat-info">
            <div class="ult-kat-stat-label">Total Kategori</div>
            <div class="ult-kat-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-kat-stat-sub"><i class="bi bi-database-fill"></i> Keseluruhan</div>
        </div>
    </div>
    <div class="ult-kat-stat" style="--ult-color:#10b981;">
        <div class="ult-kat-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-check-circle-fill"></i></div>
        <div class="ult-kat-stat-info">
            <div class="ult-kat-stat-label"><?= $isKeuangan ? 'Pemasukan' : 'Dengan Penyusutan' ?></div>
            <div class="ult-kat-stat-value"><?= number_format($statPemasukan) ?></div>
            <div class="ult-kat-stat-sub"><i class="bi bi-arrow-up-short"></i> <?= $statTotal > 0 ? round(($statPemasukan / $statTotal) * 100) : 0 ?>% dari total</div>
        </div>
    </div>
    <div class="ult-kat-stat" style="--ult-color:#ef4444;">
        <div class="ult-kat-stat-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="bi bi-x-circle-fill"></i></div>
        <div class="ult-kat-stat-info">
            <div class="ult-kat-stat-label"><?= $isKeuangan ? 'Pengeluaran' : 'Tanpa Penyusutan' ?></div>
            <div class="ult-kat-stat-value"><?= number_format($statPengeluaran) ?></div>
            <div class="ult-kat-stat-sub"><i class="bi bi-arrow-down-short"></i> <?= $statTotal > 0 ? round(($statPengeluaran / $statTotal) * 100) : 0 ?>% dari total</div>
        </div>
    </div>
    <div class="ult-kat-stat" style="--ult-color:#8b5cf6;">
        <div class="ult-kat-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-patch-check-fill"></i></div>
        <div class="ult-kat-stat-info">
            <div class="ult-kat-stat-label">Status Aktif</div>
            <div class="ult-kat-stat-value"><?= number_format($statActive) ?></div>
            <div class="ult-kat-stat-sub"><i class="bi bi-shield-check"></i> Dapat digunakan</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title"><?= $moduleTitle ?></h1>
        <p class="page-subtitle"><?= $moduleDesc ?></p>
    </div>
    <div class="ult-kat-header-actions">
        <div class="ult-kat-view-toggle" id="ultKatViewToggle">
            <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
            </button>
            <button type="button" class="<?= $currentView === 'grid' ? 'active' : '' ?>" data-view="grid" title="Tampilan Kartu">
                <i class="bi bi-grid-3x3-gap"></i> <span class="d-none-mobile">Kartu</span>
            </button>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- ===== DAFTAR KATEGORI ===== -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-list"></i> Daftar Kategori (<?= count($kategori) ?>)</h3>
        </div>
        <div class="card-body" style="padding:0;">
            
            <!-- ===== TABLE VIEW ===== -->
            <div id="ultKatTableView" class="ult-kat-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
                <table class="data-table">
                    <thead><tr><th>Kode</th><th>Nama</th><?php if ($isKeuangan): ?><th>Tipe</th><?php else: ?><th>Penyusutan</th><?php endif; ?><th>Deskripsi</th><th style="width:100px;text-align:right;">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($kategori as $k): 
                        $catColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];
                        $colorIdx = array_search($k['id'], array_column($kategori, 'id')) % count($catColors);
                        $catColor = $catColors[$colorIdx];
                    ?>
                        <tr>
                            <td><span class="ult-kat-code"><i class="bi bi-hash"></i> <?= htmlspecialchars($k['code']) ?></span></td>
                            <td>
                                <div class="ult-kat-cell">
                                    <div class="ult-kat-icon" style="background:linear-gradient(135deg,<?= $catColor ?>,<?= $catColor ?>cc);"><i class="bi <?= $isKeuangan ? 'bi-wallet2' : 'bi-box-seam-fill' ?>"></i></div>
                                    <div class="ult-kat-info">
                                        <div class="ult-kat-name"><?= htmlspecialchars($k['name']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <?php if ($isKeuangan): ?>
                                <td><span class="ult-kat-type" style="background:<?= $k['type'] === 'pemasukan' ? 'rgba(16,185,129,.15)' : 'rgba(239,68,68,.15)' ?>;color:<?= $k['type'] === 'pemasukan' ? '#10b981' : '#ef4444' ?>;"><i class="bi bi-<?= $k['type'] === 'pemasukan' ? 'arrow-down-circle-fill' : 'arrow-up-circle-fill' ?>"></i> <?= ucfirst($k['type']) ?></span></td>
                            <?php else: ?>
                                <td><span class="ult-kat-rate"><i class="bi bi-percent"></i> <?= number_format((float)$k['depreciation_rate'], 2) ?>%</span></td>
                            <?php endif; ?>
                            <td class="ult-kat-desc"><?= htmlspecialchars($k['description'] ?? '-') ?></td>
                            <td>
                                <div class="ult-kat-row-actions">
                                    <div class="ult-kat-dropdown" data-dropdown>
                                        <button type="button" class="ult-kat-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                        <div class="ult-kat-dropdown-menu right ult-kat-row-menu">
                                            <div class="ult-kat-dd-header">Kategori</div>
                                            <a href="#" class="ult-kat-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Edit kategori segera hadir','info');return false;">
                                                <i class="bi bi-pencil-square"></i><span>Edit Kategori</span>
                                            </a>
                                            <a href="#" class="ult-kat-dd-item danger" onclick="if(typeof showToast!=='undefined')showToast('Hapus kategori segera hadir','info');return false;">
                                                <i class="bi bi-trash3-fill"></i><span>Hapus Kategori</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$kategori): ?><tr><td colspan="<?= $isKeuangan ? 5 : 5 ?>">
                        <div class="ult-kat-empty">
                            <div class="ult-kat-empty-icon"><i class="bi <?= $moduleIcon ?>"></i></div>
                            <h3>Belum Ada Kategori</h3>
                            <p>Mulai tambahkan kategori untuk mengklasifikasikan <?= $isKeuangan ? 'transaksi keuangan' : 'inventaris aset' ?> dengan lebih terorganisir.</p>
                        </div>
                    </td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- ===== GRID VIEW ===== -->
            <div id="ultKatGridView" class="ult-kat-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
                <?php foreach ($kategori as $k): 
                    $catColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];
                    $colorIdx = array_search($k['id'], array_column($kategori, 'id')) % count($catColors);
                    $catColor = $catColors[$colorIdx];
                ?>
                <div class="ult-kat-card">
                    <div class="ult-kat-card-accent" style="background:linear-gradient(90deg,<?= $catColor ?>,<?= $catColor ?>cc);"></div>
                    <div class="ult-kat-card-body">
                        <div class="ult-kat-card-header">
                            <div class="ult-kat-card-icon" style="background:linear-gradient(135deg,<?= $catColor ?>,<?= $catColor ?>cc);"><i class="bi <?= $isKeuangan ? 'bi-wallet2' : 'bi-box-seam-fill' ?>"></i></div>
                            <div class="ult-kat-card-title">
                                <h4><?= htmlspecialchars($k['name']) ?></h4>
                                <code><?= htmlspecialchars($k['code']) ?></code>
                            </div>
                        </div>
                        <div class="ult-kat-card-desc"><?= htmlspecialchars($k['description'] ?? 'Tidak ada deskripsi') ?></div>
                        <div class="ult-kat-card-meta">
                            <?php if ($isKeuangan): ?>
                                <span class="ult-kat-type" style="background:<?= $k['type'] === 'pemasukan' ? 'rgba(16,185,129,.15)' : 'rgba(239,68,68,.15)' ?>;color:<?= $k['type'] === 'pemasukan' ? '#10b981' : '#ef4444' ?>;font-size:.7rem;">
                                    <i class="bi bi-<?= $k['type'] === 'pemasukan' ? 'arrow-down-circle-fill' : 'arrow-up-circle-fill' ?>"></i> <?= ucfirst($k['type']) ?>
                                </span>
                            <?php else: ?>
                                <span class="ult-kat-rate" style="font-size:.7rem;"><i class="bi bi-percent"></i> <?= number_format((float)$k['depreciation_rate'], 2) ?>%</span>
                            <?php endif; ?>
                            <span style="font-size:.75rem;color:var(--admin-text-muted);"><i class="bi bi-patch-check-fill"></i> <?= ucfirst($k['status']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
        </div>
    </div>

    <!-- ===== FORM TAMBAH KATEGORI ===== -->
    <div class="ult-kat-form-card">
        <div class="ult-kat-form-header"><i class="bi bi-plus-circle-fill"></i><h3>Tambah Kategori</h3></div>
        <div class="ult-kat-form-body">
            <form method="POST" action="<?= url('admin/' . $route . '/simpan') ?>" id="ultKatForm">
                <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
                
                <div class="ult-kat-field">
                    <label class="ult-kat-label">Kode Kategori <span class="req">*</span></label>
                    <div class="ult-kat-input">
                        <input type="text" name="code" id="katCode" required maxlength="20" placeholder="Contoh: <?= $isKeuangan ? 'OPR' : 'ELK' ?>" style="text-transform:uppercase;">
                        <i class="bi bi-hash icon"></i>
                    </div>
                    <span class="ult-kat-hint"><i class="bi bi-info-circle"></i> Kode unik, huruf kapital, maks 20 karakter</span>
                </div>
                
                <div class="ult-kat-field">
                    <label class="ult-kat-label">Nama Kategori <span class="req">*</span></label>
                    <div class="ult-kat-input">
                        <input type="text" name="name" id="katName" required maxlength="100" placeholder="Contoh: <?= $isKeuangan ? 'Biaya Operasional' : 'Elektronik' ?>">
                        <i class="bi bi-tag icon"></i>
                    </div>
                </div>
                
                <?php if ($isKeuangan): ?>
                <div class="ult-kat-field">
                    <label class="ult-kat-label">Tipe Transaksi <span class="req">*</span></label>
                    <div class="ult-kat-dropdown" data-dropdown>
                        <input type="hidden" name="type" value="pengeluaran" id="katType">
                        <button type="button" class="ult-kat-dropdown-trigger has-value" id="katTypeTrigger">
                            <i class="bi bi-arrow-up-circle" style="color:#ef4444;"></i>
                            <span>Pengeluaran</span>
                            <i class="bi bi-chevron-down chevron"></i>
                        </button>
                        <div class="ult-kat-dropdown-menu">
                            <div class="ult-kat-dd-item selected" data-value="pengeluaran" data-label="Pengeluaran">
                                <i class="bi bi-arrow-up-circle" style="color:#ef4444;"></i><span>Pengeluaran</span><i class="bi bi-check-lg"></i>
                            </div>
                            <div class="ult-kat-dd-item" data-value="pemasukan" data-label="Pemasukan">
                                <i class="bi bi-arrow-down-circle" style="color:#10b981;"></i><span>Pemasukan</span><i class="bi bi-check-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="ult-kat-field">
                    <label class="ult-kat-label">Tarif Penyusutan (%)</label>
                    <div class="ult-kat-input">
                        <input type="number" name="depreciation_rate" step="0.01" min="0" max="100" value="0" placeholder="0.00">
                        <i class="bi bi-percent icon"></i>
                    </div>
                    <span class="ult-kat-hint"><i class="bi bi-info-circle"></i> Persentase penyusutan tahunan</span>
                </div>
                <?php endif; ?>
                
                <div class="ult-kat-field">
                    <label class="ult-kat-label">Deskripsi</label>
                    <div class="ult-kat-input">
                        <textarea name="description" rows="3" placeholder="Deskripsi singkat kategori..."></textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;background:<?= $moduleColor ?>;border-color:<?= $moduleColor ?>;"><i class="bi bi-check-lg"></i> Simpan Kategori</button>
            </form>
        </div>
    </div>
</div>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function(){
    'use strict';
    
    // ===== CUSTOM DROPDOWN =====
    document.querySelectorAll('.ult-kat-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-kat-dropdown-trigger');
        const menu = dd.querySelector('.ult-kat-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-kat-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
        });
        menu.querySelectorAll('.ult-kat-dd-item[data-value]').forEach(function(item){
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
                menu.querySelectorAll('.ult-kat-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' && val !== '0' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
            });
        });
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-kat-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultKatViewToggle');
    const tv = document.getElementById('ultKatTableView');
    const gv = document.getElementById('ultKatGridView');
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
                try { localStorage.setItem('ult_kat_view', v); } catch(e){}
            });
        });
    }
    
    // ===== AUTO UPPERCASE CODE =====
    const codeInput = document.getElementById('katCode');
    if(codeInput){
        codeInput.addEventListener('input', function(){
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }
})();
</script>