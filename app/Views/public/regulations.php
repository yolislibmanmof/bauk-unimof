<?php
/**
 * BAUK UNIMOF - Halaman Regulasi ULTIMATE EDITION
 * Pusat dokumen regulasi dengan filter, search, dan statistik
 */
$db = \App\Helpers\Database::getInstance();

// ===== FILTER PARAMS =====
$q      = trim($_GET['q'] ?? '');
$cat    = $_GET['category'] ?? 'all';
$year   = $_GET['year'] ?? 'all';
$sort   = $_GET['sort'] ?? 'newest';

$where  = "status = 'active'";
$params = [];

if ($cat !== 'all') { $where .= " AND category = ?"; $params[] = $cat; }
if ($year !== 'all' && ctype_digit($year)) { $where .= " AND YEAR(effective_date) = ?"; $params[] = (int)$year; }
if ($q !== '') {
    $where .= " AND (title LIKE ? OR document_number LIKE ? OR description LIKE ?)";
    $like = '%' . $q . '%';
    $params = array_merge($params, [$like, $like, $like]);
}

$orderMap = [
    'newest'  => 'effective_date DESC',
    'oldest'  => 'effective_date ASC',
    'title'   => 'title ASC',
    'number'  => 'document_number ASC',
];
$order = $orderMap[$sort] ?? 'effective_date DESC';

try {
    $dokumen = $db->fetchAll("SELECT * FROM dokumen_regulasi WHERE {$where} ORDER BY {$order} LIMIT 100", $params);
    
    // Kategori & tahun tersedia
    $categories = $db->fetchAll("SELECT category, COUNT(*) AS total FROM dokumen_regulasi WHERE status = 'active' GROUP BY category ORDER BY total DESC");
    $years      = $db->fetchAll("SELECT DISTINCT YEAR(effective_date) AS y FROM dokumen_regulasi WHERE status = 'active' ORDER BY y DESC");
    
    // Statistik
    $stats = [
        'total'    => $db->count('dokumen_regulasi', "status = 'active'"),
        'active'   => $db->count('dokumen_regulasi', "status = 'active'"),
        'cats'     => count($categories),
        'thisYear' => $db->count('dokumen_regulasi', "status = 'active' AND YEAR(effective_date) = YEAR(CURRENT_DATE())"),
    ];
} catch (\Throwable $e) {
    $dokumen = []; $categories = []; $years = [];
    $stats = ['total' => 0, 'active' => 0, 'cats' => 0, 'thisYear' => 0];
}

// Helper ikon & warna per tipe file
function ult_file_meta(?string $path): array {
    $ext = strtolower(pathinfo((string)$path, PATHINFO_EXTENSION));
    return match ($ext) {
        'pdf'  => ['bi-file-earmark-pdf-fill',  '#dc2626', 'PDF'],
        'doc', 'docx' => ['bi-file-earmark-word-fill', '#2563eb', 'DOC'],
        'xls', 'xlsx', 'csv' => ['bi-file-earmark-excel-fill', '#16a34a', 'XLS'],
        'ppt', 'pptx' => ['bi-file-earmark-ppt-fill', '#ea580c', 'PPT'],
        'zip', 'rar' => ['bi-file-earmark-zip-fill', '#7c3aed', 'ZIP'],
        default => ['bi-file-earmark-text-fill', '#64748b', 'DOC'],
    };
}

// Helper ikon per kategori
function ult_cat_icon(string $cat): string {
    return match ($cat) {
        'peraturan', 'regulasi' => 'bi-journal-bookmark-fill',
        'keputusan'  => 'bi-award-fill',
        'surat_edaran', 'edaran' => 'bi-envelope-paper-fill',
        'sop', 'prosedur' => 'bi-list-check',
        'pedoman', 'panduan' => 'bi-book-fill',
        default => 'bi-file-earmark-text-fill',
    };
}

// Helper status badge
function ult_status_meta(string $status): array {
    return match ($status) {
        'active'   => ['Berlaku', '#d1fae5', '#065f46'],
        'revised'  => ['Direvisi', '#fef3c7', '#92400e'],
        default    => ['Tidak Berlaku', '#fee2e2', '#991b1b'],
    };
}
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== HERO ===== */
.ult-r-hero{position:relative;background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 50%,#2980b9 100%);color:#fff;padding:4.5rem 0 7rem;overflow:hidden;}
.ult-r-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/svg%3E");z-index:1;}
.ult-r-hero-shape{position:absolute;border-radius:50%;z-index:1;}
.ult-r-hero-shape-1{width:420px;height:420px;top:-120px;right:-100px;background:radial-gradient(circle,rgba(230,126,34,.25) 0%,transparent 70%);animation:ult_r_float 18s infinite ease-in-out;}
.ult-r-hero-shape-2{width:320px;height:320px;bottom:-100px;left:-80px;background:radial-gradient(circle,rgba(39,174,96,.2) 0%,transparent 70%);animation:ult_r_float 14s infinite ease-in-out reverse;}
@keyframes ult_r_float{0%,100%{transform:translate(0,0);}50%{transform:translate(35px,-35px);}}
.ult-r-hero-content{position:relative;z-index:2;text-align:center;}
.ult-r-hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.12);backdrop-filter:blur(10px);padding:.5rem 1.25rem;border-radius:50px;font-size:.85rem;margin-bottom:1.25rem;border:1px solid rgba(255,255,255,.2);}
.ult-r-hero h1{font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3.25rem);font-weight:700;margin-bottom:1rem;}
.ult-r-hero p{font-size:1.05rem;max-width:680px;margin:0 auto 2.5rem;line-height:1.8;color:rgba(255,255,255,.9);}
.ult-r-hero-stats{display:flex;justify-content:center;gap:1.25rem;flex-wrap:wrap;}
.ult-r-hero-stat{background:rgba(255,255,255,.1);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.2);border-radius:16px;padding:1.25rem 2rem;min-width:150px;transition:all .3s;}
.ult-r-hero-stat:hover{background:rgba(255,255,255,.18);transform:translateY(-3px);}
.ult-r-hero-stat-value{font-family:'Playfair Display',serif;font-size:2.25rem;font-weight:700;color:#f39c12;line-height:1;}
.ult-r-hero-stat-label{font-size:.8rem;margin-top:.375rem;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:1px;}
.ult-r-hero-wave{position:absolute;bottom:-1px;left:0;right:0;z-index:3;}
.ult-r-hero-wave svg{display:block;width:100%;}

/* ===== TOOLBAR ===== */
.ult-r-toolbar{background:#fff;padding:1.25rem 0;box-shadow:0 4px 20px rgba(0,0,0,.06);position:sticky;top:0;z-index:60;}
.ult-r-toolbar-form{display:grid;grid-template-columns:1fr auto auto auto;gap:.75rem;align-items:center;}
.ult-r-search{position:relative;}
.ult-r-search i{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--color-text-muted);}
.ult-r-search input{width:100%;padding:.8rem 1rem .8rem 2.75rem;border:2px solid var(--color-border);border-radius:12px;font-size:.9rem;background:var(--color-bg);}
.ult-r-search input:focus{outline:none;border-color:var(--color-primary);background:#fff;}
.ult-r-select{padding:.8rem 1rem;border:2px solid var(--color-border);border-radius:12px;font-size:.875rem;background:#fff;cursor:pointer;font-weight:500;min-width:150px;}
.ult-r-select:focus{outline:none;border-color:var(--color-primary);}
.ult-r-reset{padding:.8rem 1.125rem;border:2px solid var(--color-border);border-radius:12px;background:#fff;color:var(--color-text-secondary);cursor:pointer;font-size:.875rem;font-weight:600;display:inline-flex;align-items:center;gap:.375rem;transition:all .2s;}
.ult-r-reset:hover{border-color:var(--color-danger);color:var(--color-danger);}

/* ===== CATEGORY TABS ===== */
.ult-r-tabs{padding:1.5rem 0 .5rem;overflow-x:auto;}
.ult-r-tabs-wrap{display:flex;gap:.625rem;flex-wrap:wrap;}
.ult-r-tab{display:inline-flex;align-items:center;gap:.5rem;padding:.625rem 1.25rem;background:#fff;border:2px solid var(--color-border-light);border-radius:50px;font-size:.85rem;font-weight:600;color:var(--color-text-secondary);transition:all .2s;}
.ult-r-tab:hover{border-color:var(--color-primary);color:var(--color-primary);}
.ult-r-tab.active{background:var(--color-primary);border-color:var(--color-primary);color:#fff;}
.ult-r-tab-count{background:rgba(0,0,0,.08);padding:.125rem .5rem;border-radius:50px;font-size:.72rem;font-weight:700;}
.ult-r-tab.active .ult-r-tab-count{background:rgba(255,255,255,.25);}

/* ===== DOCUMENT LIST ===== */
.ult-r-list{display:flex;flex-direction:column;gap:1rem;margin-top:1.5rem;}
.ult-r-item{display:grid;grid-template-columns:auto 1fr auto;gap:1.5rem;align-items:center;background:#fff;border-radius:16px;padding:1.5rem;box-shadow:0 4px 20px rgba(0,0,0,.05);border:1px solid var(--color-border-light);border-left:4px solid var(--color-primary);transition:all .3s;}
.ult-r-item:hover{transform:translateX(5px);box-shadow:0 10px 30px rgba(0,0,0,.1);}
.ult-r-file-icon{width:64px;height:64px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.75rem;color:#fff;position:relative;flex-shrink:0;}
.ult-r-file-ext{position:absolute;bottom:-6px;right:-6px;background:#1e293b;color:#fff;font-size:.6rem;font-weight:800;padding:.125rem .4rem;border-radius:6px;letter-spacing:.5px;}
.ult-r-info h3{font-size:1.05rem;font-weight:700;margin-bottom:.375rem;line-height:1.4;}
.ult-r-info h3 a{color:var(--color-text);transition:color .2s;}
.ult-r-info h3 a:hover{color:var(--color-primary);}
.ult-r-meta{display:flex;flex-wrap:wrap;gap:.875rem;font-size:.8rem;color:var(--color-text-muted);align-items:center;}
.ult-r-meta span{display:inline-flex;align-items:center;gap:.3rem;}
.ult-r-meta i{font-size:.8rem;}
.ult-r-meta .ult-r-cat{background:var(--color-primary-50);color:var(--color-primary);padding:.125rem .625rem;border-radius:50px;font-weight:600;}
.ult-r-status{padding:.2rem .7rem;border-radius:50px;font-size:.72rem;font-weight:700;}
.ult-r-desc{font-size:.85rem;color:var(--color-text-secondary);margin-top:.5rem;line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.ult-r-actions{display:flex;flex-direction:column;gap:.5rem;align-items:stretch;min-width:150px;}
.ult-r-download{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.7rem 1.25rem;background:linear-gradient(135deg,var(--color-primary),var(--color-primary-light));color:#fff!important;border-radius:10px;font-size:.85rem;font-weight:700;transition:all .2s;box-shadow:0 5px 15px rgba(26,82,118,.25);}
.ult-r-download:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(26,82,118,.35);}
.ult-r-detail{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem 1rem;background:var(--color-bg);color:var(--color-text-secondary)!important;border:1px solid var(--color-border);border-radius:10px;font-size:.8rem;font-weight:600;transition:all .2s;}
.ult-r-detail:hover{border-color:var(--color-primary);color:var(--color-primary)!important;}
.ult-r-nofile{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.7rem 1rem;background:var(--color-bg);color:var(--color-text-muted)!important;border:1px dashed var(--color-border);border-radius:10px;font-size:.8rem;}

/* ===== RESULT HEADER ===== */
.ult-r-result-head{display:flex;justify-content:space-between;align-items:center;margin-top:2rem;flex-wrap:wrap;gap:.75rem;}
.ult-r-result-head h3{font-family:'Playfair Display',serif;font-size:1.35rem;display:flex;align-items:center;gap:.5rem;}
.ult-r-result-head h3 i{color:var(--color-primary);}
.ult-r-result-count{font-size:.875rem;color:var(--color-text-secondary);}
.ult-r-result-count strong{color:var(--color-primary);}

/* ===== EMPTY STATE ===== */
.ult-r-empty{text-align:center;padding:4rem 2rem;background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,.05);margin-top:1.5rem;}
.ult-r-empty i{font-size:4.5rem;color:var(--color-border);margin-bottom:1.25rem;}
.ult-r-empty h3{font-size:1.35rem;margin-bottom:.5rem;}
.ult-r-empty p{color:var(--color-text-secondary);margin-bottom:1.5rem;max-width:420px;margin-left:auto;margin-right:auto;}

/* ===== ALUR PERMOHONAN ===== */
.ult-r-flow{background:var(--color-bg);padding:4rem 0;margin-top:4rem;}
.ult-r-flow-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1.5rem;margin-top:2.5rem;counter-reset:flow;}
.ult-r-flow-card{background:#fff;border-radius:16px;padding:2rem 1.5rem;text-align:center;position:relative;counter-increment:flow;box-shadow:0 4px 20px rgba(0,0,0,.05);transition:all .3s;border:1px solid var(--color-border-light);}
.ult-r-flow-card:hover{transform:translateY(-6px);box-shadow:0 12px 35px rgba(0,0,0,.1);}
.ult-r-flow-card::before{content:counter(flow,decimal-leading-zero);position:absolute;top:1rem;right:1.25rem;font-family:'Playfair Display',serif;font-size:2.25rem;font-weight:700;color:var(--color-primary-100);line-height:1;}
.ult-r-flow-icon{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--color-primary-50),var(--color-primary-100));color:var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin:0 auto 1.25rem;}
.ult-r-flow-card h4{font-size:1.05rem;font-weight:700;margin-bottom:.5rem;}
.ult-r-flow-card p{font-size:.85rem;color:var(--color-text-secondary);line-height:1.6;margin:0;}

/* ===== CTA ===== */
.ult-r-cta{background:linear-gradient(135deg,var(--color-primary-dark),var(--color-primary));color:#fff;padding:3.5rem 0;text-align:center;position:relative;overflow:hidden;}
.ult-r-cta::before{content:'';position:absolute;top:-50%;right:-10%;width:350px;height:350px;background:radial-gradient(circle,rgba(230,126,34,.2) 0%,transparent 70%);border-radius:50%;}
.ult-r-cta-content{position:relative;z-index:1;max-width:640px;margin:0 auto;padding:0 1.5rem;}
.ult-r-cta h2{font-family:'Playfair Display',serif;font-size:1.875rem;margin-bottom:.75rem;}
.ult-r-cta p{color:rgba(255,255,255,.85);margin-bottom:1.75rem;line-height:1.7;}
.ult-r-cta-actions{display:flex;gap:.875rem;justify-content:center;flex-wrap:wrap;}
.ult-r-cta-btn{padding:.875rem 1.75rem;border-radius:50px;font-weight:700;font-size:.95rem;display:inline-flex;align-items:center;gap:.5rem;transition:all .3s;}
.ult-r-cta-btn.solid{background:#f39c12;color:#fff!important;}
.ult-r-cta-btn.solid:hover{background:#e67e22;transform:translateY(-2px);}
.ult-r-cta-btn.ghost{border:2px solid rgba(255,255,255,.4);color:#fff!important;}
.ult-r-cta-btn.ghost:hover{background:rgba(255,255,255,.1);border-color:#fff;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-r-toolbar-form{grid-template-columns:1fr 1fr;}
    .ult-r-search{grid-column:1 / -1;}
}
@media (max-width:768px){
    .ult-r-item{grid-template-columns:1fr;text-align:left;gap:1rem;}
    .ult-r-actions{flex-direction:row;}
    .ult-r-actions > *{flex:1;}
    .ult-r-toolbar-form{grid-template-columns:1fr;}
    .ult-r-hero-stats{gap:.75rem;}
    .ult-r-hero-stat{min-width:130px;padding:1rem 1.25rem;}
}
</style>

<!-- ===== HERO ===== -->
<section class="ult-r-hero">
    <div class="ult-r-hero-shape ult-r-hero-shape-1"></div>
    <div class="ult-r-hero-shape ult-r-hero-shape-2"></div>
    <div class="container">
        <div class="ult-r-hero-content">
            <span class="ult-r-hero-badge"><i class="bi bi-journal-bookmark-fill"></i> Pusat Dokumentasi Hukum</span>
            <h1>Dokumen <span style="color:#f39c12;">Regulasi</span></h1>
            <p>Akses lengkap peraturan, keputusan, surat edaran, SOP, dan pedoman resmi yang berlaku di lingkungan Universitas Muhammadiyah Maumere.</p>
            <div class="ult-r-hero-stats">
                <div class="ult-r-hero-stat">
                    <div class="ult-r-hero-stat-value"><?= number_format($stats['total']) ?></div>
                    <div class="ult-r-hero-stat-label">Total Dokumen</div>
                </div>
                <div class="ult-r-hero-stat">
                    <div class="ult-r-hero-stat-value"><?= number_format($stats['cats']) ?></div>
                    <div class="ult-r-hero-stat-label">Kategori</div>
                </div>
                <div class="ult-r-hero-stat">
                    <div class="ult-r-hero-stat-value"><?= number_format($stats['thisYear']) ?></div>
                    <div class="ult-r-hero-stat-label">Terbit Tahun Ini</div>
                </div>
                <div class="ult-r-hero-stat">
                    <div class="ult-r-hero-stat-value">100%</div>
                    <div class="ult-r-hero-stat-label">Digital & Gratis</div>
                </div>
            </div>
        </div>
    </div>
    <div class="ult-r-hero-wave">
        <svg viewBox="0 0 1440 90" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 90L60 78C120 66 240 42 360 30C480 18 600 18 720 24C840 30 960 42 1080 48C1200 54 1320 54 1380 54L1440 54V90H1380C1320 90 1200 90 1080 90C960 90 840 90 720 90C600 90 480 90 360 90C240 90 120 90 60 90H0Z" fill="#ffffff"/>
        </svg>
    </div>
</section>

<!-- ===== TOOLBAR ===== -->
<div class="ult-r-toolbar">
    <div class="container">
        <form method="GET" action="<?= url('regulasi') ?>" class="ult-r-toolbar-form">
            <div class="ult-r-search">
                <i class="bi bi-search"></i>
                <input type="text" name="q" placeholder="Cari judul, nomor dokumen, atau kata kunci..." value="<?= htmlspecialchars($q) ?>">
            </div>
            <select name="year" class="ult-r-select" onchange="this.form.submit()">
                <option value="all">Semua Tahun</option>
                <?php foreach ($years as $y): ?>
                    <option value="<?= (int)$y['y'] ?>" <?= $year === (string)$y['y'] ? 'selected' : '' ?>>Tahun <?= (int)$y['y'] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="sort" class="ult-r-select" onchange="this.form.submit()">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Terlama</option>
                <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Judul A-Z</option>
                <option value="number" <?= $sort === 'number' ? 'selected' : '' ?>>Nomor Dokumen</option>
            </select>
            <button type="button" class="ult-r-reset" onclick="window.location='<?= url('regulasi') ?>'">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
        </form>
    </div>
</div>

<!-- ===== CATEGORY TABS ===== -->
<div class="ult-r-tabs">
    <div class="container">
        <div class="ult-r-tabs-wrap">
            <a href="<?= url('regulasi' . ($q ? '?q=' . urlencode($q) : '')) ?>" class="ult-r-tab <?= $cat === 'all' ? 'active' : '' ?>">
                <i class="bi bi-grid-3x3-gap-fill"></i> Semua
                <span class="ult-r-tab-count"><?= $stats['total'] ?></span>
            </a>
            <?php foreach ($categories as $c): ?>
            <a href="<?= url('regulasi?category=' . urlencode($c['category']) . ($q ? '&q=' . urlencode($q) : '')) ?>" class="ult-r-tab <?= $cat === $c['category'] ? 'active' : '' ?>">
                <i class="bi <?= ult_cat_icon($c['category']) ?>"></i>
                <?= ucfirst(str_replace('_', ' ', $c['category'])) ?>
                <span class="ult-r-tab-count"><?= (int)$c['total'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ===== DOCUMENT LIST ===== -->
<section class="section" style="padding-top:1rem;">
    <div class="container">
        <div class="ult-r-result-head">
            <h3><i class="bi bi-folder2-open"></i> Hasil Dokumen</h3>
            <span class="ult-r-result-count">Menampilkan <strong><?= count($dokumen) ?></strong> dokumen<?= $q ? ' untuk "' . htmlspecialchars($q) . '"' : '' ?></span>
        </div>
        
        <?php if (!empty($dokumen)): ?>
        <div class="ult-r-list">
            <?php foreach ($dokumen as $d):
                [$fIcon, $fColor, $fExt] = ult_file_meta($d['file_path'] ?? null);
                [$sLabel, $sBg, $sFg]   = ult_status_meta($d['status'] ?? 'active');
                $hasFile = !empty($d['file_path']);
            ?>
            <div class="ult-r-item" style="border-left-color:<?= $fColor ?>;">
                <div class="ult-r-file-icon" style="background:linear-gradient(135deg,<?= $fColor ?>,<?= $fColor ?>cc);">
                    <i class="bi <?= $fIcon ?>"></i>
                    <span class="ult-r-file-ext"><?= $fExt ?></span>
                </div>
                
                <div class="ult-r-info">
                    <h3><?= htmlspecialchars($d['title']) ?></h3>
                    <div class="ult-r-meta">
                        <span class="ult-r-cat"><i class="bi <?= ult_cat_icon($d['category'] ?? '') ?>"></i> <?= ucfirst(str_replace('_', ' ', $d['category'] ?? 'umum')) ?></span>
                        <?php if (!empty($d['document_number'])): ?>
                            <span><i class="bi bi-hash"></i> <?= htmlspecialchars($d['document_number']) ?></span>
                        <?php endif; ?>
                        <span><i class="bi bi-calendar-event"></i> Berlaku: <?= date('d M Y', strtotime($d['effective_date'])) ?></span>
                        <span class="ult-r-status" style="background:<?= $sBg ?>;color:<?= $sFg ?>;"><?= $sLabel ?></span>
                        <?php if (!empty($d['download_count'])): ?>
                            <span><i class="bi bi-download"></i> <?= number_format((int)$d['download_count']) ?>x</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($d['description'])): ?>
                        <p class="ult-r-desc"><?= htmlspecialchars($d['description']) ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="ult-r-actions">
                    <?php if ($hasFile): ?>
                        <a href="<?= url('uploads/dokumen/' . $d['file_path']) ?>" class="ult-r-download" target="_blank" rel="noopener">
                            <i class="bi bi-download"></i> Unduh
                        </a>
                        <a href="<?= url('uploads/dokumen/' . $d['file_path']) ?>" class="ult-r-detail" target="_blank" rel="noopener">
                            <i class="bi bi-eye"></i> Pratinjau
                        </a>
                    <?php else: ?>
                        <span class="ult-r-nofile"><i class="bi bi-clock-history"></i> File segera tersedia</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="ult-r-empty">
            <i class="bi bi-folder-x"></i>
            <h3>Dokumen Tidak Ditemukan</h3>
            <p>
                <?php if ($q !== '' || $cat !== 'all' || $year !== 'all'): ?>
                    Tidak ada dokumen yang cocok dengan filter pencarian Anda. Coba ubah kata kunci atau reset filter.
                <?php else: ?>
                    Belum ada dokumen regulasi yang dipublikasikan. Silakan cek kembali nanti.
                <?php endif; ?>
            </p>
            <a href="<?= url('regulasi') ?>" class="btn btn-primary"><i class="bi bi-arrow-counterclockwise"></i> Reset Filter</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===== ALUR PERMOHONAN ===== -->
<section class="ult-r-flow">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-signpost-2-fill"></i> Alur Layanan</span>
                <h2 class="section-title">Permohonan Dokumen Resmi</h2>
                <p class="section-subtitle" style="margin:0 auto;">Butuh salinan legalisir atau dokumen yang tidak tersedia online? Ikuti alur berikut</p>
            </div>
        </div>
        <div class="ult-r-flow-grid">
            <div class="ult-r-flow-card">
                <div class="ult-r-flow-icon"><i class="bi bi-pencil-square"></i></div>
                <h4>Ajukan Permohonan</h4>
                <p>Isi formulir kontak dengan kategori "Permohonan Dokumen / Surat" beserta detail kebutuhan Anda.</p>
            </div>
            <div class="ult-r-flow-card">
                <div class="ult-r-flow-icon"><i class="bi bi-clipboard-check"></i></div>
                <h4>Verifikasi & Persetujuan</h4>
                <p>Tim BAUK memverifikasi permohonan dan meneruskan ke pejabat berwenang untuk persetujuan (1-2 hari kerja).</p>
            </div>
            <div class="ult-r-flow-card">
                <div class="ult-r-flow-icon"><i class="bi bi-stamp"></i></div>
                <h4>Proses Legalisir</h4>
                <p>Dokumen dicetak, ditandatangani, dan dilegalisir sesuai ketentuan yang berlaku.</p>
            </div>
            <div class="ult-r-flow-card">
                <div class="ult-r-flow-icon"><i class="bi bi-box-seam"></i></div>
                <h4>Pengambilan</h4>
                <p>Dokumen siap diambil di kantor BAUK atau dikirim dalam bentuk digital bertanda tangan elektronik.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== CTA ===== -->
<section class="ult-r-cta">
    <div class="ult-r-cta-content">
        <h2>Tidak Menemukan Dokumen yang Dicari?</h2>
        <p>Tim dokumentasi kami siap membantu mencarikan regulasi yang Anda butuhkan, termasuk arsip tahun-tahun sebelumnya.</p>
        <div class="ult-r-cta-actions">
            <a href="<?= url('kontak') ?>" class="ult-r-cta-btn solid"><i class="bi bi-headset"></i> Minta Bantuan</a>
            <a href="<?= url('berita') ?>" class="ult-r-cta-btn ghost"><i class="bi bi-newspaper"></i> Berita Regulasi</a>
        </div>
    </div>
</section>