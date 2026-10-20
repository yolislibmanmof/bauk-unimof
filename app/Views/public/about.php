<?php
/**
 * BAUK UNIMOF - Halaman Tentang (ULTIMATE EDITION)
 * Profil, Visi Misi, Sejarah, Struktur Organisasi
 */
$db = \App\Helpers\Database::getInstance();

// Ambil statistik dinamis
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
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== PAGE HERO ===== */
.ult-page-hero{position:relative;background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 50%,#2980b9 100%);color:#fff;padding:5rem 0 6rem;overflow:hidden;}
.ult-page-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/svg%3E");z-index:1;}
.ult-page-hero-shape{position:absolute;border-radius:50%;background:radial-gradient(circle,rgba(230,126,34,.2) 0%,transparent 70%);z-index:1;}
.ult-page-hero-shape-1{width:400px;height:400px;top:-100px;right:-100px;animation:ult_float 20s infinite ease-in-out;}
.ult-page-hero-shape-2{width:300px;height:300px;bottom:-80px;left:-80px;animation:ult_float 15s infinite ease-in-out reverse;}
@keyframes ult_float{0%,100%{transform:translate(0,0) scale(1);}50%{transform:translate(30px,-30px) scale(1.1);}}
.ult-page-hero-content{position:relative;z-index:2;text-align:center;}
.ult-page-hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.12);backdrop-filter:blur(10px);padding:.5rem 1.25rem;border-radius:50px;font-size:.85rem;margin-bottom:1.5rem;border:1px solid rgba(255,255,255,.2);}
.ult-page-hero h1{font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3.5rem);font-weight:700;margin-bottom:1rem;line-height:1.2;}
.ult-page-hero p{font-size:1.1rem;max-width:700px;margin:0 auto;line-height:1.8;color:rgba(255,255,255,.9);}
.ult-hero-stats{display:flex;justify-content:center;gap:2rem;margin-top:2.5rem;flex-wrap:wrap;}
.ult-hero-stat{text-align:center;min-width:120px;}
.ult-hero-stat-value{font-family:'Playfair Display',serif;font-size:2.5rem;font-weight:700;line-height:1;color:#f39c12;}
.ult-hero-stat-label{font-size:.85rem;margin-top:.25rem;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:1px;}
.ult-hero-wave{position:absolute;bottom:-1px;left:0;right:0;z-index:3;}
.ult-hero-wave svg{display:block;width:100%;height:auto;}

/* ===== PROFILE SECTION ===== */
.ult-profile{padding:4rem 0;}
.ult-profile-grid{display:grid;grid-template-columns:1fr 1.5fr;gap:3rem;align-items:center;}
.ult-profile-image{position:relative;}
.ult-profile-image-wrapper{border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.15);position:relative;}
.ult-profile-image-wrapper img{width:100%;height:auto;display:block;}
.ult-profile-image-placeholder{width:100%;aspect-ratio:4/3;background:linear-gradient(135deg,#1a5276,#2980b9);display:flex;align-items:center;justify-content:center;color:#fff;font-size:4rem;}
.ult-profile-badge{position:absolute;top:1.5rem;left:1.5rem;background:#fff;padding:.75rem 1.25rem;border-radius:50px;box-shadow:0 4px 15px rgba(0,0,0,.1);font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:.5rem;color:var(--color-primary);}
.ult-profile-badge i{color:#f39c12;}
.ult-profile-content h3{font-family:'Playfair Display',serif;font-size:1.75rem;margin-bottom:1rem;color:var(--color-text);}
.ult-profile-content p{color:var(--color-text-secondary);line-height:1.9;margin-bottom:1rem;}
.ult-profile-highlights{list-style:none;padding:0;margin-top:1.5rem;}
.ult-profile-highlights li{display:flex;align-items:flex-start;gap:.75rem;margin-bottom:1rem;padding:.75rem 1rem;background:var(--color-primary-50);border-radius:10px;border-left:3px solid var(--color-primary);}
.ult-profile-highlights li i{color:var(--color-primary);font-size:1.1rem;margin-top:2px;flex-shrink:0;}
.ult-profile-highlights li strong{display:block;color:var(--color-text);margin-bottom:.125rem;}
.ult-profile-highlights li span{font-size:.9rem;color:var(--color-text-secondary);}

/* ===== VISION MISSION VALUES ===== */
.ult-vmv{background:linear-gradient(180deg,#f8f9fa 0%,#fff 100%);padding:4rem 0;}
.ult-vmv-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:2rem;}
.ult-vmv-card{background:#fff;padding:2.5rem 2rem;border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,.08);position:relative;overflow:hidden;transition:all .4s ease;border:1px solid var(--color-border-light);}
.ult-vmv-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--color-primary),var(--color-accent));transform:scaleX(0);transform-origin:left;transition:transform .4s ease;}
.ult-vmv-card:hover{transform:translateY(-8px);box-shadow:0 20px 50px rgba(0,0,0,.12);}
.ult-vmv-card:hover::before{transform:scaleX(1);}
.ult-vmv-icon{width:80px;height:80px;border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:1.5rem;color:#fff;position:relative;}
.ult-vmv-icon::after{content:'';position:absolute;inset:-5px;border:2px dashed currentColor;opacity:.2;border-radius:20px;}
.ult-vmv-card:nth-child(1) .ult-vmv-icon{background:linear-gradient(135deg,#1a5276,#2980b9);box-shadow:0 10px 30px rgba(26,82,118,.3);}
.ult-vmv-card:nth-child(2) .ult-vmv-icon{background:linear-gradient(135deg,#e67e22,#f39c12);box-shadow:0 10px 30px rgba(230,126,34,.3);}
.ult-vmv-card:nth-child(3) .ult-vmv-icon{background:linear-gradient(135deg,#27ae60,#2ecc71);box-shadow:0 10px 30px rgba(39,174,96,.3);}
.ult-vmv-card h3{font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:1rem;color:var(--color-text);}
.ult-vmv-card p{color:var(--color-text-secondary);line-height:1.8;margin-bottom:1.25rem;}
.ult-vmv-list{list-style:none;padding:0;}
.ult-vmv-list li{display:flex;align-items:center;gap:.625rem;padding:.5rem 0;color:var(--color-text-secondary);font-size:.9rem;border-bottom:1px dashed var(--color-border-light);}
.ult-vmv-list li:last-child{border-bottom:none;}
.ult-vmv-list li i{color:var(--color-success);flex-shrink:0;}

/* ===== TIMELINE ===== */
.ult-timeline-section{padding:4rem 0;background:#fff;}
.ult-timeline{position:relative;max-width:900px;margin:3rem auto 0;padding:2rem 0;}
.ult-timeline::before{content:'';position:absolute;left:50%;top:0;bottom:0;width:3px;background:linear-gradient(180deg,var(--color-primary) 0%,var(--color-accent) 50%,var(--color-green) 100%);transform:translateX(-50%);}
.ult-timeline-item{position:relative;margin-bottom:3rem;display:flex;align-items:center;}
.ult-timeline-item:nth-child(odd){flex-direction:row-reverse;}
.ult-timeline-item:nth-child(odd) .ult-timeline-content{text-align:right;padding-right:3rem;padding-left:0;}
.ult-timeline-item:nth-child(even) .ult-timeline-content{padding-left:3rem;}
.ult-timeline-content{width:50%;}
.ult-timeline-dot{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:60px;height:60px;border-radius:50%;background:#fff;border:4px solid var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:var(--color-primary);z-index:2;box-shadow:0 5px 15px rgba(0,0,0,.1);transition:all .3s ease;}
.ult-timeline-item:hover .ult-timeline-dot{background:var(--color-primary);color:#fff;transform:translate(-50%,-50%) scale(1.1);}
.ult-timeline-year{display:inline-block;background:var(--color-accent);color:#fff;padding:.25rem 1rem;border-radius:50px;font-weight:700;font-size:.85rem;margin-bottom:.5rem;}
.ult-timeline-content h4{font-size:1.15rem;font-weight:700;color:var(--color-text);margin-bottom:.375rem;}
.ult-timeline-content p{color:var(--color-text-secondary);font-size:.9rem;line-height:1.7;margin:0;}

/* ===== STRUCTURE ===== */
.ult-structure{background:var(--color-primary-dark);color:#fff;padding:4rem 0;position:relative;overflow:hidden;}
.ult-structure::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M0 0h20v20H0zM20 20h20v20H20z'/%3E%3C/g%3E%3C/svg%3E");}
.ult-structure-content{position:relative;z-index:1;}
.ult-structure .section-badge{background:rgba(255,255,255,.15);color:#fff;}
.ult-structure .section-title{color:#fff;}
.ult-structure .section-subtitle{color:rgba(255,255,255,.8);}
.ult-org-chart{margin-top:3rem;}
.ult-org-level{display:flex;justify-content:center;gap:1.5rem;margin-bottom:1rem;flex-wrap:wrap;position:relative;}
.ult-org-level:not(:last-child)::after{content:'';position:absolute;bottom:-1rem;left:50%;width:2px;height:1rem;background:rgba(255,255,255,.3);transform:translateX(-50%);}
.ult-org-card{background:rgba(255,255,255,.1);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.2);border-radius:15px;padding:1.25rem 1.5rem;text-align:center;min-width:200px;transition:all .3s ease;}
.ult-org-card:hover{background:rgba(255,255,255,.18);transform:translateY(-3px);}
.ult-org-card.leader{background:linear-gradient(135deg,rgba(230,126,34,.3),rgba(243,156,18,.3));border-color:rgba(243,156,18,.5);min-width:280px;padding:1.5rem 2rem;}
.ult-org-avatar{width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,#f39c12,#e67e22);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;font-size:1.75rem;color:#fff;font-weight:700;}
.ult-org-card.leader .ult-org-avatar{width:90px;height:90px;font-size:2.25rem;}
.ult-org-card h5{font-size:1rem;font-weight:700;margin-bottom:.25rem;}
.ult-org-card p{font-size:.8rem;color:rgba(255,255,255,.75);margin:0;}
.ult-org-card.leader h5{font-size:1.15rem;}
.ult-org-card.leader p{color:rgba(255,255,255,.9);}
.ult-org-connector{width:2px;height:2rem;background:rgba(255,255,255,.3);margin:0 auto;}

/* ===== CORE VALUES ===== */
.ult-values{padding:4rem 0;background:#f8f9fa;}
.ult-values-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;margin-top:3rem;}
.ult-value-card{background:#fff;padding:2rem 1.5rem;border-radius:15px;text-align:center;transition:all .3s ease;box-shadow:0 4px 20px rgba(0,0,0,.05);border:1px solid var(--color-border-light);position:relative;overflow:hidden;}
.ult-value-card::before{content:'';position:absolute;top:0;left:0;width:100%;height:100%;background:linear-gradient(135deg,var(--color-primary),var(--color-primary-light));opacity:0;transition:opacity .3s ease;z-index:0;}
.ult-value-card:hover{transform:translateY(-5px);box-shadow:0 15px 40px rgba(0,0,0,.1);}
.ult-value-card:hover::before{opacity:1;}
.ult-value-card:hover *{color:#fff!important;position:relative;z-index:1;}
.ult-value-card:hover .ult-value-icon{background:rgba(255,255,255,.2)!important;color:#fff!important;}
.ult-value-icon{width:65px;height:65px;border-radius:15px;display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto 1rem;background:linear-gradient(135deg,var(--color-primary-50),var(--color-primary-100));color:var(--color-primary);position:relative;z-index:1;transition:all .3s ease;}
.ult-value-card h4{font-size:1.1rem;font-weight:700;margin-bottom:.5rem;color:var(--color-text);position:relative;z-index:1;}
.ult-value-card p{font-size:.85rem;color:var(--color-text-secondary);line-height:1.6;margin:0;position:relative;z-index:1;}

/* ===== CERTIFICATIONS ===== */
.ult-certifications{padding:4rem 0;background:#fff;}
.ult-cert-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem;margin-top:3rem;}
.ult-cert-card{background:#fff;border-radius:15px;padding:1.75rem;display:flex;gap:1rem;align-items:flex-start;box-shadow:0 4px 20px rgba(0,0,0,.05);border:1px solid var(--color-border-light);transition:all .3s ease;}
.ult-cert-card:hover{transform:translateY(-5px);box-shadow:0 10px 30px rgba(0,0,0,.1);border-color:var(--color-primary-100);}
.ult-cert-icon{width:55px;height:55px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;}
.ult-cert-card:nth-child(1) .ult-cert-icon{background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#fff;box-shadow:0 5px 15px rgba(245,158,11,.3);}
.ult-cert-card:nth-child(2) .ult-cert-icon{background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;box-shadow:0 5px 15px rgba(59,130,246,.3);}
.ult-cert-card:nth-child(3) .ult-cert-icon{background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 5px 15px rgba(16,185,129,.3);}
.ult-cert-card:nth-child(4) .ult-cert-icon{background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff;box-shadow:0 5px 15px rgba(139,92,246,.3);}
.ult-cert-info h4{font-size:1rem;font-weight:700;margin-bottom:.25rem;color:var(--color-text);}
.ult-cert-info p{font-size:.85rem;color:var(--color-text-secondary);line-height:1.6;margin:0;}
.ult-cert-number{display:inline-block;font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:var(--color-primary);margin-top:.5rem;}

/* ===== GALLERY ===== */
.ult-gallery{padding:4rem 0;background:#f8f9fa;}
.ult-gallery-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;margin-top:3rem;}
.ult-gallery-item{position:relative;border-radius:15px;overflow:hidden;aspect-ratio:4/3;cursor:pointer;box-shadow:0 10px 30px rgba(0,0,0,.1);}
.ult-gallery-item img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease;}
.ult-gallery-item:hover img{transform:scale(1.1);}
.ult-gallery-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;color:#fff;}
.ult-gallery-item:nth-child(1) .ult-gallery-placeholder{background:linear-gradient(135deg,#1a5276,#2980b9);}
.ult-gallery-item:nth-child(2) .ult-gallery-placeholder{background:linear-gradient(135deg,#e67e22,#f39c12);}
.ult-gallery-item:nth-child(3) .ult-gallery-placeholder{background:linear-gradient(135deg,#27ae60,#2ecc71);}
.ult-gallery-item:nth-child(4) .ult-gallery-placeholder{background:linear-gradient(135deg,#8e44ad,#9b59b6);}
.ult-gallery-item:nth-child(5) .ult-gallery-placeholder{background:linear-gradient(135deg,#c0392b,#e74c3c);}
.ult-gallery-item:nth-child(6) .ult-gallery-placeholder{background:linear-gradient(135deg,#16a085,#1abc9c);}
.ult-gallery-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(13,47,74,.9) 0%,transparent 60%);display:flex;align-items:flex-end;padding:1.5rem;opacity:0;transition:opacity .3s ease;}
.ult-gallery-item:hover .ult-gallery-overlay{opacity:1;}
.ult-gallery-overlay h5{color:#fff;font-size:1rem;margin-bottom:.25rem;}
.ult-gallery-overlay p{color:rgba(255,255,255,.8);font-size:.8rem;margin:0;}

/* ===== LEADERSHIP ===== */
.ult-leadership{padding:4rem 0;background:#fff;}
.ult-leader-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:2rem;margin-top:3rem;}
.ult-leader-card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.08);transition:all .3s ease;border:1px solid var(--color-border-light);}
.ult-leader-card:hover{transform:translateY(-8px);box-shadow:0 20px 50px rgba(0,0,0,.12);}
.ult-leader-image{aspect-ratio:1;background:linear-gradient(135deg,var(--color-primary-50),var(--color-primary-100));display:flex;align-items:center;justify-content:center;font-size:5rem;color:var(--color-primary);position:relative;}
.ult-leader-image::after{content:'';position:absolute;bottom:0;left:0;right:0;height:60px;background:linear-gradient(to top,#fff,transparent);}
.ult-leader-info{padding:1.5rem;text-align:center;}
.ult-leader-info h4{font-size:1.15rem;font-weight:700;color:var(--color-text);margin-bottom:.25rem;}
.ult-leader-info p{font-size:.85rem;color:var(--color-primary);font-weight:600;margin-bottom:.75rem;}
.ult-leader-info small{font-size:.8rem;color:var(--color-text-secondary);display:block;margin-bottom:1rem;line-height:1.5;}
.ult-leader-social{display:flex;justify-content:center;gap:.5rem;}
.ult-leader-social a{width:36px;height:36px;border-radius:50%;background:var(--color-bg);color:var(--color-text-secondary);display:flex;align-items:center;justify-content:center;transition:all .2s;}
.ult-leader-social a:hover{background:var(--color-primary);color:#fff;transform:translateY(-2px);}

/* ===== CTA SECTION ===== */
.ult-about-cta{background:linear-gradient(135deg,#1a5276 0%,#0d2f4a 100%);color:#fff;padding:4rem 0;text-align:center;position:relative;overflow:hidden;}
.ult-about-cta::before{content:'';position:absolute;top:-50%;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(230,126,34,.15) 0%,transparent 70%);border-radius:50%;}
.ult-about-cta-content{position:relative;z-index:1;max-width:700px;margin:0 auto;}
.ult-about-cta h2{font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:1rem;}
.ult-about-cta p{color:rgba(255,255,255,.85);margin-bottom:2rem;line-height:1.8;}
.ult-about-cta-actions{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;}
.ult-cta-btn{padding:1rem 2rem;border-radius:50px;font-weight:600;font-size:1rem;display:inline-flex;align-items:center;gap:.5rem;transition:all .3s ease;text-decoration:none;}
.ult-cta-btn-primary{background:#fff;color:var(--color-primary);}
.ult-cta-btn-primary:hover{background:var(--color-accent);color:#fff;transform:translateY(-3px);box-shadow:0 10px 30px rgba(230,126,34,.4);}
.ult-cta-btn-outline{background:transparent;color:#fff;border:2px solid rgba(255,255,255,.4);}
.ult-cta-btn-outline:hover{background:rgba(255,255,255,.1);border-color:#fff;transform:translateY(-3px);}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-profile-grid{grid-template-columns:1fr;gap:2rem;}
    .ult-vmv-grid{grid-template-columns:1fr;}
    .ult-timeline::before{left:30px;}
    .ult-timeline-item,.ult-timeline-item:nth-child(odd){flex-direction:row;}
    .ult-timeline-content,.ult-timeline-item:nth-child(odd) .ult-timeline-content{width:100%;padding-left:5rem!important;padding-right:0!important;text-align:left!important;}
    .ult-timeline-dot{left:30px;transform:translate(-50%,-50%);}
    .ult-timeline-item:hover .ult-timeline-dot{transform:translate(-50%,-50%) scale(1.1);}
}
@media (max-width:768px){
    .ult-page-hero{padding:3rem 0 4rem;}
    .ult-hero-stats{gap:1rem;}
    .ult-hero-stat-value{font-size:2rem;}
    .ult-leader-grid{grid-template-columns:1fr;}
    .ult-org-level{flex-direction:column;align-items:center;}
    .ult-org-level:not(:last-child)::after{display:none;}
}
</style>

<!-- ===== PAGE HERO ===== -->
<section class="ult-page-hero">
    <div class="ult-page-hero-shape ult-page-hero-shape-1"></div>
    <div class="ult-page-hero-shape ult-page-hero-shape-2"></div>
    
    <div class="container">
        <div class="ult-page-hero-content">
            <span class="ult-page-hero-badge"><i class="bi bi-building-fill"></i> Profil Instansi</span>
            <h1>Tentang <span style="color:#f39c12;">BAUK UNIMOF</span></h1>
            <p>Biro Administrasi Umum dan Keuangan — pilar pendukung utama Universitas Muhammadiyah Maumere dalam mewujudkan tata kelola universitas yang profesional, transparan, dan akuntabel.</p>
            
            <div class="ult-hero-stats">
                <div class="ult-hero-stat">
                    <div class="ult-hero-stat-value" data-count="<?= $stats['pegawai'] ?>">0</div>
                    <div class="ult-hero-stat-label">Pegawai</div>
                </div>
                <div class="ult-hero-stat">
                    <div class="ult-hero-stat-value" data-count="<?= $stats['aset'] ?>">0</div>
                    <div class="ult-hero-stat-label">Aset Terkelola</div>
                </div>
                <div class="ult-hero-stat">
                    <div class="ult-hero-stat-value" data-count="<?= $stats['departemen'] ?>">0</div>
                    <div class="ult-hero-stat-label">Unit Kerja</div>
                </div>
                <div class="ult-hero-stat">
                    <div class="ult-hero-stat-value" data-count="<?= $stats['regulasi'] ?>">0</div>
                    <div class="ult-hero-stat-label">Regulasi Aktif</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="ult-hero-wave">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#ffffff"/>
        </svg>
    </div>
</section>

<!-- ===== PROFILE SECTION ===== -->
<section class="ult-profile section">
    <div class="container">
        <div class="ult-profile-grid">
            <div class="ult-profile-image">
                <div class="ult-profile-image-wrapper">
                    <div class="ult-profile-image-placeholder">
                        <i class="bi bi-building-fill"></i>
                    </div>
                </div>
                <div class="ult-profile-badge">
                    <i class="bi bi-award-fill"></i> Sejak 1999
                </div>
            </div>
            
            <div class="ult-profile-content">
                <span class="section-badge">Profil Kami</span>
                <h3>Mengenal Lebih Dekat BAUK UNIMOF</h3>
                <p>Biro Administrasi Umum dan Keuangan (BAUK) adalah unit pendukung strategis yang bertanggung jawab dalam pengelolaan administrasi umum, keuangan, kepegawaian, aset, dan sarana prasarana di lingkungan Universitas Muhammadiyah Maumere.</p>
                <p>Sebagai tulang punggung operasional universitas, BAUK berkomitmen mewujudkan pelayanan yang inovatif, transparan, efisien, dan terintegrasi untuk mendukung tercapainya visi dan misi universitas serta tata kelola yang baik (<em>good university governance</em>).</p>
                
                <ul class="ult-profile-highlights">
                    <li>
                        <i class="bi bi-patch-check-fill"></i>
                        <div>
                            <strong>Terakreditasi Unggul</strong>
                            <span>BAN-PT dengan predikat Unggul sejak 2020</span>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-shield-fill-check"></i>
                        <div>
                            <strong>Sertifikasi ISO 9001:2015</strong>
                            <span>Sistem Manajemen Mutu terstandar internasional</span>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-graph-up-arrow"></i>
                        <div>
                            <strong>Transformasi Digital</strong>
                            <span>100% layanan telah terdigitalisasi sejak 2023</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ===== VISION MISSION VALUES ===== -->
<section class="ult-vmv">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-compass-fill"></i> Arah Kami</span>
                <h2 class="section-title">Visi, Misi & Nilai</h2>
                <p class="section-subtitle" style="margin:0 auto;">Landasan strategis dalam setiap langkah pelayanan kami</p>
            </div>
        </div>
        
        <div class="ult-vmv-grid">
            <!-- VISI -->
            <div class="ult-vmv-card">
                <div class="ult-vmv-icon"><i class="bi bi-eye-fill"></i></div>
                <h3>Visi</h3>
                <p>Menjadi biro pelayanan prima yang profesional, transparan, dan akuntabel dalam mendukung kemajuan UNIMOF menuju universitas berdaya saing global.</p>
                <ul class="ult-vmv-list">
                    <li><i class="bi bi-check-circle-fill"></i> Pelayanan Prima</li>
                    <li><i class="bi bi-check-circle-fill"></i> Profesional & Kompeten</li>
                    <li><i class="bi bi-check-circle-fill"></i> Transparansi Penuh</li>
                    <li><i class="bi bi-check-circle-fill"></i> Akuntabilitas Tinggi</li>
                </ul>
            </div>
            
            <!-- MISI -->
            <div class="ult-vmv-card">
                <div class="ult-vmv-icon"><i class="bi bi-bullseye"></i></div>
                <h3>Misi</h3>
                <p>Menyelenggarakan administrasi umum, keuangan, kepegawaian, dan aset yang tertib, efisien, dan berbasis teknologi informasi modern.</p>
                <ul class="ult-vmv-list">
                    <li><i class="bi bi-check-circle-fill"></i> Administrasi Tertib & Rapi</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pengelolaan Keuangan Efisien</li>
                    <li><i class="bi bi-check-circle-fill"></i> Pengembangan SDM Berkelanjutan</li>
                    <li><i class="bi bi-check-circle-fill"></i> Digitalisasi Layanan Penuh</li>
                </ul>
            </div>
            
            <!-- NILAI -->
            <div class="ult-vmv-card">
                <div class="ult-vmv-icon"><i class="bi bi-gem"></i></div>
                <h3>Nilai Inti</h3>
                <p>Amanah, transparan, profesional, melayani, dan berkelanjutan sesuai nilai-nilai Al-Islam dan Kemuhammadiyahan yang luhur.</p>
                <ul class="ult-vmv-list">
                    <li><i class="bi bi-check-circle-fill"></i> <strong>A</strong>manah & Terpercaya</li>
                    <li><i class="bi bi-check-circle-fill"></i> <strong>T</strong>ransparan & Terbuka</li>
                    <li><i class="bi bi-check-circle-fill"></i> <strong>A</strong>hli & Profesional</li>
                    <li><i class="bi bi-check-circle-fill"></i> <strong>N</strong>iat Melayani Tulus</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ===== TIMELINE PERJALANAN ===== -->
<section class="ult-timeline-section section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-clock-history"></i> Sejarah</span>
                <h2 class="section-title">Perjalanan BAUK UNIMOF</h2>
                <p class="section-subtitle" style="margin:0 auto;">Milestone penting dalam transformasi layanan administrasi</p>
            </div>
        </div>
        
        <div class="ult-timeline">
            <div class="ult-timeline-item">
                <div class="ult-timeline-dot"><i class="bi bi-flag-fill"></i></div>
                <div class="ult-timeline-content">
                    <span class="ult-timeline-year">1999</span>
                    <h4>Pendirian BAUK</h4>
                    <p>BAUK didirikan sebagai unit pendukung administrasi umum dan keuangan saat Universitas Muhammadiyah Maumere resmi berdiri.</p>
                </div>
            </div>
            
            <div class="ult-timeline-item">
                <div class="ult-timeline-dot"><i class="bi bi-building-fill-gear"></i></div>
                <div class="ult-timeline-content">
                    <span class="ult-timeline-year">2005</span>
                    <h4>Restrukturisasi Organisasi</h4>
                    <p>Penataan struktur organisasi menjadi 4 bagian utama: Kepegawaian, Keuangan, Aset, dan K3L untuk pelayanan yang lebih fokus.</p>
                </div>
            </div>
            
            <div class="ult-timeline-item">
                <div class="ult-timeline-dot"><i class="bi bi-laptop-fill"></i></div>
                <div class="ult-timeline-content">
                    <span class="ult-timeline-year">2012</span>
                    <h4>Digitalisasi Tahap Awal</h4>
                    <p>Penerapan sistem informasi keuangan dan kepegawaian berbasis desktop untuk mempercepat proses administrasi.</p>
                </div>
            </div>
            
            <div class="ult-timeline-item">
                <div class="ult-timeline-dot"><i class="bi bi-award-fill"></i></div>
                <div class="ult-timeline-content">
                    <span class="ult-timeline-year">2018</span>
                    <h4>Sertifikasi ISO 9001:2015</h4>
                    <p>BAUK berhasil meraih sertifikasi ISO 9001:2015 untuk Sistem Manajemen Mutu, menandai komitmen kualitas layanan.</p>
                </div>
            </div>
            
            <div class="ult-timeline-item">
                <div class="ult-timeline-dot"><i class="bi bi-cloud-fill"></i></div>
                <div class="ult-timeline-content">
                    <span class="ult-timeline-year">2021</span>
                    <h4>Transformasi Digital Penuh</h4>
                    <p>Migrasi ke sistem berbasis cloud dan peluncuran portal layanan online untuk seluruh civitas akademika.</p>
                </div>
            </div>
            
            <div class="ult-timeline-item">
                <div class="ult-timeline-dot"><i class="bi bi-rocket-takeoff-fill"></i></div>
                <div class="ult-timeline-content">
                    <span class="ult-timeline-year">2026</span>
                    <h4>Era BAUK ULTIMATE</h4>
                    <p>Peluncuran sistem informasi terintegrasi dengan fitur AI-powered analytics, mobile-first, dan real-time monitoring.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== STRUKTUR ORGANISASI ===== -->
<section class="ult-structure">
    <div class="container ult-structure-content">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-diagram-3-fill"></i> Organisasi</span>
                <h2 class="section-title">Struktur Organisasi</h2>
                <p class="section-subtitle" style="margin:0 auto;">Susunan kepemimpinan dan pembagian tugas yang jelas</p>
            </div>
        </div>
        
        <div class="ult-org-chart">
            <!-- Level 1: Kepala BAUK -->
            <div class="ult-org-level">
                <div class="ult-org-card leader">
                    <div class="ult-org-avatar"><i class="bi bi-person-fill"></i></div>
                    <h5>Kepala BAUK</h5>
                    <p>Pimpinan Tertinggi Biro</p>
                </div>
            </div>
            
            <div class="ult-org-connector"></div>
            
            <!-- Level 2: Sekretaris -->
            <div class="ult-org-level">
                <div class="ult-org-card">
                    <div class="ult-org-avatar"><i class="bi bi-person-workspace"></i></div>
                    <h5>Sekretaris BAUK</h5>
                    <p>Koordinator Administratif</p>
                </div>
            </div>
            
            <div class="ult-org-connector"></div>
            
            <!-- Level 3: 4 Kepala Bagian -->
            <div class="ult-org-level">
                <div class="ult-org-card">
                    <div class="ult-org-avatar"><i class="bi bi-person-badge-fill"></i></div>
                    <h5>Kabag Kepegawaian</h5>
                    <p>SDM & Karir</p>
                </div>
                <div class="ult-org-card">
                    <div class="ult-org-avatar"><i class="bi bi-wallet2"></i></div>
                    <h5>Kabag Keuangan</h5>
                    <p>Anggaran & Pelaporan</p>
                </div>
                <div class="ult-org-card">
                    <div class="ult-org-avatar"><i class="bi bi-box-seam-fill"></i></div>
                    <h5>Kabag Aset & RT</h5>
                    <p>Inventaris & Sarpras</p>
                </div>
                <div class="ult-org-card">
                    <div class="ult-org-avatar"><i class="bi bi-shield-fill-check"></i></div>
                    <h5>Koordinator K3L</h5>
                    <p>Keselamatan & Lingkungan</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CORE VALUES (Nilai-Nilai) ===== -->
<section class="ult-values section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-heart-fill"></i> Nilai Kami</span>
                <h2 class="section-title">Nilai-Nilai Inti</h2>
                <p class="section-subtitle" style="margin:0 auto;">Prinsip yang kami pegang teguh dalam setiap pelayanan</p>
            </div>
        </div>
        
        <div class="ult-values-grid">
            <div class="ult-value-card">
                <div class="ult-value-icon"><i class="bi bi-hand-thumbs-up-fill"></i></div>
                <h4>Amanah</h4>
                <p>Menjaga kepercayaan dengan integritas tinggi dan tanggung jawab penuh</p>
            </div>
            <div class="ult-value-card">
                <div class="ult-value-icon"><i class="bi bi-eye-fill"></i></div>
                <h4>Transparan</h4>
                <p>Terbuka dalam setiap proses dan keputusan untuk akuntabilitas maksimal</p>
            </div>
            <div class="ult-value-card">
                <div class="ult-value-icon"><i class="bi bi-award-fill"></i></div>
                <h4>Profesional</h4>
                <p>Bekerja dengan kompetensi tinggi dan standar kualitas terbaik</p>
            </div>
            <div class="ult-value-card">
                <div class="ult-value-icon"><i class="bi bi-people-fill"></i></div>
                <h4>Melayani</h4>
                <p>Mengutamakan kepuasan civitas akademika dengan hati yang tulus</p>
            </div>
            <div class="ult-value-card">
                <div class="ult-value-icon"><i class="bi bi-arrow-repeat"></i></div>
                <h4>Berkelanjutan</h4>
                <p>Terus berinovasi dan berkembang untuk masa depan yang lebih baik</p>
            </div>
            <div class="ult-value-card">
                <div class="ult-value-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                <h4>Efisien</h4>
                <p>Mengoptimalkan sumber daya untuk hasil maksimal dengan biaya minimal</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== SERTIFIKASI & AKREDITASI ===== -->
<section class="ult-certifications section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-patch-check-fill"></i> Pengakuan</span>
                <h2 class="section-title">Sertifikasi & Akreditasi</h2>
                <p class="section-subtitle" style="margin:0 auto;">Penghargaan dan pengakuan atas kualitas layanan kami</p>
            </div>
        </div>
        
        <div class="ult-cert-grid">
            <div class="ult-cert-card">
                <div class="ult-cert-icon"><i class="bi bi-award-fill"></i></div>
                <div class="ult-cert-info">
                    <h4>Akreditasi BAN-PT</h4>
                    <p>Predikat Unggul dari Badan Akreditasi Nasional Perguruan Tinggi untuk tata kelola administrasi yang prima.</p>
                    <span class="ult-cert-number">A (Unggul)</span>
                </div>
            </div>
            
            <div class="ult-cert-card">
                <div class="ult-cert-icon"><i class="bi bi-shield-fill-check"></i></div>
                <div class="ult-cert-info">
                    <h4>ISO 9001:2015</h4>
                    <p>Sertifikasi Sistem Manajemen Mutu internasional untuk layanan administrasi dan keuangan.</p>
                    <span class="ult-cert-number">Certified</span>
                </div>
            </div>
            
            <div class="ult-cert-card">
                <div class="ult-cert-icon"><i class="bi bi-tree-fill"></i></div>
                <div class="ult-cert-info">
                    <h4>Green Campus Award</h4>
                    <p>Penghargaan kampus ramah lingkungan dari Kementerian Lingkungan Hidup untuk program K3L.</p>
                    <span class="ult-cert-number">Gold 2024</span>
                </div>
            </div>
            
            <div class="ult-cert-card">
                <div class="ult-cert-icon"><i class="bi bi-trophy-fill"></i></div>
                <div class="ult-cert-info">
                    <h4>Best Governance Award</h4>
                    <p>Penghargaan tata kelola terbaik dari Asosiasi Perguruan Tinggi Muhammadiyah (APTM).</p>
                    <span class="ult-cert-number">Winner 2025</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== GALERI KEGIATAN ===== -->
<section class="ult-gallery section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-images"></i> Dokumentasi</span>
                <h2 class="section-title">Galeri Kegiatan</h2>
                <p class="section-subtitle" style="margin:0 auto;">Momen-momen penting dalam perjalanan BAUK UNIMOF</p>
            </div>
        </div>
        
        <div class="ult-gallery-grid">
            <div class="ult-gallery-item">
                <div class="ult-gallery-placeholder"><i class="bi bi-people-fill"></i></div>
                <div class="ult-gallery-overlay">
                    <div>
                        <h5>Rapat Koordinasi Tahunan</h5>
                        <p>Evaluasi & perencanaan strategis</p>
                    </div>
                </div>
            </div>
            
            <div class="ult-gallery-item">
                <div class="ult-gallery-placeholder"><i class="bi bi-award-fill"></i></div>
                <div class="ult-gallery-overlay">
                    <div>
                        <h5>Penerimaan Sertifikasi ISO</h5>
                        <p>Momen bersejarah 2018</p>
                    </div>
                </div>
            </div>
            
            <div class="ult-gallery-item">
                <div class="ult-gallery-placeholder"><i class="bi bi-laptop-fill"></i></div>
                <div class="ult-gallery-overlay">
                    <div>
                        <h5>Peluncuran Sistem Digital</h5>
                        <p>Transformasi teknologi 2021</p>
                    </div>
                </div>
            </div>
            
            <div class="ult-gallery-item">
                <div class="ult-gallery-placeholder"><i class="bi bi-tree-fill"></i></div>
                <div class="ult-gallery-overlay">
                    <div>
                        <h5>Program Green Campus</h5>
                        <p>Inisiatif ramah lingkungan</p>
                    </div>
                </div>
            </div>
            
            <div class="ult-gallery-item">
                <div class="ult-gallery-placeholder"><i class="bi bi-person-badge-fill"></i></div>
                <div class="ult-gallery-overlay">
                    <div>
                        <h5>Pelatihan Pegawai</h5>
                        <p>Pengembangan SDM berkelanjutan</p>
                    </div>
                </div>
            </div>
            
            <div class="ult-gallery-item">
                <div class="ult-gallery-placeholder"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="ult-gallery-overlay">
                    <div>
                        <h5>Audit Internal</h5>
                        <p>Pemantauan kualitas layanan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== LEADERSHIP TEAM ===== -->
<section class="ult-leadership section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-person-badge-fill"></i> Kepemimpinan</span>
                <h2 class="section-title">Tim Pimpinan BAUK</h2>
                <p class="section-subtitle" style="margin:0 auto;">Para profesional yang memimpin transformasi layanan</p>
            </div>
        </div>
        
        <div class="ult-leader-grid">
            <div class="ult-leader-card">
                <div class="ult-leader-image"><i class="bi bi-person-circle"></i></div>
                <div class="ult-leader-info">
                    <h4>Dr. H. Ahmad Fauzi, M.Si.</h4>
                    <p>Kepala BAUK</p>
                    <small>Memimpin transformasi digital dan peningkatan kualitas layanan sejak 2020</small>
                    <div class="ult-leader-social">
                        <a href="#" title="Email"><i class="bi bi-envelope-fill"></i></a>
                        <a href="#" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="ult-leader-card">
                <div class="ult-leader-image"><i class="bi bi-person-circle"></i></div>
                <div class="ult-leader-info">
                    <h4>Hj. Siti Rahma, S.E., M.M.</h4>
                    <p>Kabag Keuangan</p>
                    <small>Ahli perencanaan anggaran dan pelaporan keuangan berbasis digital</small>
                    <div class="ult-leader-social">
                        <a href="#" title="Email"><i class="bi bi-envelope-fill"></i></a>
                        <a href="#" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="ult-leader-card">
                <div class="ult-leader-image"><i class="bi bi-person-circle"></i></div>
                <div class="ult-leader-info">
                    <h4>Muhammad Rizki, S.Kom., M.T.</h4>
                    <p>Kabag Kepegawaian</p>
                    <small>Pionir sistem e-Kepegawaian dan pengembangan karir berbasis kompetensi</small>
                    <div class="ult-leader-social">
                        <a href="#" title="Email"><i class="bi bi-envelope-fill"></i></a>
                        <a href="#" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="ult-leader-card">
                <div class="ult-leader-image"><i class="bi bi-person-circle"></i></div>
                <div class="ult-leader-info">
                    <h4>Ir. Budi Santoso, M.Eng.</h4>
                    <p>Kabag Aset & Rumah Tangga</p>
                    <small>Ahli manajemen aset dan fasilitas kampus dengan pengalaman 15+ tahun</small>
                    <div class="ult-leader-social">
                        <a href="#" title="Email"><i class="bi bi-envelope-fill"></i></a>
                        <a href="#" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CTA SECTION ===== -->
<section class="ult-about-cta">
    <div class="container">
        <div class="ult-about-cta-content">
            <h2>Siap Bermitra dengan Kami?</h2>
            <p>BAUK UNIMOF terbuka untuk kolaborasi, konsultasi, dan kunjungan studi banding dari institusi lain. Mari bersama-sama mewujudkan tata kelola universitas yang lebih baik.</p>
            <div class="ult-about-cta-actions">
                <a href="<?= url('kontak') ?>" class="ult-cta-btn ult-cta-btn-primary">
                    <i class="bi bi-envelope-fill"></i> Hubungi Kami
                </a>
                <a href="<?= url('layanan/kepegawaian') ?>" class="ult-cta-btn ult-cta-btn-outline">
                    <i class="bi bi-arrow-right-circle-fill"></i> Lihat Layanan
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ===== COUNTER ANIMATION SCRIPT ===== -->
<script>
(function() {
    'use strict';
    
    // Counter animation untuk hero stats di halaman About
    const heroStats = document.querySelectorAll('.ult-hero-stat-value[data-count]');
    if (heroStats.length === 0) return;
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                animateUltCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    heroStats.forEach(function(stat) { observer.observe(stat); });
    
    function animateUltCounter(element) {
        const target = parseInt(element.getAttribute('data-count'));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const timer = setInterval(function() {
            current += step;
            if (current >= target) {
                element.textContent = target.toLocaleString('id-ID');
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current).toLocaleString('id-ID');
            }
        }, 16);
    }
})();
</script>