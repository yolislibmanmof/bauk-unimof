<?php
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);
$val = static function (string $key, $default = '') use ($old, $data) {
    if (array_key_exists($key, $old) && $old[$key] !== '') return $old[$key];
    if (is_array($data) && isset($data[$key]) && $data[$key] !== '') return $data[$key];
    return $default;
};
$isEdit = ($mode === 'edit');
$currentPhoto = $isEdit && !empty($data['photo']) ? url('uploads/aset/' . $data['photo']) : null;
$pageTitle = $isEdit ? 'Edit Data Aset' : 'Tambah Aset Baru';
?>

<style>
/* Reusing elegant dropdown styles from above, plus specific asset styles */
.ult-aset-layout{display:grid;grid-template-columns:1fr;gap:1.5rem;}
.ult-aset-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;}
.ult-aset-header{padding:1.25rem 1.5rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-aset-header i{font-size:1.25rem;color:var(--admin-success);}
.ult-aset-header h3{font-size:1.1rem;font-weight:700;margin:0;}
.ult-aset-body{padding:1.5rem;}
.ult-aset-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;}
@media(max-width:992px){.ult-aset-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:640px){.ult-aset-grid{grid-template-columns:1fr;}}
.ult-aset-field{display:flex;flex-direction:column;gap:.375rem;}
.ult-aset-field.full{grid-column:1/-1;}
.ult-aset-label{font-size:.825rem;font-weight:600;color:var(--admin-text);}
.ult-aset-label .req{color:var(--admin-danger);}
.ult-aset-input{position:relative;}
.ult-aset-input .icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);font-size:1rem;pointer-events:none;}
.ult-aset-input input,.ult-aset-input select,.ult-aset-input textarea{width:100%;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);font-size:.9rem;background:var(--admin-bg);transition:all .2s;}
.ult-aset-input textarea{padding-left:1rem;resize:vertical;min-height:80px;}
.ult-aset-input input:focus,.ult-aset-input select:focus,.ult-aset-input textarea:focus{outline:none;border-color:var(--admin-success);background:#fff;box-shadow:0 0 0 3px rgba(39,174,96,.08);}
.ult-aset-input input:focus ~ .icon{color:var(--admin-success);}
.ult-aset-hint{font-size:.75rem;color:var(--admin-text-muted);margin-top:.25rem;}

/* File Drop */
.ult-aset-drop{border:2px dashed var(--admin-border);border-radius:var(--radius-lg);padding:1.5rem;text-align:center;background:var(--admin-bg);transition:all .3s;cursor:pointer;position:relative;}
.ult-aset-drop:hover{border-color:var(--admin-success);background:rgba(39,174,96,.03);}
.ult-aset-drop input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;}
.ult-aset-drop-icon{width:50px;height:50px;border-radius:50%;background:var(--admin-surface);border:1px solid var(--admin-border);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;font-size:1.5rem;color:var(--admin-text-muted);}
.ult-aset-preview{margin-top:.75rem;display:none;}
.ult-aset-preview.active{display:block;}
.ult-aset-preview img{max-width:150px;max-height:150px;border-radius:var(--radius-md);border:2px solid var(--admin-border);object-fit:cover;}
</style>

<div class="page-header">
    <div><h1 class="page-title"><?= $pageTitle ?></h1><p class="page-subtitle">Kelola inventaris dan sarana prasarana kampus</p></div>
    <a href="<?= url('admin/aset') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<form method="POST" action="<?= url('admin/aset/simpan') ?>" enctype="multipart/form-data" id="ultAsetForm">
    <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$data['id'] ?>"><?php endif; ?>
    
    <div class="ult-aset-layout">
        <div class="ult-aset-card">
            <div class="ult-aset-header"><i class="bi bi-box-seam-fill"></i><h3>Informasi Aset</h3></div>
            <div class="ult-aset-body">
                <div class="ult-aset-grid">
                    <div class="ult-aset-field">
                        <label class="ult-aset-label">Kode Aset <span class="req">*</span></label>
                        <div class="ult-aset-input">
                            <input type="text" name="asset_code" value="<?= htmlspecialchars($val('asset_code')) ?>" required maxlength="50" placeholder="Contoh: AST-2026-001" style="text-transform:uppercase;">
                            <i class="bi bi-hash icon"></i>
                        </div>
                    </div>
                    <div class="ult-aset-field full">
                        <label class="ult-aset-label">Nama Aset <span class="req">*</span></label>
                        <div class="ult-aset-input">
                            <input type="text" name="name" value="<?= htmlspecialchars($val('name')) ?>" required maxlength="200" placeholder="Contoh: Laptop Asus Vivobook 14">
                            <i class="bi bi-tag icon"></i>
                        </div>
                    </div>

                    <!-- Elegant Dropdown: Kategori -->
                    <div class="ult-aset-field">
                        <label class="ult-aset-label">Kategori Aset</label>
                        <div class="ult-dropdown" data-dropdown>
                            <input type="hidden" name="category_id" value="<?= (int)$val('category_id') ?>">
                            <button type="button" class="ult-dropdown-trigger <?= (int)$val('category_id') > 0 ? 'has-value' : '' ?>">
                                <i class="bi bi-bookmark" style="color:var(--admin-success);"></i>
                                <span><?= (int)$val('category_id') > 0 ? ($categories[array_search($val('category_id'), array_column($categories, 'id'))]['name'] ?? 'Pilih Kategori') : '— Pilih Kategori —' ?></span>
                                <i class="bi bi-chevron-down chevron"></i>
                            </button>
                            <div class="ult-dropdown-menu">
                                <div class="ult-dd-item <?= (int)$val('category_id') === 0 ? 'selected' : '' ?>" data-value="0" data-label="— Pilih Kategori —">
                                    <i class="bi bi-bookmark" style="color:var(--admin-text-muted);"></i><span>— Pilih Kategori —</span><i class="bi bi-check-lg"></i>
                                </div>
                                <?php foreach ($categories as $c): ?>
                                <div class="ult-dd-item <?= (int)$val('category_id') === (int)$c['id'] ? 'selected' : '' ?>" data-value="<?= $c['id'] ?>" data-label="<?= htmlspecialchars($c['name']) ?>">
                                    <i class="bi bi-bookmark-fill" style="color:var(--admin-success);"></i><span><?= htmlspecialchars($c['name']) ?></span><i class="bi bi-check-lg"></i>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Elegant Dropdown: Departemen -->
                    <div class="ult-aset-field">
                        <label class="ult-aset-label">Lokasi / Departemen</label>
                        <div class="ult-dropdown" data-dropdown>
                            <input type="hidden" name="department_id" value="<?= (int)$val('department_id') ?>">
                            <button type="button" class="ult-dropdown-trigger <?= (int)$val('department_id') > 0 ? 'has-value' : '' ?>">
                                <i class="bi bi-building" style="color:var(--admin-success);"></i>
                                <span><?= (int)$val('department_id') > 0 ? ($departments[array_search($val('department_id'), array_column($departments, 'id'))]['name'] ?? 'Pilih Dept') : '— Umum / Gudang —' ?></span>
                                <i class="bi bi-chevron-down chevron"></i>
                            </button>
                            <div class="ult-dropdown-menu">
                                <div class="ult-dd-item <?= (int)$val('department_id') === 0 ? 'selected' : '' ?>" data-value="0" data-label="— Umum / Gudang —">
                                    <i class="bi bi-grid-3x3-gap" style="color:var(--admin-text-muted);"></i><span>— Umum / Gudang —</span><i class="bi bi-check-lg"></i>
                                </div>
                                <?php foreach ($departments as $d): ?>
                                <div class="ult-dd-item <?= (int)$val('department_id') === (int)$d['id'] ? 'selected' : '' ?>" data-value="<?= $d['id'] ?>" data-label="<?= htmlspecialchars($d['name']) ?>">
                                    <i class="bi bi-building-fill" style="color:var(--admin-success);"></i><span><?= htmlspecialchars($d['name']) ?></span><i class="bi bi-check-lg"></i>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Elegant Dropdown: Kondisi -->
                    <div class="ult-aset-field">
                        <label class="ult-aset-label">Kondisi Aset</label>
                        <div class="ult-dropdown" data-dropdown>
                            <input type="hidden" name="condition" value="<?= htmlspecialchars($val('condition', 'baik')) ?>">
                            <button type="button" class="ult-dropdown-trigger has-value">
                                <i class="bi bi-patch-check" style="color:var(--admin-success);"></i>
                                <span><?= ucfirst(str_replace('_', ' ', $val('condition', 'baik'))) ?></span>
                                <i class="bi bi-chevron-down chevron"></i>
                            </button>
                            <div class="ult-dropdown-menu">
                                <?php foreach (['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'hilang' => 'Hilang', 'dihapuskan' => 'Dihapuskan'] as $k => $v): ?>
                                <div class="ult-dd-item <?= $val('condition', 'baik') === $k ? 'selected' : '' ?>" data-value="<?= $k ?>" data-label="<?= $v ?>">
                                    <i class="bi bi-<?= $k === 'baik' ? 'check-circle-fill text-success' : ($k === 'rusak_ringan' ? 'exclamation-triangle-fill text-warning' : 'x-circle-fill text-danger') ?>"></i>
                                    <span><?= $v ?></span><i class="bi bi-check-lg"></i>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="ult-aset-field">
                        <label class="ult-aset-label">Merk / Brand</label>
                        <div class="ult-aset-input">
                            <input type="text" name="brand" value="<?= htmlspecialchars($val('brand')) ?>" maxlength="100" placeholder="Contoh: Asus, Canon">
                            <i class="bi bi-upc-scan icon"></i>
                        </div>
                    </div>
                    <div class="ult-aset-field">
                        <label class="ult-aset-label">No. Seri</label>
                        <div class="ult-aset-input">
                            <input type="text" name="serial_number" value="<?= htmlspecialchars($val('serial_number')) ?>" maxlength="100" placeholder="Serial Number">
                            <i class="bi bi-qr-code icon"></i>
                        </div>
                    </div>
                    <div class="ult-aset-field">
                        <label class="ult-aset-label">Jumlah</label>
                        <div class="ult-aset-input">
                            <input type="number" name="quantity" value="<?= htmlspecialchars($val('quantity', '1')) ?>" min="1" placeholder="1">
                            <i class="bi bi-layers icon"></i>
                        </div>
                    </div>

                    <div class="ult-aset-field">
                        <label class="ult-aset-label">Harga Perolehan (Rp)</label>
                        <div class="ult-aset-input">
                            <input type="text" name="purchase_price" id="asetPrice" value="<?= htmlspecialchars($val('purchase_price') ? number_format((float)$val('purchase_price'), 0, ',', '.') : '') ?>" placeholder="0">
                            <i class="bi bi-cash-stack icon"></i>
                        </div>
                    </div>
                    <div class="ult-aset-field">
                        <label class="ult-aset-label">Tanggal Perolehan</label>
                        <div class="ult-aset-input">
                            <input type="date" name="purchase_date" value="<?= htmlspecialchars($val('purchase_date')) ?>">
                            <i class="bi bi-calendar-check icon"></i>
                        </div>
                    </div>
                    <div class="ult-aset-field">
                        <label class="ult-aset-label">Penanggung Jawab</label>
                        <div class="ult-aset-input">
                            <input type="text" name="responsible_person" value="<?= htmlspecialchars($val('responsible_person')) ?>" maxlength="100" placeholder="Nama PIC">
                            <i class="bi bi-person-badge icon"></i>
                        </div>
                    </div>

                    <div class="ult-aset-field full">
                        <label class="ult-aset-label">Foto Aset</label>
                        <div class="ult-aset-drop" id="asetDropZone">
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" id="asetPhotoInput">
                            <div class="ult-aset-drop-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                            <div style="font-size:.9rem;font-weight:600;color:var(--admin-text);">Klik atau seret foto ke sini</div>
                            <div style="font-size:.75rem;color:var(--admin-text-muted);">JPG / PNG / WEBP. Maksimal 2 MB.</div>
                            <div class="ult-aset-preview <?= $currentPhoto ? 'active' : '' ?>" id="asetPreview">
                                <?php if ($currentPhoto): ?>
                                    <img src="<?= $currentPhoto ?>" alt="Preview">
                                <?php else: ?>
                                    <img src="" alt="Preview" id="asetPreviewImg">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 0;">
            <a href="<?= url('admin/aset') ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" style="background:var(--admin-success);border-color:var(--admin-success);"><i class="bi bi-save-fill"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Aset' ?></button>
        </div>
    </div>
</form>

<script>
(function(){
    // Dropdown Logic (Same as Keuangan)
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
                val !== '0' && val !== '' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
            });
        });
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-dropdown.open').forEach(d => d.classList.remove('open')));

    // Currency
    const price = document.getElementById('asetPrice');
    if(price){
        price.addEventListener('input', function(){ this.value = this.value.replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g, "."); });
        price.addEventListener('focus', function(){ this.value = this.value.replace(/\./g, ''); });
    }

    // Photo Preview
    const photoInput = document.getElementById('asetPhotoInput');
    const dropZone = document.getElementById('asetDropZone');
    const preview = document.getElementById('asetPreview');
    const previewImg = document.getElementById('asetPreviewImg');
    if(photoInput){
        photoInput.addEventListener('change', function(){
            if(this.files[0]){
                const reader = new FileReader();
                reader.onload = function(e){
                    if(previewImg) previewImg.src = e.target.result;
                    preview.classList.add('active');
                    dropZone.classList.add('has-file');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
})();
</script>