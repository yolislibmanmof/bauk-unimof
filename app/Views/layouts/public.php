<?php 
$cp = current_path();
$db = \App\Helpers\Database::getInstance();

// Ambil data untuk footer dinamis
try {
    $latestNews = $db->fetchAll("SELECT slug, title, published_at FROM berita WHERE is_published = 1 AND deleted_at IS NULL ORDER BY published_at DESC LIMIT 3");
    $activeRegulations = $db->fetchAll("SELECT slug, title FROM dokumen_regulasi WHERE status = 'active' ORDER BY effective_date DESC LIMIT 3");
} catch (\Throwable $e) {
    $latestNews = [];
    $activeRegulations = [];
}

// Detect halaman aktif untuk breadcrumb
$breadcrumbMap = [
    '/tentang'    => 'Tentang Kami',
    '/berita'     => 'Berita & Informasi',
    '/regulasi'   => 'Dokumen Regulasi',
    '/kontak'     => 'Hubungi Kami',
];
$currentPageName = $breadcrumbMap[$cp] ?? null;

// Untuk sub-halaman layanan
if (str_starts_with($cp, '/layanan/')) {
    $section = basename($cp);
    $names = [
        'kepegawaian' => 'Bagian Kepegawaian',
        'keuangan'    => 'Bagian Keuangan',
        'aset'        => 'Aset & Rumah Tangga',
        'k3l'         => 'Tim K3L',
    ];
    $currentPageName = $names[$section] ?? 'Layanan';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Biro Administrasi Umum dan Keuangan - Universitas Muhammadiyah Maumere. Layanan transparan, efisien, dan terintegrasi untuk civitas akademika UNIMOF.">
    <meta name="keywords" content="BAUK UNIMOF, Universitas Muhammadiyah Maumere, Keuangan Kampus, Aset Kampus, Kepegawaian, Maumere">
    <meta name="author" content="BAUK UNIMOF">
    <meta property="og:title" content="<?= htmlspecialchars($title ?? 'BAUK UNIMOF') ?>">
    <meta property="og:description" content="Biro Administrasi Umum dan Keuangan - Universitas Muhammadiyah Maumere">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta name="theme-color" content="#1a5276">
    <title><?= htmlspecialchars($title ?? 'BAUK UNIMOF') ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('css/public-style.css') ?>">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' fill='%231a5276'/%3E%3Ctext x='50' y='68' font-family='Arial' font-size='60' font-weight='bold' text-anchor='middle' fill='white'%3EB%3C/text%3E%3C/svg%3E">
    
    <!-- ===== ULTIMATE ENHANCEMENTS STYLES ===== -->
    <style>
    /* ===== PRELOADER ===== */
    .ult-preloader{position:fixed;inset:0;background:#fff;z-index:99999;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:1rem;transition:opacity .5s,visibility .5s;}
    .ult-preloader.hidden{opacity:0;visibility:hidden;}
    .ult-preloader-logo{width:80px;height:80px;background:linear-gradient(135deg,#1a5276,#2980b9);border-radius:20px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:2.5rem;font-weight:800;font-family:'Playfair Display',serif;animation:ult-logoPulse 1.5s ease infinite;}
    .ult-preloader-bar{width:200px;height:3px;background:#e2e8f0;border-radius:10px;overflow:hidden;}
    .ult-preloader-bar::after{content:'';display:block;width:40%;height:100%;background:linear-gradient(90deg,#1a5276,#e67e22);animation:ult_barLoad 1.2s ease infinite;}
    @keyframes ult_logoPulse{0%,100%{transform:scale(1);box-shadow:0 0 0 0 rgba(26,82,118,.4);}50%{transform:scale(1.05);box-shadow:0 0 0 20px rgba(26,82,118,0);}}
    @keyframes ult_barLoad{0%{margin-left:-40%;}100%{margin-left:100%;}}
    
    /* ===== SCROLL PROGRESS ===== */
    .ult-scroll-progress{position:fixed;top:0;left:0;width:0;height:3px;background:linear-gradient(90deg,#1a5276,#e67e22,#27ae60);z-index:10000;transition:width .1s;}
    
    /* ===== TOP BAR ENHANCED ===== */
    .ult-top-info-bar{background:linear-gradient(90deg,#0d2f4a,#1a5276);color:rgba(255,255,255,.95);padding:.5rem 0;font-size:.8rem;border-bottom:1px solid rgba(255,255,255,.1);}
    .ult-top-info-grid{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;}
    .ult-top-info-left{display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;}
    .ult-top-info-left span{display:flex;align-items:center;gap:.375rem;}
    .ult-top-info-left i{color:#f39c12;font-size:.85rem;}
    .ult-top-info-right{display:flex;align-items:center;gap:1rem;}
    .ult-lang-switcher{display:flex;align-items:center;gap:.25rem;background:rgba(255,255,255,.1);padding:.25rem .625rem;border-radius:20px;}
    .ult-lang-switcher button{background:none;border:none;color:rgba(255,255,255,.8);cursor:pointer;font-size:.75rem;padding:.125rem .375rem;border-radius:10px;transition:all .2s;}
    .ult-lang-switcher button.active,.ult-lang-switcher button:hover{background:#e67e22;color:#fff;}
    .ult-accessibility-btn{background:rgba(255,255,255,.1);border:none;color:#fff;padding:.25rem .5rem;border-radius:15px;cursor:pointer;font-size:.8rem;display:flex;align-items:center;gap:.25rem;transition:all .2s;}
    .ult-accessibility-btn:hover{background:#e67e22;}
    .ult-live-badge{display:inline-flex;align-items:center;gap:.375rem;background:rgba(39,174,96,.2);color:#2ecc71;padding:.25rem .625rem;border-radius:20px;font-size:.75rem;font-weight:600;}
    .ult-live-dot{width:6px;height:6px;background:#2ecc71;border-radius:50%;animation:ult_blink 1.5s infinite;}
    @keyframes ult_blink{0%,100%{opacity:1;}50%{opacity:.3;}}
    
    /* ===== HEADER ENHANCED ===== */
    .ult-header-wrapper{position:sticky;top:0;z-index:999;transition:all .3s;}
    .ult-header-wrapper.scrolled{box-shadow:0 4px 20px rgba(0,0,0,.08);}
    
    .ult-header-top{background:#fff;border-bottom:1px solid var(--color-border-light);padding:.5rem 0;}
    .ult-header-top-grid{display:flex;justify-content:space-between;align-items:center;gap:1rem;}
    .ult-contact-strip{display:flex;gap:1.25rem;font-size:.85rem;color:var(--color-text-secondary);}
    .ult-contact-strip a{display:flex;align-items:center;gap:.375rem;color:var(--color-text-secondary);}
    .ult-contact-strip a:hover{color:var(--color-primary);}
    .ult-contact-strip i{color:var(--color-primary);}
    .ult-header-actions{display:flex;align-items:center;gap:.5rem;}
    .ult-search-toggle{background:var(--color-primary-50);color:var(--color-primary);border:none;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;}
    .ult-search-toggle:hover{background:var(--color-primary);color:#fff;}
    .ult-cta-header{background:linear-gradient(135deg,var(--color-accent),#f39c12);color:#fff!important;padding:.5rem 1.25rem;border-radius:25px;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:.375rem;transition:all .2s;box-shadow:0 4px 12px rgba(230,126,34,.25);}
    .ult-cta-header:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(230,126,34,.4);color:#fff!important;}
    
    /* ===== SEARCH OVERLAY ===== */
    .ult-search-overlay{position:fixed;inset:0;background:rgba(13,47,74,.95);backdrop-filter:blur(10px);z-index:10001;display:flex;align-items:flex-start;justify-content:center;padding-top:15vh;opacity:0;visibility:hidden;transition:all .3s;}
    .ult-search-overlay.active{opacity:1;visibility:visible;}
    .ult-search-box{width:90%;max-width:700px;transform:translateY(-20px);transition:transform .3s;}
    .ult-search-overlay.active .ult-search-box{transform:translateY(0);}
    .ult-search-input-wrapper{position:relative;}
    .ult-search-input-wrapper input{width:100%;padding:1.25rem 1.5rem 1.25rem 3.5rem;font-size:1.25rem;border:none;border-radius:15px;background:#fff;color:var(--color-text);}
    .ult-search-input-wrapper i{position:absolute;left:1.25rem;top:50%;transform:translateY(-50%);font-size:1.25rem;color:var(--color-primary);}
    .ult-search-close{position:absolute;top:2rem;right:2rem;background:rgba(255,255,255,.15);color:#fff;border:none;width:50px;height:50px;border-radius:50%;font-size:1.5rem;cursor:pointer;transition:all .2s;}
    .ult-search-close:hover{background:var(--color-danger);transform:rotate(90deg);}
    .ult-search-suggestions{margin-top:1rem;background:#fff;border-radius:12px;padding:1rem;max-height:50vh;overflow-y:auto;}
    .ult-search-suggestions h5{font-size:.8rem;text-transform:uppercase;color:var(--color-text-muted);margin-bottom:.75rem;letter-spacing:1px;}
    .ult-search-suggestion-item{display:flex;align-items:center;gap:.75rem;padding:.625rem;border-radius:8px;color:var(--color-text);transition:background .2s;}
    .ult-search-suggestion-item:hover{background:var(--color-primary-50);color:var(--color-primary);}
    .ult-search-suggestion-item i{color:var(--color-primary);font-size:1.1rem;}
    
    /* ===== BREADCRUMB ===== */
    .ult-breadcrumb{background:linear-gradient(135deg,#f8f9fa,#fff);padding:.875rem 0;border-bottom:1px solid var(--color-border-light);}
    .ult-breadcrumb-list{display:flex;align-items:center;gap:.5rem;font-size:.85rem;color:var(--color-text-secondary);flex-wrap:wrap;}
    .ult-breadcrumb-list a{color:var(--color-primary);transition:color .2s;}
    .ult-breadcrumb-list a:hover{color:var(--color-accent);}
    .ult-breadcrumb-list i{font-size:.7rem;color:var(--color-text-muted);}
    .ult-breadcrumb-current{color:var(--color-text);font-weight:600;}
    
    /* ===== FLASH MESSAGE ENHANCED ===== */
    .ult-flash{position:fixed;top:100px;right:20px;z-index:9998;max-width:420px;background:#fff;border-radius:12px;box-shadow:0 15px 40px rgba(0,0,0,.15);padding:1rem 1.25rem;display:flex;align-items:flex-start;gap:.75rem;animation:ult_slideInRight .4s ease;border-left:4px solid;}
    .ult-flash.success{border-color:#10b981;}
    .ult-flash.error{border-color:#ef4444;}
    .ult-flash-icon{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
    .ult-flash.success .ult-flash-icon{background:#d1fae5;color:#10b981;}
    .ult-flash.error .ult-flash-icon{background:#fee2e2;color:#ef4444;}
    .ult-flash-content{flex:1;font-size:.9rem;line-height:1.5;}
    .ult-flash-close{background:none;border:none;color:var(--color-text-muted);cursor:pointer;font-size:1.25rem;padding:0;line-height:1;}
    .ult-flash-close:hover{color:var(--color-danger);}
    @keyframes ult_slideInRight{from{transform:translateX(120%);opacity:0;}to{transform:translateX(0);opacity:1;}}
    
    /* ===== NEWSLETTER SECTION (before footer) ===== */
    .ult-newsletter{background:linear-gradient(135deg,#1a5276 0%,#0d2f4a 100%);color:#fff;padding:3.5rem 0;position:relative;overflow:hidden;}
    .ult-newsletter::before{content:'';position:absolute;top:-50%;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(230,126,34,.15) 0%,transparent 70%);border-radius:50%;}
    .ult-newsletter::after{content:'';position:absolute;bottom:-50%;left:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(39,174,96,.15) 0%,transparent 70%);border-radius:50%;}
    .ult-newsletter-content{position:relative;z-index:1;display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center;}
    .ult-newsletter-text h3{font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:.75rem;}
    .ult-newsletter-text p{color:rgba(255,255,255,.8);line-height:1.7;}
    .ult-newsletter-features{display:flex;gap:1.5rem;margin-top:1.25rem;flex-wrap:wrap;}
    .ult-newsletter-feature{display:flex;align-items:center;gap:.375rem;font-size:.85rem;color:rgba(255,255,255,.9);}
    .ult-newsletter-feature i{color:#f39c12;}
    .ult-newsletter-form{background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);padding:2rem;border-radius:20px;}
    .ult-newsletter-form h4{margin-bottom:1rem;font-size:1.1rem;}
    .ult-newsletter-input{display:flex;gap:.5rem;margin-bottom:1rem;}
    .ult-newsletter-input input{flex:1;padding:.875rem 1.25rem;border:none;border-radius:10px;background:#fff;font-size:.95rem;}
    .ult-newsletter-input input:focus{outline:2px solid #e67e22;}
    .ult-newsletter-input button{background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;border:none;padding:.875rem 1.5rem;border-radius:10px;font-weight:600;cursor:pointer;transition:all .2s;white-space:nowrap;}
    .ult-newsletter-input button:hover{transform:translateY(-2px);box-shadow:0 6px 15px rgba(230,126,34,.4);}
    .ult-newsletter-consent{display:flex;align-items:flex-start;gap:.5rem;font-size:.8rem;color:rgba(255,255,255,.8);line-height:1.5;}
    .ult-newsletter-consent input{margin-top:3px;accent-color:#e67e22;}
    
    /* ===== FOOTER ENHANCED ===== */
    .ult-footer-map{background:#f8f9fa;padding:0;border-bottom:1px solid var(--color-border-light);}
    .ult-footer-map-grid{display:grid;grid-template-columns:1fr 1fr;gap:0;}
    .ult-footer-map-info{padding:2.5rem;background:var(--color-primary-dark);color:#fff;}
    .ult-footer-map-info h4{font-size:1.25rem;margin-bottom:1rem;color:#fff;}
    .ult-footer-map-info p{color:rgba(255,255,255,.85);line-height:1.8;margin-bottom:1rem;}
    .ult-footer-map-cta{display:flex;gap:.75rem;flex-wrap:wrap;}
    .ult-footer-map-cta a{background:rgba(255,255,255,.1);color:#fff!important;padding:.5rem 1rem;border-radius:25px;font-size:.85rem;display:flex;align-items:center;gap:.375rem;transition:all .2s;border:1px solid rgba(255,255,255,.2);}
    .ult-footer-map-cta a:hover{background:var(--color-accent);border-color:var(--color-accent);}
    .ult-footer-map-embed{min-height:280px;background:#e2e8f0;position:relative;}
    .ult-footer-map-embed iframe{width:100%;height:100%;border:0;}
    .ult-footer-map-placeholder{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:var(--color-text-secondary);flex-direction:column;gap:.5rem;}
    
    .ult-footer-badges{background:#fff;padding:1.5rem 0;border-bottom:1px solid var(--color-border-light);}
    .ult-badges-grid{display:flex;justify-content:space-around;align-items:center;flex-wrap:wrap;gap:1.5rem;}
    .ult-badge-item{display:flex;align-items:center;gap:.625rem;color:var(--color-text-secondary);font-size:.85rem;}
    .ult-badge-item i{font-size:1.75rem;color:var(--color-primary);}
    .ult-badge-item strong{display:block;color:var(--color-text);font-size:.9rem;}
    
    .ult-footer-stats{background:linear-gradient(135deg,#f8f9fa,#fff);padding:2rem 0;border-top:1px solid var(--color-border-light);}
    .ult-footer-stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;text-align:center;}
    .ult-footer-stat{padding:1rem;}
    .ult-footer-stat-number{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--color-primary);line-height:1;}
    .ult-footer-stat-label{font-size:.85rem;color:var(--color-text-secondary);margin-top:.25rem;}
    
    /* ===== FLOATING BUTTONS ===== */
    .ult-floating-buttons{position:fixed;right:20px;bottom:20px;z-index:997;display:flex;flex-direction:column;gap:.75rem;}
    .ult-float-btn{width:56px;height:56px;border-radius:50%;border:none;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;cursor:pointer;box-shadow:0 8px 20px rgba(0,0,0,.2);transition:all .3s;position:relative;}
    .ult-float-btn:hover{transform:translateY(-3px) scale(1.05);}
    .ult-float-whatsapp{background:linear-gradient(135deg,#25D366,#128C7E);}
    .ult-float-accessibility{background:linear-gradient(135deg,#8e44ad,#9b59b6);}
    .ult-float-btn .ult-float-tooltip{position:absolute;right:calc(100% + 10px);background:#1e293b;color:#fff;padding:.375rem .75rem;border-radius:6px;font-size:.75rem;white-space:nowrap;opacity:0;visibility:hidden;transition:all .2s;pointer-events:none;}
    .ult-float-btn:hover .ult-float-tooltip{opacity:1;visibility:visible;}
    .ult-float-whatsapp::after{content:'';position:absolute;inset:0;border-radius:50%;border:3px solid #25D366;animation:ult_pulseRing 2s infinite;}
    @keyframes ult_pulseRing{0%{transform:scale(1);opacity:1;}100%{transform:scale(1.4);opacity:0;}}
    
    /* ===== ACCESSIBILITY PANEL ===== */
    .ult-a11y-panel{position:fixed;right:-350px;top:0;width:320px;height:100vh;background:#fff;z-index:9999;box-shadow:-5px 0 25px rgba(0,0,0,.15);transition:right .3s;padding:1.5rem;overflow-y:auto;}
    .ult-a11y-panel.active{right:0;}
    .ult-a11y-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--color-border);}
    .ult-a11y-header h3{font-size:1.1rem;display:flex;align-items:center;gap:.5rem;}
    .ult-a11y-close{background:var(--color-bg);border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;}
    .ult-a11y-section{margin-bottom:1.5rem;}
    .ult-a11y-section h4{font-size:.85rem;text-transform:uppercase;color:var(--color-text-secondary);margin-bottom:.75rem;letter-spacing:1px;}
    .ult-a11y-option{display:flex;justify-content:space-between;align-items:center;padding:.75rem;background:var(--color-bg);border-radius:8px;margin-bottom:.5rem;}
    .ult-a11y-option button{background:var(--color-surface);border:1px solid var(--color-border);width:32px;height:32px;border-radius:6px;cursor:pointer;transition:all .2s;}
    .ult-a11y-option button:hover{background:var(--color-primary);color:#fff;}
    .ult-a11y-reset{width:100%;padding:.75rem;background:var(--color-danger);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;}
    
    body.ult-a11y-large{font-size:18px!important;}
    body.ult-a11y-xl{font-size:20px!important;}
    body.ult-a11y-contrast{filter:contrast(1.3);}
    body.ult-a11y-grayscale{filter:grayscale(1);}
    body.ult-a11y-dyslexia{font-family:'Comic Sans MS',cursive!important;}
    
    /* ===== COOKIE CONSENT ===== */
    .ult-cookie{position:fixed;bottom:20px;left:20px;right:20px;max-width:500px;background:#fff;border-radius:15px;box-shadow:0 15px 40px rgba(0,0,0,.15);padding:1.5rem;z-index:996;display:flex;flex-direction:column;gap:1rem;transform:translateY(120%);transition:transform .4s;}
    .ult-cookie.show{transform:translateY(0);}
    .ult-cookie-header{display:flex;align-items:flex-start;gap:.75rem;}
    .ult-cookie-icon{width:44px;height:44px;background:linear-gradient(135deg,#f39c12,#e67e22);color:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;}
    .ult-cookie h4{font-size:1rem;margin-bottom:.25rem;}
    .ult-cookie p{font-size:.85rem;color:var(--color-text-secondary);line-height:1.6;margin:0;}
    .ult-cookie-actions{display:flex;gap:.5rem;flex-wrap:wrap;}
    .ult-cookie-actions button{flex:1;padding:.625rem 1rem;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:.85rem;transition:all .2s;}
    .ult-cookie-accept{background:var(--color-primary);color:#fff;}
    .ult-cookie-accept:hover{background:var(--color-primary-dark);}
    .ult-cookie-settings{background:var(--color-bg);color:var(--color-text);border:1px solid var(--color-border);}
    
    /* ===== MOBILE BOTTOM NAV ===== */
    .ult-mobile-nav{display:none;position:fixed;bottom:0;left:0;right:0;background:#fff;z-index:995;border-top:1px solid var(--color-border-light);padding:.5rem 0;box-shadow:0 -4px 15px rgba(0,0,0,.08);}
    .ult-mobile-nav-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:0;}
    .ult-mobile-nav-item{display:flex;flex-direction:column;align-items:center;gap:.125rem;padding:.5rem;color:var(--color-text-secondary);font-size:.7rem;text-decoration:none;transition:all .2s;}
    .ult-mobile-nav-item.active{color:var(--color-primary);}
    .ult-mobile-nav-item i{font-size:1.2rem;}
    .ult-mobile-nav-item:hover{color:var(--color-primary);}
    
    /* ===== BACK TO TOP ENHANCED ===== */
    .ult-back-top{position:fixed;bottom:90px;right:20px;z-index:997;}
    
    /* ===== RESPONSIVE ===== */
    @media (max-width:992px){
        .ult-newsletter-content{grid-template-columns:1fr;gap:2rem;}
        .ult-footer-map-grid{grid-template-columns:1fr;}
        .ult-footer-map-info{padding:1.5rem;}
        .ult-footer-stats-grid{grid-template-columns:repeat(2,1fr);}
        .ult-header-top{display:none;}
    }
    @media (max-width:768px){
        .ult-top-info-bar{font-size:.7rem;}
        .ult-top-info-left{gap:.75rem;}
        .ult-top-info-right{display:none;}
        .ult-floating-buttons{bottom:80px;}
        .ult-mobile-nav{display:block;}
        .ult-back-top{bottom:90px;right:15px;}
        .ult-float-btn{width:48px;height:48px;font-size:1.25rem;}
        body{padding-bottom:70px;}
        .ult-cookie{left:10px;right:10px;}
        .ult-badges-grid{gap:.75rem;}
        .ult-badge-item{font-size:.75rem;}
        .ult-badge-item i{font-size:1.3rem;}
    }
    @media (max-width:480px){
        .ult-footer-stats-grid{grid-template-columns:1fr 1fr;}
        .ult-footer-stat-number{font-size:1.5rem;}
        .ult-newsletter-input{flex-direction:column;}
    }
    </style>
</head>
<body>

    <!-- ===== PRELOADER ===== -->
    <div class="ult-preloader" id="ultPreloader">
        <div class="ult-preloader-logo">B</div>
        <div class="ult-preloader-bar"></div>
    </div>

    <!-- ===== SCROLL PROGRESS ===== -->
    <div class="ult-scroll-progress" id="ultScrollProgress"></div>

    <!-- ===== SEARCH OVERLAY ===== -->
    <div class="ult-search-overlay" id="ultSearchOverlay">
        <button class="ult-search-close" id="ultSearchClose"><i class="bi bi-x-lg"></i></button>
        <div class="ult-search-box">
            <div class="ult-search-input-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" id="ultSearchInput" placeholder="Cari layanan, berita, regulasi..." autocomplete="off">
            </div>
            <div class="ult-search-suggestions">
                <h5>Akses Cepat</h5>
                <a href="<?= url('layanan/kepegawaian') ?>" class="ult-search-suggestion-item"><i class="bi bi-person-badge-fill"></i><span>Layanan Kepegawaian</span></a>
                <a href="<?= url('layanan/keuangan') ?>" class="ult-search-suggestion-item"><i class="bi bi-wallet2"></i><span>Layanan Keuangan</span></a>
                <a href="<?= url('layanan/aset') ?>" class="ult-search-suggestion-item"><i class="bi bi-box-seam-fill"></i><span>Aset & Rumah Tangga</span></a>
                <a href="<?= url('regulasi') ?>" class="ult-search-suggestion-item"><i class="bi bi-file-earmark-text-fill"></i><span>Dokumen Regulasi</span></a>
                <a href="<?= url('berita') ?>" class="ult-search-suggestion-item"><i class="bi bi-newspaper"></i><span>Berita & Informasi</span></a>
                <a href="<?= url('kontak') ?>" class="ult-search-suggestion-item"><i class="bi bi-envelope-fill"></i><span>Hubungi Kami</span></a>
            </div>
        </div>
    </div>

    <!-- ===== ACCESSIBILITY PANEL ===== -->
    <div class="ult-a11y-panel" id="ultA11yPanel">
        <div class="ult-a11y-header">
            <h3><i class="bi bi-universal-access"></i> Aksesibilitas</h3>
            <button class="ult-a11y-close" id="ultA11yClose"><i class="bi bi-x"></i></button>
        </div>
        
        <div class="ult-a11y-section">
            <h4>Ukuran Teks</h4>
            <div class="ult-a11y-option">
                <span>Teks Normal</span>
                <button onclick="ultA11yReset()"><i class="bi bi-arrow-counterclockwise"></i></button>
            </div>
            <div class="ult-a11y-option">
                <span>Teks Lebih Besar</span>
                <button onclick="ultA11ySet('large')"><i class="bi bi-type"></i></button>
            </div>
            <div class="ult-a11y-option">
                <span>Teks Sangat Besar</span>
                <button onclick="ultA11ySet('xl')"><i class="bi bi-type-h1"></i></button>
            </div>
        </div>
        
        <div class="ult-a11y-section">
            <h4>Tampilan</h4>
            <div class="ult-a11y-option">
                <span>Kontras Tinggi</span>
                <button onclick="ultA11yToggle('contrast')"><i class="bi bi-circle-half"></i></button>
            </div>
            <div class="ult-a11y-option">
                <span>Skala Abu-abu</span>
                <button onclick="ultA11yToggle('grayscale')"><i class="bi bi-droplet-half"></i></button>
            </div>
            <div class="ult-a11y-option">
                <span>Font Ramah Disleksia</span>
                <button onclick="ultA11yToggle('dyslexia')"><i class="bi bi-file-font"></i></button>
            </div>
        </div>
        
        <button class="ult-a11y-reset" onclick="ultA11yReset()">
            <i class="bi bi-arrow-counterclockwise"></i> Reset Semua
        </button>
    </div>

    <!-- ===== TOP INFO BAR ===== -->
    <div class="ult-top-info-bar">
        <div class="container">
            <div class="ult-top-info-grid">
                <div class="ult-top-info-left">
                    <span><i class="bi bi-geo-alt-fill"></i> Jl. Ahmad Yani No. 17, Maumere</span>
                    <span><i class="bi bi-clock-fill"></i> Senin - Jumat: 08:00 - 16:00 WITA</span>
                    <span class="ult-live-badge"><span class="ult-live-dot"></span> Sistem Online</span>
                </div>
                <div class="ult-top-info-right">
                    <div class="ult-lang-switcher">
                        <i class="bi bi-translate"></i>
                        <button class="active">ID</button>
                        <button>EN</button>
                    </div>
                    <button class="ult-accessibility-btn" id="ultA11yBtn" title="Opsi Aksesibilitas">
                        <i class="bi bi-universal-access"></i> Aksesibilitas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== HEADER TOP (Contact Strip) ===== -->
    <div class="ult-header-top">
        <div class="container">
            <div class="ult-header-top-grid">
                <div class="ult-contact-strip">
                    <a href="tel:038221234"><i class="bi bi-telephone-fill"></i> (0382) 21234</a>
                    <a href="mailto:bauk@unimof.ac.id"><i class="bi bi-envelope-fill"></i> bauk@unimof.ac.id</a>
                    <a href="#"><i class="bi bi-whatsapp"></i> +62 812-3456-7890</a>
                </div>
                <div class="ult-header-actions">
                    <button class="ult-search-toggle" id="ultSearchToggle" title="Cari"><i class="bi bi-search"></i></button>
                    <a href="<?= url('kontak') ?>" class="ult-cta-header">
                        <i class="bi bi-headset"></i> Butuh Bantuan?
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MAIN HEADER (Navbar) ===== -->
    <header class="main-header ult-header-wrapper" id="mainHeader">
        <div class="container">
            <nav class="navbar">
                <div class="navbar-brand">
                    <a href="<?= url() ?>">
                        <div class="logo-img brand-badge">B</div>
                        <div class="brand-text">
                            <h1>BAUK</h1>
                            <span>Universitas Muhammadiyah Maumere</span>
                        </div>
                    </a>
                </div>
                <button class="navbar-toggler" id="navbarToggler" aria-label="Menu"><span class="toggler-icon"></span></button>
                <ul class="navbar-menu" id="navbarMenu">
                    <li><a href="<?= url() ?>" class="nav-link <?= $cp === '/' ? 'active' : '' ?>">Beranda</a></li>
                    <li><a href="<?= url('tentang') ?>" class="nav-link <?= $cp === '/tentang' ? 'active' : '' ?>">Tentang</a></li>
                    <li class="nav-dropdown">
                        <a href="#" class="nav-link <?= str_starts_with($cp, '/layanan') ? 'active' : '' ?>">Layanan <i class="bi bi-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= url('layanan/kepegawaian') ?>"><i class="bi bi-person-badge"></i> Kepegawaian</a></li>
                            <li><a href="<?= url('layanan/keuangan') ?>"><i class="bi bi-wallet2"></i> Keuangan</a></li>
                            <li><a href="<?= url('layanan/aset') ?>"><i class="bi bi-house-gear"></i> Aset & Rumah Tangga</a></li>
                            <li><a href="<?= url('layanan/k3l') ?>"><i class="bi bi-shield-check"></i> K3L</a></li>
                        </ul>
                    </li>
                    <li><a href="<?= url('berita') ?>" class="nav-link <?= str_starts_with($cp, '/berita') ? 'active' : '' ?>">Berita</a></li>
                    <li><a href="<?= url('regulasi') ?>" class="nav-link <?= $cp === '/regulasi' ? 'active' : '' ?>">Regulasi</a></li>
                    <li><a href="<?= url('kontak') ?>" class="nav-link <?= $cp === '/kontak' ? 'active' : '' ?>">Kontak</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- ===== BREADCRUMB (untuk halaman dalam) ===== -->
    <?php if ($currentPageName && $cp !== '/'): ?>
    <div class="ult-breadcrumb">
        <div class="container">
            <div class="ult-breadcrumb-list">
                <a href="<?= url() ?>"><i class="bi bi-house-fill"></i> Beranda</a>
                <i class="bi bi-chevron-right"></i>
                <span class="ult-breadcrumb-current"><?= htmlspecialchars($currentPageName) ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== FLASH MESSAGES (ULTIMATE) ===== -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="ult-flash success" id="ultFlashSuccess">
            <div class="ult-flash-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="ult-flash-content"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
            <button class="ult-flash-close" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
        </div>
        <?php unset($_SESSION['flash_success']); endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="ult-flash error" id="ultFlashError">
            <div class="ult-flash-icon"><i class="bi bi-exclamation-circle-fill"></i></div>
            <div class="ult-flash-content">
                <?= is_array($_SESSION['flash_error']) ? implode('<br>', array_map('htmlspecialchars', $_SESSION['flash_error'])) : htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <button class="ult-flash-close" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
        </div>
        <?php unset($_SESSION['flash_error']); endif; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content"><?= $content ?? '' ?></main>

    <!-- ===== NEWSLETTER SECTION ===== -->
    <section class="ult-newsletter">
        <div class="container">
            <div class="ult-newsletter-content">
                <div class="ult-newsletter-text">
                    <h3>Tetap Terhubung dengan BAUK UNIMOF</h3>
                    <p>Dapatkan update terbaru seputar informasi keuangan, pengumuman penting, dan kebijakan baru langsung di email Anda.</p>
                    <div class="ult-newsletter-features">
                        <span class="ult-newsletter-feature"><i class="bi bi-check-circle-fill"></i> Gratis</span>
                        <span class="ult-newsletter-feature"><i class="bi bi-check-circle-fill"></i> Tanpa spam</span>
                        <span class="ult-newsletter-feature"><i class="bi bi-check-circle-fill"></i> Berhenti kapan saja</span>
                    </div>
                </div>
                <form class="ult-newsletter-form" onsubmit="ultSubscribeNewsletter(event)">
                    <h4><i class="bi bi-envelope-paper-fill"></i> Berlangganan Newsletter</h4>
                    <div class="ult-newsletter-input">
                        <input type="email" placeholder="nama@email.com" required>
                        <button type="submit"><i class="bi bi-send-fill"></i> Berlangganan</button>
                    </div>
                    <label class="ult-newsletter-consent">
                        <input type="checkbox" required>
                        <span>Dengan berlangganan, saya menyetujui kebijakan privasi BAUK UNIMOF dan bersedia menerima informasi melalui email.</span>
                    </label>
                </form>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER MAP SECTION ===== -->
    <div class="ult-footer-map">
        <div class="ult-footer-map-grid">
            <div class="ult-footer-map-info">
                <h4><i class="bi bi-geo-alt-fill"></i> Kunjungi Kantor Kami</h4>
                <p><strong>Biro Administrasi Umum dan Keuangan</strong><br>
                Universitas Muhammadiyah Maumere<br>
                Jl. Ahmad Yani No. 17, Maumere<br>
                Kabupaten Sikka, Nusa Tenggara Timur 86111</p>
                <p><i class="bi bi-telephone-fill"></i> (0382) 21234<br>
                <i class="bi bi-envelope-fill"></i> bauk@unimof.ac.id</p>
                <div class="ult-footer-map-cta">
                    <a href="https://maps.google.com/?q=Universitas+Muhammadiyah+Maumere" target="_blank"><i class="bi bi-map-fill"></i> Buka di Maps</a>
                    <a href="https://wa.me/6281234567890" target="_blank"><i class="bi bi-whatsapp"></i> Chat WhatsApp</a>
                    <a href="tel:038221234"><i class="bi bi-telephone-fill"></i> Telepon</a>
                </div>
            </div>
            <div class="ult-footer-map-embed">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3950.123!2d122.2!3d-8.6!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOMKwMzYnMDAuMCJTIDEyMsKwMTInMDAuMCJF!5e0!3m2!1sid!2sid!4v1234567890" allowfullscreen="" loading="lazy"></iframe>
                <div class="ult-footer-map-placeholder" style="display:none;">
                    <i class="bi bi-map" style="font-size:3rem;"></i>
                    <span>Peta Lokasi BAUK UNIMOF</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== FOOTER BADGES ===== -->
    <div class="ult-footer-badges">
        <div class="container">
            <div class="ult-badges-grid">
                <div class="ult-badge-item"><i class="bi bi-award-fill"></i><div><strong>BAN-PT</strong><span>Terakreditasi</span></div></div>
                <div class="ult-badge-item"><i class="bi bi-shield-fill-check"></i><div><strong>ISO 9001</strong><span>Manajemen Mutu</span></div></div>
                <div class="ult-badge-item"><i class="bi bi-patch-check-fill"></i><div><strong>Kemendikbud</strong><span>Terverifikasi</span></div></div>
                <div class="ult-badge-item"><i class="bi bi-globe2"></i><div><strong>Muhammadiyah</strong><span>Jaringan Global</span></div></div>
            </div>
        </div>
    </div>

    <!-- ===== MAIN FOOTER ===== -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-brand">
                        <div class="footer-logo brand-badge">B</div>
                        <h3>BAUK UNIMOF</h3>
                        <p>Biro Administrasi Umum dan Keuangan<br>Universitas Muhammadiyah Maumere</p>
                    </div>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                        <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Layanan Kami</h4>
                    <ul>
                        <li><a href="<?= url('layanan/kepegawaian') ?>"><i class="bi bi-chevron-right"></i> Kepegawaian</a></li>
                        <li><a href="<?= url('layanan/keuangan') ?>"><i class="bi bi-chevron-right"></i> Keuangan</a></li>
                        <li><a href="<?= url('layanan/aset') ?>"><i class="bi bi-chevron-right"></i> Aset & Rumah Tangga</a></li>
                        <li><a href="<?= url('layanan/k3l') ?>"><i class="bi bi-chevron-right"></i> K3L</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Informasi Terbaru</h4>
                    <ul>
                        <?php if (!empty($latestNews)): foreach ($latestNews as $n): ?>
                            <li><a href="<?= url('berita/' . $n['slug']) ?>"><i class="bi bi-newspaper"></i> <?= htmlspecialchars(mb_substr($n['title'], 0, 40)) ?>...</a></li>
                        <?php endforeach; else: ?>
                            <li><a href="<?= url('berita') ?>"><i class="bi bi-chevron-right"></i> Lihat Semua Berita</a></li>
                        <?php endif; ?>
                        <li><a href="<?= url('regulasi') ?>"><i class="bi bi-file-earmark-text"></i> Dokumen Regulasi</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Kontak Langsung</h4>
                    <ul class="contact-info">
                        <li><i class="bi bi-geo-alt-fill"></i> Jl. Ahmad Yani No. 17, Maumere, Sikka, NTT</li>
                        <li><i class="bi bi-telephone-fill"></i> (0382) 21234</li>
                        <li><i class="bi bi-envelope-fill"></i> bauk@unimof.ac.id</li>
                        <li><i class="bi bi-clock-fill"></i> Senin - Jumat: 08:00 - 16:00 WITA</li>
                    </ul>
                </div>
            </div>
            
            <div class="ult-footer-stats">
                <div class="ult-footer-stats-grid">
                    <div class="ult-footer-stat"><div class="ult-footer-stat-number">30+</div><div class="ult-footer-stat-label">Tahun Pengabdian</div></div>
                    <div class="ult-footer-stat"><div class="ult-footer-stat-number">150+</div><div class="ult-footer-stat-label">Pegawai Profesional</div></div>
                    <div class="ult-footer-stat"><div class="ult-footer-stat-number">12</div><div class="ult-footer-stat-label">Unit Kerja Terpadu</div></div>
                    <div class="ult-footer-stat"><div class="ult-footer-stat-number">5000+</div><div class="ult-footer-stat-label">Civitas Terlayani</div></div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <strong>BAUK UNIMOF</strong> - Universitas Muhammadiyah Maumere. All Rights Reserved.</p>
                <p><i class="bi bi-shield-fill-check"></i> Good University Governance • <a href="#" style="color:rgba(255,255,255,.8);">Kebijakan Privasi</a> • <a href="#" style="color:rgba(255,255,255,.8);">Syarat & Ketentuan</a></p>
            </div>
        </div>
    </footer>

    <!-- ===== BACK TO TOP (ORIGINAL CLASS) ===== -->
    <div class="ult-back-top">
        <button class="back-to-top" id="backToTop" aria-label="Kembali ke atas"><i class="bi bi-arrow-up"></i></button>
    </div>

    <!-- ===== FLOATING BUTTONS ===== -->
    <div class="ult-floating-buttons">
        <a href="https://wa.me/6281234567890?text=Halo%20BAUK%20UNIMOF,%20saya%20ingin%20bertanya..." target="_blank" class="ult-float-btn ult-float-whatsapp" title="Chat WhatsApp">
            <i class="bi bi-whatsapp"></i>
            <span class="ult-float-tooltip">Chat WhatsApp</span>
        </a>
        <button class="ult-float-btn ult-float-accessibility" id="ultA11yFloat" title="Aksesibilitas">
            <i class="bi bi-universal-access"></i>
            <span class="ult-float-tooltip">Aksesibilitas</span>
        </button>
    </div>

    <!-- ===== COOKIE CONSENT ===== -->
    <div class="ult-cookie" id="ultCookie">
        <div class="ult-cookie-header">
            <div class="ult-cookie-icon"><i class="bi bi-cookie"></i></div>
            <div>
                <h4>Kami Menggunakan Cookie</h4>
                <p>Website ini menggunakan cookie untuk meningkatkan pengalaman Anda dan menganalisis traffic. Dengan melanjutkan, Anda menyetujui penggunaan cookie sesuai kebijakan privasi kami.</p>
            </div>
        </div>
        <div class="ult-cookie-actions">
            <button class="ult-cookie-accept" onclick="ultAcceptCookie()">Terima Semua</button>
            <button class="ult-cookie-settings" onclick="ultAcceptCookie()">Hanya Diperlukan</button>
        </div>
    </div>

    <!-- ===== MOBILE BOTTOM NAV ===== -->
    <nav class="ult-mobile-nav">
        <div class="ult-mobile-nav-grid">
            <a href="<?= url() ?>" class="ult-mobile-nav-item <?= $cp === '/' ? 'active' : '' ?>"><i class="bi bi-house-fill"></i><span>Beranda</span></a>
            <a href="<?= url('layanan/kepegawaian') ?>" class="ult-mobile-nav-item"><i class="bi bi-grid-fill"></i><span>Layanan</span></a>
            <a href="<?= url('berita') ?>" class="ult-mobile-nav-item"><i class="bi bi-newspaper"></i><span>Berita</span></a>
            <a href="https://wa.me/6281234567890" target="_blank" class="ult-mobile-nav-item" style="color:#25D366;"><i class="bi bi-whatsapp"></i><span>Chat</span></a>
            <a href="<?= url('kontak') ?>" class="ult-mobile-nav-item"><i class="bi bi-telephone-fill"></i><span>Kontak</span></a>
        </div>
    </nav>

    <!-- ===== ORIGINAL PUBLIC SCRIPT ===== -->
    <script src="<?= asset('js/public-script.js') ?>"></script>

    <!-- ===== ULTIMATE ENHANCEMENTS SCRIPT ===== -->
    <script>
    (function() {
        'use strict';

        // ===== PRELOADER =====
        window.addEventListener('load', function() {
            setTimeout(function() {
                const preloader = document.getElementById('ultPreloader');
                if (preloader) preloader.classList.add('hidden');
                setTimeout(function() { if (preloader) preloader.remove(); }, 600);
            }, 400);
        });

        // ===== SCROLL PROGRESS BAR =====
        const progressBar = document.getElementById('ultScrollProgress');
        const headerWrapper = document.querySelector('.ult-header-wrapper');
        if (progressBar) {
            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset;
                const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                const progress = (scrollTop / docHeight) * 100;
                progressBar.style.width = progress + '%';
                
                if (headerWrapper) {
                    if (scrollTop > 50) headerWrapper.classList.add('scrolled');
                    else headerWrapper.classList.remove('scrolled');
                }
            });
        }

        // ===== SEARCH OVERLAY =====
        const searchToggle = document.getElementById('ultSearchToggle');
        const searchOverlay = document.getElementById('ultSearchOverlay');
        const searchClose = document.getElementById('ultSearchClose');
        const searchInput = document.getElementById('ultSearchInput');

        if (searchToggle) {
            searchToggle.addEventListener('click', function() {
                searchOverlay.classList.add('active');
                setTimeout(function() { searchInput.focus(); }, 100);
            });
        }
        if (searchClose) {
            searchClose.addEventListener('click', function() {
                searchOverlay.classList.remove('active');
            });
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                searchOverlay.classList.remove('active');
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchOverlay.classList.add('active');
                setTimeout(function() { searchInput.focus(); }, 100);
            }
        });

        // ===== ACCESSIBILITY PANEL =====
        const a11yBtn = document.getElementById('ultA11yBtn');
        const a11yFloat = document.getElementById('ultA11yFloat');
        const a11yPanel = document.getElementById('ultA11yPanel');
        const a11yClose = document.getElementById('ultA11yClose');

        function toggleA11y() { a11yPanel.classList.toggle('active'); }
        if (a11yBtn) a11yBtn.addEventListener('click', toggleA11y);
        if (a11yFloat) a11yFloat.addEventListener('click', toggleA11y);
        if (a11yClose) a11yClose.addEventListener('click', function() { a11yPanel.classList.remove('active'); });

        // ===== FLASH MESSAGE AUTO-DISMISS =====
        setTimeout(function() {
            document.querySelectorAll('.ult-flash').forEach(function(flash) {
                flash.style.opacity = '0';
                flash.style.transform = 'translateX(120%)';
                setTimeout(function() { flash.remove(); }, 400);
            });
        }, 5000);

        // ===== COOKIE CONSENT =====
        if (!localStorage.getItem('ult_cookie_consent')) {
            setTimeout(function() {
                const cookie = document.getElementById('ultCookie');
                if (cookie) cookie.classList.add('show');
            }, 2000);
        }

    })();

    // ===== GLOBAL FUNCTIONS =====
    function ultA11ySet(type) {
        document.body.classList.remove('ult-a11y-large', 'ult-a11y-xl');
        document.body.classList.add('ult-a11y-' + type);
        localStorage.setItem('ult_a11y_size', type);
    }

    function ultA11yToggle(type) {
        document.body.classList.toggle('ult-a11y-' + type);
        localStorage.setItem('ult_a11y_' + type, document.body.classList.contains('ult-a11y-' + type));
    }

    function ultA11yReset() {
        document.body.classList.remove('ult-a11y-large', 'ult-a11y-xl', 'ult-a11y-contrast', 'ult-a11y-grayscale', 'ult-a11y-dyslexia');
        localStorage.removeItem('ult_a11y_size');
        ['contrast', 'grayscale', 'dyslexia'].forEach(function(t) { localStorage.removeItem('ult_a11y_' + t); });
    }

    function ultAcceptCookie() {
        localStorage.setItem('ult_cookie_consent', 'true');
        const cookie = document.getElementById('ultCookie');
        if (cookie) {
            cookie.classList.remove('show');
            setTimeout(function() { cookie.remove(); }, 500);
        }
    }

    function ultSubscribeNewsletter(e) {
        e.preventDefault();
        alert('Terima kasih telah berlangganan! (Fitur akan terhubung ke sistem email)');
        e.target.reset();
    }

    // ===== LOAD SAVED ACCESSIBILITY PREFERENCES =====
    (function() {
        const size = localStorage.getItem('ult_a11y_size');
        if (size) document.body.classList.add('ult-a11y-' + size);
        ['contrast', 'grayscale', 'dyslexia'].forEach(function(t) {
            if (localStorage.getItem('ult_a11y_' + t) === 'true') document.body.classList.add('ult-a11y-' + t);
        });
    })();
    </script>
</body>
</html>