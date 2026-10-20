<?php
/**
 * BAUK UNIMOF - Halaman Berita ULTIMATE EDITION
 * Daftar berita, pengumuman, dan informasi terkini
 */
$db = \App\Helpers\Database::getInstance();

// Filter & Search
$activeCategory = $_GET['category'] ?? 'all';
$searchQuery    = $_GET['q'] ?? '';
$sortBy         = $_GET['sort'] ?? 'latest';

$where = "is_published = 1 AND deleted_at IS NULL";
$params = [];

if ($activeCategory !== 'all' && in_array($activeCategory, ['pengumuman','berita','kegiatan','pengadaan','regulasi'])) {
    $where .= " AND category = ?";
    $params[] = $activeCategory;
}

if ($searchQuery !== '') {
    $where .= " AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
    $searchParam = '%' . $searchQuery . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$order = $sortBy === 'popular' ? 'views DESC' : 'published_at DESC';

try {
    $allNews = $db->fetchAll("SELECT b.*, u.full_name AS author_name FROM berita b LEFT JOIN users u ON b.author_id = u.id WHERE {$where} ORDER BY {$order} LIMIT 50", $params);
    
    // Featured (hanya jika tidak ada filter/search)
    $featured = null;
    $otherNews = $allNews;
    if ($activeCategory === 'all' && $searchQuery === '' && !empty($allNews)) {
        $featured = $allNews[0];
        $otherNews = array_slice($allNews, 1);
    }
    
    // Stats
    $categoryCounts = [
        'all'        => $db->count('berita', "is_published = 1 AND deleted_at IS NULL"),
        'pengumuman' => $db->count('berita', "is_published = 1 AND deleted_at IS NULL AND category = 'pengumuman'"),
        'berita'     => $db->count('berita', "is_published = 1 AND deleted_at IS NULL AND category = 'berita'"),
        'kegiatan'   => $db->count('berita', "is_published = 1 AND deleted_at IS NULL AND category = 'kegiatan'"),
        'pengadaan'  => $db->count('berita', "is_published = 1 AND deleted_at IS NULL AND category = 'pengadaan'"),
        'regulasi'   => $db->count('berita', "is_published = 1 AND deleted_at IS NULL AND category = 'regulasi'"),
    ];
} catch (\Throwable $e) {
    $allNews = [];
    $featured = null;
    $otherNews = [];
    $categoryCounts = ['all' => 0, 'pengumuman' => 0, 'berita' => 0, 'kegiatan' => 0, 'pengadaan' => 0, 'regulasi' => 0];
}

$categoryColors = [
    'pengumuman' => '#e67e22',
    'berita'     => '#1a5276',
    'kegiatan'   => '#27ae60',
    'pengadaan'  => '#8e44ad',
    'regulasi'   => '#c0392b',
];
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== PAGE HERO ===== */
.ult-news-hero{position:relative;background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 50%,#2980b9 100%);color:#fff;padding:4rem 0 5rem;overflow:hidden;}
.ult-news-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/svg%3E");z-index:1;}
.ult-news-hero-shape{position:absolute;border-radius:50%;z-index:1;}
.ult-news-hero-shape-1{width:400px;height:400px;top:-100px;right:-100px;background:radial-gradient(circle,rgba(230,126,34,.2) 0%,transparent 70%);animation:ult_float 20s infinite ease-in-out;}
.ult-news-hero-shape-2{width:300px;height:300px;bottom:-80px;left:-80px;background:radial-gradient(circle,rgba(39,174,96,.2) 0%,transparent 70%);animation:ult_float 15s infinite ease-in-out reverse;}
@keyframes ult_float{0%,100%{transform:translate(0,0);}50%{transform:translate(30px,-30px);}}
.ult-news-hero-content{position:relative;z-index:2;}
.ult-news-hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.12);backdrop-filter:blur(10px);padding:.5rem 1.25rem;border-radius:50px;font-size:.85rem;margin-bottom:1.25rem;border:1px solid rgba(255,255,255,.2);}
.ult-news-hero h1{font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3rem);font-weight:700;margin-bottom:1rem;line-height:1.2;}
.ult-news-hero p{font-size:1.05rem;max-width:700px;line-height:1.8;color:rgba(255,255,255,.9);margin-bottom:2rem;}
.ult-news-hero-wave{position:absolute;bottom:-1px;left:0;right:0;z-index:3;}
.ult-news-hero-wave svg{display:block;width:100%;}

/* ===== SEARCH & FILTER BAR ===== */
.ult-news-toolbar{background:#fff;padding:1.5rem 0;box-shadow:0 4px 20px rgba(0,0,0,.05);position:sticky;top:0;z-index:50;}
.ult-toolbar-grid{display:grid;grid-template-columns:1fr auto;gap:1.5rem;align-items:center;}
.ult-search-form{position:relative;}
.ult-search-form input{width:100%;padding:.875rem 1.25rem .875rem 3rem;border:2px solid var(--color-border);border-radius:12px;font-size:.95rem;transition:all .2s;background:var(--color-bg);}
.ult-search-form input:focus{outline:none;border-color:var(--color-primary);background:#fff;box-shadow:0 0 0 3px rgba(26,82,118,.1);}
.ult-search-form i{position:absolute;left:1.125rem;top:50%;transform:translateY(-50%);color:var(--color-text-muted);font-size:1.05rem;}
.ult-search-form button{position:absolute;right:.5rem;top:50%;transform:translateY(-50%);background:var(--color-primary);color:#fff;border:none;padding:.5rem 1.25rem;border-radius:8px;font-weight:600;cursor:pointer;font-size:.85rem;}
.ult-toolbar-actions{display:flex;gap:.5rem;}
.ult-sort-select{padding:.75rem 1rem;border:2px solid var(--color-border);border-radius:12px;font-size:.9rem;background:#fff;cursor:pointer;font-weight:500;}
.ult-view-toggle{display:flex;border:2px solid var(--color-border);border-radius:12px;overflow:hidden;}
.ult-view-toggle button{background:#fff;border:none;padding:.625rem 1rem;cursor:pointer;color:var(--color-text-secondary);transition:all .2s;}
.ult-view-toggle button.active{background:var(--color-primary);color:#fff;}

/* ===== CATEGORY TABS ===== */
.ult-category-tabs{padding:1.5rem 0;overflow-x:auto;white-space:nowrap;}
.ult-tabs-wrapper{display:flex;gap:.625rem;justify-content:flex-start;}
.ult-tab{display:inline-flex;align-items:center;gap:.5rem;padding:.625rem 1.25rem;background:#fff;border:2px solid var(--color-border-light);border-radius:50px;font-size:.875rem;font-weight:600;color:var(--color-text-secondary);transition:all .2s;cursor:pointer;white-space:nowrap;}
.ult-tab:hover{border-color:var(--color-primary);color:var(--color-primary);}
.ult-tab.active{background:var(--color-primary);border-color:var(--color-primary);color:#fff;}
.ult-tab-count{background:rgba(0,0,0,.1);padding:.125rem .5rem;border-radius:50px;font-size:.75rem;font-weight:700;}
.ult-tab.active .ult-tab-count{background:rgba(255,255,255,.2);}

/* ===== FEATURED NEWS ===== */
.ult-featured-wrapper{margin-bottom:3rem;}
.ult-featured-card{display:grid;grid-template-columns:1.3fr 1fr;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.08);transition:all .4s ease;border:1px solid var(--color-border-light);}
.ult-featured-card:hover{box-shadow:0 20px 60px rgba(0,0,0,.12);transform:translateY(-5px);}
.ult-featured-image{position:relative;min-height:400px;background:linear-gradient(135deg,#1a5276,#2980b9);overflow:hidden;}
.ult-featured-image img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;}
.ult-featured-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:5rem;color:rgba(255,255,255,.3);}
.ult-featured-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(13,47,74,.6) 0%,transparent 60%);}
.ult-featured-badge-wrapper{position:absolute;top:1.5rem;left:1.5rem;display:flex;gap:.5rem;z-index:2;}
.ult-featured-badge{display:inline-flex;align-items:center;gap:.375rem;background:rgba(230,126,34,.95);color:#fff;padding:.375rem .875rem;border-radius:50px;font-size:.75rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;backdrop-filter:blur(10px);}
.ult-featured-category{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .875rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#fff;backdrop-filter:blur(10px);}
.ult-featured-content{padding:2.5rem;display:flex;flex-direction:column;justify-content:center;}
.ult-featured-meta{display:flex;gap:1rem;color:var(--color-text-muted);font-size:.85rem;margin-bottom:1rem;flex-wrap:wrap;}
.ult-featured-meta span{display:flex;align-items:center;gap:.375rem;}
.ult-featured-meta i{color:var(--color-primary);}
.ult-featured-title{font-family:'Playfair Display',serif;font-size:1.75rem;font-weight:700;line-height:1.3;margin-bottom:1rem;color:var(--color-text);}
.ult-featured-title a{color:inherit;transition:color .2s;}
.ult-featured-title a:hover{color:var(--color-primary);}
.ult-featured-excerpt{color:var(--color-text-secondary);line-height:1.8;margin-bottom:1.5rem;font-size:.95rem;}
.ult-featured-author{display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;}
.ult-author-avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--color-primary),var(--color-primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;}
.ult-author-info small{display:block;color:var(--color-text-muted);font-size:.75rem;}
.ult-author-info strong{display:block;color:var(--color-text);font-size:.9rem;}
.ult-featured-cta{display:inline-flex;align-items:center;gap:.5rem;background:var(--color-primary);color:#fff!important;padding:.875rem 1.75rem;border-radius:50px;font-weight:600;transition:all .3s;align-self:flex-start;box-shadow:0 6px 20px rgba(26,82,118,.3);}
.ult-featured-cta:hover{background:var(--color-accent);transform:translateY(-2px);box-shadow:0 10px 25px rgba(230,126,34,.4);}

/* ===== NEWS GRID ENHANCED ===== */
.ult-news-grid-wrapper{margin-top:2rem;}
.ult-news-section-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid var(--color-border-light);}
.ult-news-section-header h3{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;display:flex;align-items:center;gap:.5rem;}
.ult-news-section-header h3 i{color:var(--color-primary);}
.ult-results-count{color:var(--color-text-secondary);font-size:.9rem;}
.ult-results-count strong{color:var(--color-primary);}

/* News Card Enhanced */
.ult-news-card{background:#fff;border-radius:15px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.06);transition:all .4s ease;border:1px solid var(--color-border-light);height:100%;display:flex;flex-direction:column;}
.ult-news-card:hover{transform:translateY(-6px);box-shadow:0 15px 40px rgba(0,0,0,.12);border-color:var(--color-primary-100);}
.ult-news-card-image{position:relative;aspect-ratio:16/10;background:linear-gradient(135deg,var(--color-primary-50),var(--color-primary-100));overflow:hidden;}
.ult-news-card-image img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease;}
.ult-news-card:hover .ult-news-card-image img{transform:scale(1.08);}
.ult-news-card-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--color-primary-100);}
.ult-news-card-category{position:absolute;top:1rem;left:1rem;padding:.25rem .75rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#fff;z-index:2;}
.ult-news-card-reading{position:absolute;bottom:1rem;right:1rem;background:rgba(0,0,0,.7);color:#fff;padding:.25rem .75rem;border-radius:50px;font-size:.7rem;display:flex;align-items:center;gap:.25rem;backdrop-filter:blur(5px);}
.ult-news-card-body{padding:1.5rem;flex:1;display:flex;flex-direction:column;}
.ult-news-card-meta{display:flex;gap:.75rem;color:var(--color-text-muted);font-size:.8rem;margin-bottom:.75rem;flex-wrap:wrap;}
.ult-news-card-meta span{display:flex;align-items:center;gap:.25rem;}
.ult-news-card-title{font-size:1.1rem;font-weight:700;line-height:1.4;margin-bottom:.75rem;color:var(--color-text);min-height:3.2em;}
.ult-news-card-title a{color:inherit;transition:color .2s;}
.ult-news-card-title a:hover{color:var(--color-primary);}
.ult-news-card-excerpt{color:var(--color-text-secondary);font-size:.875rem;line-height:1.7;margin-bottom:1.25rem;flex:1;}
.ult-news-card-footer{display:flex;justify-content:space-between;align-items:center;padding-top:1rem;border-top:1px solid var(--color-border-light);}
.ult-news-card-author{display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:var(--color-text-secondary);}
.ult-news-card-author-avatar{width:28px;height:28px;border-radius:50%;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;}
.ult-news-card-link{color:var(--color-primary);font-weight:600;font-size:.85rem;display:inline-flex;align-items:center;gap:.25rem;transition:gap .2s;}
.ult-news-card-link:hover{gap:.5rem;color:var(--color-accent);}

/* Grid layout */
.ult-news-grid-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.5rem;}

/* ===== LIST VIEW ===== */
.ult-news-list{display:flex;flex-direction:column;gap:1.25rem;}
.ult-news-list-item{display:grid;grid-template-columns:250px 1fr;background:#fff;border-radius:15px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.06);transition:all .3s;border:1px solid var(--color-border-light);}
.ult-news-list-item:hover{transform:translateX(5px);box-shadow:0 10px 30px rgba(0,0,0,.1);}
.ult-news-list-image{aspect-ratio:16/10;background:linear-gradient(135deg,var(--color-primary-50),var(--color-primary-100));position:relative;overflow:hidden;}
.ult-news-list-image img{width:100%;height:100%;object-fit:cover;}
.ult-news-list-body{padding:1.5rem;}
.ult-news-list-title{font-size:1.2rem;font-weight:700;margin-bottom:.5rem;}

/* ===== TRENDING SIDEBAR ===== */
.ult-news-layout{display:grid;grid-template-columns:1fr 320px;gap:2.5rem;margin-top:3rem;}
.ult-trending-card{background:#fff;border-radius:15px;padding:1.5rem;box-shadow:0 4px 20px rgba(0,0,0,.06);position:sticky;top:120px;}
.ult-trending-header{display:flex;align-items:center;gap:.5rem;font-weight:700;font-size:1.1rem;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:2px solid var(--color-border-light);}
.ult-trending-header i{color:var(--color-accent);}
.ult-trending-list{display:flex;flex-direction:column;gap:1rem;}
.ult-trending-item{display:flex;gap:1rem;align-items:flex-start;padding-bottom:1rem;border-bottom:1px solid var(--color-border-light);}
.ult-trending-item:last-child{border-bottom:none;padding-bottom:0;}
.ult-trending-number{width:32px;height:32px;background:linear-gradient(135deg,var(--color-accent),#f39c12);color:#fff;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.95rem;flex-shrink:0;font-family:'Playfair Display',serif;}
.ult-trending-info h5{font-size:.9rem;font-weight:700;line-height:1.4;margin-bottom:.25rem;}
.ult-trending-info h5 a{color:var(--color-text);transition:color .2s;}
.ult-trending-info h5 a:hover{color:var(--color-primary);}
.ult-trending-info small{font-size:.75rem;color:var(--color-text-muted);display:flex;align-items:center;gap:.25rem;}

/* ===== NEWSLETTER CTA ===== */
.ult-news-newsletter{background:linear-gradient(135deg,var(--color-primary-dark),var(--color-primary));color:#fff;padding:3rem;border-radius:20px;text-align:center;margin-top:2rem;position:relative;overflow:hidden;}
.ult-news-newsletter::before{content:'';position:absolute;top:-50%;right:-10%;width:300px;height:300px;background:radial-gradient(circle,rgba(230,126,34,.2) 0%,transparent 70%);border-radius:50%;}
.ult-news-newsletter-content{position:relative;z-index:1;}
.ult-news-newsletter i.big{font-size:3rem;margin-bottom:1rem;color:#f39c12;}
.ult-news-newsletter h3{font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:.5rem;}
.ult-news-newsletter p{color:rgba(255,255,255,.85);margin-bottom:1.5rem;}

/* ===== EMPTY STATE ===== */
.ult-empty-state{text-align:center;padding:4rem 2rem;background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,.05);}
.ult-empty-state i{font-size:5rem;color:var(--color-border);margin-bottom:1.5rem;}
.ult-empty-state h3{font-size:1.5rem;margin-bottom:.5rem;}
.ult-empty-state p{color:var(--color-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== PAGINATION ===== */
.ult-pagination{display:flex;justify-content:center;gap:.375rem;margin-top:3rem;flex-wrap:wrap;}
.ult-pagination a,.ult-pagination span{padding:.625rem 1rem;border-radius:8px;font-weight:600;font-size:.9rem;transition:all .2s;min-width:40px;text-align:center;}
.ult-pagination a{background:#fff;color:var(--color-text);border:1px solid var(--color-border);}
.ult-pagination a:hover{background:var(--color-primary);color:#fff;border-color:var(--color-primary);}
.ult-pagination .active{background:var(--color-primary);color:#fff;border-color:var(--color-primary);}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-featured-card{grid-template-columns:1fr;}
    .ult-featured-image{min-height:250px;}
    .ult-news-layout{grid-template-columns:1fr;}
    .ult-trending-card{position:static;}
}
@media (max-width:768px){
    .ult-toolbar-grid{grid-template-columns:1fr;}
    .ult-news-grid-cards{grid-template-columns:1fr;}
    .ult-news-list-item{grid-template-columns:1fr;}
    .ult-news-list-image{aspect-ratio:16/9;}
    .ult-featured-content{padding:1.5rem;}
    .ult-featured-title{font-size:1.35rem;}
}
</style>

<!-- ===== PAGE HERO ===== -->
<section class="ult-news-hero">
    <div class="ult-news-hero-shape ult-news-hero-shape-1"></div>
    <div class="ult-news-hero-shape ult-news-hero-shape-2"></div>
    <div class="container">
        <div class="ult-news-hero-content">
            <span class="ult-news-hero-badge"><i class="bi bi-newspaper"></i> Pusat Informasi</span>
            <h1>Berita & <span style="color:#f39c12;">Pengumuman</span></h1>
            <p>Dapatkan informasi terkini seputar kegiatan, pengumuman resmi, kebijakan baru, dan update penting dari Biro Administrasi Umum dan Keuangan UNIMOF.</p>
        </div>
    </div>
    <div class="ult-news-hero-wave">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 80L60 70C120 60 240 40 360 30C480 20 600 20 720 25C840 30 960 40 1080 45C1200 50 1320 50 1380 50L1440 50V80H1380C1320 80 1200 80 1080 80C960 80 840 80 720 80C600 80 480 80 360 80C240 80 120 80 60 80H0Z" fill="#ffffff"/>
        </svg>
    </div>
</section>

<!-- ===== SEARCH & TOOLBAR ===== -->
<div class="ult-news-toolbar">
    <div class="container">
        <form class="ult-toolbar-grid" method="GET" action="<?= url('berita') ?>">
            <div class="ult-search-form">
                <i class="bi bi-search"></i>
                <input type="text" name="q" placeholder="Cari berita, pengumuman, kegiatan..." value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit">Cari</button>
            </div>
            <div class="ult-toolbar-actions">
                <select name="sort" class="ult-sort-select" onchange="this.form.submit()">
                    <option value="latest" <?= $sortBy === 'latest' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="popular" <?= $sortBy === 'popular' ? 'selected' : '' ?>>Terpopuler</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- ===== CATEGORY TABS ===== -->
<div class="ult-category-tabs">
    <div class="container">
        <div class="ult-tabs-wrapper">
            <a href="<?= url('berita') ?>" class="ult-tab <?= $activeCategory === 'all' ? 'active' : '' ?>">
                <i class="bi bi-grid-3x3-gap-fill"></i> Semua
                <span class="ult-tab-count"><?= $categoryCounts['all'] ?></span>
            </a>
            <?php foreach (['pengumuman','berita','kegiatan','pengadaan','regulasi'] as $cat): ?>
            <a href="<?= url('berita?category=' . $cat) ?>" class="ult-tab <?= $activeCategory === $cat ? 'active' : '' ?>">
                <i class="bi bi-<?= $cat === 'pengumuman' ? 'megaphone-fill' : ($cat === 'berita' ? 'newspaper' : ($cat === 'kegiatan' ? 'calendar-event-fill' : ($cat === 'pengadaan' ? 'cart-fill' : 'file-earmark-text-fill'))) ?>"></i>
                <?= ucfirst($cat) ?>
                <span class="ult-tab-count"><?= $categoryCounts[$cat] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ===== MAIN CONTENT ===== -->
<section class="section" style="padding-top:2rem;">
    <div class="container">
        
        <!-- Featured News (hanya tampil jika all category & no search) -->
        <?php if ($featured && $activeCategory === 'all' && $searchQuery === ''): ?>
        <div class="ult-featured-wrapper">
            <article class="ult-featured-card">
                <div class="ult-featured-image">
                    <?php if (!empty($featured['featured_image'])): ?>
                        <img src="<?= url('uploads/berita/' . $featured['featured_image']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>">
                    <?php else: ?>
                        <div class="ult-featured-placeholder"><i class="bi bi-newspaper"></i></div>
                    <?php endif; ?>
                    <div class="ult-featured-overlay"></div>
                    <div class="ult-featured-badge-wrapper">
                        <span class="ult-featured-badge"><i class="bi bi-star-fill"></i> FEATURED</span>
                        <span class="ult-featured-category" style="background:<?= $categoryColors[$featured['category']] ?? '#1a5276' ?>;"><?= ucfirst($featured['category']) ?></span>
                    </div>
                </div>
                <div class="ult-featured-content">
                    <div class="ult-featured-meta">
                        <span><i class="bi bi-calendar-event"></i> <?= date('d F Y', strtotime($featured['published_at'])) ?></span>
                        <span><i class="bi bi-eye"></i> <?= number_format($featured['views']) ?> views</span>
                        <span><i class="bi bi-clock"></i> <?= ceil(strlen($featured['content'] ?? '') / 1000) ?> menit baca</span>
                    </div>
                    <h2 class="ult-featured-title"><a href="<?= url('berita/' . $featured['slug']) ?>"><?= htmlspecialchars($featured['title']) ?></a></h2>
                    <p class="ult-featured-excerpt"><?= htmlspecialchars(mb_substr($featured['excerpt'] ?? $featured['content'] ?? '', 0, 200)) ?>...</p>
                    <div class="ult-featured-author">
                        <div class="ult-author-avatar"><?= strtoupper(substr($featured['author_name'] ?? 'B', 0, 1)) ?></div>
                        <div class="ult-author-info">
                            <small>Ditulis oleh</small>
                            <strong><?= htmlspecialchars($featured['author_name'] ?? 'Tim BAUK') ?></strong>
                        </div>
                    </div>
                    <a href="<?= url('berita/' . $featured['slug']) ?>" class="ult-featured-cta">Baca Selengkapnya <i class="bi bi-arrow-right"></i></a>
                </div>
            </article>
        </div>
        <?php endif; ?>
        
        <!-- Layout dengan sidebar trending -->
        <div class="ult-news-layout">
            <div class="ult-news-main">
                <!-- Section Header -->
                <div class="ult-news-section-header">
                    <h3><i class="bi bi-collection"></i> 
                        <?= $activeCategory === 'all' ? 'Semua Berita' : ucfirst($activeCategory) ?>
                        <?php if ($searchQuery): ?>
                            <span style="font-size:.9rem;color:var(--color-text-muted);font-weight:400;"> untuk "<?= htmlspecialchars($searchQuery) ?>"</span>
                        <?php endif; ?>
                    </h3>
                    <span class="ult-results-count">Menampilkan <strong><?= count($otherNews) ?></strong> artikel</span>
                </div>
                
                <!-- News Grid -->
                <?php if (!empty($otherNews)): ?>
                <div class="ult-news-grid-cards">
                    <?php foreach ($otherNews as $item): 
                        $readTime = max(1, ceil(strlen($item['content'] ?? '') / 1000));
                        $bgColor = $categoryColors[$item['category']] ?? '#1a5276';
                    ?>
                    <article class="ult-news-card">
                        <div class="ult-news-card-image">
                            <?php if (!empty($item['featured_image'])): ?>
                                <img src="<?= url('uploads/berita/' . $item['featured_image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                            <?php else: ?>
                                <div class="ult-news-card-placeholder"><i class="bi bi-newspaper"></i></div>
                            <?php endif; ?>
                            <span class="ult-news-card-category" style="background:<?= $bgColor ?>;"><?= ucfirst($item['category']) ?></span>
                            <span class="ult-news-card-reading"><i class="bi bi-clock"></i> <?= $readTime ?> min</span>
                        </div>
                        <div class="ult-news-card-body">
                            <div class="ult-news-card-meta">
                                <span><i class="bi bi-calendar"></i> <?= date('d M Y', strtotime($item['published_at'])) ?></span>
                                <span><i class="bi bi-eye"></i> <?= number_format($item['views']) ?></span>
                            </div>
                            <h3 class="ult-news-card-title"><a href="<?= url('berita/' . $item['slug']) ?>"><?= htmlspecialchars($item['title']) ?></a></h3>
                            <p class="ult-news-card-excerpt"><?= htmlspecialchars(mb_substr($item['excerpt'] ?? $item['content'] ?? '', 0, 120)) ?>...</p>
                            <div class="ult-news-card-footer">
                                <div class="ult-news-card-author">
                                    <div class="ult-news-card-author-avatar"><?= strtoupper(substr($item['author_name'] ?? 'B', 0, 1)) ?></div>
                                    <span><?= htmlspecialchars($item['author_name'] ?? 'BAUK') ?></span>
                                </div>
                                <a href="<?= url('berita/' . $item['slug']) ?>" class="ult-news-card-link">Baca <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <div class="ult-pagination">
                    <span class="active">1</span>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <a href="#"><i class="bi bi-chevron-right"></i></a>
                </div>
                
                <?php else: ?>
                <!-- Empty State -->
                <div class="ult-empty-state">
                    <i class="bi bi-inbox"></i>
                    <h3>Tidak Ada Artikel</h3>
                    <p>
                        <?php if ($searchQuery): ?>
                            Tidak ditemukan artikel untuk pencarian "<strong><?= htmlspecialchars($searchQuery) ?></strong>". Coba kata kunci lain.
                        <?php else: ?>
                            Belum ada artikel yang dipublikasikan untuk kategori ini.
                        <?php endif; ?>
                    </p>
                    <a href="<?= url('berita') ?>" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Lihat Semua Berita</a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar Trending -->
            <aside>
                <?php 
                $trending = $db->fetchAll("SELECT slug, title, views, published_at, category FROM berita WHERE is_published = 1 AND deleted_at IS NULL ORDER BY views DESC LIMIT 5");
                if (!empty($trending)): 
                ?>
                <div class="ult-trending-card">
                    <div class="ult-trending-header">
                        <i class="bi bi-fire"></i> Paling Populer
                    </div>
                    <div class="ult-trending-list">
                        <?php foreach ($trending as $i => $t): ?>
                        <div class="ult-trending-item">
                            <div class="ult-trending-number"><?= $i + 1 ?></div>
                            <div class="ult-trending-info">
                                <h5><a href="<?= url('berita/' . $t['slug']) ?>"><?= htmlspecialchars(mb_substr($t['title'], 0, 60)) ?><?= strlen($t['title']) > 60 ? '...' : '' ?></a></h5>
                                <small><i class="bi bi-eye"></i> <?= number_format($t['views']) ?> views • <?= date('d M', strtotime($t['published_at'])) ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Newsletter CTA -->
                <div class="ult-news-newsletter">
                    <div class="ult-news-newsletter-content">
                        <i class="bi bi-envelope-paper-fill big"></i>
                        <h3>Jangan Lewatkan Update</h3>
                        <p>Dapatkan berita terbaru langsung di email Anda</p>
                        <a href="#" class="btn btn-primary" style="background:#f39c12;border-color:#f39c12;">Berlangganan</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>