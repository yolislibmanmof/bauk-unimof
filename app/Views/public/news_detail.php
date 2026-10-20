<?php
/**
 * BAUK UNIMOF - Detail Berita ULTIMATE EDITION
 */
$db = \App\Helpers\Database::getInstance();

// Related articles
try {
    $related = $db->fetchAll(
        "SELECT * FROM berita WHERE category = ? AND id != ? AND is_published = 1 AND deleted_at IS NULL ORDER BY published_at DESC LIMIT 3",
        [$article['category'], $article['id']]
    );
    
    // Prev & Next article
    $prevArticle = $db->fetch(
        "SELECT slug, title FROM berita WHERE published_at < ? AND is_published = 1 AND deleted_at IS NULL ORDER BY published_at DESC LIMIT 1",
        [$article['published_at']]
    );
    $nextArticle = $db->fetch(
        "SELECT slug, title FROM berita WHERE published_at > ? AND is_published = 1 AND deleted_at IS NULL ORDER BY published_at ASC LIMIT 1",
        [$article['published_at']]
    );
} catch (\Throwable $e) {
    $related = [];
    $prevArticle = null;
    $nextArticle = null;
}

$readTime = max(1, ceil(strlen($article['content'] ?? '') / 1000));
$categoryColors = [
    'pengumuman' => '#e67e22',
    'berita'     => '#1a5276',
    'kegiatan'   => '#27ae60',
    'pengadaan'  => '#8e44ad',
    'regulasi'   => '#c0392b',
];
$catColor = $categoryColors[$article['category']] ?? '#1a5276';

// Extract headings untuk TOC
$headings = [];
if (preg_match_all('/<h[23][^>]*>(.*?)<\/h[23]>/is', $article['content'], $matches)) {
    foreach ($matches[1] as $i => $text) {
        $headings[] = [
            'text' => strip_tags($text),
            'id'   => 'heading-' . $i,
            'level' => strpos($matches[0][$i], '<h2') !== false ? 2 : 3,
        ];
    }
}
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== READING PROGRESS BAR ===== */
.ult-reading-progress{position:fixed;top:0;left:0;width:0;height:4px;background:linear-gradient(90deg,#1a5276,#e67e22);z-index:10000;transition:width .1s;}

/* ===== ARTICLE HERO ===== */
.ult-article-hero{position:relative;padding:3rem 0 0;overflow:hidden;}
.ult-article-hero::before{content:'';position:absolute;inset:0;background:linear-gradient(180deg,transparent 60%,#fff 100%);z-index:1;}
.ult-article-hero-content{position:relative;z-index:2;max-width:900px;margin:0 auto;padding:0 1.5rem;}
.ult-article-breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.85rem;color:var(--color-text-secondary);margin-bottom:1.5rem;flex-wrap:wrap;}
.ult-article-breadcrumb a{color:var(--color-primary);transition:color .2s;}
.ult-article-breadcrumb a:hover{color:var(--color-accent);}
.ult-article-breadcrumb i{font-size:.7rem;color:var(--color-text-muted);}
.ult-article-meta-top{display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap;}
.ult-article-category-badge{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem 1rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#fff;}
.ult-article-read-time{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .875rem;background:var(--color-bg);color:var(--color-text-secondary);border-radius:50px;font-size:.8rem;font-weight:600;}
.ult-article-title{font-family:'Playfair Display',serif;font-size:clamp(1.75rem,4vw,2.75rem);font-weight:700;line-height:1.2;margin-bottom:1.5rem;color:var(--color-text);}
.ult-article-excerpt{font-size:1.15rem;color:var(--color-text-secondary);line-height:1.7;margin-bottom:2rem;font-style:italic;padding-left:1.25rem;border-left:4px solid var(--color-accent);}

/* ===== AUTHOR BAR ===== */
.ult-author-bar{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 0;border-top:1px solid var(--color-border);border-bottom:1px solid var(--color-border);margin-bottom:2.5rem;flex-wrap:wrap;gap:1rem;}
.ult-author-info-full{display:flex;align-items:center;gap:1rem;}
.ult-author-avatar-lg{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--color-primary),var(--color-primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;}
.ult-author-details small{display:block;color:var(--color-text-muted);font-size:.8rem;}
.ult-author-details strong{display:block;font-size:1rem;color:var(--color-text);}
.ult-author-details .ult-author-date{font-size:.85rem;color:var(--color-text-secondary);margin-top:.125rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;}
.ult-author-date span{display:inline-flex;align-items:center;gap:.25rem;}
.ult-share-bar{display:flex;align-items:center;gap:.5rem;}
.ult-share-bar-label{font-size:.85rem;color:var(--color-text-secondary);margin-right:.5rem;}
.ult-share-btn{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.95rem;transition:all .2s;}
.ult-share-btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.2);}
.ult-share-btn.fb{background:#1877f2;}
.ult-share-btn.tw{background:#1da1f2;}
.ult-share-btn.wa{background:#25d366;}
.ult-share-btn.li{background:#0077b5;}
.ult-share-btn.mail{background:#64748b;}
.ult-share-btn.copy{background:var(--color-primary);}

/* ===== ARTICLE IMAGE ===== */
.ult-article-image{margin:0 auto 2.5rem;max-width:900px;border-radius:20px;overflow:hidden;box-shadow:0 15px 50px rgba(0,0,0,.15);aspect-ratio:16/9;background:linear-gradient(135deg,var(--color-primary-50),var(--color-primary-100));}
.ult-article-image img{width:100%;height:100%;object-fit:cover;}
.ult-article-image-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:5rem;color:var(--color-primary-100);}

/* ===== ARTICLE LAYOUT ===== */
.ult-article-layout{display:grid;grid-template-columns:220px 1fr;gap:3rem;max-width:1100px;margin:0 auto;padding:0 1.5rem;}
.ult-article-sidebar{position:relative;}
.ult-sticky-wrapper{position:sticky;top:100px;}

/* Table of Contents */
.ult-toc{background:#fff;border:1px solid var(--color-border-light);border-radius:15px;padding:1.5rem;box-shadow:0 4px 15px rgba(0,0,0,.04);}
.ult-toc-header{font-weight:700;font-size:.95rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;padding-bottom:.75rem;border-bottom:2px solid var(--color-border-light);}
.ult-toc-header i{color:var(--color-primary);}
.ult-toc-list{list-style:none;padding:0;margin:0;}
.ult-toc-list li{margin-bottom:.5rem;}
.ult-toc-list li.level-3{padding-left:1rem;font-size:.85rem;}
.ult-toc-list a{color:var(--color-text-secondary);font-size:.875rem;text-decoration:none;display:block;padding:.25rem 0;transition:color .2s;border-left:2px solid transparent;padding-left:.75rem;}
.ult-toc-list a:hover,.ult-toc-list a.active{color:var(--color-primary);border-left-color:var(--color-primary);font-weight:600;}

/* Share Floating */
.ult-share-floating{display:flex;flex-direction:column;gap:.5rem;margin-top:1.5rem;}
.ult-share-floating .ult-share-btn{width:44px;height:44px;}

/* Article Content */
.ult-article-content{max-width:800px;}
.ult-article-content h2{font-family:'Playfair Display',serif;font-size:1.75rem;font-weight:700;margin:2.5rem 0 1rem;color:var(--color-text);position:relative;padding-left:1rem;}
.ult-article-content h2::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:4px;height:70%;background:var(--color-accent);border-radius:2px;}
.ult-article-content h3{font-size:1.35rem;font-weight:700;margin:2rem 0 .75rem;color:var(--color-text);}
.ult-article-content p{font-size:1.05rem;line-height:1.9;color:var(--color-text-secondary);margin-bottom:1.25rem;text-align:justify;}
.ult-article-content img{max-width:100%;height:auto;border-radius:12px;margin:1.5rem 0;box-shadow:0 8px 25px rgba(0,0,0,.1);}
.ult-article-content a{color:var(--color-primary);text-decoration:underline;text-decoration-color:rgba(26,82,118,.3);transition:text-decoration-color .2s;}
.ult-article-content a:hover{text-decoration-color:var(--color-primary);}
.ult-article-content blockquote{border-left:4px solid var(--color-accent);background:var(--color-bg);padding:1.25rem 1.5rem;margin:1.5rem 0;border-radius:0 10px 10px 0;font-style:italic;color:var(--color-text);font-size:1.05rem;}
.ult-article-content ul,.ult-article-content ol{margin:1rem 0 1.5rem 1.5rem;line-height:1.9;}
.ult-article-content li{margin-bottom:.5rem;color:var(--color-text-secondary);}
.ult-article-content code{background:var(--color-bg);padding:.125rem .375rem;border-radius:4px;font-size:.9em;color:var(--color-accent);}
.ult-article-content pre{background:#1e293b;color:#f1f5f9;padding:1.25rem;border-radius:10px;overflow-x:auto;margin:1.5rem 0;}
.ult-article-content table{width:100%;border-collapse:collapse;margin:1.5rem 0;}
.ult-article-content th,.ult-article-content td{padding:.75rem 1rem;border:1px solid var(--color-border);text-align:left;}
.ult-article-content th{background:var(--color-bg);font-weight:600;}

/* Tags */
.ult-article-tags{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:3rem;padding-top:2rem;border-top:1px solid var(--color-border);}
.ult-article-tags-label{font-weight:600;font-size:.9rem;color:var(--color-text);display:flex;align-items:center;gap:.375rem;margin-right:.5rem;}
.ult-tag{display:inline-block;padding:.375rem .875rem;background:var(--color-bg);color:var(--color-text-secondary);border-radius:50px;font-size:.8rem;transition:all .2s;text-decoration:none!important;}
.ult-tag:hover{background:var(--color-primary);color:#fff;}

/* Author Bio Box */
.ult-author-bio{background:linear-gradient(135deg,var(--color-primary-50),#fff);border-radius:20px;padding:2rem;margin:3rem 0;display:flex;gap:1.5rem;align-items:center;border:1px solid var(--color-primary-100);}
.ult-author-bio-avatar{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--color-primary),var(--color-primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;flex-shrink:0;}
.ult-author-bio-info h4{font-size:1.15rem;margin-bottom:.25rem;}
.ult-author-bio-info p{font-size:.9rem;color:var(--color-text-secondary);line-height:1.7;margin:0;}
.ult-author-bio-info .ult-author-role{font-size:.8rem;color:var(--color-primary);font-weight:600;margin-bottom:.375rem;}

/* Prev/Next Navigation */
.ult-article-nav{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:3rem;padding-top:2rem;border-top:1px solid var(--color-border);}
.ult-article-nav-item{padding:1.25rem;background:#fff;border-radius:15px;border:1px solid var(--color-border-light);transition:all .3s;color:var(--color-text)!important;display:block;}
.ult-article-nav-item:hover{border-color:var(--color-primary);box-shadow:0 8px 25px rgba(0,0,0,.08);transform:translateY(-3px);}
.ult-article-nav-item.next{text-align:right;}
.ult-article-nav-label{font-size:.8rem;color:var(--color-text-muted);display:flex;align-items:center;gap:.375rem;margin-bottom:.375rem;}
.ult-article-nav-item.next .ult-article-nav-label{justify-content:flex-end;}
.ult-article-nav-title{font-weight:600;font-size:.95rem;line-height:1.4;}

/* Share Bar Bottom */
.ult-share-bar-bottom{background:#fff;border-radius:15px;padding:1.5rem;margin-top:2rem;display:flex;justify-content:space-between;align-items:center;box-shadow:0 4px 15px rgba(0,0,0,.05);flex-wrap:wrap;gap:1rem;}
.ult-share-bar-bottom-left{display:flex;align-items:center;gap:1rem;}
.ult-article-views{display:flex;align-items:center;gap:.5rem;color:var(--color-text-secondary);font-size:.9rem;}
.ult-article-views i{color:var(--color-primary);}
.ult-article-views strong{color:var(--color-text);font-size:1.1rem;}

/* Print Button */
.ult-print-btn{background:var(--color-bg);border:1px solid var(--color-border);color:var(--color-text);padding:.5rem 1rem;border-radius:8px;font-size:.85rem;cursor:pointer;display:inline-flex;align-items:center;gap:.375rem;transition:all .2s;}
.ult-print-btn:hover{background:var(--color-primary);color:#fff;}

/* ===== RELATED ARTICLES ===== */
.ult-related{background:var(--color-bg);padding:4rem 0;margin-top:4rem;}
.ult-related-header{text-align:center;margin-bottom:2.5rem;}
.ult-related-header h2{font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:.5rem;}
.ult-related-header p{color:var(--color-text-secondary);}
.ult-related-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;max-width:1100px;margin:0 auto;padding:0 1.5rem;}

/* ===== CTA AFTER ARTICLE ===== */
.ult-article-cta{background:linear-gradient(135deg,var(--color-primary-dark),var(--color-primary));color:#fff;padding:3rem;border-radius:20px;text-align:center;margin-top:3rem;position:relative;overflow:hidden;}
.ult-article-cta::before{content:'';position:absolute;top:-50%;right:-10%;width:300px;height:300px;background:radial-gradient(circle,rgba(230,126,34,.2) 0%,transparent 70%);border-radius:50%;}
.ult-article-cta-content{position:relative;z-index:1;}
.ult-article-cta h3{font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:.5rem;}
.ult-article-cta p{color:rgba(255,255,255,.85);margin-bottom:1.5rem;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-article-layout{grid-template-columns:1fr;gap:2rem;}
    .ult-article-sidebar{order:2;}
    .ult-sticky-wrapper{position:static;}
    .ult-share-floating{flex-direction:row;justify-content:center;}
}
@media (max-width:768px){
    .ult-article-nav{grid-template-columns:1fr;}
    .ult-author-bio{flex-direction:column;text-align:center;}
    .ult-author-bar{flex-direction:column;align-items:flex-start;}
    .ult-share-bar{width:100%;justify-content:flex-start;}
}
@media print{
    .ult-reading-progress,.ult-share-bar,.ult-share-floating,.ult-related,.ult-article-cta,.ult-article-nav,.ult-print-btn,.ult-share-bar-bottom{display:none!important;}
    .ult-article-layout{grid-template-columns:1fr;}
    .ult-article-sidebar{display:none;}
}
</style>

<!-- Reading Progress Bar -->
<div class="ult-reading-progress" id="ultReadingProgress"></div>

<!-- ===== ARTICLE HERO ===== -->
<section class="ult-article-hero">
    <div class="ult-article-hero-content">
        <!-- Breadcrumb -->
        <nav class="ult-article-breadcrumb">
            <a href="<?= url() ?>"><i class="bi bi-house-fill"></i> Beranda</a>
            <i class="bi bi-chevron-right"></i>
            <a href="<?= url('berita') ?>">Berita</a>
            <i class="bi bi-chevron-right"></i>
            <span><?= ucfirst($article['category']) ?></span>
        </nav>
        
        <!-- Category & Read Time -->
        <div class="ult-article-meta-top">
            <span class="ult-article-category-badge" style="background:<?= $catColor ?>;">
                <i class="bi bi-<?= $article['category'] === 'pengumuman' ? 'megaphone-fill' : ($article['category'] === 'berita' ? 'newspaper' : ($article['category'] === 'kegiatan' ? 'calendar-event-fill' : 'file-earmark-text-fill')) ?>"></i>
                <?= ucfirst($article['category']) ?>
            </span>
            <span class="ult-article-read-time">
                <i class="bi bi-clock"></i> <?= $readTime ?> menit baca
            </span>
        </div>
        
        <!-- Title -->
        <h1 class="ult-article-title"><?= htmlspecialchars($article['title']) ?></h1>
        
        <!-- Excerpt -->
        <?php if (!empty($article['excerpt'])): ?>
        <p class="ult-article-excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
        <?php endif; ?>
        
        <!-- Author Bar -->
        <div class="ult-author-bar">
            <div class="ult-author-info-full">
                <div class="ult-author-avatar-lg"><?= strtoupper(substr($article['author_name'] ?? 'B', 0, 1)) ?></div>
                <div class="ult-author-details">
                    <small>Ditulis oleh</small>
                    <strong><?= htmlspecialchars($article['author_name'] ?? 'Tim BAUK UNIMOF') ?></strong>
                    <div class="ult-author-date">
                        <span><i class="bi bi-calendar-event"></i> <?= date('d F Y', strtotime($article['published_at'])) ?></span>
                        <span><i class="bi bi-clock"></i> <?= date('H:i', strtotime($article['published_at'])) ?> WITA</span>
                        <span><i class="bi bi-eye"></i> <?= number_format($article['views']) ?> views</span>
                    </div>
                </div>
            </div>
            <div class="ult-share-bar">
                <span class="ult-share-bar-label">Bagikan:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('berita/' . $article['slug'])) ?>" target="_blank" class="ult-share-btn fb" title="Facebook"><i class="bi bi-facebook"></i></a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode(url('berita/' . $article['slug'])) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" class="ult-share-btn tw" title="Twitter"><i class="bi bi-twitter-x"></i></a>
                <a href="https://wa.me/?text=<?= urlencode($article['title'] . ' - ' . url('berita/' . $article['slug'])) ?>" target="_blank" class="ult-share-btn wa" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(url('berita/' . $article['slug'])) ?>" target="_blank" class="ult-share-btn li" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                <a href="mailto:?subject=<?= urlencode($article['title']) ?>&body=<?= urlencode('Baca artikel ini: ' . url('berita/' . $article['slug'])) ?>" class="ult-share-btn mail" title="Email"><i class="bi bi-envelope-fill"></i></a>
                <button class="ult-share-btn copy" onclick="ultCopyLink()" title="Salin Link"><i class="bi bi-link-45deg"></i></button>
            </div>
        </div>
    </div>
</section>

<!-- ===== FEATURED IMAGE ===== -->
<?php if (!empty($article['featured_image'])): ?>
<div class="ult-article-image">
    <img src="<?= url('uploads/berita/' . $article['featured_image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
</div>
<?php else: ?>
<div class="ult-article-image">
    <div class="ult-article-image-placeholder"><i class="bi bi-newspaper"></i></div>
</div>
<?php endif; ?>

<!-- ===== ARTICLE LAYOUT ===== -->
<div class="ult-article-layout">
    
    <!-- Sidebar: TOC & Share -->
    <aside class="ult-article-sidebar">
        <div class="ult-sticky-wrapper">
            <?php if (!empty($headings)): ?>
            <div class="ult-toc">
                <div class="ult-toc-header"><i class="bi bi-list-ul"></i> Daftar Isi</div>
                <ul class="ult-toc-list">
                    <?php foreach ($headings as $h): ?>
                    <li class="level-<?= $h['level'] ?>">
                        <a href="#<?= $h['id'] ?>"><?= htmlspecialchars($h['text']) ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Floating Share -->
            <div class="ult-share-floating">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('berita/' . $article['slug'])) ?>" target="_blank" class="ult-share-btn fb" title="Facebook"><i class="bi bi-facebook"></i></a>
                <a href="https://wa.me/?text=<?= urlencode($article['title'] . ' - ' . url('berita/' . $article['slug'])) ?>" target="_blank" class="ult-share-btn wa" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode(url('berita/' . $article['slug'])) ?>" target="_blank" class="ult-share-btn tw" title="Twitter"><i class="bi bi-twitter-x"></i></a>
                <button class="ult-share-btn copy" onclick="ultCopyLink()" title="Salin"><i class="bi bi-link-45deg"></i></button>
            </div>
        </div>
    </aside>
    
    <!-- Main Content -->
    <article class="ult-article-content">
        <?php
        // Tambahkan ID ke heading untuk TOC
        $content = $article['content'];
        if (!empty($headings)) {
            foreach ($headings as $i => $h) {
                $content = preg_replace(
                    '/<h[' . $h['level'] . '][^>]*>' . preg_quote($h['text'], '/') . '<\/h' . $h['level'] . '>/i',
                    '<h' . $h['level'] . ' id="' . $h['id'] . '">' . $h['text'] . '</h' . $h['level'] . '>',
                    $content,
                    1
                );
            }
        }
        echo nl2br($content);
        ?>
        
        <!-- Tags -->
        <?php if (!empty($article['tags'])): ?>
        <div class="ult-article-tags">
            <span class="ult-article-tags-label"><i class="bi bi-tags-fill"></i> Tags:</span>
            <?php foreach (explode(',', $article['tags']) as $tag): ?>
                <a href="<?= url('berita?q=' . urlencode(trim($tag))) ?>" class="ult-tag">#<?= htmlspecialchars(trim($tag)) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Author Bio -->
        <div class="ult-author-bio">
            <div class="ult-author-bio-avatar"><?= strtoupper(substr($article['author_name'] ?? 'B', 0, 1)) ?></div>
            <div class="ult-author-bio-info">
                <div class="ult-author-role">Penulis</div>
                <h4><?= htmlspecialchars($article['author_name'] ?? 'Tim BAUK UNIMOF') ?></h4>
                <p>Tim redaksi Biro Administrasi Umum dan Keuangan Universitas Muhammadiyah Maumere yang berkomitmen menyajikan informasi transparan dan akuntabel untuk civitas akademika.</p>
            </div>
        </div>
        
        <!-- Share Bar Bottom -->
        <div class="ult-share-bar-bottom">
            <div class="ult-share-bar-bottom-left">
                <div class="ult-article-views">
                    <i class="bi bi-eye-fill"></i>
                    <div><strong><?= number_format($article['views']) ?></strong> views</div>
                </div>
                <button class="ult-print-btn" onclick="window.print()">
                    <i class="bi bi-printer-fill"></i> Cetak Artikel
                </button>
            </div>
            <div class="ult-share-bar">
                <span class="ult-share-bar-label">Bagikan:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('berita/' . $article['slug'])) ?>" target="_blank" class="ult-share-btn fb"><i class="bi bi-facebook"></i></a>
                <a href="https://wa.me/?text=<?= urlencode($article['title'] . ' - ' . url('berita/' . $article['slug'])) ?>" target="_blank" class="ult-share-btn wa"><i class="bi bi-whatsapp"></i></a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode(url('berita/' . $article['slug'])) ?>" target="_blank" class="ult-share-btn tw"><i class="bi bi-twitter-x"></i></a>
                <button class="ult-share-btn copy" onclick="ultCopyLink()"><i class="bi bi-link-45deg"></i></button>
            </div>
        </div>
        
        <!-- Prev / Next Navigation -->
        <div class="ult-article-nav">
            <?php if ($prevArticle): ?>
            <a href="<?= url('berita/' . $prevArticle['slug']) ?>" class="ult-article-nav-item prev">
                <div class="ult-article-nav-label"><i class="bi bi-arrow-left"></i> Artikel Sebelumnya</div>
                <div class="ult-article-nav-title"><?= htmlspecialchars(mb_substr($prevArticle['title'], 0, 60)) ?><?= strlen($prevArticle['title']) > 60 ? '...' : '' ?></div>
            </a>
            <?php else: ?><div></div><?php endif; ?>
            
            <?php if ($nextArticle): ?>
            <a href="<?= url('berita/' . $nextArticle['slug']) ?>" class="ult-article-nav-item next">
                <div class="ult-article-nav-label">Artikel Berikutnya <i class="bi bi-arrow-right"></i></div>
                <div class="ult-article-nav-title"><?= htmlspecialchars(mb_substr($nextArticle['title'], 0, 60)) ?><?= strlen($nextArticle['title']) > 60 ? '...' : '' ?></div>
            </a>
            <?php endif; ?>
        </div>
        
        <!-- CTA -->
        <div class="ult-article-cta">
            <div class="ult-article-cta-content">
                <h3>Punya Pertanyaan?</h3>
                <p>Tim kami siap membantu Anda. Hubungi kami untuk informasi lebih lanjut.</p>
                <a href="<?= url('kontak') ?>" class="btn btn-primary" style="background:#fff;color:var(--color-primary);"><i class="bi bi-chat-dots-fill"></i> Hubungi Kami</a>
            </div>
        </div>
    </article>
</div>

<!-- ===== RELATED ARTICLES ===== -->
<?php if (!empty($related)): ?>
<section class="ult-related">
    <div class="ult-related-header">
        <h2>Artikel Terkait</h2>
        <p>Baca juga artikel lain dalam kategori <?= ucfirst($article['category']) ?></p>
    </div>
    <div class="ult-related-grid">
        <?php foreach ($related as $item): 
            $bgColor = $categoryColors[$item['category']] ?? '#1a5276';
        ?>
        <article class="ult-news-card">
            <div class="ult-news-card-image">
                <?php if (!empty($item['featured_image'])): ?>
                    <img src="<?= url('uploads/berita/' . $item['featured_image']) ?>" alt="">
                <?php else: ?>
                    <div class="ult-news-card-placeholder"><i class="bi bi-newspaper"></i></div>
                <?php endif; ?>
                <span class="ult-news-card-category" style="background:<?= $bgColor ?>;"><?= ucfirst($item['category']) ?></span>
            </div>
            <div class="ult-news-card-body">
                <div class="ult-news-card-meta">
                    <span><i class="bi bi-calendar"></i> <?= date('d M Y', strtotime($item['published_at'])) ?></span>
                    <span><i class="bi bi-eye"></i> <?= number_format($item['views']) ?></span>
                </div>
                <h3 class="ult-news-card-title"><a href="<?= url('berita/' . $item['slug']) ?>"><?= htmlspecialchars($item['title']) ?></a></h3>
                <p class="ult-news-card-excerpt"><?= htmlspecialchars(mb_substr($item['excerpt'] ?? $item['content'] ?? '', 0, 100)) ?>...</p>
                <div class="ult-news-card-footer">
                    <span></span>
                    <a href="<?= url('berita/' . $item['slug']) ?>" class="ult-news-card-link">Baca <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ===== SCRIPTS ===== -->
<script>
(function() {
    'use strict';
    
    // Reading Progress Bar
    const progressBar = document.getElementById('ultReadingProgress');
    if (progressBar) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = (scrollTop / docHeight) * 100;
            progressBar.style.width = Math.min(progress, 100) + '%';
        });
    }
    
    // TOC active state on scroll
    const tocLinks = document.querySelectorAll('.ult-toc-list a');
    const headings = [];
    tocLinks.forEach(function(link) {
        const id = link.getAttribute('href').substring(1);
        const heading = document.getElementById(id);
        if (heading) headings.push({ id: id, el: heading, link: link });
    });
    
    if (headings.length > 0) {
        window.addEventListener('scroll', function() {
            const scrollPos = window.pageYOffset + 150;
            let active = headings[0];
            
            for (let i = 0; i < headings.length; i++) {
                if (headings[i].el.offsetTop <= scrollPos) {
                    active = headings[i];
                }
            }
            
            tocLinks.forEach(function(link) { link.classList.remove('active'); });
            if (active) active.link.classList.add('active');
        });
    }
    
    // Smooth scroll for TOC links
    tocLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('href').substring(1);
            const target = document.getElementById(id);
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
})();

// Copy link function
function ultCopyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(function() {
        if (typeof showToast === 'function') {
            showToast('Link berhasil disalin!', 'success');
        } else {
            alert('Link berhasil disalin ke clipboard!');
        }
    });
}
</script>