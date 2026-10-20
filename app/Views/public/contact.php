<?php
/**
 * BAUK UNIMOF - Halaman Kontak ULTIMATE EDITION
 * Form kontak, info kantor, peta, jam operasional live
 */
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== PAGE HERO ===== */
.ult-contact-hero{position:relative;background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 50%,#2980b9 100%);color:#fff;padding:4.5rem 0 5.5rem;overflow:hidden;}
.ult-contact-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/svg%3E");z-index:1;}
.ult-contact-hero-shape{position:absolute;border-radius:50%;z-index:1;}
.ult-contact-hero-shape-1{width:400px;height:400px;top:-120px;right:-120px;background:radial-gradient(circle,rgba(230,126,34,.25) 0%,transparent 70%);animation:ult_cfloat 18s infinite ease-in-out;}
.ult-contact-hero-shape-2{width:320px;height:320px;bottom:-100px;left:-100px;background:radial-gradient(circle,rgba(39,174,96,.25) 0%,transparent 70%);animation:ult_cfloat 14s infinite ease-in-out reverse;}
@keyframes ult_cfloat{0%,100%{transform:translate(0,0);}50%{transform:translate(35px,-35px);}}
.ult-contact-hero-content{position:relative;z-index:2;text-align:center;}
.ult-contact-hero-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.12);backdrop-filter:blur(10px);padding:.5rem 1.25rem;border-radius:50px;font-size:.85rem;margin-bottom:1.25rem;border:1px solid rgba(255,255,255,.2);}
.ult-contact-hero h1{font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3.25rem);font-weight:700;margin-bottom:1rem;}
.ult-contact-hero p{font-size:1.05rem;max-width:650px;margin:0 auto;line-height:1.8;color:rgba(255,255,255,.9);}
.ult-contact-hero-wave{position:absolute;bottom:-1px;left:0;right:0;z-index:3;}
.ult-contact-hero-wave svg{display:block;width:100%;}

/* ===== QUICK CONTACT CARDS ===== */
.ult-quick-contacts{margin-top:-3.5rem;position:relative;z-index:5;padding-bottom:1rem;}
.ult-quick-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1.25rem;}
.ult-quick-card{background:#fff;border-radius:16px;padding:1.75rem 1.5rem;text-align:center;box-shadow:0 10px 35px rgba(0,0,0,.1);transition:all .35s ease;border:1px solid var(--color-border-light);color:var(--color-text)!important;display:block;}
.ult-quick-card:hover{transform:translateY(-6px);box-shadow:0 18px 45px rgba(0,0,0,.15);}
.ult-quick-icon{width:60px;height:60px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin:0 auto 1rem;color:#fff;}
.ult-quick-card:nth-child(1) .ult-quick-icon{background:linear-gradient(135deg,#1a5276,#2980b9);box-shadow:0 8px 20px rgba(26,82,118,.3);}
.ult-quick-card:nth-child(2) .ult-quick-icon{background:linear-gradient(135deg,#27ae60,#2ecc71);box-shadow:0 8px 20px rgba(39,174,96,.3);}
.ult-quick-card:nth-child(3) .ult-quick-icon{background:linear-gradient(135deg,#e67e22,#f39c12);box-shadow:0 8px 20px rgba(230,126,34,.3);}
.ult-quick-card:nth-child(4) .ult-quick-icon{background:linear-gradient(135deg,#25D366,#128C7E);box-shadow:0 8px 20px rgba(37,211,102,.3);}
.ult-quick-card h4{font-size:1.05rem;font-weight:700;margin-bottom:.375rem;}
.ult-quick-card p{font-size:.875rem;color:var(--color-text-secondary);margin-bottom:.75rem;line-height:1.5;}
.ult-quick-card .ult-quick-action{font-size:.8rem;font-weight:700;color:var(--color-primary);display:inline-flex;align-items:center;gap:.25rem;}
.ult-quick-card:hover .ult-quick-action{color:var(--color-accent);}

/* ===== MAIN CONTACT LAYOUT ===== */
.ult-contact-layout{display:grid;grid-template-columns:1.4fr 1fr;gap:2.5rem;margin-top:2.5rem;}

/* Form Card */
.ult-form-card{background:#fff;border-radius:20px;padding:2.5rem;box-shadow:0 10px 40px rgba(0,0,0,.08);border:1px solid var(--color-border-light);}
.ult-form-header{margin-bottom:2rem;}
.ult-form-header h2{font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:.5rem;display:flex;align-items:center;gap:.625rem;}
.ult-form-header h2 i{color:var(--color-primary);}
.ult-form-header p{color:var(--color-text-secondary);font-size:.925rem;line-height:1.7;}
.ult-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;}
.ult-field{position:relative;}
.ult-field.full{grid-column:1/-1;}
.ult-field label{display:block;font-size:.85rem;font-weight:600;color:var(--color-text);margin-bottom:.5rem;}
.ult-field label .req{color:var(--color-danger);}
.ult-input-wrap{position:relative;}
.ult-input-wrap > i{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--color-text-muted);font-size:1rem;pointer-events:none;transition:color .2s;}
.ult-field textarea ~ .ult-input-wrap > i,
.ult-input-wrap.textarea-wrap > i{top:1rem;transform:none;}
.ult-input-wrap input,.ult-input-wrap select,.ult-input-wrap textarea{width:100%;padding:.875rem 1rem .875rem 2.75rem;border:2px solid var(--color-border);border-radius:12px;font-size:.925rem;background:var(--color-bg);transition:all .2s;font-family:inherit;color:var(--color-text);}
.ult-input-wrap textarea{resize:vertical;min-height:140px;line-height:1.7;}
.ult-input-wrap input:focus,.ult-input-wrap select:focus,.ult-input-wrap textarea:focus{outline:none;border-color:var(--color-primary);background:#fff;box-shadow:0 0 0 4px rgba(26,82,118,.08);}
.ult-input-wrap input:focus ~ i,.ult-input-wrap textarea:focus ~ i{color:var(--color-primary);}
.ult-char-counter{position:absolute;right:.875rem;bottom:.625rem;font-size:.7rem;color:var(--color-text-muted);background:rgba(255,255,255,.9);padding:0 .375rem;border-radius:4px;}
.ult-char-counter.limit{color:var(--color-danger);font-weight:700;}
.ult-honeypot{position:absolute!important;left:-9999px!important;width:1px;height:1px;overflow:hidden;}
.ult-form-footer{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-top:1.5rem;flex-wrap:wrap;}
.ult-form-note{font-size:.8rem;color:var(--color-text-muted);display:flex;align-items:center;gap:.375rem;max-width:320px;line-height:1.5;}
.ult-form-note i{color:var(--color-success);flex-shrink:0;}
.ult-submit-btn{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-light));color:#fff;border:none;padding:1rem 2.25rem;border-radius:50px;font-size:1rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.625rem;transition:all .3s;box-shadow:0 8px 25px rgba(26,82,118,.3);}
.ult-submit-btn:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(26,82,118,.4);}
.ult-submit-btn:disabled{opacity:.7;cursor:not-allowed;transform:none;}
.ult-submit-btn .ult-spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:ult_spin .7s linear infinite;}
.ult-submit-btn.loading .ult-spinner{display:inline-block;}
.ult-submit-btn.loading .ult-btn-icon{display:none;}
@keyframes ult_spin{to{transform:rotate(360deg);}}

/* Sidebar Info */
.ult-contact-sidebar{display:flex;flex-direction:column;gap:1.5rem;}
.ult-info-card{background:#fff;border-radius:16px;padding:1.75rem;box-shadow:0 6px 25px rgba(0,0,0,.06);border:1px solid var(--color-border-light);}
.ult-info-card-header{display:flex;align-items:center;gap:.625rem;font-weight:700;font-size:1.05rem;margin-bottom:1.25rem;padding-bottom:.875rem;border-bottom:2px solid var(--color-border-light);}
.ult-info-card-header i{color:var(--color-primary);font-size:1.15rem;}
.ult-info-row{display:flex;gap:.875rem;margin-bottom:1.125rem;align-items:flex-start;}
.ult-info-row:last-child{margin-bottom:0;}
.ult-info-row-icon{width:40px;height:40px;border-radius:10px;background:var(--color-primary-50);color:var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.ult-info-row-content small{display:block;font-size:.75rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.125rem;}
.ult-info-row-content strong{font-size:.925rem;color:var(--color-text);line-height:1.5;display:block;}
.ult-info-row-content a{color:var(--color-primary);}
.ult-copy-btn{background:none;border:none;color:var(--color-text-muted);cursor:pointer;font-size:.85rem;margin-left:.375rem;transition:color .2s;}
.ult-copy-btn:hover{color:var(--color-primary);}

/* Live Status */
.ult-live-status{display:flex;align-items:center;gap:.625rem;padding:.875rem 1rem;border-radius:12px;font-size:.875rem;font-weight:600;margin-bottom:1.25rem;}
.ult-live-status.open{background:#d1fae5;color:#065f46;}
.ult-live-status.closed{background:#fee2e2;color:#991b1b;}
.ult-live-status-dot{width:9px;height:9px;border-radius:50%;background:currentColor;animation:ult_cblink 1.5s infinite;}
@keyframes ult_cblink{0%,100%{opacity:1;}50%{opacity:.35;}}
.ult-hours-table{width:100%;font-size:.875rem;border-collapse:collapse;}
.ult-hours-table td{padding:.5rem 0;border-bottom:1px dashed var(--color-border-light);color:var(--color-text-secondary);}
.ult-hours-table tr:last-child td{border-bottom:none;}
.ult-hours-table td:last-child{text-align:right;font-weight:600;color:var(--color-text);}
.ult-hours-table tr.today td{color:var(--color-primary);font-weight:700;}
.ult-hours-table tr.today td:first-child::after{content:'(Hari ini)';font-size:.7rem;margin-left:.375rem;opacity:.8;}

/* Social */
.ult-social-row{display:flex;gap:.625rem;flex-wrap:wrap;}
.ult-social-btn{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.15rem;transition:all .25s;}
.ult-social-btn:hover{transform:translateY(-3px) scale(1.05);}
.ult-social-btn.fb{background:#1877f2;}
.ult-social-btn.ig{background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#bc1888);}
.ult-social-btn.yt{background:#ff0000;}
.ult-social-btn.tt{background:#010101;}
.ult-social-btn.wa{background:#25D366;}

/* ===== DEPARTMENT CONTACTS ===== */
.ult-dept-section{background:var(--color-bg);padding:4rem 0;margin-top:4rem;}
.ult-dept-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem;margin-top:2.5rem;}
.ult-dept-card{background:#fff;border-radius:16px;padding:1.75rem;box-shadow:0 4px 20px rgba(0,0,0,.05);border-top:4px solid var(--color-primary);transition:all .3s;}
.ult-dept-card:hover{transform:translateY(-5px);box-shadow:0 12px 35px rgba(0,0,0,.1);}
.ult-dept-card:nth-child(2){border-top-color:var(--color-accent);}
.ult-dept-card:nth-child(3){border-top-color:var(--color-green);}
.ult-dept-card:nth-child(4){border-top-color:#8e44ad;}
.ult-dept-card h4{font-size:1.05rem;font-weight:700;margin-bottom:.375rem;display:flex;align-items:center;gap:.5rem;}
.ult-dept-card h4 i{color:var(--color-primary);}
.ult-dept-card > p{font-size:.85rem;color:var(--color-text-secondary);margin-bottom:1rem;line-height:1.6;}
.ult-dept-contact{font-size:.85rem;color:var(--color-text-secondary);display:flex;align-items:center;gap:.5rem;margin-bottom:.375rem;}
.ult-dept-contact i{color:var(--color-accent);width:14px;}

/* ===== MAP SECTION ===== */
.ult-map-section{position:relative;}
.ult-map-wrapper{position:relative;height:420px;background:#e2e8f0;}
.ult-map-wrapper iframe{width:100%;height:100%;border:0;display:block;}
.ult-map-overlay-card{position:absolute;top:2rem;left:2rem;background:#fff;border-radius:16px;padding:1.5rem;box-shadow:0 15px 40px rgba(0,0,0,.2);max-width:320px;z-index:5;}
.ult-map-overlay-card h4{font-size:1.05rem;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem;}
.ult-map-overlay-card h4 i{color:var(--color-accent);}
.ult-map-overlay-card p{font-size:.85rem;color:var(--color-text-secondary);line-height:1.6;margin-bottom:1rem;}
.ult-map-actions{display:flex;gap:.5rem;flex-wrap:wrap;}
.ult-map-actions a{font-size:.8rem;font-weight:600;padding:.5rem .875rem;border-radius:8px;display:inline-flex;align-items:center;gap:.375rem;transition:all .2s;}
.ult-map-actions a.primary{background:var(--color-primary);color:#fff!important;}
.ult-map-actions a.primary:hover{background:var(--color-primary-dark);}
.ult-map-actions a.secondary{background:var(--color-bg);color:var(--color-text)!important;border:1px solid var(--color-border);}
.ult-map-actions a.secondary:hover{border-color:var(--color-primary);color:var(--color-primary)!important;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-contact-layout{grid-template-columns:1fr;}
    .ult-map-overlay-card{position:static;max-width:none;margin:1rem;border-radius:12px;}
    .ult-map-wrapper{height:auto;}
}
@media (max-width:768px){
    .ult-form-grid{grid-template-columns:1fr;}
    .ult-form-card{padding:1.75rem;}
    .ult-quick-contacts{margin-top:-2.5rem;}
    .ult-form-footer{flex-direction:column;align-items:stretch;}
    .ult-submit-btn{justify-content:center;}
}
</style>

<!-- ===== PAGE HERO ===== -->
<section class="ult-contact-hero">
    <div class="ult-contact-hero-shape ult-contact-hero-shape-1"></div>
    <div class="ult-contact-hero-shape ult-contact-hero-shape-2"></div>
    <div class="container">
        <div class="ult-contact-hero-content">
            <span class="ult-contact-hero-badge"><i class="bi bi-headset"></i> Layanan Pelanggan</span>
            <h1>Hubungi <span style="color:#f39c12;">Kami</span></h1>
            <p>Pertanyaan, saran, atau kebutuhan layanan? Tim BAUK UNIMOF siap membantu Anda pada jam kerja maupun melalui kanal digital 24/7.</p>
        </div>
    </div>
    <div class="ult-contact-hero-wave">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 80L60 70C120 60 240 40 360 30C480 20 600 20 720 25C840 30 960 40 1080 45C1200 50 1320 50 1380 50L1440 50V80H1380C1320 80 1200 80 1080 80C960 80 840 80 720 80C600 80 480 80 360 80C240 80 120 80 60 80H0Z" fill="#f8f9fa"/>
        </svg>
    </div>
</section>

<!-- ===== QUICK CONTACT CARDS ===== -->
<section class="ult-quick-contacts">
    <div class="container">
        <div class="ult-quick-grid">
            <a href="https://maps.google.com/?q=Universitas+Muhammadiyah+Maumere" target="_blank" class="ult-quick-card">
                <div class="ult-quick-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <h4>Kunjungi Kantor</h4>
                <p>Jl. Ahmad Yani No. 17, Maumere, Sikka, NTT</p>
                <span class="ult-quick-action">Lihat Peta <i class="bi bi-arrow-right"></i></span>
            </a>
            <a href="tel:038221234" class="ult-quick-card">
                <div class="ult-quick-icon"><i class="bi bi-telephone-fill"></i></div>
                <h4>Telepon Kami</h4>
                <p>(0382) 21234 — Senin s.d. Jumat, 08:00-16:00 WITA</p>
                <span class="ult-quick-action">Panggil Sekarang <i class="bi bi-arrow-right"></i></span>
            </a>
            <a href="mailto:bauk@unimof.ac.id" class="ult-quick-card">
                <div class="ult-quick-icon"><i class="bi bi-envelope-fill"></i></div>
                <h4>Kirim Email</h4>
                <p>bauk@unimof.ac.id — Respon maks. 1x24 jam kerja</p>
                <span class="ult-quick-action">Tulis Email <i class="bi bi-arrow-right"></i></span>
            </a>
            <a href="https://wa.me/6281234567890" target="_blank" class="ult-quick-card">
                <div class="ult-quick-icon"><i class="bi bi-whatsapp"></i></div>
                <h4>Chat WhatsApp</h4>
                <p>+62 812-3456-7890 — Respon cepat pada jam kerja</p>
                <span class="ult-quick-action">Mulai Chat <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>

<!-- ===== MAIN CONTACT SECTION ===== -->
<section class="section" style="padding-top:2.5rem;">
    <div class="container">
        <div class="ult-contact-layout">
            
            <!-- ===== FORM ===== -->
            <div class="ult-form-card">
                <div class="ult-form-header">
                    <h2><i class="bi bi-pencil-square"></i> Kirim Pesan</h2>
                    <p>Isi formulir di bawah ini dengan lengkap. Pesan Anda akan diterima langsung oleh tim layanan BAUK dan dijawab melalui email atau telepon.</p>
                </div>
                
                <form method="POST" action="<?= url('kontak/submit') ?>" id="ultContactForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
                    <!-- Honeypot anti-spam (field tersembunyi, jangan diisi) -->
                    <input type="text" name="website_url" class="ult-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
                    
                    <div class="ult-form-grid">
                        <div class="ult-field">
                            <label for="cf-name">Nama Lengkap <span class="req">*</span></label>
                            <div class="ult-input-wrap">
                                <input type="text" id="cf-name" name="name" required maxlength="100" placeholder="Contoh: Budi Santoso">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        </div>
                        
                        <div class="ult-field">
                            <label for="cf-email">Alamat Email <span class="req">*</span></label>
                            <div class="ult-input-wrap">
                                <input type="email" id="cf-email" name="email" required maxlength="100" placeholder="nama@email.com">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                        </div>
                        
                        <div class="ult-field">
                            <label for="cf-phone">No. HP / WhatsApp <span style="font-weight:400;color:var(--color-text-muted);">(opsional)</span></label>
                            <div class="ult-input-wrap">
                                <input type="tel" id="cf-phone" name="phone" maxlength="20" placeholder="08xx-xxxx-xxxx">
                                <i class="bi bi-phone-fill"></i>
                            </div>
                        </div>
                        
                        <div class="ult-field">
                            <label for="cf-dept">Tujuan Bagian</label>
                            <div class="ult-input-wrap">
                                <select id="cf-dept" name="department_target">
                                    <option value="">— Umum / Front Office —</option>
                                    <option value="Kepegawaian">Bagian Kepegawaian</option>
                                    <option value="Keuangan">Bagian Keuangan</option>
                                    <option value="Aset">Bagian Aset & Rumah Tangga</option>
                                    <option value="K3L">Tim K3L</option>
                                </select>
                                <i class="bi bi-diagram-3-fill"></i>
                            </div>
                        </div>
                        
                        <div class="ult-field full">
                            <label for="cf-subject">Subjek Pesan <span class="req">*</span></label>
                            <div class="ult-input-wrap">
                                <input type="text" id="cf-subject" name="subject" required maxlength="200" placeholder="Ringkasan kebutuhan Anda">
                                <i class="bi bi-chat-square-text-fill"></i>
                            </div>
                        </div>
                        
                        <div class="ult-field full">
                            <label for="cf-message">Isi Pesan <span class="req">*</span></label>
                            <div class="ult-input-wrap textarea-wrap">
                                <textarea id="cf-message" name="message" rows="6" required maxlength="1000" placeholder="Jelaskan kebutuhan atau pertanyaan Anda secara detail (min. 10 karakter)..."></textarea>
                                <i class="bi bi-text-paragraph"></i>
                                <span class="ult-char-counter" id="ultCharCounter">0 / 1000</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ult-form-footer">
                        <div class="ult-form-note">
                            <i class="bi bi-shield-fill-check"></i>
                            Data Anda terlindungi & hanya digunakan untuk keperluan layanan.
                        </div>
                        <button type="submit" class="ult-submit-btn" id="ultSubmitBtn">
                            <span class="ult-spinner"></span>
                            <i class="bi bi-send-fill ult-btn-icon"></i>
                            Kirim Pesan
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- ===== SIDEBAR ===== -->
            <aside class="ult-contact-sidebar">
                
                <!-- Office Info -->
                <div class="ult-info-card">
                    <div class="ult-info-card-header"><i class="bi bi-building-fill"></i> Informasi Kantor</div>
                    
                    <div class="ult-info-row">
                        <div class="ult-info-row-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div class="ult-info-row-content">
                            <small>Alamat</small>
                            <strong id="ultAddress">Jl. Ahmad Yani No. 17, Maumere, Sikka, NTT 86111</strong>
                            <button class="ult-copy-btn" onclick="ultCopyText('ultAddress')" title="Salin alamat"><i class="bi bi-clipboard"></i></button>
                        </div>
                    </div>
                    
                    <div class="ult-info-row">
                        <div class="ult-info-row-icon"><i class="bi bi-telephone-fill"></i></div>
                        <div class="ult-info-row-content">
                            <small>Telepon / Fax</small>
                            <strong><a href="tel:038221234">(0382) 21234</a></strong>
                        </div>
                    </div>
                    
                    <div class="ult-info-row">
                        <div class="ult-info-row-icon"><i class="bi bi-envelope-fill"></i></div>
                        <div class="ult-info-row-content">
                            <small>Email Resmi</small>
                            <strong id="ultEmail">bauk@unimof.ac.id</strong>
                            <button class="ult-copy-btn" onclick="ultCopyText('ultEmail')" title="Salin email"><i class="bi bi-clipboard"></i></button>
                        </div>
                    </div>
                    
                    <div class="ult-info-row">
                        <div class="ult-info-row-icon"><i class="bi bi-globe2"></i></div>
                        <div class="ult-info-row-content">
                            <small>Website</small>
                            <strong><a href="<?= url() ?>" target="_blank">bauk.unimof.ac.id</a></strong>
                        </div>
                    </div>
                </div>
                
                <!-- Operating Hours with LIVE status -->
                <div class="ult-info-card">
                    <div class="ult-info-card-header"><i class="bi bi-clock-fill"></i> Jam Operasional</div>
                    
                    <div class="ult-live-status closed" id="ultLiveStatus">
                        <span class="ult-live-status-dot"></span>
                        <span id="ultLiveStatusText">Memeriksa status...</span>
                    </div>
                    
                    <table class="ult-hours-table" id="ultHoursTable">
                        <tr data-day="1"><td>Senin</td><td>08:00 - 16:00</td></tr>
                        <tr data-day="2"><td>Selasa</td><td>08:00 - 16:00</td></tr>
                        <tr data-day="3"><td>Rabu</td><td>08:00 - 16:00</td></tr>
                        <tr data-day="4"><td>Kamis</td><td>08:00 - 16:00</td></tr>
                        <tr data-day="5"><td>Jumat</td><td>08:00 - 16:00</td></tr>
                        <tr data-day="6"><td>Sabtu</td><td>Tutup</td></tr>
                        <tr data-day="0"><td>Minggu</td><td>Tutup</td></tr>
                    </table>
                </div>
                
                <!-- Social Media -->
                <div class="ult-info-card">
                    <div class="ult-info-card-header"><i class="bi bi-share-fill"></i> Media Sosial</div>
                    <div class="ult-social-row">
                        <a href="#" class="ult-social-btn fb" title="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="ult-social-btn ig" title="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="ult-social-btn yt" title="YouTube"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="ult-social-btn tt" title="TikTok"><i class="bi bi-tiktok"></i></a>
                        <a href="https://wa.me/6281234567890" class="ult-social-btn wa" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                
            </aside>
        </div>
    </div>
</section>

<!-- ===== DEPARTMENT CONTACTS ===== -->
<section class="ult-dept-section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-diagram-3-fill"></i> Kontak Langsung</span>
                <h2 class="section-title">Kontak Per Bagian</h2>
                <p class="section-subtitle" style="margin:0 auto;">Hubungi langsung bagian yang sesuai dengan kebutuhan Anda</p>
            </div>
        </div>
        
        <div class="ult-dept-grid">
            <div class="ult-dept-card">
                <h4><i class="bi bi-person-badge-fill"></i> Kepegawaian</h4>
                <p>Administrasi pegawai, cuti, mutasi, pengembangan karir, dan kesejahteraan.</p>
                <div class="ult-dept-contact"><i class="bi bi-envelope-fill"></i> kepegawaian@unimof.ac.id</div>
                <div class="ult-dept-contact"><i class="bi bi-telephone-fill"></i> (0382) 21234 ext. 101</div>
            </div>
            <div class="ult-dept-card">
                <h4><i class="bi bi-wallet2"></i> Keuangan</h4>
                <p>Anggaran, pembayaran, penggajian, pelaporan, dan verifikasi transaksi.</p>
                <div class="ult-dept-contact"><i class="bi bi-envelope-fill"></i> keuangan@unimof.ac.id</div>
                <div class="ult-dept-contact"><i class="bi bi-telephone-fill"></i> (0382) 21234 ext. 102</div>
            </div>
            <div class="ult-dept-card">
                <h4><i class="bi bi-house-gear-fill"></i> Aset & Rumah Tangga</h4>
                <p>Inventaris aset, pemeliharaan fasilitas, kebersihan, dan penggunaan ruang.</p>
                <div class="ult-dept-contact"><i class="bi bi-envelope-fill"></i> aset@unimof.ac.id</div>
                <div class="ult-dept-contact"><i class="bi bi-telephone-fill"></i> (0382) 21234 ext. 103</div>
            </div>
            <div class="ult-dept-card">
                <h4><i class="bi bi-shield-fill-check"></i> Tim K3L</h4>
                <p>Keselamatan kerja, kesehatan lingkungan, mitigasi bencana, dan laporan darurat.</p>
                <div class="ult-dept-contact"><i class="bi bi-envelope-fill"></i> k3l@unimof.ac.id</div>
                <div class="ult-dept-contact"><i class="bi bi-telephone-fill"></i> (0382) 21234 ext. 119</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== MAP SECTION ===== -->
<section class="ult-map-section">
    <div class="ult-map-wrapper">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3950.123!2d122.2!3d-8.6!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOMKwMzYnMDAuMCJTIDEyMsKwMTInMDAuMCJF!5e0!3m2!1sid!2sid!4v1234567890" allowfullscreen="" loading="lazy" title="Lokasi BAUK UNIMOF"></iframe>
        <div class="ult-map-overlay-card">
            <h4><i class="bi bi-geo-alt-fill"></i> Kantor BAUK UNIMOF</h4>
            <p>Jl. Ahmad Yani No. 17, Maumere, Kabupaten Sikka, Nusa Tenggara Timur 86111</p>
            <div class="ult-map-actions">
                <a href="https://maps.google.com/?q=Universitas+Muhammadiyah+Maumere" target="_blank" class="primary"><i class="bi bi-sign-turn-right-fill"></i> Rute</a>
                <a href="https://maps.google.com/?q=Universitas+Muhammadiyah+Maumere" target="_blank" class="secondary"><i class="bi bi-box-arrow-up-right"></i> Buka Maps</a>
            </div>
        </div>
    </div>
</section>

<!-- ===== SCRIPTS ===== -->
<script>
(function() {
    'use strict';
    
    // ===== CHARACTER COUNTER =====
    const messageField = document.getElementById('cf-message');
    const counter = document.getElementById('ultCharCounter');
    if (messageField && counter) {
        messageField.addEventListener('input', function() {
            const len = this.value.length;
            counter.textContent = len + ' / 1000';
            counter.classList.toggle('limit', len > 900);
        });
    }
    
    // ===== DEPARTMENT -> SUBJECT PREFIX =====
    const form = document.getElementById('ultContactForm');
    const deptSelect = document.getElementById('cf-dept');
    if (form && deptSelect) {
        form.addEventListener('submit', function(e) {
            // Honeypot check: jika terisi => bot, batalkan silently
            const honeypot = form.querySelector('[name="website_url"]');
            if (honeypot && honeypot.value !== '') {
                e.preventDefault();
                return false;
            }
            
            // Validasi client-side sederhana
            const name = form.querySelector('[name="name"]');
            const email = form.querySelector('[name="email"]');
            const subject = form.querySelector('[name="subject"]');
            const message = form.querySelector('[name="message"]');
            let valid = true;
            
            [name, email, subject, message].forEach(function(f) {
                f.style.borderColor = '';
            });
            
            if (!name.value.trim()) { name.style.borderColor = '#ef4444'; valid = false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) { email.style.borderColor = '#ef4444'; valid = false; }
            if (!subject.value.trim()) { subject.style.borderColor = '#ef4444'; valid = false; }
            if (message.value.trim().length < 10) { message.style.borderColor = '#ef4444'; valid = false; }
            
            if (!valid) {
                e.preventDefault();
                if (typeof showToast === 'function') showToast('Mohon lengkapi field yang ditandai merah.', 'error');
                return false;
            }
            
            // Prepend tujuan bagian ke subjek
            if (deptSelect.value && !subject.value.startsWith('[')) {
                subject.value = '[' + deptSelect.value + '] ' + subject.value;
            }
            
            // Loading state
            const btn = document.getElementById('ultSubmitBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    }
    
    // ===== LIVE OPEN/CLOSED STATUS (WITA = UTC+8) =====
    function updateLiveStatus() {
        const statusBox = document.getElementById('ultLiveStatus');
        const statusText = document.getElementById('ultLiveStatusText');
        if (!statusBox || !statusText) return;
        
        try {
            const now = new Date();
            const wita = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Makassar' }));
            const day = wita.getDay();           // 0=Minggu
            const hour = wita.getHours();
            const minute = wita.getMinutes();
            const timeVal = hour + (minute / 60);
            
            const isWorkday = day >= 1 && day <= 5;
            const isOpen = isWorkday && timeVal >= 8 && timeVal < 16;
            
            // Highlight baris hari ini
            document.querySelectorAll('#ultHoursTable tr').forEach(function(tr) {
                tr.classList.toggle('today', parseInt(tr.dataset.day) === day);
            });
            
            if (isOpen) {
                statusBox.className = 'ult-live-status open';
                statusText.textContent = 'Buka Sekarang — tutup pukul 16:00 WITA';
            } else {
                statusBox.className = 'ult-live-status closed';
                statusText.textContent = isWorkday
                    ? (timeVal < 8 ? 'Tutup — buka pukul 08:00 WITA' : 'Tutup — buka besok 08:00 WITA')
                    : 'Tutup — buka Senin 08:00 WITA';
            }
        } catch (e) {
            statusText.textContent = 'Senin - Jumat: 08:00 - 16:00 WITA';
        }
    }
    updateLiveStatus();
    setInterval(updateLiveStatus, 60000);
})();

// ===== COPY TEXT HELPER =====
function ultCopyText(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    navigator.clipboard.writeText(el.textContent.trim()).then(function() {
        if (typeof showToast === 'function') showToast('Berhasil disalin ke clipboard!', 'success');
    });
}
</script>