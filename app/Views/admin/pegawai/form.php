<?php
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);

$val = static function (string $key, $default = '') use ($old, $data) {
    if (array_key_exists($key, $old) && $old[$key] !== '') return $old[$key];
    if (is_array($data) && isset($data[$key]) && $data[$key] !== '') return $data[$key];
    return $default;
};

$isEdit       = ($mode === 'edit');
$currentPhoto = $isEdit && !empty($data['photo']) ? url('uploads/pegawai/' . $data['photo']) : null;
$pageTitle    = $isEdit ? 'Edit Data Pegawai' : 'Tambah Pegawai Baru';
$pageDesc     = $isEdit ? 'Memperbarui informasi untuk: <strong>' . htmlspecialchars($data['full_name']) . '</strong>' : 'Lengkapi seluruh data pegawai dengan akurat dan benar.';

// Map departemen untuk label dropdown
$deptMap = [];
foreach ($departments as $d) $deptMap[$d['id']] = $d['name'];
?>

<!-- ===== ULTIMATE FORM STYLES (Scoped) ===== -->
<style>
/* ===== LAYOUT GRID ===== */
.ult-pgn-form-layout{display:grid;grid-template-columns:260px 1fr;gap:2rem;align-items:start;}
@media (max-width:992px){.ult-pgn-form-layout{grid-template-columns:1fr;}}

/* ===== STICKY SIDEBAR NAV ===== */
.ult-pgn-form-nav{position:sticky;top:90px;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);padding:1rem;box-shadow:0 4px 15px rgba(0,0,0,.04);}
.ult-pgn-form-nav-title{font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--admin-text-muted);font-weight:700;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--admin-border);}
.ult-pgn-form-nav-link{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:var(--radius-md);color:var(--admin-text-secondary);text-decoration:none;font-size:.875rem;font-weight:600;transition:all .2s;margin-bottom:.25rem;}
.ult-pgn-form-nav-link:hover{background:var(--admin-bg);color:var(--admin-text);}
.ult-pgn-form-nav-link.active{background:rgba(26,82,118,.08);color:var(--admin-primary);border-left:3px solid var(--admin-primary);}
.ult-pgn-form-nav-link i{width:20px;text-align:center;font-size:1rem;}

/* ===== FORM SECTIONS ===== */
.ult-pgn-form-section{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);margin-bottom:1.5rem;overflow:hidden;scroll-margin-top:100px;}
.ult-pgn-form-section-header{padding:1.25rem 1.5rem;background:linear-gradient(to right, var(--admin-bg), transparent);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-pgn-form-section-icon{width:40px;height:40px;border-radius:var(--radius-md);background:var(--admin-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.ult-pgn-form-section-title{font-size:1.1rem;font-weight:700;color:var(--admin-text);margin:0;}
.ult-pgn-form-section-desc{font-size:.8rem;color:var(--admin-text-secondary);margin:.25rem 0 0;}
.ult-pgn-form-section-body{padding:1.5rem;}

/* ===== ENHANCED INPUTS ===== */
.ult-pgn-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem 1.5rem;}
.ult-pgn-form-grid.cols-3{grid-template-columns:repeat(3,1fr);}
.ult-pgn-form-grid.cols-1{grid-template-columns:1fr;}
@media (max-width:992px){.ult-pgn-form-grid,.ult-pgn-form-grid.cols-3{grid-template-columns:repeat(2,1fr);}}
@media (max-width:640px){.ult-pgn-form-grid,.ult-pgn-form-grid.cols-3{grid-template-columns:1fr;}}

.ult-pgn-field{display:flex;flex-direction:column;gap:.375rem;}
.ult-pgn-field.full{grid-column:1/-1;}
.ult-pgn-label{font-size:.825rem;font-weight:600;color:var(--admin-text);display:flex;align-items:center;gap:.375rem;}
.ult-pgn-label .req{color:var(--admin-danger);}

/* ===== CUSTOM ELEGANT DROPDOWN ===== */
.ult-pgn-dropdown{position:relative;}
.ult-pgn-dropdown-trigger{width:100%;display:flex;align-items:center;gap:.5rem;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);background:var(--admin-bg);color:var(--admin-text);font-size:.9rem;cursor:pointer;transition:all .2s;text-align:left;}
.ult-pgn-dropdown-trigger:hover{border-color:var(--admin-primary);background:#fff;}
.ult-pgn-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.04);}
.ult-pgn-dropdown-trigger i.chevron{margin-left:auto;font-size:.7rem;color:var(--admin-text-muted);transition:transform .2s;}
.ult-pgn-dropdown.open .ult-pgn-dropdown-trigger{border-color:var(--admin-primary);background:#fff;box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-pgn-dropdown.open .ult-pgn-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-pgn-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:280px;overflow-y:auto;}
.ult-pgn-dropdown.open .ult-pgn-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-pgn-dd-search{padding:.625rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);z-index:1;}
.ult-pgn-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;font-size:.825rem;background:var(--admin-bg);}
.ult-pgn-dd-search input:focus{outline:none;border-color:var(--admin-primary);}
.ult-pgn-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-pgn-dd-item:last-child{border-bottom:none;}
.ult-pgn-dd-item:hover{background:var(--admin-bg);}
.ult-pgn-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-pgn-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-pgn-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-pgn-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}

/* ===== STANDARD INPUTS ===== */
.ult-pgn-input-wrap{position:relative;}
.ult-pgn-input-wrap .input-icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);font-size:1rem;pointer-events:none;transition:color .2s;z-index:2;}
.ult-pgn-input-wrap input:not([type="hidden"]),.ult-pgn-input-wrap textarea{width:100%;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);font-size:.9rem;background:var(--admin-bg);color:var(--admin-text);transition:all .2s;font-family:inherit;}
.ult-pgn-input-wrap textarea{padding-left:1rem;resize:vertical;min-height:100px;line-height:1.6;}
.ult-pgn-input-wrap input:not([type="hidden"]):focus,.ult-pgn-input-wrap textarea:focus{outline:none;border-color:var(--admin-primary);background:#fff;box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-pgn-input-wrap input:not([type="hidden"]):focus ~ .input-icon,.ult-pgn-input-wrap textarea:focus ~ .input-icon{color:var(--admin-primary);}
.ult-pgn-hint{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;display:flex;align-items:center;gap:.25rem;}
.ult-pgn-hint i{font-size:.7rem;}

/* ===== FILE UPLOAD BOX ===== */
.ult-pgn-file-drop{border:2px dashed var(--admin-border);border-radius:var(--radius-lg);padding:2rem;text-align:center;background:var(--admin-bg);transition:all .3s;cursor:pointer;position:relative;}
.ult-pgn-file-drop:hover{border-color:var(--admin-primary);background:rgba(26,82,118,.03);}
.ult-pgn-file-drop.has-file{border-color:var(--admin-success);background:rgba(16,185,129,.05);}
.ult-pgn-file-drop input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;}
.ult-pgn-file-icon{width:64px;height:64px;border-radius:50%;background:var(--admin-surface);border:1px solid var(--admin-border);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.75rem;color:var(--admin-text-muted);transition:all .3s;}
.ult-pgn-file-drop.has-file .ult-pgn-file-icon{background:var(--admin-success);color:#fff;border-color:var(--admin-success);}
.ult-pgn-file-text h4{font-size:1rem;margin-bottom:.25rem;color:var(--admin-text);}
.ult-pgn-file-text p{font-size:.8rem;color:var(--admin-text-secondary);margin:0;}
.ult-pgn-file-preview{margin-top:1rem;display:none;}
.ult-pgn-file-preview.active{display:block;}
.ult-pgn-file-preview img{max-width:180px;max-height:180px;border-radius:var(--radius-md);border:2px solid var(--admin-border);object-fit:cover;box-shadow:0 4px 15px rgba(0,0,0,.1);}
.ult-pgn-file-preview .remove-file{margin-top:.5rem;font-size:.8rem;color:var(--admin-danger);cursor:pointer;display:inline-flex;align-items:center;gap:.25rem;}

/* ===== STICKY ACTION BAR ===== */
.ult-pgn-sticky-actions{position:sticky;bottom:0;background:var(--admin-surface);border-top:1px solid var(--admin-border);padding:1rem 1.5rem;display:flex;justify-content:flex-end;gap:.75rem;z-index:50;box-shadow:0 -4px 15px rgba(0,0,0,.05);margin:0 -1.5rem -1.5rem;border-radius:0 0 var(--radius-lg) var(--radius-lg);}
@media (max-width:992px){.ult-pgn-sticky-actions{position:fixed;bottom:0;left:0;right:0;margin:0;border-radius:0;z-index:1000;padding:1rem;box-shadow:0 -4px 20px rgba(0,0,0,.1);}}

/* ===== ANIMATIONS ===== */
@keyframes ult-pgn-fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.ult-pgn-form-section{animation:ult-pgn-fadeIn .4s ease forwards;}
@keyframes ult-pgn-spin{to{transform:rotate(360deg);}}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= $pageTitle ?></h1>
        <p class="page-subtitle"><?= $pageDesc ?></p>
    </div>
    <a href="<?= url('admin/pegawai') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Daftar</a>
</div>

<form method="POST" action="<?= url('admin/pegawai/simpan') ?>" enctype="multipart/form-data" id="ultPgnForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$data['id'] ?>"><?php endif; ?>
    
    <div class="ult-pgn-form-layout">
        
        <!-- ===== SIDEBAR NAVIGATION ===== -->
        <aside class="ult-pgn-form-nav d-none-mobile">
            <div class="ult-pgn-form-nav-title">Navigasi Form</div>
            <a href="#section-pribadi" class="ult-pgn-form-nav-link active" data-scroll><i class="bi bi-person-vcard-fill"></i> Data Pribadi</a>
            <a href="#section-kepegawaian" class="ult-pgn-form-nav-link" data-scroll><i class="bi bi-briefcase-fill"></i> Data Kepegawaian</a>
            <a href="#section-catatan" class="ult-pgn-form-nav-link" data-scroll><i class="bi bi-journal-text"></i> Catatan Tambahan</a>
            
            <div style="margin-top:2rem;padding-top:1rem;border-top:1px solid var(--admin-border);">
                <div class="ult-pgn-form-nav-title" style="margin-bottom:.5rem;">Progress</div>
                <div style="background:var(--admin-bg);border-radius:50px;height:6px;overflow:hidden;">
                    <div id="ultFormProgress" style="width:0%;height:100%;background:var(--admin-primary);transition:width .3s;"></div>
                </div>
                <div style="font-size:.75rem;color:var(--admin-text-muted);margin-top:.375rem;text-align:right;" id="ultFormProgressText">0% Terisi</div>
            </div>
        </aside>
        
        <!-- ===== MAIN FORM CONTENT ===== -->
        <div style="padding-bottom:80px;">
            
            <!-- SECTION 1: DATA PRIBADI -->
            <div class="ult-pgn-form-section" id="section-pribadi">
                <div class="ult-pgn-form-section-header">
                    <div class="ult-pgn-form-section-icon"><i class="bi bi-person-vcard-fill"></i></div>
                    <div>
                        <h3 class="ult-pgn-form-section-title">Bagian A — Data Pribadi</h3>
                        <p class="ult-pgn-form-section-desc">Informasi identitas dasar sesuai KTP/Ijazah.</p>
                    </div>
                </div>
                <div class="ult-pgn-form-section-body">
                    <div class="ult-pgn-form-grid">
                        <div class="ult-pgn-field full">
                            <label class="ult-pgn-label">Nama Lengkap <span class="req">*</span></label>
                            <div class="ult-pgn-input-wrap">
                                <input type="text" name="full_name" value="<?= htmlspecialchars($val('full_name')) ?>" required maxlength="100" placeholder="Contoh: Budi Santoso, S.Kom.">
                                <i class="bi bi-person input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Jenis Kelamin <span class="req">*</span></label>
                            <div class="ult-pgn-dropdown" data-dropdown>
                                <input type="hidden" name="gender" value="<?= htmlspecialchars($val('gender')) ?>" required>
                                <button type="button" class="ult-pgn-dropdown-trigger <?= $val('gender') !== '' ? 'has-value' : '' ?>">
                                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                                        <i class="bi bi-gender-ambiguous" style="color:var(--admin-primary);"></i>
                                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $val('gender') === 'L' ? 'Laki-laki' : ($val('gender') === 'P' ? 'Perempuan' : '— Pilih Jenis Kelamin —') ?></span>
                                    </span>
                                    <i class="bi bi-chevron-down chevron"></i>
                                </button>
                                <div class="ult-pgn-dropdown-menu">
                                    <div class="ult-pgn-dd-item <?= $val('gender') === '' ? 'selected' : '' ?>" data-value="" data-label="— Pilih Jenis Kelamin —">
                                        <i class="bi bi-gender-ambiguous" style="color:var(--admin-text-muted);"></i><span>— Pilih Jenis Kelamin —</span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <div class="ult-pgn-dd-divider"></div>
                                    <div class="ult-pgn-dd-item <?= $val('gender') === 'L' ? 'selected' : '' ?>" data-value="L" data-label="Laki-laki">
                                        <i class="bi bi-gender-male" style="color:#3b82f6;"></i><span>Laki-laki</span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <div class="ult-pgn-dd-item <?= $val('gender') === 'P' ? 'selected' : '' ?>" data-value="P" data-label="Perempuan">
                                        <i class="bi bi-gender-female" style="color:#ec4899;"></i><span>Perempuan</span><i class="bi bi-check-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Tempat Lahir</label>
                            <div class="ult-pgn-input-wrap">
                                <input type="text" name="birth_place" value="<?= htmlspecialchars($val('birth_place')) ?>" maxlength="50" placeholder="Contoh: Maumere">
                                <i class="bi bi-geo-alt input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Tanggal Lahir</label>
                            <div class="ult-pgn-input-wrap">
                                <input type="date" name="birth_date" value="<?= htmlspecialchars($val('birth_date')) ?>">
                                <i class="bi bi-calendar-event input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Agama</label>
                            <div class="ult-pgn-dropdown" data-dropdown>
                                <input type="hidden" name="religion" value="<?= htmlspecialchars($val('religion')) ?>">
                                <button type="button" class="ult-pgn-dropdown-trigger <?= $val('religion') !== '' ? 'has-value' : '' ?>">
                                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                                        <i class="bi bi-book" style="color:var(--admin-primary);"></i>
                                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $val('religion') !== '' ? $val('religion') : '— Pilih Agama —' ?></span>
                                    </span>
                                    <i class="bi bi-chevron-down chevron"></i>
                                </button>
                                <div class="ult-pgn-dropdown-menu">
                                    <div class="ult-pgn-dd-item <?= $val('religion') === '' ? 'selected' : '' ?>" data-value="" data-label="— Pilih Agama —">
                                        <i class="bi bi-book" style="color:var(--admin-text-muted);"></i><span>— Pilih Agama —</span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <div class="ult-pgn-dd-divider"></div>
                                    <?php foreach (['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $r): ?>
                                    <div class="ult-pgn-dd-item <?= $val('religion') === $r ? 'selected' : '' ?>" data-value="<?= $r ?>" data-label="<?= $r ?>">
                                        <i class="bi bi-star-fill" style="color:var(--admin-accent,#e67e22);"></i><span><?= $r ?></span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Status Pernikahan</label>
                            <div class="ult-pgn-dropdown" data-dropdown>
                                <input type="hidden" name="marital_status" value="<?= htmlspecialchars($val('marital_status')) ?>">
                                <button type="button" class="ult-pgn-dropdown-trigger <?= $val('marital_status') !== '' ? 'has-value' : '' ?>">
                                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                                        <i class="bi bi-heart" style="color:var(--admin-primary);"></i>
                                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $val('marital_status') !== '' ? $val('marital_status') : '— Pilih Status —' ?></span>
                                    </span>
                                    <i class="bi bi-chevron-down chevron"></i>
                                </button>
                                <div class="ult-pgn-dropdown-menu">
                                    <div class="ult-pgn-dd-item <?= $val('marital_status') === '' ? 'selected' : '' ?>" data-value="" data-label="— Pilih Status —">
                                        <i class="bi bi-heart" style="color:var(--admin-text-muted);"></i><span>— Pilih Status —</span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <div class="ult-pgn-dd-divider"></div>
                                    <?php foreach (['Belum Menikah','Menikah','Cerai Hidup','Cerai Mati'] as $m): ?>
                                    <div class="ult-pgn-dd-item <?= $val('marital_status') === $m ? 'selected' : '' ?>" data-value="<?= $m ?>" data-label="<?= $m ?>">
                                        <i class="bi bi-people-fill" style="color:var(--admin-primary);"></i><span><?= $m ?></span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">No. Telepon / WhatsApp</label>
                            <div class="ult-pgn-input-wrap">
                                <input type="tel" name="phone" value="<?= htmlspecialchars($val('phone')) ?>" maxlength="20" placeholder="08xx-xxxx-xxxx">
                                <i class="bi bi-telephone input-icon"></i>
                            </div>
                            <span class="ult-pgn-hint"><i class="bi bi-info-circle"></i> Format: 08123456789</span>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Alamat Email</label>
                            <div class="ult-pgn-input-wrap">
                                <input type="email" name="email" value="<?= htmlspecialchars($val('email')) ?>" maxlength="100" placeholder="nama@unimof.ac.id">
                                <i class="bi bi-envelope input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field full">
                            <label class="ult-pgn-label">Alamat Lengkap</label>
                            <div class="ult-pgn-input-wrap">
                                <textarea name="address" rows="3" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kode Pos"><?= htmlspecialchars($val('address')) ?></textarea>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field full">
                            <label class="ult-pgn-label">Foto Pegawai</label>
                            <div class="ult-pgn-file-drop <?= $currentPhoto ? 'has-file' : '' ?>" id="photoDropZone">
                                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" id="photoInput">
                                <div class="ult-pgn-file-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                <div class="ult-pgn-file-text">
                                    <h4>Klik atau seret foto ke sini</h4>
                                    <p>Format JPG / PNG / WEBP. Maksimal 2 MB.</p>
                                </div>
                                <div class="ult-pgn-file-preview <?= $currentPhoto ? 'active' : '' ?>" id="photoPreview">
                                    <?php if ($currentPhoto): ?>
                                        <img src="<?= $currentPhoto ?>" alt="Preview Foto">
                                        <div class="remove-file" onclick="removePhoto()"><i class="bi bi-trash3"></i> Hapus Foto</div>
                                    <?php else: ?>
                                        <img src="" alt="Preview Foto" id="previewImg">
                                        <div class="remove-file" onclick="removePhoto()" style="display:none;" id="removeBtn"><i class="bi bi-trash3"></i> Hapus Foto</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="ult-pgn-hint"><i class="bi bi-shield-check"></i> Foto akan dienkripsi dan disimpan aman di server.</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 2: DATA KEPEGAWAIAN -->
            <div class="ult-pgn-form-section" id="section-kepegawaian">
                <div class="ult-pgn-form-section-header">
                    <div class="ult-pgn-form-section-icon" style="background:var(--admin-accent,#e67e22);"><i class="bi bi-briefcase-fill"></i></div>
                    <div>
                        <h3 class="ult-pgn-form-section-title">Bagian B — Data Kepegawaian</h3>
                        <p class="ult-pgn-form-section-desc">Informasi jabatan, unit kerja, dan status kepegawaian.</p>
                    </div>
                </div>
                <div class="ult-pgn-form-section-body">
                    <div class="ult-pgn-form-grid cols-3">
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">NIP <span class="req">*</span></label>
                            <div class="ult-pgn-input-wrap">
                                <input type="text" name="nip" value="<?= htmlspecialchars($val('nip')) ?>" required maxlength="30" placeholder="Nomor Induk Pegawai">
                                <i class="bi bi-hash input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">NIK (KTP)</label>
                            <div class="ult-pgn-input-wrap">
                                <input type="text" name="nik" value="<?= htmlspecialchars($val('nik')) ?>" maxlength="20" placeholder="16 digit NIK">
                                <i class="bi bi-card-text input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">NUPTK</label>
                            <div class="ult-pgn-input-wrap">
                                <input type="text" name="nuptk" value="<?= htmlspecialchars($val('nuptk')) ?>" maxlength="30" placeholder="Khusus tenaga pengajar">
                                <i class="bi bi-mortarboard input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Departemen / Unit Kerja</label>
                            <div class="ult-pgn-dropdown" data-dropdown>
                                <input type="hidden" name="department_id" value="<?= (int)$val('department_id', 0) ?>">
                                <button type="button" class="ult-pgn-dropdown-trigger <?= (int)$val('department_id', 0) > 0 ? 'has-value' : '' ?>">
                                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                                        <i class="bi bi-building" style="color:var(--admin-primary);"></i>
                                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            <?= (int)$val('department_id', 0) > 0 ? htmlspecialchars($deptMap[$val('department_id', 0)] ?? 'Pilih Departemen') : '— Pilih Departemen —' ?>
                                        </span>
                                    </span>
                                    <i class="bi bi-chevron-down chevron"></i>
                                </button>
                                <div class="ult-pgn-dropdown-menu">
                                    <div class="ult-pgn-dd-search"><input type="text" placeholder="Cari departemen..." data-search></div>
                                    <div class="ult-pgn-dd-item <?= (int)$val('department_id', 0) === 0 ? 'selected' : '' ?>" data-value="0" data-label="— Pilih Departemen —">
                                        <i class="bi bi-grid-3x3-gap" style="color:var(--admin-text-muted);"></i><span>— Pilih Departemen —</span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <div class="ult-pgn-dd-divider"></div>
                                    <?php foreach ($departments as $d): ?>
                                    <div class="ult-pgn-dd-item <?= (int)$val('department_id', 0) === (int)$d['id'] ? 'selected' : '' ?>" data-value="<?= $d['id'] ?>" data-label="<?= htmlspecialchars($d['name']) ?>">
                                        <i class="bi bi-building-fill" style="color:var(--admin-primary);"></i><span><?= htmlspecialchars($d['name']) ?></span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Jabatan</label>
                            <div class="ult-pgn-input-wrap">
                                <input type="text" name="position" value="<?= htmlspecialchars($val('position')) ?>" maxlength="100" placeholder="Contoh: Staf Administrasi">
                                <i class="bi bi-briefcase input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Status Kepegawaian</label>
                            <div class="ult-pgn-dropdown" data-dropdown>
                                <input type="hidden" name="employment_status" value="<?= htmlspecialchars($val('employment_status', 'Tetap')) ?>">
                                <button type="button" class="ult-pgn-dropdown-trigger has-value">
                                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                                        <i class="bi bi-patch-check" style="color:var(--admin-primary);"></i>
                                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $val('employment_status', 'Tetap') ?: '— Pilih Status —' ?></span>
                                    </span>
                                    <i class="bi bi-chevron-down chevron"></i>
                                </button>
                                <div class="ult-pgn-dropdown-menu">
                                    <?php foreach (['Tetap','Kontrak','Honorer','Magang'] as $e): ?>
                                    <div class="ult-pgn-dd-item <?= $val('employment_status', 'Tetap') === $e ? 'selected' : '' ?>" data-value="<?= $e ?>" data-label="<?= $e ?>">
                                        <i class="bi bi-check-circle-fill" style="color:var(--admin-success);"></i><span><?= $e ?></span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Pendidikan Terakhir</label>
                            <div class="ult-pgn-dropdown" data-dropdown>
                                <input type="hidden" name="education_level" value="<?= htmlspecialchars($val('education_level')) ?>">
                                <button type="button" class="ult-pgn-dropdown-trigger <?= $val('education_level') !== '' ? 'has-value' : '' ?>">
                                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                                        <i class="bi bi-book-half" style="color:var(--admin-primary);"></i>
                                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $val('education_level') !== '' ? $val('education_level') : '— Pilih Pendidikan —' ?></span>
                                    </span>
                                    <i class="bi bi-chevron-down chevron"></i>
                                </button>
                                <div class="ult-pgn-dropdown-menu">
                                    <div class="ult-pgn-dd-item <?= $val('education_level') === '' ? 'selected' : '' ?>" data-value="" data-label="— Pilih Pendidikan —">
                                        <i class="bi bi-book-half" style="color:var(--admin-text-muted);"></i><span>— Pilih Pendidikan —</span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <div class="ult-pgn-dd-divider"></div>
                                    <?php foreach (['SD','SMP','SMA/SMK','D3','S1','S2','S3'] as $ed): ?>
                                    <div class="ult-pgn-dd-item <?= $val('education_level') === $ed ? 'selected' : '' ?>" data-value="<?= $ed ?>" data-label="<?= $ed ?>">
                                        <i class="bi bi-mortarboard-fill" style="color:var(--admin-info);"></i><span><?= $ed ?></span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Tanggal Masuk</label>
                            <div class="ult-pgn-input-wrap">
                                <input type="date" name="join_date" value="<?= htmlspecialchars($val('join_date')) ?>">
                                <i class="bi bi-calendar-check input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Tanggal Keluar</label>
                            <div class="ult-pgn-input-wrap">
                                <input type="date" name="end_date" value="<?= htmlspecialchars($val('end_date')) ?>">
                                <i class="bi bi-calendar-x input-icon"></i>
                            </div>
                            <span class="ult-pgn-hint"><i class="bi bi-info-circle"></i> Kosongkan jika masih aktif</span>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Gaji Pokok (Rp)</label>
                            <div class="ult-pgn-input-wrap">
                                <input type="text" name="salary" id="salaryInput" value="<?= htmlspecialchars($val('salary') ? number_format((float)$val('salary'), 0, ',', '.') : '') ?>" placeholder="0">
                                <i class="bi bi-cash-stack input-icon"></i>
                            </div>
                        </div>
                        
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Status Aktif</label>
                            <div class="ult-pgn-dropdown" data-dropdown>
                                <input type="hidden" name="status" value="<?= htmlspecialchars($val('status', 'active')) ?>">
                                <button type="button" class="ult-pgn-dropdown-trigger has-value">
                                    <span style="display:flex;align-items:center;gap:.5rem;overflow:hidden;">
                                        <i class="bi bi-toggle-on" style="color:var(--admin-primary);"></i>
                                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            <?php 
                                            $st = $val('status', 'active');
                                            $stLabels = ['active' => 'Aktif', 'inactive' => 'Nonaktif', 'retired' => 'Pensiun', 'resigned' => 'Mengundurkan Diri'];
                                            echo $stLabels[$st] ?? 'Aktif';
                                            ?>
                                        </span>
                                    </span>
                                    <i class="bi bi-chevron-down chevron"></i>
                                </button>
                                <div class="ult-pgn-dropdown-menu">
                                    <?php foreach (['active' => 'Aktif', 'inactive' => 'Nonaktif', 'retired' => 'Pensiun', 'resigned' => 'Mengundurkan Diri'] as $k => $v): ?>
                                    <div class="ult-pgn-dd-item <?= $val('status', 'active') === $k ? 'selected' : '' ?>" data-value="<?= $k ?>" data-label="<?= $v ?>">
                                        <i class="bi bi-<?= $k === 'active' ? 'check-circle-fill text-success' : ($k === 'retired' ? 'hourglass-split text-info' : 'x-circle-fill text-danger') ?>" style="color:var(--admin-<?= $k === 'active' ? 'success' : ($k === 'retired' ? 'info' : 'danger') ?>);"></i>
                                        <span><?= $v ?></span><i class="bi bi-check-lg"></i>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 3: CATATAN -->
            <div class="ult-pgn-form-section" id="section-catatan">
                <div class="ult-pgn-form-section-header">
                    <div class="ult-pgn-form-section-icon" style="background:var(--admin-purple,#8e44ad);"><i class="bi bi-journal-text"></i></div>
                    <div>
                        <h3 class="ult-pgn-form-section-title">Bagian C — Catatan Tambahan</h3>
                        <p class="ult-pgn-form-section-desc">Informasi internal atau catatan khusus (opsional).</p>
                    </div>
                </div>
                <div class="ult-pgn-form-section-body">
                    <div class="ult-pgn-form-grid cols-1">
                        <div class="ult-pgn-field">
                            <label class="ult-pgn-label">Catatan Internal</label>
                            <div class="ult-pgn-input-wrap">
                                <textarea name="notes" rows="4" data-autoresize placeholder="Contoh: Memiliki riwayat penyakit tertentu, atau catatan kinerja khusus..."><?= htmlspecialchars($val('notes')) ?></textarea>
                            </div>
                            <span class="ult-pgn-hint"><i class="bi bi-shield-lock"></i> Catatan ini hanya dapat dilihat oleh Administrator.</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- STICKY ACTION BAR -->
            <div class="ult-pgn-sticky-actions">
                <a href="<?= url('admin/pegawai') ?>" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Batal</a>
                <button type="reset" class="btn btn-outline" style="border-color:var(--admin-border);color:var(--admin-text);"><i class="bi bi-arrow-counterclockwise"></i> Reset Form</button>
                <button type="submit" class="btn btn-primary" id="ultSubmitBtn" style="min-width:180px;">
                    <span class="ult-spinner" style="display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:ult-pgn-spin .7s linear infinite;margin-right:.5rem;"></span>
                    <i class="bi bi-save-fill"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Pegawai' ?>
                </button>
            </div>
            
        </div>
    </div>
</form>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function() {
    'use strict';
    
    // ===== CUSTOM DROPDOWN LOGIC =====
    document.querySelectorAll('.ult-pgn-dropdown[data-dropdown]').forEach(function(dd) {
        const trigger = dd.querySelector('.ult-pgn-dropdown-trigger');
        const menu = dd.querySelector('.ult-pgn-dropdown-menu');
        if (!trigger || !menu) return;
        
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.ult-pgn-dropdown.open').forEach(function(other) { if (other !== dd) other.classList.remove('open'); });
            dd.classList.toggle('open');
            if (dd.classList.contains('open')) {
                const searchInput = menu.querySelector('[data-search]');
                if (searchInput) setTimeout(function() { searchInput.focus(); }, 50);
            }
        });
        
        menu.querySelectorAll('.ult-pgn-dd-item[data-value]').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const value = this.getAttribute('data-value');
                const label = this.getAttribute('data-label') || this.querySelector('span').textContent.trim();
                const hiddenInput = dd.querySelector('input[type="hidden"]');
                if (hiddenInput) hiddenInput.value = value;
                
                if (trigger) {
                    const triggerSpan = trigger.querySelector('span');
                    if (triggerSpan) {
                        const icon = trigger.querySelector('i:not(.chevron)');
                        if (icon) {
                            triggerSpan.innerHTML = '';
                            triggerSpan.appendChild(icon.cloneNode(true));
                            const textSpan = document.createElement('span');
                            textSpan.style.cssText = 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
                            textSpan.textContent = label;
                            triggerSpan.appendChild(textSpan);
                        } else { triggerSpan.textContent = label; }
                    }
                }
                
                menu.querySelectorAll('.ult-pgn-dd-item').forEach(function(i) { i.classList.remove('selected'); });
                this.classList.add('selected');
                
                if (value !== '' && value !== '0') trigger.classList.add('has-value');
                else trigger.classList.remove('has-value');
                
                dd.classList.remove('open');
                if (hiddenInput) hiddenInput.dispatchEvent(new Event('change'));
            });
        });
        
        const searchInput = menu.querySelector('[data-search]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                menu.querySelectorAll('.ult-pgn-dd-item').forEach(function(item) {
                    if (item.hasAttribute('data-value')) item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    });
    
    document.addEventListener('click', function() {
        document.querySelectorAll('.ult-pgn-dropdown.open').forEach(function(dd) { dd.classList.remove('open'); });
    });
    
    // ===== SMOOTH SCROLL NAV =====
    document.querySelectorAll('.ult-pgn-form-nav-link[data-scroll]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) window.scrollTo({ top: target.offsetTop - 100, behavior: 'smooth' });
        });
    });
    
    // ===== SCROLL SPY =====
    const sections = document.querySelectorAll('.ult-pgn-form-section');
    const navLinks = document.querySelectorAll('.ult-pgn-form-nav-link');
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
    const form = document.getElementById('ultPgnForm');
    const progressBar = document.getElementById('ultFormProgress');
    const progressText = document.getElementById('ultFormProgressText');
    function updateProgress() {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required], .ult-pgn-dropdown input[required]');
        let filled = 0;
        inputs.forEach(function(input) {
            if (input.value.trim() !== '') filled++;
        });
        const pct = inputs.length > 0 ? Math.round((filled / inputs.length) * 100) : 0;
        if (progressBar) progressBar.style.width = pct + '%';
        if (progressText) progressText.textContent = pct + '% Terisi';
    }
    form.addEventListener('input', updateProgress);
    form.addEventListener('change', updateProgress);
    updateProgress();
    
    // ===== FILE UPLOAD PREVIEW =====
    const photoInput = document.getElementById('photoInput');
    const photoDropZone = document.getElementById('photoDropZone');
    const photoPreview = document.getElementById('photoPreview');
    const previewImg = document.getElementById('previewImg');
    const removeBtn = document.getElementById('removeBtn');
    
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    if (typeof showToast !== 'undefined') showToast('Ukuran file maksimal 2 MB!', 'error');
                    this.value = ''; return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    photoPreview.classList.add('active');
                    if(removeBtn) removeBtn.style.display = 'inline-flex';
                    photoDropZone.classList.add('has-file');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    window.removePhoto = function() {
        if (photoInput) photoInput.value = '';
        if (previewImg) previewImg.src = '';
        if (photoPreview) photoPreview.classList.remove('active');
        if (removeBtn) removeBtn.style.display = 'none';
        if (photoDropZone) photoDropZone.classList.remove('has-file');
    };
    
    // ===== CURRENCY FORMATTING =====
    const salaryInput = document.getElementById('salaryInput');
    if (salaryInput) {
        salaryInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            this.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
        });
        salaryInput.addEventListener('focus', function() {
            this.value = this.value.replace(/\./g, '');
        });
    }
    
    // ===== SUBMIT LOADING STATE =====
    form.addEventListener('submit', function() {
        const btn = document.getElementById('ultSubmitBtn');
        const spinner = btn.querySelector('.ult-spinner');
        const icon = btn.querySelector('.bi-save-fill');
        btn.disabled = true;
        if (spinner) spinner.style.display = 'inline-block';
        if (icon) icon.style.display = 'none';
        btn.innerHTML = (spinner ? spinner.outerHTML : '') + ' Menyimpan...';
    });
    
    // ===== DRAG & DROP VISUAL FEEDBACK =====
    if (photoDropZone) {
        ['dragenter', 'dragover'].forEach(function(eventName) {
            photoDropZone.addEventListener(eventName, function(e) {
                e.preventDefault(); e.stopPropagation();
                photoDropZone.style.borderColor = 'var(--admin-primary)';
                photoDropZone.style.background = 'rgba(26,82,118,.08)';
            }, false);
        });
        ['dragleave', 'drop'].forEach(function(eventName) {
            photoDropZone.addEventListener(eventName, function(e) {
                e.preventDefault(); e.stopPropagation();
                photoDropZone.style.borderColor = '';
                photoDropZone.style.background = '';
            }, false);
        });
    }
})();
</script>