<?php
/**
 * BAUK UNIMOF - Homepage ULTIMATE EDITION
 * Elegant • Professional • Multimate
 */
$db = \App\Helpers\Database::getInstance();

// 1. Ambil data dinamis dari tabel settings
$settingsData = $db->fetchAll("SELECT `key`, `value` FROM settings");
$cfg = [];
foreach ($settingsData as $s) {
    $cfg[$s['key']] = $s['value'];
}

// Helper function untuk mendapatkan URL gambar dari settings
function getPubImage($key, $default = '') {
    global $cfg;
    if (!empty($cfg[$key])) {
        return url('uploads/settings/' . $cfg[$key]);
    }
    return $default;
}

// 2. Ambil data berita & pengumuman
$news      = $db->fetchAll("SELECT * FROM berita WHERE is_published = 1 AND deleted_at IS NULL ORDER BY published_at DESC LIMIT 4");
$featured  = $news[0] ?? null;
$otherNews = array_slice($news, 1, 3);
$pengumumanAktif = $db->fetchAll("SELECT * FROM pengumuman WHERE is_active = 1 AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY priority DESC, created_at DESC LIMIT 3");

// 3. Counter dinamis dari DB
$totalPegawai = $db->count('pegawai', "status = 'active' AND deleted_at IS NULL") ?: 150;
$totalAset    = $db->count('aset', "status = 'active' AND deleted_at IS NULL") ?: 2500;
$transaksi    = $db->count('transaksi_keuangan', "MONTH(transaction_date) = MONTH(CURRENT_DATE()) AND YEAR(transaction_date) = YEAR(CURRENT_DATE()) AND deleted_at IS NULL") ?: 850;
$departemen   = $db->count('departments', "status = 'active' AND deleted_at IS NULL") ?: 12;
?>

<!-- =====================================================
     ULTIMATE STYLES (Scoped, tidak mengganggu public-style.css)
     ===================================================== -->
<style>
/* ===== HERO ENHANCEMENTS ===== */
.hero-section{position:relative;overflow:hidden;}
.hero-floating-shapes{position:absolute;inset:0;pointer-events:none;z-index:1;}
.hero-shape{position:absolute;opacity:.06;animation:float 20s infinite ease-in-out;}
.hero-shape-1{top:10%;left:5%;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,#e67e22 0%,transparent 70%);animation-delay:0s;}
.hero-shape-2{top:60%;right:8%;width:380px;height:380px;border-radius:50%;background:radial-gradient(circle,#27ae60 0%,transparent 70%);animation-delay:-5s;}
.hero-shape-3{top:30%;right:25%;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,#fff 0%,transparent 70%);animation-delay:-10s;}
@keyframes float{0%,100%{transform:translate(0,0) scale(1);}33%{transform:translate(30px,-30px) scale(1.05);}66%{transform:translate(-20px,20px) scale(.95);}}

.hero-meta-strip{display:flex;gap:1.5rem;margin-top:2rem;flex-wrap:wrap;}
.hero-meta-item{display:flex;align-items:center;gap:.5rem;color:rgba(255,255,255,.9);font-size:.85rem;font-weight:500;}
.hero-meta-item i{color:var(--color-accent);font-size:1rem;}

.hero-quick-access{margin-top:3rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;max-width:750px;}
.hero-quick-card{background:rgba(255,255,255,.08);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.15);border-radius:var(--radius-lg);padding:1.25rem;display:flex;align-items:center;gap:1rem;color:#fff!important;transition:all .3s ease;cursor:pointer;text-decoration:none!important;}
.hero-quick-card:hover{background:rgba(255,255,255,.18);transform:translateY(-4px);border-color:var(--color-accent);box-shadow:0 10px 30px rgba(0,0,0,.2);}
.hero-quick-icon{width:48px;height:48px;background:var(--color-accent);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;box-shadow:0 4px 12px rgba(230,126,34,.3);}
.hero-quick-text{font-size:.9rem;line-height:1.3;}
.hero-quick-text strong{display:block;font-size:1rem;margin-bottom:2px;}

.scroll-indicator{position:absolute;bottom:40px;left:50%;transform:translateX(-50%);z-index:5;color:rgba(255,255,255,.7);text-align:center;font-size:.75rem;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:opacity .3s;}
.scroll-indicator:hover{opacity:1;color:#fff;}
.scroll-indicator-mouse{width:26px;height:42px;border:2px solid rgba(255,255,255,.5);border-radius:13px;margin:0 auto .5rem;position:relative;}
.scroll-indicator-wheel{width:4px;height:8px;background:rgba(255,255,255,.9);border-radius:2px;position:absolute;top:6px;left:50%;transform:translateX(-50%);animation:scrollWheel 2s infinite;}
@keyframes scrollWheel{0%{opacity:1;top:6px;}100%{opacity:0;top:22px;}}

/* ===== TRUST STRIP ===== */
.trust-strip{background:var(--color-surface);border-bottom:1px solid var(--color-border-light);padding:1.5rem 0;}
.trust-grid{display:flex;justify-content:space-around;align-items:center;flex-wrap:wrap;gap:1.5rem;}
.trust-item{display:flex;align-items:center;gap:.75rem;color:var(--color-text-secondary);font-size:.9rem;font-weight:500;}
.trust-icon{width:48px;height:48px;background:linear-gradient(135deg,var(--color-primary-50),var(--color-primary-100));color:var(--color-primary);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.4rem;}
.trust-item strong{display:block;color:var(--color-text);font-weight:700;font-size:.95rem;}

/* ===== STATS ENHANCEMENTS ===== */
.stat-card{position:relative;overflow:hidden;transition:all .4s ease;background:var(--color-surface);border-radius:var(--radius-xl);padding:2rem 1.5rem;border:1px solid var(--color-border-light);text-align:center;}
.stat-card::before{content:'';position:absolute;top:0;left:0;width:100%;height:4px;background:linear-gradient(90deg,var(--color-primary),var(--color-accent));transform:scaleX(0);transform-origin:left;transition:transform .4s ease;}
.stat-card:hover{transform:translateY(-6px);box-shadow:0 15px 40px rgba(0,0,0,.08);border-color:transparent;}
.stat-card:hover::before{transform:scaleX(1);}
.stat-icon{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto 1rem;color:#fff;box-shadow:0 8px 20px rgba(0,0,0,.15);}
.stat-card:nth-child(1) .stat-icon{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-light));}
.stat-card:nth-child(2) .stat-icon{background:linear-gradient(135deg,#27ae60,#2ecc71);}
.stat-card:nth-child(3) .stat-icon{background:linear-gradient(135deg,#e67e22,#f39c12);}
.stat-card:nth-child(4) .stat-icon{background:linear-gradient(135deg,#8e44ad,#9b59b6);}
.stat-number{font-family:'Playfair Display',serif;font-size:2.5rem;font-weight:700;color:var(--color-text);line-height:1;margin-bottom:.5rem;}
.stat-label{font-size:.9rem;color:var(--color-text-secondary);font-weight:600;}
.stat-trend{display:inline-flex;align-items:center;gap:4px;margin-top:.75rem;font-size:.75rem;color:var(--color-success);font-weight:600;background:rgba(16,185,129,.1);padding:.25rem .75rem;border-radius:50px;}
.stat-trend i{font-size:.7rem;}

/* ===== SAMBUTAN ENHANCEMENTS ===== */
.sambutan-section{position:relative;}
.sambutan-wrapper{display:grid;grid-template-columns:350px 1fr;gap:3rem;align-items:start;}
.sambutan-photo{position:relative;}
.sambutan-photo::before{content:'';position:absolute;inset:-15px;border:2px dashed var(--color-primary-100);border-radius:50%;animation:rotate 30s linear infinite;}
@keyframes rotate{to{transform:rotate(360deg);}}
.sambutan-quote-mark{position:absolute;top:-30px;left:-10px;font-size:7rem;color:var(--color-primary-100);font-family:Georgia,serif;line-height:1;z-index:0;opacity:.5;}
.sambutan-text{position:relative;z-index:1;}
.sambutan-signature{margin-top:2.5rem;padding-top:1.5rem;border-top:2px solid var(--color-border);display:flex;align-items:center;gap:1.5rem;}
.signature-visual{font-family:'Brush Script MT',cursive;font-size:2rem;color:var(--color-primary);font-weight:700;}
.signature-info{font-size:.85rem;color:var(--color-text-secondary);}
.signature-info strong{display:block;color:var(--color-text);font-size:1rem;}

/* ===== WHY CHOOSE US ===== */
.why-section{background:linear-gradient(180deg,var(--color-bg) 0%,var(--color-surface) 100%);}
.why-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;}
.why-card{background:var(--color-surface);padding:2.5rem 2rem;border-radius:var(--radius-xl);box-shadow:var(--shadow-sm);border:1px solid var(--color-border-light);transition:all .4s ease;position:relative;overflow:hidden;}
.why-card::after{content:'';position:absolute;top:0;left:0;width:100%;height:4px;background:linear-gradient(90deg,var(--color-primary),var(--color-accent));transform:scaleX(0);transform-origin:left;transition:transform .4s ease;}
.why-card:hover{transform:translateY(-8px);box-shadow:var(--shadow-xl);border-color:var(--color-primary-100);}
.why-card:hover::after{transform:scaleX(1);}
.why-icon{width:64px;height:64px;background:linear-gradient(135deg,var(--color-primary-50),var(--color-primary-100));color:var(--color-primary);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin-bottom:1.5rem;transition:all .4s ease;}
.why-card:hover .why-icon{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-light));color:#fff;transform:rotate(-5deg) scale(1.05);}
.why-card h3{font-size:1.15rem;font-weight:700;margin-bottom:.75rem;color:var(--color-text);}
.why-card p{font-size:.9rem;color:var(--color-text-secondary);line-height:1.7;margin:0;}

/* ===== SERVICES ENHANCEMENTS ===== */
.services-grid{counter-reset:service-counter;display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;}
.service-card{counter-increment:service-counter;position:relative;background:var(--color-surface);padding:2.5rem 2rem;border-radius:var(--radius-xl);border:1px solid var(--color-border-light);transition:all .4s ease;text-decoration:none!important;color:var(--color-text)!important;display:flex;flex-direction:column;}
.service-card::before{content:'0' counter(service-counter);position:absolute;top:1.5rem;right:1.5rem;font-family:'Playfair Display',serif;font-size:3rem;font-weight:700;color:var(--color-primary-100);opacity:.4;transition:all .4s ease;line-height:1;}
.service-card:hover{transform:translateY(-8px);box-shadow:var(--shadow-xl);border-color:var(--color-primary-100);}
.service-card:hover::before{color:var(--color-accent);opacity:1;transform:scale(1.1);}
.service-features{list-style:none;padding:0;margin:1.5rem 0;flex:1;}
.service-features li{display:flex;align-items:center;gap:.625rem;font-size:.875rem;color:var(--color-text-secondary);margin-bottom:.5rem;}
.service-features li i{color:var(--color-success);font-size:.9rem;flex-shrink:0;}
.service-link{color:var(--color-primary);font-weight:700;font-size:.9rem;display:inline-flex;align-items:center;gap:.5rem;transition:gap .3s;margin-top:auto;}
.service-card:hover .service-link{gap:.75rem;color:var(--color-accent);}

/* ===== NEWS ENHANCEMENTS ===== */
.news-layout{display:grid;grid-template-columns:1.6fr 1fr;gap:2rem;}
.news-featured{background:var(--color-surface);border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow-md);transition:all .3s ease;height:100%;border:1px solid var(--color-border-light);}
.news-featured:hover{box-shadow:var(--shadow-xl);transform:translateY(-4px);border-color:var(--color-primary-100);}
.news-featured .news-image{height:340px;position:relative;}
.news-featured .news-image img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease;}
.news-featured:hover .news-image img{transform:scale(1.03);}
.news-featured .news-body{padding:2rem;}
.news-featured .news-title{font-family:'Playfair Display',serif;font-size:1.6rem;line-height:1.3;margin-bottom:1rem;}
.news-featured .news-excerpt{font-size:1rem;line-height:1.7;margin-bottom:1.5rem;color:var(--color-text-secondary);}
.news-featured .news-category{font-size:.8rem;padding:.375rem 1rem;}
.news-featured-badge{display:inline-flex;align-items:center;gap:.375rem;background:var(--color-accent);color:#fff;padding:.375rem 1rem;border-radius:var(--radius-full);font-size:.75rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:1rem;}
.news-side-list{display:flex;flex-direction:column;gap:1rem;}
.news-side-item{display:flex;gap:1rem;background:var(--color-surface);padding:1rem;border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);transition:all .3s ease;color:var(--color-text)!important;border:1px solid var(--color-border-light);text-decoration:none!important;}
.news-side-item:hover{transform:translateX(6px);box-shadow:var(--shadow-md);border-color:var(--color-primary-100);}
.news-side-thumb{width:100px;height:100px;flex-shrink:0;border-radius:var(--radius-md);overflow:hidden;background:var(--color-primary-50);display:flex;align-items:center;justify-content:center;color:var(--color-primary-100);font-size:2rem;}
.news-side-thumb img{width:100%;height:100%;object-fit:cover;}
.news-side-info{flex:1;min-width:0;display:flex;flex-direction:column;justify-content:center;}
.news-side-info .news-category{position:static;font-size:.65rem;padding:2px 8px;margin-bottom:.5rem;display:inline-block;width:fit-content;}
.news-side-info h4{font-size:1rem;font-weight:700;margin-bottom:.5rem;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.news-side-info h4 a{color:var(--color-text)!important;transition:color .2s;}
.news-side-info h4 a:hover{color:var(--color-primary)!important;}
.news-side-meta{font-size:.75rem;color:var(--color-text-muted);display:flex;align-items:center;gap:.75rem;margin-top:auto;}
.news-side-meta i{font-size:.7rem;}

/* ===== QUICK SYSTEMS ===== */
.systems-section{background:var(--color-primary-dark);color:#fff;padding:5rem 0;position:relative;overflow:hidden;}
.systems-section::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='1.5'/%3E%3C/g%3E%3C/svg%3E");}
.systems-section .section-badge{background:rgba(230,126,34,.2);color:var(--color-accent);}
.systems-section .section-title{color:#fff;}
.systems-section .section-subtitle{color:rgba(255,255,255,.7);}
.systems-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;position:relative;z-index:1;}
.system-card{background:rgba(255,255,255,.05);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius-lg);padding:2rem 1.5rem;text-align:center;color:#fff!important;transition:all .3s ease;text-decoration:none!important;}
.system-card:hover{background:rgba(255,255,255,.12);transform:translateY(-6px);border-color:var(--color-accent);box-shadow:0 10px 30px rgba(0,0,0,.2);}
.system-icon{width:64px;height:64px;background:linear-gradient(135deg,var(--color-accent),#f39c12);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto 1.25rem;box-shadow:0 8px 20px rgba(230,126,34,.3);transition:transform .3s;}
.system-card:hover .system-icon{transform:scale(1.1) rotate(-5deg);}
.system-card h4{font-size:1.05rem;font-weight:700;margin-bottom:.5rem;}
.system-card span{font-size:.8rem;color:rgba(255,255,255,.6);}

/* ===== DOWNLOAD CENTER ===== */
.download-section{background:var(--color-bg);}
.download-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;}
.download-card{background:var(--color-surface);padding:2rem;border-radius:var(--radius-xl);box-shadow:var(--shadow-sm);display:flex;gap:1.25rem;align-items:flex-start;transition:all .3s ease;border:1px solid var(--color-border-light);color:var(--color-text)!important;text-decoration:none!important;}
.download-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg);border-color:var(--color-primary-100);}
.download-icon{width:60px;height:60px;background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff;border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;font-size:1.75rem;flex-shrink:0;transition:transform .3s;}
.download-card:nth-child(2) .download-icon{background:linear-gradient(135deg,#2563eb,#3b82f6);}
.download-card:nth-child(3) .download-icon{background:linear-gradient(135deg,#16a34a,#22c55e);}
.download-card:nth-child(4) .download-icon{background:linear-gradient(135deg,#9333ea,#a855f7);}
.download-card:hover .download-icon{transform:scale(1.1) rotate(-5deg);}
.download-info h4{font-size:1.1rem;font-weight:700;margin-bottom:.5rem;}
.download-info p{font-size:.85rem;color:var(--color-text-secondary);margin-bottom:.75rem;line-height:1.6;}
.download-count{font-size:.8rem;color:var(--color-text-muted);display:flex;align-items:center;gap:.375rem;font-weight:600;}

/* ===== FAQ SECTION ===== */
.faq-section{background:var(--color-surface);}
.faq-container{max-width:850px;margin:0 auto;}
.faq-item{background:var(--color-bg);border-radius:var(--radius-lg);margin-bottom:1rem;overflow:hidden;transition:all .3s ease;border:1px solid transparent;}
.faq-item.active{background:var(--color-surface);box-shadow:var(--shadow-md);border-color:var(--color-primary-100);}
.faq-question{width:100%;padding:1.5rem;background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:1rem;text-align:left;font-weight:600;font-size:1.05rem;color:var(--color-text);transition:color .2s;}
.faq-question:hover{color:var(--color-primary);}
.faq-question i{transition:transform .3s ease;color:var(--color-primary);font-size:1.25rem;flex-shrink:0;}
.faq-item.active .faq-question i{transform:rotate(180deg);}
.faq-answer{max-height:0;overflow:hidden;transition:max-height .4s ease,padding .4s ease;padding:0 1.5rem;color:var(--color-text-secondary);line-height:1.8;font-size:.95rem;}
.faq-item.active .faq-answer{max-height:500px;padding:0 1.5rem 1.5rem;}

/* ===== CTA ENHANCEMENTS ===== */
.cta-section{position:relative;background:linear-gradient(135deg,var(--color-primary-dark) 0%,var(--color-primary) 100%);color:#fff;padding:5rem 0;overflow:hidden;}
.cta-section::before{content:'';position:absolute;top:-50%;right:-10%;width:500px;height:500px;background:radial-gradient(circle,rgba(230,126,34,.15) 0%,transparent 70%);border-radius:50%;}
.cta-content{position:relative;z-index:1;text-align:center;max-width:800px;margin:0 auto;}
.cta-content h2{font-family:'Playfair Display',serif;font-size:2.5rem;margin-bottom:1rem;}
.cta-content p{font-size:1.1rem;color:rgba(255,255,255,.85);margin-bottom:2.5rem;line-height:1.7;}
.cta-contact-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5rem;margin-bottom:2.5rem;}
.cta-contact-card{background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);border-radius:var(--radius-lg);padding:1.5rem;text-align:center;color:#fff!important;transition:all .3s ease;text-decoration:none!important;}
.cta-contact-card:hover{background:rgba(255,255,255,.18);transform:translateY(-4px);border-color:var(--color-accent);}
.cta-contact-icon{width:56px;height:56px;background:var(--color-accent);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;box-shadow:0 6px 15px rgba(230,126,34,.3);}
.cta-contact-card h5{font-size:1rem;margin-bottom:.5rem;font-weight:700;}
.cta-contact-card p{font-size:.9rem;margin:0;opacity:.9;}

/* ===== PARTNERS SECTION ===== */
.partners-section{background:var(--color-bg);padding:4rem 0;border-top:1px solid var(--color-border-light);}
.partners-grid{display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:2rem 3rem;}
.partner-item{display:flex;align-items:center;gap:.75rem;color:var(--color-text-secondary);font-weight:600;font-size:1rem;opacity:.7;transition:all .3s ease;}
.partner-item:hover{opacity:1;color:var(--color-primary);transform:translateY(-2px);}
.partner-item i{font-size:1.75rem;color:var(--color-primary);}

/* ===== PENGUMUMAN TICKER ===== */
.pengumuman-ticker{background:linear-gradient(90deg,var(--color-accent),#f39c12);color:#fff;padding:.875rem 0;overflow:hidden;position:relative;z-index:10;}
.ticker-label{display:inline-flex;align-items:center;gap:.5rem;background:rgba(0,0,0,.2);padding:.5rem 1rem;border-radius:var(--radius-full);font-size:.8rem;font-weight:700;margin-right:1.5rem;flex-shrink:0;}
.ticker-wrapper{display:flex;align-items:center;}
.ticker-content{display:flex;gap:3rem;white-space:nowrap;animation:ticker 40s linear infinite;}
.ticker-item{display:flex;align-items:center;gap:.5rem;font-size:.9rem;font-weight:500;}
.ticker-item i{font-size:.9rem;}
@keyframes ticker{0%{transform:translateX(0);}100%{transform:translateX(-50%);}}

/* ===== RESPONSIVE ULTIMATE ===== */
@media (max-width:992px){
    .sambutan-wrapper{grid-template-columns:1fr;}
    .sambutan-photo{max-width:300px;margin:0 auto;}
    .news-layout{grid-template-columns:1fr;}
    .news-side-list{flex-direction:row;overflow-x:auto;padding-bottom:.5rem;scrollbar-width:none;}
    .news-side-list::-webkit-scrollbar{display:none;}
    .news-side-item{min-width:320px;}
}
@media (max-width:768px){
    .hero-quick-access{grid-template-columns:1fr;}
    .hero-meta-strip{justify-content:center;}
    .trust-grid{justify-content:center;}
    .scroll-indicator{display:none;}
    .sambutan-quote-mark{font-size:5rem;top:-20px;left:0;}
    .systems-grid{grid-template-columns:repeat(2,1fr);}
    .cta-contact-grid{grid-template-columns:1fr;}
    .cta-content h2{font-size:2rem;}
}
@media (max-width:480px){
    .systems-grid{grid-template-columns:1fr;}
    .news-side-list{flex-direction:column;}
    .news-side-item{min-width:0;}
}
</style>

<!-- =====================================================
     PENGUMUMAN TICKER (jika ada pengumuman aktif)
     ===================================================== -->
<?php if (!empty($pengumumanAktif)): ?>
<div class="pengumuman-ticker">
    <div class="ticker-wrapper">
        <span class="ticker-label"><i class="bi bi-megaphone-fill"></i> PENGUMUMAN PENTING</span>
        <div class="ticker-content">
            <?php foreach ($pengumumanAktif as $p): ?>
                <span class="ticker-item"><i class="bi bi-dot"></i> <?= htmlspecialchars($p['title']) ?></span>
            <?php endforeach; ?>
            <?php foreach ($pengumumanAktif as $p): ?>
                <span class="ticker-item"><i class="bi bi-dot"></i> <?= htmlspecialchars($p['title']) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- =====================================================
     HERO SECTION (ULTIMATE)
     ===================================================== -->
<section class="hero-section">
    <div class="hero-overlay"></div>
    
    <div class="hero-floating-shapes">
        <div class="hero-shape hero-shape-1"></div>
        <div class="hero-shape hero-shape-2"></div>
        <div class="hero-shape hero-shape-3"></div>
    </div>
    
    <div class="hero-content">
        <div class="container">
            <div class="hero-text">
                <span class="hero-badge"><i class="bi bi-bank2"></i> Universitas Muhammadiyah Maumere</span>
                <h1 class="hero-title">Biro Administrasi Umum<br><span class="text-accent">& Keuangan</span></h1>
                <p class="hero-subtitle">Mewujudkan tata kelola universitas yang transparan, efisien, dan terintegrasi melalui sistem informasi modern berbasis <em>Good University Governance</em>.</p>
                
                <div class="hero-actions">
                    <a href="<?= url('layanan/keuangan') ?>" class="btn btn-primary btn-lg"><i class="bi bi-cash-stack"></i> Layanan Keuangan</a>
                    <a href="<?= url('tentang') ?>" class="btn btn-outline btn-lg"><i class="bi bi-info-circle"></i> Pelajari Lebih</a>
                </div>
                
                <div class="hero-meta-strip">
                    <div class="hero-meta-item"><i class="bi bi-patch-check-fill"></i> Terakreditasi BAN-PT</div>
                    <div class="hero-meta-item"><i class="bi bi-shield-fill-check"></i> ISO 9001:2015</div>
                    <div class="hero-meta-item"><i class="bi bi-clock-fill"></i> Layanan 24/7 Online</div>
                </div>
                
                <div class="hero-quick-access">
                    <a href="<?= url('layanan/kepegawaian') ?>" class="hero-quick-card">
                        <div class="hero-quick-icon"><i class="bi bi-person-vcard-fill"></i></div>
                        <div class="hero-quick-text"><strong>e-Kepegawaian</strong>Data & Karir Pegawai</div>
                    </a>
                    <a href="<?= url('layanan/keuangan') ?>" class="hero-quick-card">
                        <div class="hero-quick-icon"><i class="bi bi-credit-card-2-front-fill"></i></div>
                        <div class="hero-quick-text"><strong>e-Budgeting</strong>Sistem Anggaran</div>
                    </a>
                    <a href="<?= url('layanan/aset') ?>" class="hero-quick-card">
                        <div class="hero-quick-icon"><i class="bi bi-box2-heart-fill"></i></div>
                        <div class="hero-quick-text"><strong>SI-ASET</strong>Inventaris Kampus</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="scroll-indicator" onclick="document.querySelector('.trust-strip').scrollIntoView({behavior: 'smooth'})">
        <div class="scroll-indicator-mouse"><div class="scroll-indicator-wheel"></div></div>
        <span>Scroll</span>
    </div>
    
    <div class="hero-wave">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#ffffff"/>
        </svg>
    </div>
</section>

<!-- =====================================================
     TRUST STRIP
     ===================================================== -->
<section class="trust-strip">
    <div class="container">
        <div class="trust-grid">
            <div class="trust-item">
                <div class="trust-icon"><i class="bi bi-award-fill"></i></div>
                <div><strong>Terakreditasi</strong><span>BAN-PT Unggul</span></div>
            </div>
            <div class="trust-item">
                <div class="trust-icon"><i class="bi bi-shield-fill-check"></i></div>
                <div><strong>ISO 9001:2015</strong><span>Manajemen Mutu</span></div>
            </div>
            <div class="trust-item">
                <div class="trust-icon"><i class="bi bi-globe2"></i></div>
                <div><strong>Muhammadiyah</strong><span>Jaringan Global</span></div>
            </div>
            <div class="trust-item">
                <div class="trust-icon"><i class="bi bi-patch-check-fill"></i></div>
                <div><strong>Terverifikasi</strong><span>Kemendikbudristek</span></div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     STATS SECTION (ULTIMATE)
     ===================================================== -->
<section class="stats-section section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <div class="stat-number" data-count="<?= $totalPegawai ?>">0</div>
                <div class="stat-label">Total Pegawai Aktif</div>
                <div class="stat-trend"><i class="bi bi-arrow-up-short"></i> Terus Berkembang</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
                <div class="stat-number" data-count="<?= $totalAset ?>">0</div>
                <div class="stat-label">Aset Terkelola</div>
                <div class="stat-trend"><i class="bi bi-arrow-up-short"></i> Inventaris Terpadu</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                <div class="stat-number" data-count="<?= $transaksi ?>">0</div>
                <div class="stat-label">Transaksi/Bulan</div>
                <div class="stat-trend"><i class="bi bi-graph-up-arrow"></i> Real-time Monitoring</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-buildings-fill"></i></div>
                <div class="stat-number" data-count="<?= $departemen ?>">0</div>
                <div class="stat-label">Unit Kerja Terpadu</div>
                <div class="stat-trend"><i class="bi bi-diagram-3-fill"></i> Koordinasi Optimal</div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     SAMBUTAN SECTION (ULTIMATE - DINAMIS DARI ADMIN)
     ===================================================== -->
<section class="section sambutan-section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-quote"></i> Sambutan</span>
                <h2 class="section-title">Kepala Biro Administrasi Umum & Keuangan</h2>
                <p class="section-subtitle" style="margin:0 auto;">Pesan dan komitmen kepemimpinan BAUK UNIMOF</p>
            </div>
        </div>
        
        <div class="sambutan-wrapper">
            <div class="sambutan-image">
                <div class="sambutan-photo">
                    <!-- FOTO DINAMIS: Mengambil dari Pengaturan Admin -->
                    <img src="<?= getPubImage('kepala_bauk_photo', asset('images/user-placeholder.png')) ?>" 
                         alt="Kepala BAUK" 
                         style="width:100%; height:100%; object-fit:cover; border-radius:50%; border: 4px solid var(--color-border); background: #fff;">
                </div>
                <div class="sambutan-info">
                    <!-- NAMA & JABATAN DINAMIS: Mengambil dari Pengaturan Admin -->
                    <h4><?= htmlspecialchars($cfg['kepala_bauk_name'] ?? 'Nama Kepala BAUK') ?></h4>
                    <p><?= htmlspecialchars($cfg['kepala_bauk_title'] ?? 'Kepala Biro Administrasi Umum dan Keuangan') ?></p>
                </div>
            </div>
            <div class="sambutan-text">
                <span class="sambutan-quote-mark">"</span>
                <p><em>Assalamu'alaikum Warahmatullahi Wabarakatuh.</em></p>
                <p>Segala puji bagi Allah SWT yang telah melimpahkan rahmat dan karunia-Nya. Website Biro Administrasi Umum dan Keuangan (BAUK) Universitas Muhammadiyah Maumere hadir sebagai wujud nyata komitmen kami dalam menjawab tantangan era digital.</p>
                <p>Melalui platform ini, kami berusaha memberikan pelayanan yang inovatif dan efisien. Berbagai layanan—mulai dari pengelolaan anggaran, pencatatan keuangan, inventarisasi aset, hingga administrasi kepegawaian—dapat diakses dengan mudah oleh seluruh civitas akademika.</p>
                <p>Kami percaya bahwa pelayanan modern berbasis teknologi informasi merupakan langkah strategis dalam menciptakan <em>Good University Governance</em>. BAUK UNIMOF akan terus beradaptasi dan berinovasi.</p>
                <p><em>Wassalamu'alaikum Warahmatullahi Wabarakatuh.</em></p>
                
                <div class="sambutan-signature">
                    <div class="signature-visual">Kepala BAUK</div>
                    <div class="signature-info">
                        <strong><?= htmlspecialchars($cfg['kepala_bauk_name'] ?? 'Kepala Biro') ?></strong>
                        Universitas Muhammadiyah Maumere
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     WHY CHOOSE US
     ===================================================== -->
<section class="section why-section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-trophy-fill"></i> Keunggulan Kami</span>
                <h2 class="section-title">Mengapa Memilih Layanan BAUK UNIMOF?</h2>
                <p class="section-subtitle" style="margin:0 auto;">Komitmen kami dalam memberikan pelayanan terbaik bagi civitas akademika</p>
            </div>
        </div>
        
        <div class="why-grid">
            <div class="why-card">
                <div class="why-icon"><i class="bi bi-shield-lock-fill"></i></div>
                <h3>Keamanan Data Terjamin</h3>
                <p>Sistem dilindungi dengan enkripsi AES-256, proteksi CSRF, dan audit trail lengkap untuk menjaga kerahasiaan data.</p>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="bi bi-eye-fill"></i></div>
                <h3>Transparansi Penuh</h3>
                <p>Setiap transaksi keuangan dan pengelolaan aset tercatat dengan jelas, dapat dilacak, dan dipertanggungjawabkan.</p>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                <h3>Layanan Cepat & Efisien</h3>
                <p>Proses administrasi yang dulunya memakan waktu berhari-hari kini dapat diselesaikan dalam hitungan menit.</p>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <h3>Monitoring Real-time</h3>
                <p>Dashboard interaktif menyajikan data terkini mengenai keuangan, aset, dan kepegawaian untuk keputusan strategis.</p>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="bi bi-diagram-3-fill"></i></div>
                <h3>Integrasi Sistem</h3>
                <p>Terhubung dengan berbagai sistem informasi kampus seperti SIAKAD, SIMPEG, dan PMB untuk alur kerja seamless.</p>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="bi bi-people-fill"></i></div>
                <h3>SDM Profesional</h3>
                <p>Didukung oleh tim profesional yang kompeten di bidangnya dengan komitmen pelayanan prima dan ramah.</p>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     SERVICES SECTION (ULTIMATE)
     ===================================================== -->
<section class="section services-section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-grid-3x3-gap-fill"></i> Layanan Kami</span>
                <h2 class="section-title">Bidang Layanan BAUK</h2>
                <p class="section-subtitle" style="margin:0 auto;">Empat pilar utama pelayanan administrasi dan keuangan universitas</p>
            </div>
        </div>
        
        <div class="services-grid">
            <a href="<?= url('layanan/kepegawaian') ?>" class="service-card">
                <div class="service-icon"><i class="bi bi-person-badge-fill"></i></div>
                <h3>Kepegawaian</h3>
                <p>Pengelolaan sumber daya manusia yang komprehensif dan terintegrasi.</p>
                <ul class="service-features">
                    <li><i class="bi bi-check-circle-fill"></i> Administrasi & mutasi pegawai</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pengembangan karir & kompetensi</li>
                    <li><i class="bi bi-check-circle-fill"></i> Penggajian & kesejahteraan</li>
                </ul>
                <span class="service-link">Selengkapnya <i class="bi bi-arrow-right"></i></span>
            </a>
            
            <a href="<?= url('layanan/keuangan') ?>" class="service-card">
                <div class="service-icon"><i class="bi bi-wallet2"></i></div>
                <h3>Keuangan</h3>
                <p>Pengelolaan keuangan yang akuntabel, transparan, dan sesuai regulasi.</p>
                <ul class="service-features">
                    <li><i class="bi bi-check-circle-fill"></i> Penyusunan RKAT tahunan</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pencatatan transaksi real-time</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pelaporan keuangan berkala</li>
                </ul>
                <span class="service-link">Selengkapnya <i class="bi bi-arrow-right"></i></span>
            </a>
            
            <a href="<?= url('layanan/aset') ?>" class="service-card">
                <div class="service-icon"><i class="bi bi-house-gear-fill"></i></div>
                <h3>Aset & Rumah Tangga</h3>
                <p>Pengelolaan aset universitas dengan sistem inventarisasi modern.</p>
                <ul class="service-features">
                    <li><i class="bi bi-check-circle-fill"></i> Inventarisasi & pelabelan aset</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pemeliharaan terjadwal</li>
                    <li><i class="bi bi-check-circle-fill"></i> Sarana prasarana kampus</li>
                </ul>
                <span class="service-link">Selengkapnya <i class="bi bi-arrow-right"></i></span>
            </a>
            
            <a href="<?= url('layanan/k3l') ?>" class="service-card">
                <div class="service-icon"><i class="bi bi-shield-fill-check"></i></div>
                <h3>Tim K3L</h3>
                <p>Mewujudkan lingkungan kampus yang aman, sehat, dan nyaman.</p>
                <ul class="service-features">
                    <li><i class="bi bi-check-circle-fill"></i> Kebijakan & SOP K3</li>
                    <li><i class="bi bi-check-circle-fill"></i> Mitigasi bencana & evakuasi</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pengelolaan lingkungan hijau</li>
                </ul>
                <span class="service-link">Selengkapnya <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>

<!-- =====================================================
     NEWS SECTION (ULTIMATE)
     ===================================================== -->
<section class="section news-section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-badge"><i class="bi bi-newspaper"></i> Informasi Terkini</span>
                <h2 class="section-title">Berita & Pengumuman</h2>
                <p class="section-subtitle">Update terbaru seputar kegiatan dan informasi BAUK UNIMOF</p>
            </div>
            <a href="<?= url('berita') ?>" class="btn btn-outline">Lihat Semua <i class="bi bi-arrow-right"></i></a>
        </div>
        
        <?php if ($featured): ?>
        <div class="news-layout">
            <article class="news-featured">
                <div class="news-image">
                    <?php if (!empty($featured['featured_image'])): ?>
                        <img src="<?= url('uploads/berita/' . $featured['featured_image']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>">
                    <?php else: ?>
                        <div class="news-placeholder"><i class="bi bi-newspaper"></i></div>
                    <?php endif; ?>
                    <span class="news-category"><?= ucfirst($featured['category']) ?></span>
                </div>
                <div class="news-body">
                    <span class="news-featured-badge"><i class="bi bi-star-fill"></i> FEATURED</span>
                    <div class="news-meta">
                        <span><i class="bi bi-calendar-event"></i> <?= date('d F Y', strtotime($featured['published_at'])) ?></span>
                        <span><i class="bi bi-person-circle"></i> <?= htmlspecialchars($featured['author_name'] ?? 'Tim BAUK') ?></span>
                        <span><i class="bi bi-eye"></i> <?= number_format($featured['views']) ?> views</span>
                    </div>
                    <h3 class="news-title"><a href="<?= url('berita/' . $featured['slug']) ?>"><?= htmlspecialchars($featured['title']) ?></a></h3>
                    <p class="news-excerpt"><?= htmlspecialchars(mb_substr($featured['excerpt'] ?? $featured['content'] ?? '', 0, 200)) ?>...</p>
                    <a href="<?= url('berita/' . $featured['slug']) ?>" class="news-read-more">Baca Selengkapnya <i class="bi bi-arrow-right"></i></a>
                </div>
            </article>
            
            <div class="news-side-list">
                <?php foreach ($otherNews as $item): ?>
                <a href="<?= url('berita/' . $item['slug']) ?>" class="news-side-item">
                    <div class="news-side-thumb">
                        <?php if (!empty($item['featured_image'])): ?>
                            <img src="<?= url('uploads/berita/' . $item['featured_image']) ?>" alt="">
                        <?php else: ?>
                            <i class="bi bi-newspaper"></i>
                        <?php endif; ?>
                    </div>
                    <div class="news-side-info">
                        <span class="news-category"><?= ucfirst($item['category']) ?></span>
                        <h4><?= htmlspecialchars($item['title']) ?></h4>
                        <div class="news-side-meta">
                            <span><i class="bi bi-calendar"></i> <?= date('d M Y', strtotime($item['published_at'])) ?></span>
                            <span><i class="bi bi-eye"></i> <?= number_format($item['views']) ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
                
                <?php if (empty($otherNews)): ?>
                    <div class="news-side-item" style="justify-content:center;">
                        <p style="color:var(--color-text-muted);text-align:center;width:100%;">Belum ada berita lainnya</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="news-grid">
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Belum ada berita dipublikasikan.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- =====================================================
     SISTEM INFORMASI TERINTEGRASI
     ===================================================== -->
<section class="systems-section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-diagram-3-fill"></i> Ekosistem Digital</span>
                <h2 class="section-title">Sistem Informasi Terintegrasi</h2>
                <p class="section-subtitle" style="margin:0 auto;">Akses berbagai sistem informasi kampus dalam satu ekosistem digital yang terpadu</p>
            </div>
        </div>
        
        <div class="systems-grid">
            <a href="#" class="system-card">
                <div class="system-icon"><i class="bi bi-mortarboard-fill"></i></div>
                <h4>SIAKAD</h4>
                <span>Sistem Informasi Akademik</span>
            </a>
            <a href="#" class="system-card">
                <div class="system-icon"><i class="bi bi-person-vcard-fill"></i></div>
                <h4>SIMPEG</h4>
                <span>Sistem Kepegawaian</span>
            </a>
            <a href="#" class="system-card">
                <div class="system-icon"><i class="bi bi-person-plus-fill"></i></div>
                <h4>PMB Online</h4>
                <span>Penerimaan Mahasiswa Baru</span>
            </a>
            <a href="#" class="system-card">
                <div class="system-icon"><i class="bi bi-book-half"></i></div>
                <h4>e-Learning</h4>
                <span>Pembelajaran Daring</span>
            </a>
            <a href="#" class="system-card">
                <div class="system-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
                <h4>Repository</h4>
                <span>Karya Ilmiah Digital</span>
            </a>
            <a href="#" class="system-card">
                <div class="system-icon"><i class="bi bi-envelope-paper-fill"></i></div>
                <h4>e-Surat</h4>
                <span>Persuratan Digital</span>
            </a>
            <a href="#" class="system-card">
                <div class="system-icon"><i class="bi bi-cash-stack"></i></div>
                <h4>SIPKA</h4>
                <span>Pembayaran Keuangan</span>
            </a>
            <a href="#" class="system-card">
                <div class="system-icon"><i class="bi bi-clipboard2-data-fill"></i></div>
                <h4>Tracer Study</h4>
                <span>Alumni & Karir</span>
            </a>
        </div>
    </div>
</section>

<!-- =====================================================
     DOWNLOAD CENTER
     ===================================================== -->
<section class="section download-section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-cloud-arrow-down-fill"></i> Pusat Unduhan</span>
                <h2 class="section-title">Download Center</h2>
                <p class="section-subtitle" style="margin:0 auto;">Formulir, template, dan dokumen resmi yang dapat diunduh langsung</p>
            </div>
        </div>
        
        <div class="download-grid">
            <a href="<?= url('regulasi') ?>" class="download-card">
                <div class="download-icon"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                <div class="download-info">
                    <h4>Formulir Resmi</h4>
                    <p>Formulir pengajuan cuti, mutasi, perjalanan dinas, dan administrasi pegawai lainnya.</p>
                    <span class="download-count"><i class="bi bi-download"></i> 24 template tersedia</span>
                </div>
            </a>
            <a href="<?= url('regulasi') ?>" class="download-card">
                <div class="download-icon"><i class="bi bi-file-earmark-ruled-fill"></i></div>
                <div class="download-info">
                    <h4>Template Surat</h4>
                    <p>Template surat resmi, nota dinas, undangan, dan korespondensi internal kampus.</p>
                    <span class="download-count"><i class="bi bi-download"></i> 18 template tersedia</span>
                </div>
            </a>
            <a href="<?= url('regulasi') ?>" class="download-card">
                <div class="download-icon"><i class="bi bi-list-check"></i></div>
                <div class="download-info">
                    <h4>SOP & Panduan</h4>
                    <p>Standar Operasional Prosedur dan panduan teknis untuk setiap layanan BAUK.</p>
                    <span class="download-count"><i class="bi bi-download"></i> 32 dokumen SOP</span>
                </div>
            </a>
            <a href="<?= url('regulasi') ?>" class="download-card">
                <div class="download-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                <div class="download-info">
                    <h4>Regulasi & Peraturan</h4>
                    <p>Peraturan rektor, keputusan, dan surat edaran yang berlaku di lingkungan UNIMOF.</p>
                    <span class="download-count"><i class="bi bi-download"></i> 45 regulasi aktif</span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- =====================================================
     FAQ SECTION
     ===================================================== -->
<section class="section faq-section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-question-circle-fill"></i> FAQ</span>
                <h2 class="section-title">Pertanyaan yang Sering Diajukan</h2>
                <p class="section-subtitle" style="margin:0 auto;">Temukan jawaban atas pertanyaan umum seputar layanan BAUK</p>
            </div>
        </div>
        
        <div class="faq-container">
            <div class="faq-item">
                <button class="faq-question">
                    <span>Bagaimana cara mengajukan cuti pegawai?</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Pengajuan cuti dapat dilakukan melalui modul e-Kepegawaian dengan mengisi formulir cuti online. Setelah diisi, sistem akan meneruskan ke atasan langsung untuk persetujuan berjenjang.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question">
                    <span>Kapan jadwal pembayaran gaji dan tunjangan?</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Gaji pokok dibayarkan setiap tanggal 1 bulan berjalan, sedangkan tunjangan kinerja dibayarkan setiap tanggal 15. Slip gaji dapat diunduh melalui portal kepegawaian.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question">
                    <span>Bagaimana prosedur pengadaan barang/jasa?</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Prosedur pengadaan mengikuti Peraturan Rektor. Unit kerja mengajukan permohonan melalui sistem dengan melampirkan spesifikasi dan RAB. Tim procurement akan memproses sesuai nominal.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question">
                    <span>Dimana saya bisa mendapatkan formulir resmi BAUK?</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Semua formulir resmi tersedia di bagian Download Center pada website ini. Formulir dapat diunduh dalam format Word atau PDF secara gratis.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     CTA SECTION (ULTIMATE)
     ===================================================== -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Butuh Bantuan atau Informasi?</h2>
            <p>Tim kami siap membantu Anda. Silakan hubungi kami melalui berbagai kanal berikut untuk konsultasi atau pertanyaan terkait layanan BAUK UNIMOF.</p>
            
            <div class="cta-contact-grid">
                <a href="tel:038221234" class="cta-contact-card">
                    <div class="cta-contact-icon"><i class="bi bi-telephone-fill"></i></div>
                    <h5>Telepon</h5>
                    <p>(0382) 21234</p>
                </a>
                <a href="mailto:bauk@unimof.ac.id" class="cta-contact-card">
                    <div class="cta-contact-icon"><i class="bi bi-envelope-fill"></i></div>
                    <h5>Email</h5>
                    <p>bauk@unimof.ac.id</p>
                </a>
                <a href="<?= url('kontak') ?>" class="cta-contact-card">
                    <div class="cta-contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <h5>Lokasi</h5>
                    <p>Maumere, NTT</p>
                </a>
            </div>
            
            <div class="cta-actions">
                <a href="<?= url('kontak') ?>" class="btn btn-white btn-lg"><i class="bi bi-chat-dots-fill"></i> Kirim Pesan</a>
                <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-outline-white btn-lg"><i class="bi bi-whatsapp"></i> WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     PARTNERS SECTION
     ===================================================== -->
<section class="partners-section">
    <div class="container">
        <div class="section-header text-center" style="margin-bottom:2.5rem;">
            <div>
                <span class="section-badge"><i class="bi bi-link-45deg"></i> Mitra Kami</span>
                <h2 class="section-title" style="font-size:1.5rem;">Jejaring & Kemitraan</h2>
            </div>
        </div>
        <div class="partners-grid">
            <div class="partner-item"><i class="bi bi-bank2"></i> Muhammadiyah</div>
            <div class="partner-item"><i class="bi bi-mortarboard-fill"></i> Kemendikbudristek</div>
            <div class="partner-item"><i class="bi bi-building-fill-gear"></i> LLDIKTI Wilayah VIII</div>
            <div class="partner-item"><i class="bi bi-shield-fill-check"></i> BAN-PT</div>
            <div class="partner-item"><i class="bi bi-globe-asia-australia"></i> APTIK</div>
            <div class="partner-item"><i class="bi bi-award-fill"></i> ISO Indonesia</div>
        </div>
    </div>
</section>

<!-- =====================================================
     FAQ INTERACTION SCRIPT
     ===================================================== -->
<script>
(function() {
    'use strict';
    
    // FAQ Accordion
    document.querySelectorAll('.faq-question').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const item = this.parentElement;
            const isActive = item.classList.contains('active');
            document.querySelectorAll('.faq-item').forEach(function(el) { el.classList.remove('active'); });
            if (!isActive) item.classList.add('active');
        });
    });
})();
</script>