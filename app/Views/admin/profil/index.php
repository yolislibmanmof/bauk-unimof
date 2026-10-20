<?php
$u = \App\Helpers\Auth::user();
$db = \App\Helpers\Database::getInstance();

// ===== FETCH USER DATA LENGKAP =====
try {
    $userData = $db->fetch("SELECT * FROM users WHERE id = ?", [(int)$u['id']]);
    if (!$userData) $userData = $u;
    
    // Activity stats
    $loginCount = $db->count('activity_logs', "user_id = ? AND action = 'login'", [(int)$u['id']]);
    $lastActivity = $db->fetch("SELECT action, created_at FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 1", [(int)$u['id']]);
    $totalUpdates = $db->count('activity_logs', "user_id = ? AND action = 'update'", [(int)$u['id']]);
} catch (\Throwable $e) {
    $userData = $u;
    $loginCount = 0;
    $lastActivity = null;
    $totalUpdates = 0;
}

$avatarUrl = !empty($userData['avatar']) ? url('uploads/profil/' . $userData['avatar']) : null;
$roleColors = [
    'super_admin'         => ['#ef4444', 'Super Administrator'],
    'admin_bauk'          => ['#3b82f6', 'Admin BAUK'],
    'admin_kepegawaian'   => ['#10b981', 'Admin Kepegawaian'],
    'admin_keuangan'      => ['#f59e0b', 'Admin Keuangan'],
    'admin_aset'          => ['#8b5cf6', 'Admin Aset'],
    'staff'               => ['#06b6d4', 'Staff'],
    'viewer'              => ['#64748b', 'Viewer'],
];
[$roleColor, $roleLabel] = $roleColors[$u['role']] ?? ['#64748b', ucfirst(str_replace('_', ' ', $u['role']))];

// Password strength helper
$passChecks = [
    'length'   => ['min 8 karakter', 8],
    'upper'    => ['huruf kapital', 0],
    'lower'    => ['huruf kecil', 0],
    'number'   => ['angka', 0],
    'special'  => ['karakter spesial', 0],
];
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== PROFILE HEADER ===== */
.ult-profile-header{background:linear-gradient(135deg, var(--admin-primary-dark, #0d2f4a) 0%, var(--admin-primary, #1a5276) 50%, var(--admin-primary-light, #2980b9) 100%);color:#fff;border-radius:var(--radius-xl);padding:2.5rem;margin-bottom:1.5rem;position:relative;overflow:hidden;}
.ult-profile-header::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/svg%3E");z-index:1;}
.ult-profile-header::after{content:'';position:absolute;top:-50%;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(230,126,34,.15) 0%,transparent 70%);border-radius:50%;z-index:1;}
.ult-profile-header-content{position:relative;z-index:2;display:grid;grid-template-columns:auto 1fr auto;gap:2rem;align-items:center;}
@media (max-width:992px){.ult-profile-header-content{grid-template-columns:1fr;text-align:center;}}

.ult-profile-avatar-wrap{position:relative;}
.ult-profile-avatar{width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,.3);box-shadow:0 10px 30px rgba(0,0,0,.2);}
.ult-profile-avatar-initial{width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg,#f39c12,#e67e22);color:#fff;display:flex;align-items:center;justify-content:center;font-size:3rem;font-weight:700;border:4px solid rgba(255,255,255,.3);box-shadow:0 10px 30px rgba(0,0,0,.2);}
.ult-profile-avatar-badge{position:absolute;bottom:5px;right:5px;width:32px;height:32px;border-radius:50%;background:#10b981;border:3px solid var(--admin-primary);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.85rem;}

.ult-profile-info h1{font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:.5rem;color:#fff;}
.ult-profile-info-username{font-size:.95rem;color:rgba(255,255,255,.8);margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
@media (max-width:992px){.ult-profile-info-username{justify-content:center;}}
.ult-profile-info-username i{font-size:.8rem;}
.ult-profile-role-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:50px;font-size:.85rem;font-weight:700;background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.25);}
.ult-profile-role-dot{width:8px;height:8px;border-radius:50%;background:<?= $roleColor ?>;}

.ult-profile-stats{display:flex;flex-direction:column;gap:.75rem;}
@media (max-width:992px){.ult-profile-stats{flex-direction:row;justify-content:center;flex-wrap:wrap;}}
.ult-profile-stat{background:rgba(255,255,255,.1);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.2);border-radius:var(--radius-md);padding:.875rem 1.25rem;min-width:140px;}
.ult-profile-stat-value{font-size:1.5rem;font-weight:700;color:#fff;line-height:1;}
.ult-profile-stat-label{font-size:.7rem;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.5px;margin-top:.25rem;}

/* ===== LAYOUT ===== */
.ult-profile-layout{display:grid;grid-template-columns:1fr 340px;gap:1.5rem;}
@media (max-width:992px){.ult-profile-layout{grid-template-columns:1fr;}}

/* ===== SECTIONS ===== */
.ult-profile-section{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1.5rem;}
.ult-profile-section-header{padding:1.25rem 1.5rem;background:linear-gradient(to right, var(--admin-bg), transparent);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-profile-section-icon{width:40px;height:40px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff;flex-shrink:0;}
.ult-profile-section-title{font-size:1.1rem;font-weight:700;color:var(--admin-text);margin:0;}
.ult-profile-section-desc{font-size:.8rem;color:var(--admin-text-secondary);margin:.25rem 0 0;}
.ult-profile-section-body{padding:1.5rem;}

/* ===== FORM ===== */
.ult-profile-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem;}
@media (max-width:768px){.ult-profile-grid{grid-template-columns:1fr;}}
.ult-profile-field{display:flex;flex-direction:column;gap:.375rem;}
.ult-profile-field.full{grid-column:1/-1;}
.ult-profile-label{font-size:.825rem;font-weight:600;color:var(--admin-text);display:flex;align-items:center;gap:.375rem;}
.ult-profile-label .req{color:var(--admin-danger);}
.ult-profile-input-wrap{position:relative;}
.ult-profile-input-wrap .input-icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);font-size:1rem;pointer-events:none;transition:color .2s;z-index:2;}
.ult-profile-input-wrap input,.ult-profile-input-wrap select{width:100%;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);font-size:.9rem;background:var(--admin-bg);color:var(--admin-text);transition:all .2s;font-family:inherit;}
.ult-profile-input-wrap input:read-only{background:var(--admin-bg);color:var(--admin-text-secondary);cursor:not-allowed;}
.ult-profile-input-wrap input:focus,.ult-profile-input-wrap select:focus{outline:none;border-color:var(--admin-primary);background:#fff;box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-profile-input-wrap input:focus ~ .input-icon{color:var(--admin-primary);}
.ult-profile-hint{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;display:flex;align-items:center;gap:.25rem;}
.ult-profile-hint i{font-size:.7rem;}

/* ===== AVATAR UPLOAD ===== */
.ult-profile-avatar-upload{display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;}
.ult-profile-avatar-preview{position:relative;}
.ult-profile-avatar-preview img,.ult-profile-avatar-preview .ult-profile-avatar-initial{width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid var(--admin-border);box-shadow:0 4px 15px rgba(0,0,0,.1);}
.ult-profile-avatar-change{position:absolute;bottom:0;right:0;width:32px;height:32px;border-radius:50%;background:var(--admin-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.85rem;cursor:pointer;border:2px solid #fff;transition:all .2s;}
.ult-profile-avatar-change:hover{background:var(--admin-primary-dark);transform:scale(1.1);}
.ult-profile-avatar-change input{position:absolute;inset:0;opacity:0;cursor:pointer;}
.ult-profile-avatar-info h4{font-size:.95rem;margin-bottom:.25rem;color:var(--admin-text);}
.ult-profile-avatar-info p{font-size:.8rem;color:var(--admin-text-secondary);margin:0;}

/* ===== PASSWORD STRENGTH ===== */
.ult-pass-strength{margin-top:.75rem;}
.ult-pass-strength-bar{height:6px;background:var(--admin-bg);border-radius:50px;overflow:hidden;margin-bottom:.5rem;}
.ult-pass-strength-fill{height:100%;border-radius:50px;transition:all .3s;width:0%;}
.ult-pass-strength-fill.weak{background:#ef4444;width:25%;}
.ult-pass-strength-fill.fair{background:#f59e0b;width:50%;}
.ult-pass-strength-fill.good{background:#3b82f6;width:75%;}
.ult-pass-strength-fill.strong{background:#10b981;width:100%;}
.ult-pass-strength-label{font-size:.75rem;font-weight:600;margin-bottom:.5rem;}
.ult-pass-checks{display:grid;grid-template-columns:repeat(2,1fr);gap:.375rem;}
.ult-pass-check{display:flex;align-items:center;gap:.375rem;font-size:.75rem;color:var(--admin-text-muted);transition:color .2s;}
.ult-pass-check.pass{color:var(--admin-success);}
.ult-pass-check i{font-size:.7rem;}

/* ===== SIDEBAR CARDS ===== */
.ult-profile-side-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1rem;}
.ult-profile-side-header{padding:1rem 1.25rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.625rem;}
.ult-profile-side-header i{font-size:1.1rem;color:var(--admin-primary);}
.ult-profile-side-header h4{font-size:.95rem;font-weight:700;margin:0;}
.ult-profile-side-body{padding:1.25rem;}
.ult-profile-side-row{display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;font-size:.85rem;border-bottom:1px dashed var(--admin-border);}
.ult-profile-side-row:last-child{border-bottom:none;}
.ult-profile-side-row span:first-child{color:var(--admin-text-muted);font-weight:600;display:flex;align-items:center;gap:.375rem;}
.ult-profile-side-row span:first-child i{font-size:.8rem;color:var(--admin-primary);}
.ult-profile-side-row span:last-child{color:var(--admin-text);font-weight:500;text-align:right;}

/* ===== QUICK ACTIONS ===== */
.ult-profile-quick-actions{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;}
.ult-profile-quick-btn{padding:.75rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);display:flex;flex-direction:column;align-items:center;gap:.375rem;text-decoration:none!important;color:var(--admin-text)!important;font-size:.75rem;font-weight:600;transition:all .2s;text-align:center;}
.ult-profile-quick-btn:hover{background:var(--admin-primary);color:#fff!important;border-color:var(--admin-primary);transform:translateY(-2px);}
.ult-profile-quick-btn i{font-size:1.25rem;color:var(--admin-primary);}
.ult-profile-quick-btn:hover i{color:#fff;}

/* ===== STICKY ACTION BAR ===== */
.ult-profile-sticky-actions{position:sticky;bottom:0;background:var(--admin-surface);border-top:1px solid var(--admin-border);padding:1rem 1.5rem;display:flex;justify-content:flex-end;gap:.75rem;z-index:50;box-shadow:0 -4px 15px rgba(0,0,0,.05);margin:0 -1.5rem -1.5rem;border-radius:0 0 var(--radius-lg) var(--radius-lg);}
@media (max-width:992px){.ult-profile-sticky-actions{position:fixed;bottom:0;left:0;right:0;margin:0;border-radius:0;z-index:1000;padding:1rem;box-shadow:0 -4px 20px rgba(0,0,0,.1);}}

/* ===== ANIMATIONS ===== */
@keyframes ult-profile-fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.ult-profile-section{animation:ult-profile-fadeIn .4s ease forwards;}
@keyframes ult-profile-spin{to{transform:rotate(360deg);}}

/* ===== PRINT ===== */
@media print {
    .sidebar, .admin-header, .ult-profile-sticky-actions, .btn { display: none !important; }
    .main-wrapper { margin-left: 0 !important; }
    .ult-profile-header { background: #fff !important; color: #000 !important; border: 1px solid #ccc; }
    .ult-profile-header * { color: #000 !important; }
}
</style>

<!-- ===== PROFILE HEADER ===== -->
<div class="ult-profile-header">
    <div class="ult-profile-header-content">
        <div class="ult-profile-avatar-wrap">
            <?php if ($avatarUrl): ?>
                <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($u['full_name']) ?>" class="ult-profile-avatar">
            <?php else: ?>
                <div class="ult-profile-avatar-initial"><?= strtoupper(substr($u['full_name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div class="ult-profile-avatar-badge"><i class="bi bi-check-lg"></i></div>
        </div>
        <div class="ult-profile-info">
            <h1><?= htmlspecialchars($u['full_name']) ?></h1>
            <div class="ult-profile-info-username">
                <span><i class="bi bi-at"></i> <?= htmlspecialchars($u['username']) ?></span>
                <span>•</span>
                <span><i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($u['email']) ?></span>
            </div>
            <div class="ult-profile-role-badge">
                <span class="ult-profile-role-dot"></span>
                <?= $roleLabel ?>
            </div>
        </div>
        <div class="ult-profile-stats">
            <div class="ult-profile-stat">
                <div class="ult-profile-stat-value"><?= number_format($loginCount) ?></div>
                <div class="ult-profile-stat-label">Total Login</div>
            </div>
            <div class="ult-profile-stat">
                <div class="ult-profile-stat-value"><?= number_format($totalUpdates) ?></div>
                <div class="ult-profile-stat-label">Aktivitas</div>
            </div>
        </div>
    </div>
</div>

<div class="ult-profile-layout">
    <!-- ===== MAIN FORM ===== -->
    <div>
        <form method="POST" action="<?= url('admin/profil/update') ?>" enctype="multipart/form-data" id="ultProfileForm">
            <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
            
            <!-- Section 1: Info Pribadi -->
            <div class="ult-profile-section">
                <div class="ult-profile-section-header">
                    <div class="ult-profile-section-icon" style="background:linear-gradient(135deg,var(--admin-primary),var(--admin-primary-light));"><i class="bi bi-person-fill"></i></div>
                    <div>
                        <h3 class="ult-profile-section-title">Informasi Pribadi</h3>
                        <p class="ult-profile-section-desc">Perbarui data profil Anda</p>
                    </div>
                </div>
                <div class="ult-profile-section-body">
                    <div class="ult-profile-grid">
                        <div class="ult-profile-field full">
                            <label class="ult-profile-label">Foto Profil</label>
                            <div class="ult-profile-avatar-upload">
                                <div class="ult-profile-avatar-preview">
                                    <?php if ($avatarUrl): ?>
                                        <img src="<?= $avatarUrl ?>" alt="Avatar" id="ultAvatarPreview">
                                    <?php else: ?>
                                        <div class="ult-profile-avatar-initial" id="ultAvatarPreview"><?= strtoupper(substr($u['full_name'], 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <label class="ult-profile-avatar-change" title="Ganti Foto">
                                        <i class="bi bi-camera-fill"></i>
                                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" id="ultAvatarInput">
                                    </label>
                                </div>
                                <div class="ult-profile-avatar-info">
                                    <h4>Upload Foto Baru</h4>
                                    <p>Format JPG / PNG / WEBP. Maksimal 2 MB.</p>
                                    <?php if ($avatarUrl): ?>
                                        <p style="margin-top:.25rem;"><a href="#" onclick="ultRemoveAvatar();return false;" style="color:var(--admin-danger);font-size:.8rem;"><i class="bi bi-trash3"></i> Hapus foto saat ini</a></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ult-profile-field">
                            <label class="ult-profile-label">Nama Lengkap <span class="req">*</span></label>
                            <div class="ult-profile-input-wrap">
                                <input type="text" name="full_name" value="<?= htmlspecialchars($userData['full_name'] ?? $u['full_name']) ?>" required maxlength="100">
                                <i class="bi bi-person input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-profile-field">
                            <label class="ult-profile-label">Username</label>
                            <div class="ult-profile-input-wrap">
                                <input type="text" value="<?= htmlspecialchars($u['username']) ?>" readonly>
                                <i class="bi bi-at input-icon"></i>
                            </div>
                            <span class="ult-profile-hint"><i class="bi bi-lock-fill"></i> Username tidak dapat diubah</span>
                        </div>
                        
                        <div class="ult-profile-field">
                            <label class="ult-profile-label">Email <span class="req">*</span></label>
                            <div class="ult-profile-input-wrap">
                                <input type="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? $u['email']) ?>" required maxlength="100">
                                <i class="bi bi-envelope input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-profile-field">
                            <label class="ult-profile-label">No. Telepon</label>
                            <div class="ult-profile-input-wrap">
                                <input type="tel" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>" maxlength="20" placeholder="08xx-xxxx-xxxx">
                                <i class="bi bi-telephone input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-profile-field">
                            <label class="ult-profile-label">Role</label>
                            <div class="ult-profile-input-wrap">
                                <input type="text" value="<?= $roleLabel ?>" readonly style="color:<?= $roleColor ?>;font-weight:700;">
                                <i class="bi bi-shield-fill-check input-icon" style="color:<?= $roleColor ?>;"></i>
                            </div>
                            <span class="ult-profile-hint"><i class="bi bi-info-circle"></i> Hubungi Super Admin untuk perubahan role</span>
                        </div>
                        
                        <div class="ult-profile-field">
                            <label class="ult-profile-label">Status Akun</label>
                            <div class="ult-profile-input-wrap">
                                <input type="text" value="<?= ucfirst($userData['status'] ?? 'active') ?>" readonly style="color:<?= ($userData['status'] ?? 'active') === 'active' ? 'var(--admin-success)' : 'var(--admin-warning)' ?>;font-weight:700;">
                                <i class="bi bi-toggle-on input-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section 2: Keamanan -->
            <div class="ult-profile-section">
                <div class="ult-profile-section-header">
                    <div class="ult-profile-section-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="bi bi-shield-lock-fill"></i></div>
                    <div>
                        <h3 class="ult-profile-section-title">Keamanan Akun</h3>
                        <p class="ult-profile-section-desc">Ganti password untuk menjaga keamanan akun Anda</p>
                    </div>
                </div>
                <div class="ult-profile-section-body">
                    <div class="ult-profile-grid">
                        <div class="ult-profile-field full">
                            <label class="ult-profile-label">Password Saat Ini</label>
                            <div class="ult-profile-input-wrap">
                                <input type="password" name="current_password" id="ultCurrentPass" placeholder="Masukkan password saat ini untuk verifikasi">
                                <i class="bi bi-lock-fill input-icon"></i>
                            </div>
                            <span class="ult-profile-hint"><i class="bi bi-info-circle"></i> Wajib diisi jika ingin mengubah password</span>
                        </div>
                        
                        <div class="ult-profile-field full">
                            <label class="ult-profile-label">Password Baru</label>
                            <div class="ult-profile-input-wrap">
                                <input type="password" name="new_password" id="ultNewPass" placeholder="Minimal 8 karakter dengan kombinasi kuat">
                                <i class="bi bi-key-fill input-icon"></i>
                            </div>
                            
                            <!-- Password Strength Meter -->
                            <div class="ult-pass-strength" id="ultPassStrength" style="display:none;">
                                <div class="ult-pass-strength-bar">
                                    <div class="ult-pass-strength-fill" id="ultPassStrengthFill"></div>
                                </div>
                                <div class="ult-pass-strength-label" id="ultPassStrengthLabel">Kekuatan password</div>
                                <div class="ult-pass-checks">
                                    <div class="ult-pass-check" id="check-length"><i class="bi bi-circle"></i> Min. 8 karakter</div>
                                    <div class="ult-pass-check" id="check-upper"><i class="bi bi-circle"></i> Huruf kapital</div>
                                    <div class="ult-pass-check" id="check-lower"><i class="bi bi-circle"></i> Huruf kecil</div>
                                    <div class="ult-pass-check" id="check-number"><i class="bi bi-circle"></i> Angka</div>
                                    <div class="ult-pass-check" id="check-special"><i class="bi bi-circle"></i> Karakter spesial</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ult-profile-field full">
                            <label class="ult-profile-label">Konfirmasi Password Baru</label>
                            <div class="ult-profile-input-wrap">
                                <input type="password" id="ultConfirmPass" placeholder="Ketik ulang password baru">
                                <i class="bi bi-key-fill input-icon"></i>
                            </div>
                            <span class="ult-profile-hint" id="ultPassMatch" style="display:none;"><i class="bi bi-check-circle-fill"></i> Password cocok</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sticky Action Bar -->
            <div class="ult-profile-sticky-actions">
                <button type="reset" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                <button type="submit" class="btn btn-primary" id="ultProfileSubmitBtn" style="min-width:180px;">
                    <span class="ult-spinner" style="display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:ult-profile-spin .7s linear infinite;margin-right:.5rem;"></span>
                    <i class="bi bi-save-fill"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
    
    <!-- ===== SIDEBAR ===== -->
    <aside>
        <!-- Info Akun -->
        <div class="ult-profile-side-card">
            <div class="ult-profile-side-header">
                <i class="bi bi-info-circle-fill"></i>
                <h4>Info Akun</h4>
            </div>
            <div class="ult-profile-side-body">
                <div class="ult-profile-side-row">
                    <span><i class="bi bi-person-fill"></i> Nama</span>
                    <span><?= htmlspecialchars($u['full_name']) ?></span>
                </div>
                <div class="ult-profile-side-row">
                    <span><i class="bi bi-at"></i> Username</span>
                    <span style="font-family:monospace;font-size:.8rem;"><?= htmlspecialchars($u['username']) ?></span>
                </div>
                <div class="ult-profile-side-row">
                    <span><i class="bi bi-envelope-fill"></i> Email</span>
                    <span style="font-size:.8rem;"><?= htmlspecialchars($u['email']) ?></span>
                </div>
                <div class="ult-profile-side-row">
                    <span><i class="bi bi-shield-fill-check"></i> Role</span>
                    <span style="color:<?= $roleColor ?>;font-weight:700;font-size:.8rem;"><?= $roleLabel ?></span>
                </div>
                <div class="ult-profile-side-row">
                    <span><i class="bi bi-toggle-on"></i> Status</span>
                    <span style="color:<?= ($u['status'] ?? 'active') === 'active' ? 'var(--admin-success)' : 'var(--admin-warning)' ?>;font-weight:700;"><?= ucfirst($u['status'] ?? 'active') ?></span>
                </div>
            </div>
        </div>
        
        <!-- Aktivitas -->
        <div class="ult-profile-side-card">
            <div class="ult-profile-side-header">
                <i class="bi bi-clock-history"></i>
                <h4>Aktivitas Terakhir</h4>
            </div>
            <div class="ult-profile-side-body">
                <div class="ult-profile-side-row">
                    <span><i class="bi bi-box-arrow-in-right"></i> Login Terakhir</span>
                    <span style="font-size:.8rem;"><?= isset($u['login_time']) ? date('d M H:i', $u['login_time']) : '-' ?> WITA</span>
                </div>
                <div class="ult-profile-side-row">
                    <span><i class="bi bi-activity"></i> Aksi Terakhir</span>
                    <span style="font-size:.8rem;"><?= $lastActivity ? ucfirst($lastActivity['action']) : '-' ?></span>
                </div>
                <div class="ult-profile-side-row">
                    <span><i class="bi bi-clock"></i> Waktu Aksi</span>
                    <span style="font-size:.8rem;"><?= $lastActivity ? date('d M H:i', strtotime($lastActivity['created_at'])) : '-' ?></span>
                </div>
                <div class="ult-profile-side-row">
                    <span><i class="bi bi-bar-chart-fill"></i> Total Login</span>
                    <span style="font-weight:700;color:var(--admin-primary);"><?= number_format($loginCount) ?>x</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="ult-profile-side-card">
            <div class="ult-profile-side-header">
                <i class="bi bi-lightning-fill"></i>
                <h4>Aksi Cepat</h4>
            </div>
            <div class="ult-profile-side-body">
                <div class="ult-profile-quick-actions">
                    <a href="<?= url('admin') ?>" class="ult-profile-quick-btn">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?= url('admin/pengaturan') ?>" class="ult-profile-quick-btn">
                        <i class="bi bi-gear-fill"></i>
                        <span>Pengaturan</span>
                    </a>
                    <a href="<?= url('admin/aktivitas') ?>" class="ult-profile-quick-btn">
                        <i class="bi bi-clock-history"></i>
                        <span>Log Aktivitas</span>
                    </a>
                    <a href="<?= url('admin/logout') ?>" class="ult-profile-quick-btn" style="color:var(--admin-danger)!important;" onclick="return confirm('Yakin ingin logout?');">
                        <i class="bi bi-box-arrow-right" style="color:var(--admin-danger);"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Security Tips -->
        <div class="ult-profile-side-card" style="background:linear-gradient(135deg,#fef3c7,#fde68a);border-color:#f59e0b;">
            <div class="ult-profile-side-body" style="padding:1.25rem;">
                <div style="display:flex;align-items:flex-start;gap:.75rem;">
                    <i class="bi bi-shield-fill-exclamation" style="font-size:1.5rem;color:#f59e0b;flex-shrink:0;"></i>
                    <div>
                        <h4 style="font-size:.95rem;margin:0 0 .375rem;color:#92400e;">Tips Keamanan</h4>
                        <ul style="margin:0;padding-left:1.25rem;font-size:.8rem;color:#78350f;line-height:1.7;">
                            <li>Ganti password secara berkala</li>
                            <li>Gunakan password yang kuat & unik</li>
                            <li>Jangan bagikan kredensial login</li>
                            <li>Logout setelah selesai menggunakan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function() {
    'use strict';
    
    // ===== AVATAR PREVIEW =====
    const avatarInput = document.getElementById('ultAvatarInput');
    const avatarPreview = document.getElementById('ultAvatarPreview');
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
                        img.className = 'ult-profile-avatar';
                        img.id = 'ultAvatarPreview';
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
    
    // ===== PASSWORD STRENGTH METER =====
    const newPass = document.getElementById('ultNewPass');
    const confirmPass = document.getElementById('ultConfirmPass');
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
        strengthFill.className = 'ult-pass-strength-fill ' + level.cls;
        strengthLabel.textContent = 'Kekuatan: ' + level.label;
        strengthLabel.style.color = level.color;
        
        // Match check
        if (confirmPass.value) {
            const match = val === confirmPass.value;
            passMatch.style.display = 'flex';
            passMatch.innerHTML = match 
                ? '<i class="bi bi-check-circle-fill" style="color:var(--admin-success);"></i> Password cocok'
                : '<i class="bi bi-x-circle-fill" style="color:var(--admin-danger);"></i> Password tidak cocok';
        } else {
            passMatch.style.display = 'none';
        }
    }
    
    if (newPass) newPass.addEventListener('input', updateStrength);
    if (confirmPass) confirmPass.addEventListener('input', updateStrength);
    
    // ===== SUBMIT LOADING STATE =====
    const form = document.getElementById('ultProfileForm');
    if (form) {
        form.addEventListener('submit', function() {
            const btn = document.getElementById('ultProfileSubmitBtn');
            const spinner = btn.querySelector('.ult-spinner');
            const icon = btn.querySelector('.bi-save-fill');
            btn.disabled = true;
            if (spinner) spinner.style.display = 'inline-block';
            if (icon) icon.style.display = 'none';
            btn.innerHTML = (spinner ? spinner.outerHTML : '') + ' Menyimpan...';
        });
    }
})();
</script>