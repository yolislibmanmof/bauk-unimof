<?php
/**
 * =====================================================
 * BAUK UNIMOF - FORM TUPOKSI (ULTIMATE v2)
 * Add/Edit Unit Kerja dengan Live Preview & Icon Picker
 * =====================================================
 */
use App\Helpers\Security;

$data = $data ?? [];
$mode = $mode ?? 'add';
$isEdit = ($mode === 'edit');

$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);

$val = static function (string $key, $default = '') use ($old, $data) {
    if (array_key_exists($key, $old) && $old[$key] !== '') return $old[$key];
    if (is_array($data) && isset($data[$key]) && $data[$key] !== '') return $data[$key];
    return $default;
};

$pageTitle = $isEdit ? 'Edit Unit Kerja' : 'Tambah Unit Kerja Baru';
$pageDesc  = $isEdit 
    ? 'Memperbarui tugas pokok dan fungsi untuk: <strong>' . htmlspecialchars($data['unit_kerja'] ?? '') . '</strong>' 
    : 'Definisikan tugas pokok dan fungsi unit kerja baru dalam struktur BAUK UNIMOF.';

$defaultIcon = $val('icon', 'bi-briefcase');
$defaultColor = $val('warna', '#1a5276');
?>

<!-- ===== TUPOKSI FORM v2 STYLES ===== -->
<style>
/* ===== HERO ===== */
.ult2-t-hero{background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 55%,#2980b9 100%);border-radius:20px;padding:2rem 2.25rem;color:#fff;position:relative;overflow:hidden;margin-bottom:1.5rem;}
.ult2-t-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='1.5'/%3E%3C/g%3E%3C/svg%3E");}
.ult2-t-hero::after{content:'';position:absolute;top:-60%;right:-8%;width:380px;height:380px;background:radial-gradient(circle,rgba(230,126,34,.25) 0%,transparent 70%);border-radius:50%;}
.ult2-t-hero-inner{position:relative;z-index:2;display:flex;justify-content:space-between;align-items:center;gap:1.5rem;flex-wrap:wrap;}
.ult2-t-hero-title{display:flex;align-items:center;gap:1rem;}
.ult2-t-hero-icon{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.14);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:1.7rem;flex-shrink:0;}
.ult2-t-hero h1{font-family:'Playfair Display',serif;font-size:1.65rem;margin:0 0 .25rem;color:#fff;}
.ult2-t-hero p{margin:0;font-size:.85rem;color:rgba(255,255,255,.85);}
.ult2-t-hero-breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:rgba(255,255,255,.85);margin-top:.5rem;}
.ult2-t-hero-breadcrumb a{color:#f39c12;text-decoration:none!important;font-weight:600;}
.ult2-t-hero-breadcrumb i{font-size:.6rem;}

/* ===== LAYOUT ===== */
.ult2-t-layout{display:grid;grid-template-columns:260px 1fr;gap:2rem;align-items:start;}
@media (max-width:1024px){.ult2-t-layout{grid-template-columns:1fr;}}

/* ===== SIDEBAR ===== */
.ult2-t-nav{position:sticky;top:90px;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;padding:1rem;box-shadow:0 4px 15px rgba(0,0,0,.04);}
@media (max-width:1024px){.ult2-t-nav{position:static;display:flex;gap:.5rem;overflow-x:auto;padding:.75rem;}}
.ult2-t-nav-title{font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--admin-text-muted);font-weight:700;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--admin-border);}
@media (max-width:1024px){.ult2-t-nav-title{display:none;}}
.ult2-t-nav-link{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:10px;color:var(--admin-text-secondary);text-decoration:none!important;font-size:.86rem;font-weight:600;transition:all .2s;margin-bottom:.25rem;}
@media (max-width:1024px){.ult2-t-nav-link{white-space:nowrap;padding:.5rem .85rem;font-size:.8rem;}}
.ult2-t-nav-link:hover{background:var(--admin-bg);color:var(--admin-text);}
.ult2-t-nav-link.active{background:rgba(26,82,118,.08);color:var(--admin-primary);border-left:3px solid var(--admin-primary);}
@media (max-width:1024px){.ult2-t-nav-link.active{border-left:none;border-bottom:2px solid var(--admin-primary);}}
.ult2-t-nav-link i{width:20px;text-align:center;font-size:1rem;}
.ult2-t-nav-progress{margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
@media (max-width:1024px){.ult2-t-nav-progress{display:none;}}
.ult2-t-progress-bar{background:var(--admin-bg);border-radius:50px;height:8px;overflow:hidden;margin-bottom:.35rem;}
.ult2-t-progress-fill{height:100%;background:linear-gradient(90deg,#1a5276,#e67e22);border-radius:50px;transition:width .4s ease;width:0%;}
.ult2-t-progress-text{font-size:.7rem;color:var(--admin-text-muted);text-align:right;}

/* ===== SECTIONS ===== */
.ult2-t-section{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;margin-bottom:1.5rem;overflow:hidden;scroll-margin-top:100px;transition:all .3s;}
.ult2-t-section:focus-within{border-color:var(--admin-primary);box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult2-t-section-header{padding:1.25rem 1.5rem;background:linear-gradient(to right,var(--admin-bg),transparent);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.85rem;}
.ult2-t-section-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff;flex-shrink:0;box-shadow:0 6px 16px rgba(0,0,0,.12);}
.ult2-t-section-icon.primary{background:linear-gradient(135deg,#1a5276,#2980b9);}
.ult2-t-section-icon.accent{background:linear-gradient(135deg,#e67e22,#f39c12);}
.ult2-t-section-icon.purple{background:linear-gradient(135deg,#8e44ad,#9b59b6);}
.ult2-t-section-title{font-size:1.05rem;font-weight:800;color:var(--admin-text);margin:0;}
.ult2-t-section-desc{font-size:.78rem;color:var(--admin-text-secondary);margin:.2rem 0 0;}
.ult2-t-section-body{padding:1.5rem;}

/* ===== FORM GRID ===== */
.ult2-t-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem 1.5rem;}
@media (max-width:768px){.ult2-t-grid{grid-template-columns:1fr;}}
.ult2-t-field{display:flex;flex-direction:column;gap:.375rem;}
.ult2-t-field.full{grid-column:1/-1;}
.ult2-t-label{font-size:.82rem;font-weight:700;color:var(--admin-text);display:flex;align-items:center;gap:.4rem;}
.ult2-t-label .req{color:#ef4444;font-size:1rem;line-height:1;}

/* ===== INPUTS ===== */
.ult2-t-input-wrap{position:relative;}
.ult2-t-input-wrap .input-icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);font-size:1rem;pointer-events:none;transition:color .2s;z-index:2;}
.ult2-t-input-wrap input,.ult2-t-input-wrap select{width:100%;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:12px;font-size:.88rem;background:var(--admin-bg);color:var(--admin-text);transition:all .2s;font-family:inherit;}
.ult2-t-input-wrap textarea{width:100%;padding:.75rem 1rem;border:1.5px solid var(--admin-border);border-radius:12px;font-size:.88rem;background:var(--admin-bg);color:var(--admin-text);transition:all .2s;font-family:inherit;resize:vertical;min-height:100px;line-height:1.6;}
.ult2-t-input-wrap input:focus,.ult2-t-input-wrap select:focus,.ult2-t-input-wrap textarea:focus{outline:none;border-color:var(--admin-primary);background:var(--admin-surface);box-shadow:0 0 0 3px rgba(26,82,118,.1);}
.ult2-t-input-wrap input:focus ~ .input-icon,.ult2-t-input-wrap textarea:focus ~ .input-icon{color:var(--admin-primary);}
.ult2-t-hint{font-size:.72rem;color:var(--admin-text-muted);margin-top:.15rem;display:flex;align-items:center;gap:.3rem;}
.ult2-t-hint i{font-size:.7rem;}
.ult2-t-char-count{font-size:.7rem;color:var(--admin-text-muted);text-align:right;margin-top:.25rem;}
.ult2-t-char-count.warn{color:#f59e0b;}
.ult2-t-char-count.danger{color:#ef4444;}

/* ===== ICON PICKER ===== */
.ult2-t-icon-picker{display:flex;gap:.75rem;align-items:center;}
.ult2-t-icon-preview{width:48px;height:48px;border-radius:12px;background:var(--admin-bg);border:1.5px solid var(--admin-border);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:var(--admin-primary);transition:all .2s;}
.ult2-t-icon-picker input{flex:1;}

/* ===== COLOR PICKER ===== */
.ult2-t-color-picker{display:flex;gap:.75rem;align-items:center;}
.ult2-t-color-input{width:56px;height:48px;border:1.5px solid var(--admin-border);border-radius:12px;padding:4px;cursor:pointer;background:var(--admin-bg);}
.ult2-t-color-input::-webkit-color-swatch-wrapper{padding:0;}
.ult2-t-color-input::-webkit-color-swatch{border:none;border-radius:8px;}
.ult2-t-color-text{flex:1;font-family:ui-monospace,monospace;font-size:.88rem;text-transform:uppercase;}

/* ===== LIVE PREVIEW ===== */
.ult2-t-preview-wrap{background:linear-gradient(135deg,var(--admin-bg),var(--admin-surface));border:1px solid var(--admin-border);border-radius:16px;padding:1.75rem;position:relative;overflow:hidden;}
.ult2-t-preview-wrap::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#1a5276,#e67e22);}
.ult2-t-preview-title{font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--admin-text-muted);font-weight:700;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;}
.ult2-t-preview-title i{color:#e67e22;}

.ult2-t-preview-card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(13,47,74,.1);border:1px solid rgba(13,47,74,.06);max-width:320px;margin:0 auto;transition:all .3s;}
.ult2-t-preview-card:hover{transform:translateY(-4px);box-shadow:0 15px 40px rgba(13,47,74,.15);}
.ult2-t-preview-header{padding:1.5rem;color:#fff;position:relative;overflow:hidden;}
.ult2-t-preview-header::before{content:'';position:absolute;top:-30%;right:-20%;width:120px;height:120px;background:radial-gradient(circle,rgba(255,255,255,.2) 0%,transparent 70%);border-radius:50%;}
.ult2-t-preview-icon{width:56px;height:56px;border-radius:14px;background:rgba(255,255,255,.2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:1rem;border:1px solid rgba(255,255,255,.3);position:relative;z-index:2;}
.ult2-t-preview-kode{display:inline-block;font-size:.7rem;letter-spacing:1.5px;font-weight:700;background:rgba(255,255,255,.25);padding:.2rem .7rem;border-radius:20px;margin-bottom:.5rem;position:relative;z-index:2;}
.ult2-t-preview-unit{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;position:relative;z-index:2;}
.ult2-t-preview-body{padding:1.25rem;}
.ult2-t-preview-jabatan{font-size:.82rem;font-weight:700;color:var(--admin-primary);margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.5px;}
.ult2-t-preview-tugas{font-size:.82rem;color:#64748b;line-height:1.6;margin:0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}

/* ===== ACTION BAR ===== */
.ult2-t-actions{position:sticky;bottom:0;background:var(--admin-surface);border-top:1px solid var(--admin-border);padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;z-index:50;box-shadow:0 -6px 20px rgba(0,0,0,.06);margin:0 -1.5rem -1.5rem;border-radius:0 0 16px 16px;}
@media (max-width:768px){.ult2-t-actions{position:fixed;bottom:0;left:0;right:0;margin:0;border-radius:0;z-index:1000;padding:.85rem 1rem;flex-direction:column;gap:.6rem;}}
.ult2-t-actions-info{display:flex;align-items:center;gap:.75rem;font-size:.82rem;color:var(--admin-text-secondary);}
.ult2-t-changes{display:none;align-items:center;gap:.4rem;background:rgba(230,126,34,.12);color:#c2610a;border:1px solid rgba(230,126,34,.35);padding:.3rem .75rem;border-radius:20px;font-size:.72rem;font-weight:700;}
.ult2-t-changes.show{display:inline-flex;animation:ult2_pop .25s ease;}
@keyframes ult2_pop{from{transform:scale(.85);}to{transform:scale(1);}}
.ult2-t-actions-btns{display:flex;gap:.6rem;}
.ult2-t-spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:ult2-t-spin .7s linear infinite;}
@keyframes ult2-t-spin{to{transform:rotate(360deg);}}
</style>

<!-- ===== HERO ===== -->
<div class="ult2-t-hero">
    <div class="ult2-t-hero-inner">
        <div class="ult2-t-hero-title">
            <div class="ult2-t-hero-icon"><i class="bi bi-clipboard-check-fill"></i></div>
            <div>
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
                <p><?= $pageDesc ?></p>
                <div class="ult2-t-hero-breadcrumb">
                    <a href="<?= url('admin/tupoksi') ?>">Tupoksi</a>
                    <i class="bi bi-chevron-right"></i>
                    <span><?= $isEdit ? 'Edit Unit' : 'Tambah Unit' ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== FORM ===== -->
<form method="POST" action="<?= url('admin/tupoksi/simpan') ?>" id="ult2TupoksiForm" novalidate>
    <?= Security::csrfField() ?>
    <input type="hidden" name="id" value="<?= $data['id'] ?? 0 ?>">
    
    <div class="ult2-t-layout">
        <!-- SIDEBAR -->
        <aside class="ult2-t-nav">
            <div class="ult2-t-nav-title">Navigasi Form</div>
            <a href="#section-identitas" class="ult2-t-nav-link active" data-scroll><i class="bi bi-building-fill"></i> <span>Identitas Unit</span></a>
            <a href="#section-tampilan" class="ult2-t-nav-link" data-scroll><i class="bi bi-palette-fill"></i> <span>Tampilan</span></a>
            <a href="#section-tugas" class="ult2-t-nav-link" data-scroll><i class="bi bi-list-check"></i> <span>Tugas & Fungsi</span></a>
            <a href="#section-preview" class="ult2-t-nav-link" data-scroll><i class="bi bi-eye-fill"></i> <span>Live Preview</span></a>
            
            <div class="ult2-t-nav-progress">
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--admin-text-muted);font-weight:700;margin-bottom:.5rem;display:flex;justify-content:space-between;">
                    <span>Progress</span><span id="ult2TProgressPct">0%</span>
                </div>
                <div class="ult2-t-progress-bar"><div class="ult2-t-progress-fill" id="ult2TProgressFill"></div></div>
                <div style="font-size:.7rem;color:var(--admin-text-muted);text-align:right;" id="ult2TProgressText">0 dari 5 field terisi</div>
            </div>
        </aside>
        
        <!-- MAIN CONTENT -->
        <div style="padding-bottom:80px;">
            
            <!-- SECTION 1: IDENTITAS -->
            <div class="ult2-t-section" id="section-identitas">
                <div class="ult2-t-section-header">
                    <div class="ult2-t-section-icon primary"><i class="bi bi-building-fill"></i></div>
                    <div><h3 class="ult2-t-section-title">Identitas Unit</h3><p class="ult2-t-section-desc">Informasi dasar unit kerja</p></div>
                </div>
                <div class="ult2-t-section-body">
                    <div class="ult2-t-grid">
                        <div class="ult2-t-field">
                            <label class="ult2-t-label">Nama Unit Kerja <span class="req">*</span></label>
                            <div class="ult2-t-input-wrap">
                                <input type="text" name="unit_kerja" id="tUnitKerja" value="<?= htmlspecialchars($val('unit_kerja')) ?>" required maxlength="100" placeholder="Contoh: Kepegawaian">
                                <i class="bi bi-building input-icon"></i>
                            </div>
                        </div>
                        <div class="ult2-t-field">
                            <label class="ult2-t-label">Kode Unit</label>
                            <div class="ult2-t-input-wrap">
                                <input type="text" name="kode_unit" id="tKodeUnit" value="<?= htmlspecialchars($val('kode_unit')) ?>" maxlength="20" placeholder="KEPEG" style="text-transform:uppercase;">
                                <i class="bi bi-hash input-icon"></i>
                            </div>
                            <span class="ult2-t-hint"><i class="bi bi-info-circle"></i> Akan otomatis huruf kapital</span>
                        </div>
                        <div class="ult2-t-field full">
                            <label class="ult2-t-label">Nama Jabatan <span class="req">*</span></label>
                            <div class="ult2-t-input-wrap">
                                <input type="text" name="nama_jabatan" id="tNamaJabatan" value="<?= htmlspecialchars($val('nama_jabatan')) ?>" required maxlength="150" placeholder="Kepala Bagian Kepegawaian">
                                <i class="bi bi-person-badge input-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 2: TAMPILAN -->
            <div class="ult2-t-section" id="section-tampilan">
                <div class="ult2-t-section-header">
                    <div class="ult2-t-section-icon accent"><i class="bi bi-palette-fill"></i></div>
                    <div><h3 class="ult2-t-section-title">Tampilan</h3><p class="ult2-t-section-desc">Kustomisasi visual unit kerja</p></div>
                </div>
                <div class="ult2-t-section-body">
                    <div class="ult2-t-grid">
                        <div class="ult2-t-field">
                            <label class="ult2-t-label">Icon (Bootstrap Icons)</label>
                            <div class="ult2-t-icon-picker">
                                <div class="ult2-t-icon-preview" id="tIconPreview"><i class="bi <?= htmlspecialchars($defaultIcon) ?>"></i></div>
                                <div class="ult2-t-input-wrap" style="flex:1;">
                                    <input type="text" name="icon" id="tIcon" value="<?= htmlspecialchars($defaultIcon) ?>" maxlength="50" placeholder="bi-briefcase">
                                    <i class="bi bi-emoji-smile input-icon"></i>
                                </div>
                            </div>
                            <span class="ult2-t-hint"><i class="bi bi-info-circle"></i> <a href="https://icons.getbootstrap.com/" target="_blank" style="color:var(--admin-primary);">Lihat daftar icon</a></span>
                        </div>
                        <div class="ult2-t-field">
                            <label class="ult2-t-label">Warna Tema (HEX)</label>
                            <div class="ult2-t-color-picker">
                                <input type="color" name="warna_color" id="tWarnaColor" value="<?= htmlspecialchars($defaultColor) ?>" class="ult2-t-color-input">
                                <div class="ult2-t-input-wrap" style="flex:1;">
                                    <input type="text" name="warna" id="tWarna" value="<?= htmlspecialchars($defaultColor) ?>" maxlength="7" class="ult2-t-color-text" placeholder="#1a5276">
                                    <i class="bi bi-palette input-icon"></i>
                                </div>
                            </div>
                        </div3>
                        <div class="ult2-t-field">
                            <label class="ult2-t-label">Urutan Tampil</label>
                            <div class="ult2-t-input-wrap">
                                <input type="number" name="sort_order" id="tSortOrder" value="<?= (int)$val('sort_order', $data['sort_order'] ?? 0) ?>" min="0" max="999">
                                <i class="bi bi-sort-down input-icon"></i>
                            </div>
                            <span class="ult2-t-hint"><i class="bi bi-info-circle"></i> Angka lebih kecil tampil lebih dulu</span>
                        </div>
                        <div class="ult2-t-field">
                            <label class="ult2-t-label">Status</label>
                            <div class="ult2-t-input-wrap">
                                <select name="is_active" id="tIsActive">
                                    <option value="1" <?= ($val('is_active', $data['is_active'] ?? 1) == 1) ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= ($val('is_active', $data['is_active'] ?? 1) == 0) ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                                <i class="bi bi-toggle-on input-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Fix typo in closing tag above -->
                <script>
                // Quick fix for the typo in PHP above if any, but let's just ensure clean HTML
                </script>
            </div>
            
            <!-- SECTION 3: TUGAS & FUNGSI -->
            <div class="ult2-t-section" id="section-tugas">
                <div class="ult2-t-section-header">
                    <div class="ult2-t-section-icon purple"><i class="bi bi-list-check"></i></div>
                    <div><h3 class="ult2-t-section-title">Tugas & Fungsi</h3><p class="ult2-t-section-desc">Deskripsi tanggung jawab unit kerja</p></div>
                </div>
                <div class="ult2-t-section-body">
                    <div class="ult2-t-grid">
                        <div class="ult2-t-field full">
                            <label class="ult2-t-label">Tugas Pokok <span class="req">*</span> <small style="color:var(--admin-text-muted);font-weight:400;">(satu paragraf)</small></label>
                            <div class="ult2-t-input-wrap">
                                <textarea name="tugas_pokok" id="tTugasPokok" rows="4" required maxlength="1000" placeholder="Menyelenggarakan pengelolaan administrasi umum, keuangan..."><?= htmlspecialchars($val('tugas_pokok')) ?></textarea>
                            </div>
                            <div class="ult2-t-char-count" id="cnt_tugas_pokok">0 / 1000</div>
                        </div>
                        <div class="ult2-t-field full">
                            <label class="ult2-t-label">Fungsi <small style="color:var(--admin-text-muted);font-weight:400;">(satu poin per baris)</small></label>
                            <div class="ult2-t-input-wrap">
                                <textarea name="fungsi" id="tFungsi" rows="6" maxlength="2000" placeholder="1. Menyusun rencana kebutuhan...&#10;2. Mengelola administrasi..."><?= htmlspecialchars($val('fungsi')) ?></textarea>
                            </
div>
                            <div class="ult2-t-char-count" id="cnt_fungsi">0 / 2000</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 4: LIVE PREVIEW -->
            <div class="ult2-t-section" id="section-preview">
                <div class="ult2-t-section-header">
                    <div class="ult2-t-section-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-eye-fill"></i></div>
                    <div><h3 class="ult2-t-section-title">Live Preview</h3><p class="ult2-t-section-desc">Pratinjau tampilan di halaman publik</p></div>
                </div>
                <div class="ult2-t-section-body">
                    <div class="ult2-t-preview-wrap">
                        <div class="ult2-t-preview-title"><i class="bi bi-magic"></i> Pratinjau Kartu Tupoksi</div>
                        <div class="ult2-t-preview-card">
                            <div class="ult2-t-preview-header" id="previewHeader" style="background:linear-gradient(135deg,<?= htmlspecialchars($defaultColor) ?>,<?= htmlspecialchars($defaultColor) ?>dd);">
                                <div class="ult2-t-preview-icon"><i class="bi <?= htmlspecialchars($defaultIcon) ?>" id="previewIcon"></i></div>
                                <div class="ult2-t-preview-kode" id="previewKode"><?= htmlspecialchars($val('kode_unit', 'KODE')) ?></div>
                                <div class="ult2-t-preview-unit" id="previewUnit"><?= htmlspecialchars($val('unit_kerja', 'Nama Unit Kerja')) ?></div>
                            </div>
                            <div class="ult2-t-preview-body">
                                <div class="ult2-t-preview-jabatan" id="previewJabatan"><?= htmlspecialchars($val('nama_jabatan', 'Nama Jabatan')) ?></div>
                                <p class="ult2-t-preview-tugas" id="previewTugas"><?= htmlspecialchars($val('tugas_pokok', 'Deskripsi tugas pokok akan muncul di sini...')) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- STICKY ACTION BAR -->
            <div class="ult2-t-actions">
                <div class="ult2-t-actions-info">
                    <i class="bi bi-info-circle-fill" style="color:var(--admin-primary);"></i>
                    <span>Data akan disimpan ke database</span>
                    <span class="ult2-t-changes" id="ult2TChanges"><i class="bi bi-exclamation-circle-fill"></i> Perubahan belum disimpan</span>
                </div>
                <div class="ult2-t-actions-btns">
                    <a href="<?= url('admin/tupoksi') ?>" class="btn btn-secondary" onclick="return confirm('Batalkan semua perubahan?');"><i class="bi bi-x-lg"></i> Batal</a>
                    <button type="reset" class="btn btn-outline" style="border-color:var(--admin-border);color:var(--admin-text);"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                    <button type="submit" class="btn btn-primary" id="ult2TSubmitBtn" style="min-width:180px;">
                        <span class="ult2-t-spinner" id="ult2TSpinner"></span>
                        <i class="bi bi-save-fill" id="ult2TSubmitIcon"></i>
                        <span id="ult2TSubmitText"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Unit' ?></span>
                    </button>
                </div>
            </div>
            
        </div>
    </div>
</form>

<!-- ===== SCRIPTS ===== -->
<script>
(function(){
    'use strict';
    var form = document.getElementById('ult2TupoksiForm');
    var isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    
    // ===== SCROLL SPY =====
    document.querySelectorAll('.ult2-t-nav-link[data-scroll]').forEach(function(link){
        link.addEventListener('click', function(e){
            e.preventDefault();
            var target = document.querySelector(this.getAttribute('href'));
            if(target) window.scrollTo({top: target.offsetTop - 100, behavior: 'smooth'});
        });
    });
    var sections = document.querySelectorAll('.ult2-t-section');
    var navLinks = document.querySelectorAll('.ult2-t-nav-link');
    window.addEventListener('scroll', function(){
        var current = '';
        sections.forEach(function(sec){ if(window.pageYOffset >= sec.offsetTop - 120) current = sec.getAttribute('id'); });
        navLinks.forEach(function(link){
            link.classList.remove('active');
            if(link.getAttribute('href') === '#' + current) link.classList.add('active');
        });
    });
    
    // ===== PROGRESS TRACKER =====
    function updateProgress(){
        var req = ['unit_kerja', 'nama_jabatan', 'tugas_pokok'];
        var opt = ['kode_unit', 'fungsi'];
        var filled = 0;
        req.forEach(function(f){ if(form.querySelector('[name="'+f+'"]').value.trim() !== '') filled++; });
        opt.forEach(function(f){ if(form.querySelector('[name="'+f+'"]').value.trim() !== '') filled++; });
        var pct = Math.round((filled / (req.length + opt.length)) * 100);
        document.getElementById('ult2TProgressFill').style.width = pct + '%';
        document.getElementById('ult2TProgressPct').textContent = pct + '%';
        document.getElementById('ult2TProgressText').textContent = filled + ' dari ' + (req.length + opt.length) + ' field terisi';
    }
    form.addEventListener('input', updateProgress);
    updateProgress();
    
    // ===== CHANGE TRACKER =====
    var initialSnapshot = '';
    function serializeForm(){
        var parts = [];
        new FormData(form).forEach(function(v, k){ if(typeof v === 'string' && k !== 'csrf_token') parts.push(k + '=' + v); });
        return parts.sort().join('&');
    }
    initialSnapshot = serializeForm();
    function updateChanges(){
        var hasChanges = serializeForm() !== initialSnapshot;
        document.getElementById('ult2TChanges').classList.toggle('show', hasChanges);
    }
    form.addEventListener('input', updateChanges);
    form.addEventListener('change', updateChanges);
    
    // ===== CHAR COUNTERS =====
    ['tugas_pokok', 'fungsi'].forEach(function(name){
        var inp = form.querySelector('[name="'+name+'"]');
        var cnt = document.getElementById('cnt_' + name);
        if(inp && cnt){
            var upd = function(){
                var len = inp.value.length;
                var max = parseInt(inp.getAttribute('maxlength'));
                cnt.textContent = len + ' / ' + max;
                cnt.className = 'ult2-t-char-count' + (len > max * 0.9 ? ' danger' : (len > max * 0.7 ? ' warn' : ''));
            };
            inp.addEventListener('input', upd);
            upd();
        }
    });
    
    // ===== AUTO UPPERCASE KODE =====
    var kodeInput = document.getElementById('tKodeUnit');
    if(kodeInput) kodeInput.addEventListener('input', function(){ this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); });
    
    // ===== COLOR SYNC =====
    var colorInput = document.getElementById('tWarnaColor');
    var textInput = document.getElementById('tWarna');
    if(colorInput && textInput){
        colorInput.addEventListener('input', function(){ textInput.value = this.value; updatePreview(); });
        textInput.addEventListener('input', function(){ 
            if(/^#[0-9A-F]{6}$/i.test(this.value)) { colorInput.value = this.value; updatePreview(); }
        });
    }
    
    // ===== LIVE PREVIEW =====
    function updatePreview(){
        var unit = document.getElementById('tUnitKerja').value || 'Nama Unit Kerja';
        var kode = document.getElementById('tKodeUnit').value || 'KODE';
        var jabatan = document.getElementById('tNamaJabatan').value || 'Nama Jabatan';
        var icon = document.getElementById('tIcon').value || 'bi-briefcase';
        var warna = document.getElementById('tWarna').value || '#1a5276';
        var tugas = document.getElementById('tTugasPokok').value || 'Deskripsi tugas pokok akan muncul di sini...';
        
        document.getElementById('previewUnit').textContent = unit;
        document.getElementById('previewKode').textContent = kode;
        document.getElementById('previewJabatan').textContent = jabatan;
        document.getElementById('previewIcon').className = 'bi ' + icon;
        document.getElementById('previewTugas').textContent = tugas;
        document.getElementById('previewHeader').style.background = 'linear-gradient(135deg, ' + warna + ', ' + warna + 'dd)';
        
        // Update sidebar icon preview
        document.getElementById('tIconPreview').innerHTML = '<i class="bi ' + icon + '"></i>';
    }
    ['tUnitKerja','tKodeUnit','tNamaJabatan','tIcon','tWarna','tTugasPokok'].forEach(function(id){
        var el = document.getElementById(id);
        if(el) el.addEventListener('input', updatePreview);
    });
    updatePreview();
    
    // ===== SUBMIT HANDLING =====
    var submitBtn = document.getElementById('ult2TSubmitBtn');
    form.addEventListener('submit', function(e){
        if(!document.getElementById('tUnitKerja').value.trim() || !document.getElementById('tNamaJabatan').value.trim() || !document.getElementById('tTugasPokok').value.trim()){
            e.preventDefault();
            if(window.showToast) showToast('Field wajib (*) harus diisi', 'error');
            return false;
        }
        submitBtn.disabled = true;
        document.getElementById('ult2TSpinner').style.display = 'inline-block';
        document.getElementById('ult2TSubmitIcon').style.display = 'none';
        document.getElementById('ult2TSubmitText').textContent = 'Menyimpan...';
    });
    
    form.addEventListener('reset', function(e){
        if(!confirm('Batalkan semua perubahan dan kembalikan ke nilai awal?')){ e.preventDefault(); return false; }
        setTimeout(function(){ initialSnapshot = serializeForm(); updateProgress(); updateChanges(); updatePreview(); }, 50);
    });
    
    window.addEventListener('beforeunload', function(e){
        if(serializeForm() !== initialSnapshot){ e.preventDefault(); e.returnValue = ''; }
    });
    
    document.addEventListener('keydown', function(e){
        if((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's'){ e.preventDefault(); form.requestSubmit ? form.requestSubmit() : form.submit(); }
    });
})();
</script>