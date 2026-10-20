<?php
use App\Helpers\Auth;
$isSuperAdmin = Auth::hasRole('super_admin');
$isEdit = !empty($data['id']);

if (!$isSuperAdmin) {
    echo '<div style="padding:2rem;text-align:center;"><h2>Akses Ditolak</h2><p>Anda tidak memiliki izin untuk mengakses halaman ini.</p><a href="' . url('admin') . '" class="btn btn-primary">Kembali</a></div>';
    return;
}

// ===== ROLE MAP =====
$roleMap = [
    'super_admin'         => ['Super Admin', 'bi-shield-fill-check', '#ef4444', 'Akses penuh ke semua modul'],
    'admin_bauk'          => ['Admin BAUK', 'bi-briefcase-fill', '#3b82f6', 'Kelola BAUK & laporan'],
    'admin_kepegawaian'   => ['Admin Kepegawaian', 'bi-person-badge-fill', '#10b981', 'Kelola data pegawai'],
    'admin_keuangan'      => ['Admin Keuangan', 'bi-wallet2', '#f59e0b', 'Kelola transaksi keuangan'],
    'admin_aset'          => ['Admin Aset', 'bi-box-seam-fill', '#8b5cf6', 'Kelola inventaris aset'],
    'staff'               => ['Staff', 'bi-person-workspace', '#06b6d4', 'Akses terbatas untuk input data'],
    'viewer'              => ['Viewer', 'bi-eye-fill', '#64748b', 'Hanya bisa melihat data'],
];

$val = function($key, $default = '') use ($data) {
    return $data[$key] ?? ($_POST[$key] ?? $default);
};

$avatarUrl = !empty($data['avatar']) ? url('uploads/profil/' . $data['avatar']) : null;
$pageTitle = $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru';
$pageDesc = $isEdit ? 'Perbarui informasi akun pengguna' : 'Buat akun baru untuk pengguna sistem';
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== LAYOUT ===== */
.ult-users-form-layout{display:grid;grid-template-columns:280px 1fr;gap:1.5rem;}
@media (max-width:992px){.ult-users-form-layout{grid-template-columns:1fr;}}

/* ===== SIDEBAR NAV ===== */
.ult-users-form-nav{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);padding:1.25rem;height:fit-content;position:sticky;top:100px;}
.ult-users-form-nav-title{font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--admin-text-muted);font-weight:700;margin-bottom:.75rem;padding-bottom:.5rem;border-bottom:1px solid var(--admin-border);}
.ult-users-form-nav-link{display:flex;align-items:center;gap:.75rem;padding:.625rem .875rem;border-radius:var(--radius-md);color:var(--admin-text-secondary);text-decoration:none;font-size:.85rem;font-weight:600;transition:all .2s;margin-bottom:.25rem;}
.ult-users-form-nav-link:hover{background:var(--admin-bg);color:var(--admin-text);}
.ult-users-form-nav-link.active{background:var(--admin-primary);color:#fff;}
.ult-users-form-nav-link.active i{color:#fff;}
.ult-users-form-nav-link i{font-size:1rem;color:var(--admin-primary);width:20px;text-align:center;}

/* ===== SECTIONS ===== */
.ult-users-form-section{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1.5rem;}
.ult-users-form-section-header{padding:1.25rem 1.5rem;background:linear-gradient(to right, var(--admin-bg), transparent);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:1rem;}
.ult-users-form-section-icon{width:44px;height:44px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:#fff;flex-shrink:0;}
.ult-users-form-section-title{font-size:1.1rem;font-weight:700;color:var(--admin-text);margin:0;}
.ult-users-form-section-desc{font-size:.8rem;color:var(--admin-text-secondary);margin:.25rem 0 0;}
.ult-users-form-section-body{padding:1.5rem;}

/* ===== FORM GRID ===== */
.ult-users-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem;}
.ult-users-form-grid.cols-1{grid-template-columns:1fr;}
@media (max-width:768px){.ult-users-form-grid{grid-template-columns:1fr;}}

/* ===== FIELDS ===== */
.ult-users-field{display:flex;flex-direction:column;gap:.375rem;}
.ult-users-field.full{grid-column:1/-1;}
.ult-users-label{font-size:.825rem;font-weight:600;color:var(--admin-text);display:flex;align-items:center;gap:.375rem;}
.ult-users-label .req{color:var(--admin-danger);}
.ult-users-input-wrap{position:relative;}
.ult-users-input-wrap .input-icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);font-size:1rem;pointer-events:none;transition:color .2s;z-index:2;}
.ult-users-input-wrap input,.ult-users-input-wrap select,.ult-users-input-wrap textarea{width:100%;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);font-size:.9rem;background:var(--admin-bg);color:var(--admin-text);transition:all .2s;font-family:inherit;}
.ult-users-input-wrap textarea{padding-left:1rem;resize:vertical;min-height:80px;}
.ult-users-input-wrap input:read-only{background:var(--admin-bg);color:var(--admin-text-secondary);cursor:not-allowed;}
.ult-users-input-wrap input:focus,.ult-users-input-wrap select:focus,.ult-users-input-wrap textarea:focus{outline:none;border-color:var(--admin-primary);background:#fff;box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-users-input-wrap input:focus ~ .input-icon{color:var(--admin-primary);}
.ult-users-hint{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;display:flex;align-items:center;gap:.25rem;}
.ult-users-hint i{font-size:.7rem;}
.ult-users-hint.success{color:var(--admin-success);}
.ult-users-hint.danger{color:var(--admin-danger);}

/* ===== AVATAR UPLOAD ===== */
.ult-users-avatar-upload{display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;}
.ult-users-avatar-preview{position:relative;}
.ult-users-avatar-preview img,.ult-users-avatar-preview .ult-users-avatar-initial{width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid var(--admin-border);box-shadow:0 4px 15px rgba(0,0,0,.1);}
.ult-users-avatar-initial{display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:700;color:#fff;background:linear-gradient(135deg,var(--admin-primary),var(--admin-primary-light));}
.ult-users-avatar-change{position:absolute;bottom:0;right:0;width:32px;height:32px;border-radius:50%;background:var(--admin-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.85rem;cursor:pointer;border:2px solid #fff;transition:all .2s;}
.ult-users-avatar-change:hover{background:var(--admin-primary-dark);transform:scale(1.1);}
.ult-users-avatar-change input{position:absolute;inset:0;opacity:0;cursor:pointer;}
.ult-users-avatar-info h4{font-size:.95rem;margin-bottom:.25rem;color:var(--admin-text);}
.ult-users-avatar-info p{font-size:.8rem;color:var(--admin-text-secondary);margin:0;}

/* ===== CUSTOM DROPDOWN (Role Selector) ===== */
.ult-users-dropdown{position:relative;}
.ult-users-dropdown-trigger{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;background:var(--admin-bg);transition:all .2s;min-height:48px;}
.ult-users-dropdown-trigger:hover{border-color:var(--admin-primary);}
.ult-users-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.03);}
.ult-users-dropdown-trigger .role-icon{width:36px;height:36px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:1rem;color:#fff;flex-shrink:0;}
.ult-users-dropdown-trigger .role-info{flex:1;min-width:0;}
.ult-users-dropdown-trigger .role-name{font-weight:700;color:var(--admin-text);font-size:.9rem;}
.ult-users-dropdown-trigger .role-desc{font-size:.72rem;color:var(--admin-text-muted);margin-top:.125rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-users-dropdown-trigger i.chevron{color:var(--admin-text-muted);transition:transform .2s;}
.ult-users-dropdown.open .ult-users-dropdown-trigger{border-color:var(--admin-primary);box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-users-dropdown.open .ult-users-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-users-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;right:0;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:320px;overflow-y:auto;}
.ult-users-dropdown.open .ult-users-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-users-dd-item{display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;cursor:pointer;transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-users-dd-item:last-child{border-bottom:none;}
.ult-users-dd-item:hover{background:var(--admin-bg);}
.ult-users-dd-item.selected{background:rgba(26,82,118,.06);}
.ult-users-dd-item .role-icon{width:36px;height:36px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:1rem;color:#fff;flex-shrink:0;}
.ult-users-dd-item .role-info{flex:1;min-width:0;}
.ult-users-dd-item .role-name{font-weight:700;color:var(--admin-text);font-size:.85rem;}
.ult-users-dd-item .role-desc{font-size:.72rem;color:var(--admin-text-muted);margin-top:.125rem;}
.ult-users-dd-item i.bi-check-lg{color:var(--admin-primary);opacity:0;flex-shrink:0;}
.ult-users-dd-item.selected i.bi-check-lg{opacity:1;}

/* ===== PASSWORD GENERATOR ===== */
.ult-users-pass-gen{display:flex;gap:.5rem;margin-top:.5rem;}
.ult-users-pass-gen-btn{padding:.5rem .875rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);font-size:.78rem;font-weight:600;color:var(--admin-text);cursor:pointer;display:inline-flex;align-items:center;gap:.375rem;transition:all .2s;}
.ult-users-pass-gen-btn:hover{background:var(--admin-primary);color:#fff;border-color:var(--admin-primary);}
.ult-users-pass-gen-btn i{font-size:.85rem;}

/* ===== PASSWORD STRENGTH ===== */
.ult-users-pass-strength{margin-top:.75rem;}
.ult-users-pass-strength-bar{height:6px;background:var(--admin-bg);border-radius:50px;overflow:hidden;margin-bottom:.5rem;}
.ult-users-pass-strength-fill{height:100%;border-radius:50px;transition:all .3s;width:0%;}
.ult-users-pass-strength-fill.weak{background:#ef4444;width:25%;}
.ult-users-pass-strength-fill.fair{background:#f59e0b;width:50%;}
.ult-users-pass-strength-fill.good{background:#3b82f6;width:75%;}
.ult-users-pass-strength-fill.strong{background:#10b981;width:100%;}
.ult-users-pass-strength-label{font-size:.75rem;font-weight:600;margin-bottom:.5rem;}
.ult-users-pass-checks{display:grid;grid-template-columns:repeat(2,1fr);gap:.375rem;}
.ult-users-pass-check{display:flex;align-items:center;gap:.375rem;font-size:.75rem;color:var(--admin-text-muted);transition:color .2s;}
.ult-users-pass-check.pass{color:var(--admin-success);}
.ult-users-pass-check i{font-size:.7rem;}

/* ===== TOGGLE SWITCH ===== */
.ult-users-toggle{display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;transition:all .2s;}
.ult-users-toggle:hover{border-color:var(--admin-primary);}
.ult-users-toggle.active{border-color:var(--admin-primary);background:rgba(26,82,118,.03);}
.ult-users-toggle-switch{width:44px;height:24px;border-radius:50px;background:var(--admin-border);position:relative;transition:background .2s;flex-shrink:0;}
.ult-users-toggle-switch::after{content:'';position:absolute;top:2px;left:2px;width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,.2);transition:transform .2s;}
.ult-users-toggle.active .ult-users-toggle-switch{background:var(--admin-primary);}
.ult-users-toggle.active .ult-users-toggle-switch::after{transform:translateX(20px);}
.ult-users-toggle-info{flex:1;}
.ult-users-toggle-title{font-size:.85rem;font-weight:700;color:var(--admin-text);}
.ult-users-toggle-desc{font-size:.72rem;color:var(--admin-text-muted);margin-top:.125rem;}

/* ===== PREVIEW CARD ===== */
.ult-users-preview{background:linear-gradient(135deg, var(--admin-bg), var(--admin-surface));border:1px solid var(--admin-border);border-radius:var(--radius-lg);padding:1.5rem;margin-top:1rem;}
.ult-users-preview-title{font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--admin-text-muted);font-weight:700;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
.ult-users-preview-title i{color:var(--admin-primary);}
.ult-users-preview-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);padding:1.25rem;display:flex;align-items:center;gap:1rem;}
.ult-users-preview-avatar{width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:#fff;background:linear-gradient(135deg,var(--admin-primary),var(--admin-primary-light));flex-shrink:0;}
.ult-users-preview-info{flex:1;min-width:0;}
.ult-users-preview-name{font-weight:700;color:var(--admin-text);font-size:1.05rem;margin-bottom:.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-users-preview-email{font-size:.8rem;color:var(--admin-text-secondary);display:flex;align-items:center;gap:.375rem;}
.ult-users-preview-email i{font-size:.7rem;color:var(--admin-primary);}
.ult-users-preview-role{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;border-radius:50px;font-size:.72rem;font-weight:700;color:#fff;margin-top:.5rem;}

/* ===== STICKY ACTION BAR ===== */
.ult-users-sticky-actions{position:sticky;bottom:0;background:var(--admin-surface);border-top:1px solid var(--admin-border);padding:1rem 1.5rem;display:flex;justify-content:flex-end;gap:.75rem;z-index:50;box-shadow:0 -4px 15px rgba(0,0,0,.05);margin:0 -1.5rem -1.5rem;border-radius:0 0 var(--radius-lg) var(--radius-lg);}
@media (max-width:992px){.ult-users-sticky-actions{position:fixed;bottom:0;left:0;right:0;margin:0;border-radius:0;z-index:1000;padding:1rem;box-shadow:0 -4px 20px rgba(0,0,0,.1);}}

/* ===== ANIMATIONS ===== */
@keyframes ult-users-fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.ult-users-form-section{animation:ult-users-fadeIn .4s ease forwards;}
@keyframes ult-users-spin{to{transform:rotate(360deg);}}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= $pageTitle ?></h1>
        <p class="page-subtitle"><?= $pageDesc ?></p>
    </div>
    <a href="<?= url('admin/pengguna') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Daftar</a>
</div>

<form method="POST" action="<?= url('admin/pengguna/simpan') ?>" enctype="multipart/form-data" id="ultUsersForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
    <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
    <?php endif; ?>
    
    <div class="ult-users-form-layout">
        
        <!-- ===== SIDEBAR NAVIGATION ===== -->
        <aside class="ult-users-form-nav d-none-mobile">
            <div class="ult-users-form-nav-title">Navigasi Form</div>
            <a href="#section-identitas" class="ult-users-form-nav-link active" data-scroll><i class="bi bi-person-badge-fill"></i> Identitas Akun</a>
            <a href="#section-pribadi" class="ult-users-form-nav-link" data-scroll><i class="bi bi-person-fill"></i> Informasi Pribadi</a>
            <a href="#section-role" class="ult-users-form-nav-link" data-scroll><i class="bi bi-shield-fill-check"></i> Role & Akses</a>
            <a href="#section-keamanan" class="ult-users-form-nav-link" data-scroll><i class="bi bi-key-fill"></i> Keamanan</a>
            <a href="#section-preview" class="ult-users-form-nav-link" data-scroll><i class="bi bi-eye-fill"></i> Preview</a>
            
            <div style="margin-top:2rem;padding-top:1rem;border-top:1px solid var(--admin-border);">
                <div class="ult-users-form-nav-title" style="margin-bottom:.5rem;">Progress</div>
                <div style="background:var(--admin-bg);border-radius:50px;height:6px;overflow:hidden;">
                    <div id="ultUsersProgress" style="width:0%;height:100%;background:var(--admin-primary);transition:width .3s;"></div>
                </div>
                <div style="font-size:.75rem;color:var(--admin-text-muted);margin-top:.375rem;text-align:right;" id="ultUsersProgressText">0% Terisi</div>
            </div>
        </aside>
        
        <!-- ===== MAIN FORM CONTENT ===== -->
        <div style="padding-bottom:80px;">
            
            <!-- SECTION 1: IDENTITAS AKUN -->
            <div class="ult-users-form-section" id="section-identitas">
                <div class="ult-users-form-section-header">
                    <div class="ult-users-form-section-icon" style="background:linear-gradient(135deg,var(--admin-primary),var(--admin-primary-light));"><i class="bi bi-person-badge-fill"></i></div>
                    <div>
                        <h3 class="ult-users-form-section-title">Identitas Akun</h3>
                        <p class="ult-users-form-section-desc">Username dan informasi login dasar.</p>
                    </div>
                </div>
                <div class="ult-users-form-section-body">
                    <div class="ult-users-form-grid">
                        <div class="ult-users-field full">
                            <label class="ult-users-label">Foto Profil</label>
                            <div class="ult-users-avatar-upload">
                                <div class="ult-users-avatar-preview">
                                    <?php if ($avatarUrl): ?>
                                        <img src="<?= $avatarUrl ?>" alt="Avatar" id="ultUserAvatarPreview">
                                    <?php else: ?>
                                        <div class="ult-users-avatar-initial" id="ultUserAvatarPreview"><?= strtoupper(substr($val('full_name', 'U'), 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <label class="ult-users-avatar-change" title="Ganti Foto">
                                        <i class="bi bi-camera-fill"></i>
                                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" id="ultUserAvatarInput">
                                    </label>
                                </div>
                                <div class="ult-users-avatar-info">
                                    <h4>Upload Foto Profil</h4>
                                    <p>Format JPG / PNG / WEBP. Maksimal 2 MB.</p>
                                    <?php if ($avatarUrl): ?>
                                        <p style="margin-top:.25rem;"><a href="#" onclick="ultRemoveAvatar();return false;" style="color:var(--admin-danger);font-size:.8rem;"><i class="bi bi-trash3"></i> Hapus foto saat ini</a></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ult-users-field">
                            <label class="ult-users-label">Username <span class="req">*</span></label>
                            <div class="ult-users-input-wrap">
                                <input type="text" name="username" id="userUsername" value="<?= htmlspecialchars($val('username')) ?>" required maxlength="50" placeholder="contoh: john_doe" <?= $isEdit ? 'readonly' : '' ?>>
                                <i class="bi bi-at input-icon"></i>
                            </div>
                            <span class="ult-users-hint"><i class="bi bi-info-circle"></i> <?= $isEdit ? 'Username tidak dapat diubah' : 'Huruf kecil, angka, underscore. Min 4 karakter.' ?></span>
                        </div>
                        
                        <div class="ult-users-field">
                            <label class="ult-users-label">Email <span class="req">*</span></label>
                            <div class="ult-users-input-wrap">
                                <input type="email" name="email" id="userEmail" value="<?= htmlspecialchars($val('email')) ?>" required maxlength="100" placeholder="contoh: john@unimof.ac.id">
                                <i class="bi bi-envelope input-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 2: INFORMASI PRIBADI -->
            <div class="ult-users-form-section" id="section-pribadi">
                <div class="ult-users-form-section-header">
                    <div class="ult-users-form-section-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-person-fill"></i></div>
                    <div>
                        <h3 class="ult-users-form-section-title">Informasi Pribadi</h3>
                        <p class="ult-users-form-section-desc">Data lengkap pengguna.</p>
                    </div>
                </div>
                <div class="ult-users-form-section-body">
                    <div class="ult-users-form-grid">
                        <div class="ult-users-field">
                            <label class="ult-users-label">Nama Lengkap <span class="req">*</span></label>
                            <div class="ult-users-input-wrap">
                                <input type="text" name="full_name" id="userFullName" value="<?= htmlspecialchars($val('full_name')) ?>" required maxlength="100" placeholder="Contoh: John Doe, S.Kom.">
                                <i class="bi bi-person input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-users-field">
                            <label class="ult-users-label">No. Telepon</label>
                            <div class="ult-users-input-wrap">
                                <input type="tel" name="phone" value="<?= htmlspecialchars($val('phone')) ?>" maxlength="20" placeholder="08xx-xxxx-xxxx">
                                <i class="bi bi-telephone input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-users-field full">
                            <label class="ult-users-label">Status Akun</label>
                            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                                <label class="ult-users-toggle <?= ($val('status', 'active') === 'active') ? 'active' : '' ?>" data-toggle>
                                    <input type="hidden" name="status" value="<?= $val('status', 'active') ?>">
                                    <div class="ult-users-toggle-switch"></div>
                                    <div class="ult-users-toggle-info">
                                        <div class="ult-users-toggle-title">Akun Aktif</div>
                                        <div class="ult-users-toggle-desc">Pengguna dapat login dan mengakses sistem</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 3: ROLE & AKSES -->
            <div class="ult-users-form-section" id="section-role">
                <div class="ult-users-form-section-header">
                    <div class="ult-users-form-section-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-shield-fill-check"></i></div>
                    <div>
                        <h3 class="ult-users-form-section-title">Role & Hak Akses</h3>
                        <p class="ult-users-form-section-desc">Tentukan level akses pengguna dalam sistem.</p>
                    </div>
                </div>
                <div class="ult-users-form-section-body">
                    <div class="ult-users-form-grid cols-1">
                        <div class="ult-users-field">
                            <label class="ult-users-label">Pilih Role <span class="req">*</span></label>
                            <input type="hidden" name="role" id="userRoleValue" value="<?= htmlspecialchars($val('role', 'staff')) ?>">
                            <div class="ult-users-dropdown" id="ultRoleDropdown">
                                <div class="ult-users-dropdown-trigger has-value" id="ultRoleTrigger">
                                    <?php 
                                    $currentRole = $val('role', 'staff');
                                    [$rLbl, $rIcn, $rClr, $rDesc] = $roleMap[$currentRole] ?? ['Staff', 'bi-person-workspace', '#06b6d4', 'Akses terbatas'];
                                    ?>
                                    <div class="role-icon" style="background:linear-gradient(135deg,<?= $rClr ?>,<?= $rClr ?>cc);"><i class="bi <?= $rIcn ?>"></i></div>
                                    <div class="role-info">
                                        <div class="role-name"><?= $rLbl ?></div>
                                        <div class="role-desc"><?= $rDesc ?></div>
                                    </div>
                                    <i class="bi bi-chevron-down chevron"></i>
                                </div>
                                <div class="ult-users-dropdown-menu">
                                    <?php foreach ($roleMap as $key => [$lbl, $icn, $clr, $desc]): ?>
                                    <div class="ult-users-dd-item <?= $currentRole === $key ? 'selected' : '' ?>" data-value="<?= $key ?>" data-label="<?= $lbl ?>" data-desc="<?= htmlspecialchars($desc) ?>" data-color="<?= $clr ?>" data-icon="<?= $icn ?>">
                                        <div class="role-icon" style="background:linear-gradient(135deg,<?= $clr ?>,<?= $clr ?>cc);"><i class="bi <?= $icn ?>"></i></div>
                                        <div class="role-info">
                                            <div class="role-name"><?= $lbl ?></div>
                                            <div class="role-desc"><?= $desc ?></div>
                                        </div>
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <span class="ult-users-hint"><i class="bi bi-info-circle"></i> Role menentukan modul yang dapat diakses pengguna</span>
                        </div>
                        
                        <div class="ult-users-field">
                            <label class="ult-users-label">Opsi Keamanan Tambahan</label>
                            <div style="display:flex;flex-direction:column;gap:.75rem;">
                                <label class="ult-users-toggle" data-toggle>
                                    <input type="hidden" name="two_factor_enabled" value="0">
                                    <input type="checkbox" name="two_factor_enabled_cb" value="1" style="display:none;" <?= $val('two_factor_enabled') ? 'checked' : '' ?>>
                                    <div class="ult-users-toggle-switch"></div>
                                    <div class="ult-users-toggle-info">
                                        <div class="ult-users-toggle-title"><i class="bi bi-shield-lock-fill" style="color:var(--admin-primary);"></i> Aktifkan 2FA</div>
                                        <div class="ult-users-toggle-desc">Wajib verifikasi 2 langkah saat login</div>
                                    </div>
                                </label>
                                <label class="ult-users-toggle" data-toggle>
                                    <input type="hidden" name="force_password_change" value="0">
                                    <input type="checkbox" name="force_password_change_cb" value="1" style="display:none;" <?= $val('force_password_change') ? 'checked' : '' ?>>
                                    <div class="ult-users-toggle-switch"></div>
                                    <div class="ult-users-toggle-info">
                                        <div class="ult-users-toggle-title"><i class="bi bi-key-fill" style="color:#f59e0b;"></i> Wajib Ganti Password</div>
                                        <div class="ult-users-toggle-desc">Pengguna harus mengganti password saat login pertama</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 4: KEAMANAN -->
            <div class="ult-users-form-section" id="section-keamanan">
                <div class="ult-users-form-section-header">
                    <div class="ult-users-form-section-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="bi bi-key-fill"></i></div>
                    <div>
                        <h3 class="ult-users-form-section-title">Keamanan Akun</h3>
                        <p class="ult-users-form-section-desc"><?= $isEdit ? 'Kosongkan jika tidak ingin mengubah password' : 'Atur password untuk akun baru' ?></p>
                    </div>
                </div>
                <div class="ult-users-form-section-body">
                    <div class="ult-users-form-grid cols-1">
                        <div class="ult-users-field">
                            <label class="ult-users-label">Password <?= $isEdit ? '' : '<span class="req">*</span>' ?></label>
                            <div class="ult-users-input-wrap">
                                <input type="password" name="password" id="userPassword" <?= $isEdit ? '' : 'required' ?> minlength="8" placeholder="Minimal 8 karakter dengan kombinasi kuat">
                                <i class="bi bi-lock-fill input-icon"></i>
                            </div>
                            
                            <!-- Password Generator -->
                            <div class="ult-users-pass-gen">
                                <button type="button" class="ult-users-pass-gen-btn" onclick="ultGeneratePassword()"><i class="bi bi-magic"></i> Generate Password</button>
                                <button type="button" class="ult-users-pass-gen-btn" onclick="ultTogglePasswordVisibility()"><i class="bi bi-eye-fill"></i> Lihat</button>
                            </div>
                            
                            <!-- Password Strength Meter -->
                            <div class="ult-users-pass-strength" id="ultPassStrength" style="display:none;">
                                <div class="ult-users-pass-strength-bar">
                                    <div class="ult-users-pass-strength-fill" id="ultPassStrengthFill"></div>
                                </div>
                                <div class="ult-users-pass-strength-label" id="ultPassStrengthLabel">Kekuatan password</div>
                                <div class="ult-users-pass-checks">
                                    <div class="ult-users-pass-check" id="check-length"><i class="bi bi-circle"></i> Min. 8 karakter</div>
                                    <div class="ult-users-pass-check" id="check-upper"><i class="bi bi-circle"></i> Huruf kapital</div>
                                    <div class="ult-users-pass-check" id="check-lower"><i class="bi bi-circle"></i> Huruf kecil</div>
                                    <div class="ult-users-pass-check" id="check-number"><i class="bi bi-circle"></i> Angka</div>
                                    <div class="ult-users-pass-check" id="check-special"><i class="bi bi-circle"></i> Karakter spesial</div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!$isEdit): ?>
                        <div class="ult-users-field">
                            <label class="ult-users-label">Konfirmasi Password <span class="req">*</span></label>
                            <div class="ult-users-input-wrap">
                                <input type="password" id="userPasswordConfirm" required placeholder="Ketik ulang password">
                                <i class="bi bi-lock-fill input-icon"></i>
                            </div>
                            <span class="ult-users-hint" id="ultPassMatch" style="display:none;"></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 5: PREVIEW -->
            <div class="ult-users-form-section" id="section-preview">
                <div class="ult-users-form-section-header">
                    <div class="ult-users-form-section-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-eye-fill"></i></div>
                    <div>
                        <h3 class="ult-users-form-section-title">Preview Pengguna</h3>
                        <p class="ult-users-form-section-desc">Pratinjau tampilan pengguna di sistem.</p>
                    </div>
                </div>
                <div class="ult-users-form-section-body">
                    <div class="ult-users-preview">
                        <div class="ult-users-preview-title"><i class="bi bi-magic"></i> Tampilan Live Preview</div>
                        <div class="ult-users-preview-card">
                            <div class="ult-users-preview-avatar" id="previewAvatar"><?= strtoupper(substr($val('full_name', 'U'), 0, 1)) ?></div>
                            <div class="ult-users-preview-info">
                                <div class="ult-users-preview-name" id="previewName"><?= $isEdit ? htmlspecialchars($data['full_name']) : 'Nama Pengguna' ?></div>
                                <div class="ult-users-preview-email" id="previewEmail">
                                    <i class="bi bi-envelope-fill"></i>
                                    <span><?= $isEdit ? htmlspecialchars($data['email']) : 'email@unimof.ac.id' ?></span>
                                </div>
                                <?php 
                                [$rLbl, $rIcn, $rClr, $rDesc] = $roleMap[$currentRole] ?? ['Staff', 'bi-person-workspace', '#06b6d4', ''];
                                ?>
                                <div class="ult-users-preview-role" id="previewRole" style="background:<?= $rClr ?>;">
                                    <i class="bi <?= $rIcn ?>"></i> <?= $rLbl ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- STICKY ACTION BAR -->
            <div class="ult-users-sticky-actions">
                <a href="<?= url('admin/pengguna') ?>" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Batal</a>
                <button type="reset" class="btn btn-outline" style="border-color:var(--admin-border);color:var(--admin-text);"><i class="bi bi-arrow-counterclockwise"></i> Reset Form</button>
                <button type="submit" class="btn btn-primary" id="ultUsersSubmitBtn" style="min-width:180px;">
                    <span class="ult-spinner" style="display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:ult-users-spin .7s linear infinite;margin-right:.5rem;"></span>
                    <i class="bi bi-save-fill"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Buat Pengguna' ?>
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
    document.querySelectorAll('.ult-users-form-nav-link[data-scroll]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) window.scrollTo({ top: target.offsetTop - 100, behavior: 'smooth' });
        });
    });
    
    // ===== SCROLL SPY =====
    const sections = document.querySelectorAll('.ult-users-form-section');
    const navLinks = document.querySelectorAll('.ult-users-form-nav-link');
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
    const form = document.getElementById('ultUsersForm');
    const progressBar = document.getElementById('ultUsersProgress');
    const progressText = document.getElementById('ultUsersProgressText');
    function updateProgress() {
        const inputs = form.querySelectorAll('input[required]:not([type="hidden"]):not([type="file"])');
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
    
    // ===== AVATAR PREVIEW =====
    const avatarInput = document.getElementById('ultUserAvatarInput');
    const avatarPreview = document.getElementById('ultUserAvatarPreview');
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    if (typeof showToast !== 'undefined') showToast('Ukuran file maksimal 2 MB!', 'error');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (avatarPreview.tagName === 'IMG') {
                        avatarPreview.src = e.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Avatar Preview';
                        img.id = 'ultUserAvatarPreview';
                        img.style.cssText = 'width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid var(--admin-border);';
                        avatarPreview.parentNode.replaceChild(img, avatarPreview);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    window.ultRemoveAvatar = function() {
        if (avatarInput) avatarInput.value = '';
        if (typeof showToast !== 'undefined') showToast('Foto akan dihapus saat disimpan', 'info');
    };
    
    // ===== ROLE DROPDOWN =====
    const roleDropdown = document.getElementById('ultRoleDropdown');
    const roleTrigger = document.getElementById('ultRoleTrigger');
    const roleValue = document.getElementById('userRoleValue');
    const previewRole = document.getElementById('previewRole');
    
    if (roleDropdown) {
        roleTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            roleDropdown.classList.toggle('open');
        });
        
        roleDropdown.querySelectorAll('.ult-users-dd-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const val = this.getAttribute('data-value');
                const lbl = this.getAttribute('data-label');
                const desc = this.getAttribute('data-desc');
                const clr = this.getAttribute('data-color');
                const icn = this.getAttribute('data-icon');
                
                roleValue.value = val;
                
                // Update trigger
                const iconDiv = roleTrigger.querySelector('.role-icon');
                iconDiv.style.background = 'linear-gradient(135deg,' + clr + ',' + clr + 'cc)';
                iconDiv.innerHTML = '<i class="bi ' + icn + '"></i>';
                roleTrigger.querySelector('.role-name').textContent = lbl;
                roleTrigger.querySelector('.role-desc').textContent = desc;
                
                // Update preview
                if (previewRole) {
                    previewRole.style.background = clr;
                    previewRole.innerHTML = '<i class="bi ' + icn + '"></i> ' + lbl;
                }
                
                // Update selected state
                roleDropdown.querySelectorAll('.ult-users-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                
                roleDropdown.classList.remove('open');
            });
        });
        
        document.addEventListener('click', function() {
            roleDropdown.classList.remove('open');
        });
    }
    
    // ===== TOGGLE SWITCHES =====
    document.querySelectorAll('[data-toggle]').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            if (e.target.tagName === 'INPUT') return;
            this.classList.toggle('active');
            const hidden = this.querySelector('input[type="hidden"]');
            const checkbox = this.querySelector('input[type="checkbox"]');
            if (hidden) hidden.value = this.classList.contains('active') ? '1' : '0';
            if (checkbox) checkbox.checked = this.classList.contains('active');
        });
        
        // Initialize state
        const checkbox = toggle.querySelector('input[type="checkbox"]');
        if (checkbox && checkbox.checked) {
            toggle.classList.add('active');
            const hidden = toggle.querySelector('input[type="hidden"]');
            if (hidden) hidden.value = '1';
        }
    });
    
    // ===== LIVE PREVIEW =====
    const fullNameInput = document.getElementById('userFullName');
    const emailInput = document.getElementById('userEmail');
    const previewName = document.getElementById('previewName');
    const previewEmail = document.getElementById('previewEmail');
    const previewAvatar = document.getElementById('previewAvatar');
    
    if (fullNameInput && previewName) {
        fullNameInput.addEventListener('input', function() {
            previewName.textContent = this.value || 'Nama Pengguna';
            if (previewAvatar && !avatarInput.value) {
                previewAvatar.textContent = this.value ? this.value.charAt(0).toUpperCase() : 'U';
            }
        });
    }
    if (emailInput && previewEmail) {
        emailInput.addEventListener('input', function() {
            previewEmail.querySelector('span').textContent = this.value || 'email@unimof.ac.id';
        });
    }
    
    // ===== USERNAME AUTO-GENERATE =====
    const usernameInput = document.getElementById('userUsername');
    if (usernameInput && !<?= $isEdit ? 'true' : 'false' ?>) {
        fullNameInput.addEventListener('input', function() {
            if (!usernameInput.dataset.manual) {
                const name = this.value.toLowerCase().replace(/[^a-z0-9\s]/g, '').replace(/\s+/g, '_').substring(0, 30);
                usernameInput.value = name;
            }
        });
        usernameInput.addEventListener('input', function() {
            this.dataset.manual = '1';
        });
    }
    
    // ===== PASSWORD GENERATOR =====
    window.ultGeneratePassword = function() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%^&*';
        let pass = '';
        for (let i = 0; i < 12; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
        const passInput = document.getElementById('userPassword');
        passInput.value = pass;
        passInput.type = 'text';
        passInput.dispatchEvent(new Event('input'));
        if (typeof showToast !== 'undefined') showToast('Password berhasil di-generate!', 'success');
    };
    
    window.ultTogglePasswordVisibility = function() {
        const passInput = document.getElementById('userPassword');
        const btn = passInput.nextElementSibling;
        if (passInput.type === 'password') {
            passInput.type = 'text';
            btn.innerHTML = '<i class="bi bi-eye-slash-fill"></i> Sembunyikan';
        } else {
            passInput.type = 'password';
            btn.innerHTML = '<i class="bi bi-eye-fill"></i> Lihat';
        }
    };
    
    // ===== PASSWORD STRENGTH METER =====
    const newPass = document.getElementById('userPassword');
    const confirmPass = document.getElementById('userPasswordConfirm');
    const strengthBox = document.getElementById('ultPassStrength');
    const strengthFill = document.getElementById('ultPassStrengthFill');
    const strengthLabel = document.getElementById('ultPassStrengthLabel');
    const passMatch = document.getElementById('ultPassMatch');
    
    const checks = {
        length:  { el: document.getElementById('check-length'),  test: v => v.length >= 8 },
        upper:   { el: document.getElementById('check-upper'),   test: v => /[A-Z]/.test(v) },
        lower:   { el: document.getElementById('check-lower'),   test: v => /[a-z]/.test(v) },
        number:  { el: document.getElementById('check-number'),  test: v => /[0-9]/.test(v) },
        special: { el: document.getElementById('check-special'), test: v => /[^A-Za-z0-9]/.test(v) },
    };
    
    function updateStrength() {
        const val = newPass.value;
        if (!val) {
            strengthBox.style.display = 'none';
            return;
        }
        strengthBox.style.display = 'block';
        
        let passed = 0;
        for (const key in checks) {
            const ok = checks[key].test(val);
            checks[key].el.classList.toggle('pass', ok);
            checks[key].el.querySelector('i').className = ok ? 'bi bi-check-circle-fill' : 'bi bi-circle';
            if (ok) passed++;
        }
        
        const levels = [
            { max: 1, cls: 'weak',  label: 'Sangat Lemah', color: '#ef4444' },
            { max: 2, cls: 'fair',  label: 'Lemah',        color: '#f59e0b' },
            { max: 3, cls: 'good',  label: 'Cukup',        color: '#3b82f6' },
            { max: 4, cls: 'strong',label: 'Kuat',         color: '#10b981' },
            { max: 5, cls: 'strong',label: 'Sangat Kuat',  color: '#10b981' },
        ];
        const level = levels[passed - 1] || levels[0];
        strengthFill.className = 'ult-users-pass-strength-fill ' + level.cls;
        strengthLabel.textContent = 'Kekuatan: ' + level.label;
        strengthLabel.style.color = level.color;
        
        // Match check
        if (confirmPass && confirmPass.value) {
            const match = val === confirmPass.value;
            passMatch.style.display = 'flex';
            passMatch.innerHTML = match 
                ? '<i class="bi bi-check-circle-fill" style="color:var(--admin-success);"></i> Password cocok'
                : '<i class="bi bi-x-circle-fill" style="color:var(--admin-danger);"></i> Password tidak cocok';
            passMatch.className = 'ult-users-hint ' + (match ? 'success' : 'danger');
        } else if (passMatch) {
            passMatch.style.display = 'none';
        }
    }
    
    if (newPass) newPass.addEventListener('input', updateStrength);
    if (confirmPass) confirmPass.addEventListener('input', updateStrength);
    
    // ===== SUBMIT LOADING STATE =====
    form.addEventListener('submit', function() {
        const btn = document.getElementById('ultUsersSubmitBtn');
        const spinner = btn.querySelector('.ult-spinner');
        const icon = btn.querySelector('.bi-save-fill');
        btn.disabled = true;
        if (spinner) spinner.style.display = 'inline-block';
        if (icon) icon.style.display = 'none';
        btn.innerHTML = (spinner ? spinner.outerHTML : '') + ' Menyimpan...';
    });
})();
</script>