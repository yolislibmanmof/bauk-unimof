<?php
/**
 * =====================================================
 * BAUK UNIMOF - TENTANG KAMI (ULTIMATE v2)
 * Profile • Sejarah • Struktur • Leadership
 * Semua data diambil dinamis dari settings & DB
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

// ===== STATISTIK DINAMIS =====
try {
    $stats = [
        'pegawai'    => $db->count('pegawai', "status = 'active' AND deleted_at IS NULL") ?: 150,
        'aset'       => $db->count('aset', "status = 'active' AND deleted_at IS NULL") ?: 2500,
        'departemen' => $db->count('departments', "status = 'active' AND deleted_at IS NULL") ?: 12,
        'regulasi'   => $db->count('dokumen_regulasi', "status = 'active'") ?: 45,
    ];
} catch (\Throwable $e) {
    $stats = ['pegawai' => 150, 'aset' => 2500, 'departemen' => 12, 'regulasi' => 45];
}

// ===== VISI MISI DINAMIS =====
$visi    = $cfg['visi_text'] ?? '';
$misiRaw = $cfg['misi_text'] ?? '';
$misi    = array_values(array_filter(array_map('trim', explode("\n", $misiRaw))));

// ===== STRUKTUR ORGANISASI DINAMIS =====
try {
    $struktur = $db->fetchAll("SELECT * FROM struktur_organisasi WHERE deleted_at IS NULL AND is_active = 1 ORDER BY level ASC, sort_order ASC, id ASC");
    $strukturByLevel = [];
    foreach ($struktur as $s) {
        $strukturByLevel[(int)$s['level']][] = $s;
    }
    ksort($strukturByLevel);
} catch (\Throwable $e) {
    $struktur = [];
    $strukturByLevel = [];
}

// ===== TUPOKSI DINAMIS (untuk preview di leadership) =====
try {
    $tupoksi = $db->fetchAll("SELECT * FROM tupoksi WHERE deleted_at IS NULL AND is_active = 1 ORDER BY sort_order ASC LIMIT 4");
} catch (\Throwable $e) { $tupoksi = []; }

// ===== HELPER FOTO =====
function aboutImg($key, $default = '') {
    global $cfg;
    return !empty($cfg[$key]) ? url('uploads/settings/' . $cfg[$key]) : $default;
}

// ===== YEAR CALC =====
$foundedYear = 2013;
$yearsActive = (int)date('Y') - $foundedYear;
?>

<!-- ===== ABOUT v2 STYLES ===== -->
<style>
/* ===== HERO SINEMATIK ===== */
.about-v2-hero{position:relative;min-height:88vh;display:flex;align-items:center;background:linear-gradient(135deg,#050f1e 0%,#0a2740 35%,#123c5e 70%,#1a5276 100%);color:#fff;overflow:hidden;padding:6rem 0 7rem;}
.about-v2-hero::before{content:'';position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120' viewBox='0 0 120 120'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Ccircle cx='60' cy='60' r='1.5'/%3E%3C/g%3E%3C/svg%3E");}
.about-v2-hero::after{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 70% 30%,rgba(230,126,34,.15) 0%,transparent 55%),radial-gradient(ellipse at 20% 80%,rgba(39,174,96,.12) 0%,transparent 55%);}
.about-v2-shape{position:absolute;border-radius:50%;filter:blur(60px);opacity:.4;z-index:1;}
.about-v2-shape-1{width:420px;height:420px;background:#e67e22;top:-100px;right:-80px;animation:about2_float 18s ease-in-out infinite;}
.about-v2-shape-2{width:340px;height:340px;background:#27ae60;bottom:-120px;left:-100px;animation:about2_float 22s ease-in-out infinite reverse;}
@keyframes about2_float{0%,100%{transform:translate(0,0) scale(1);}50%{transform:translate(40px,-40px) scale(1.1);}}
.about-v2-hero-content{position:relative;z-index:3;}
.about-v2-hero-grid{display:grid;grid-template-columns:1.3fr 1fr;gap:4rem;align-items:center;}
.about-v2-hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);padding:.5rem 1.2rem;border-radius:50px;font-size:.78rem;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;margin-bottom:1.5rem;}
.about-v2-hero-badge i{color:#f39c12;}
.about-v2-hero-title{font-family:'Playfair Display',serif;font-size:clamp(2.2rem,5.5vw,4rem);font-weight:800;line-height:1.08;margin-bottom:1.5rem;letter-spacing:-.5px;}
.about-v2-hero-title .about-v2-accent{color:#f39c12;position:relative;display:inline-block;}
.about-v2-hero-title .about-v2-accent::after{content:'';position:absolute;bottom:4px;left:0;right:0;height:8px;background:rgba(230,126,34,.25);z-index:-1;border-radius:4px;}
.about-v2-hero-desc{font-size:1.05rem;line-height:1.75;color:rgba(255,255,255,.88);margin-bottom:2rem;max-width:560px;}
.about-v2-hero-meta{display:flex;gap:1.75rem;flex-wrap:wrap;margin-bottom:2rem;}
.about-v2-meta-item{display:flex;align-items:center;gap:.5rem;font-size:.85rem;font-weight:600;color:rgba(255,255,255,.95);}
.about-v2-meta-item i{color:#f39c12;font-size:1.1rem;}
.about-v2-hero-cta{display:flex;gap:.85rem;flex-wrap:wrap;}
.about-v2-cta-primary{display:inline-flex;align-items:center;gap:.55rem;padding:.95rem 1.8rem;background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff!important;text-decoration:none!important;border-radius:14px;font-weight:700;font-size:.9rem;box-shadow:0 12px 32px rgba(230,126,34,.4);transition:all .3s;}
.about-v2-cta-primary:hover{transform:translateY(-3px);box-shadow:0 18px 42px rgba(230,126,34,.5);color:#fff!important;}
.about-v2-cta-secondary{display:inline-flex;align-items:center;gap:.55rem;padding:.95rem 1.8rem;background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1.5px solid rgba(255,255,255,.25);color:#fff!important;text-decoration:none!important;border-radius:14px;font-weight:700;font-size:.9rem;transition:all .3s;}
.about-v2-cta-secondary:hover{background:rgba(255,255,255,.18);border-color:#f39c12;color:#fff!important;}

/* Hero visual card */
.about-v2-visual{position:relative;}
.about-v2-visual-card{background:linear-gradient(145deg,rgba(255,255,255,.12),rgba(255,255,255,.04));backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.18);border-radius:28px;padding:2rem;box-shadow:0 30px 80px rgba(0,0,0,.35);position:relative;}
.about-v2-visual-card::before{content:'';position:absolute;top:-1px;left:20%;right:20%;height:2px;background:linear-gradient(90deg,transparent,#f39c12,transparent);}
.about-v2-stat-mini{display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;margin-bottom:1.5rem;}
.about-v2-stat-item{background:rgba(255,255,255,.06);padding:1.15rem;border-radius:14px;border:1px solid rgba(255,255,255,.1);}
.about-v2-stat-num{font-family:'Playfair Display',serif;font-size:2rem;font-weight:800;color:#f39c12;line-height:1;margin-bottom:.35rem;display:block;}
.about-v2-stat-lbl{font-size:.75rem;color:rgba(255,255,255,.8);font-weight:600;letter-spacing:.5px;text-transform:uppercase;}
.about-v2-mini-list{background:rgba(255,255,255,.06);border-radius:14px;padding:1.15rem;border:1px solid rgba(255,255,255,.1);}
.about-v2-mini-list h5{font-size:.85rem;font-weight:700;margin-bottom:.75rem;color:#fff;display:flex;align-items:center;gap:.5rem;}
.about-v2-mini-list h5 i{color:#f39c12;}
.about-v2-mini-item{display:flex;align-items:center;gap:.6rem;font-size:.8rem;color:rgba(255,255,255,.85);padding:.3rem 0;}
.about-v2-mini-item i{color:#2ecc71;font-size:.7rem;}

.about-v2-scroll{position:absolute;bottom:30px;left:50%;transform:translateX(-50%);z-index:5;color:rgba(255,255,255,.7);text-align:center;font-size:.72rem;letter-spacing:2.5px;text-transform:uppercase;cursor:pointer;}
.about-v2-scroll-mouse{width:22px;height:36px;border:2px solid rgba(255,255,255,.5);border-radius:11px;margin:0 auto .5rem;position:relative;}
.about-v2-scroll-mouse::before{content:'';position:absolute;top:6px;left:50%;transform:translateX(-50%);width:3px;height:7px;background:rgba(255,255,255,.9);border-radius:3px;animation:about2_wheel 2s infinite;}
@keyframes about2_wheel{0%{opacity:1;top:6px;}100%{opacity:0;top:18px;}}

/* ===== SECTION COMMON ===== */
.about-v2-section{padding:6rem 0;}
.about-v2-section-header{text-align:center;max-width:700px;margin:0 auto 3.5rem;}
.about-v2-section-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(26,82,118,.08);color:#1a5276;padding:.4rem 1rem;border-radius:50px;font-size:.78rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:1rem;}
.about-v2-section-badge i{color:#e67e22;}
.about-v2-section-title{font-family:'Playfair Display',serif;font-size:clamp(1.7rem,3vw,2.5rem);font-weight:800;color:#0d2f4a;margin-bottom:.75rem;line-height:1.2;}
.about-v2-section-title .about-v2-accent{color:#e67e22;}
.about-v2-section-sub{color:#64748b;line-height:1.7;}

/* ===== PROFILE / SAMBUTAN ===== */
.about-v2-profile{background:linear-gradient(180deg,#f8fafc,#fff);}
.about-v2-profile-grid{display:grid;grid-template-columns:400px 1fr;gap:4rem;align-items:center;}
.about-v2-profile-img{position:relative;}
.about-v2-profile-frame{position:relative;width:360px;height:440px;margin:0 auto;border-radius:24px;overflow:hidden;box-shadow:0 30px 70px rgba(13,47,74,.25);transform:rotate(-2deg);transition:transform .5s;}
.about-v2-profile-frame:hover{transform:rotate(0);}
.about-v2-profile-frame img{width:100%;height:100%;object-fit:cover;}
.about-v2-profile-frame::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(13,47,74,.35) 0%,transparent 45%);}
.about-v2-profile-deco{position:absolute;width:75px;height:75px;border-radius:50%;background:linear-gradient(135deg,#e67e22,#f39c12);display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:#fff;box-shadow:0 15px 35px rgba(230,126,34,.4);z-index:3;}
.about-v2-profile-deco-1{top:-25px;right:30px;}
.about-v2-profile-deco-2{bottom:-25px;left:30px;background:linear-gradient(135deg,#1a5276,#2980b9);box-shadow:0 15px 35px rgba(26,82,118,.4);}
.about-v2-profile-deco-text{font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:800;}

.about-v2-profile-body .about-v2-section-badge{margin-bottom:.75rem;}
.about-v2-profile-title{font-family:'Playfair Display',serif;font-size:clamp(1.6rem,2.8vw,2.2rem);font-weight:800;line-height:1.2;color:#0d2f4a;margin-bottom:1.25rem;}
.about-v2-profile-title .about-v2-accent{color:#e67e22;}
.about-v2-profile-quote{position:relative;padding:1rem 0 1rem 1.5rem;margin-bottom:1.25rem;border-left:4px solid #e67e22;}
.about-v2-profile-quote::before{content:'"';position:absolute;top:-10px;left:-5px;font-family:'Playfair Display',serif;font-size:5rem;color:#e67e22;opacity:.15;line-height:1;}
.about-v2-profile-quote p{font-size:1rem;line-height:1.85;color:#475569;font-style:italic;margin:0;}
.about-v2-profile-signature{display:flex;align-items:center;gap:1.25rem;padding-top:1.5rem;border-top:2px solid rgba(13,47,74,.08);margin-top:1.5rem;}
.about-v2-name-avatar{width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#1a5276,#2980b9);color:#fff;display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;flex-shrink:0;}
.about-v2-name-text strong{display:block;font-size:1.05rem;font-weight:700;color:#0d2f4a;}
.about-v2-name-text span{font-size:.85rem;color:#64748b;}
.about-v2-name-sign{margin-left:auto;font-family:'Brush Script MT',cursive;font-size:1.8rem;color:#1a5276;font-weight:700;}

.about-v2-highlights{list-style:none;padding:0;margin:1.5rem 0 0;display:grid;gap:.6rem;}
.about-v2-highlights li{display:flex;align-items:center;gap:.7rem;padding:.7rem 1rem;background:#fff;border:1px solid rgba(13,47,74,.06);border-radius:12px;border-left:4px solid #1a5276;transition:all .3s;}
.about-v2-highlights li:hover{transform:translateX(4px);border-left-color:#e67e22;box-shadow:0 8px 24px rgba(13,47,74,.08);}
.about-v2-highlights li i{color:#1a5276;font-size:1.1rem;flex-shrink:0;}
.about-v2-highlights li strong{display:block;font-size:.88rem;color:#0d2f4a;}
.about-v2-highlights li span{font-size:.78rem;color:#64748b;}

/* ===== VISION MISSION VALUES ===== */
.about-v2-vmv{background:#fff;}
.about-v2-vmv-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.75rem;}
.about-v2-vmv-card{background:#fff;padding:2.5rem 2rem;border-radius:22px;box-shadow:0 10px 40px rgba(13,47,74,.08);position:relative;overflow:hidden;transition:all .4s;border:1px solid rgba(13,47,74,.06);min-height:420px;}
.about-v2-vmv-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#1a5276,#e67e22);transform:scaleX(0);transform-origin:left;transition:transform .4s;}
.about-v2-vmv-card:hover{transform:translateY(-8px);box-shadow:0 20px 50px rgba(13,47,74,.15);}
.about-v2-vmv-card:hover::before{transform:scaleX(1);}
.about-v2-vmv-icon{width:80px;height:80px;border-radius:22px;display:flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:1.5rem;color:#fff;position:relative;}
.about-v2-vmv-icon::after{content:'';position:absolute;inset:-5px;border:2px dashed currentColor;opacity:.2;border-radius:22px;}
.about-v2-vmv-card:nth-child(1) .about-v2-vmv-icon{background:linear-gradient(135deg,#1a5276,#2980b9);box-shadow:0 10px 30px rgba(26,82,118,.3);}
.about-v2-vmv-card:nth-child(2) .about-v2-vmv-icon{background:linear-gradient(135deg,#e67e22,#f39c12);box-shadow:0 10px 30px rgba(230,126,34,.3);}
.about-v2-vmv-card:nth-child(3) .about-v2-vmv-icon{background:linear-gradient(135deg,#27ae60,#2ecc71);box-shadow:0 10px 30px rgba(39,174,96,.3);}
.about-v2-vmv-card h3{font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:1rem;color:#0d2f4a;}
.about-v2-vmv-card > p{color:#475569;line-height:1.8;margin-bottom:1.25rem;font-size:.92rem;}
.about-v2-vmv-list{list-style:none;padding:0;margin:0;}
.about-v2-vmv-list li{display:flex;align-items:flex-start;gap:.65rem;padding:.6rem 0;color:#475569;font-size:.88rem;border-bottom:1px dashed rgba(13,47,74,.08);line-height:1.5;}
.about-v2-vmv-list li:last-child{border-bottom:none;}
.about-v2-vmv-list li i{color:#10b981;flex-shrink:0;margin-top:.15rem;}

/* ===== TIMELINE ===== */
.about-v2-timeline{background:linear-gradient(180deg,#f8fafc,#fff);}
.about-v2-timeline-wrap{position:relative;max-width:1000px;margin:3rem auto 0;padding:2rem 0;}
.about-v2-timeline-wrap::before{content:'';position:absolute;left:50%;top:0;bottom:0;width:3px;background:linear-gradient(180deg,#1a5276,#e67e22,#27ae60);transform:translateX(-50%);}
.about-v2-tl-item{position:relative;margin-bottom:3rem;display:flex;align-items:center;animation:about2_fadeUp .6s ease backwards;}
.about-v2-tl-item:nth-child(1){animation-delay:.1s;}
.about-v2-tl-item:nth-child(2){animation-delay:.2s;}
.about-v2-tl-item:nth-child(3){animation-delay:.3s;}
.about-v2-tl-item:nth-child(4){animation-delay:.4s;}
.about-v2-tl-item:nth-child(5){animation-delay:.5s;}
.about-v2-tl-item:nth-child(6){animation-delay:.6s;}
@keyframes about2_fadeUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
.about-v2-tl-item:nth-child(odd){flex-direction:row-reverse;}
.about-v2-tl-content{width:50%;background:#fff;padding:1.75rem;border-radius:16px;box-shadow:0 10px 30px rgba(13,47,74,.08);border:1px solid rgba(13,47,74,.06);transition:all .3s;position:relative;}
.about-v2-tl-content:hover{transform:translateY(-4px);box-shadow:0 15px 40px rgba(13,47,74,.12);border-color:#e67e22;}
.about-v2-tl-item:nth-child(odd) .about-v2-tl-content{margin-right:3rem;text-align:right;}
.about-v2-tl-item:nth-child(even) .about-v2-tl-content{margin-left:3rem;}
.about-v2-tl-dot{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:60px;height:60px;border-radius:50%;background:#fff;border:4px solid #1a5276;display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:#1a5276;z-index:2;box-shadow:0 5px 15px rgba(0,0,0,.1);transition:all .3s;}
.about-v2-tl-item:hover .about-v2-tl-dot{background:#e67e22;border-color:#e67e22;color:#fff;transform:translate(-50%,-50%) scale(1.1);}
.about-v2-tl-year{display:inline-block;background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;padding:.3rem 1rem;border-radius:50px;font-weight:700;font-size:.82rem;margin-bottom:.6rem;letter-spacing:.5px;}
.about-v2-tl-content h4{font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:700;color:#0d2f4a;margin-bottom:.5rem;}
.about-v2-tl-content p{color:#64748b;font-size:.88rem;line-height:1.7;margin:0;}

/* ===== STRUKTUR ORGANISASI ===== */
.about-v2-struktur{background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 50%,#2980b9 100%);color:#fff;position:relative;overflow:hidden;}
.about-v2-struktur::before{content:'';position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='1.5'/%3E%3C/g%3E%3C/svg%3E");}
.about-v2-struktur .about-v2-section-badge{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2);}
.about-v2-struktur .about-v2-section-title{color:#fff;}
.about-v2-struktur .about-v2-section-sub{color:rgba(255,255,255,.75);}
.about-v2-struktur-content{position:relative;z-index:2;}
.about-v2-org-chart{margin-top:3rem;display:flex;flex-direction:column;align-items:center;gap:0;}
.about-v2-org-level{display:flex;justify-content:center;gap:1.5rem;flex-wrap:wrap;position:relative;padding:1rem 0;}
.about-v2-org-connector{width:2px;height:2rem;background:linear-gradient(180deg,rgba(255,255,255,.5),rgba(255,255,255,.15));margin:0 auto;}
.about-v2-org-card{background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.2);border-radius:16px;padding:1.5rem 1.75rem;text-align:center;min-width:210px;max-width:250px;transition:all .35s;position:relative;overflow:hidden;}
.about-v2-org-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#f39c12,#e67e22);transform:scaleX(0);transition:transform .3s;}
.about-v2-org-card:hover{background:rgba(255,255,255,.15);transform:translateY(-5px);box-shadow:0 20px 50px rgba(0,0,0,.3);}
.about-v2-org-card:hover::before{transform:scaleX(1);}
.about-v2-org-card.leader{background:linear-gradient(135deg,rgba(230,126,34,.25),rgba(243,156,18,.25));border-color:rgba(243,156,18,.5);min-width:280px;padding:2rem;}
.about-v2-org-card.leader:hover{box-shadow:0 20px 50px rgba(230,126,34,.35);}
.about-v2-org-avatar{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#f39c12,#e67e22);display:flex;align-items:center;justify-content:center;margin:0 auto .85rem;font-family:'Playfair Display',serif;font-size:1.6rem;color:#fff;font-weight:700;border:3px solid rgba(255,255,255,.25);overflow:hidden;}
.about-v2-org-card.leader .about-v2-org-avatar{width:90px;height:90px;font-size:2rem;}
.about-v2-org-avatar img{width:100%;height:100%;object-fit:cover;}
.about-v2-org-jabatan{font-size:.72rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;color:#f39c12;margin-bottom:.4rem;}
.about-v2-org-card.leader .about-v2-org-jabatan{color:#fbbf24;font-size:.75rem;}
.about-v2-org-nama{font-size:1rem;font-weight:700;margin-bottom:.2rem;color:#fff;}
.about-v2-org-card.leader .about-v2-org-nama{font-size:1.15rem;}
.about-v2-org-nip{font-size:.75rem;color:rgba(255,255,255,.7);font-family:monospace;}

/* ===== CORE VALUES ===== */
.about-v2-values{background:#fff;}
.about-v2-values-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;}
.about-v2-value-card{background:#fff;padding:2rem 1.5rem;border-radius:16px;text-align:center;transition:all .4s;box-shadow:0 4px 20px rgba(13,47,74,.06);border:1px solid rgba(13,47,74,.06);position:relative;overflow:hidden;}
.about-v2-value-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,#1a5276,#e67e22);opacity:0;transition:opacity .35s;z-index:0;}
.about-v2-value-card:hover{transform:translateY(-6px);box-shadow:0 15px 40px rgba(13,47,74,.15);}
.about-v2-value-card:hover::before{opacity:1;}
.about-v2-value-card:hover *{color:#fff!important;position:relative;z-index:1;}
.about-v2-value-card:hover .about-v2-value-icon{background:rgba(255,255,255,.2)!important;color:#fff!important;border-color:rgba(255,255,255,.3)!important;}
.about-v2-value-icon{width:64px;height:64px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto 1rem;background:linear-gradient(135deg,#e6f0fa,#d4e4f5);color:#1a5276;position:relative;z-index:1;transition:all .35s;border:1px solid rgba(26,82,118,.08);}
.about-v2-value-card h4{font-size:1.05rem;font-weight:700;margin-bottom:.5rem;color:#0d2f4a;position:relative;z-index:1;}
.about-v2-value-card p{font-size:.82rem;color:#64748b;line-height:1.6;margin:0;position:relative;z-index:1;}

/* ===== CERTIFICATIONS ===== */
.about-v2-certs{background:linear-gradient(180deg,#f8fafc,#fff);}
.about-v2-certs-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem;}
.about-v2-cert-card{background:#fff;border-radius:16px;padding:1.75rem;display:flex;gap:1rem;align-items:flex-start;box-shadow:0 4px 20px rgba(13,47,74,.06);border:1px solid rgba(13,47,74,.06);transition:all .35s;position:relative;overflow:hidden;}
.about-v2-cert-card::after{content:'';position:absolute;top:0;right:0;width:50px;height:50px;background:linear-gradient(135deg,transparent 50%,rgba(16,185,129,.15) 50%);border-radius:0 16px 0 0;}
.about-v2-cert-card:hover{transform:translateY(-6px);box-shadow:0 15px 40px rgba(13,47,74,.12);border-color:#10b981;}
.about-v2-cert-icon{width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;color:#fff;}
.about-v2-cert-card:nth-child(1) .about-v2-cert-icon{background:linear-gradient(135deg,#fbbf24,#f59e0b);box-shadow:0 8px 20px rgba(245,158,11,.3);}
.about-v2-cert-card:nth-child(2) .about-v2-cert-icon{background:linear-gradient(135deg,#3b82f6,#2563eb);box-shadow:0 8px 20px rgba(59,130,246,.3);}
.about-v2-cert-card:nth-child(3) .about-v2-cert-icon{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 20px rgba(16,185,129,.3);}
.about-v2-cert-card:nth-child(4) .about-v2-cert-icon{background:linear-gradient(135deg,#8b5cf6,#7c3aed);box-shadow:0 8px 20px rgba(139,92,246,.3);}
.about-v2-cert-info{flex:1;}
.about-v2-cert-info h4{font-size:1rem;font-weight:700;margin-bottom:.25rem;color:#0d2f4a;}
.about-v2-cert-info p{font-size:.82rem;color:#64748b;line-height:1.6;margin:0 0 .5rem;}
.about-v2-cert-number{display:inline-block;font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:700;color:#e67e22;}

/* ===== GALLERY ===== */
.about-v2-gallery{background:#fff;}
.about-v2-gallery-tabs{display:flex;justify-content:center;gap:.5rem;margin-bottom:2.5rem;flex-wrap:wrap;}
.about-v2-gallery-tab{padding:.55rem 1.2rem;border-radius:50px;border:1.5px solid rgba(13,47,74,.1);background:#fff;color:#475569;font-weight:600;font-size:.82rem;cursor:pointer;transition:all .25s;display:inline-flex;align-items:center;gap:.4rem;}
.about-v2-gallery-tab:hover{border-color:#1a5276;color:#1a5276;}
.about-v2-gallery-tab.active{background:#1a5276;border-color:#1a5276;color:#fff;}
.about-v2-gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;}
.about-v2-gallery-item{position:relative;border-radius:16px;overflow:hidden;aspect-ratio:4/3;cursor:pointer;box-shadow:0 10px 30px rgba(13,47,74,.1);transition:all .4s;}
.about-v2-gallery-item:hover{transform:translateY(-6px);box-shadow:0 20px 50px rgba(13,47,74,.2);}
.about-v2-gallery-ph{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;color:#fff;transition:transform .5s;}
.about-v2-gallery-item:hover .about-v2-gallery-ph{transform:scale(1.1);}
.about-v2-gallery-item[data-cat="rapat"] .about-v2-gallery-ph{background:linear-gradient(135deg,#1a5276,#2980b9);}
.about-v2-gallery-item[data-cat="sertifikasi"] .about-v2-gallery-ph{background:linear-gradient(135deg,#e67e22,#f39c12);}
.about-v2-gallery-item[data-cat="digital"] .about-v2-gallery-ph{background:linear-gradient(135deg,#27ae60,#2ecc71);}
.about-v2-gallery-item[data-cat="lingkungan"] .about-v2-gallery-ph{background:linear-gradient(135deg,#16a085,#1abc9c);}
.about-v2-gallery-item[data-cat="sdm"] .about-v2-gallery-ph{background:linear-gradient(135deg,#8e44ad,#9b59b6);}
.about-v2-gallery-item[data-cat="audit"] .about-v2-gallery-ph{background:linear-gradient(135deg,#c0392b,#e74c3c);}
.about-v2-gallery-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(13,47,74,.95) 0%,rgba(13,47,74,.3) 40%,transparent 70%);display:flex;align-items:flex-end;padding:1.5rem;opacity:0;transition:opacity .3s;}
.about-v2-gallery-item:hover .about-v2-gallery-overlay{opacity:1;}
.about-v2-gallery-overlay h5{color:#fff;font-size:1rem;margin-bottom:.25rem;font-family:'Playfair Display',serif;}
.about-v2-gallery-overlay p{color:rgba(255,255,255,.85);font-size:.78rem;margin:0;}
.about-v2-gallery-overlay .about-v2-gallery-view{position:absolute;top:1rem;right:1rem;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.2);backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;}

/* ===== LEADERSHIP ===== */
.about-v2-leadership{background:linear-gradient(180deg,#f8fafc,#fff);}
.about-v2-leader-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.75rem;}
.about-v2-leader-card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 10px 30px rgba(13,47,74,.08);transition:all .35s;border:1px solid rgba(13,47,74,.06);}
.about-v2-leader-card:hover{transform:translateY(-8px);box-shadow:0 25px 60px rgba(13,47,74,.15);border-color:#e67e22;}
.about-v2-leader-image{aspect-ratio:1;background:linear-gradient(135deg,#e6f0fa,#d4e4f5);display:flex;align-items:center;justify-content:center;font-size:5rem;color:#1a5276;position:relative;overflow:hidden;}
.about-v2-leader-image img{width:100%;height:100%;object-fit:cover;}
.about-v2-leader-image::after{content:'';position:absolute;bottom:0;left:0;right:0;height:60px;background:linear-gradient(to top,#fff,transparent);}
.about-v2-leader-info{padding:1.5rem;text-align:center;position:relative;}
.about-v2-leader-info h4{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:#0d2f4a;margin-bottom:.25rem;}
.about-v2-leader-info p{font-size:.82rem;color:#e67e22;font-weight:700;margin-bottom:.75rem;text-transform:uppercase;letter-spacing:.5px;}
.about-v2-leader-info small{font-size:.78rem;color:#64748b;display:block;margin-bottom:1rem;line-height:1.5;}
.about-v2-leader-social{display:flex;justify-content:center;gap:.5rem;}
.about-v2-leader-social a{width:36px;height:36px;border-radius:50%;background:rgba(26,82,118,.08);color:#1a5276;display:flex;align-items:center;justify-content:center;transition:all .25s;text-decoration:none!important;}
.about-v2-leader-social a:hover{background:#e67e22;color:#fff;transform:translateY(-2px);}

/* ===== CTA ===== */
.about-v2-cta{background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 100%);color:#fff;padding:5rem 0;position:relative;overflow:hidden;}
.about-v2-cta::before{content:'';position:absolute;top:-50%;right:-10%;width:500px;height:500px;background:radial-gradient(circle,rgba(230,126,34,.18) 0%,transparent 70%);border-radius:50%;}
.about-v2-cta-content{position:relative;z-index:1;text-align:center;max-width:800px;margin:0 auto;}
.about-v2-cta h2{font-family:'Playfair Display',serif;font-size:clamp(1.7rem,3vw,2.5rem);font-weight:800;margin-bottom:1rem;color:#fff;}
.about-v2-cta p{color:rgba(255,255,255,.85);margin-bottom:2.5rem;line-height:1.8;}
.about-v2-cta-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:2.5rem;}
.about-v2-cta-card{background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:1.5rem;text-align:center;color:#fff!important;text-decoration:none!important;transition:all .3s;}
.about-v2-cta-card:hover{background:rgba(255,255,255,.18);transform:translateY(-4px);border-color:#f39c12;color:#fff!important;}
.about-v2-cta-icon{width:56px;height:56px;background:linear-gradient(135deg,#e67e22,#f39c12);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .85rem;font-size:1.4rem;box-shadow:0 6px 15px rgba(230,126,34,.35);}
.about-v2-cta-card h5{font-size:.95rem;margin-bottom:.3rem;font-weight:700;}
.about-v2-cta-card p{font-size:.85rem;margin:0;opacity:.9;}
.about-v2-cta-actions{display:flex;gap:.85rem;justify-content:center;flex-wrap:wrap;}
.about-v2-cta-btn{padding:.9rem 2rem;border-radius:14px;font-weight:700;font-size:.9rem;display:inline-flex;align-items:center;gap:.5rem;text-decoration:none!important;transition:all .3s;}
.about-v2-cta-btn-primary{background:#fff;color:#0d2f4a!important;}
.about-v2-cta-btn-primary:hover{background:#e67e22;color:#fff!important;transform:translateY(-3px);box-shadow:0 12px 30px rgba(230,126,34,.4);}
.about-v2-cta-btn-outline{background:transparent;color:#fff!important;border:2px solid rgba(255,255,255,.4);}
.about-v2-cta-btn-outline:hover{background:rgba(255,255,255,.1);border-color:#fff;transform:translateY(-3px);color:#fff!important;}

/* ===== RESPONSIVE ===== */
@media (max-width:1100px){
    .about-v2-hero-grid{grid-template-columns:1fr;gap:3rem;}
    .about-v2-visual{order:-1;max-width:500px;margin:0 auto;}
    .about-v2-profile-grid{grid-template-columns:1fr;gap:3rem;}
    .about-v2-profile-img{order:-1;max-width:400px;margin:0 auto;}
}
@media (max-width:992px){
    .about-v2-vmv-grid{grid-template-columns:1fr;}
    .about-v2-timeline-wrap::before{left:30px;}
    .about-v2-tl-item,.about-v2-tl-item:nth-child(odd){flex-direction:row;}
    .about-v2-tl-content{width:100%!important;margin:0 0 0 5rem!important;text-align:left!important;}
    .about-v2-tl-dot{left:30px!important;}
    .about-v2-org-level{flex-direction:column;align-items:center;}
}
@media (max-width:640px){
    .about-v2-hero{padding:4rem 0 5rem;min-height:auto;}
    .about-v2-section{padding:4rem 0;}
    .about-v2-cta-grid{grid-template-columns:1fr;}
    .about-v2-profile-frame{width:280px;height:340px;}
}
</style>

<!-- ===== HERO SINEMATIK ===== -->
<section class="about-v2-hero">
    <div class="about-v2-shape about-v2-shape-1"></div>
    <div class="about-v2-shape about-v2-shape-2"></div>
    <div class="container about-v2-hero-content">
        <div class="about-v2-hero-grid">
            <div>
                <span class="about-v2-hero-badge"><i class="bi bi-building-fill"></i> Profil Instansi</span>
                <h1 class="about-v2-hero-title">
                    Tentang<br>
                    <span class="about-v2-accent">BAUK UNIMOF</span>
                </h1>
                <p class="about-v2-hero-desc">
                    Biro Administrasi Umum dan Keuangan — pilar pendukung utama Universitas Muhammadiyah Maumere dalam mewujudkan tata kelola universitas yang <strong>profesional</strong>, <strong>transparan</strong>, dan <strong>akuntabel</strong>.
                </p>
                <div class="about-v2-hero-meta">
                    <div class="about-v2-meta-item"><i class="bi bi-calendar-check-fill"></i> Berdiri Sejak <?= $foundedYear ?></div>
                    <div class="about-v2-meta-item"><i class="bi bi-award-fill"></i> Akreditasi Unggul</div>
                    <div class="about-v2-meta-item"><i class="bi bi-shield-fill-check"></i> ISO 9001:2015</div>
                </div>
                <div class="about-v2-hero-cta">
                    <a href="<?= url('struktur-organisasi') ?>" class="about-v2-cta-primary">
                        <i class="bi bi-diagram-3-fill"></i> Struktur Organisasi
                    </a>
                    <a href="<?= url('visi-misi') ?>" class="about-v2-cta-secondary">
                        <i class="bi bi-compass-fill"></i> Visi & Misi
                    </a>
                </div>
            </div>

            <!-- VISUAL CARD -->
            <div class="about-v2-visual">
                <div class="about-v2-visual-card">
                    <div class="about-v2-stat-mini">
                        <div class="about-v2-stat-item">
                            <span class="about-v2-stat-num about-v2-count" data-count="<?= $stats['pegawai'] ?>">0</span>
                            <span class="about-v2-stat-lbl">Pegawai Aktif</span>
                        </div>
                        <div class="about-v2-stat-item">
                            <span class="about-v2-stat-num about-v2-count" data-count="<?= $stats['aset'] ?>">0</span>
                            <span class="about-v2-stat-lbl">Aset Terkelola</span>
                        </div>
                        <div class="about-v2-stat-item">
                            <span class="about-v2-stat-num about-v2-count" data-count="<?= $stats['departemen'] ?>">0</span>
                            <span class="about-v2-stat-lbl">Unit Kerja</span>
                        </div>
                        <div class="about-v2-stat-item">
                            <span class="about-v2-stat-num about-v2-count" data-count="<?= $stats['regulasi'] ?>">0</span>
                            <span class="about-v2-stat-lbl">Regulasi Aktif</span>
                        </div>
                    </div>
                    <div class="about-v2-mini-list">
                        <h5><i class="bi bi-stars"></i> Pencapaian Kami</h5>
                        <div class="about-v2-mini-item"><i class="bi bi-check-circle-fill"></i> <?= $yearsActive ?>+ Tahun Pengabdian</div>
                        <div class="about-v2-mini-item"><i class="bi bi-check-circle-fill"></i> 100% Layanan Terdigitalisasi</div>
                        <div class="about-v2-mini-item"><i class="bi bi-check-circle-fill"></i> Best Governance Award 2025</div>
                        <div class="about-v2-mini-item"><i class="bi bi-check-circle-fill"></i> Green Campus Gold 2024</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="about-v2-scroll" onclick="document.getElementById('about2Profile').scrollIntoView({behavior:'smooth'})">
        <div class="about-v2-scroll-mouse"></div>
        <span>Scroll</span>
    </div>
</section>

<!-- ===== PROFILE / SAMBUTAN ===== -->
<section class="about-v2-section about-v2-profile" id="about2Profile">
    <div class="container">
        <div class="about-v2-profile-grid">
            <div class="about-v2-profile-img">
                <div class="about-v2-profile-deco about-v2-profile-deco-1">
                    <span class="about-v2-profile-deco-text"><?= $yearsActive ?>+</span>
                </div>
                <div class="about-v2-profile-frame">
                    <img src="<?= aboutImg('kepala_bauk_photo', asset('images/user-placeholder.png')) ?>" alt="Kepala BAUK UNIMOF">
                </div>
                <div class="about-v2-profile-deco about-v2-profile-deco-2">
                    <i class="bi bi-award-fill"></i>
                </div>
            </div>

            <div class="about-v2-profile-body">
                <span class="about-v2-section-badge"><i class="bi bi-quote"></i> Sambutan</span>
                <h2 class="about-v2-profile-title">Mengenal Lebih Dekat <span class="about-v2-accent">BAUK UNIMOF</span></h2>

                <div class="about-v2-profile-quote">
                    <p>Biro Administrasi Umum dan Keuangan (BAUK) adalah unit pendukung strategis yang bertanggung jawab dalam pengelolaan administrasi umum, keuangan, kepegawaian, aset, dan sarana prasarana di lingkungan Universitas Muhammadiyah Maumere. Sebagai tulang punggung operasional universitas, kami berkomitmen mewujudkan pelayanan yang inovatif, transparan, efisien, dan terintegrasi.</p>
                </div>

                <p style="color:#475569;line-height:1.8;font-size:.92rem;">Kami percaya bahwa pelayanan modern berbasis teknologi informasi merupakan langkah strategis menciptakan <em>Good University Governance</em>. BAUK UNIMOF akan terus beradaptasi dan berinovasi.</p>

                <div class="about-v2-profile-signature">
                    <div class="about-v2-name-avatar">
                        <?php
                            $kepalaName = $cfg['kepala_bauk_name'] ?? 'Kepala BAUK';
                            echo strtoupper(substr($kepalaName, 0, 1));
                        ?>
                    </div>
                    <div class="about-v2-name-text">
                        <strong><?= htmlspecialchars($cfg['kepala_bauk_name'] ?? 'Kepala BAUK') ?></strong>
                        <span><?= htmlspecialchars($cfg['kepala_bauk_title'] ?? 'Kepala Biro Administrasi Umum & Keuangan') ?></span>
                    </div>
                    <div class="about-v2-name-sign">Kepala BAUK</div>
                </div>

                <ul class="about-v2-highlights">
                    <li>
                        <i class="bi bi-patch-check-fill"></i>
                        <div><strong>Terakreditasi Unggul</strong><span>BAN-PT dengan predikat Unggul sejak 2019</span></div>
                    </li>
                    <li>
                        <i class="bi bi-shield-fill-check"></i>
                        <div><strong>Sertifikasi ISO 9001:2015</strong><span>Sistem Manajemen Mutu terstandar internasional</span></div>
                    </li>
                    <li>
                        <i class="bi bi-graph-up-arrow"></i>
                        <div><strong>Transformasi Digital</strong><span>100% layanan telah terdigitalisasi sejak 2023</span></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ===== VISI MISI NILAI ===== -->
<section class="about-v2-section about-v2-vmv">
    <div class="container">
        <div class="about-v2-section-header">
            <span class="about-v2-section-badge"><i class="bi bi-compass-fill"></i> Arah Kami</span>
            <h2 class="about-v2-section-title">Visi, Misi & <span class="about-v2-accent">Nilai Inti</span></h2>
            <p class="about-v2-section-sub">Landasan strategis dalam setiap langkah pelayanan kami untuk mendukung kemajuan UNIMOF</p>
        </div>

        <div class="about-v2-vmv-grid">
            <!-- VISI -->
            <div class="about-v2-vmv-card">
                <div class="about-v2-vmv-icon"><i class="bi bi-eye-fill"></i></div>
                <h3>Visi</h3>
                <?php if ($visi): ?>
                    <p><?= htmlspecialchars($visi) ?></p>
                <?php else: ?>
                    <p>Menjadi biro pelayanan prima yang profesional, transparan, dan akuntabel dalam mendukung kemajuan UNIMOF menuju universitas berdaya saing global.</p>
                <?php endif; ?>
                <ul class="about-v2-vmv-list">
                    <li><i class="bi bi-check-circle-fill"></i> Pelayanan Prima</li>
                    <li><i class="bi bi-check-circle-fill"></i> Profesional & Kompeten</li>
                    <li><i class="bi bi-check-circle-fill"></i> Transparansi Penuh</li>
                    <li><i class="bi bi-check-circle-fill"></i> Akuntabilitas Tinggi</li>
                </ul>
            </div>

            <!-- MISI -->
            <div class="about-v2-vmv-card">
                <div class="about-v2-vmv-icon"><i class="bi bi-bullseye"></i></div>
                <h3>Misi</h3>
                <p>Menyelenggarakan administrasi umum, keuangan, kepegawaian, dan aset yang tertib, efisien, dan berbasis teknologi informasi modern.</p>
                <?php if (!empty($misi)): ?>
                <ul class="about-v2-vmv-list">
                    <?php foreach (array_slice($misi, 0, 4) as $m): ?>
                        <li><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars(mb_substr($m, 0, 80)) ?><?= mb_strlen($m) > 80 ? '...' : '' ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <ul class="about-v2-vmv-list">
                    <li><i class="bi bi-check-circle-fill"></i> Administrasi Tertib & Rapi</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pengelolaan Keuangan Efisien</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pengembangan SDM Berkelanjutan</li>
                    <li><i class="bi bi-check-circle-fill"></i> Digitalisasi Layanan Penuh</li>
                </ul>
                <?php endif; ?>
            </div>

            <!-- NILAI -->
            <div class="about-v2-vmv-card">
                <div class="about-v2-vmv-icon"><i class="bi bi-gem"></i></div>
                <h3>Nilai Inti</h3>
                <p>Amanah, transparan, profesional, melayani, dan berkelanjutan sesuai nilai-nilai Al-Islam dan Kemuhammadiyahan yang luhur.</p>
                <ul class="about-v2-vmv-list">
                    <li><i class="bi bi-check-circle-fill"></i> <strong>A</strong>manah & Terpercaya</li>
                    <li><i class="bi bi-check-circle-fill"></i> <strong>T</strong>ransparan & Terbuka</li>
                    <li><i class="bi bi-check-circle-fill"></i> <strong>A</strong>hli & Profesional</li>
                    <li><i class="bi bi-check-circle-fill"></i> <strong>N</strong>iat Melayani Tulus</li>
                </ul>
            </div>
        </div>

        <div style="text-align:center;margin-top:2.5rem;">
            <a href="<?= url('visi-misi') ?>" class="about-v2-cta-primary" style="display:inline-flex;">
                <i class="bi bi-arrow-right-circle-fill"></i> Lihat Visi & Misi Lengkap
            </a>
        </div>
    </div>
</section>

<!-- ===== TIMELINE PERJALANAN ===== -->
<section class="about-v2-section about-v2-timeline">
    <div class="container">
        <div class="about-v2-section-header">
            <span class="about-v2-section-badge"><i class="bi bi-clock-history"></i> Sejarah</span>
            <h2 class="about-v2-section-title">Perjalanan <span class="about-v2-accent">BAUK UNIMOF</span></h2>
            <p class="about-v2-section-sub">Milestone penting dalam transformasi layanan administrasi selama <?= $yearsActive ?>+ tahun</p>
        </div>

        <div class="about-v2-timeline-wrap">
            <?php
            $milestones = [
                ['year' => $foundedYear, 'title' => 'Pendirian BAUK', 'desc' => 'BAUK didirikan sebagai unit pendukung administrasi umum dan keuangan saat Universitas Muhammadiyah Maumere resmi berdiri.', 'icon' => 'flag-fill'],
                ['year' => 2005, 'title' => 'Restrukturisasi Organisasi', 'desc' => 'Penataan struktur organisasi menjadi 4 bagian utama: Kepegawaian, Keuangan, Aset, dan K3L untuk pelayanan yang lebih fokus.', 'icon' => 'building-fill-gear'],
                ['year' => 2012, 'title' => 'Digitalisasi Tahap Awal', 'desc' => 'Penerapan sistem informasi keuangan dan kepegawaian berbasis desktop untuk mempercepat proses administrasi.', 'icon' => 'laptop-fill'],
                ['year' => 2018, 'title' => 'Sertifikasi ISO 9001:2015', 'desc' => 'BAUK berhasil meraih sertifikasi ISO 9001:2015 untuk Sistem Manajemen Mutu, menandai komitmen kualitas layanan.', 'icon' => 'award-fill'],
                ['year' => 2021, 'title' => 'Transformasi Digital Penuh', 'desc' => 'Migrasi ke sistem berbasis cloud dan peluncuran portal layanan online untuk seluruh civitas akademika.', 'icon' => 'cloud-fill'],
                ['year' => 2026, 'title' => 'Era BAUK ULTIMATE', 'desc' => 'Peluncuran sistem informasi terintegrasi dengan fitur AI-powered analytics, mobile-first, dan real-time monitoring.', 'icon' => 'rocket-takeoff-fill'],
            ];
            foreach ($milestones as $m): ?>
            <div class="about-v2-tl-item">
                <div class="about-v2-tl-dot"><i class="bi bi-<?= $m['icon'] ?>"></i></div>
                <div class="about-v2-tl-content">
                    <span class="about-v2-tl-year"><?= $m['year'] ?></span>
                    <h4><?= htmlspecialchars($m['title']) ?></h4>
                    <p><?= htmlspecialchars($m['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== STRUKTUR ORGANISASI ===== -->
<section class="about-v2-section about-v2-struktur">
    <div class="container about-v2-struktur-content">
        <div class="about-v2-section-header">
            <span class="about-v2-section-badge"><i class="bi bi-diagram-3-fill"></i> Organisasi</span>
            <h2 class="about-v2-section-title">Struktur <span class="about-v2-accent">Organisasi</span></h2>
            <p class="about-v2-section-sub">Susunan kepemimpinan dan pembagian tugas yang jelas dalam BAUK UNIMOF</p>
        </div>

        <?php if (!empty($strukturByLevel)): ?>
        <div class="about-v2-org-chart">
            <?php foreach ($strukturByLevel as $level => $nodes): ?>
                <?php if ($level > 1): ?><div class="about-v2-org-connector"></div><?php endif; ?>
                <div class="about-v2-org-level">
                    <?php foreach ($nodes as $n):
                        $initial = !empty($n['nama_pejabat']) ? strtoupper(substr($n['nama_pejabat'], 0, 1)) : strtoupper(substr($n['nama_jabatan'], 0, 1));
                        $fotoUrl = !empty($n['foto']) && is_file(UPLOAD_DIR . 'struktur/' . $n['foto']) ? url('uploads/struktur/' . $n['foto']) : null;
                    ?>
                    <div class="about-v2-org-card <?= $level === 1 ? 'leader' : '' ?>">
                        <div class="about-v2-org-avatar">
                            <?php if ($fotoUrl): ?>
                                <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="<?= htmlspecialchars($n['nama_pejabat'] ?? $n['nama_jabatan']) ?>">
                            <?php else: ?>
                                <?= $initial ?>
                            <?php endif; ?>
                        </div>
                        <div class="about-v2-org-jabatan"><?= htmlspecialchars($n['nama_jabatan']) ?></div>
                        <div class="about-v2-org-nama">
                            <?= !empty($n['nama_pejabat']) ? htmlspecialchars($n['nama_pejabat']) : '<em style="opacity:.6;font-weight:400;font-size:.85rem;">(Belum diisi)</em>' ?>
                        </div>
                        <?php if (!empty($n['nip'])): ?>
                            <div class="about-v2-org-nip"><?= htmlspecialchars($n['nip']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:3rem;background:rgba(255,255,255,.05);border-radius:16px;">
            <i class="bi bi-diagram-3-fill" style="font-size:3rem;color:rgba(255,255,255,.4);"></i>
            <p style="color:rgba(255,255,255,.75);margin-top:1rem;">Struktur organisasi belum ditambahkan admin.</p>
        </div>
        <?php endif; ?>

        <div style="text-align:center;margin-top:2.5rem;">
            <a href="<?= url('struktur-organisasi') ?>" class="about-v2-cta-primary" style="display:inline-flex;">
                <i class="bi bi-arrow-right-circle-fill"></i> Lihat Bagan Lengkap
            </a>
        </div>
    </div>
</section>

<!-- ===== CORE VALUES ===== -->
<section class="about-v2-section about-v2-values">
    <div class="container">
        <div class="about-v2-section-header">
            <span class="about-v2-section-badge"><i class="bi bi-heart-fill"></i> Nilai Kami</span>
            <h2 class="about-v2-section-title">Nilai-Nilai <span class="about-v2-accent">Inti</span></h2>
            <p class="about-v2-section-sub">Prinsip yang kami pegang teguh dalam setiap pelayanan kepada civitas akademika</p>
        </div>

        <div class="about-v2-values-grid">
            <?php
            $values = [
                ['icon' => 'hand-thumbs-up-fill', 'title' => 'Amanah', 'desc' => 'Menjaga kepercayaan dengan integritas tinggi dan tanggung jawab penuh'],
                ['icon' => 'eye-fill', 'title' => 'Transparan', 'desc' => 'Terbuka dalam setiap proses dan keputusan untuk akuntabilitas maksimal'],
                ['icon' => 'award-fill', 'title' => 'Profesional', 'desc' => 'Bekerja dengan kompetensi tinggi dan standar kualitas terbaik'],
                ['icon' => 'people-fill', 'title' => 'Melayani', 'desc' => 'Mengutamakan kepuasan civitas akademika dengan hati yang tulus'],
                ['icon' => 'arrow-repeat', 'title' => 'Berkelanjutan', 'desc' => 'Terus berinovasi dan berkembang untuk masa depan yang lebih baik'],
                ['icon' => 'lightning-charge-fill', 'title' => 'Efisien', 'desc' => 'Mengoptimalkan sumber daya untuk hasil maksimal dengan biaya minimal'],
            ];
            foreach ($values as $v): ?>
            <div class="about-v2-value-card">
                <div class="about-v2-value-icon"><i class="bi bi-<?= $v['icon'] ?>"></i></div>
                <h4><?= $v['title'] ?></h4>
                <p><?= $v['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== SERTIFIKASI & AKREDITASI ===== -->
<section class="about-v2-section about-v2-certs">
    <div class="container">
        <div class="about-v2-section-header">
            <span class="about-v2-section-badge"><i class="bi bi-patch-check-fill"></i> Pengakuan</span>
            <h2 class="about-v2-section-title">Sertifikasi & <span class="about-v2-accent">Akreditasi</span></h2>
            <p class="about-v2-section-sub">Penghargaan dan pengakuan atas kualitas layanan yang kami berikan</p>
        </div>

        <div class="about-v2-certs-grid">
            <div class="about-v2-cert-card">
                <div class="about-v2-cert-icon"><i class="bi bi-award-fill"></i></div>
                <div class="about-v2-cert-info">
                    <h4>Akreditasi BAN-PT</h4>
                    <p>Predikat Unggul dari Badan Akreditasi Nasional Perguruan Tinggi.</p>
                    <span class="about-v2-cert-number">A (Unggul)</span>
                </div>
            </div>
            <div class="about-v2-cert-card">
                <div class="about-v2-cert-icon"><i class="bi bi-shield-fill-check"></i></div>
                <div class="about-v2-cert-info">
                    <h4>ISO 9001:2015</h4>
                    <p>Sertifikasi Sistem Manajemen Mutu internasional.</p>
                    <span class="about-v2-cert-number">Certified</span>
                </div>
            </div>
            <div class="about-v2-cert-card">
                <div class="about-v2-cert-icon"><i class="bi bi-tree-fill"></i></div>
                <div class="about-v2-cert-info">
                    <h4>Green Campus Award</h4>
                    <p>Penghargaan kampus ramah lingkungan.</p>
                    <span class="about-v2-cert-number">Gold 2024</span>
                </div>
            </div>
            <div class="about-v2-cert-card">
                <div class="about-v2-cert-icon"><i class="bi bi-trophy-fill"></i></div>
                <div class="about-v2-cert-info">
                    <h4>Best Governance Award</h4>
                    <p>Tata kelola terbaik dari APTM.</p>
                    <span class="about-v2-cert-number">Winner 2025</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== GALERI KEGIATAN ===== -->
<section class="about-v2-section about-v2-gallery">
    <div class="container">
        <div class="about-v2-section-header">
            <span class="about-v2-section-badge"><i class="bi bi-images"></i> Dokumentasi</span>
            <h2 class="about-v2-section-title">Galeri <span class="about-v2-accent">Kegiatan</span></h2>
            <p class="about-v2-section-sub">Momen-momen penting dalam perjalanan BAUK UNIMOF</p>
        </div>

        <div class="about-v2-gallery-tabs">
            <button class="about-v2-gallery-tab active" data-filter="all"><i class="bi bi-grid-fill"></i> Semua</button>
            <button class="about-v2-gallery-tab" data-filter="rapat"><i class="bi bi-people-fill"></i> Rapat</button>
            <button class="about-v2-gallery-tab" data-filter="sertifikasi"><i class="bi bi-award-fill"></i> Sertifikasi</button>
            <button class="about-v2-gallery-tab" data-filter="digital"><i class="bi bi-laptop-fill"></i> Digital</button>
            <button class="about-v2-gallery-tab" data-filter="lingkungan"><i class="bi bi-tree-fill"></i> Lingkungan</button>
            <button class="about-v2-gallery-tab" data-filter="sdm"><i class="bi bi-person-badge-fill"></i> SDM</button>
        </div>

        <div class="about-v2-gallery-grid">
            <?php
            $gallery = [
                ['cat' => 'rapat', 'title' => 'Rapat Koordinasi Tahunan', 'desc' => 'Evaluasi & perencanaan strategis', 'icon' => 'people-fill'],
                ['cat' => 'sertifikasi', 'title' => 'Penerimaan Sertifikasi ISO', 'desc' => 'Momen bersejarah 2018', 'icon' => 'award-fill'],
                ['cat' => 'digital', 'title' => 'Peluncuran Sistem Digital', 'desc' => 'Transformasi teknologi 2021', 'icon' => 'laptop-fill'],
                ['cat' => 'lingkungan', 'title' => 'Program Green Campus', 'desc' => 'Inisiatif ramah lingkungan', 'icon' => 'tree-fill'],
                ['cat' => 'sdm', 'title' => 'Pelatihan Pegawai', 'desc' => 'Pengembangan SDM berkelanjutan', 'icon' => 'person-badge-fill'],
                ['cat' => 'audit', 'title' => 'Audit Internal', 'desc' => 'Pemantauan kualitas layanan', 'icon' => 'graph-up-arrow'],
            ];
            foreach ($gallery as $g): ?>
            <div class="about-v2-gallery-item" data-cat="<?= $g['cat'] ?>">
                <div class="about-v2-gallery-ph"><i class="bi bi-<?= $g['icon'] ?>"></i></div>
                <div class="about-v2-gallery-overlay">
                    <div>
                        <h5><?= $g['title'] ?></h5>
                        <p><?= $g['desc'] ?></p>
                    </div>
                    <span class="about-v2-gallery-view"><i class="bi bi-arrow-up-right"></i></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== LEADERSHIP ===== -->
<section class="about-v2-section about-v2-leadership">
    <div class="container">
        <div class="about-v2-section-header">
            <span class="about-v2-section-badge"><i class="bi bi-person-badge-fill"></i> Kepemimpinan</span>
            <h2 class="about-v2-section-title">Tim <span class="about-v2-accent">Pimpinan BAUK</span></h2>
            <p class="about-v2-section-sub">Para profesional yang memimpin transformasi layanan</p>
        </div>

        <?php
        // Ambil leadership dari struktur_organisasi (level 1 & 2) + tambah dummy jika kurang
        $leaders = [];
        foreach ($struktur as $s) {
            if ((int)$s['level'] <= 2 || strpos(strtolower($s['nama_jabatan']), 'kabag') !== false || strpos(strtolower($s['nama_jabatan']), 'koordinator') !== false) {
                $leaders[] = $s;
                if (count($leaders) >= 5) break;
            }
        }
        ?>

        <?php if (!empty($leaders)): ?>
        <div class="about-v2-leader-grid">
            <?php foreach ($leaders as $l):
                $fotoUrl = !empty($l['foto']) && is_file(UPLOAD_DIR . 'struktur/' . $l['foto']) ? url('uploads/struktur/' . $l['foto']) : null;
            ?>
            <div class="about-v2-leader-card">
                <div class="about-v2-leader-image">
                    <?php if ($fotoUrl): ?>
                        <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="<?= htmlspecialchars($l['nama_pejabat'] ?? $l['nama_jabatan']) ?>">
                    <?php else: ?>
                        <i class="bi bi-person-circle"></i>
                    <?php endif; ?>
                </div>
                <div class="about-v2-leader-info">
                    <h4><?= htmlspecialchars($l['nama_pejabat'] ?? $l['nama_jabatan']) ?></h4>
                    <p><?= htmlspecialchars($l['nama_jabatan']) ?></p>
                    <?php if (!empty($l['nip'])): ?>
                        <small><i class="bi bi-hash"></i> <?= htmlspecialchars($l['nip']) ?></small>
                    <?php else: ?>
                        <small>Memimpin transformasi dan peningkatan kualitas layanan</small>
                    <?php endif; ?>
                    <div class="about-v2-leader-social">
                        <a href="mailto:bauk@unimof.ac.id" title="Email"><i class="bi bi-envelope-fill"></i></a>
                        <a href="<?= url('kontak') ?>" title="Kontak"><i class="bi bi-telephone-fill"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:3rem;background:#f8fafc;border-radius:16px;">
            <i class="bi bi-person-badge-fill" style="font-size:3rem;color:#c3ced9;"></i>
            <p style="color:#64748b;margin-top:1rem;">Data kepemimpinan belum ditambahkan.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===== CTA ===== -->
<section class="about-v2-cta">
    <div class="container">
        <div class="about-v2-cta-content">
            <h2>Siap <span style="color:#f39c12;">Bermitra</span> dengan Kami?</h2>
            <p>BAUK UNIMOF terbuka untuk kolaborasi, konsultasi, dan kunjungan studi banding dari institusi lain. Mari bersama-sama mewujudkan tata kelola universitas yang lebih baik.</p>

            <div class="about-v2-cta-grid">
                <a href="tel:<?= preg_replace('/[^0-9+]/', '', $cfg['contact_phone'] ?? '03822425464') ?>" class="about-v2-cta-card">
                    <div class="about-v2-cta-icon"><i class="bi bi-telephone-fill"></i></div>
                    <h5>Telepon</h5>
                    <p><?= htmlspecialchars($cfg['contact_phone'] ?? '(0382) 2425464') ?></p>
                </a>
                <a href="mailto:<?= htmlspecialchars($cfg['contact_email'] ?? 'bauk@unimof.ac.id') ?>" class="about-v2-cta-card">
                    <div class="about-v2-cta-icon"><i class="bi bi-envelope-fill"></i></div>
                    <h5>Email</h5>
                    <p><?= htmlspecialchars($cfg['contact_email'] ?? 'bauk@unimof.ac.id') ?></p>
                </a>
                <a href="<?= url('kontak') ?>" class="about-v2-cta-card">
                    <div class="about-v2-cta-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <h5>Lokasi</h5>
                    <p>Maumere, NTT</p>
                </a>
            </div>

            <div class="about-v2-cta-actions">
                <a href="<?= url('kontak') ?>" class="about-v2-cta-btn about-v2-cta-btn-primary">
                    <i class="bi bi-envelope-fill"></i> Hubungi Kami
                </a>
                <a href="<?= url('layanan/kepegawaian') ?>" class="about-v2-cta-btn about-v2-cta-btn-outline">
                    <i class="bi bi-arrow-right-circle-fill"></i> Lihat Layanan
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ===== COUNTER + GALLERY FILTER SCRIPT ===== -->
<script>
(function(){
    'use strict';

    // ===== COUNTER ANIMATION =====
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
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        document.querySelectorAll('.about-v2-count').forEach(function(el){ observer.observe(el); });
    } else {
        document.querySelectorAll('.about-v2-count').forEach(function(el){
            el.textContent = parseInt(el.getAttribute('data-count')).toLocaleString('id-ID');
        });
    }

    // ===== GALLERY FILTER =====
    document.querySelectorAll('.about-v2-gallery-tab').forEach(function(tab){
        tab.addEventListener('click', function(){
            document.querySelectorAll('.about-v2-gallery-tab').forEach(function(t){ t.classList.remove('active'); });
            this.classList.add('active');
            var filter = this.getAttribute('data-filter');
            document.querySelectorAll('.about-v2-gallery-item').forEach(function(item){
                if (filter === 'all' || item.getAttribute('data-cat') === filter) {
                    item.style.display = '';
                    setTimeout(function(){ item.style.opacity = '1'; item.style.transform = 'scale(1)'; }, 10);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.9)';
                    setTimeout(function(){ item.style.display = 'none'; }, 250);
                }
            });
        });
    });
})();
</script>