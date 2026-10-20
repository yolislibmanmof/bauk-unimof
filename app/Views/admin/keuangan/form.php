<?php
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);
$val = static function (string $key, $default = '') use ($old, $data) {
    if (array_key_exists($key, $old) && $old[$key] !== '') return $old[$key];
    if (is_array($data) && isset($data[$key]) && $data[$key] !== '') return $data[$key];
    return $default;
};
$isEdit = ($mode === 'edit');
$pageTitle = $isEdit ? 'Edit Transaksi Keuangan' : 'Input Transaksi Baru';
?>

<style>
.ult-keu-layout{display:grid;grid-template-columns:1fr;gap:1.5rem;}
.ult-keu-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;}
.ult-keu-header{padding:1.25rem 1.5rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-keu-header i{font-size:1.25rem;color:var(--admin-primary);}
.ult-keu-header h3{font-size:1.1rem;font-weight:700;margin:0;}
.ult-keu-body{padding:1.5rem;}
.ult-keu-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem;}
@media(max-width:768px){.ult-keu-grid{grid-template-columns:1fr;}}
.ult-keu-field{display:flex;flex-direction:column;gap:.375rem;}
.ult-keu-field.full{grid-column:1/-1;}
.ult-keu-label{font-size:.825rem;font-weight:600;color:var(--admin-text);}
.ult-keu-label .req{color:var(--admin-danger);}
.ult-keu-input{position:relative;}
.ult-keu-input .icon{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);font-size:1rem;pointer-events:none;}
.ult-keu-input input,.ult-keu-input select,.ult-keu-input textarea{width:100%;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);font-size:.9rem;background:var(--admin-bg);transition:all .2s;}
.ult-keu-input textarea{padding-left:1rem;resize:vertical;min-height:100px;}
.ult-keu-input input:focus,.ult-keu-input select:focus,.ult-keu-input textarea:focus{outline:none;border-color:var(--admin-primary);background:#fff;box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-keu-input input:focus ~ .icon{color:var(--admin-primary);}
.ult-keu-hint{font-size:.75rem;color:var(--admin-text-muted);margin-top:.25rem;}

/* Elegant Dropdown (Reusable) */
.ult-dropdown{position:relative;}
.ult-dropdown-trigger{width:100%;display:flex;align-items:center;gap:.5rem;padding:.75rem 1rem .75rem 2.75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);background:var(--admin-bg);color:var(--admin-text);font-size:.9rem;cursor:pointer;transition:all .2s;text-align:left;}
.ult-dropdown-trigger:hover{border-color:var(--admin-primary);background:#fff;}
.ult-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.04);}
.ult-dropdown-trigger i.chevron{margin-left:auto;font-size:.7rem;color:var(--admin-text-muted);transition:transform .2s;}
.ult-dropdown.open .ult-dropdown-trigger{border-color:var(--admin-primary);background:#fff;box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-dropdown.open .ult-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:250px;overflow-y:auto;}
.ult-dropdown.open .ult-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;}
.ult-dd-item:hover{background:var(--admin-bg);}
.ult-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-dd-item.selected i.bi-check-lg{opacity:1;}
</style>

<div class="page-header">
    <div><h1 class="page-title"><?= $pageTitle ?></h1><p class="page-subtitle">Kelola pemasukan dan pengeluaran dengan akurat</p></div>
    <a href="<?= url('admin/keuangan') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<form method="POST" action="<?= url('admin/keuangan/simpan') ?>" enctype="multipart/form-data" id="ultKeuForm">
    <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$data['id'] ?>"><?php endif; ?>
    
    <div class="ult-keu-layout">
        <!-- Detail Transaksi -->
        <div class="ult-keu-card">
            <div class="ult-keu-header"><i class="bi bi-receipt"></i><h3>Detail Transaksi</h3></div>
            <div class="ult-keu-body">
                <div class="ult-keu-grid">
                    <div class="ult-keu-field">
                        <label class="ult-keu-label">Tipe Transaksi <span class="req">*</span></label>
                        <div class="ult-dropdown" data-dropdown>
                            <input type="hidden" name="type" value="<?= htmlspecialchars($val('type', 'pengeluaran')) ?>" required>
                            <button type="button" class="ult-dropdown-trigger has-value">
                                <i class="bi bi-arrow-down-circle" style="color:var(--admin-danger);"></i>
                                <span><?= $val('type', 'pengeluaran') === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran' ?></span>
                                <i class="bi bi-chevron-down chevron"></i>
                            </button>
                            <div class="ult-dropdown-menu">
                                <div class="ult-dd-item <?= $val('type', 'pengeluaran') === 'pengeluaran' ? 'selected' : '' ?>" data-value="pengeluaran" data-label="Pengeluaran">
                                    <i class="bi bi-arrow-up-circle" style="color:var(--admin-danger);"></i><span>Pengeluaran</span><i class="bi bi-check-lg"></i>
                                </div>
                                <div class="ult-dd-item <?= $val('type', 'pengeluaran') === 'pemasukan' ? 'selected' : '' ?>" data-value="pemasukan" data-label="Pemasukan">
                                    <i class="bi bi-arrow-down-circle" style="color:var(--admin-success);"></i><span>Pemasukan</span><i class="bi bi-check-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ult-keu-field">
                        <label class="ult-keu-label">Tanggal Transaksi <span class="req">*</span></label>
                        <div class="ult-keu-input">
                            <input type="date" name="transaction_date" value="<?= htmlspecialchars($val('transaction_date', date('Y-m-d'))) ?>" required>
                            <i class="bi bi-calendar-event icon"></i>
                        </div>
                    </div>

                    <div class="ult-keu-field">
                        <label class="ult-keu-label">Kategori <span class="req">*</span></label>
                        <div class="ult-dropdown" data-dropdown>
                            <input type="hidden" name="category_id" value="<?= (int)$val('category_id') ?>" required>
                            <button type="button" class="ult-dropdown-trigger <?= (int)$val('category_id') > 0 ? 'has-value' : '' ?>">
                                <i class="bi bi-tag" style="color:var(--admin-primary);"></i>
                                <span><?= (int)$val('category_id') > 0 ? ($categories[array_search($val('category_id'), array_column($categories, 'id'))]['name'] ?? 'Pilih Kategori') : '— Pilih Kategori —' ?></span>
                                <i class="bi bi-chevron-down chevron"></i>
                            </button>
                            <div class="ult-dropdown-menu">
                                <div class="ult-dd-item <?= (int)$val('category_id') === 0 ? 'selected' : '' ?>" data-value="0" data-label="— Pilih Kategori —">
                                    <i class="bi bi-tag" style="color:var(--admin-text-muted);"></i><span>— Pilih Kategori —</span><i class="bi bi-check-lg"></i>
                                </div>
                                <?php foreach ($categories as $c): ?>
                                <div class="ult-dd-item <?= (int)$val('category_id') === (int)$c['id'] ? 'selected' : '' ?>" data-value="<?= $c['id'] ?>" data-label="<?= htmlspecialchars($c['name']) ?>">
                                    <i class="bi bi-bookmark-fill" style="color:var(--admin-accent);"></i><span><?= htmlspecialchars($c['name']) ?> (<?= ucfirst($c['type']) ?>)</span><i class="bi bi-check-lg"></i>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="ult-keu-field">
                        <label class="ult-keu-label">Departemen / Unit</label>
                        <div class="ult-dropdown" data-dropdown>
                            <input type="hidden" name="department_id" value="<?= (int)$val('department_id') ?>">
                            <button type="button" class="ult-dropdown-trigger <?= (int)$val('department_id') > 0 ? 'has-value' : '' ?>">
                                <i class="bi bi-building" style="color:var(--admin-primary);"></i>
                                <span><?= (int)$val('department_id') > 0 ? ($departments[array_search($val('department_id'), array_column($departments, 'id'))]['name'] ?? 'Pilih Dept') : '— Umum / Semua —' ?></span>
                                <i class="bi bi-chevron-down chevron"></i>
                            </button>
                            <div class="ult-dropdown-menu">
                                <div class="ult-dd-item <?= (int)$val('department_id') === 0 ? 'selected' : '' ?>" data-value="0" data-label="— Umum / Semua —">
                                    <i class="bi bi-grid-3x3-gap" style="color:var(--admin-text-muted);"></i><span>— Umum / Semua —</span><i class="bi bi-check-lg"></i>
                                </div>
                                <?php foreach ($departments as $d): ?>
                                <div class="ult-dd-item <?= (int)$val('department_id') === (int)$d['id'] ? 'selected' : '' ?>" data-value="<?= $d['id'] ?>" data-label="<?= htmlspecialchars($d['name']) ?>">
                                    <i class="bi bi-building-fill" style="color:var(--admin-primary);"></i><span><?= htmlspecialchars($d['name']) ?></span><i class="bi bi-check-lg"></i>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="ult-keu-field">
                        <label class="ult-keu-label">Jumlah (Rp) <span class="req">*</span></label>
                        <div class="ult-keu-input">
                            <input type="text" name="amount" id="keuAmount" value="<?= htmlspecialchars($val('amount') ? number_format((float)$val('amount'), 0, ',', '.') : '') ?>" placeholder="0" required>
                            <i class="bi bi-cash-stack icon"></i>
                        </div>
                    </div>

                    <div class="ult-keu-field">
                        <label class="ult-keu-label">Metode Pembayaran</label>
                        <div class="ult-dropdown" data-dropdown>
                            <input type="hidden" name="payment_method" value="<?= htmlspecialchars($val('payment_method', 'transfer')) ?>">
                            <button type="button" class="ult-dropdown-trigger has-value">
                                <i class="bi bi-credit-card" style="color:var(--admin-primary);"></i>
                                <span><?= ucfirst($val('payment_method', 'transfer')) ?></span>
                                <i class="bi bi-chevron-down chevron"></i>
                            </button>
                            <div class="ult-dropdown-menu">
                                <?php foreach (['transfer' => 'Transfer Bank', 'cash' => 'Tunai', 'check' => 'Cek', 'giro' => 'Giro'] as $k => $v): ?>
                                <div class="ult-dd-item <?= $val('payment_method', 'transfer') === $k ? 'selected' : '' ?>" data-value="<?= $k ?>" data-label="<?= $v ?>">
                                    <i class="bi bi-wallet2" style="color:var(--admin-info);"></i><span><?= $v ?></span><i class="bi bi-check-lg"></i>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="ult-keu-field full">
                        <label class="ult-keu-label">Deskripsi / Keterangan <span class="req">*</span></label>
                        <div class="ult-keu-input">
                            <textarea name="description" rows="3" required placeholder="Jelaskan detail transaksi ini..."><?= htmlspecialchars($val('description')) ?></textarea>
                        </div>
                    </div>

                    <div class="ult-keu-field full">
                        <label class="ult-keu-label">Lampiran (Bukti Transaksi)</label>
                        <div class="ult-keu-input">
                            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
                            <i class="bi bi-paperclip icon"></2>
                        </div>
                        <span class="ult-keu-hint">Format: PDF, JPG, PNG. Maksimal 5 MB.</span>
                        <?php if ($isEdit && !empty($data['attachment'])): ?>
                            <span class="ult-keu-hint" style="color:var(--admin-success);"><i class="bi bi-check-circle-fill"></i> File saat ini: <?= htmlspecialchars($data['attachment']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 0;">
            <a href="<?= url('admin/keuangan') ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Transaksi' ?></button>
        </div>
    </div>
</form>

<script>
(function(){
    // Elegant Dropdown Logic
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

    // Currency Format
    const amount = document.getElementById('keuAmount');
    if(amount){
        amount.addEventListener('input', function(){ this.value = this.value.replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g, "."); });
        amount.addEventListener('focus', function(){ this.value = this.value.replace(/\./g, ''); });
    }
})();
</script>