<?php
/**
 * =====================================================
 * BAUK UNIMOF - TUPOKSI LIST (ULTIMATE v2)
 * Dashboard Stats + Table/Card View + Instant Search
 * =====================================================
 */
use App\Helpers\Auth;

$canEdit = Auth::hasRole(['super_admin', 'admin_bauk']);
$items = $items ?? [];

// ===== STATS =====
$totalItems = count($items);
$activeItems = count(array_filter($items, fn($i) => (int)$i['is_active'] === 1));
$inactiveItems = $totalItems - $activeItems;
$maxSort = $totalItems > 0 ? max(array_map(fn($i) => (int)($i['sort_order'] ?? 0), $items)) : 0;
?>

<!-- ===== TUPOKSI LIST v2 STYLES ===== -->
<style>
/* ===== HERO ===== */
.ult2-tp-hero{background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 55%,#2980b9 100%);border-radius:20px;padding:2rem 2.25rem;color:#fff;position:relative;overflow:hidden;margin-bottom:1.5rem;}
.ult2-tp-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='1.5'/%3E%3C/g%3E%3C/svg%3E");}
.ult2-tp-hero::after{content:'';position:absolute;top:-60%;right:-8%;width:380px;height:380px;background:radial-gradient(circle,rgba(230,126,34,.25) 0%,transparent 70%);border-radius:50%;}
.ult2-tp-hero-inner{position:relative;z-index:2;display:flex;justify-content:space-between;align-items:center;gap:1.5rem;flex-wrap:wrap;}
.ult2-tp-hero-title{display:flex;align-items:center;gap:1rem;}
.ult2-tp-hero-icon{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.14);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:1.7rem;flex-shrink:0;}
.ult2-tp-hero h1{font-family:'Playfair Display',serif;font-size:1.65rem;margin:0 0 .25rem;color:#fff;}
.ult2-tp-hero p{margin:0;font-size:.85rem;color:rgba(255,255,255,.85);}
.ult2-tp-hero-actions{display:flex;gap:.6rem;flex-wrap:wrap;}
.ult2-tp-hero-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.7rem 1.3rem;border-radius:12px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .25s;text-decoration:none!important;border:1px solid rgba(255,255,255,.25);}
.ult2-tp-hero-btn-primary{background:linear-gradient(135deg,#e67e22,#f39c12);border-color:transparent;color:#fff;box-shadow:0 8px 22px rgba(230,126,34,.4);}
.ult2-tp-hero-btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(230,126,34,.5);color:#fff;}

/* ===== STATS ===== */
.ult2-tp-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult2-tp-stat{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;padding:1.15rem 1.25rem;display:flex;align-items:center;gap:.9rem;position:relative;overflow:hidden;transition:all .3s;}
.ult2-tp-stat::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--sc,#3b82f6);}
.ult2-tp-stat:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(0,0,0,.08);}
.ult2-tp-stat-icon{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff;flex-shrink:0;}
.ult2-tp-stat-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.6px;font-weight:700;color:var(--admin-text-muted);}
.ult2-tp-stat-value{font-size:1.5rem;font-weight:800;color:var(--admin-text);line-height:1.15;}
.ult2-tp-stat-sub{font-size:.7rem;color:var(--admin-text-muted);}

/* ===== TOOLBAR ===== */
.ult2-tp-toolbar{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;padding:1rem 1.25rem;display:flex;gap:.85rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem;}
.ult2-tp-searchbox{position:relative;flex:1;min-width:220px;}
.ult2-tp-searchbox i{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);}
.ult2-tp-searchbox input{width:100%;padding:.7rem 1rem .7rem 2.6rem;border:1.5px solid var(--admin-border);border-radius:12px;background:var(--admin-bg);color:var(--admin-text);font-size:.88rem;transition:all .2s;}
.ult2-tp-searchbox input:focus{outline:none;border-color:var(--admin-primary);box-shadow:0 0 0 3px rgba(26,82,118,.1);background:var(--admin-surface);}
.ult2-tp-filters{display:flex;gap:.4rem;flex-wrap:wrap;}
.ult2-tp-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .9rem;border-radius:50px;border:1.5px solid var(--admin-border);background:var(--admin-surface);color:var(--admin-text-secondary);font-size:.78rem;font-weight:700;cursor:pointer;transition:all .2s;}
.ult2-tp-chip:hover{border-color:var(--admin-primary);color:var(--admin-primary);}
.ult2-tp-chip.active{background:var(--admin-primary);border-color:var(--admin-primary);color:#fff;box-shadow:0 6px 16px rgba(26,82,118,.3);}
.ult2-tp-viewtoggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:12px;overflow:hidden;}
.ult2-tp-viewtoggle button{background:none;border:none;padding:.6rem .9rem;cursor:pointer;color:var(--admin-text-secondary);font-size:.82rem;font-weight:700;display:flex;align-items:center;gap:.4rem;transition:all .2s;}
.ult2-tp-viewtoggle button.active{background:var(--admin-primary);color:#fff;}
.ult2-tp-viewtoggle button:hover:not(.active){background:var(--admin-border);}

/* ===== CARD CONTAINER ===== */
.ult2-tp-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;overflow:hidden;}

/* ===== TABLE VIEW ===== */
.ult2-tp-table{width:100%;border-collapse:collapse;}
.ult2-tp-table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;color:var(--admin-text-muted);text-align:left;padding:.85rem 1.4rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);font-weight:700;}
.ult2-tp-table td{padding:1rem 1.4rem;border-bottom:1px solid var(--admin-border);vertical-align:middle;font-size:.88rem;color:var(--admin-text);}
.ult2-tp-table tr:last-child td{border-bottom:none;}
.ult2-tp-table tbody tr{transition:background .2s;}
.ult2-tp-table tbody tr:hover{background:rgba(26,82,118,.03);}
.ult2-tp-table tbody tr.hidden{display:none;}

.ult2-tp-unit-cell{display:flex;align-items:center;gap:.85rem;}
.ult2-tp-unit-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff;flex-shrink:0;}
.ult2-tp-unit-name{font-weight:700;color:var(--admin-text);}

.ult2-tp-kode{font-family:ui-monospace,monospace;font-size:.78rem;background:rgba(26,82,118,.08);color:var(--admin-primary);padding:.25rem .6rem;border-radius:6px;font-weight:700;border:1px solid rgba(26,82,118,.12);display:inline-block;}

.ult2-tp-jabatan{font-weight:600;color:var(--admin-text);}

.ult2-tp-status{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .7rem;border-radius:50px;font-size:.72rem;font-weight:700;}
.ult2-tp-status.active{background:rgba(16,185,129,.12);color:#059669;}
.ult2-tp-status.inactive{background:rgba(100,116,139,.1);color:#64748b;}

.ult2-tp-actions{display:flex;gap:.4rem;justify-content:flex-end;}
.ult2-tp-action-btn{width:36px;height:36px;border-radius:10px;border:1px solid var(--admin-border);background:var(--admin-surface);color:var(--admin-text-secondary);display:inline-flex;align-items:center;justify-content:center;font-size:.95rem;cursor:pointer;transition:all .2s;text-decoration:none!important;}
.ult2-tp-action-btn:hover{background:var(--admin-primary);color:#fff;border-color:var(--admin-primary);transform:translateY(-2px);box-shadow:0 6px 16px rgba(26,82,118,.25);}
.ult2-tp-action-btn.danger:hover{background:#ef4444;border-color:#ef4444;box-shadow:0 6px 16px rgba(239,68,68,.3);}

/* ===== CARD VIEW ===== */
.ult2-tp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;padding:1.5rem;}
.ult2-tp-grid-item{background:#fff;border:1px solid var(--admin-border);border-radius:16px;overflow:hidden;transition:all .3s;}
.ult2-tp-grid-item:hover{transform:translateY(-5px);box-shadow:0 15px 40px rgba(13,47,74,.12);}
.ult2-tp-grid-item.hidden{display:none;}
.ult2-tp-grid-header{padding:1.5rem;color:#fff;position:relative;overflow:hidden;}
.ult2-tp-grid-header::before{content:'';position:absolute;top:-30%;right:-20%;width:100px;height:100px;background:radial-gradient(circle,rgba(255,255,255,.2) 0%,transparent 70%);border-radius:50%;}
.ult2-tp-grid-icon{width:56px;height:56px;border-radius:14px;background:rgba(255,255,255,.2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:1rem;border:1px solid rgba(255,255,255,.3);position:relative;z-index:2;}
.ult2-tp-grid-kode{display:inline-block;font-size:.7rem;letter-spacing:1.5px;font-weight:700;background:rgba(255,255,255,.25);padding:.2rem .7rem;border-radius:20px;margin-bottom:.5rem;position:relative;z-index:2;}
.ult2-tp-grid-unit{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;position:relative;z-index:2;}
.ult2-tp-grid-body{padding:1.25rem;}
.ult2-tp-grid-jabatan{font-size:.82rem;font-weight:700;color:var(--admin-primary);margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.5px;}
.ult2-tp-grid-tugas{font-size:.82rem;color:#64748b;line-height:1.6;margin:0 0 1rem;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
.ult2-tp-grid-meta{display:flex;justify-content:space-between;align-items:center;padding-top:1rem;border-top:1px dashed var(--admin-border);}
.ult2-tp-grid-sort{font-family:ui-monospace,monospace;font-size:.75rem;font-weight:700;color:var(--admin-primary);background:rgba(26,82,118,.08);padding:.2rem .6rem;border-radius:6px;}
.ult2-tp-grid-actions{display:flex;gap:.35rem;}

/* ===== EMPTY STATE ===== */
.ult2-tp-empty{padding:4rem 2rem;text-align:center;}
.ult2-tp-empty-icon{width:100px;height:100px;margin:0 auto 1.2rem;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#1a5276;display:flex;align-items:center;justify-content:center;font-size:2.8rem;position:relative;}
.ult2-tp-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(26,82,118,.3);border-radius:50%;animation:ult2-spin 18s linear infinite;}
@keyframes ult2-spin{to{transform:rotate(360deg);}}
.ult2-tp-empty h3{margin:0 0 .35rem;font-size:1.2rem;color:var(--admin-text);}
.ult2-tp-empty p{margin:0 0 1.25rem;color:var(--admin-text-secondary);font-size:.88rem;}
.ult2-tp-empty-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.7rem 1.4rem;border-radius:12px;background:var(--admin-primary);color:#fff;text-decoration:none!important;font-weight:700;font-size:.88rem;transition:all .2s;}
.ult2-tp-empty-btn:hover{background:#0d2f4a;transform:translateY(-2px);box-shadow:0 8px 20px rgba(26,82,118,.3);}

/* ===== ACTION BAR ===== */
.ult2-tp-actionbar{position:sticky;bottom:0;background:var(--admin-surface);border-top:1px solid var(--admin-border);padding:.85rem 1.25rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-top:1rem;border-radius:16px;box-shadow:0 -4px 15px rgba(0,0,0,.05);border:1px solid var(--admin-border);}
@media (max-width:768px){.ult2-tp-actionbar{position:fixed;left:.5rem;right:.5rem;bottom:.5rem;margin:0;border-radius:14px;z-index:100;box-shadow:0 10px 30px rgba(0,0,0,.15);} body{padding-bottom:75px;}}
.ult2-tp-actionbar-info{font-size:.82rem;color:var(--admin-text-secondary);display:flex;align-items:center;gap:.5rem;}
.ult2-tp-actionbar-info i{color:var(--admin-primary);}

@media (max-width:768px){
    .ult2-tp-hero{padding:1.5rem;}
    .ult2-tp-stats{grid-template-columns:repeat(2,1fr);}
    .ult2-tp-toolbar{flex-direction:column;align-items:stretch;}
    .ult2-tp-searchbox{min-width:100%;}
    .ult2-tp-viewtoggle{align-self:flex-start;}
}
@media (max-width:480px){
    .ult2-tp-stats{grid-template-columns:1fr;}
    .ult2-tp-grid{grid-template-columns:1fr;}
}
</style>

<!-- ===== HERO ===== -->
<div class="ult2-tp-hero">
    <div class="ult2-tp-hero-inner">
        <div class="ult2-tp-hero-title">
            <div class="ult2-tp-hero-icon"><i class="bi bi-clipboard-check-fill"></i></div>
            <div>
                <h1>Tupoksi Unit Kerja</h1>
                <p>Kelola tugas pokok dan fungsi setiap unit di BAUK UNIMOF</h1>
            </div>
        </div>
        <div class="ult2-tp-hero-actions">
            <?php if ($canEdit): ?>
            <a href="<?= url('admin/tupoksi/tambah') ?>" class="ult2-tp-hero-btn ult2-tp-hero-btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Unit
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== STATS ===== -->
<div class="ult2-tp-stats">
    <div class="ult2-tp-stat" style="--sc:#1a5276;">
        <div class="ult2-tp-stat-icon" style="background:linear-gradient(135deg,#1a5276,#2980b9);"><i class="bi bi-building-fill"></i></div>
        <div><div class="ult2-tp-stat-label">Total Unit</div><div class="ult2-tp-stat-value"><?= number_format($totalItems) ?></div><div class="ult2-tp-stat-sub">Seluruh unit kerja</div></div>
    </div>
    <div class="ult2-tp-stat" style="--sc:#10b981;">
        <div class="ult2-tp-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-check-circle-fill"></i></div>
        <div><div class="ult2-tp-stat-label">Unit Aktif</div><div class="ult2-tp-stat-value"><?= number_format($activeItems) ?></div><div class="ult2-tp-stat-sub">Sedang beroperasi</div></div>
    </div>
    <div class="ult2-tp-stat" style="--sc:#64748b;">
        <div class="ult2-tp-stat-icon" style="background:linear-gradient(135deg,#64748b,#475569);"><i class="bi bi-pause-circle-fill"></i></div>
        <div><div class="ult2-tp-stat-label">Unit Nonaktif</div><div class="ult2-tp-stat-value"><?= number_format($inactiveItems) ?></div><div class="ult2-tp-stat-sub">Dinonaktifkan</div></div>
    </div>
    <div class="ult2-tp-stat" style="--sc:#e67e22;">
        <div class="ult2-tp-stat-icon" style="background:linear-gradient(135deg,#e67e22,#f39c12);"><i class="bi bi-sort-down"></i></div>
        <div><div class="ult2-tp-stat-label">Urutan Maks</div><div class="ult2-tp-stat-value"><?= number_format($maxSort) ?></div><div class="ult2-tp-stat-sub">Sort order tertinggi</div></div>
    </div>
</div>

<?php if (empty($items)): ?>
    <!-- EMPTY STATE -->
    <div class="ult2-tp-card">
        <div class="ult2-tp-empty">
            <div class="ult2-tp-empty-icon"><i class="bi bi-clipboard-x"></i></div>
            <h3>Belum Ada Data Tupoksi</h3>
            <p>Mulai definisikan tugas pokok dan fungsi unit kerja pertama Anda.</p>
            <?php if ($canEdit): ?>
            <a href="<?= url('admin/tupoksi/tambah') ?>" class="ult2-tp-empty-btn">
                <i class="bi bi-plus-lg"></i> Tambah Unit Pertama
            </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>

<!-- ===== TOOLBAR ===== -->
<div class="ult2-tp-toolbar">
    <div class="ult2-tp-searchbox">
        <i class="bi bi-search"></i>
        <input type="text" id="ult2TpSearch" placeholder="Cari unit kerja, jabatan, atau kode... (filter instan)">
    </div>
    <div class="ult2-tp-filters" id="ult2TpFilters">
        <button type="button" class="ult2-tp-chip active" data-status="all">
            <i class="bi bi-grid-fill"></i> Semua
            <span style="font-size:.68rem;background:rgba(0,0,0,.1);padding:.05rem .4rem;border-radius:20px;"><?= $totalItems ?></span>
        </button>
        <button type="button" class="ult2-tp-chip" data-status="1">
            <i class="bi bi-check-circle-fill" style="color:#10b981;"></i> Aktif
            <span style="font-size:.68rem;background:rgba(0,0,0,.1);padding:.05rem .4rem;border-radius:20px;"><?= $activeItems ?></span>
        </button>
        <button type="button" class="ult2-tp-chip" data-status="0">
            <i class="bi bi-pause-circle-fill" style="color:#64748b;"></i> Nonaktif
            <span style="font-size:.68rem;background:rgba(0,0,0,.1);padding:.05rem .4rem;border-radius:20px;"><?= $inactiveItems ?></span>
        </button>
    </div>
    <div class="ult2-tp-viewtoggle" id="ult2TpViewToggle">
        <button type="button" data-view="table" class="active" title="Tampilan Tabel"><i class="bi bi-list-ul"></i> Tabel</button>
        <button type="button" data-view="cards" title="Tampilan Kartu"><i class="bi bi-grid-3x3-gap"></i> Kartu</button>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="ult2-tp-card">
    
    <!-- TABLE VIEW -->
    <div id="ult2TpTable">
        <div style="overflow-x:auto;">
            <table class="ult2-tp-table">
                <thead>
                    <tr>
                        <th style="width:8%;">Urutan</th>
                        <th style="width:25%;">Unit Kerja</th>
                        <th style="width:12%;">Kode</th>
                        <th style="width:25%;">Nama Jabatan</th>
                        <th style="width:10%;">Status</th>
                        <?php if ($canEdit): ?>
                        <th style="width:20%;text-align:right;">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="ult2TpTbody">
                <?php 
                $sortedItems = $items;
                usort($sortedItems, function($a, $b) {
                    return (int)($a['sort_order'] ?? 0) - (int)($b['sort_order'] ?? 0);
                });
                
                foreach ($sortedItems as $t): 
                    $searchText = strtolower(($t['unit_kerja'] ?? '') . ' ' . ($t['nama_jabatan'] ?? '') . ' ' . ($t['kode_unit'] ?? ''));
                    $isActive = (int)$t['is_active'] === 1;
                ?>
                <tr data-status="<?= $isActive ? '1' : '0' ?>" data-search="<?= htmlspecialchars($searchText, ENT_QUOTES) ?>">
                    <td><span style="font-family:ui-monospace,monospace;font-weight:700;color:var(--admin-primary);"><?= (int)($t['sort_order'] ?? 0) ?></span></td>
                    <td>
                        <div class="ult2-tp-unit-cell">
                            <div class="ult2-tp-unit-icon" style="background:linear-gradient(135deg,<?= htmlspecialchars($t['warna'] ?? '#1a5276') ?>,<?= htmlspecialchars($t['warna'] ?? '#1a5276') ?>cc);">
                                <i class="bi <?= htmlspecialchars($t['icon'] ?? 'bi-briefcase') ?>"></i>
                            </div>
                            <span class="ult2-tp-unit-name"><?= htmlspecialchars($t['unit_kerja']) ?></span>
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($t['kode_unit'])): ?>
                            <span class="ult2-tp-kode"><?= htmlspecialchars($t['kode_unit']) ?></span>
                        <?php else: ?>
                            <span style="color:var(--admin-text-muted);font-size:.85rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="ult2-tp-jabatan"><?= htmlspecialchars($t['nama_jabatan']) ?></span></td>
                    <td>
                        <span class="ult2-tp-status <?= $isActive ? 'active' : 'inactive' ?>">
                            <i class="bi bi-<?= $isActive ? 'check-circle-fill' : 'pause-circle-fill' ?>"></i>
                            <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <?php if ($canEdit): ?>
                    <td>
                        <div class="ult2-tp-actions">
                            <a href="<?= url('admin/tupoksi/edit/' . $t['id']) ?>" class="ult2-tp-action-btn" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                            <a href="<?= url('admin/tupoksi/hapus/' . $t['id']) ?>" class="ult2-tp-action-btn danger" title="Hapus" onclick="return confirm('Yakin hapus unit \"<?= htmlspecialchars(addslashes($t['unit_kerja'])) ?>\"?');"><i class="bi bi-trash3-fill"></i></a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="ult2-tp-empty" id="ult2TpTableNoResult" style="display:none;padding:2rem;">
            <div class="ult2-tp-empty-icon" style="width:70px;height:70px;font-size:2rem;"><i class="bi bi-search"></i></div>
            <h3 style="font-size:1rem;">Tidak Ditemukan</h3>
            <p style="font-size:.85rem;">Tidak ada unit yang cocok dengan filter saat ini.</p>
        </div>
    </div>
    
    <!-- CARD VIEW -->
    <div id="ult2TpCards" class="ult2-tp-grid" style="display:none;">
        <?php foreach ($sortedItems as $t): 
            $searchText = strtolower(($t['unit_kerja'] ?? '') . ' ' . ($t['nama_jabatan'] ?? '') . ' ' . ($t['kode_unit'] ?? ''));
            $isActive = (int)$t['is_active'] === 1;
        ?>
        <div class="ult2-tp-grid-item" data-status="<?= $isActive ? '1' : '0' ?>" data-search="<?= htmlspecialchars($searchText, ENT_QUOTES) ?>">
            <div class="ult2-tp-grid-header" style="background:linear-gradient(135deg,<?= htmlspecialchars($t['warna'] ?? '#1a5276') ?>,<?= htmlspecialchars($t['warna'] ?? '#1a5276') ?>cc);">
                <div class="ult2-tp-grid-icon"><i class="bi <?= htmlspecialchars($t['icon'] ?? 'bi-briefcase') ?>"></i></div>
                <?php if (!empty($t['kode_unit'])): ?>
                <div class="ult2-tp-grid-kode"><?= htmlspecialchars($t['kode_unit']) ?></div>
                <?php endif; ?>
                <div class="ult2-tp-grid-unit"><?= htmlspecialchars($t['unit_kerja']) ?></div>
            </div>
            <div class="ult2-tp-grid-body">
                <div class="ult2-tp-grid-jabatan"><?= htmlspecialchars($t['nama_jabatan']) ?></div>
                <p class="ult2-tp-grid-tugas"><?= htmlspecialchars($t['tugas_pokok'] ?? 'Tidak ada deskripsi tugas pokok.') ?></p>
                <div class="ult2-tp-grid-meta">
                    <span class="ult2-tp-grid-sort">Urutan: <?= (int)($t['sort_order'] ?? 0) ?></span>
                    <?php if ($canEdit): ?>
                    <div class="ult2-tp-grid-actions">
                        <a href="<?= url('admin/tupoksi/edit/' . $t['id']) ?>" class="ult2-tp-action-btn" style="width:32px;height:32px;font-size:.85rem;" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                        <a href="<?= url('admin/tupoksi/hapus/' . $t['id']) ?>" class="ult2-tp-action-btn danger" style="width:32px;height:32px;font-size:.85rem;" title="Hapus" onclick="return confirm('Yakin hapus?');"><i class="bi bi-trash3-fill"></i></a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="ult2-tp-empty" id="ult2TpCardsNoResult" style="display:none;grid-column:1/-1;padding:2rem;">
            <div class="ult2-tp-empty-icon" style="width:70px;height:70px;font-size:2rem;"><i class="bi bi-search"></i></div>
            <h3 style="font-size:1rem;">Tidak Ditemukan</h3>
            <p style="font-size:.85rem;">Tidak ada unit yang cocok dengan filter saat ini.</p>
        </div>
    </div>
    
    <!-- ACTION BAR -->
    <?php if ($canEdit): ?>
    <div class="ult2-tp-actionbar">
        <div class="ult2-tp-actionbar-info">
            <i class="bi bi-info-circle-fill"></i>
            <span>Menampilkan <strong id="ult2TpVisibleCount"><?= $totalItems ?></strong> dari <?= $totalItems ?> unit</span>
        </div>
        <a href="<?= url('admin/tupoksi/tambah') ?>" class="ult2-tp-hero-btn ult2-tp-hero-btn-primary" style="padding:.6rem 1.2rem;font-size:.82rem;">
            <i class="bi bi-plus-lg"></i> Tambah Unit
        </a>
    </div>
    <?php endif; ?>
    
</div>
<?php endif; ?>

<!-- ===== SCRIPTS ===== -->
<script>
(function(){
    'use strict';
    
    // ===== VIEW TOGGLE =====
    var viewToggle = document.getElementById('ult2TpViewToggle');
    var tableView = document.getElementById('ult2TpTable');
    var cardsView = document.getElementById('ult2TpCards');
    
    function switchView(name) {
        if (tableView) tableView.style.display = name === 'table' ? '' : 'none';
        if (cardsView) cardsView.style.display = name === 'cards' ? '' : 'none';
        if (viewToggle) viewToggle.querySelectorAll('button').forEach(function(b){
            b.classList.toggle('active', b.getAttribute('data-view') === name);
        });
        try { localStorage.setItem('ult2_tp_view', name); } catch(e) {}
        applyFilters();
    }
    
    if (viewToggle) {
        viewToggle.querySelectorAll('button[data-view]').forEach(function(btn){
            btn.addEventListener('click', function(){ switchView(this.getAttribute('data-view')); });
        });
    }
    
    var storedView = null;
    try { storedView = localStorage.getItem('ult2_tp_view'); } catch(e) {}
    if (storedView === 'cards' || storedView === 'table') switchView(storedView);
    
    // ===== FILTERS =====
    var filters = document.getElementById('ult2TpFilters');
    var currentStatus = 'all';
    
    if (filters) {
        filters.querySelectorAll('.ult2-tp-chip').forEach(function(chip){
            chip.addEventListener('click', function(){
                filters.querySelectorAll('.ult2-tp-chip').forEach(function(c){ c.classList.remove('active'); });
                this.classList.add('active');
                currentStatus = this.getAttribute('data-status');
                applyFilters();
            });
        });
    }
    
    // ===== SEARCH =====
    var searchInput = document.getElementById('ult2TpSearch');
    var searchQuery = '';
    var searchTimer;
    
    if (searchInput) {
        searchInput.addEventListener('input', function(){
            clearTimeout(searchTimer);
            var val = this.value;
            searchTimer = setTimeout(function(){
                searchQuery = val.toLowerCase().trim();
                applyFilters();
            }, 120);
        });
    }
    
    // ===== APPLY FILTERS =====
    function applyFilters() {
        var q = searchQuery;
        var status = currentStatus;
        var visibleCount = 0;
        
        // Table rows
        document.querySelectorAll('#ult2TpTbody tr[data-status]').forEach(function(row){
            var rowStatus = row.getAttribute('data-status');
            var rowSearch = row.getAttribute('data-search') || '';
            var matchStatus = status === 'all' || rowStatus === status;
            var matchQ = q === '' || rowSearch.indexOf(q) !== -1;
            var show = matchStatus && matchQ;
            row.classList.toggle('hidden', !show);
            if (show) visibleCount++;
        });
        
        // Card items
        document.querySelectorAll('.ult2-tp-grid-item[data-status]').forEach(function(item){
            var itemStatus = item.getAttribute('data-status');
            var itemSearch = item.getAttribute('data-search') || '';
            var matchStatus = status === 'all' || itemStatus === status;
            var matchQ = q === '' || itemSearch.indexOf(q) !== -1;
            var show = matchStatus && matchQ;
            item.classList.toggle('hidden', !show);
            if (show) visibleCount++;
        });
        
        // No result messages
        var currentView = (cardsView && cardsView.style.display !== 'none') ? 'cards' : 'table';
        var tableNoResult = document.getElementById('ult2TpTableNoResult');
        var cardsNoResult = document.getElementById('ult2TpCardsNoResult');
        
        // Count visible in current view
        var viewVisible = 0;
        if (currentView === 'table') {
            document.querySelectorAll('#ult2TpTbody tr[data-status]:not(.hidden)').forEach(function(){ viewVisible++; });
            if (tableNoResult) tableNoResult.style.display = viewVisible === 0 ? '' : 'none';
        } else {
            document.querySelectorAll('.ult2-tp-grid-item[data-status]:not(.hidden)').forEach(function(){ viewVisible++; });
            if (cardsNoResult) cardsNoResult.style.display = viewVisible === 0 ? '' : 'none';
        }
        
        var vc = document.getElementById('ult2TpVisibleCount');
        if (vc) vc.textContent = viewVisible;
    }
    
    if (document.querySelectorAll('#ult2TpTbody tr').length > 0) {
        applyFilters();
    }
    
    // ===== KEYBOARD SHORTCUT: / for search =====
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            var tag = (e.target.tagName || '').toLowerCase();
            if (tag !== 'input' && tag !== 'textarea' && tag !== 'select' && !e.target.isContentEditable) {
                e.preventDefault();
                if (searchInput) searchInput.focus();
            }
        }
        if (e.key === 'Escape' && searchInput === document.activeElement) {
            searchInput.value = '';
            searchQuery = '';
            applyFilters();
            searchInput.blur();
        }
    });
})();
</script>