<?php
/**
 * =====================================================
 * BAUK UNIMOF - PUBLIC LAYOUT (ULTIMATE v2)
 * Elegant • Professional • Multimate
 * Header 2 lapis + Mega Menu + SEO dinamis
 * =====================================================
 */
$cp = current_path();
$db = \App\Helpers\Database::getInstance();

// ===== KONFIGURASI DINAMIS DARI SETTINGS =====
$__cfg = [];
try {
    foreach ($db->fetchAll("SELECT `key`, `value` FROM settings") as $__s) {
        $__cfg[$__s['key']] = $__s['value'];
    }
} catch (\Throwable $__e) {}

$__logo    = !empty($__cfg['site_logo'])    ? url('uploads/settings/' . $__cfg['site_logo'])    : null;
$__favicon = !empty($__cfg['site_favicon']) ? url('uploads/settings/' . $__cfg['site_favicon']) : null;
$__phone   = $__cfg['contact_phone']  ?? '(0382) 2425464';
$__email   = $__cfg['contact_email']  ?? 'bauk@unimof.ac.id';
$__address = $__cfg['contact_address'] ?? 'Jl. Ahmad Yani No. 17, Maumere, Sikka, NTT';
$__siteName = $__cfg['site_name'] ?? 'BAUK UNIMOF';
$__siteDesc = $__cfg['site_description'] ?? 'Biro Administrasi Umum dan Keuangan - Universitas Muhammadiyah Maumere. Layanan transparan, efisien, dan terintegrasi.';
$__waNumber = '6281234567890';

// ===== BERITA TERBARU (footer) =====
try {
    $latestNews = $db->fetchAll("SELECT slug, title, published_at FROM berita WHERE is_published = 1 AND deleted_at IS NULL ORDER BY published_at DESC LIMIT 3");
} catch (\Throwable $e) {
    $latestNews = [];
}

// ===== BREADCRUMB MAP (lengkap termasuk halaman profil baru) =====
$breadcrumbMap = [
    '/tentang'              => 'Tentang Kami',
    '/visi-misi'            => 'Visi & Misi',
    '/struktur-organisasi'  => 'Struktur Organisasi',
    '/tupoksi'              => 'Tupoksi',
    '/berita'               => 'Berita & Informasi',
    '/regulasi'             => 'Dokumen Regulasi',
    '/kontak'               => 'Hubungi Kami',
];
$currentPageName = $breadcrumbMap[$cp] ?? null;
if (str_starts_with($cp, '/layanan/')) {
    $section = basename($cp);
    $names = ['kepegawaian' => 'Bagian Kepegawaian', 'keuangan' => 'Bagian Keuangan', 'aset' => 'Aset & Rumah Tangga', 'k3l' => 'Tim K3L'];
    $currentPageName = $names[$section] ?? 'Layanan';
}
$__isProfilGroup  = in_array($cp, ['/tentang', '/visi-misi', '/struktur-organisasi', '/tupoksi'], true);
$__isLayananGroup = str_starts_with($cp, '/layanan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($__siteDesc) ?>">
    <meta name="keywords" content="BAUK UNIMOF, Universitas Muhammadiyah Maumere, Keuangan Kampus, Aset, Kepegawaian, Tupoksi, Struktur Organisasi">
    <meta name="author" content="BAUK UNIMOF">
    <meta name="theme-color" content="#0d2f4a">
    <link rel="canonical" href="<?= url(ltrim($cp, '/')) ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($title ?? $__siteName) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($__siteDesc) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:url" content="<?= url(ltrim($cp, '/')) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($__siteName) ?>">
    <?php if ($__logo): ?><meta property="og:image" content="<?= htmlspecialchars($__logo) ?>"><?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">

    <title><?= htmlspecialchars($title ?? $__siteName) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('css/public-style.css') ?>">

    <?php if ($__favicon): ?>
        <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($__favicon) ?>">
        <link rel="shortcut icon" href="<?= htmlspecialchars($__favicon) ?>">
    <?php else: ?>
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' fill='%231a5276'/%3E%3Ctext x='50' y='68' font-family='Arial' font-size='60' font-weight='bold' text-anchor='middle' fill='white'%3EB%3C/text%3E%3C/svg%3E">
    <?php endif; ?>

    <!-- JSON-LD SEO -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "GovernmentOrganization",
        "name": "<?= htmlspecialchars($__siteName) ?>",
        "description": <?= json_encode($__siteDesc, JSON_UNESCAPED_UNICODE) ?>,
        "url": "<?= url() ?>",
        <?php if ($__logo): ?>"logo": "<?= htmlspecialchars($__logo) ?>",<?php endif; ?>
        "address": { "@type": "PostalAddress", "streetAddress": <?= json_encode($__address, JSON_UNESCAPED_UNICODE) ?>, "addressLocality": "Maumere", "addressRegion": "NTT", "addressCountry": "ID" },
        "contactPoint": { "@type": "ContactPoint", "telephone": "<?= htmlspecialchars($__phone) ?>", "email": "<?= htmlspecialchars($__email) ?>", "contactType": "customer service" }
    }
    </script>

    <!-- =====================================================
         ULTIMATE v2 STYLES (scoped ult2-*, tidak merusak CSS lama)
         ===================================================== -->
    <style>
    /* ===== PRELOADER & SCROLL PROGRESS (dipertahankan) ===== */
    .ult-preloader{position:fixed;inset:0;background:#fff;z-index:99999;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:1rem;transition:opacity .5s,visibility .5s;}
    .ult-preloader.hidden{opacity:0;visibility:hidden;}
    .ult-preloader-logo{width:84px;height:84px;background:linear-gradient(135deg,#0d2f4a,#1a5276 60%,#2980b9);border-radius:22px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:2.6rem;font-weight:800;font-family:'Playfair Display',serif;animation:ult2_logoPulse 1.5s ease infinite;box-shadow:0 15px 40px rgba(13,47,74,.3);}
    .ult-preloader-bar{width:200px;height:3px;background:#e2e8f0;border-radius:10px;overflow:hidden;}
    .ult-preloader-bar::after{content:'';display:block;width:40%;height:100%;background:linear-gradient(90deg,#1a5276,#e67e22);animation:ult2_barLoad 1.2s ease infinite;}
    @keyframes ult2_logoPulse{0%,100%{transform:scale(1);}50%{transform:scale(1.06);}}
    @keyframes ult2_barLoad{0%{margin-left:-40%;}100%{margin-left:100%;}}
    .ult-scroll-progress{position:fixed;top:0;left:0;width:0;height:3px;background:linear-gradient(90deg,#1a5276,#e67e22,#27ae60);z-index:10000;transition:width .1s;}

    /* ===== SKIP LINK (aksesibilitas) ===== */
    .ult2-skip{position:absolute;left:-9999px;top:0;background:#1a5276;color:#fff;padding:.75rem 1.25rem;z-index:100000;border-radius:0 0 10px 0;font-weight:600;}
    .ult2-skip:focus{left:0;}

    /* ===== UTILITY BAR (lapis 1 - slim & padat info) ===== */
    .ult2-topbar{background:linear-gradient(90deg,#0a2740 0%,#0d2f4a 50%,#123c5e 100%);color:rgba(255,255,255,.92);font-size:.78rem;position:relative;z-index:1001;border-bottom:1px solid rgba(255,255,255,.08);}
    .ult2-topbar-inner{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:.5rem 0;flex-wrap:wrap;}
    .ult2-topbar-left,.ult2-topbar-right{display:flex;align-items:center;gap:1.1rem;flex-wrap:wrap;}
    .ult2-tb-item{display:inline-flex;align-items:center;gap:.4rem;white-space:nowrap;}
    .ult2-tb-item i{color:#f39c12;font-size:.8rem;}
    .ult2-tb-link{color:rgba(255,255,255,.92);text-decoration:none!important;transition:color .2s;}
    .ult2-tb-link:hover{color:#f39c12;}
    .ult2-tb-divider{width:1px;height:14px;background:rgba(255,255,255,.2);}
    .ult2-live{display:inline-flex;align-items:center;gap:.4rem;background:rgba(39,174,96,.18);color:#2ecc71;padding:.2rem .7rem;border-radius:20px;font-weight:600;font-size:.72rem;}
    .ult2-live-dot{width:6px;height:6px;background:#2ecc71;border-radius:50%;animation:ult2_blink 1.5s infinite;}
    @keyframes ult2_blink{0%,100%{opacity:1;}50%{opacity:.3;}}
    .ult2-socials{display:flex;gap:.35rem;}
    .ult2-socials a{width:24px;height:24px;border-radius:6px;background:rgba(255,255,255,.1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;transition:all .2s;text-decoration:none!important;}
    .ult2-socials a:hover{background:#e67e22;transform:translateY(-2px);}
    .ult2-lang{display:flex;align-items:center;gap:.2rem;background:rgba(255,255,255,.1);padding:.15rem .5rem;border-radius:20px;}
    .ult2-lang button{background:none;border:none;color:rgba(255,255,255,.75);cursor:pointer;font-size:.7rem;padding:.1rem .35rem;border-radius:10px;font-weight:600;}
    .ult2-lang button.active,.ult2-lang button:hover{background:#e67e22;color:#fff;}
    .ult2-a11y-btn{background:rgba(255,255,255,.1);border:none;color:#fff;padding:.25rem .7rem;border-radius:20px;cursor:pointer;font-size:.72rem;display:flex;align-items:center;gap:.35rem;font-weight:600;transition:all .2s;}
    .ult2-a11y-btn:hover{background:#e67e22;}

    /* ===== MAIN HEADER (lapis 2 - glass & sticky) ===== */
    .ult2-header{position:sticky;top:0;z-index:1000;background:rgba(255,255,255,.92);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-bottom:1px solid rgba(13,47,74,.08);transition:all .3s ease;}
    .ult2-header.ult2-scrolled{background:rgba(255,255,255,.98);box-shadow:0 6px 30px rgba(13,47,74,.1);}
    .ult2-header-inner{display:flex;align-items:center;justify-content:space-between;gap:1.5rem;padding:.85rem 0;transition:padding .3s ease;}
    .ult2-header.ult2-scrolled .ult2-header-inner{padding:.55rem 0;}

    /* Brand */
    .ult2-brand{display:flex;align-items:center;gap:.85rem;text-decoration:none!important;flex-shrink:0;}
    .ult2-brand-logo{position:relative;width:52px;height:52px;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:transform .3s;}
    .ult2-brand:hover .ult2-brand-logo{transform:scale(1.05) rotate(-3deg);}
    .ult2-brand-logo img{width:52px;height:52px;object-fit:contain;border-radius:50%;}
    .ult2-brand-logo .ult2-brand-fallback{width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#0d2f4a,#2980b9);color:#fff;font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:800;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 18px rgba(13,47,74,.3);}
    .ult2-brand-logo::after{content:'';position:absolute;inset:-4px;border:2px solid rgba(230,126,34,.35);border-radius:50%;animation:ult2_ring 3s ease infinite;pointer-events:none;}
    @keyframes ult2_ring{0%,100%{transform:scale(1);opacity:.6;}50%{transform:scale(1.08);opacity:.2;}}
    .ult2-brand-text{line-height:1.15;}
    .ult2-brand-text .ult2-brand-name{font-family:'Playfair Display',serif;font-size:1.45rem;font-weight:800;color:#0d2f4a;letter-spacing:.5px;margin:0;display:block;}
    .ult2-brand-text .ult2-brand-sub{display:block;font-size:.62rem;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;color:#e67e22;margin-top:2px;}
    .ult2-brand-text .ult2-brand-line{display:block;width:34px;height:2px;background:linear-gradient(90deg,#1a5276,#e67e22);margin-top:4px;border-radius:2px;}

    /* Nav */
    .ult2-nav{display:flex;align-items:center;gap:.25rem;}
    .ult2-nav > li{list-style:none;position:relative;}
    .ult2-navlink{display:flex;align-items:center;gap:.35rem;padding:.6rem .95rem;border-radius:10px;color:#33475b!important;text-decoration:none!important;font-weight:600;font-size:.9rem;transition:all .2s;position:relative;}
    .ult2-navlink:hover{color:#1a5276!important;background:rgba(26,82,118,.06);}
    .ult2-navlink.ult2-active{color:#1a5276!important;background:rgba(26,82,118,.08);}
    .ult2-navlink.ult2-active::after{content:'';position:absolute;left:50%;transform:translateX(-50%);bottom:2px;width:22px;height:3px;border-radius:3px;background:linear-gradient(90deg,#e67e22,#f39c12);}
    .ult2-navlink i.bi-chevron-down{font-size:.65rem;transition:transform .25s;}
    li:hover > .ult2-navlink i.bi-chevron-down{transform:rotate(180deg);}

    /* Mega Dropdown */
    .ult2-mega{position:absolute;top:calc(100% + 14px);left:50%;transform:translate(-50%,10px);width:620px;background:#fff;border-radius:18px;border:1px solid rgba(13,47,74,.08);box-shadow:0 25px 70px rgba(13,47,74,.16);padding:1.25rem;opacity:0;visibility:hidden;transition:all .25s ease;z-index:1002;}
    .ult2-mega::before{content:'';position:absolute;top:-7px;left:50%;transform:translateX(-50%) rotate(45deg);width:14px;height:14px;background:#fff;border-left:1px solid rgba(13,47,74,.08);border-top:1px solid rgba(13,47,74,.08);}
    li:hover > .ult2-mega{opacity:1;visibility:visible;transform:translate(-50%,0);}
    .ult2-mega-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;}
    .ult2-mega-item{display:flex;gap:.85rem;padding:.85rem;border-radius:12px;text-decoration:none!important;transition:all .2s;align-items:flex-start;}
    .ult2-mega-item:hover{background:linear-gradient(135deg,rgba(26,82,118,.06),rgba(230,126,34,.06));transform:translateX(3px);}
    .ult2-mi-icon{width:42px;height:42px;flex-shrink:0;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;color:#fff;background:linear-gradient(135deg,#1a5276,#2980b9);box-shadow:0 5px 14px rgba(26,82,118,.25);}
    .ult2-mega-item:nth-child(2) .ult2-mi-icon{background:linear-gradient(135deg,#e67e22,#f39c12);box-shadow:0 5px 14px rgba(230,126,34,.25);}
    .ult2-mega-item:nth-child(3) .ult2-mi-icon{background:linear-gradient(135deg,#27ae60,#2ecc71);box-shadow:0 5px 14px rgba(39,174,96,.25);}
    .ult2-mega-item:nth-child(4) .ult2-mi-icon{background:linear-gradient(135deg,#8e44ad,#9b59b6);box-shadow:0 5px 14px rgba(142,68,173,.25);}
    .ult2-mi-text strong{display:block;font-size:.88rem;color:#0d2f4a;margin-bottom:2px;}
    .ult2-mi-text span{font-size:.75rem;color:#7a8ba0;line-height:1.45;display:block;}
    .ult2-mega-footer{display:flex;justify-content:space-between;align-items:center;margin-top:.85rem;padding-top:.85rem;border-top:1px dashed rgba(13,47,74,.12);font-size:.78rem;color:#7a8ba0;}
    .ult2-mega-footer a{color:#e67e22;font-weight:700;text-decoration:none!important;display:inline-flex;align-items:center;gap:.35rem;}
    .ult2-mega-footer a:hover{gap:.6rem;}

    /* Header actions */
    .ult2-actions{display:flex;align-items:center;gap:.6rem;flex-shrink:0;}
    .ult2-icon-btn{width:42px;height:42px;border-radius:12px;border:1px solid rgba(13,47,74,.12);background:#fff;color:#1a5276;display:flex;align-items:center;justify-content:center;font-size:1.05rem;cursor:pointer;transition:all .25s;}
    .ult2-icon-btn:hover{background:#1a5276;color:#fff;border-color:#1a5276;transform:translateY(-2px);box-shadow:0 8px 20px rgba(26,82,118,.25);}
    .ult2-admin-link{display:flex;align-items:center;gap:.4rem;padding:.55rem .9rem;border-radius:12px;border:1.5px solid rgba(13,47,74,.15);color:#33475b!important;text-decoration:none!important;font-size:.82rem;font-weight:700;transition:all .25s;}
    .ult2-admin-link:hover{border-color:#1a5276;color:#1a5276!important;background:rgba(26,82,118,.05);}
    .ult2-cta{display:flex;align-items:center;gap:.45rem;padding:.6rem 1.3rem;border-radius:12px;background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff!important;text-decoration:none!important;font-size:.85rem;font-weight:700;box-shadow:0 8px 22px rgba(230,126,34,.35);transition:all .25s;}
    .ult2-cta:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(230,126,34,.45);color:#fff!important;}

    /* Toggler mobile */
    .ult2-toggler{display:none;width:44px;height:44px;border-radius:12px;border:1px solid rgba(13,47,74,.12);background:#fff;cursor:pointer;flex-direction:column;align-items:center;justify-content:center;gap:5px;}
    .ult2-toggler span{display:block;width:20px;height:2px;background:#0d2f4a;border-radius:2px;transition:all .3s;}

    /* ===== BREADCRUMB v2 ===== */
    .ult2-breadcrumb{background:linear-gradient(180deg,#f8fafc,#fff);border-bottom:1px solid rgba(13,47,74,.06);padding:.9rem 0;}
    .ult2-breadcrumb-list{display:flex;align-items:center;gap:.6rem;font-size:.82rem;color:#7a8ba0;flex-wrap:wrap;}
    .ult2-bc-home{display:inline-flex;align-items:center;gap:.4rem;background:rgba(26,82,118,.07);color:#1a5276!important;padding:.3rem .8rem;border-radius:20px;text-decoration:none!important;font-weight:600;transition:all .2s;}
    .ult2-bc-home:hover{background:#1a5276;color:#fff!important;}
    .ult2-bc-sep{color:#c3ced9;font-size:.65rem;}
    .ult2-bc-current{display:inline-flex;align-items:center;gap:.4rem;color:#0d2f4a;font-weight:700;font-family:'Playfair Display',serif;font-size:.92rem;}

    /* ===== SEARCH OVERLAY (dipertahankan + diperhalus) ===== */
    .ult-search-overlay{position:fixed;inset:0;background:rgba(10,39,64,.96);backdrop-filter:blur(12px);z-index:10001;display:flex;align-items:flex-start;justify-content:center;padding-top:14vh;opacity:0;visibility:hidden;transition:all .3s;}
    .ult-search-overlay.active{opacity:1;visibility:visible;}
    .ult-search-box{width:90%;max-width:720px;transform:translateY(-20px);transition:transform .3s;}
    .ult-search-overlay.active .ult-search-box{transform:translateY(0);}
    .ult-search-input-wrapper{position:relative;}
    .ult-search-input-wrapper input{width:100%;padding:1.3rem 1.6rem 1.3rem 3.8rem;font-size:1.2rem;border:none;border-radius:18px;background:#fff;color:#0d2f4a;box-shadow:0 25px 70px rgba(0,0,0,.3);}
    .ult-search-input-wrapper input:focus{outline:3px solid rgba(230,126,34,.5);}
    .ult-search-input-wrapper > i{position:absolute;left:1.4rem;top:50%;transform:translateY(-50%);font-size:1.3rem;color:#1a5276;}
    .ult-search-close{position:absolute;top:1.5rem;right:1.5rem;background:rgba(255,255,255,.12);color:#fff;border:none;width:52px;height:52px;border-radius:50%;font-size:1.4rem;cursor:pointer;transition:all .25s;}
    .ult-search-close:hover{background:#e74c3c;transform:rotate(90deg);}
    .ult-search-suggestions{margin-top:1rem;background:#fff;border-radius:16px;padding:1.1rem;max-height:46vh;overflow-y:auto;}
    .ult-search-suggestions h5{font-size:.72rem;text-transform:uppercase;color:#9aa8b8;margin-bottom:.7rem;letter-spacing:1.5px;font-weight:700;}
    .ult-search-suggestion-item{display:flex;align-items:center;gap:.8rem;padding:.75rem .85rem;border-radius:10px;color:#33475b!important;transition:all .2s;text-decoration:none!important;font-size:.9rem;font-weight:500;}
    .ult-search-suggestion-item:hover{background:rgba(26,82,118,.07);color:#1a5276!important;transform:translateX(4px);}
    .ult-search-suggestion-item i{color:#e67e22;font-size:1.05rem;}

    /* ===== FLASH, A11Y PANEL, COOKIE, FLOATING, MOBILE NAV (dipertahankan) ===== */
    .ult-flash{position:fixed;top:110px;right:20px;z-index:9998;max-width:420px;background:#fff;border-radius:14px;box-shadow:0 15px 40px rgba(0,0,0,.18);padding:1rem 1.25rem;display:flex;align-items:flex-start;gap:.75rem;animation:ult2_slideIn .4s ease;border-left:4px solid;}
    .ult-flash.success{border-color:#10b981;} .ult-flash.error{border-color:#ef4444;}
    .ult-flash-icon{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
    .ult-flash.success .ult-flash-icon{background:#d1fae5;color:#10b981;} .ult-flash.error .ult-flash-icon{background:#fee2e2;color:#ef4444;}
    .ult-flash-content{flex:1;font-size:.9rem;line-height:1.5;}
    .ult-flash-close{background:none;border:none;color:#9aa8b8;cursor:pointer;font-size:1.2rem;padding:0;line-height:1;}
    @keyframes ult2_slideIn{from{transform:translateX(120%);opacity:0;}to{transform:translateX(0);opacity:1;}}

    .ult-a11y-panel{position:fixed;right:-350px;top:0;width:320px;height:100vh;background:#fff;z-index:9999;box-shadow:-5px 0 25px rgba(0,0,0,.15);transition:right .3s;padding:1.5rem;overflow-y:auto;}
    .ult-a11y-panel.active{right:0;}
    .ult-a11y-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid #e2e8f0;}
    .ult-a11y-header h3{font-size:1.1rem;display:flex;align-items:center;gap:.5rem;margin:0;}
    .ult-a11y-close{background:#f1f5f9;border:none;width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;}
    .ult-a11y-section{margin-bottom:1.5rem;} .ult-a11y-section h4{font-size:.75rem;text-transform:uppercase;color:#9aa8b8;margin-bottom:.75rem;letter-spacing:1px;font-weight:700;}
    .ult-a11y-option{display:flex;justify-content:space-between;align-items:center;padding:.8rem;background:#f8fafc;border-radius:10px;margin-bottom:.5rem;}
    .ult-a11y-option button{background:#fff;border:1px solid #e2e8f0;width:36px;height:36px;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;}
    .ult-a11y-option button:hover{background:#1a5276;color:#fff;}
    .ult-a11y-reset{width:100%;padding:.85rem;background:#e74c3c;color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer;}
    body.ult-a11y-large{font-size:18px!important;} body.ult-a11y-xl{font-size:20px!important;}
    body.ult-a11y-contrast{filter:contrast(1.3);} body.ult-a11y-grayscale{filter:grayscale(1);} body.ult-a11y-dyslexia{font-family:'Comic Sans MS',cursive!important;}

    .ult-cookie{position:fixed;bottom:20px;left:20px;right:20px;max-width:500px;background:#fff;border-radius:16px;box-shadow:0 15px 40px rgba(0,0,0,.18);padding:1.5rem;z-index:996;display:flex;flex-direction:column;gap:1rem;transform:translateY(130%);transition:transform .4s;}
    .ult-cookie.show{transform:translateY(0);}
    .ult-cookie-header{display:flex;align-items:flex-start;gap:.75rem;}
    .ult-cookie-icon{width:46px;height:46px;background:linear-gradient(135deg,#f39c12,#e67e22);color:#fff;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
    .ult-cookie h4{margin:0 0 .25rem;font-size:1rem;} .ult-cookie p{font-size:.83rem;color:#5c6f82;line-height:1.6;margin:0;}
    .ult-cookie-actions{display:flex;gap:.5rem;}
    .ult-cookie-actions button{flex:1;padding:.75rem 1rem;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:.85rem;}
    .ult-cookie-accept{background:#1a5276;color:#fff;} .ult-cookie-settings{background:#f1f5f9;color:#33475b;border:1px solid #e2e8f0!important;}

    .ult-floating-buttons{position:fixed;right:20px;bottom:20px;z-index:997;display:flex;flex-direction:column;gap:.75rem;}
    .ult-float-btn{width:56px;height:56px;border-radius:50%;border:none;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;cursor:pointer;box-shadow:0 8px 20px rgba(0,0,0,.25);transition:all .3s;position:relative;text-decoration:none!important;}
    .ult-float-btn:hover{transform:translateY(-4px) scale(1.05);}
    .ult-float-whatsapp{background:linear-gradient(135deg,#25D366,#128C7E);}
    .ult-float-btn .ult-float-tooltip{position:absolute;right:calc(100% + 10px);background:#1e293b;color:#fff;padding:.5rem .875rem;border-radius:8px;font-size:.75rem;white-space:nowrap;opacity:0;visibility:hidden;transition:all .2s;pointer-events:none;font-weight:600;}
    .ult-float-btn:hover .ult-float-tooltip{opacity:1;visibility:visible;}
    .ult-float-whatsapp::after{content:'';position:absolute;inset:0;border-radius:50%;border:3px solid #25D366;animation:ult2_pulse 2s infinite;}
    @keyframes ult2_pulse{0%{transform:scale(1);opacity:1;}100%{transform:scale(1.45);opacity:0;}}
    .ult-back-top{position:fixed;bottom:92px;right:26px;z-index:997;}

    .ult-mobile-nav{display:none;position:fixed;bottom:0;left:0;right:0;background:#fff;z-index:995;border-top:1px solid rgba(13,47,74,.08);padding:.4rem 0;box-shadow:0 -4px 20px rgba(0,0,0,.08);}
    .ult-mobile-nav-grid{display:grid;grid-template-columns:repeat(5,1fr);}
    .ult-mobile-nav-item{display:flex;flex-direction:column;align-items:center;gap:.15rem;padding:.6rem .4rem;color:#5c6f82;font-size:.68rem;text-decoration:none!important;font-weight:600;}
    .ult-mobile-nav-item.active{color:#1a5276;}
    .ult-mobile-nav-item i{font-size:1.25rem;}

    /* ===== FOOTER v2 enhancements ===== */
    .ult2-footer-logo img{width:54px;height:54px;object-fit:contain;border-radius:50%;}
    .ult2-footer-profil a{display:flex;align-items:center;gap:.5rem;padding:.3rem 0;color:rgba(255,255,255,.75)!important;text-decoration:none!important;font-size:.88rem;transition:all .2s;}
    .ult2-footer-profil a:hover{color:#f39c12!important;transform:translateX(4px);}
    .ult2-footer-profil a i{font-size:.7rem;color:#e67e22;}

    /* ===== RESPONSIVE ===== */
    @media (max-width:1100px){ .ult2-mega{width:520px;} }
    @media (max-width:992px){
        .ult2-toggler{display:flex;}
        .ult2-nav{position:fixed;top:0;right:-320px;width:300px;height:100vh;background:#fff;flex-direction:column;align-items:stretch;padding:5rem 1.25rem 2rem;gap:.25rem;box-shadow:-10px 0 40px rgba(0,0,0,.15);transition:right .35s ease;overflow-y:auto;z-index:1001;}
        .ult2-nav.ult2-open{right:0;}
        .ult2-navlink{width:100%;justify-content:flex-start;}
        .ult2-mega{position:static;transform:none;opacity:1;visibility:visible;width:100%;box-shadow:none;border:0;padding:.25rem 0 .5rem .75rem;display:none;}
        .ult2-mega::before{display:none;}
        li.ult2-open > .ult2-mega{display:block;}
        .ult2-mega-grid{grid-template-columns:1fr;}
        .ult2-mega-footer{display:none;}
        .ult2-admin-link span{display:none;}
        .ult2-topbar-left .ult2-tb-item:nth-child(2){display:none;}
    }
    @media (max-width:768px){
        .ult2-topbar-right .ult2-socials,.ult2-topbar-right .ult2-tb-divider{display:none;}
        .ult2-cta span{display:none;}
        .ult-mobile-nav{display:block;}
        body{padding-bottom:64px;}
        .ult-back-top{bottom:88px;right:16px;}
        .ult-floating-buttons{bottom:80px;right:14px;}
        .ult-flash{top:90px;right:12px;left:12px;max-width:none;}
    }
    </style>
</head>
<body>

    <a href="#mainContent" class="ult2-skip">Lewati ke konten utama</a>

    <!-- ===== PRELOADER ===== -->
    <div class="ult-preloader" id="ultPreloader">
        <div class="ult-preloader-logo"><?= $__logo ? '' : 'B' ?><?php if ($__logo): ?><img src="<?= htmlspecialchars($__logo) ?>" alt="" style="width:56px;height:56px;object-fit:contain;border-radius:50%;"><?php endif; ?></div>
        <div class="ult-preloader-bar"></div>
    </div>

    <!-- ===== SCROLL PROGRESS ===== -->
    <div class="ult-scroll-progress" id="ultScrollProgress"></div>

    <!-- ===== SEARCH OVERLAY ===== -->
    <div class="ult-search-overlay" id="ultSearchOverlay">
        <button class="ult-search-close" id="ultSearchClose" aria-label="Tutup pencarian"><i class="bi bi-x-lg"></i></button>
        <div class="ult-search-box">
            <div class="ult-search-input-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" id="ultSearchInput" placeholder="Cari layanan, berita, regulasi..." autocomplete="off">
            </div>
            <div class="ult-search-suggestions">
                <h5>Akses Cepat</h5>
                <a href="<?= url('visi-misi') ?>" class="ult-search-suggestion-item"><i class="bi bi-compass-fill"></i><span>Visi & Misi BAUK</span></a>
                <a href="<?= url('struktur-organisasi') ?>" class="ult-search-suggestion-item"><i class="bi bi-diagram-3-fill"></i><span>Struktur Organisasi</span></a>
                <a href="<?= url('tupoksi') ?>" class="ult-search-suggestion-item"><i class="bi bi-clipboard-check-fill"></i><span>Tugas Pokok & Fungsi</span></a>
                <a href="<?= url('layanan/kepegawaian') ?>" class="ult-search-suggestion-item"><i class="bi bi-person-badge-fill"></i><span>Layanan Kepegawaian</span></a>
                <a href="<?= url('layanan/keuangan') ?>" class="ult-search-suggestion-item"><i class="bi bi-wallet2"></i><span>Layanan Keuangan</span></a>
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
            <button class="ult-a11y-close" id="ultA11yClose" aria-label="Tutup"><i class="bi bi-x"></i></button>
        </div>
        <div class="ult-a11y-section">
            <h4>Ukuran Teks</h4>
            <div class="ult-a11y-option"><span>Teks Normal</span><button onclick="ultA11yReset()" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></button></div>
            <div class="ult-a11y-option"><span>Teks Lebih Besar</span><button onclick="ultA11ySet('large')" title="Besar"><i class="bi bi-type"></i></button></div>
            <div class="ult-a11y-option"><span>Teks Sangat Besar</span><button onclick="ultA11ySet('xl')" title="Sangat besar"><i class="bi bi-type-h1"></i></button></div>
        </div>
        <div class="ult-a11y-section">
            <h4>Tampilan</h4>
            <div class="ult-a11y-option"><span>Kontras Tinggi</span><button onclick="ultA11yToggle('contrast')" title="Kontras"><i class="bi bi-circle-half"></i></button></div>
            <div class="ult-a11y-option"><span>Skala Abu-abu</span><button onclick="ultA11yToggle('grayscale')" title="Abu-abu"><i class="bi bi-droplet-half"></i></button></div>
            <div class="ult-a11y-option"><span>Ramah Disleksia</span><button onclick="ultA11yToggle('dyslexia')" title="Disleksia"><i class="bi bi-file-font"></i></button></div>
        </div>
        <button class="ult-a11y-reset" onclick="ultA11yReset()"><i class="bi bi-arrow-counterclockwise"></i> Reset Semua</button>
    </div>

    <!-- =====================================================
         LAPIS 1: UTILITY BAR
         ===================================================== -->
    <div class="ult2-topbar">
        <div class="container">
            <div class="ult2-topbar-inner">
                <div class="ult2-topbar-left">
                    <span class="ult2-tb-item"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($__address) ?></span>
                    <span class="ult2-tb-item"><i class="bi bi-clock-fill"></i> Senin - Sabtu: 08:00 - 17:00 WITA</span>
                    <span class="ult2-live"><span class="ult2-live-dot"></span> Sistem Online</span>
                </div>
                <div class="ult2-topbar-right">
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $__phone) ?>" class="ult2-tb-item ult2-tb-link"><i class="bi bi-telephone-fill"></i> <?= htmlspecialchars($__phone) ?></a>
                    <a href="mailto:<?= htmlspecialchars($__email) ?>" class="ult2-tb-item ult2-tb-link"><i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($__email) ?></a>
                    <span class="ult2-tb-divider"></span>
                    <div class="ult2-socials">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                        <a href="https://wa.me/<?= $__waNumber ?>" target="_blank" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    </div>
                    <span class="ult2-tb-divider"></span>
                    <div class="ult2-lang"><i class="bi bi-translate" style="font-size:.75rem;"></i><button class="active">ID</button><button>EN</button></div>
                    <button class="ult2-a11y-btn" id="ultA11yBtn"><i class="bi bi-universal-access"></i> Aksesibilitas</button>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         LAPIS 2: MAIN HEADER (glass sticky + mega menu)
         ===================================================== -->
    <header class="ult2-header" id="mainHeader">
        <div class="container">
            <div class="ult2-header-inner">

                <!-- Brand -->
                <a href="<?= url() ?>" class="ult2-brand">
                    <div class="ult2-brand-logo">
                        <?php if ($__logo): ?>
                            <img src="<?= htmlspecialchars($__logo) ?>" alt="Logo <?= htmlspecialchars($__siteName) ?>">
                        <?php else: ?>
                            <div class="ult2-brand-fallback">B</div>
                        <?php endif; ?>
                    </div>
                    <div class="ult2-brand-text">
                        <span class="ult2-brand-name">BAUK</span>
                        <span class="ult2-brand-sub">Universitas Muhammadiyah Maumere</span>
                        <span class="ult2-brand-line"></span>
                    </div>
                </a>

                <!-- Navigation -->
                <ul class="ult2-nav" id="navbarMenu">
                    <li><a href="<?= url() ?>" class="ult2-navlink <?= $cp === '/' ? 'ult2-active' : '' ?>">Beranda</a></li>

                    <!-- MEGA: PROFIL -->
                    <li class="ult2-dd <?= $__isProfilGroup ? 'ult2-open' : '' ?>">
                        <a href="#" class="ult2-navlink <?= $__isProfilGroup ? 'ult2-active' : '' ?>">Profil <i class="bi bi-chevron-down"></i></a>
                        <div class="ult2-mega">
                            <div class="ult2-mega-grid">
                                <a href="<?= url('tentang') ?>" class="ult2-mega-item">
                                    <div class="ult2-mi-icon"><i class="bi bi-building"></i></div>
                                    <div class="ult2-mi-text"><strong>Tentang Kami</strong><span>Profil, sejarah & pencapaian BAUK</span></div>
                                </a>
                                <a href="<?= url('visi-misi') ?>" class="ult2-mega-item">
                                    <div class="ult2-mi-icon"><i class="bi bi-compass"></i></div>
                                    <div class="ult2-mi-text"><strong>Visi & Misi</strong><span>Arah strategis & tujuan biro</span></div>
                                </a>
                                <a href="<?= url('struktur-organisasi') ?>" class="ult2-mega-item">
                                    <div class="ult2-mi-icon"><i class="bi bi-diagram-3"></i></div>
                                    <div class="ult2-mi-text"><strong>Struktur Organisasi</strong><span>Hierarki & pejabat struktural</span></div>
                                </a>
                                <a href="<?= url('tupoksi') ?>" class="ult2-mega-item">
                                    <div class="ult2-mi-icon"><i class="bi bi-clipboard-check"></i></div>
                                    <div class="ult2-mi-text"><strong>Tupoksi</strong><span>Tugas pokok & fungsi unit kerja</span></div>
                                </a>
                            </div>
                            <div class="ult2-mega-footer">
                                <span>Kenali institusi kami lebih dekat</span>
                                <a href="<?= url('tentang') ?>">Lihat profil lengkap <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </li>

                    <!-- MEGA: LAYANAN -->
                    <li class="ult2-dd <?= $__isLayananGroup ? 'ult2-open' : '' ?>">
                        <a href="#" class="ult2-navlink <?= $__isLayananGroup ? 'ult2-active' : '' ?>">Layanan <i class="bi bi-chevron-down"></i></a>
                        <div class="ult2-mega">
                            <div class="ult2-mega-grid">
                                <a href="<?= url('layanan/kepegawaian') ?>" class="ult2-mega-item">
                                    <div class="ult2-mi-icon"><i class="bi bi-person-badge"></i></div>
                                    <div class="ult2-mi-text"><strong>Kepegawaian</strong><span>Administrasi SDM, karir & kesejahteraan</span></div>
                                </a>
                                <a href="<?= url('layanan/keuangan') ?>" class="ult2-mega-item">
                                    <div class="ult2-mi-icon"><i class="bi bi-wallet2"></i></div>
                                    <div class="ult2-mi-text"><strong>Keuangan</strong><span>Anggaran, transaksi & pelaporan</span></div>
                                </a>
                                <a href="<?= url('layanan/aset') ?>" class="ult2-mega-item">
                                    <div class="ult2-mi-icon"><i class="bi bi-house-gear"></i></div>
                                    <div class="ult2-mi-text"><strong>Aset & Rumah Tangga</strong><span>Inventaris, sarpras & pemeliharaan</span></div>
                                </a>
                                <a href="<?= url('layanan/k3l') ?>" class="ult2-mega-item">
                                    <div class="ult2-mi-icon"><i class="bi bi-shield-check"></i></div>
                                    <div class="ult2-mi-text"><strong>K3L</strong><span>Keselamatan, kesehatan & lingkungan</span></div>
                                </a>
                            </div>
                            <div class="ult2-mega-footer">
                                <span>Empat pilar pelayanan terpadu</span>
                                <a href="<?= url('layanan/kepegawaian') ?>">Mulai layanan <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </li>

                    <li><a href="<?= url('berita') ?>" class="ult2-navlink <?= str_starts_with($cp, '/berita') ? 'ult2-active' : '' ?>">Berita</a></li>
                    <li><a href="<?= url('regulasi') ?>" class="ult2-navlink <?= $cp === '/regulasi' ? 'ult2-active' : '' ?>">Regulasi</a></li>
                    <li><a href="<?= url('kontak') ?>" class="ult2-navlink <?= $cp === '/kontak' ? 'ult2-active' : '' ?>">Kontak</a></li>
                </ul>

                <!-- Actions -->
                <div class="ult2-actions">
                    <button class="ult2-icon-btn" id="ultSearchToggle" title="Cari (Ctrl+K)" aria-label="Cari"><i class="bi bi-search"></i></button>
                    <a href="<?= url('admin/login') ?>" class="ult2-admin-link" title="Portal Admin"><i class="bi bi-shield-lock-fill"></i> <span>Admin</span></a>
                    <a href="<?= url('kontak') ?>" class="ult2-cta"><i class="bi bi-headset"></i> <span>Konsultasi</span></a>
                    <button class="ult2-toggler" id="navbarToggler" aria-label="Buka menu">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- ===== BREADCRUMB v2 ===== -->
    <?php if ($currentPageName && $cp !== '/'): ?>
    <div class="ult2-breadcrumb">
        <div class="container">
            <div class="ult2-breadcrumb-list">
                <a href="<?= url() ?>" class="ult2-bc-home"><i class="bi bi-house-fill"></i> Beranda</a>
                <i class="bi bi-chevron-right ult2-bc-sep"></i>
                <span class="ult2-bc-current"><i class="bi bi-bookmark-star-fill" style="color:#e67e22;"></i> <?= htmlspecialchars($currentPageName) ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== FLASH MESSAGES ===== -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="ult-flash success">
            <div class="ult-flash-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="ult-flash-content"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
            <button class="ult-flash-close" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
        </div>
        <?php unset($_SESSION['flash_success']); endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="ult-flash error">
            <div class="ult-flash-icon"><i class="bi bi-exclamation-circle-fill"></i></div>
            <div class="ult-flash-content">
                <?= is_array($_SESSION['flash_error']) ? implode('<br>', array_map('htmlspecialchars', $_SESSION['flash_error'])) : htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <button class="ult-flash-close" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
        </div>
        <?php unset($_SESSION['flash_error']); endif; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content" id="mainContent"><?= $content ?? '' ?></main>

    <!-- ===== NEWSLETTER ===== -->
    <section class="ult-newsletter" style="background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 100%);color:#fff;padding:4rem 0;position:relative;overflow:hidden;">
        <div class="container" style="position:relative;z-index:1;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center;" class="ult2-news-grid">
                <div>
                    <h3 style="font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:.75rem;">Tetap Terhubung dengan BAUK UNIMOF</h3>
                    <p style="color:rgba(255,255,255,.85);line-height:1.7;">Dapatkan update informasi keuangan, pengumuman penting, dan kebijakan baru langsung di email Anda.</p>
                    <div style="display:flex;gap:1.5rem;margin-top:1.25rem;flex-wrap:wrap;font-size:.85rem;color:rgba(255,255,255,.9);">
                        <span><i class="bi bi-check-circle-fill" style="color:#f39c12;"></i> Gratis</span>
                        <span><i class="bi bi-check-circle-fill" style="color:#f39c12;"></i> Tanpa spam</span>
                        <span><i class="bi bi-check-circle-fill" style="color:#f39c12;"></i> Berhenti kapan saja</span>
                    </div>
                </div>
                <form style="background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);padding:2rem;border-radius:20px;" onsubmit="ultSubscribeNewsletter(event)">
                    <h4 style="margin-bottom:1rem;font-size:1.05rem;"><i class="bi bi-envelope-paper-fill"></i> Berlangganan Newsletter</h4>
                    <div style="display:flex;gap:.5rem;margin-bottom:1rem;" class="ult2-news-input">
                        <input type="email" placeholder="nama@email.com" required style="flex:1;padding:.875rem 1.25rem;border:none;border-radius:10px;font-size:.95rem;">
                        <button type="submit" style="background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;border:none;padding:.875rem 1.5rem;border-radius:10px;font-weight:600;cursor:pointer;"><i class="bi bi-send-fill"></i> Kirim</button>
                    </div>
                    <label style="display:flex;align-items:flex-start;gap:.5rem;font-size:.78rem;color:rgba(255,255,255,.8);line-height:1.5;">
                        <input type="checkbox" required style="margin-top:3px;accent-color:#e67e22;">
                        <span>Dengan berlangganan, saya menyetujui kebijakan privasi BAUK UNIMOF.</span>
                    </label>
                </form>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER MAP ===== -->
    <div style="background:#f8f9fa;border-bottom:1px solid rgba(13,47,74,.06);">
        <div style="display:grid;grid-template-columns:1fr 1fr;" class="ult2-map-grid">
            <div style="padding:3rem;background:#0d2f4a;color:#fff;">
                <h4 style="font-size:1.25rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;"><i class="bi bi-geo-alt-fill" style="color:#f39c12;"></i> Kunjungi Kantor Kami</h4>
                <p style="color:rgba(255,255,255,.85);line-height:1.8;margin-bottom:1rem;"><strong>Biro Administrasi Umum dan Keuangan</strong><br>Universitas Muhammadiyah Maumere<br><?= htmlspecialchars($__address) ?></p>
                <p style="color:rgba(255,255,255,.85);"><i class="bi bi-telephone-fill"></i> <?= htmlspecialchars($__phone) ?><br><i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($__email) ?></p>
                <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.25rem;">
                    <a href="https://maps.google.com/?q=Universitas+Muhammadiyah+Maumere" target="_blank" style="background:rgba(255,255,255,.1);color:#fff!important;padding:.6rem 1.2rem;border-radius:25px;font-size:.85rem;display:flex;align-items:center;gap:.4rem;border:1px solid rgba(255,255,255,.2);text-decoration:none!important;"><i class="bi bi-map-fill"></i> Buka di Maps</a>
                    <a href="https://wa.me/<?= $__waNumber ?>" target="_blank" style="background:rgba(255,255,255,.1);color:#fff!important;padding:.6rem 1.2rem;border-radius:25px;font-size:.85rem;display:flex;align-items:center;gap:.4rem;border:1px solid rgba(255,255,255,.2);text-decoration:none!important;"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                </div>
            </div>
            <div style="min-height:320px;background:#e2e8f0;">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3950.123!2d122.2!3d-8.6!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOMKwMzYnMDAuMCJTIDEyMsKwMTInMDAuMCJF!5e0!3m2!1sid!2sid!4v1234567890" allowfullscreen="" loading="lazy" title="Peta Lokasi BAUK UNIMOF" style="width:100%;height:100%;border:0;"></iframe>
            </div>
        </div>
    </div>

    <!-- ===== MAIN FOOTER ===== -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-brand">
                        <div class="footer-logo brand-badge ult2-footer-logo">
                            <?php if ($__logo): ?>
                                <img src="<?= htmlspecialchars($__logo) ?>" alt="Logo BAUK">
                            <?php else: ?>
                                B
                            <?php endif; ?>
                        </div>
                        <h3><?= htmlspecialchars($__siteName) ?></h3>
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
                    <h4>Profil & Layanan</h4>
                    <ul class="ult2-footer-profil">
                        <li><a href="<?= url('tentang') ?>"><i class="bi bi-chevron-right"></i> Tentang Kami</a></li>
                        <li><a href="<?= url('visi-misi') ?>"><i class="bi bi-chevron-right"></i> Visi & Misi</a></li>
                        <li><a href="<?= url('struktur-organisasi') ?>"><i class="bi bi-chevron-right"></i> Struktur Organisasi</a></li>
                        <li><a href="<?= url('tupoksi') ?>"><i class="bi bi-chevron-right"></i> Tupoksi</a></li>
                        <li><a href="<?= url('layanan/kepegawaian') ?>"><i class="bi bi-chevron-right"></i> Layanan Kepegawaian</a></li>
                        <li><a href="<?= url('layanan/keuangan') ?>"><i class="bi bi-chevron-right"></i> Layanan Keuangan</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Informasi Terbaru</h4>
                    <ul>
                        <?php if (!empty($latestNews)): foreach ($latestNews as $n): ?>
                            <li><a href="<?= url('berita/' . $n['slug']) ?>"><i class="bi bi-newspaper"></i> <?= htmlspecialchars(mb_substr($n['title'], 0, 45)) ?>...</a></li>
                        <?php endforeach; else: ?>
                            <li><a href="<?= url('berita') ?>"><i class="bi bi-chevron-right"></i> Lihat Semua Berita</a></li>
                        <?php endif; ?>
                        <li><a href="<?= url('regulasi') ?>"><i class="bi bi-file-earmark-text"></i> Dokumen Regulasi</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Kontak Langsung</h4>
                    <ul class="contact-info">
                        <li><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($__address) ?></li>
                        <li><i class="bi bi-telephone-fill"></i> <?= htmlspecialchars($__phone) ?></li>
                        <li><i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($__email) ?></li>
                        <li><i class="bi bi-clock-fill"></i> Senin - Sabtu: 08:00 - 17:00 WITA</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <strong><?= htmlspecialchars($__siteName) ?></strong> - Universitas Muhammadiyah Maumere. All Rights Reserved.</p>
                <p><i class="bi bi-shield-fill-check"></i> Good University Governance • <a href="#" style="color:rgba(255,255,255,.8);text-decoration:none;">Kebijakan Privasi</a> • <a href="#" style="color:rgba(255,255,255,.8);text-decoration:none;">Syarat & Ketentuan</a></p>
            </div>
        </div>
    </footer>

    <!-- ===== BACK TO TOP & FLOATING ===== -->
    <div class="ult-back-top">
        <button class="back-to-top" id="backToTop" aria-label="Kembali ke atas"><i class="bi bi-arrow-up"></i></button>
    </div>
    <div class="ult-floating-buttons">
        <a href="https://wa.me/<?= $__waNumber ?>?text=Halo%20BAUK%20UNIMOF,%20saya%20ingin%20bertanya..." target="_blank" class="ult-float-btn ult-float-whatsapp" title="Chat WhatsApp">
            <i class="bi bi-whatsapp"></i>
            <span class="ult-float-tooltip">Chat WhatsApp</span>
        </a>
    </div>

    <!-- ===== COOKIE CONSENT ===== -->
    <div class="ult-cookie" id="ultCookie">
        <div class="ult-cookie-header">
            <div class="ult-cookie-icon"><i class="bi bi-cookie"></i></div>
            <div>
                <h4>Kami Menggunakan Cookie</h4>
                <p>Website ini menggunakan cookie untuk meningkatkan pengalaman Anda dan menganalisis traffic.</p>
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
            <a href="<?= url('struktur-organisasi') ?>" class="ult-mobile-nav-item <?= $cp === '/struktur-organisasi' ? 'active' : '' ?>"><i class="bi bi-diagram-3-fill"></i><span>Profil</span></a>
            <a href="<?= url('layanan/kepegawaian') ?>" class="ult-mobile-nav-item"><i class="bi bi-grid-fill"></i><span>Layanan</span></a>
            <a href="<?= url('berita') ?>" class="ult-mobile-nav-item <?= str_starts_with($cp, '/berita') ? 'active' : '' ?>"><i class="bi bi-newspaper"></i><span>Berita</span></a>
            <a href="<?= url('kontak') ?>" class="ult-mobile-nav-item <?= $cp === '/kontak' ? 'active' : '' ?>"><i class="bi bi-telephone-fill"></i><span>Kontak</span></a>
        </div>
    </nav>

    <!-- ===== SCRIPTS ===== -->
    <script src="<?= asset('js/public-script.js') ?>"></script>
    <script>
    (function() {
        'use strict';

        // Preloader
        window.addEventListener('load', function() {
            setTimeout(function() {
                var p = document.getElementById('ultPreloader');
                if (p) { p.classList.add('hidden'); setTimeout(function(){ p.remove(); }, 600); }
            }, 350);
        });

        // Scroll progress + header shrink
        var progressBar = document.getElementById('ultScrollProgress');
        var header = document.getElementById('mainHeader');
        window.addEventListener('scroll', function() {
            var top = window.pageYOffset;
            var h = document.documentElement.scrollHeight - window.innerHeight;
            if (progressBar) progressBar.style.width = (h > 0 ? (top / h) * 100 : 0) + '%';
            if (header) header.classList.toggle('ult2-scrolled', top > 40);
        }, { passive: true });

        // Mobile nav toggle (kompatibel dgn ID lama)
        var toggler = document.getElementById('navbarToggler');
        var nav = document.getElementById('navbarMenu');
        if (toggler && nav) {
            toggler.addEventListener('click', function(e) {
                e.stopPropagation();
                nav.classList.toggle('ult2-open');
                nav.classList.toggle('active'); // kompatibilitas CSS lama
            });
            document.addEventListener('click', function(e) {
                if (!nav.contains(e.target) && !toggler.contains(e.target)) {
                    nav.classList.remove('ult2-open'); nav.classList.remove('active');
                }
            });
        }

        // Mega dropdown klik (mobile)
        document.querySelectorAll('.ult2-dd > a').forEach(function(a) {
            a.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    this.parentElement.classList.toggle('ult2-open');
                } else {
                    e.preventDefault();
                }
            });
        });

        // Search overlay
        var sToggle = document.getElementById('ultSearchToggle');
        var sOverlay = document.getElementById('ultSearchOverlay');
        var sClose = document.getElementById('ultSearchClose');
        var sInput = document.getElementById('ultSearchInput');
        function openSearch(){ sOverlay.classList.add('active'); setTimeout(function(){ sInput.focus(); }, 120); }
        function closeSearch(){ sOverlay.classList.remove('active'); }
        if (sToggle) sToggle.addEventListener('click', openSearch);
        if (sClose) sClose.addEventListener('click', closeSearch);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSearch();
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openSearch(); }
        });

        // Accessibility panel
        var aBtn = document.getElementById('ultA11yBtn');
        var aPanel = document.getElementById('ultA11yPanel');
        var aClose = document.getElementById('ultA11yClose');
        if (aBtn) aBtn.addEventListener('click', function(){ aPanel.classList.toggle('active'); });
        if (aClose) aClose.addEventListener('click', function(){ aPanel.classList.remove('active'); });

        // Flash auto-dismiss
        setTimeout(function() {
            document.querySelectorAll('.ult-flash').forEach(function(f) {
                f.style.opacity = '0'; f.style.transform = 'translateX(120%)';
                setTimeout(function(){ f.remove(); }, 400);
            });
        }, 5000);

        // Cookie consent
        if (!localStorage.getItem('ult_cookie_consent')) {
            setTimeout(function() {
                var c = document.getElementById('ultCookie');
                if (c) c.classList.add('show');
            }, 2000);
        }
    })();

    // Global a11y & utility functions
    function ultA11ySet(t){ document.body.classList.remove('ult-a11y-large','ult-a11y-xl'); document.body.classList.add('ult-a11y-'+t); localStorage.setItem('ult_a11y_size', t); }
    function ultA11yToggle(t){ document.body.classList.toggle('ult-a11y-'+t); localStorage.setItem('ult_a11y_'+t, document.body.classList.contains('ult-a11y-'+t)); }
    function ultA11yReset(){ document.body.classList.remove('ult-a11y-large','ult-a11y-xl','ult-a11y-contrast','ult-a11y-grayscale','ult-a11y-dyslexia'); localStorage.removeItem('ult_a11y_size'); ['contrast','grayscale','dyslexia'].forEach(function(t){ localStorage.removeItem('ult_a11y_'+t); }); }
    function ultAcceptCookie(){ localStorage.setItem('ult_cookie_consent','true'); var c = document.getElementById('ultCookie'); if (c) { c.classList.remove('show'); setTimeout(function(){ c.remove(); }, 500); } }
    function ultSubscribeNewsletter(e){ e.preventDefault(); alert('Terima kasih telah berlangganan! (Fitur akan terhubung ke sistem email)'); e.target.reset(); }

    // Restore a11y prefs
    (function() {
        var s = localStorage.getItem('ult_a11y_size');
        if (s) document.body.classList.add('ult-a11y-' + s);
        ['contrast','grayscale','dyslexia'].forEach(function(t) {
            if (localStorage.getItem('ult_a11y_' + t) === 'true') document.body.classList.add('ult-a11y-' + t);
        });
    })();
    </script>
    <style>
    /* Responsive tambahan newsletter & map */
    @media (max-width:992px){
        .ult2-news-grid{grid-template-columns:1fr!important;gap:2rem!important;}
        .ult2-map-grid{grid-template-columns:1fr!important;}
    }
    @media (max-width:640px){
        .ult2-news-input{flex-direction:column;}
    }
    </style>
</body>
</html>