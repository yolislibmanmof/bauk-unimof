<?php
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);

$val = static function (string $key, $default = '') use ($old, $data) {
    if (array_key_exists($key, $old) && $old[$key] !== '') return $old[$key];
    if (is_array($data) && isset($data[$key]) && $data[$key] !== '') return $data[$key];
    return $default;
};

$isEdit    = ($mode === 'edit');
$pageTitle = $isEdit ? 'Edit Departemen' : 'Tambah Departemen Baru';
$pageDesc  = $isEdit ? 'Memperbarui informasi unit kerja: <strong>' . htmlspecialchars($data['name']) . '</strong>' : 'Tambahkan unit kerja baru ke dalam struktur organisasi UNIMOF.';
?>

<!-- ===== ULTIMATE FORM STYLES ===== -->
<style>
/* ===== LAYOUT GRID ===== */
.ult-dept-form-layout{display:grid;grid-template-columns:260px 1fr;gap:2rem;align-items:start;}
@media (max-width:992px){.ult-dept-form-layout{grid-template-columns:1fr;}}

/* ===== STICKY SIDEBAR NAV ===== */
.ult-dept-form-nav{position:sticky;top:90px;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);padding:1rem;box-shadow:0 4px 15px rgba(0,0,0,.04);}
.ult-dept-form-nav-title{font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--admin-text-muted);font-weight:700;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--admin-border);}
.ult-dept-form-nav-link{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:var(--radius-md);color:var(--admin-text-secondary);text-decoration:none;font-size:.875rem;font-weight:600;transition:all .2s;margin-bottom:.25rem;}
.ult-dept-form-nav-link:hover{background:var(--admin-bg);color:var(--admin-text);}
.ult-dept-form-nav-link.active{background:rgba(26,82,118,.08);color:var(--admin-primary);border-left:3px solid var(--admin-primary);}
.ult-dept-form-nav-link i{width:20px;text-align:center;font-size:1rem;}

/* ===== FORM SECTIONS ===== */
.ult-dept-form-section{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);margin-bottom:1.5rem;overflow:hidden;scroll-margin-top:100px;}
.ult-dept-form-section-header{padding:1.25rem 1.5rem;background:linear-gradient(to right, var(--admin-bg), transparent);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-dept-form-section-icon{width:40px;height:40px;border-radius:var(--radius-md);background:var(--admin-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.ult-dept-form-section-title{font-size:1.1rem;font-weight:700;color:var(--admin-text);margin:0;}
.ult-dept-form-section-desc{font-size:.8rem;color:var(--admin-text-secondary);margin:.25rem 0 0;}
.ult-dept-form-section-body{padding:1.5rem;}

/* ===== ENHANCED INPUTS ===== */
.ult-dept-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem 1.5rem;}
.ult-dept-form-grid.cols-1{grid-template-columns:1fr;}
@media (max-width:768px){.ult-dept-form-grid{grid-template-columns:1fr;}}

.ult-dept-field{display:flex;flex-direction:column;gap:.375rem;}
.ult-dept-field.full{grid-column:1/-1;}
.ult-dept-label{font-size:.825rem;font-weight:600;color:var(--admin-text);display:flex;align-items:center;gap:.375rem;}
.ult-dept-label .req{color:var(--admin-danger);}

/* ===== CUSTOM ELEGANT DROPDOWN ===== */
.ult-dept-dropdown{position:relative;}
.ult-dept-dropdown-trigger{width:100%;display:flex;align-items:center;gap:.5rem;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);background:var(--admin-bg);color:var(--admin-text);font-size:.9rem;cursor:pointer;transition:all .2s;text-align:left;}
.ult-dept-dropdown-trigger:hover{border-color:var(--admin-primary);background:#fff;}
.ult-dept-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.04);}
.ult-dept-dropdown-trigger i.chevron{margin-left:auto;font-size:.7rem;color:var(--admin-text-muted);transition:transform .2s;}
.ult-dept-dropdown.open .ult-dept-dropdown-trigger{border-color:var(--admin-primary);background:#fff;box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-dept-dropdown.open .ult-dept-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-dept-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:280px;overflow-y:auto;}
.ult-dept-dropdown.open .ult-dept-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-dept-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-dept-dd-item:last-child{border-bottom:none;}
.ult-dept-dd-item:hover{background:var(--admin-bg);}
.ult-dept-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-dept-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-dept-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-dept-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}

/* ===== STANDARD INPUTS ===== */
.ult-dept-input-wrap{position:relative;}
.ult-dept-input-wrap .input-icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);font-size:1rem;pointer-events:none;transition:color .2s;z-index:2;}
.ult-dept-input-wrap input,.ult-dept-input-wrap textarea{width:100%;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);font-size:.9rem;background:var(--admin-bg);color:var(--admin-text);transition:all .2s;font-family:inherit;}
.ult-dept-input-wrap textarea{padding-left:1rem;resize:vertical;min-height:100px;line-height:1.6;}
.ult-dept-input-wrap input:focus,.ult-dept-input-wrap textarea:focus{outline:none;border-color:var(--admin-primary);background:#fff;box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-dept-input-wrap input:focus ~ .input-icon,.ult-dept-input-wrap textarea:focus ~ .input-icon{color:var(--admin-primary);}
.ult-dept-hint{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;display:flex;align-items:center;gap:.25rem;}
.ult-dept-hint i{font-size:.7rem;}

/* ===== PREVIEW CARD ===== */
.ult-dept-preview{background:linear-gradient(135deg, var(--admin-bg), var(--admin-surface));border:1px solid var(--admin-border);border-radius:var(--radius-lg);padding:1.5rem;margin-top:1rem;}
.ult-dept-preview-title{font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--admin-text-muted);font-weight:700;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
.ult-dept-preview-title i{color:var(--admin-primary);}
.ult-dept-preview-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);padding:1.25rem;display:flex;align-items:center;gap:1rem;}
.ult-dept-preview-icon{width:50px;height:50px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--admin-primary),var(--admin-primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;}
.ult-dept-preview-info{flex:1;min-width:0;}
.ult-dept-preview-name{font-weight:700;color:var(--admin-text);font-size:1rem;margin-bottom:.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-dept-preview-code{font-family:monospace;font-size:.75rem;font-weight:700;color:var(--admin-primary);background:rgba(26,82,118,.08);padding:.125rem .5rem;border-radius:4px;display:inline-block;margin-bottom:.25rem;}
.ult-dept-preview-head{font-size:.8rem;color:var(--admin-text-secondary);display:flex;align-items:center;gap:.375rem;}
.ult-dept-preview-head i{font-size:.7rem;color:var(--admin-primary);}

/* ===== STICKY ACTION BAR ===== */
.ult-dept-sticky-actions{position:sticky;bottom:0;background:var(--admin-surface);border-top:1px solid var(--admin-border);padding:1rem 1.5rem;display:flex;justify-content:flex-end;gap:.75rem;z-index:50;box-shadow:0 -4px 15px rgba(0,0,0,.05);margin:0 -1.5rem -1.5rem;border-radius:0 0 var(--radius-lg) var(--radius-lg);}
@media (max-width:992px){.ult-dept-sticky-actions{position:fixed;bottom:0;left:0;right:0;margin:0;border-radius:0;z-index:1000;padding:1rem;box-shadow:0 -4px 20px rgba(0,0,0,.1);}}

/* ===== ANIMATIONS ===== */
@keyframes ult-dept-fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.ult-dept-form-section{animation:ult-dept-fadeIn .4s ease forwards;}
@keyframes ult-dept-spin{to{transform:rotate(360deg);}}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= $pageTitle ?></h1>
        <p class="page-subtitle"><?= $pageDesc ?></p>
    </div>
    <a href="<?= url('admin/departemen') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Daftar</a>
</div>

<form method="POST" action="<?= url('admin/departemen/simpan') ?>" id="ultDeptForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
    
    <div class="ult-dept-form-layout">
        
        <!-- ===== SIDEBAR NAVIGATION ===== -->
        <aside class="ult-dept-form-nav d-none-mobile">
            <div class="ult-dept-form-nav-title">Navigasi Form</div>
            <a href="#section-identitas" class="ult-dept-form-nav-link active" data-scroll><i class="bi bi-building-fill"></i> Identitas Unit</a>
            <a href="#section-kepala" class="ult-dept-form-nav-link" data-scroll><i class="bi bi-person-badge-fill"></i> Kepala Unit</a>
            <a href="#section-deskripsi" class="ult-dept-form-nav-link" data-scroll><i class="bi bi-journal-text"></i> Deskripsi</a>
            <a href="#section-preview" class="ult-dept-form-nav-link" data-scroll><i class="bi bi-eye-fill"></i> Preview</a>
            
            <div style="margin-top:2rem;padding-top:1rem;border-top:1px solid var(--admin-border);">
                <div class="ult-dept-form-nav-title" style="margin-bottom:.5rem;">Progress</div>
                <div style="background:var(--admin-bg);border-radius:50px;height:6px;overflow:hidden;">
                    <div id="ultDeptProgress" style="width:0%;height:100%;background:var(--admin-primary);transition:width .3s;"></div>
                </div>
                <div style="font-size:.75rem;color:var(--admin-text-muted);margin-top:.375rem;text-align:right;" id="ultDeptProgressText">0% Terisi</div>
            </div>
        </aside>
        
        <!-- ===== MAIN FORM CONTENT ===== -->
        <div style="padding-bottom:80px;">
            
            <!-- SECTION 1: IDENTITAS UNIT -->
            <div class="ult-dept-form-section" id="section-identitas">
                <div class="ult-dept-form-section-header">
                    <div class="ult-dept-form-section-icon"><i class="bi bi-building-fill"></i></div>
                    <div>
                        <h3 class="ult-dept-form-section-title">Identitas Unit Kerja</h3>
                        <p class="ult-dept-form-section-desc">Informasi dasar departemen atau unit kerja.</p>
                    </div>
                </div>
                <div class="ult-dept-form-section-body">
                    <div class="ult-dept-form-grid">
                        <div class="ult-dept-field">
                            <label class="ult-dept-label">Kode Unit <span class="req">*</span></label>
                            <div class="ult-dept-input-wrap">
                                <input type="text" name="code" id="deptCode" value="<?= htmlspecialchars($val('code')) ?>" required maxlength="20" placeholder="Contoh: KEMHS" style="text-transform:uppercase;">
                                <i class="bi bi-hash input-icon"></i>
                            </div>
                            <span class="ult-dept-hint"><i class="bi bi-info-circle"></i> Kode unik, huruf kapital, maks 20 karakter</span>
                        </div>
                        
                        <div class="ult-dept-field">
                            <label class="ult-dept-label">Nama Departemen <span class="req">*</span></label>
                            <div class="ult-dept-input-wrap">
                                <input type="text" name="name" id="deptName" value="<?= htmlspecialchars($val('name')) ?>" required maxlength="100" placeholder="Contoh: Bagian Kemahasiswaan">
                                <i class="bi bi-building input-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 2: KEPALA UNIT -->
            <div class="ult-dept-form-section" id="section-kepala">
                <div class="ult-dept-form-section-header">
                    <div class="ult-dept-form-section-icon" style="background:var(--admin-accent,#e67e22);"><i class="bi bi-person-badge-fill"></i></div>
                    <div>
                        <h3 class="ult-dept-form-section-title">Kepala Unit</h3>
                        <p class="ult-dept-form-section-desc">Informasi pimpinan departemen (opsional).</p>
                    </div>
                </div>
                <div class="ult-dept-form-section-body">
                    <div class="ult-dept-form-grid cols-1">
                        <div class="ult-dept-field">
                            <label class="ult-dept-label">Nama Kepala Unit</label>
                            <div class="ult-dept-input-wrap">
                                <input type="text" name="head_name" id="deptHead" value="<?= htmlspecialchars($val('head_name')) ?>" maxlength="100" placeholder="Contoh: Dr. Ahmad Fauzi, M.Si.">
                                <i class="bi bi-person-badge input-icon"></i>
                            </div>
                            <span class="ult-dept-hint"><i class="bi bi-info-circle"></i> Nama lengkap beserta gelar akademik (opsional)</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 3: DESKRIPSI -->
            <div class="ult-dept-form-section" id="section-deskripsi">
                <div class="ult-dept-form-section-header">
                    <div class="ult-dept-form-section-icon" style="background:var(--admin-purple,#8e44ad);"><i class="bi bi-journal-text"></i></div>
                    <div>
                        <h3 class="ult-dept-form-section-title">Deskripsi & Tugas</h3>
                        <p class="ult-dept-form-section-desc">Jelaskan fungsi dan tanggung jawab unit kerja.</p>
                    </div>
                </div>
                <div class="ult-dept-form-section-body">
                    <div class="ult-dept-form-grid cols-1">
                        <div class="ult-dept-field">
                            <label class="ult-dept-label">Deskripsi Unit</label>
                            <div class="ult-dept-input-wrap">
                                <textarea name="description" id="deptDesc" rows="5" maxlength="500" placeholder="Jelaskan tugas, fungsi, dan tanggung jawab departemen ini secara singkat..."><?= htmlspecialchars($val('description')) ?></textarea>
                            </div>
                            <span class="ult-dept-hint"><i class="bi bi-info-circle"></i> Maksimal 500 karakter. Akan ditampilkan di profil departemen.</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 4: PREVIEW -->
            <div class="ult-dept-form-section" id="section-preview">
                <div class="ult-dept-form-section-header">
                    <div class="ult-dept-form-section-icon" style="background:var(--admin-success,#10b981);"><i class="bi bi-eye-fill"></i></div>
                    <div>
                        <h3 class="ult-dept-form-section-title">Preview Departemen</h3>
                        <p class="ult-dept-form-section-desc">Pratinjau tampilan departemen di sistem.</p>
                    </div>
                </div>
                <div class="ult-dept-form-section-body">
                    <div class="ult-dept-preview">
                        <div class="ult-dept-preview-title"><i class="bi bi-magic"></i> Tampilan Live Preview</div>
                        <div class="ult-dept-preview-card">
                            <div class="ult-dept-preview-icon"><i class="bi bi-building-fill"></i></div>
                            <div class="ult-dept-preview-info">
                                <div class="ult-dept-preview-name" id="previewName"><?= $isEdit ? htmlspecialchars($data['name']) : 'Nama Departemen' ?></div>
                                <div class="ult-dept-preview-code" id="previewCode"><?= $isEdit ? htmlspecialchars($data['code']) : 'KODE' ?></div>
                                <div class="ult-dept-preview-head" id="previewHead">
                                    <i class="bi bi-person-badge-fill"></i>
                                    <span><?= $isEdit && !empty($data['head_name']) ? htmlspecialchars($data['head_name']) : 'Belum ada kepala unit' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- STICKY ACTION BAR -->
            <div class="ult-dept-sticky-actions">
                <a href="<?= url('admin/departemen') ?>" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Batal</a>
                <button type="reset" class="btn btn-outline" style="border-color:var(--admin-border);color:var(--admin-text);"><i class="bi bi-arrow-counterclockwise"></i> Reset Form</button>
                <button type="submit" class="btn btn-primary" id="ultDeptSubmitBtn" style="min-width:180px;">
                    <span class="ult-spinner" style="display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:ult-dept-spin .7s linear infinite;margin-right:.5rem;"></span>
                    <i class="bi bi-save-fill"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Departemen' ?>
                </button>
            </div>
            
        </div>
    </div>
</form>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function() {
    'use strict';
    
    // ===== SMOOTH SCROLL NAV =====
    document.querySelectorAll('.ult-dept-form-nav-link[data-scroll]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) window.scrollTo({ top: target.offsetTop - 100, behavior: 'smooth' });
        });
    });
    
    // ===== SCROLL SPY =====
    const sections = document.querySelectorAll('.ult-dept-form-section');
    const navLinks = document.querySelectorAll('.ult-dept-form-nav-link');
    window.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(function(section) {
            if (window.pageYOffset >= section.offsetTop - 120) current = section.getAttribute('id');
        });
        navLinks.forEach(function(link) {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) link.classList.add('active');
        });
    });
    
    // ===== FORM PROGRESS TRACKER =====
    const form = document.getElementById('ultDeptForm');
    const progressBar = document.getElementById('ultDeptProgress');
    const progressText = document.getElementById('ultDeptProgressText');
    function updateProgress() {
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        let filled = 0;
        inputs.forEach(function(input) {
            if (input.value.trim() !== '') filled++;
        });
        const pct = inputs.length > 0 ? Math.round((filled / inputs.length) * 100) : 0;
        if (progressBar) progressBar.style.width = pct + '%';
        if (progressText) progressText.textContent = pct + '% Terisi';
    }
    form.addEventListener('input', updateProgress);
    updateProgress();
    
    // ===== LIVE PREVIEW =====
    const codeInput = document.getElementById('deptCode');
    const nameInput = document.getElementById('deptName');
    const headInput = document.getElementById('deptHead');
    const previewCode = document.getElementById('previewCode');
    const previewName = document.getElementById('previewName');
    const previewHead = document.getElementById('previewHead');
    
    if (codeInput && previewCode) {
        codeInput.addEventListener('input', function() {
            previewCode.textContent = this.value.toUpperCase() || 'KODE';
        });
    }
    if (nameInput && previewName) {
        nameInput.addEventListener('input', function() {
            previewName.textContent = this.value || 'Nama Departemen';
        });
    }
    if (headInput && previewHead) {
        headInput.addEventListener('input', function() {
            previewHead.innerHTML = '<i class="bi bi-person-badge-fill"></i><span>' + (this.value || 'Belum ada kepala unit') + '</span>';
        });
    }
    
    // ===== UPPERCASE CODE AUTO =====
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }
    
    // ===== SUBMIT LOADING STATE =====
    form.addEventListener('submit', function() {
        const btn = document.getElementById('ultDeptSubmitBtn');
        const spinner = btn.querySelector('.ult-spinner');
        const icon = btn.querySelector('.bi-save-fill');
        btn.disabled = true;
        if (spinner) spinner.style.display = 'inline-block';
        if (icon) icon.style.display = 'none';
        btn.innerHTML = (spinner ? spinner.outerHTML : '') + ' Menyimpan...';
    });
})();
</script>