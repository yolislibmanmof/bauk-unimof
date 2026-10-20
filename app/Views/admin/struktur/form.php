<?php
/**
 * =====================================================
 * BAUK UNIMOF - FORM STRUKTUR ORGANISASI (ULTIMATE v2)
 * Add/Edit Jabatan dengan Live Preview
 * =====================================================
 */
use App\Helpers\Security;

$data = $data ?? null;
$mode = $mode ?? 'add';
$parents = $parents ?? [];
$isEdit = ($mode === 'edit');

$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);

$val = static function (string $key, $default = '') use ($old, $data) {
    if (array_key_exists($key, $old) && $old[$key] !== '') return $old[$key];
    if (is_array($data) && isset($data[$key]) && $data[$key] !== '') return $data[$key];
    return $default;
};

$fotoUrl = '';
if (!empty($data['foto']) && is_file(UPLOAD_DIR . 'struktur/' . $data['foto'])) {
    $fotoUrl = url('uploads/struktur/' . $data['foto']);
}

$pageTitle = $isEdit ? 'Edit Jabatan' : 'Tambah Jabatan Baru';
$pageDesc  = $isEdit 
    ? 'Memperbarui data jabatan: <strong>' . htmlspecialchars($data['nama_jabatan'] ?? '') . '</strong>' 
    : 'Tambahkan jabatan baru ke dalam struktur organisasi BAUK UNIMOF.';

// Auto-calculate level dari parent
$currentParentId = $val('parent_id', $data['parent_id'] ?? '');
$currentLevel = (int)$val('level', $data['level'] ?? 1);
if ($currentParentId && is_array($parents)) {
    foreach ($parents as $p) {
        if ((int)$p['id'] === (int)$currentParentId) {
            $currentLevel = (int)$p['level'] + 1;
            break;
        }
    }
} elseif (empty($currentParentId)) {
    $currentLevel = 1;
}
?>

<!-- ===== ULTIMATE FORM STYLES ===== -->
<style>
/* ===== HERO HEADER ===== */
.ult-struktur-hero{background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 55%,#2980b9 100%);border-radius:20px;padding:2rem 2.25rem;color:#fff;position:relative;overflow:hidden;margin-bottom:1.5rem;}
.ult-struktur-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='1.5'/%3E%3C/g%3E%3C/svg%3E");}
.ult-struktur-hero::after{content:'';position:absolute;top:-60%;right:-8%;width:380px;height:380px;background:radial-gradient(circle,rgba(230,126,34,.25) 0%,transparent 70%);border-radius:50%;}
.ult-struktur-hero-inner{position:relative;z-index:2;display:flex;justify-content:space-between;align-items:center;gap:1.5rem;flex-wrap:wrap;}
.ult-struktur-hero-title{display:flex;align-items:center;gap:1rem;}
.ult-struktur-hero-icon{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.14);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:1.7rem;flex-shrink:0;}
.ult-struktur-hero h1{font-family:'Playfair Display',serif;font-size:1.65rem;margin:0 0 .25rem;color:#fff;}
.ult-struktur-hero p{margin:0;font-size:.85rem;color:rgba(255,255,255,.85);}
.ult-struktur-hero-breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:rgba(255,255,255,.85);margin-top:.5rem;}
.ult-struktur-hero-breadcrumb a{color:#f39c12;text-decoration:none!important;font-weight:600;}
.ult-struktur-hero-breadcrumb i{font-size:.6rem;}

/* ===== LAYOUT ===== */
.ult-struktur-layout{display:grid;grid-template-columns:260px 1fr;gap:2rem;align-items:start;}
@media (max-width:1024px){.ult-struktur-layout{grid-template-columns:1fr;}}

/* ===== SIDEBAR NAV ===== */
.ult-struktur-nav{position:sticky;top:90px;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;padding:1rem;box-shadow:0 4px 15px rgba(0,0,0,.04);}
@media (max-width:1024px){.ult-struktur-nav{position:static;display:flex;gap:.5rem;overflow-x:auto;padding:.75rem;}}
.ult-struktur-nav-title{font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--admin-text-muted);font-weight:700;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--admin-border);}
@media (max-width:1024px){.ult-struktur-nav-title{display:none;}}
.ult-struktur-nav-link{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:10px;color:var(--admin-text-secondary);text-decoration:none!important;font-size:.86rem;font-weight:600;transition:all .2s;margin-bottom:.25rem;}
@media (max-width:1024px){.ult-struktur-nav-link{white-space:nowrap;padding:.5rem .85rem;font-size:.8rem;}}
.ult-struktur-nav-link:hover{background:var(--admin-bg);color:var(--admin-text);}
.ult-struktur-nav-link.active{background:rgba(26,82,118,.08);color:var(--admin-primary);border-left:3px solid var(--admin-primary);}
@media (max-width:1024px){.ult-struktur-nav-link.active{border-left:none;border-bottom:2px solid var(--admin-primary);border-radius:8px 8px 0 0;}}
.ult-struktur-nav-link i{width:20px;text-align:center;font-size:1rem;}
.ult-struktur-nav-progress{margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
@media (max-width:1024px){.ult-struktur-nav-progress{display:none;}}
.ult-struktur-nav-progress-label{font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--admin-text-muted);font-weight:700;margin-bottom:.5rem;display:flex;justify-content:space-between;}
.ult-struktur-progress-bar{background:var(--admin-bg);border-radius:50px;height:8px;overflow:hidden;margin-bottom:.35rem;}
.ult-struktur-progress-fill{height:100%;background:linear-gradient(90deg,#1a5276,#e67e22);border-radius:50px;transition:width .4s ease;width:0%;}
.ult-struktur-progress-text{font-size:.7rem;color:var(--admin-text-muted);text-align:right;}

/* ===== FORM SECTIONS ===== */
.ult-struktur-section{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;margin-bottom:1.5rem;overflow:hidden;scroll-margin-top:100px;transition:all .3s;}
.ult-struktur-section:focus-within{border-color:var(--admin-primary);box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-struktur-section-header{padding:1.25rem 1.5rem;background:linear-gradient(to right,var(--admin-bg),transparent);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.85rem;}
.ult-struktur-section-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff;flex-shrink:0;box-shadow:0 6px 16px rgba(0,0,0,.12);}
.ult-struktur-section-icon.primary{background:linear-gradient(135deg,#1a5276,#2980b9);}
.ult-struktur-section-icon.accent{background:linear-gradient(135deg,#e67e22,#f39c12);}
.ult-struktur-section-icon.purple{background:linear-gradient(135deg,#8e44ad,#9b59b6);}
.ult-struktur-section-icon.green{background:linear-gradient(135deg,#10b981,#059669);}
.ult-struktur-section-title{font-size:1.05rem;font-weight:800;color:var(--admin-text);margin:0;}
.ult-struktur-section-desc{font-size:.78rem;color:var(--admin-text-secondary);margin:.2rem 0 0;}
.ult-struktur-section-body{padding:1.5rem;}

/* ===== FORM GRID ===== */
.ult-struktur-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem 1.5rem;}
@media (max-width:768px){.ult-struktur-grid{grid-template-columns:1fr;}}
.ult-struktur-field{display:flex;flex-direction:column;gap:.375rem;}
.ult-struktur-field.full{grid-column:1/-1;}
.ult-struktur-label{font-size:.82rem;font-weight:700;color:var(--admin-text);display:flex;align-items:center;gap:.4rem;}
.ult-struktur-label .req{color:#ef4444;font-size:1rem;line-height:1;}
.ult-struktur-label .info-icon{color:var(--admin-text-muted);cursor:help;font-size:.85rem;margin-left:auto;}

/* ===== INPUTS ===== */
.ult-struktur-input-wrap{position:relative;}
.ult-struktur-input-wrap .input-icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);font-size:1rem;pointer-events:none;transition:color .2s;z-index:2;}
.ult-struktur-input-wrap input,.ult-struktur-input-wrap select{width:100%;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:12px;font-size:.88rem;background:var(--admin-bg);color:var(--admin-text);transition:all .2s;font-family:inherit;}
.ult-struktur-input-wrap textarea{width:100%;padding:.75rem 1rem;border:1.5px solid var(--admin-border);border-radius:12px;font-size:.88rem;background:var(--admin-bg);color:var(--admin-text);transition:all .2s;font-family:inherit;resize:vertical;min-height:80px;line-height:1.6;}
.ult-struktur-input-wrap input:read-only{background:var(--admin-bg);color:var(--admin-text-secondary);cursor:not-allowed;}
.ult-struktur-input-wrap input:focus,.ult-struktur-input-wrap select:focus,.ult-struktur-input-wrap textarea:focus{outline:none;border-color:var(--admin-primary);background:var(--admin-surface);box-shadow:0 0 0 3px rgba(26,82,118,.1);}
.ult-struktur-input-wrap input:focus ~ .input-icon,.ult-struktur-input-wrap select:focus ~ .input-icon{color:var(--admin-primary);}
.ult-struktur-hint{font-size:.72rem;color:var(--admin-text-muted);margin-top:.15rem;display:flex;align-items:center;gap:.3rem;}
.ult-struktur-hint i{font-size:.7rem;}
.ult-struktur-char-count{font-size:.7rem;color:var(--admin-text-muted);text-align:right;margin-top:.25rem;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-struktur-dropdown{position:relative;}
.ult-struktur-dd-trigger{width:100%;display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:12px;background:var(--admin-bg);color:var(--admin-text);font-size:.88rem;cursor:pointer;transition:all .2s;text-align:left;font-family:inherit;}
.ult-struktur-dd-trigger:hover{border-color:var(--admin-primary);background:var(--admin-surface);}
.ult-struktur-dd-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.04);}
.ult-struktur-dd-trigger i.chevron{margin-left:auto;font-size:.7rem;color:var(--admin-text-muted);transition:transform .25s;}
.ult-struktur-dropdown.open .ult-struktur-dd-trigger{border-color:var(--admin-primary);background:var(--admin-surface);box-shadow:0 0 0 3px rgba(26,82,118,.1);}
.ult-struktur-dropdown.open .ult-struktur-dd-trigger i.chevron{transform:rotate(180deg);}
.ult-struktur-dd-menu{position:absolute;top:calc(100% + 6px);left:0;width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:12px;box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .25s;z-index:100;max-height:300px;overflow:hidden;display:flex;flex-direction:column;}
.ult-struktur-dropdown.open .ult-struktur-dd-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-struktur-dd-search{padding:.6rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);z-index:2;}
.ult-struktur-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:8px;font-size:.82rem;background:var(--admin-bg);}
.ult-struktur-dd-search input:focus{outline:none;border-color:var(--admin-primary);}
.ult-struktur-dd-scroll{overflow-y:auto;flex:1;}
.ult-struktur-dd-item{display:flex;align-items:center;gap:.7rem;padding:.7rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-struktur-dd-item:last-child{border-bottom:none;}
.ult-struktur-dd-item:hover{background:var(--admin-bg);}
.ult-struktur-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-struktur-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;transition:opacity .2s;}
.ult-struktur-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-struktur-dd-level{display:inline-block;min-width:60px;font-size:.7rem;font-weight:700;color:var(--admin-primary);background:rgba(26,82,118,.1);padding:.15rem .5rem;border-radius:50px;text-align:center;}
.ult-struktur-dd-empty{padding:1.5rem;text-align:center;color:var(--admin-text-muted);font-size:.85rem;}

/* ===== PHOTO UPLOAD ===== */
.ult-struktur-photo-upload{border:2px dashed var(--admin-border);border-radius:16px;padding:2rem;background:var(--admin-bg);transition:all .25s;position:relative;}
.ult-struktur-photo-upload:hover{border-color:var(--admin-primary);background:rgba(26,82,118,.03);}
.ult-struktur-photo-upload.dragover{border-color:#e67e22;background:rgba(230,126,34,.08);transform:scale(1.01);}
.ult-struktur-photo-content{display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap;}
.ult-struktur-photo-preview{width:140px;height:140px;border-radius:16px;overflow:hidden;background:linear-gradient(135deg,#e6f0fa,#d4e4f5);display:flex;align-items:center;justify-content:center;color:#1a5276;font-size:3.5rem;border:1px solid var(--admin-border);flex-shrink:0;position:relative;}
.ult-struktur-photo-preview img{width:100%;height:100%;object-fit:cover;}
.ult-struktur-photo-preview .ult-struktur-photo-ph{display:flex;flex-direction:column;align-items:center;gap:.5rem;}
.ult-struktur-photo-preview .ult-struktur-photo-ph span{font-size:.7rem;color:#94a3b8;font-weight:600;}
.ult-struktur-photo-info{flex:1;min-width:200px;}
.ult-struktur-photo-info h4{font-size:.95rem;font-weight:700;margin:0 0 .35rem;color:var(--admin-text);display:flex;align-items:center;gap:.4rem;}
.ult-struktur-photo-info p{font-size:.8rem;color:var(--admin-text-secondary);margin:0 0 .85rem;line-height:1.5;}
.ult-struktur-photo-actions{display:flex;gap:.5rem;flex-wrap:wrap;}
.ult-struktur-photo-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1rem;border-radius:10px;font-size:.82rem;font-weight:600;cursor:pointer;border:none;transition:all .2s;}
.ult-struktur-photo-btn-primary{background:var(--admin-primary);color:#fff;}
.ult-struktur-photo-btn-primary:hover{background:#0d2f4a;transform:translateY(-1px);}
.ult-struktur-photo-btn-danger{background:rgba(239,68,68,.1);color:#dc2626;border:1px solid rgba(239,68,68,.2);}
.ult-struktur-photo-btn-danger:hover{background:#dc2626;color:#fff;}
.ult-struktur-photo-hints{display:flex;gap:.75rem;margin-top:.75rem;font-size:.72rem;color:var(--admin-text-muted);flex-wrap:wrap;}
.ult-struktur-photo-hints span{display:inline-flex;align-items:center;gap:.3rem;}
.ult-struktur-photo-hints i{color:var(--admin-primary);}

/* ===== LIVE PREVIEW ===== */
.ult-struktur-preview-wrap{background:linear-gradient(135deg,var(--admin-bg),var(--admin-surface));border:1px solid var(--admin-border);border-radius:16px;padding:1.75rem;position:relative;overflow:hidden;}
.ult-struktur-preview-wrap::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#1a5276,#e67e22);}
.ult-struktur-preview-title{font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--admin-text-muted);font-weight:700;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;}
.ult-struktur-preview-title i{color:#e67e22;}

.ult-struktur-org-card{background:#fff;border-radius:16px;padding:1.75rem;text-align:center;max-width:280px;margin:0 auto;box-shadow:0 10px 30px rgba(13,47,74,.1);border:1px solid rgba(13,47,74,.06);position:relative;overflow:hidden;transition:all .3s;}
.ult-struktur-org-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#1a5276,#e67e22);}
.ult-struktur-org-card.leader{background:linear-gradient(135deg,#1a5276,#2980b9);color:#fff;}
.ult-struktur-org-card.leader::before{background:linear-gradient(90deg,#f39c12,#e67e22);}
.ult-struktur-org-avatar{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#f39c12,#e67e22);display:flex;align-items:center;justify-content:center;margin:0 auto .85rem;font-family:'Playfair Display',serif;font-size:1.8rem;color:#fff;font-weight:700;border:3px solid rgba(255,255,255,.3);overflow:hidden;transition:transform .3s;}
.ult-struktur-org-card:hover .ult-struktur-org-avatar{transform:scale(1.05);}
.ult-struktur-org-card.leader .ult-struktur-org-avatar{width:100px;height:100px;font-size:2.2rem;}
.ult-struktur-org-avatar img{width:100%;height:100%;object-fit:cover;}
.ult-struktur-org-jabatan{font-size:.72rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;color:#e67e22;margin-bottom:.4rem;}
.ult-struktur-org-card.leader .ult-struktur-org-jabatan{color:#fbbf24;}
.ult-struktur-org-nama{font-family:'Playfair Display',serif;font-size:1.15rem;font-weight:700;margin-bottom:.3rem;min-height:1.5em;word-break:break-word;}
.ult-struktur-org-card.leader .ult-struktur-org-nama{font-size:1.3rem;}
.ult-struktur-org-nip{font-size:.75rem;color:var(--admin-text-secondary);font-family:monospace;min-height:1em;}
.ult-struktur-org-card.leader .ult-struktur-org-nip{color:rgba(255,255,255,.85);}
.ult-struktur-org-empty{font-style:italic;color:var(--admin-text-muted);font-weight:400;font-size:.9rem;}

.ult-struktur-preview-meta{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px dashed var(--admin-border);}
.ult-struktur-preview-meta-item{text-align:center;}
.ult-struktur-preview-meta-label{font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;margin-bottom:.2rem;}
.ult-struktur-preview-meta-value{font-size:.9rem;font-weight:700;color:var(--admin-primary);}

/* ===== STICKY ACTION BAR ===== */
.ult-struktur-actions{position:sticky;bottom:0;background:var(--admin-surface);border-top:1px solid var(--admin-border);padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;z-index:50;box-shadow:0 -6px 20px rgba(0,0,0,.06);margin:0 -1.5rem -1.5rem;border-radius:0 0 16px 16px;}
@media (max-width:768px){.ult-struktur-actions{position:fixed;bottom:0;left:0;right:0;margin:0;border-radius:0;z-index:1000;padding:.85rem 1rem;flex-direction:column;gap:.6rem;}}
.ult-struktur-actions-info{display:flex;align-items:center;gap:.75rem;font-size:.82rem;color:var(--admin-text-secondary);}
.ult-struktur-changes{display:none;align-items:center;gap:.4rem;background:rgba(230,126,34,.12);color:#c2610a;border:1px solid rgba(230,126,34,.35);padding:.3rem .75rem;border-radius:20px;font-size:.72rem;font-weight:700;}
.ult-struktur-changes.show{display:inline-flex;animation:ult2_pop .25s ease;}
@keyframes ult2_pop{from{transform:scale(.85);}to{transform:scale(1);}}
.ult-struktur-actions-btns{display:flex;gap:.6rem;}

/* ===== VALIDATION ===== */
.ult-struktur-input-wrap input.invalid,.ult-struktur-input-wrap select.invalid{border-color:#ef4444;background:rgba(239,68,68,.03);}
.ult-struktur-error{font-size:.72rem;color:#dc2626;margin-top:.2rem;display:none;align-items:center;gap:.3rem;}
.ult-struktur-error.show{display:flex;}

/* ===== SPINNER ===== */
@keyframes ult-struktur-spin{to{transform:rotate(360deg);}}
.ult-struktur-spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:ult-struktur-spin .7s linear infinite;}
</style>

<!-- ===== HERO HEADER ===== -->
<div class="ult-struktur-hero">
    <div class="ult-struktur-hero-inner">
        <div class="ult-struktur-hero-title">
            <div class="ult-struktur-hero-icon"><i class="bi bi-diagram-3-fill"></i></div>
            <div>
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
                <p><?= $pageDesc ?></p>
                <div class="ult-struktur-hero-breadcrumb">
                    <a href="<?= url('admin/struktur-organisasi') ?>">Struktur Organisasi</a>
                    <i class="bi bi-chevron-right"></i>
                    <span><?= $isEdit ? 'Edit Jabatan' : 'Tambah Jabatan' ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== LAYOUT ===== -->
<form method="POST" action="<?= url('admin/struktur-organisasi/simpan') ?>" enctype="multipart/form-data" id="ultStrukturForm" novalidate>
    <?= Security::csrfField() ?>
    <input type="hidden" name="id" value="<?= $data['id'] ?? 0 ?>">
    
    <div class="ult-struktur-layout">
        
        <!-- ===== SIDEBAR NAV ===== -->
        <aside class="ult-struktur-nav">
            <div class="ult-struktur-nav-title">Navigasi Form</div>
            <a href="#section-identitas" class="ult-struktur-nav-link active" data-scroll>
                <i class="bi bi-briefcase-fill"></i> <span>Identitas Jabatan</span>
            </a>
            <a href="#section-pejabat" class="ult-struktur-nav-link" data-scroll>
                <i class="bi bi-person-badge-fill"></i> <span>Data Pejabat</span>
            </a>
            <a href="#section-foto" class="ult-struktur-nav-link" data-scroll>
                <i class="bi bi-image-fill"></i> <span>Foto Pejabat</span>
            </a>
            <a href="#section-preview" class="ult-struktur-nav-link" data-scroll>
                <i class="bi bi-eye-fill"></i> <span>Live Preview</span>
            </a>
            
            <div class="ult-struktur-nav-progress">
                <div class="ult-struktur-nav-progress-label">
                    <span>Progress</span>
                    <span id="ultStrukturProgressPct">0%</span>
                </div>
                <div class="ult-struktur-progress-bar">
                    <div class="ult-struktur-progress-fill" id="ultStrukturProgressFill"></div>
                </div>
                <div class="ult-struktur-progress-text" id="ultStrukturProgressText">0 dari 4 field terisi</div>
            </div>
        </aside>
        
        <!-- ===== MAIN CONTENT ===== -->
        <div style="padding-bottom:80px;">
            
            <!-- SECTION 1: IDENTITAS JABATAN -->
            <div class="ult-struktur-section" id="section-identitas">
                <div class="ult-struktur-section-header">
                    <div class="ult-struktur-section-icon primary"><i class="bi bi-briefcase-fill"></i></div>
                    <div>
                        <h3 class="ult-struktur-section-title">Identitas Jabatan</h3>
                        <p class="ult-struktur-section-desc">Informasi dasar jabatan dalam struktur organisasi</p>
                    </div>
                </div>
                <div class="ult-struktur-section-body">
                    <div class="ult-struktur-grid">
                        <div class="ult-struktur-field full">
                            <label class="ult-struktur-label">
                                Nama Jabatan <span class="req">*</span>
                                <span class="info-icon" title="Contoh: Kepala Bagian Kepegawaian, Sekretaris BAUK"><i class="bi bi-info-circle-fill"></i></span>
                            </label>
                            <div class="ult-struktur-input-wrap">
                                <input type="text" name="nama_jabatan" id="strukturNamaJabatan" value="<?= htmlspecialchars($val('nama_jabatan')) ?>" required maxlength="150" placeholder="Contoh: Kepala Bagian Keuangan">
                                <i class="bi bi-briefcase input-icon"></i>
                            </div>
                            <div class="ult-struktur-char-count"><span id="cnt_nama_jabatan">0</span> / 150</div>
                            <div class="ult-struktur-error" id="err_nama_jabatan"><i class="bi bi-exclamation-circle"></i> <span>Nama jabatan wajib diisi</span></div>
                        </div>
                        
                        <div class="ult-struktur-field">
                            <label class="ult-struktur-label">Atasan (Parent)</label>
                            <div class="ult-struktur-dropdown" id="ultStrukturParentDD">
                                <input type="hidden" name="parent_id" id="strukturParentId" value="<?= htmlspecialchars($val('parent_id', $data['parent_id'] ?? '')) ?>">
                                <div class="ult-struktur-input-wrap">
                                    <button type="button" class="ult-struktur-dd-trigger" id="strukturParentTrigger">
                                        <i class="bi bi-diagram-3 input-icon"></i>
                                        <span id="strukturParentLabel">
                                            <?php if ($currentParentId): ?>
                                                <?php foreach ($parents as $p): if ((int)$p['id'] === (int)$currentParentId): ?>
                                                    <?= htmlspecialchars($p['nama_jabatan']) ?>
                                                <?php endif; endforeach; ?>
                                            <?php else: ?>
                                                Jabatan Tertinggi (Level 1)
                                            <?php endif; ?>
                                        </span>
                                        <i class="bi bi-chevron-down chevron"></i>
                                    </button>
                                </div>
                                <div class="ult-struktur-dd-menu">
                                    <div class="ult-struktur-dd-search">
                                        <input type="text" placeholder="Cari jabatan..." id="strukturParentSearch">
                                    </div>
                                    <div class="ult-struktur-dd-scroll">
                                        <div class="ult-struktur-dd-item <?= empty($currentParentId) ? 'selected' : '' ?>" data-value="" data-label="Jabatan Tertinggi (Level 1)">
                                            <span class="ult-struktur-dd-level">L1</span>
                                            <span>Jabatan Tertinggi (Level 1)</span>
                                            <i class="bi bi-check-lg"></i>
                                        </div>
                                        <?php foreach ($parents as $p): ?>
                                        <div class="ult-struktur-dd-item <?= ((int)($data['parent_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>" 
                                             data-value="<?= $p['id'] ?>" 
                                             data-label="<?= htmlspecialchars($p['nama_jabatan']) ?>"
                                             data-level="<?= $p['level'] ?>">
                                            <span class="ult-struktur-dd-level">L<?= $p['level'] ?></span>
                                            <span><?= htmlspecialchars($p['nama_jabatan']) ?></span>
                                            <i class="bi bi-check-lg"></i>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php if (empty($parents)): ?>
                                        <div class="ult-struktur-dd-empty">
                                            <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:.5rem;"></i>
                                            Belum ada jabatan atasan
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <span class="ult-struktur-hint"><i class="bi bi-info-circle"></i> Tentukan jabatan ini melapor ke siapa</span>
                        </div>
                        
                        <div class="ult-struktur-field">
                            <label class="ult-struktur-label">Level (Otomatis)</label>
                            <div class="ult-struktur-input-wrap">
                                <input type="number" name="level" id="strukturLevel" value="<?= $currentLevel ?>" min="1" max="10" readonly style="font-weight:700;color:var(--admin-primary);">
                                <i class="bi bi-layers input-icon"></i>
                            </div>
                            <span class="ult-struktur-hint"><i class="bi bi-info-circle"></i> Dihitung otomatis dari parent + 1</span>
                        </div>
                        
                        <div class="ult-struktur-field">
                            <label class="ult-struktur-label">Urutan Tampil</label>
                            <div class="ult-struktur-input-wrap">
                                <input type="number" name="sort_order" value="<?= htmlspecialchars($val('sort_order', $data['sort_order'] ?? 0)) ?>" min="0" max="999">
                                <i class="bi bi-sort-down input-icon"></i>
                            </div>
                            <span class="ult-struktur-hint"><i class="bi bi-info-circle"></i> Angka lebih kecil tampil lebih dulu</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 2: DATA PEJABAT -->
            <div class="ult-struktur-section" id="section-pejabat">
                <div class="ult-struktur-section-header">
                    <div class="ult-struktur-section-icon accent"><i class="bi bi-person-badge-fill"></i></div>
                    <div>
                        <h3 class="ult-struktur-section-title">Data Pejabat</h3>
                        <p class="ult-struktur-section-desc">Informasi pejabat yang menduduki jabatan ini</p>
                    </div>
                </div>
                <div class="ult-struktur-section-body">
                    <div class="ult-struktur-grid">
                        <div class="ult-struktur-field">
                            <label class="ult-struktur-label">Nama Pejabat</label>
                            <div class="ult-struktur-input-wrap">
                                <input type="text" name="nama_pejabat" id="strukturNamaPejabat" value="<?= htmlspecialchars($val('nama_pejabat')) ?>" maxlength="150" placeholder="Contoh: Dr. Ahmad Fauzi, M.Si.">
                                <i class="bi bi-person input-icon"></i>
                            </div>
                            <div class="ult-struktur-char-count"><span id="cnt_nama_pejabat">0</span> / 150</div>
                        </div>
                        
                        <div class="ult-struktur-field">
                            <label class="ult-struktur-label">NIP</label>
                            <div class="ult-struktur-input-wrap">
                                <input type="text" name="nip" id="strukturNip" value="<?= htmlspecialchars($val('nip')) ?>" maxlength="50" placeholder="Contoh: 198012032005011002">
                                <i class="bi bi-hash input-icon"></i>
                            </div>
                            <span class="ult-struktur-hint"><i class="bi bi-info-circle"></i> Nomor Induk Pegawai</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 3: FOTO PEJABAT -->
            <div class="ult-struktur-section" id="section-foto">
                <div class="ult-struktur-section-header">
                    <div class="ult-struktur-section-icon purple"><i class="bi bi-image-fill"></i></div>
                    <div>
                        <h3 class="ult-struktur-section-title">Foto Pejabat</h3>
                        <p class="ult-struktur-section-desc">Upload foto resmi untuk ditampilkan di struktur</p>
                    </div>
                </div>
                <div class="ult-struktur-section-body">
                    <div class="ult-struktur-photo-upload" id="ultStrukturPhotoDropzone">
                        <div class="ult-struktur-photo-content">
                            <div class="ult-struktur-photo-preview" id="ultStrukturPhotoPreview">
                                <?php if ($fotoUrl): ?>
                                    <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="Foto Pejabat">
                                <?php else: ?>
                                    <div class="ult-struktur-photo-ph">
                                        <i class="bi bi-person-fill"></i>
                                        <span>Belum ada foto</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ult-struktur-photo-info">
                                <h4><i class="bi bi-camera-fill" style="color:#e67e22;"></i> Upload Foto Pejabat</h4>
                                <p>Pilih file dari perangkat atau <strong>drag & drop</strong> langsung ke area ini. Foto akan ditampilkan berbentuk lingkaran.</p>
                                <div class="ult-struktur-photo-actions">
                                    <label class="ult-struktur-photo-btn ult-struktur-photo-btn-primary">
                                        <i class="bi bi-upload"></i> Pilih File
                                        <input type="file" name="foto" id="ultStrukturPhotoInput" accept="image/jpeg,image/png,image/webp" hidden>
                                    </label>
                                    <?php if ($fotoUrl): ?>
                                    <button type="button" class="ult-struktur-photo-btn ult-struktur-photo-btn-danger" id="ultStrukturPhotoRemove">
                                        <i class="bi bi-trash3"></i> Hapus
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <div class="ult-struktur-photo-hints">
                                    <span><i class="bi bi-file-earmark-image"></i> JPG / PNG / WEBP</span>
                                    <span><i class="bi bi-aspect-ratio"></i> Rekomendasi 1:1</span>
                                    <span><i class="bi bi-file-earmark-arrow-up"></i> Maks 2MB</span>
                                </div>
                                <div class="ult-struktur-photo-file-info" id="ultStrukturPhotoFileInfo" style="margin-top:.75rem;font-size:.78rem;color:#10b981;font-weight:600;display:none;">
                                    <i class="bi bi-check-circle-fill"></i> <span id="ultStrukturPhotoFileName"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 4: LIVE PREVIEW -->
            <div class="ult-struktur-section" id="section-preview">
                <div class="ult-struktur-section-header">
                    <div class="ult-struktur-section-icon green"><i class="bi bi-eye-fill"></i></div>
                    <div>
                        <h3 class="ult-struktur-section-title">Live Preview</h3>
                        <p class="ult-struktur-section-desc">Pratinjau bagaimana jabatan ini akan tampil di halaman struktur</p>
                    </div>
                </div>
                <div class="ult-struktur-section-body">
                    <div class="ult-struktur-preview-wrap">
                        <div class="ult-struktur-preview-title">
                            <i class="bi bi-magic"></i> Pratinjau Kartu Organisasi
                        </div>
                        
                        <div class="ult-struktur-org-card" id="ultStrukturPreviewCard">
                            <div class="ult-struktur-org-avatar" id="ultStrukturPreviewAvatar">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="ult-struktur-org-jabatan" id="ultStrukturPreviewJabatan">Nama Jabatan</div>
                            <div class="ult-struktur-org-nama" id="ultStrukturPreviewNama">
                                <span class="ult-struktur-org-empty">(Nama pejabat belum diisi)</span>
                            </div>
                            <div class="ult-struktur-org-nip" id="ultStrukturPreviewNip"></div>
                        </div>
                        
                        <div class="ult-struktur-preview-meta">
                            <div class="ult-struktur-preview-meta-item">
                                <div class="ult-struktur-preview-meta-label">Level</div>
                                <div class="ult-struktur-preview-meta-value" id="ultStrukturPreviewLevel">L1</div>
                            </div>
                            <div class="ult-struktur-preview-meta-item">
                                <div class="ult-struktur-preview-meta-label">Atasan</div>
                                <div class="ult-struktur-preview-meta-value" id="ultStrukturPreviewParent">-</div>
                            </div>
                            <div class="ult-struktur-preview-meta-item">
                                <div class="ult-struktur-preview-meta-label">Urutan</div>
                                <div class="ult-struktur-preview-meta-value" id="ultStrukturPreviewSort">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ===== STICKY ACTION BAR ===== -->
            <div class="ult-struktur-actions">
                <div class="ult-struktur-actions-info">
                    <i class="bi bi-info-circle-fill" style="color:var(--admin-primary);"></i>
                    <span>Data akan disimpan ke database</span>
                    <span class="ult-struktur-changes" id="ultStrukturChanges"><i class="bi bi-exclamation-circle-fill"></i> Perubahan belum disimpan</span>
                </div>
                <div class="ult-struktur-actions-btns">
                    <a href="<?= url('admin/struktur-organisasi') ?>" class="btn btn-secondary" onclick="return confirm('Batalkan semua perubahan?');">
                        <i class="bi bi-x-lg"></i> Batal
                    </a>
                    <button type="reset" class="btn btn-outline" style="border-color:var(--admin-border);color:var(--admin-text);">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary" id="ultStrukturSubmitBtn" style="min-width:180px;">
                        <span class="ult-struktur-spinner" id="ultStrukturSpinner"></span>
                        <i class="bi bi-save-fill" id="ultStrukturSubmitIcon"></i>
                        <span id="ultStrukturSubmitText"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Jabatan' ?></span>
                    </button>
                </div>
            </div>
            
        </div>
    </div>
</form>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function(){
    'use strict';
    
    var form = document.getElementById('ultStrukturForm');
    var isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    
    // ===== INITIAL FIELD VALUES (untuk change tracker) =====
    var initialSnapshot = '';
    function serializeForm() {
        var parts = [];
        new FormData(form).forEach(function(v, k) {
            if (typeof v === 'string' && k !== 'csrf_token') parts.push(k + '=' + v);
        });
        return parts.sort().join('&');
    }
    initialSnapshot = serializeForm();
    
    // ===== SMOOTH SCROLL NAV =====
    document.querySelectorAll('.ult-struktur-nav-link[data-scroll]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var target = document.querySelector(this.getAttribute('href'));
            if (target) window.scrollTo({ top: target.offsetTop - 100, behavior: 'smooth' });
        });
    });
    
    // ===== SCROLL SPY =====
    var sections = document.querySelectorAll('.ult-struktur-section');
    var navLinks = document.querySelectorAll('.ult-struktur-nav-link');
    window.addEventListener('scroll', function() {
        var current = '';
        sections.forEach(function(section) {
            if (window.pageYOffset >= section.offsetTop - 120) current = section.getAttribute('id');
        });
        navLinks.forEach(function(link) {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) link.classList.add('active');
        });
    });
    
    // ===== PROGRESS TRACKER =====
    var progressFill = document.getElementById('ultStrukturProgressFill');
    var progressPct = document.getElementById('ultStrukturProgressPct');
    var progressText = document.getElementById('ultStrukturProgressText');
    
    function updateProgress() {
        var requiredFields = ['nama_jabatan'];
        var optionalFields = ['nama_pejabat', 'nip'];
        var photoInput = document.getElementById('ultStrukturPhotoInput');
        var hasPhoto = document.querySelector('#ultStrukturPhotoPreview img') !== null;
        
        var filled = 0;
        var total = requiredFields.length + optionalFields.length + 1; // +1 untuk foto
        
        requiredFields.forEach(function(f) {
            var inp = form.querySelector('[name="' + f + '"]');
            if (inp && inp.value.trim() !== '') filled++;
        });
        optionalFields.forEach(function(f) {
            var inp = form.querySelector('[name="' + f + '"]');
            if (inp && inp.value.trim() !== '') filled++;
        });
        if (hasPhoto || (photoInput && photoInput.files && photoInput.files.length > 0)) filled++;
        
        var pct = Math.round((filled / total) * 100);
        if (progressFill) progressFill.style.width = pct + '%';
        if (progressPct) progressPct.textContent = pct + '%';
        if (progressText) progressText.textContent = filled + ' dari ' + total + ' field terisi';
    }
    form.addEventListener('input', updateProgress);
    updateProgress();
    
    // ===== CHANGE TRACKER =====
    var changesBadge = document.getElementById('ultStrukturChanges');
    function updateChanges() {
        var current = serializeForm();
        var hasChanges = current !== initialSnapshot;
        if (changesBadge) changesBadge.classList.toggle('show', hasChanges);
    }
    form.addEventListener('input', updateChanges);
    form.addEventListener('change', updateChanges);
    
    // ===== CHAR COUNTERS =====
    ['nama_jabatan', 'nama_pejabat'].forEach(function(name) {
        var inp = form.querySelector('[name="' + name + '"]');
        var cnt = document.getElementById('cnt_' + name);
        if (inp && cnt) {
            var upd = function() { cnt.textContent = inp.value.length; };
            inp.addEventListener('input', upd);
            upd();
        }
    });
    
    // ===== CUSTOM DROPDOWN (Parent Selection) =====
    var dd = document.getElementById('ultStrukturParentDD');
    var ddTrigger = document.getElementById('strukturParentTrigger');
    var ddMenu = dd ? dd.querySelector('.ult-struktur-dd-menu') : null;
    var ddSearch = document.getElementById('strukturParentSearch');
    var hiddenInput = document.getElementById('strukturParentId');
    var labelSpan = document.getElementById('strukturParentLabel');
    var levelInput = document.getElementById('strukturLevel');
    
    if (ddTrigger && ddMenu) {
        ddTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.ult-struktur-dropdown.open').forEach(function(o) { if (o !== dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
            if (dd.classList.contains('open') && ddSearch) setTimeout(function(){ ddSearch.focus(); }, 50);
        });
        
        ddMenu.querySelectorAll('.ult-struktur-dd-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                var val = this.getAttribute('data-value');
                var lbl = this.getAttribute('data-label');
                var lvl = this.getAttribute('data-level');
                
                if (hiddenInput) hiddenInput.value = val;
                if (labelSpan) labelSpan.textContent = lbl;
                
                ddMenu.querySelectorAll('.ult-struktur-dd-item').forEach(function(i) { i.classList.remove('selected'); });
                this.classList.add('selected');
                
                if (val) ddTrigger.classList.add('has-value');
                else ddTrigger.classList.remove('has-value');
                
                // Auto-calculate level
                if (levelInput) {
                    if (val && lvl) {
                        levelInput.value = parseInt(lvl) + 1;
                    } else {
                        levelInput.value = 1;
                    }
                    updatePreview();
                }
                
                dd.classList.remove('open');
                updateChanges();
            });
        });
        
        if (ddSearch) {
            ddSearch.addEventListener('input', function() {
                var q = this.value.toLowerCase();
                ddMenu.querySelectorAll('.ult-struktur-dd-item').forEach(function(item) {
                    var text = item.textContent.toLowerCase();
                    item.style.display = text.includes(q) ? '' : 'none';
                });
            });
        }
    }
    document.addEventListener('click', function() {
        document.querySelectorAll('.ult-struktur-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
    });
    
    // ===== PHOTO UPLOAD =====
    var photoInput = document.getElementById('ultStrukturPhotoInput');
    var photoPreview = document.getElementById('ultStrukturPhotoPreview');
    var dropzone = document.getElementById('ultStrukturPhotoDropzone');
    var fileInfo = document.getElementById('ultStrukturPhotoFileInfo');
    var fileName = document.getElementById('ultStrukturPhotoFileName');
    var removeBtn = document.getElementById('ultStrukturPhotoRemove');
    var originalPhotoSrc = photoPreview && photoPreview.querySelector('img') ? photoPreview.querySelector('img').src : '';
    
    function applyPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        if (file.size > 2 * 1024 * 1024) {
            if (window.showToast) showToast('Ukuran file maksimal 2MB!', 'error');
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            photoPreview.innerHTML = '<img src="' + e.target.result + '" alt="Foto Preview">';
            if (fileInfo && fileName) {
                fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                fileInfo.style.display = 'flex';
            }
            updatePreview();
            updateChanges();
        };
        reader.readAsDataURL(file);
    }
    
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) applyPreview(this.files[0]);
        });
    }
    
    // Drag & Drop
    if (dropzone) {
        ['dragover', 'dragenter'].forEach(function(ev) {
            dropzone.addEventListener(ev, function(e) { e.preventDefault(); dropzone.classList.add('dragover'); });
        });
        ['dragleave', 'drop'].forEach(function(ev) {
            dropzone.addEventListener(ev, function(e) { e.preventDefault(); dropzone.classList.remove('dragover'); });
        });
        dropzone.addEventListener('drop', function(e) {
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                photoInput.files = e.dataTransfer.files;
                applyPreview(e.dataTransfer.files[0]);
            }
        });
    }
    
    // Remove photo
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            if (!confirm('Hapus foto yang ada?')) return;
            if (photoInput) photoInput.value = '';
            photoPreview.innerHTML = '<div class="ult-struktur-photo-ph"><i class="bi bi-person-fill"></i><span>Belum ada foto</span></div>';
            if (fileInfo) fileInfo.style.display = 'none';
            updatePreview();
            updateChanges();
        });
    }
    
    // ===== LIVE PREVIEW =====
    var previewCard = document.getElementById('ultStrukturPreviewCard');
    var previewAvatar = document.getElementById('ultStrukturPreviewAvatar');
    var previewJabatan = document.getElementById('ultStrukturPreviewJabatan');
    var previewNama = document.getElementById('ultStrukturPreviewNama');
    var previewNip = document.getElementById('ultStrukturPreviewNip');
    var previewLevel = document.getElementById('ultStrukturPreviewLevel');
    var previewParent = document.getElementById('ultStrukturPreviewParent');
    var previewSort = document.getElementById('ultStrukturPreviewSort');
    
    var namaJabatanInput = form.querySelector('[name="nama_jabatan"]');
    var namaPejabatInput = form.querySelector('[name="nama_pejabat"]');
    var nipInput = form.querySelector('[name="nip"]');
    var sortOrderInput = form.querySelector('[name="sort_order"]');
    
    function updatePreview() {
        // Jabatan
        var jabatan = namaJabatanInput ? namaJabatanInput.value.trim() : '';
        if (previewJabatan) previewJabatan.textContent = jabatan || 'Nama Jabatan';
        
        // Toggle leader style jika level 1
        var level = levelInput ? parseInt(levelInput.value) : 1;
        if (previewCard) previewCard.classList.toggle('leader', level === 1);
        
        // Nama
        var nama = namaPejabatInput ? namaPejabatInput.value.trim() : '';
        if (previewNama) {
            if (nama) {
                previewNama.textContent = nama;
            } else {
                previewNama.innerHTML = '<span class="ult-struktur-org-empty">(Nama pejabat belum diisi)</span>';
            }
        }
        
        // Avatar: pakai foto jika ada, else inisial
        var photo = photoPreview ? photoPreview.querySelector('img') : null;
        if (previewAvatar) {
            if (photo) {
                previewAvatar.innerHTML = '<img src="' + photo.src + '" alt="">';
            } else if (nama) {
                previewAvatar.innerHTML = nama.charAt(0).toUpperCase();
            } else {
                previewAvatar.innerHTML = '<i class="bi bi-person-fill"></i>';
            }
        }
        
        // NIP
        var nip = nipInput ? nipInput.value.trim() : '';
        if (previewNip) previewNip.textContent = nip || '';
        
        // Meta
        if (previewLevel) previewLevel.textContent = 'L' + level;
        if (previewParent) {
            var parentLabel = labelSpan ? labelSpan.textContent : '-';
            previewParent.textContent = parentLabel === 'Jabatan Tertinggi (Level 1)' ? 'Tertinggi' : parentLabel;
        }
        if (previewSort) previewSort.textContent = sortOrderInput ? sortOrderInput.value : '0';
    }
    
    if (namaJabatanInput) namaJabatanInput.addEventListener('input', updatePreview);
    if (namaPejabatInput) namaPejabatInput.addEventListener('input', updatePreview);
    if (nipInput) nipInput.addEventListener('input', updatePreview);
    if (sortOrderInput) sortOrderInput.addEventListener('input', updatePreview);
    if (levelInput) levelInput.addEventListener('change', updatePreview);
    
    updatePreview();
    
    // ===== SUBMIT HANDLING =====
    var submitBtn = document.getElementById('ultStrukturSubmitBtn');
    var spinner = document.getElementById('ultStrukturSpinner');
    var submitIcon = document.getElementById('ultStrukturSubmitIcon');
    var submitText = document.getElementById('ultStrukturSubmitText');
    
    form.addEventListener('submit', function(e) {
        // Validasi
        var namaJabatan = namaJabatanInput ? namaJabatanInput.value.trim() : '';
        if (!namaJabatan) {
            e.preventDefault();
            namaJabatanInput.classList.add('invalid');
            var err = document.getElementById('err_nama_jabatan');
            if (err) err.classList.add('show');
            namaJabatanInput.focus();
            if (window.showToast) showToast('Nama jabatan wajib diisi', 'error');
            return false;
        }
        
        // Loading state
        if (submitBtn) submitBtn.disabled = true;
        if (spinner) spinner.style.display = 'inline-block';
        if (submitIcon) submitIcon.style.display = 'none';
        if (submitText) submitText.textContent = 'Menyimpan...';
    });
    
    namaJabatanInput && namaJabatanInput.addEventListener('input', function() {
        this.classList.remove('invalid');
        var err = document.getElementById('err_nama_jabatan');
        if (err) err.classList.remove('show');
    });
    
    // ===== CTRL+S SHORTCUT =====
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            form.requestSubmit ? form.requestSubmit() : form.submit();
        }
    });
    
    // ===== BEFOREUNLOAD WARNING =====
    window.addEventListener('beforeunload', function(e) {
        var current = serializeForm();
        if (current !== initialSnapshot) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    // ===== RESET HANDLER =====
    form.addEventListener('reset', function(e) {
        if (!confirm('Batalkan semua perubahan dan kembalikan ke nilai awal?')) {
            e.preventDefault();
            return false;
        }
        setTimeout(function() {
            initialSnapshot = serializeForm();
            updateProgress();
            updateChanges();
            updatePreview();
        }, 50);
    });
})();
</script>