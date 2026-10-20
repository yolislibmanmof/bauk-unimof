<?php
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);
$val = static function (string $key, $default = '') use ($old, $data) {
    if (array_key_exists($key, $old) && $old[$key] !== '') return $old[$key];
    if (is_array($data) && isset($data[$key]) && $data[$key] !== '') return $data[$key];
    return $default;
};
$isEdit = ($mode === 'edit');
$pageTitle = $isEdit ? 'Edit Dokumen Regulasi' : 'Unggah Regulasi Baru';
?>

<style>
/* Reusing elegant dropdown + document specific */
.ult-reg-layout{display:grid;grid-template-columns:1fr;gap:1.5rem;max-width:800px;}
.ult-reg-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;}
.ult-reg-header{padding:1.25rem 1.5rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-reg-header i{font-size:1.25rem;color:var(--admin-purple);}
.ult-reg-header h3{font-size:1.1rem;font-weight:700;margin:0;}
.ult-reg-body{padding:1.5rem;}
.ult-reg-field{display:flex;flex-direction:column;gap:.375rem;margin-bottom:1.25rem;}
.ult-reg-label{font-size:.825rem;font-weight:600;color:var(--admin-text);}
.ult-reg-label .req{color:var(--admin-danger);}
.ult-reg-input{position:relative;}
.ult-reg-input .icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);font-size:1rem;pointer-events:none;}
.ult-reg-input input,.ult-reg-input select,.ult-reg-input textarea{width:100%;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);font-size:.9rem;background:var(--admin-bg);transition:all .2s;}
.ult-reg-input textarea{padding-left:1rem;resize:vertical;min-height:100px;}
.ult-reg-input input:focus,.ult-reg-input select:focus,.ult-reg-input textarea:focus{outline:none;border-color:var(--admin-purple);background:#fff;box-shadow:0 0 0 3px rgba(142,68,173,.08);}
.ult-reg-input input:focus ~ .icon{color:var(--admin-purple);}
.ult-reg-hint{font-size:.75rem;color:var(--admin-text-muted);}

/* Doc Drop */
.ult-reg-drop{border:2px dashed var(--admin-border);border-radius:var(--radius-lg);padding:2rem;text-align:center;background:var(--admin-bg);cursor:pointer;position:relative;transition:all .3s;}
.ult-reg-drop:hover{border-color:var(--admin-purple);background:rgba(142,68,173,.03);}
.ult-reg-drop input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;}
.ult-reg-drop-icon{width:60px;height:60px;border-radius:50%;background:var(--admin-surface);border:1px solid var(--admin-border);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.75rem;color:var(--admin-text-muted);}
.ult-reg-file-info{margin-top:1rem;padding:1rem;background:var(--admin-surface);border-radius:var(--radius-md);border:1px solid var(--admin-border);display:none;}
.ult-reg-file-info.active{display:flex;align-items:center;gap:1rem;}
.ult-reg-file-info i{font-size:2rem;color:var(--admin-danger);}
.ult-reg-file-info div{flex:1;text-align:left;}
.ult-reg-file-info strong{display:block;font-size:.9rem;color:var(--admin-text);}
.ult-reg-file-info small{font-size:.75rem;color:var(--admin-text-muted);}
</style>

<div class="page-header">
    <div><h1 class="page-title"><?= $pageTitle ?></h1><p class="page-subtitle">Kelola peraturan, keputusan, dan SOP universitas</p></div>
    <a href="<?= url('admin/regulasi') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<form method="POST" action="<?= url('admin/regulasi/simpan') ?>" enctype="multipart/form-data" id="ultRegForm">
    <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$data['id'] ?>"><?php endif; ?>
    
    <div class="ult-reg-layout">
        <div class="ult-reg-card">
            <div class="ult-reg-header"><i class="bi bi-file-earmark-text-fill"></i><h3>Detail Dokumen</h3></div>
            <div class="ult-reg-body">
                <div class="ult-reg-field">
                    <label class="ult-reg-label">Judul Regulasi <span class="req">*</span></label>
                    <div class="ult-reg-input">
                        <input type="text" name="title" value="<?= htmlspecialchars($val('title')) ?>" required maxlength="255" placeholder="Contoh: Peraturan Rektor No. X Tahun 2026">
                        <i class="bi bi-type icon"></i>
                    </div>
                </div>

                <div class="ult-reg-field">
                    <label class="ult-reg-label">Nomor Dokumen</label>
                    <div class="ult-reg-input">
                        <input type="text" name="document_number" value="<?= htmlspecialchars($val('document_number')) ?>" maxlength="100" placeholder="Contoh: 001/PR/UNIMOF/2026">
                        <i class="bi bi-hash icon"></i>
                    </div>
                </div>

                <!-- Elegant Dropdown: Kategori -->
                <div class="ult-reg-field">
                    <label class="ult-reg-label">Kategori Dokumen <span class="req">*</span></label>
                    <div class="ult-dropdown" data-dropdown>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($val('category', 'peraturan')) ?>" required>
                        <button type="button" class="ult-dropdown-trigger has-value">
                            <i class="bi bi-folder-fill" style="color:var(--admin-purple);"></i>
                            <span><?= ucfirst(str_replace('_', ' ', $val('category', 'peraturan'))) ?></span>
                            <i class="bi bi-chevron-down chevron"></i>
                        </button>
                        <div class="ult-dropdown-menu">
                            <?php foreach (['peraturan' => 'Peraturan', 'keputusan' => 'Keputusan', 'surat_edaran' => 'Surat Edaran', 'sop' => 'SOP', 'pedoman' => 'Pedoman'] as $k => $v): ?>
                            <div class="ult-dd-item <?= $val('category', 'peraturan') === $k ? 'selected' : '' ?>" data-value="<?= $k ?>" data-label="<?= ucfirst(str_replace('_', ' ', $k)) ?>">
                                <i class="bi bi-file-earmark-fill" style="color:var(--admin-purple);"></i><span><?= $v ?></span><i class="bi bi-check-lg"></i>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Elegant Dropdown: Status -->
                <div class="ult-reg-field">
                    <label class="ult-reg-label">Status Berlaku</label>
                    <div class="ult-dropdown" data-dropdown>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($val('status', 'active')) ?>">
                        <button type="button" class="ult-dropdown-trigger has-value">
                            <i class="bi bi-patch-check-fill" style="color:var(--admin-success);"></i>
                            <span><?= $val('status', 'active') === 'active' ? 'Aktif / Berlaku' : ($val('status', 'active') === 'expired' ? 'Kedaluwarsa' : 'Dicabut') ?></span>
                            <i class="bi bi-chevron-down chevron"></i>
                        </button>
                        <div class="ult-dropdown-menu">
                            <div class="ult-dd-item <?= $val('status', 'active') === 'active' ? 'selected' : '' ?>" data-value="active" data-label="Aktif / Berlaku">
                                <i class="bi bi-patch-check-fill" style="color:var(--admin-success);"></i><span>Aktif / Berlaku</span><i class="bi bi-check-lg"></i>
                            </div>
                            <div class="ult-dd-item <?= $val('status', 'active') === 'expired' ? 'selected' : '' ?>" data-value="expired" data-label="Kedaluwarsa">
                                <i class="bi bi-clock-history" style="color:var(--admin-warning);"></i><span>Kedaluwarsa</span><i class="bi bi-check-lg"></i>
                            </div>
                            <div class="ult-dd-item <?= $val('status', 'active') === 'revoked' ? 'selected' : '' ?>" data-value="revoked" data-label="Dicabut">
                                <i class="bi bi-x-circle-fill" style="color:var(--admin-danger);"></i><span>Dicabut</span><i class="bi bi-check-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ult-reg-field">
                    <label class="ult-reg-label">Tanggal Berlaku Efektif</label>
                    <div class="ult-reg-input">
                        <input type="date" name="effective_date" value="<?= htmlspecialchars($val('effective_date')) ?>">
                        <i class="bi bi-calendar-check icon"></i>
                    </div>
                </div>

                <div class="ult-reg-field">
                    <label class="ult-reg-label">Deskripsi / Ringkasan</label>
                    <div class="ult-reg-input">
                        <textarea name="description" rows="3" placeholder="Jelaskan singkat isi dokumen ini..."><?= htmlspecialchars($val('description')) ?></textarea>
                    </div>
                </div>

                <div class="ult-reg-field">
                    <label class="ult-reg-label">File Dokumen <span class="req">*</span></label>
                    <div class="ult-reg-drop" id="regDropZone">
                        <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx" id="regFileInput" <?= $isEdit ? '' : 'required' ?>>
                        <div class="ult-reg-drop-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                        <div style="font-size:.95rem;font-weight:600;color:var(--admin-text);">Klik untuk unggah atau seret file ke sini</div>
                        <div style="font-size:.8rem;color:var(--admin-text-muted);margin-top:.25rem;">PDF, DOC, DOCX, XLS, XLSX. Maksimal 10 MB.</div>
                    </div>
                    <div class="ult-reg-file-info <?= $isEdit && !empty($data['file_path']) ? 'active' : '' ?>" id="regFileInfo">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                        <div>
                            <strong id="regFileName"><?= $isEdit && !empty($data['file_path']) ? htmlspecialchars($data['file_path']) : 'Belum ada file' ?></strong>
                            <small id="regFileSize"><?= $isEdit && !empty($data['file_size']) ? round($data['file_size']/1024, 1) . ' KB' : '' ?></small>
                        </div>
                    </div>
                    <?php if ($isEdit && !empty($data['file_path'])): ?>
                        <span class="ult-reg-hint"><i class="bi bi-info-circle"></i> Unggah file baru untuk mengganti file yang ada.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:.75rem;">
            <a href="<?= url('admin/regulasi') ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" style="background:var(--admin-purple);border-color:var(--admin-purple);"><i class="bi bi-save-fill"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Unggah Regulasi' ?></button>
        </div>
    </div>
</form>

<script>
(function(){
    // Dropdown Logic
    document.querySelectorAll('.ult-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-dropdown-trigger');
        const menu = dd.querySelector('.ult-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
        });
        menu.querySelectorAll('.ult-dd-item[data-value]').forEach(function(item){
            item.addEventListener('click', function(e){
                e.preventDefault();
                const val = this.getAttribute('data-value');
                const lbl = this.getAttribute('data-label');
                dd.querySelector('input[type="hidden"]').value = val;
                const span = trigger.querySelector('span');
                if(span) span.textContent = lbl;
                menu.querySelectorAll('.ult-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                dd.classList.remove('open');
            });
        });
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-dropdown.open').forEach(d => d.classList.remove('open')));

    // File Info Display
    const fileInput = document.getElementById('regFileInput');
    const fileInfo = document.getElementById('regFileInfo');
    const fileName = document.getElementById('regFileName');
    const fileSize = document.getElementById('regFileSize');
    if(fileInput){
        fileInput.addEventListener('change', function(){
            if(this.files[0]){
                fileName.textContent = this.files[0].name;
                fileSize.textContent = (this.files[0].size / 1024 / 1024).toFixed(2) + ' MB';
                fileInfo.classList.add('active');
            }
        });
    }
})();
</script>