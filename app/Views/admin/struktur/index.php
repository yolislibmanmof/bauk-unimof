<?php
/**
 * =====================================================
 * BAUK UNIMOF - STRUKTUR ORGANISASI (ULTIMATE v2)
 * CRUD List dengan Table & Tree View
 * =====================================================
 */
use App\Helpers\Auth;

$canEdit = Auth::hasRole(['super_admin', 'admin_bauk']);
$items = $items ?? [];

// ===== STATS =====
$totalItems    = count($items);
$totalLevel1   = count(array_filter($items, fn($i) => (int)$i['level'] === 1));
$totalLevel2   = count(array_filter($items, fn($i) => (int)$i['level'] === 2));
$totalWithFoto = count(array_filter($items, fn($i) => !empty($i['foto']) && is_file(UPLOAD_DIR . 'struktur/' . $i['foto'])));
$maxLevel      = $totalItems > 0 ? max(array_map(fn($i) => (int)$i['level'], $items)) : 0;

// ===== GROUP BY LEVEL =====
$byLevel = [];
foreach ($items as $it) {
    $byLevel[(int)$it['level']][] = $it;
}
ksort($byLevel);

// ===== LEVEL COLORS =====
$levelColors = [
    1 => ['#1a5276', 'Pimpinan Tertinggi'],
    2 => ['#e67e22', 'Eselon II'],
    3 => ['#27ae60', 'Eselon III'],
    4 => ['#8e44ad', 'Eselon IV'],
];
function getLevelInfo(int $lvl): array {
    global $levelColors;
    return $levelColors[$lvl] ?? ['#64748b', 'Level ' . $lvl];
}
?>

<!-- ===== STRUKTUR v2 STYLES ===== -->
<style>
/* ===== HERO ===== */
.ult2-s-hero{background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 55%,#2980b9 100%);border-radius:20px;padding:2rem 2.25rem;color:#fff;position:relative;overflow:hidden;margin-bottom:1.5rem;}
.ult2-s-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='1.5'/%3E%3C/g%3E%3C/svg%3E");}
.ult2-s-hero::after{content:'';position:absolute;top:-60%;right:-8%;width:380px;height:380px;background:radial-gradient(circle,rgba(230,126,34,.25) 0%,transparent 70%);border-radius:50%;}
.ult2-s-hero-inner{position:relative;z-index:2;display:flex;justify-content:space-between;align-items:center;gap:1.5rem;flex-wrap:wrap;}
.ult2-s-hero-title{display:flex;align-items:center;gap:1rem;}
.ult2-s-hero-icon{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.14);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:1.7rem;flex-shrink:0;}
.ult2-s-hero h1{font-family:'Playfair Display',serif;font-size:1.65rem;margin:0 0 .25rem;color:#fff;}
.ult2-s-hero p{margin:0;font-size:.85rem;color:rgba(255,255,255,.85);}
.ult2-s-hero-actions{display:flex;gap:.6rem;flex-wrap:wrap;}
.ult2-s-hero-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.7rem 1.3rem;border-radius:12px;font-size:.86rem;font-weight:700;cursor:pointer;transition:all .25s;text-decoration:none!important;border:1px solid rgba(255,255,255,.25);}
.ult2-s-hero-btn-primary{background:linear-gradient(135deg,#e67e22,#f39c12);border-color:transparent;color:#fff;box-shadow:0 8px 22px rgba(230,126,34,.4);}
.ult2-s-hero-btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(230,126,34,.5);color:#fff;}
.ult2-s-hero-btn-ghost{background:rgba(255,255,255,.1);color:#fff;}
.ult2-s-hero-btn-ghost:hover{background:rgba(255,255,255,.2);color:#fff;}

/* ===== STATS ===== */
.ult2-s-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult2-s-stat{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;padding:1.15rem 1.25rem;display:flex;align-items:center;gap:.9rem;position:relative;overflow:hidden;transition:all .3s;}
.ult2-s-stat::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--sc,#3b82f6);}
.ult2-s-stat:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(0,0,0,.08);}
.ult2-s-stat-icon{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff;flex-shrink:0;}
.ult2-s-stat-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.6px;font-weight:700;color:var(--admin-text-muted);}
.ult2-s-stat-value{font-size:1.5rem;font-weight:800;color:var(--admin-text);line-height:1.15;}
.ult2-s-stat-sub{font-size:.7rem;color:var(--admin-text-muted);}

/* ===== TOOLBAR ===== */
.ult2-s-toolbar{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;padding:1rem 1.25rem;display:flex;gap:.85rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem;}
.ult2-s-searchbox{position:relative;flex:1;min-width:220px;}
.ult2-s-searchbox i{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);}
.ult2-s-searchbox input{width:100%;padding:.7rem 1rem .7rem 2.6rem;border:1.5px solid var(--admin-border);border-radius:12px;background:var(--admin-bg);color:var(--admin-text);font-size:.88rem;transition:all .2s;}
.ult2-s-searchbox input:focus{outline:none;border-color:var(--admin-primary);box-shadow:0 0 0 3px rgba(26,82,118,.1);background:var(--admin-surface);}
.ult2-s-filters{display:flex;gap:.4rem;flex-wrap:wrap;}
.ult2-s-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .9rem;border-radius:50px;border:1.5px solid var(--admin-border);background:var(--admin-surface);color:var(--admin-text-secondary);font-size:.78rem;font-weight:700;cursor:pointer;transition:all .2s;}
.ult2-s-chip:hover{border-color:var(--admin-primary);color:var(--admin-primary);}
.ult2-s-chip.active{background:var(--admin-primary);border-color:var(--admin-primary);color:#fff;box-shadow:0 6px 16px rgba(26,82,118,.3);}
.ult2-s-chip .ult2-s-chip-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.ult2-s-chip .ult2-s-chip-count{font-size:.68rem;background:rgba(0,0,0,.1);padding:.05rem .4rem;border-radius:20px;}
.ult2-s-chip.active .ult2-s-chip-count{background:rgba(255,255,255,.25);}
.ult2-s-viewtoggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:12px;overflow:hidden;}
.ult2-s-viewtoggle button{background:none;border:none;padding:.6rem .9rem;cursor:pointer;color:var(--admin-text-secondary);font-size:.82rem;font-weight:700;display:flex;align-items:center;gap:.4rem;transition:all .2s;}
.ult2-s-viewtoggle button.active{background:var(--admin-primary);color:#fff;}
.ult2-s-viewtoggle button:hover:not(.active){background:var(--admin-border);}

/* ===== CARD CONTAINER ===== */
.ult2-s-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;overflow:hidden;}

/* ===== TABLE VIEW ===== */
.ult2-s-table{width:100%;border-collapse:collapse;}
.ult2-s-table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;color:var(--admin-text-muted);text-align:left;padding:.85rem 1.4rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);font-weight:700;}
.ult2-s-table td{padding:1rem 1.4rem;border-bottom:1px solid var(--admin-border);vertical-align:middle;font-size:.88rem;color:var(--admin-text);}
.ult2-s-table tr:last-child td{border-bottom:none;}
.ult2-s-table tbody tr{transition:background .2s;}
.ult2-s-table tbody tr:hover{background:rgba(26,82,118,.03);}
.ult2-s-table tbody tr.hidden{display:none;}

.ult2-s-level{display:inline-flex;align-items:center;gap:.4rem;padding:.28rem .75rem;border-radius:50px;font-size:.72rem;font-weight:700;color:#fff;}
.ult2-s-level::before{content:'';width:6px;height:6px;border-radius:50%;background:#fff;flex-shrink:0;}

.ult2-s-jabatan-cell{display:flex;align-items:center;gap:.85rem;}
.ult2-s-avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#e6f0fa,#d4e4f5);display:flex;align-items:center;justify-content:center;color:#1a5276;font-family:'Playfair Display',serif;font-weight:700;font-size:1rem;flex-shrink:0;overflow:hidden;border:2px solid var(--admin-border);}
.ult2-s-avatar img{width:100%;height:100%;object-fit:cover;}
.ult2-s-avatar.leader{background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;border-color:rgba(230,126,34,.3);}
.ult2-s-jabatan-info{min-width:0;flex:1;}
.ult2-s-jabatan-name{font-weight:700;color:var(--admin-text);margin-bottom:.15rem;display:block;word-break:break-word;}
.ult2-s-jabatan-parent{font-size:.75rem;color:var(--admin-text-muted);display:flex;align-items:center;gap:.3rem;}
.ult2-s-jabatan-parent i{font-size:.65rem;color:var(--admin-primary);}

.ult2-s-pejabat-name{font-weight:600;color:var(--admin-text);}
.ult2-s-pejabat-empty{color:var(--admin-text-muted);font-style:italic;font-size:.85rem;}

.ult2-s-nip{font-family:ui-monospace,monospace;font-size:.78rem;background:rgba(26,82,118,.08);color:var(--admin-primary);padding:.25rem .6rem;border-radius:6px;font-weight:700;border:1px solid rgba(26,82,118,.12);display:inline-block;}

.ult2-s-foto-cell{display:flex;align-items:center;gap:.5rem;}
.ult2-s-foto-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:700;padding:.2rem .6rem;border-radius:50px;}
.ult2-s-foto-badge.has{background:rgba(16,185,129,.12);color:#059669;}
.ult2-s-foto-badge.no{background:rgba(100,116,139,.1);color:#64748b;}

.ult2-s-sort{font-family:ui-monospace,monospace;font-weight:700;color:var(--admin-primary);background:rgba(26,82,118,.08);padding:.2rem .6rem;border-radius:6px;font-size:.8rem;}

.ult2-s-actions{display:flex;gap:.4rem;justify-content:flex-end;}
.ult2-s-action-btn{width:36px;height:36px;border-radius:10px;border:1px solid var(--admin-border);background:var(--admin-surface);color:var(--admin-text-secondary);display:inline-flex;align-items:center;justify-content:center;font-size:.95rem;cursor:pointer;transition:all .2s;text-decoration:none!important;}
.ult2-s-action-btn:hover{background:var(--admin-primary);color:#fff;border-color:var(--admin-primary);transform:translateY(-2px);box-shadow:0 6px 16px rgba(26,82,118,.25);}
.ult2-s-action-btn.danger:hover{background:#ef4444;border-color:#ef4444;box-shadow:0 6px 16px rgba(239,68,68,.3);}
.ult2-s-action-btn.view{color:var(--admin-primary);}
.ult2-s-action-btn.view:hover{background:var(--admin-primary);color:#fff;}

/* ===== TREE VIEW ===== */
.ult2-s-tree{padding:2rem 1.5rem;}
.ult2-s-tree-level{margin-bottom:2.5rem;position:relative;}
.ult2-s-tree-level-header{display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:2px dashed var(--admin-border);}
.ult2-s-tree-level-num{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--lvl-color,#64748b),var(--lvl-color-dark,#475569));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;flex-shrink:0;box-shadow:0 6px 16px rgba(0,0,0,.12);}
.ult2-s-tree-level-title{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--admin-text);}
.ult2-s-tree-level-desc{font-size:.78rem;color:var(--admin-text-muted);}
.ult2-s-tree-level-count{margin-left:auto;font-size:.72rem;font-weight:700;background:var(--admin-bg);border:1px solid var(--admin-border);color:var(--admin-text-secondary);padding:.2rem .7rem;border-radius:20px;}

.ult2-s-tree-nodes{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;}
.ult2-s-tree-node{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;padding:1.25rem;transition:all .3s;position:relative;overflow:hidden;}
.ult2-s-tree-node::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:var(--lvl-color,#64748b);transform:scaleX(0);transform-origin:left;transition:transform .3s;}
.ult2-s-tree-node:hover{transform:translateY(-5px);box-shadow:0 15px 40px rgba(13,47,74,.12);border-color:var(--lvl-color,#64748b);}
.ult2-s-tree-node:hover::before{transform:scaleX(1);}
.ult2-s-tree-node-head{display:flex;align-items:center;gap:.75rem;margin-bottom:.85rem;padding-bottom:.85rem;border-bottom:1px dashed var(--admin-border);}
.ult2-s-tree-node-avatar{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#e6f0fa,#d4e4f5);display:flex;align-items:center;justify-content:center;color:#1a5276;font-family:'Playfair Display',serif;font-weight:700;font-size:1.2rem;flex-shrink:0;overflow:hidden;border:2px solid var(--admin-border);}
.ult2-s-tree-node-avatar img{width:100%;height:100%;object-fit:cover;}
.ult2-s-tree-node.leader .ult2-s-tree-node-avatar{background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;border-color:rgba(230,126,34,.3);}
.ult2-s-tree-node-info{flex:1;min-width:0;}
.ult2-s-tree-node-jabatan{font-weight:700;font-size:.9rem;color:var(--admin-text);margin-bottom:.15rem;word-break:break-word;}
.ult2-s-tree-node-pejabat{font-size:.78rem;color:var(--admin-text-secondary);display:flex;align-items:center;gap:.3rem;}
.ult2-s-tree-node.leader .ult2-s-tree-node-jabatan{font-size:1rem;}
.ult2-s-tree-node-body{display:flex;flex-direction:column;gap:.45rem;}
.ult2-s-tree-node-meta{display:flex;justify-content:space-between;align-items:center;font-size:.75rem;}
.ult2-s-tree-node-meta-label{color:var(--admin-text-muted);font-weight:600;}
.ult2-s-tree-node-meta-value{font-family:ui-monospace,monospace;color:var(--admin-primary);font-weight:700;}
.ult2-s-tree-node-actions{display:flex;gap:.35rem;margin-top:.85rem;padding-top:.85rem;border-top:1px dashed var(--admin-border);}
.ult2-s-tree-node-actions a{flex:1;}

/* ===== EMPTY STATE ===== */
.ult2-s-empty{padding:4rem 2rem;text-align:center;}
.ult2-s-empty-icon{width:100px;height:100px;margin:0 auto 1.2rem;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#1a5276;display:flex;align-items:center;justify-content:center;font-size:2.8rem;position:relative;}
.ult2-s-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(26,82,118,.3);border-radius:50%;animation:ult2-spin 18s linear infinite;}
@keyframes ult2-spin{to{transform:rotate(360deg);}}
.ult2-s-empty h3{margin:0 0 .35rem;font-size:1.2rem;color:var(--admin-text);}
.ult2-s-empty p{margin:0 0 1.25rem;color:var(--admin-text-secondary);font-size:.88rem;}
.ult2-s-empty-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.7rem 1.4rem;border-radius:12px;background:var(--admin-primary);color:#fff;text-decoration:none!important;font-weight:700;font-size:.88rem;transition:all .2s;}
.ult2-s-empty-btn:hover{background:#0d2f4a;transform:translateY(-2px);box-shadow:0 8px 20px rgba(26,82,118,.3);}

/* ===== ACTION BAR (sticky di bawah saat ada data) ===== */
.ult2-s-actionbar{position:sticky;bottom:0;background:var(--admin-surface);border-top:1px solid var(--admin-border);padding:.85rem 1.25rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-top:1rem;border-radius:16px;box-shadow:0 -4px 15px rgba(0,0,0,.05);border:1px solid var(--admin-border);}
.ult2-s-actionbar-info{font-size:.82rem;color:var(--admin-text-secondary);display:flex;align-items:center;gap:.5rem;}
.ult2-s-actionbar-info i{color:var(--admin-primary);}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult2-s-table th:nth-child(4),.ult2-s-table td:nth-child(4){display:none;}
}
@media (max-width:768px){
    .ult2-s-hero{padding:1.5rem;}
    .ult2-s-stats{grid-template-columns:repeat(2,1fr);}
    .ult2-s-toolbar{flex-direction:column;align-items:stretch;}
    .ult2-s-searchbox{min-width:100%;}
    .ult2-s-viewtoggle{align-self:flex-start;}
    .ult2-s-table th:nth-child(5),.ult2-s-table td:nth-child(5),
    .ult2-s-table th:nth-child(6),.ult2-s-table td:nth-child(6){display:none;}
    .ult2-s-jabatan-cell{flex-direction:column;align-items:flex-start;gap:.4rem;}
    .ult2-s-avatar{width:38px;height:38px;font-size:.9rem;}
    .ult2-s-actionbar{position:fixed;left:.5rem;right:.5rem;bottom:.5rem;margin:0;border-radius:14px;z-index:100;box-shadow:0 10px 30px rgba(0,0,0,.15);}
    body{padding-bottom:75px;}
}
@media (max-width:480px){
    .ult2-s-stats{grid-template-columns:1fr;}
    .ult2-s-tree-nodes{grid-template-columns:1fr;}
}
</style>

<!-- ===== HERO HEADER ===== -->
<div class="ult2-s-hero">
    <div class="ult2-s-hero-inner">
        <div class="ult2-s-hero-title">
            <div class="ult2-s-hero-icon"><i class="bi bi-diagram-3-fill"></i></div>
            <div>
                <h1>Struktur Organisasi</h1>
                <p>Kelola hierarki, jabatan & pejabat di BAUK UNIMOF</p>
            </div>
        </div>
        <div class="ult2-s-hero-actions">
            <a href="<?= url('struktur-organisasi') ?>" target="_blank" class="ult2-s-hero-btn ult2-s-hero-btn-ghost" title="Lihat halaman publik">
                <i class="bi bi-eye-fill"></i> Lihat Halaman Publik
            </a>
            <?php if ($canEdit): ?>
            <a href="<?= url('admin/struktur-organisasi/tambah') ?>" class="ult2-s-hero-btn ult2-s-hero-btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Jabatan
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== STATS CARDS ===== -->
<div class="ult2-s-stats">
    <div class="ult2-s-stat" style="--sc:#1a5276;">
        <div class="ult2-s-stat-icon" style="background:linear-gradient(135deg,#1a5276,#2980b9);"><i class="bi bi-diagram-3-fill"></i></div>
        <div><div class="ult2-s-stat-label">Total Jabatan</div><div class="ult2-s-stat-value"><?= number_format($totalItems) ?></div><div class="ult2-s-stat-sub">Seluruh struktur</div></div>
    </div>
    <div class="ult2-s-stat" style="--sc:#e67e22;">
        <div class="ult2-s-stat-icon" style="background:linear-gradient(135deg,#e67e22,#f39c12);"><i class="bi bi-person-badge-fill"></i></div>
        <div><div class="ult2-s-stat-label">Level Pimpinan</div><div class="ult2-s-stat-value"><?= number_format($totalLevel1) ?></div><div class="ult2-s-stat-sub">Jabatan tertinggi</div></div>
    </div>
    <div class="ult2-s-stat" style="--sc:#27ae60;">
        <div class="ult2-s-stat-icon" style="background:linear-gradient(135deg,#27ae60,#2ecc71);"><i class="bi bi-layers-fill"></i></div>
        <div><div class="ult2-s-stat-label">Kedalaman Level</div><div class="ult2-s-stat-value"><?= $maxLevel ?></div><div class="ult2-s-stat-sub">Level terdalam</div></div>
    </div>
    <div class="ult2-s-stat" style="--sc:#8b5cf6;">
        <div class="ult2-s-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-image-fill"></i></div>
        <div><div class="ult2-s-stat-label">Dengan Foto</div><div class="ult2-s-stat-value"><?= number_format($totalWithFoto) ?></div><div class="ult2-s-stat-sub"><?= $totalItems > 0 ? round($totalWithFoto / $totalItems * 100) : 0 ?>% terisi</div></div>
    </div>
</div>

<!-- ===== TOOLBAR ===== -->
<div class="ult2-s-toolbar">
    <div class="ult2-s-searchbox">
        <i class="bi bi-search"></i>
        <input type="text" id="ult2StrukturSearch" placeholder="Cari jabatan, nama pejabat, atau NIP... (filter instan)">
    </div>
    <div class="ult2-s-filters" id="ult2StrukturFilters">
        <button type="button" class="ult2-s-chip active" data-level="all">
            <i class="bi bi-grid-fill"></i> Semua
            <span class="ult2-s-chip-count"><?= $totalItems ?></span>
        </button>
        <?php foreach (array_keys($byLevel) as $lvl): 
            [$color, $label] = getLevelInfo($lvl);
            $count = count($byLevel[$lvl]);
        ?>
        <button type="button" class="ult2-s-chip" data-level="<?= $lvl ?>">
            <span class="ult2-s-chip-dot" style="background:<?= $color ?>;"></span>
            Level <?= $lvl ?>
            <span class="ult2-s-chip-count"><?= $count ?></span>
        </button>
        <?php endforeach; ?>
    </div>
    <div class="ult2-s-viewtoggle" id="ult2StrukturViewToggle">
        <button type="button" data-view="table" class="active" title="Tampilan Tabel"><i class="bi bi-list-ul"></i> Tabel</button>
        <button type="button" data-view="tree" title="Tampilan Tree"><i class="bi bi-diagram-3"></i> Tree</button>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="ult2-s-card">
    <?php if (empty($items)): ?>
        <!-- EMPTY STATE -->
        <div class="ult2-s-empty">
            <div class="ult2-s-empty-icon"><i class="bi bi-diagram-3"></i></div>
            <h3>Struktur Organisasi Masih Kosong</h3>
            <p>Belum ada jabatan yang ditambahkan. Mulai dengan menambahkan jabatan puncak.</p>
            <?php if ($canEdit): ?>
            <a href="<?= url('admin/struktur-organisasi/tambah') ?>" class="ult2-s-empty-btn">
                <i class="bi bi-plus-lg"></i> Tambah Jabatan Pertama
            </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ult2StrukturTable">
            <div style="overflow-x:auto;">
                <table class="ult2-s-table">
                    <thead>
                        <tr>
                            <th style="width:10%;">Level</th>
                            <th style="width:28%;">Nama Jabatan</th>
                            <th style="width:22%;">Nama Pejabat</th>
                            <th style="width:13%;">NIP</th>
                            <th style="width:10%;">Foto</th>
                            <th style="width:7%;">Urutan</th>
                            <?php if ($canEdit): ?>
                            <th style="width:10%;text-align:right;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="ult2StrukturTbody">
                    <?php 
                    // Flatten untuk tabel (urutan level, sort_order)
                    $flat = $items;
                    usort($flat, function($a, $b) {
                        if ((int)$a['level'] !== (int)$b['level']) return (int)$a['level'] - (int)$b['level'];
                        return (int)($a['sort_order'] ?? 0) - (int)($b['sort_order'] ?? 0);
                    });
                    
                    // Parent lookup
                    $parentMap = [];
                    foreach ($items as $it) $parentMap[(int)$it['id']] = $it['nama_jabatan'];
                    
                    foreach ($flat as $it): 
                        $lvl = (int)$it['level'];
                        [$lvlColor, $lvlLabel] = getLevelInfo($lvl);
                        $hasFoto = !empty($it['foto']) && is_file(UPLOAD_DIR . 'struktur/' . $it['foto']);
                        $fotoUrl = $hasFoto ? url('uploads/struktur/' . $it['foto']) : '';
                        $initial = !empty($it['nama_pejabat']) ? strtoupper(substr($it['nama_pejabat'], 0, 1)) : strtoupper(substr($it['nama_jabatan'], 0, 1));
                        $parentName = !empty($it['parent_id']) && isset($parentMap[(int)$it['parent_id']]) ? $parentMap[(int)$it['parent_id']] : '';
                        
                        // Search text untuk filter
                        $searchText = strtolower(($it['nama_jabatan'] ?? '') . ' ' . ($it['nama_pejabat'] ?? '') . ' ' . ($it['nip'] ?? ''));
                    ?>
                    <tr data-level="<?= $lvl ?>" data-search="<?= htmlspecialchars($searchText, ENT_QUOTES) ?>">
                        <td>
                            <span class="ult2-s-level" style="background:<?= $lvlColor ?>;">
                                L<?= $lvl ?>
                            </span>
                        </td>
                        <td>
                            <div class="ult2-s-jabatan-cell">
                                <div class="ult2-s-avatar <?= $lvl === 1 ? 'leader' : '' ?>">
                                    <?php if ($hasFoto): ?>
                                        <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="">
                                    <?php else: ?>
                                        <?= $initial ?>
                                    <?php endif; ?>
                                </div>
                                <div class="ult2-s-jabatan-info">
                                    <span class="ult2-s-jabatan-name"><?= htmlspecialchars($it['nama_jabatan']) ?></span>
                                    <?php if ($parentName): ?>
                                    <span class="ult2-s-jabatan-parent"><i class="bi bi-arrow-return-right"></i> Melapor ke: <?= htmlspecialchars($parentName) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($it['nama_pejabat'])): ?>
                                <span class="ult2-s-pejabat-name"><?= htmlspecialchars($it['nama_pejabat']) ?></span>
                            <?php else: ?>
                                <span class="ult2-s-pejabat-empty">— belum diisi —</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($it['nip'])): ?>
                                <span class="ult2-s-nip"><?= htmlspecialchars($it['nip']) ?></span>
                            <?php else: ?>
                                <span style="color:var(--admin-text-muted);font-size:.85rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($hasFoto): ?>
                                <span class="ult2-s-foto-badge has"><i class="bi bi-check-circle-fill"></i> Ada</span>
                            <?php else: ?>
                                <span class="ult2-s-foto-badge no"><i class="bi bi-x-circle"></i> Tidak</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="ult2-s-sort"><?= (int)($it['sort_order'] ?? 0) ?></span>
                        </td>
                        <?php if ($canEdit): ?>
                        <td>
                            <div class="ult2-s-actions">
                                <a href="<?= url('admin/struktur-organisasi/edit/' . $it['id']) ?>" class="ult2-s-action-btn view" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="<?= url('admin/struktur-organisasi/hapus/' . $it['id']) ?>" class="ult2-s-action-btn danger" title="Hapus" onclick="return confirm('Yakin hapus jabatan \"<?= htmlspecialchars(addslashes($it['nama_jabatan'])) ?>\"?');">
                                    <i class="bi bi-trash3-fill"></i>
                                </a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Table empty result (ketika filter tidak match) -->
            <div class="ult2-s-empty" id="ult2StrukturNoResult" style="display:none;">
                <div class="ult2-s-empty-icon"><i class="bi bi-search"></i></div>
                <h3>Tidak Ditemukan</h3>
                <p>Tidak ada jabatan yang cocok dengan filter saat ini.</p>
            </div>
        </div>
        
        <!-- ===== TREE VIEW ===== -->
        <div id="ult2StrukturTree" class="ult2-s-tree" style="display:none;">
            <?php foreach ($byLevel as $lvl => $nodes): 
                [$lvlColor, $lvlLabel] = getLevelInfo($lvl);
                $lvlColorDark = $lvl === 1 ? '#0d2f4a' : ($lvl === 2 ? '#c2610a' : ($lvl === 3 ? '#1e8449' : '#6c3483'));
            ?>
            <div class="ult2-s-tree-level" data-level="<?= $lvl ?>" style="--lvl-color:<?= $lvlColor ?>;--lvl-color-dark:<?= $lvlColorDark ?>;">
                <div class="ult2-s-tree-level-header">
                    <div class="ult2-s-tree-level-num">L<?= $lvl ?></div>
                    <div>
                        <div class="ult2-s-tree-level-title">Level <?= $lvl ?> — <?= $lvlLabel ?></div>
                        <div class="ult2-s-tree-level-desc"><?= count($nodes) ?> jabatan di level ini</div>
                    </div>
                    <span class="ult2-s-tree-level-count"><?= count($nodes) ?> jabatan</span>
                </div>
                <div class="ult2-s-tree-nodes">
                    <?php foreach ($nodes as $it): 
                        $hasFoto = !empty($it['foto']) && is_file(UPLOAD_DIR . 'struktur/' . $it['foto']);
                        $fotoUrl = $hasFoto ? url('uploads/struktur/' . $it['foto']) : '';
                        $initial = !empty($it['nama_pejabat']) ? strtoupper(substr($it['nama_pejabat'], 0, 1)) : strtoupper(substr($it['nama_jabatan'], 0, 1));
                        $searchText = strtolower(($it['nama_jabatan'] ?? '') . ' ' . ($it['nama_pejabat'] ?? '') . ' ' . ($it['nip'] ?? ''));
                    ?>
                    <div class="ult2-s-tree-node <?= $lvl === 1 ? 'leader' : '' ?>" data-level="<?= $lvl ?>" data-search="<?= htmlspecialchars($searchText, ENT_QUOTES) ?>">
                        <div class="ult2-s-tree-node-head">
                            <div class="ult2-s-tree-node-avatar">
                                <?php if ($hasFoto): ?>
                                    <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="">
                                <?php else: ?>
                                    <?= $initial ?>
                                <?php endif; ?>
                            </div>
                            <div class="ult2-s-tree-node-info">
                                <div class="ult2-s-tree-node-jabatan"><?= htmlspecialchars($it['nama_jabatan']) ?></div>
                                <div class="ult2-s-tree-node-pejabat">
                                    <?php if (!empty($it['nama_pejabat'])): ?>
                                        <i class="bi bi-person-fill"></i> <?= htmlspecialchars($it['nama_pejabat']) ?>
                                    <?php else: ?>
                                        <i class="bi bi-person-dash"></i> Belum diisi
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="ult2-s-tree-node-body">
                            <?php if (!empty($it['nip'])): ?>
                            <div class="ult2-s-tree-node-meta">
                                <span class="ult2-s-tree-node-meta-label">NIP</span>
                                <span class="ult2-s-tree-node-meta-value"><?= htmlspecialchars($it['nip']) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="ult2-s-tree-node-meta">
                                <span class="ult2-s-tree-node-meta-label">Urutan</span>
                                <span class="ult2-s-tree-node-meta-value">#<?= (int)($it['sort_order'] ?? 0) ?></span>
                            </div>
                            <div class="ult2-s-tree-node-meta">
                                <span class="ult2-s-tree-node-meta-label">Foto</span>
                                <?php if ($hasFoto): ?>
                                    <span style="color:#059669;font-weight:700;font-size:.75rem;"><i class="bi bi-check-circle-fill"></i> Tersedia</span>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-weight:600;font-size:.75rem;">Belum ada</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($canEdit): ?>
                        <div class="ult2-s-tree-node-actions">
                            <a href="<?= url('admin/struktur-organisasi/edit/' . $it['id']) ?>" class="ult2-s-action-btn view" title="Edit" style="flex:1;width:auto;padding:.5rem;">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <a href="<?= url('admin/struktur-organisasi/hapus/' . $it['id']) ?>" class="ult2-s-action-btn danger" title="Hapus" style="flex:1;width:auto;padding:.5rem;" onclick="return confirm('Yakin hapus jabatan ini?');">
                                <i class="bi bi-trash3-fill"></i>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Tree no result -->
            <div class="ult2-s-empty" id="ult2StrukturTreeNoResult" style="display:none;">
                <div class="ult2-s-empty-icon"><i class="bi bi-search"></i></div>
                <h3>Tidak Ditemukan</h3>
                <p>Tidak ada jabatan yang cocok dengan filter saat ini.</p>
            </div>
        </div>
        
        <!-- ===== ACTION BAR ===== -->
        <?php if ($canEdit): ?>
        <div class="ult2-s-actionbar">
            <div class="ult2-s-actionbar-info">
                <i class="bi bi-info-circle-fill"></i>
                <span>Menampilkan <strong id="ult2StrukturVisibleCount"><?= $totalItems ?></strong> dari <?= $totalItems ?> jabatan</span>
            </div>
            <div style="display:flex;gap:.6rem;">
                <a href="<?= url('admin/struktur-organisasi/tambah') ?>" class="ult2-s-hero-btn ult2-s-hero-btn-primary" style="padding:.6rem 1.2rem;font-size:.82rem;">
                    <i class="bi bi-plus-lg"></i> Tambah Jabatan
                </a>
            </div>
        </div>
        <?php endif; ?>
    
    <?php endif; ?>
</div>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function(){
    'use strict';
    
    // ===== VIEW TOGGLE =====
    var viewToggle = document.getElementById('ult2StrukturViewToggle');
    var tableView = document.getElementById('ult2StrukturTable');
    var treeView = document.getElementById('ult2StrukturTree');
    
    function switchView(name) {
        if (tableView) tableView.style.display = name === 'table' ? '' : 'none';
        if (treeView) treeView.style.display = name === 'tree' ? '' : 'none';
        if (viewToggle) viewToggle.querySelectorAll('button').forEach(function(b){
            b.classList.toggle('active', b.getAttribute('data-view') === name);
        });
        try { localStorage.setItem('ult2_struktur_view', name); } catch(e) {}
        applyFilters(); // re-apply filter setelah switch view
    }
    
    if (viewToggle) {
        viewToggle.querySelectorAll('button[data-view]').forEach(function(btn){
            btn.addEventListener('click', function(){ switchView(this.getAttribute('data-view')); });
        });
    }
    
    var storedView = null;
    try { storedView = localStorage.getItem('ult2_struktur_view'); } catch(e) {}
    if (storedView === 'tree' || storedView === 'table') switchView(storedView);
    
    // ===== FILTER LEVEL CHIPS =====
    var filters = document.getElementById('ult2StrukturFilters');
    var currentLevel = 'all';
    
    if (filters) {
        filters.querySelectorAll('.ult2-s-chip').forEach(function(chip){
            chip.addEventListener('click', function(){
                filters.querySelectorAll('.ult2-s-chip').forEach(function(c){ c.classList.remove('active'); });
                this.classList.add('active');
                currentLevel = this.getAttribute('data-level');
                applyFilters();
            });
        });
    }
    
    // ===== SEARCH =====
    var searchInput = document.getElementById('ult2StrukturSearch');
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
        var lvl = currentLevel;
        var visibleCount = 0;
        
        // Table rows
        document.querySelectorAll('#ult2StrukturTbody tr[data-level]').forEach(function(row){
            var rowLvl = row.getAttribute('data-level');
            var rowSearch = row.getAttribute('data-search') || '';
            var matchLvl = lvl === 'all' || rowLvl === lvl;
            var matchQ = q === '' || rowSearch.indexOf(q) !== -1;
            var show = matchLvl && matchQ;
            row.classList.toggle('hidden', !show);
            if (show) visibleCount++;
        });
        
        // Tree nodes
        document.querySelectorAll('.ult2-s-tree-level').forEach(function(levelBlock){
            var blockLvl = levelBlock.getAttribute('data-level');
            var blockVisible = false;
            levelBlock.querySelectorAll('.ult2-s-tree-node').forEach(function(node){
                var nodeLvl = node.getAttribute('data-level');
                var nodeSearch = node.getAttribute('data-search') || '';
                var matchLvl = lvl === 'all' || nodeLvl === lvl;
                var matchQ = q === '' || nodeSearch.indexOf(q) !== -1;
                var show = matchLvl && matchQ;
                node.style.display = show ? '' : 'none';
                if (show) blockVisible = true;
            });
            levelBlock.style.display = blockVisible ? '' : 'none';
            if (blockVisible && lvl === 'all') {
                // count dari nodes visible
                var c = levelBlock.querySelectorAll('.ult2-s-tree-node[style=""],.ult2-s-tree-node:not([style])').length;
            }
        });
        
        // No result message
        var tableNoResult = document.getElementById('ult2StrukturNoResult');
        var treeNoResult = document.getElementById('ult2StrukturTreeNoResult');
        var currentView = (treeView && treeView.style.display !== 'none') ? 'tree' : 'table';
        
        if (tableNoResult) tableNoResult.style.display = (currentView === 'table' && visibleCount === 0) ? '' : 'none';
        if (treeNoResult) treeNoResult.style.display = (currentView === 'tree' && visibleCount === 0) ? '' : 'none';
        
        // Update visible count
        var vc = document.getElementById('ult2StrukturVisibleCount');
        if (vc) vc.textContent = visibleCount;
    }
    
    // Init
    if (document.querySelectorAll('#ult2StrukturTbody tr').length > 0) {
        applyFilters();
    }
    
    // ===== KEYBOARD SHORTCUT: / untuk focus search =====
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