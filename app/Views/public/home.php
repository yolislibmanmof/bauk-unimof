<?php
/**
 * =====================================================
 * BAUK UNIMOF - HOMEPAGE (ULTIMATE v3)
 * Cinematic • Professional • Multimate
 * =====================================================
 */
$db = \App\Helpers\Database::getInstance();

// ===== SETTINGS DINAMIS =====
$cfg = [];
try {
    foreach ($db->fetchAll("SELECT `key`, `value` FROM settings") as $s) {
        $cfg[$s['key']] = $s['value'];
    }
} catch (\Throwable $__e) {}

function getPubImage($key, $default = '') {
    global $cfg;
    return !empty($cfg[$key]) ? url('uploads/settings/' . $cfg[$key]) : $default;
}

// ===== DATA KONTEN =====
$news      = $db->fetchAll("SELECT * FROM berita WHERE is_published = 1 AND deleted_at IS NULL ORDER BY published_at DESC LIMIT 4");
$featured  = $news[0] ?? null;
$otherNews = array_slice($news, 1, 3);
$pengumumanAktif = $db->fetchAll("SELECT * FROM pengumuman WHERE is_active = 1 AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY priority DESC, created_at DESC LIMIT 5");

// ===== COUNTER DINAMIS =====
$totalPegawai = $db->count('pegawai', "status = 'active' AND deleted_at IS NULL") ?: 150;
$totalAset    = $db->count('aset', "status = 'active' AND deleted_at IS NULL") ?: 2500;
$transaksi    = $db->count('transaksi_keuangan', "MONTH(transaction_date) = MONTH(CURRENT_DATE()) AND YEAR(transaction_date) = YEAR(CURRENT_DATE()) AND deleted_at IS NULL") ?: 850;
$departemen   = $db->count('departments', "status = 'active' AND deleted_at IS NULL") ?: 12;

// ===== VISI MISI PREVIEW =====
$visi = $cfg['visi_text'] ?? '';
$misiRaw = $cfg['misi_text'] ?? '';
$misiPreview = array_slice(array_filter(array_map('trim', explode("\n", $misiRaw))), 0, 3);

// ===== TUPOKSI PREVIEW =====
try {
    $tupoksiPreview = $db->fetchAll("SELECT * FROM tupoksi WHERE deleted_at IS NULL AND is_active = 1 ORDER BY sort_order ASC LIMIT 4");
} catch (\Throwable $__e) { $tupoksiPreview = []; }
?>

<!-- =====================================================
     HOMEPAGE v3 STYLES (scoped home-v3-*)
     ===================================================== -->
<style>
/* ===== HERO SINEMATIK ===== */
.home-v3-hero{position:relative;min-height:92vh;display:flex;align-items:center;background:linear-gradient(135deg,#050f1e 0%,#0a2740 35%,#123c5e 70%,#1a5276 100%);color:#fff;overflow:hidden;padding:6rem 0 7rem;}
.home-v3-hero::before{content:'';position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Ccircle cx='60' cy='60' r='1.5'/%3E%3C/g%3E%3C/svg%3E");}
.home-v3-hero::after{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 70% 30%,rgba(230,126,34,.15) 0%,transparent 55%),radial-gradient(ellipse at 20% 80%,rgba(39,174,96,.12) 0%,transparent 55%);}

.home-v3-shape{position:absolute;border-radius:50%;filter:blur(60px);opacity:.4;z-index:1;}
.home-v3-shape-1{width:420px;height:420px;background:#e67e22;top:-100px;right:-80px;animation:homev3_float 18s ease-in-out infinite;}
.home-v3-shape-2{width:340px;height:340px;background:#27ae60;bottom:-120px;left:-100px;animation:homev3_float 22s ease-in-out infinite reverse;}
.home-v3-shape-3{width:220px;height:220px;background:#3498db;top:40%;left:50%;animation:homev3_float 25s ease-in-out infinite;}
@keyframes homev3_float{0%,100%{transform:translate(0,0) scale(1);}33%{transform:translate(40px,-40px) scale(1.1);}66%{transform:translate(-30px,30px) scale(.9);}}

.home-v3-hero-content{position:relative;z-index:3;}
.home-v3-hero-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:4rem;align-items:center;}

.home-v3-hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);padding:.5rem 1.2rem;border-radius:50px;font-size:.78rem;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;margin-bottom:1.5rem;}
.home-v3-hero-badge i{color:#f39c12;font-size:.95rem;}

.home-v3-hero-title{font-family:'Playfair Display',serif;font-size:clamp(2.2rem,5.5vw,4.2rem);font-weight:800;line-height:1.08;margin-bottom:1.5rem;letter-spacing:-.5px;}
.home-v3-hero-title .home-v3-accent{color:#f39c12;position:relative;display:inline-block;}
.home-v3-hero-title .home-v3-accent::after{content:'';position:absolute;bottom:4px;left:0;right:0;height:8px;background:rgba(230,126,34,.25);z-index:-1;border-radius:4px;}

.home-v3-hero-desc{font-size:1.08rem;line-height:1.75;color:rgba(255,255,255,.88);margin-bottom:2.25rem;max-width:560px;}

.home-v3-hero-cta{display:flex;gap:.85rem;flex-wrap:wrap;margin-bottom:2.5rem;}
.home-v3-cta-primary{display:inline-flex;align-items:center;gap:.55rem;padding:1rem 2rem;background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff!important;text-decoration:none!important;border-radius:14px;font-weight:700;font-size:.95rem;box-shadow:0 12px 32px rgba(230,126,34,.4);transition:all .3s;}
.home-v3-cta-primary:hover{transform:translateY(-3px);box-shadow:0 18px 42px rgba(230,126,34,.5);color:#fff!important;}
.home-v3-cta-secondary{display:inline-flex;align-items:center;gap:.55rem;padding:1rem 2rem;background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1.5px solid rgba(255,255,255,.25);color:#fff!important;text-decoration:none!important;border-radius:14px;font-weight:700;font-size:.95rem;transition:all .3s;}
.home-v3-cta-secondary:hover{background:rgba(255,255,255,.18);border-color:#f39c12;transform:translateY(-3px);color:#fff!important;}

.home-v3-hero-meta{display:flex;gap:1.75rem;flex-wrap:wrap;margin-bottom:2.5rem;}
.home-v3-meta-item{display:flex;align-items:center;gap:.5rem;font-size:.85rem;font-weight:600;color:rgba(255,255,255,.95);}
.home-v3-meta-item i{color:#f39c12;font-size:1.1rem;}

.home-v3-hero-quick{display:grid;grid-template-columns:repeat(3,1fr);gap:.85rem;}
.home-v3-quick-card{display:flex;align-items:center;gap:.85rem;padding:1rem 1.15rem;background:rgba(255,255,255,.06);backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,.12);border-radius:14px;color:#fff!important;text-decoration:none!important;transition:all .3s;}
.home-v3-quick-card:hover{background:rgba(255,255,255,.14);border-color:#f39c12;transform:translateY(-4px);box-shadow:0 15px 40px rgba(0,0,0,.25);color:#fff!important;}
.home-v3-qc-icon{width:44px;height:44px;border-radius:11px;background:linear-gradient(135deg,#e67e22,#f39c12);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;box-shadow:0 6px 16px rgba(230,126,34,.35);}
.home-v3-qc-text{font-size:.82rem;line-height:1.3;}
.home-v3-qc-text strong{display:block;font-size:.95rem;font-weight:700;margin-bottom:2px;}

/* Hero visual card */
.home-v3-visual{position:relative;}
.home-v3-visual-card{background:linear-gradient(145deg,rgba(255,255,255,.12),rgba(255,255,255,.04));backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.18);border-radius:28px;padding:2rem;box-shadow:0 30px 80px rgba(0,0,0,.35);position:relative;}
.home-v3-visual-card::before{content:'';position:absolute;top:-1px;left:20%;right:20%;height:2px;background:linear-gradient(90deg,transparent,#f39c12,transparent);}

.home-v3-stat-mini{display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;margin-bottom:1.5rem;}
.home-v3-stat-item{background:rgba(255,255,255,.06);padding:1.15rem;border-radius:14px;border:1px solid rgba(255,255,255,.1);}
.home-v3-stat-num{font-family:'Playfair Display',serif;font-size:2rem;font-weight:800;color:#f39c12;line-height:1;margin-bottom:.35rem;display:block;}
.home-v3-stat-lbl{font-size:.78rem;color:rgba(255,255,255,.8);font-weight:600;letter-spacing:.5px;}

.home-v3-service-mini{background:rgba(255,255,255,.06);border-radius:14px;padding:1.15rem;border:1px solid rgba(255,255,255,.1);}
.home-v3-service-mini-title{font-size:.85rem;font-weight:700;margin-bottom:.75rem;color:#fff;display:flex;align-items:center;gap:.5rem;}
.home-v3-service-mini-title i{color:#f39c12;}
.home-v3-service-mini-list{display:flex;flex-direction:column;gap:.5rem;}
.home-v3-service-mini-item{display:flex;align-items:center;gap:.6rem;font-size:.8rem;color:rgba(255,255,255,.85);}
.home-v3-service-mini-item i{color:#2ecc71;font-size:.7rem;}

.home-v3-scroll{position:absolute;bottom:30px;left:50%;transform:translateX(-50%);z-index:5;color:rgba(255,255,255,.7);text-align:center;font-size:.72rem;letter-spacing:2.5px;text-transform:uppercase;cursor:pointer;transition:all .3s;}
.home-v3-scroll:hover{color:#fff;}
.home-v3-scroll-mouse{width:22px;height:36px;border:2px solid rgba(255,255,255,.5);border-radius:11px;margin:0 auto .5rem;position:relative;}
.home-v3-scroll-mouse::before{content:'';position:absolute;top:6px;left:50%;transform:translateX(-50%);width:3px;height:7px;background:rgba(255,255,255,.9);border-radius:3px;animation:homev3_scrollWheel 2s infinite;}
@keyframes homev3_scrollWheel{0%{opacity:1;top:6px;}100%{opacity:0;top:18px;}}

/* ===== SPOTLIGHT KEPALA BAUK ===== */
.home-v3-spotlight{padding:6rem 0;background:linear-gradient(180deg,#f8fafc 0%,#fff 100%);position:relative;overflow:hidden;}
.home-v3-spotlight::before{content:'';position:absolute;top:0;left:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(26,82,118,.06) 0%,transparent 70%);border-radius:50%;}
.home-v3-spotlight::after{content:'';position:absolute;bottom:0;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(230,126,34,.06) 0%,transparent 70%);border-radius:50%;}

.home-v3-spotlight-grid{display:grid;grid-template-columns:420px 1fr;gap:4rem;align-items:center;position:relative;z-index:2;}

.home-v3-spotlight-img{position:relative;}
.home-v3-spotlight-frame{position:relative;width:380px;height:460px;margin:0 auto;border-radius:24px;overflow:hidden;box-shadow:0 30px 70px rgba(13,47,74,.25);transform:rotate(-2deg);transition:transform .5s;}
.home-v3-spotlight-frame:hover{transform:rotate(0);}
.home-v3-spotlight-frame img{width:100%;height:100%;object-fit:cover;}
.home-v3-spotlight-frame::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(13,47,74,.4) 0%,transparent 40%);}

.home-v3-spotlight-deco{position:absolute;width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#e67e22,#f39c12);display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#fff;box-shadow:0 15px 35px rgba(230,126,34,.4);z-index:3;}
.home-v3-spotlight-deco-1{top:-25px;right:25px;}
.home-v3-spotlight-deco-2{bottom:-25px;left:25px;background:linear-gradient(135deg,#1a5276,#2980b9);box-shadow:0 15px 35px rgba(26,82,118,.4);}

.home-v3-spotlight-content .home-v3-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(26,82,118,.08);color:#1a5276;padding:.45rem 1.1rem;border-radius:50px;font-size:.78rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:1rem;}
.home-v3-spotlight-content .home-v3-badge i{color:#e67e22;}

.home-v3-spotlight-title{font-family:'Playfair Display',serif;font-size:clamp(1.7rem,3.2vw,2.6rem);font-weight:800;line-height:1.15;color:#0d2f4a;margin-bottom:1.25rem;}
.home-v3-spotlight-title .home-v3-accent{color:#e67e22;}

.home-v3-quote-mark{font-family:'Playfair Display',serif;font-size:5rem;line-height:1;color:#e67e22;opacity:.25;position:absolute;top:-20px;left:-15px;}

.home-v3-spotlight-quote{position:relative;margin-bottom:1.5rem;}
.home-v3-spotlight-quote p{font-size:1rem;line-height:1.85;color:#475569;font-style:italic;}

.home-v3-spotlight-name{display:flex;align-items:center;gap:1.25rem;padding-top:1.5rem;border-top:2px solid rgba(13,47,74,.08);}
.home-v3-name-avatar{width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#1a5276,#2980b9);color:#fff;display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;flex-shrink:0;}
.home-v3-name-text strong{display:block;font-size:1.1rem;font-weight:700;color:#0d2f4a;}
.home-v3-name-text span{font-size:.85rem;color:#64748b;}
.home-v3-name-signature{margin-left:auto;font-family:'Brush Script MT',cursive;font-size:2rem;color:#1a5276;font-weight:700;}

/* ===== VISION MISSION PREVIEW ===== */
.home-v3-vm-preview{padding:6rem 0;background:#fff;position:relative;}
.home-v3-vm-grid{display:grid;grid-template-columns:1fr 1fr;gap:2.5rem;margin-top:3rem;}
.home-v3-vm-card{position:relative;padding:3rem 2.5rem;border-radius:24px;overflow:hidden;transition:all .4s;min-height:440px;}
.home-v3-vm-card:hover{transform:translateY(-8px);}

.home-v3-vm-visi{background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 60%,#2980b9 100%);color:#fff;box-shadow:0 25px 60px rgba(13,47,74,.25);}
.home-v3-vm-misi{background:#fff;border:2px solid rgba(13,47,74,.08);color:#0d2f4a;box-shadow:0 15px 40px rgba(13,47,74,.08);}

.home-v3-vm-card::before{content:'';position:absolute;top:-50%;right:-20%;width:300px;height:300px;background:radial-gradient(circle,rgba(255,255,255,.12) 0%,transparent 70%);border-radius:50%;}
.home-v3-vm-misi::before{background:radial-gradient(circle,rgba(230,126,34,.08) 0%,transparent 70%);}

.home-v3-vm-icon{width:80px;height:80px;border-radius:22px;display:flex;align-items:center;justify-content:center;font-size:2.2rem;margin-bottom:1.5rem;position:relative;z-index:2;}
.home-v3-vm-visi .home-v3-vm-icon{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.2);}
.home-v3-vm-misi .home-v3-vm-icon{background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;box-shadow:0 10px 28px rgba(230,126,34,.35);}

.home-v3-vm-card h3{font-family:'Playfair Display',serif;font-size:1.85rem;font-weight:700;margin-bottom:1rem;position:relative;z-index:2;}
.home-v3-vm-card p,.home-v3-vm-card ul{position:relative;z-index:2;}
.home-v3-vm-visi p{font-size:1.05rem;line-height:1.8;color:rgba(255,255,255,.92);}
.home-v3-vm-misi ul{list-style:none;padding:0;margin:0;}
.home-v3-vm-misi li{display:flex;gap:.85rem;padding:.65rem 0;font-size:.95rem;color:#475569;line-height:1.6;border-bottom:1px dashed rgba(13,47,74,.1);}
.home-v3-vm-misi li:last-child{border-bottom:0;}
.home-v3-vm-misi li i{color:#e67e22;margin-top:.2rem;flex-shrink:0;}

.home-v3-vm-link{display:inline-flex;align-items:center;gap:.5rem;margin-top:1.5rem;padding:.65rem 1.3rem;border-radius:10px;font-weight:700;font-size:.85rem;text-decoration:none!important;transition:all .25s;position:relative;z-index:2;}
.home-v3-vm-visi .home-v3-vm-link{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);}
.home-v3-vm-visi .home-v3-vm-link:hover{background:rgba(255,255,255,.25);color:#fff;}
.home-v3-vm-misi .home-v3-vm-link{background:#0d2f4a;color:#fff;}
.home-v3-vm-misi .home-v3-vm-link:hover{background:#e67e22;color:#fff;}

/* ===== TRUST STRIP ===== */
.home-v3-trust{background:#fff;padding:2.5rem 0;border-top:1px solid rgba(13,47,74,.06);border-bottom:1px solid rgba(13,47,74,.06);}
.home-v3-trust-grid{display:flex;justify-content:space-around;align-items:center;flex-wrap:wrap;gap:1.5rem;}
.home-v3-trust-item{display:flex;align-items:center;gap:.85rem;color:#64748b;font-weight:600;font-size:.88rem;transition:all .3s;}
.home-v3-trust-item:hover{color:#0d2f4a;transform:translateY(-3px);}
.home-v3-trust-icon{width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#e6f0fa,#d4e4f5);color:#1a5276;display:flex;align-items:center;justify-content:center;font-size:1.4rem;}
.home-v3-trust-item strong{display:block;color:#0d2f4a;font-size:.95rem;font-weight:700;}

/* ===== STATS COUNTER ===== */
.home-v3-stats{padding:5rem 0;background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 50%,#123c5e 100%);color:#fff;position:relative;overflow:hidden;}
.home-v3-stats::before{content:'';position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='40' cy='40' r='1'/%3E%3C/g%3E%3C/svg%3E");}
.home-v3-stats-header{text-align:center;margin-bottom:3.5rem;position:relative;z-index:2;}
.home-v3-stats-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);padding:.4rem 1rem;border-radius:50px;font-size:.75rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#f39c12;margin-bottom:1rem;}
.home-v3-stats-title{font-family:'Playfair Display',serif;font-size:clamp(1.7rem,3vw,2.5rem);font-weight:700;margin-bottom:.75rem;}
.home-v3-stats-sub{color:rgba(255,255,255,.75);max-width:600px;margin:0 auto;}

.home-v3-stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;position:relative;z-index:2;}
.home-v3-stat-card{background:rgba(255,255,255,.06);backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,.12);padding:2.25rem 1.75rem;border-radius:22px;text-align:center;transition:all .4s;position:relative;overflow:hidden;}
.home-v3-stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#e67e22,#f39c12);transform:scaleX(0);transform-origin:left;transition:transform .4s;}
.home-v3-stat-card:hover{background:rgba(255,255,255,.12);transform:translateY(-6px);box-shadow:0 20px 50px rgba(0,0,0,.3);}
.home-v3-stat-card:hover::before{transform:scaleX(1);}

.home-v3-stat-icon{width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto 1.25rem;box-shadow:0 10px 28px rgba(230,126,34,.35);}
.home-v3-stat-card:nth-child(2) .home-v3-stat-icon{background:linear-gradient(135deg,#27ae60,#2ecc71);box-shadow:0 10px 28px rgba(39,174,96,.35);}
.home-v3-stat-card:nth-child(3) .home-v3-stat-icon{background:linear-gradient(135deg,#3498db,#2980b9);box-shadow:0 10px 28px rgba(52,152,219,.35);}
.home-v3-stat-card:nth-child(4) .home-v3-stat-icon{background:linear-gradient(135deg,#9b59b6,#8e44ad);box-shadow:0 10px 28px rgba(142,68,173,.35);}

.home-v3-stat-num{font-family:'Playfair Display',serif;font-size:3rem;font-weight:800;color:#f39c12;line-height:1;margin-bottom:.5rem;}
.home-v3-stat-label{font-size:.85rem;color:rgba(255,255,255,.8);font-weight:600;letter-spacing:.5px;margin-bottom:.5rem;}
.home-v3-stat-trend{display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;color:#2ecc71;font-weight:600;background:rgba(39,174,96,.15);padding:.25rem .7rem;border-radius:20px;}

/* ===== SERVICES ===== */
.home-v3-services{padding:6rem 0;background:#fff;}
.home-v3-services-header{text-align:center;margin-bottom:3.5rem;}
.home-v3-services-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;counter-reset:svc;}
.home-v3-service-card{counter-increment:svc;background:#fff;border:1px solid rgba(13,47,74,.08);border-radius:22px;padding:2.25rem 1.75rem;transition:all .4s;position:relative;overflow:hidden;text-decoration:none!important;color:#0d2f4a!important;display:flex;flex-direction:column;}
.home-v3-service-card::before{content:'0' counter(svc);position:absolute;top:1rem;right:1.5rem;font-family:'Playfair Display',serif;font-size:3.5rem;font-weight:800;color:rgba(13,47,74,.06);line-height:1;transition:all .4s;}
.home-v3-service-card::after{content:'';position:absolute;top:0;left:0;width:100%;height:4px;background:linear-gradient(90deg,#1a5276,#e67e22);transform:scaleX(0);transform-origin:left;transition:transform .4s;}
.home-v3-service-card:hover{transform:translateY(-8px);box-shadow:0 25px 60px rgba(13,47,74,.15);border-color:transparent;}
.home-v3-service-card:hover::before{color:rgba(230,126,34,.35);transform:scale(1.1);}
.home-v3-service-card:hover::after{transform:scaleX(1);}

.home-v3-service-icon{width:68px;height:68px;border-radius:18px;background:linear-gradient(135deg,#e6f0fa,#d4e4f5);color:#1a5276;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin-bottom:1.25rem;transition:all .35s;}
.home-v3-service-card:nth-child(1) .home-v3-service-icon{background:linear-gradient(135deg,#1a5276,#2980b9);color:#fff;box-shadow:0 10px 28px rgba(26,82,118,.3);}
.home-v3-service-card:nth-child(2) .home-v3-service-icon{background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;box-shadow:0 10px 28px rgba(230,126,34,.3);}
.home-v3-service-card:nth-child(3) .home-v3-service-icon{background:linear-gradient(135deg,#27ae60,#2ecc71);color:#fff;box-shadow:0 10px 28px rgba(39,174,96,.3);}
.home-v3-service-card:nth-child(4) .home-v3-service-icon{background:linear-gradient(135deg,#8e44ad,#9b59b6);color:#fff;box-shadow:0 10px 28px rgba(142,68,173,.3);}

.home-v3-service-card h3{font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:700;margin-bottom:.65rem;}
.home-v3-service-card > p{font-size:.9rem;color:#64748b;line-height:1.6;margin-bottom:1.25rem;}
.home-v3-service-list{list-style:none;padding:0;margin:0 0 1.5rem 0;flex:1;}
.home-v3-service-list li{display:flex;align-items:center;gap:.55rem;font-size:.82rem;color:#475569;padding:.35rem 0;}
.home-v3-service-list li i{color:#2ecc71;font-size:.8rem;flex-shrink:0;}
.home-v3-service-link{color:#e67e22;font-weight:700;font-size:.85rem;display:inline-flex;align-items:center;gap:.4rem;margin-top:auto;transition:gap .3s;}
.home-v3-service-card:hover .home-v3-service-link{gap:.75rem;}

/* ===== TUPOKSI HIGHLIGHT ===== */
.home-v3-tupoksi{padding:6rem 0;background:linear-gradient(180deg,#f8fafc,#fff);}
.home-v3-tupoksi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;margin-top:3rem;}
.home-v3-tp-card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 10px 30px rgba(13,47,74,.08);transition:all .4s;}
.home-v3-tp-card:hover{transform:translateY(-8px);box-shadow:0 25px 60px rgba(13,47,74,.15);}
.home-v3-tp-header{padding:2rem 1.5rem;color:#fff;position:relative;overflow:hidden;}
.home-v3-tp-header::before{content:'';position:absolute;top:-30%;right:-20%;width:180px;height:180px;background:radial-gradient(circle,rgba(255,255,255,.15) 0%,transparent 70%);border-radius:50%;}
.home-v3-tp-icon{width:56px;height:56px;border-radius:14px;background:rgba(255,255,255,.2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:1rem;border:1px solid rgba(255,255,255,.3);position:relative;z-index:2;}
.home-v3-tp-kode{display:inline-block;font-size:.7rem;letter-spacing:1.5px;font-weight:700;background:rgba(255,255,255,.25);padding:.2rem .7rem;border-radius:20px;margin-bottom:.5rem;position:relative;z-index:2;}
.home-v3-tp-header h3{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;position:relative;z-index:2;}
.home-v3-tp-body{padding:1.5rem;}
.home-v3-tp-body p{font-size:.85rem;color:#64748b;line-height:1.6;margin:0;}

/* ===== PENGUMUMAN TICKER ===== */
.home-v3-ticker{background:linear-gradient(90deg,#e67e22,#f39c12);color:#fff;padding:.85rem 0;overflow:hidden;position:relative;z-index:10;}
.home-v3-ticker-wrap{display:flex;align-items:center;max-width:1200px;margin:0 auto;padding:0 1rem;}
.home-v3-ticker-label{display:inline-flex;align-items:center;gap:.5rem;background:rgba(0,0,0,.2);padding:.5rem 1rem;border-radius:25px;font-size:.78rem;font-weight:700;letter-spacing:.5px;margin-right:1.5rem;flex-shrink:0;}
.home-v3-ticker-track{flex:1;overflow:hidden;position:relative;white-space:nowrap;}
.home-v3-ticker-content{display:inline-flex;gap:3rem;animation:homev3_ticker 45s linear infinite;align-items:center;}
.home-v3-ticker-item{display:flex;align-items:center;gap:.5rem;font-size:.88rem;font-weight:500;}
@keyframes homev3_ticker{0%{transform:translateX(0);}100%{transform:translateX(-50%);}}

/* ===== RESPONSIVE ===== */
@media (max-width:1100px){
    .home-v3-hero-grid{grid-template-columns:1fr;gap:3rem;}
    .home-v3-visual{order:-1;}
    .home-v3-spotlight-grid{grid-template-columns:1fr;gap:3rem;}
    .home-v3-spotlight-img{order:-1;}
}
@media (max-width:992px){
    .home-v3-services-grid,.home-v3-tupoksi-grid{grid-template-columns:repeat(2,1fr);}
    .home-v3-stats-grid{grid-template-columns:repeat(2,1fr);}
    .home-v3-vm-grid{grid-template-columns:1fr;}
    .home-v3-hero-quick{grid-template-columns:1fr 1fr;}
}
@media (max-width:640px){
    .home-v3-hero{padding:4rem 0 5rem;min-height:auto;}
    .home-v3-hero-quick{grid-template-columns:1fr;}
    .home-v3-services-grid,.home-v3-tupoksi-grid,.home-v3-stats-grid{grid-template-columns:1fr;}
    .home-v3-vm-card{padding:2rem 1.5rem;}
    .home-v3-spotlight-frame{width:280px;height:340px;}
}
</style>

<!-- =====================================================
     PENGUMUMAN TICKER
     ===================================================== -->
<?php if (!empty($pengumumanAktif)): ?>
<div class="home-v3-ticker">
    <div class="home-v3-ticker-wrap">
        <span class="home-v3-ticker-label"><i class="bi bi-megaphone-fill"></i> PENGUMUMAN</span>
        <div class="home-v3-ticker-track">
            <div class="home-v3-ticker-content">
                <?php foreach ($pengumumanAktif as $p): ?>
                    <span class="home-v3-ticker-item"><i class="bi bi-dot"></i> <?= htmlspecialchars($p['title']) ?></span>
                <?php endforeach; ?>
                <?php foreach ($pengumumanAktif as $p): ?>
                    <span class="home-v3-ticker-item"><i class="bi bi-dot"></i> <?= htmlspecialchars($p['title']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- =====================================================
     HERO SINEMATIK
     ===================================================== -->
<section class="home-v3-hero">
    <div class="home-v3-shape home-v3-shape-1"></div>
    <div class="home-v3-shape home-v3-shape-2"></div>
    <div class="home-v3-shape home-v3-shape-3"></div>

    <div class="container home-v3-hero-content">
        <div class="home-v3-hero-grid">
            <div>
                <span class="home-v3-hero-badge"><i class="bi bi-bank2"></i> Universitas Muhammadiyah Maumere</span>
                <h1 class="home-v3-hero-title">
                    Biro Administrasi Umum<br>
                    <span class="home-v3-accent">& Keuangan</span>
                </h1>
                <p class="home-v3-hero-desc">
                    Mewujudkan tata kelola universitas yang <strong>transparan</strong>, <strong>efisien</strong>, dan <strong>terintegrasi</strong> melalui sistem informasi modern berbasis <em>Good University Governance</em>.
                </p>

                <div class="home-v3-hero-cta">
                    <a href="<?= url('layanan/keuangan') ?>" class="home-v3-cta-primary">
                        <i class="bi bi-cash-stack"></i> Layanan Keuangan
                    </a>
                    <a href="<?= url('tentang') ?>" class="home-v3-cta-secondary">
                        <i class="bi bi-play-circle"></i> Tentang Kami
                    </a>
                </div>

                <div class="home-v3-hero-meta">
                    <span class="home-v3-meta-item"><i class="bi bi-patch-check-fill"></i> Terakreditasi BAN-PT</span>
                    <span class="home-v3-meta-item"><i class="bi bi-shield-fill-check"></i> ISO 9001:2015</span>
                    <span class="home-v3-meta-item"><i class="bi bi-clock-fill"></i> Layanan 24/7 Online</span>
                </div>

                <div class="home-v3-hero-quick">
                    <a href="<?= url('layanan/kepegawaian') ?>" class="home-v3-quick-card">
                        <div class="home-v3-qc-icon"><i class="bi bi-person-vcard-fill"></i></div>
                        <div class="home-v3-qc-text"><strong>e-Kepegawaian</strong>Data & Karir Pegawai</div>
                    </a>
                    <a href="<?= url('layanan/keuangan') ?>" class="home-v3-quick-card">
                        <div class="home-v3-qc-icon"><i class="bi bi-credit-card-2-front-fill"></i></div>
                        <div class="home-v3-qc-text"><strong>e-Budgeting</strong>Sistem Anggaran</div>
                    </a>
                    <a href="<?= url('layanan/aset') ?>" class="home-v3-quick-card">
                        <div class="home-v3-qc-icon"><i class="bi bi-box2-heart-fill"></i></div>
                        <div class="home-v3-qc-text"><strong>SI-ASET</strong>Inventaris Kampus</div>
                    </a>
                </div>
            </div>

            <!-- VISUAL CARD -->
            <div class="home-v3-visual">
                <div class="home-v3-visual-card">
                    <div class="home-v3-stat-mini">
                        <div class="home-v3-stat-item">
                            <span class="home-v3-stat-num" data-count="<?= $totalPegawai ?>">0</span>
                            <span class="home-v3-stat-lbl">PEGAWAI AKTIF</span>
                        </div>
                        <div class="home-v3-stat-item">
                            <span class="home-v3-stat-num" data-count="<?= $totalAset ?>">0</span>
                            <span class="home-v3-stat-lbl">ASET TERKELOLA</span>
                        </div>
                        <div class="home-v3-stat-item">
                            <span class="home-v3-stat-num" data-count="<?= $transaksi ?>">0</span>
                            <span class="home-v3-stat-lbl">TRANSAKSI/BULAN</span>
                        </div>
                        <div class="home-v3-stat-item">
                            <span class="home-v3-stat-num" data-count="<?= $departemen ?>">0</span>
                            <span class="home-v3-stat-lbl">UNIT KERJA</span>
                        </div>
                    </div>
                    <div class="home-v3-service-mini">
                        <div class="home-v3-service-mini-title"><i class="bi bi-stars"></i> Layanan Unggulan</div>
                        <div class="home-v3-service-mini-list">
                            <div class="home-v3-service-mini-item"><i class="bi bi-check-circle-fill"></i> Monitoring Real-time</div>
                            <div class="home-v3-service-mini-item"><i class="bi bi-check-circle-fill"></i> Audit Trail Lengkap</div>
                            <div class="home-v3-service-mini-item"><i class="bi bi-check-circle-fill"></i> Integrasi Sistem Kampus</div>
                            <div class="home-v3-service-mini-item"><i class="bi bi-check-circle-fill"></i> Keamanan Data AES-256</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="home-v3-scroll" onclick="document.getElementById('homev3Spotlight').scrollIntoView({behavior:'smooth'})">
        <div class="home-v3-scroll-mouse"></div>
        <span>Scroll</span>
    </div>
</section>

<!-- =====================================================
     SPOTLIGHT KEPALA BAUK
     ===================================================== -->
<section class="home-v3-spotlight" id="homev3Spotlight">
    <div class="container">
        <div class="home-v3-spotlight-grid">
            <div class="home-v3-spotlight-img">
                <div class="home-v3-spotlight-deco home-v3-spotlight-deco-1"><i class="bi bi-quote"></i></div>
                <div class="home-v3-spotlight-frame">
                    <img src="<?= getPubImage('kepala_bauk_photo', asset('images/user-placeholder.png')) ?>" alt="Kepala BAUK">
                </div>
                <div class="home-v3-spotlight-deco home-v3-spotlight-deco-2"><i class="bi bi-award-fill"></i></div>
            </div>

            <div class="home-v3-spotlight-content">
                <span class="home-v3-badge"><i class="bi bi-mic-fill"></i> Sambutan</span>
                <h2 class="home-v3-spotlight-title">
                    Pesan dari <span class="home-v3-accent">Kepala Biro</span>
                </h2>

                <div class="home-v3-spotlight-quote">
                    <span class="home-v3-quote-mark">"</span>
                    <p>
                        <em>Assalamu'alaikum Warahmatullahi Wabarakatuh.</em><br>
                        Segala puji bagi Allah SWT. Website BAUK UNIMOF hadir sebagai wujud nyata komitmen kami menjawab tantangan era digital. Melalui platform ini, berbagai layanan — dari pengelolaan anggaran, inventarisasi aset, hingga administrasi kepegawaian — dapat diakses dengan mudah oleh seluruh civitas akademika.
                    </p>
                    <p>
                        Kami percaya bahwa pelayanan modern berbasis teknologi informasi merupakan langkah strategis mewujudkan <em>Good University Governance</em>.
                    </p>
                </div>

                <div class="home-v3-spotlight-name">
                    <div class="home-v3-name-avatar">
                        <?php
                            $kepalaName = $cfg['kepala_bauk_name'] ?? 'Kepala BAUK';
                            echo strtoupper(substr($kepalaName, 0, 1));
                        ?>
                    </div>
                    <div class="home-v3-name-text">
                        <strong><?= htmlspecialchars($cfg['kepala_bauk_name'] ?? 'Kepala BAUK') ?></strong>
                        <span><?= htmlspecialchars($cfg['kepala_bauk_title'] ?? 'Kepala Biro Administrasi Umum & Keuangan') ?></span>
                    </div>
                    <div class="home-v3-name-signature">Kepala BAUK</div>
                </div>

                <a href="<?= url('tentang') ?>" class="home-v3-vm-link" style="background:#e67e22;color:#fff;margin-top:1.5rem;display:inline-flex;">
                    Selengkapnya <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     VISION MISSION PREVIEW
     ===================================================== -->
<section class="home-v3-vm-preview">
    <div class="container">
        <div style="text-align:center;max-width:700px;margin:0 auto;">
            <span class="home-v3-badge" style="background:rgba(26,82,118,.08);color:#1a5276;display:inline-flex;align-items:center;gap:.5rem;padding:.4rem 1rem;border-radius:50px;font-size:.78rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:1rem;">
                <i class="bi bi-compass-fill" style="color:#e67e22;"></i> Arah Strategis
            </span>
            <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.7rem,3vw,2.5rem);font-weight:700;color:#0d2f4a;margin-bottom:.75rem;">Visi & Misi BAUK</h2>
            <p style="color:#64748b;line-height:1.7;">Landasan strategis dalam setiap langkah pelayanan kami untuk mendukung kemajuan UNIMOF</p>
        </div>

        <div class="home-v3-vm-grid">
            <!-- VISI -->
            <div class="home-v3-vm-card home-v3-vm-visi">
                <div class="home-v3-vm-icon"><i class="bi bi-eye-fill"></i></div>
                <h3>Visi</h3>
                <?php if ($visi): ?>
                    <p><?= htmlspecialchars($visi) ?></p>
                <?php else: ?>
                    <p>Menjadi biro pelayanan prima yang profesional, transparan, dan akuntabel dalam mendukung kemajuan UNIMOF menuju universitas berdaya saing global.</p>
                <?php endif; ?>
                <a href="<?= url('visi-misi') ?>" class="home-v3-vm-link">
                    Baca lengkap <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <!-- MISI -->
            <div class="home-v3-vm-card home-v3-vm-misi">
                <div class="home-v3-vm-icon"><i class="bi bi-bullseye"></i></div>
                <h3>Misi</h3>
                <?php if (!empty($misiPreview)): ?>
                    <ul>
                        <?php foreach ($misiPreview as $m): ?>
                            <li><i class="bi bi-check-circle-fill"></i><span><?= htmlspecialchars($m) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <ul>
                        <li><i class="bi bi-check-circle-fill"></i><span>Menyelenggarakan administrasi umum yang tertib dan efisien</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Mengelola keuangan universitas secara akuntabel</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Mengembangkan SDM yang kompeten dan berintegritas</span></li>
                    </ul>
                <?php endif; ?>
                <a href="<?= url('visi-misi') ?>" class="home-v3-vm-link">
                    Lihat semua misi <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     TRUST STRIP
     ===================================================== -->
<section class="home-v3-trust">
    <div class="container">
        <div class="home-v3-trust-grid">
            <div class="home-v3-trust-item">
                <div class="home-v3-trust-icon"><i class="bi bi-award-fill"></i></div>
                <div><strong>Terakreditasi</strong><span>BAN-PT Unggul</span></div>
            </div>
            <div class="home-v3-trust-item">
                <div class="home-v3-trust-icon"><i class="bi bi-shield-fill-check"></i></div>
                <div><strong>ISO 9001:2015</strong><span>Manajemen Mutu</span></div>
            </div>
            <div class="home-v3-trust-item">
                <div class="home-v3-trust-icon"><i class="bi bi-globe2"></i></div>
                <div><strong>Muhammadiyah</strong><span>Jaringan Global</span></div>
            </div>
            <div class="home-v3-trust-item">
                <div class="home-v3-trust-icon"><i class="bi bi-patch-check-fill"></i></div>
                <div><strong>Terverifikasi</strong><span>Kemendikbudristek</span></div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     STATS COUNTER
     ===================================================== -->
<section class="home-v3-stats">
    <div class="container">
        <div class="home-v3-stats-header">
            <span class="home-v3-stats-badge"><i class="bi bi-graph-up-arrow"></i> Kinerja Kami</span>
            <h2 class="home-v3-stats-title">Angka Berbicara</h2>
            <p class="home-v3-stats-sub">Data terkini dari operasional BAUK UNIMOF sebagai bukti komitmen kami dalam melayani civitas akademika</p>
        </div>

        <div class="home-v3-stats-grid">
            <div class="home-v3-stat-card">
                <div class="home-v3-stat-icon"><i class="bi bi-people-fill"></i></div>
                <div class="home-v3-stat-num home-v3-count" data-count="<?= $totalPegawai ?>">0</div>
                <div class="home-v3-stat-label">Total Pegawai Aktif</div>
                <div class="home-v3-stat-trend"><i class="bi bi-arrow-up-short"></i> Terus Berkembang</div>
            </div>
            <div class="home-v3-stat-card">
                <div class="home-v3-stat-icon"><i class="bi bi-box-seam-fill"></i></div>
                <div class="home-v3-stat-num home-v3-count" data-count="<?= $totalAset ?>">0</div>
                <div class="home-v3-stat-label">Aset Terkelola</div>
                <div class="home-v3-stat-trend"><i class="bi bi-arrow-up-short"></i> Inventaris Terpadu</div>
            </div>
            <div class="home-v3-stat-card">
                <div class="home-v3-stat-icon"><i class="bi bi-cash-coin"></i></div>
                <div class="home-v3-stat-num home-v3-count" data-count="<?= $transaksi ?>">0</div>
                <div class="home-v3-stat-label">Transaksi/Bulan</div>
                <div class="home-v3-stat-trend"><i class="bi bi-graph-up-arrow"></i> Real-time</div>
            </div>
            <div class="home-v3-stat-card">
                <div class="home-v3-stat-icon"><i class="bi bi-buildings-fill"></i></div>
                <div class="home-v3-stat-num home-v3-count" data-count="<?= $departemen ?>">0</div>
                <div class="home-v3-stat-label">Unit Kerja Terpadu</div>
                <div class="home-v3-stat-trend"><i class="bi bi-diagram-3-fill"></i> Koordinasi Optimal</div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     SERVICES (4 PILAR LAYANAN)
     ===================================================== -->
<section class="home-v3-services">
    <div class="container">
        <div class="home-v3-services-header">
            <span class="home-v3-badge" style="background:rgba(26,82,118,.08);color:#1a5276;display:inline-flex;align-items:center;gap:.5rem;padding:.4rem 1rem;border-radius:50px;font-size:.78rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:1rem;">
                <i class="bi bi-grid-3x3-gap-fill" style="color:#e67e22;"></i> Layanan Kami
            </span>
            <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.7rem,3vw,2.5rem);font-weight:700;color:#0d2f4a;margin-bottom:.75rem;">Empat Pilar Pelayanan</h2>
            <p style="color:#64748b;max-width:600px;margin:0 auto;">Bidang layanan utama BAUK UNIMOF dalam mendukung tata kelola universitas yang baik</p>
        </div>

        <div class="home-v3-services-grid">
            <a href="<?= url('layanan/kepegawaian') ?>" class="home-v3-service-card">
                <div class="home-v3-service-icon"><i class="bi bi-person-badge-fill"></i></div>
                <h3>Kepegawaian</h3>
                <p>Pengelolaan SDM komprehensif dan terintegrasi.</p>
                <ul class="home-v3-service-list">
                    <li><i class="bi bi-check-circle-fill"></i> Administrasi & mutasi pegawai</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pengembangan karir</li>
                    <li><i class="bi bi-check-circle-fill"></i> Penggajian & kesejahteraan</li>
                </ul>
                <span class="home-v3-service-link">Selengkapnya <i class="bi bi-arrow-right"></i></span>
            </a>

            <a href="<?= url('layanan/keuangan') ?>" class="home-v3-service-card">
                <div class="home-v3-service-icon"><i class="bi bi-wallet2"></i></div>
                <h3>Keuangan</h3>
                <p>Pengelolaan akuntabel, transparan, sesuai regulasi.</p>
                <ul class="home-v3-service-list">
                    <li><i class="bi bi-check-circle-fill"></i> Penyusunan RKAT tahunan</li>
                    <li><i class="bi bi-check-circle-fill"></i> Transaksi real-time</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pelaporan berkala</li>
                </ul>
                <span class="home-v3-service-link">Selengkapnya <i class="bi bi-arrow-right"></i></span>
            </a>

            <a href="<?= url('layanan/aset') ?>" class="home-v3-service-card">
                <div class="home-v3-service-icon"><i class="bi bi-house-gear-fill"></i></div>
                <h3>Aset & Rumah Tangga</h3>
                <p>Sistem inventarisasi modern dan pengelolaan sarpras.</p>
                <ul class="home-v3-service-list">
                    <li><i class="bi bi-check-circle-fill"></i> Inventarisasi & pelabelan</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pemeliharaan terjadwal</li>
                    <li><i class="bi bi-check-circle-fill"></i> Sarana prasarana</li>
                </ul>
                <span class="home-v3-service-link">Selengkapnya <i class="bi bi-arrow-right"></i></span>
            </a>

            <a href="<?= url('layanan/k3l') ?>" class="home-v3-service-card">
                <div class="home-v3-service-icon"><i class="bi bi-shield-fill-check"></i></div>
                <h3>Tim K3L</h3>
                <p>Lingkungan kampus yang aman, sehat, dan nyaman.</p>
                <ul class="home-v3-service-list">
                    <li><i class="bi bi-check-circle-fill"></i> Kebijakan & SOP K3</li>
                    <li><i class="bi bi-check-circle-fill"></i> Mitigasi bencana</li>
                    <li><i class="bi bi-check-circle-fill"></i> Lingkungan hijau</li>
                </ul>
                <span class="home-v3-service-link">Selengkapnya <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>

<!-- =====================================================
     TUPOKSI PREVIEW
     ===================================================== -->
<?php if (!empty($tupoksiPreview)): ?>
<section class="home-v3-tupoksi">
    <div class="container">
        <div style="text-align:center;max-width:700px;margin:0 auto;">
            <span class="home-v3-badge" style="background:rgba(26,82,118,.08);color:#1a5276;display:inline-flex;align-items:center;gap:.5rem;padding:.4rem 1rem;border-radius:50px;font-size:.78rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:1rem;">
                <i class="bi bi-clipboard-check-fill" style="color:#e67e22;"></i> Tupoksi
            </span>
            <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.7rem,3vw,2.5rem);font-weight:700;color:#0d2f4a;margin-bottom:.75rem;">Unit Kerja Kami</h2>
            <p style="color:#64748b;line-height:1.7;">Empat unit kerja utama BAUK dengan tugas pokok dan fungsi yang jelas</p>
        </div>

        <div class="home-v3-tupoksi-grid">
            <?php foreach ($tupoksiPreview as $t): 
                $warna = $t['warna'] ?? '#1a5276';
            ?>
            <a href="<?= url('tupoksi') ?>" class="home-v3-tp-card" style="text-decoration:none!important;color:inherit!important;">
                <div class="home-v3-tp-header" style="background:linear-gradient(135deg,<?= htmlspecialchars($warna) ?>,<?= htmlspecialchars($warna) ?>dd);">
                    <div class="home-v3-tp-icon"><i class="bi <?= htmlspecialchars($t['icon']) ?>"></i></div>
                    <?php if (!empty($t['kode_unit'])): ?>
                        <span class="home-v3-tp-kode"><?= htmlspecialchars($t['kode_unit']) ?></span>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($t['unit_kerja']) ?></h3>
                </div>
                <div class="home-v3-tp-body">
                    <p><?= htmlspecialchars(mb_substr($t['tugas_pokok'], 0, 120)) ?>...</p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:2.5rem;">
            <a href="<?= url('tupoksi') ?>" class="home-v3-cta-primary" style="display:inline-flex;">
                <i class="bi bi-clipboard-check"></i> Lihat Semua Tupoksi
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =====================================================
     CTA SECTION
     ===================================================== -->
<section style="position:relative;background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 100%);color:#fff;padding:5rem 0;overflow:hidden;">
    <div style="position:absolute;top:-50%;right:-10%;width:500px;height:500px;background:radial-gradient(circle,rgba(230,126,34,.18) 0%,transparent 70%);border-radius:50%;"></div>
    <div class="container" style="position:relative;z-index:1;text-align:center;max-width:800px;margin:0 auto;">
        <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.7rem,3vw,2.5rem);font-weight:700;margin-bottom:1rem;">Butuh Bantuan atau Informasi?</h2>
        <p style="color:rgba(255,255,255,.85);margin-bottom:2.5rem;line-height:1.8;">Tim kami siap membantu Anda. Hubungi kami melalui berbagai kanal berikut untuk konsultasi atau pertanyaan terkait layanan BAUK UNIMOF.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;margin-bottom:2.5rem;" class="homev3-cta-grid">
            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $cfg['contact_phone'] ?? '03822425464') ?>" style="background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:1.5rem;text-align:center;color:#fff!important;text-decoration:none!important;transition:all .3s;">
                <div style="width:56px;height:56px;background:#e67e22;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;font-size:1.4rem;"><i class="bi bi-telephone-fill"></i></div>
                <h5 style="margin-bottom:.35rem;font-size:1rem;">Telepon</h5>
                <p style="margin:0;font-size:.9rem;opacity:.9;"><?= htmlspecialchars($cfg['contact_phone'] ?? '(0382) 2425464') ?></p>
            </a>
            <a href="mailto:<?= htmlspecialchars($cfg['contact_email'] ?? 'bauk@unimof.ac.id') ?>" style="background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:1.5rem;text-align:center;color:#fff!important;text-decoration:none!important;transition:all .3s;">
                <div style="width:56px;height:56px;background:#e67e22;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;font-size:1.4rem;"><i class="bi bi-envelope-fill"></i></div>
                <h5 style="margin-bottom:.35rem;font-size:1rem;">Email</h5>
                <p style="margin:0;font-size:.9rem;opacity:.9;"><?= htmlspecialchars($cfg['contact_email'] ?? 'bauk@unimof.ac.id') ?></p>
            </a>
            <a href="<?= url('kontak') ?>" style="background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:1.5rem;text-align:center;color:#fff!important;text-decoration:none!important;transition:all .3s;">
                <div style="width:56px;height:56px;background:#e67e22;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;font-size:1.4rem;"><i class="bi bi-geo-alt-fill"></i></div>
                <h5 style="margin-bottom:.35rem;font-size:1rem;">Lokasi</h5>
                <p style="margin:0;font-size:.9rem;opacity:.9;">Maumere, NTT</p>
            </a>
        </div>

        <div style="display:flex;gap:.85rem;justify-content:center;flex-wrap:wrap;">
            <a href="<?= url('kontak') ?>" style="padding:.9rem 2rem;background:#fff;color:#0d2f4a!important;border-radius:12px;font-weight:700;text-decoration:none!important;display:inline-flex;align-items:center;gap:.5rem;"><i class="bi bi-chat-dots-fill"></i> Kirim Pesan</a>
            <a href="https://wa.me/6281234567890" target="_blank" style="padding:.9rem 2rem;background:transparent;border:2px solid rgba(255,255,255,.4);color:#fff!important;border-radius:12px;font-weight:700;text-decoration:none!important;display:inline-flex;align-items:center;gap:.5rem;"><i class="bi bi-whatsapp"></i> WhatsApp</a>
        </div>
    </div>
</section>

<!-- =====================================================
     COUNTER ANIMATION SCRIPT
     ===================================================== -->
<script>
(function() {
    'use strict';

    // Counter animation
    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-count'));
        if (isNaN(target) || target <= 0) { el.textContent = '0'; return; }
        var duration = 2200;
        var step = target / (duration / 16);
        var current = 0;
        var timer = setInterval(function() {
            current += step;
            if (current >= target) {
                el.textContent = target.toLocaleString('id-ID');
                clearInterval(timer);
            } else {
                el.textContent = Math.floor(current).toLocaleString('id-ID');
            }
        }, 16);
    }

    // Observer untuk semua elemen dengan class home-v3-count
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        document.querySelectorAll('.home-v3-count, .home-v3-stat-num[data-count]').forEach(function(el) {
            observer.observe(el);
        });
    } else {
        // Fallback: langsung tampilkan angka
        document.querySelectorAll('.home-v3-count, .home-v3-stat-num[data-count]').forEach(function(el) {
            el.textContent = parseInt(el.getAttribute('data-count')).toLocaleString('id-ID');
        });
    }
})();
</script>