<?php
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);
$val = static function (string $key, $default = '') use ($old, $data) {
    if (array_key_exists($key, $old) && $old[$key] !== '') return $old[$key];
    if (is_array($data) && isset($data[$key]) && $data[$key] !== '') return $data[$key];
    return $default;
};
$isEdit = ($mode === 'edit');
$currentImg = $isEdit && !empty($data['featured_image']) ? url('uploads/berita/' . $data['featured_image']) : null;
$pageTitle = $isEdit ? 'Edit Berita' : 'Tulis Berita Baru';
?>

<style>
/* Reusing elegant dropdown styles + content specific */
.ult-berita-layout{display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;}
@media(max-width:992px){.ult-berita-layout{grid-template-columns:1fr;}}
.ult-berita-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1.5rem;}
.ult-berita-header{padding:1.25rem 1.5rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-berita-header i{font-size:1.25rem;color:var(--admin-info);}
.ult-berita-header h3{font-size:1.1rem;font-weight:700;margin:0;}
.ult-berita-body{padding:1.5rem;}
.ult-berita-field{display:flex;flex-direction:column;gap:.375rem;margin-bottom:1.25rem;}
.ult-berita-label{font-size:.825rem;font-weight:600;color:var(--admin-text);}
.ult-berita-input{position:relative;}
.ult-berita-input input,.ult-berita-input textarea{width:100%;padding:.75rem 1rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);font-size:.95rem;background:var(--admin-bg);transition:all .2s;}
.ult-berita-input textarea{resize:vertical;min-height:120px;line-height:1.6;}
.ult-berita-input input:focus,.ult-berita-input textarea:focus{outline:none;border-color:var(--admin-info);background:#fff;box-shadow:0 0 0 3px rgba(59,130,246,.08);}
.ult-berita-hint{font-size:.75rem;color:var(--admin-text-muted);}
.ult-berita-drop{border:2px dashed var(--admin-border);border-radius:var(--radius-lg);padding:2rem;text-align:center;background:var(--admin-bg);cursor:pointer;position:relative;transition:all .3s;}
.ult-berita-drop:hover{border-color:var(--admin-info);background:rgba(59,130,246,.03);}
.ult-berita-drop input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;}
.ult-berita-preview{margin-top:1rem;display:none;}
.ult-berita-preview.active{display:block;}
.ult-berita-preview img{max-width:100%;border-radius:var(--radius-md);border:1px solid var(--admin-border);}
</style>

<div class="page-header">
    <div><h1 class="page-title"><?= $pageTitle ?></h1><p class="page-subtitle">Publikasikan informasi terkini untuk civitas akademika</p></div>
    <a href="<?= url('admin/berita') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<form method="POST" action="<?= url('admin/berita/simpan') ?>" enctype="multipart/form-data" id="ultBeritaForm">
    <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$data['id'] ?>"><?php endif; ?>
    
    <div class="ult-berita-layout">
        <!-- Main Content -->
        <div>
            <div class="ult-berita-card">
                <div class="ult-berita-header"><i class="bi bi-pencil-square"></i><h3>Konten Berita</h3></div>
                <div class="ult-berita-body">
                    <div class="ult-berita-field">
                        <label class="ult-berita-label">Judul Berita <span style="color:var(--admin-danger);">*</span></label>
                        <div class="ult-berita-input">
                            <input type="text" name="title" value="<?= htmlspecialchars($val('title')) ?>" required maxlength="255" placeholder="Masukkan judul yang menarik...">
                        </div>
                    </div>
                    <div class="ult-berita-field">
                        <label class="ult-berita-label">Ringkasan (Excerpt)</label>
                        <div class="ult-berita-input">
                            <textarea name="excerpt" rows="3" placeholder="Ringkasan singkat untuk tampilan preview (opsional, akan otomatis diambil dari konten jika kosong)"><?= htmlspecialchars($val('excerpt')) ?></textarea>
                        </div>
                    </div>
                    <div class="ult-berita-field">
                        <label class="ult-berita-label">Isi Konten <span style="color:var(--admin-danger);">*</span></label>
                        <div class="ult-berita-input">
                            <textarea name="content" rows="12" required placeholder="Tulis konten berita di sini... (Mendukung HTML dasar)"><?= htmlspecialchars($val('content')) ?></textarea>
                        </div>
                        <span class="ult-berita-hint"><i class="bi bi-info-circle"></i> Gunakan tag HTML seperti &lt;p&gt;, &lt;strong&gt;, &lt;ul&gt; untuk formatting.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Settings -->
        <div>
            <div class="ult-berita-card">
                <div class="ult-berita-header"><i class="bi bi-gear-fill"></i><h3>Pengaturan Publikasi</h3></div>
                <div class="ult-berita-body">
                    <!-- Elegant Dropdown: Kategori -->
                    <div class="ult-berita-field">
                        <label class="ult-berita-label">Kategori</label>
                        <div class="ult-dropdown" data-dropdown>
                            <input type="hidden" name="category" value="<?= htmlspecialchars($val('category', 'berita')) ?>">
                            <button type="button" class="ult-dropdown-trigger has-value">
                                <i class="bi bi-tag" style="color:var(--admin-info);"></i>
                                <span><?= ucfirst($val('category', 'berita')) ?></span>
                                <i class="bi bi-chevron-down chevron"></i>
                            </button>
                            <div class="ult-dropdown-menu">
                                <?php foreach (['berita' => 'Berita', 'pengumuman' => 'Pengumuman', 'kegiatan' => 'Kegiatan', 'pengadaan' => 'Pengadaan', 'regulasi' => 'Regulasi'] as $k => $v): ?>
                                <div class="ult-dd-item <?= $val('category', 'berita') === $k ? 'selected' : '' ?>" data-value="<?= $k ?>" data-label="<?= ucfirst($k) ?>">
                                    <i class="bi bi-bookmark-fill" style="color:var(--admin-info);"></i><span><?= $v ?></span><i class="bi bi-check-lg"></i>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Elegant Dropdown: Status -->
                    <div class="ult-berita-field">
                        <label class="ult-berita-label">Status Publikasi</label>
                        <div class="ult-dropdown" data-dropdown>
                            <input type="hidden" name="is_published" value="<?= $val('is_published', '0') ?>">
                            <button type="button" class="ult-dropdown-trigger has-value">
                                <i class="bi bi-<?= $val('is_published', '0') == '1' ? 'eye-fill text-success' : 'eye-slash-fill text-warning' ?>"></i>
                                <span><?= $val('is_published', '0') == '1' ? 'Dipublikasikan' : 'Draft' ?></span>
                                <i class="bi bi-chevron-down chevron"></i>
                            </button>
                            <div class="ult-dropdown-menu">
                                <div class="ult-dd-item <?= $val('is_published', '0') == '0' ? 'selected' : '' ?>" data-value="0" data-label="Draft">
                                    <i class="bi bi-eye-slash-fill" style="color:var(--admin-warning);"></i><span>Draft</span><i class="bi bi-check-lg"></i>
                                </div>
                                <div class="ult-dd-item <?= $val('is_published', '0') == '1' ? 'selected' : '' ?>" data-value="1" data-label="Dipublikasikan">
                                    <i class="bi bi-eye-fill" style="color:var(--admin-success);"></i><span>Dipublikasikan</span><i class="bi bi-check-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ult-berita-field">
                        <label class="ult-berita-label">Tags (Opsional)</label>
                        <div class="ult-berita-input">
                            <input type="text" name="tags" value="<?= htmlspecialchars($val('tags')) ?>" placeholder="pisahkan dengan koma">
                        </div>
                    </div>
                </div>
            </div>

            <div class="ult-berita-card">
                <div class="ult-berita-header"><i class="bi bi-image-fill"></i><h3>Gambar Utama</h3></div>
                <div class="ult-berita-body">
                    <div class="ult-berita-drop" id="beritaDropZone">
                        <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp" id="beritaImgInput">
                        <i class="bi bi-cloud-arrow-up" style="font-size:2rem;color:var(--admin-text-muted);margin-bottom:.5rem;"></i>
                        <div style="font-size:.9rem;font-weight:600;">Unggah Gambar</div>
                        <div style="font-size:.75rem;color:var(--admin-text-muted);">JPG / PNG. Maksimal 3 MB.</div>
                        <div class="ult-berita-preview <?= $currentImg ? 'active' : '' ?>" id="beritaPreview">
                            <?php if ($currentImg): ?>
                                <img src="<?= $currentImg ?>" alt="Preview">
                            <?php else: ?>
                                <img src="" alt="Preview" id="beritaPreviewImg">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="bi bi-save-fill"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Publikasikan Berita' ?></button>
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

    // Image Preview
    const imgInput = document.getElementById('beritaImgInput');
    const dropZone = document.getElementById('beritaDropZone');
    const preview = document.getElementById('beritaPreview');
    const previewImg = document.getElementById('beritaPreviewImg');
    if(imgInput){
        imgInput.addEventListener('change', function(){
            if(this.files[0]){
                const reader = new FileReader();
                reader.onload = function(e){
                    if(previewImg) previewImg.src = e.target.result;
                    preview.classList.add('active');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
})();
</script>