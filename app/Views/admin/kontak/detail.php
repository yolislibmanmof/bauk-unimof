<?php if (!$msg): ?>
<div class="ult-kontak-detail-empty" style="text-align:center;padding:4rem 2rem;">
    <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#fee2e2,#fecaca);color:#ef4444;display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;">
        <i class="bi bi-envelope-x"></i>
    </div>
    <h3 style="font-size:1.25rem;margin-bottom:.5rem;">Pesan Tidak Ditemukan</h3>
    <p style="color:var(--admin-text-secondary);margin-bottom:1.5rem;">Pesan yang Anda cari tidak ada atau telah dihapus.</p>
    <a href="<?= url('admin/kontak') ?>" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Kembali ke Kotak Masuk</a>
</div>
<?php return; endif; ?>

<?php
$avatarColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];
$colorIdx = crc32($msg['email'] ?? $msg['name']) % count($avatarColors);
$avatarColor = $avatarColors[$colorIdx];
$isUnread = !$msg['is_read'];
$isReplied = $msg['is_replied'];
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
.ult-kontak-detail-layout{display:grid;grid-template-columns:1fr 320px;gap:1.5rem;}
@media (max-width:992px){.ult-kontak-detail-layout{grid-template-columns:1fr;}}

/* ===== MESSAGE THREAD CARD ===== */
.ult-kontak-thread{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-xl);overflow:hidden;}
.ult-kontak-thread-header{padding:1.5rem;border-bottom:1px solid var(--admin-border);background:linear-gradient(to right, var(--admin-bg), transparent);display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
.ult-kontak-thread-avatar{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,<?= $avatarColor ?>,<?= $avatarColor ?>cc);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:700;flex-shrink:0;box-shadow:0 6px 20px <?= $avatarColor ?>44;}
.ult-kontak-thread-info{flex:1;min-width:0;}
.ult-kontak-thread-name{font-size:1.25rem;font-weight:700;color:var(--admin-text);margin-bottom:.25rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
.ult-kontak-thread-email{font-size:.85rem;color:var(--admin-text-secondary);display:flex;align-items:center;gap:.375rem;}
.ult-kontak-thread-email i{font-size:.75rem;color:var(--admin-primary);}
.ult-kontak-thread-status{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}

.ult-kontak-thread-meta{padding:1rem 1.5rem;border-bottom:1px solid var(--admin-border);background:var(--admin-bg);display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;}
.ult-kontak-meta-item{display:flex;flex-direction:column;gap:.25rem;}
.ult-kontak-meta-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:600;display:flex;align-items:center;gap:.375rem;}
.ult-kontak-meta-label i{font-size:.75rem;color:var(--admin-primary);}
.ult-kontak-meta-value{font-size:.875rem;font-weight:600;color:var(--admin-text);}
.ult-kontak-meta-value code{background:var(--admin-surface);padding:.125rem .375rem;border-radius:4px;font-size:.8rem;border:1px solid var(--admin-border);}

.ult-kontak-thread-subject{padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);}
.ult-kontak-thread-subject h2{font-size:1.35rem;font-weight:700;color:var(--admin-text);margin:0;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;}
.ult-kontak-thread-subject h2 i{color:var(--admin-primary);}

.ult-kontak-thread-body{padding:2rem 1.5rem;}
.ult-kontak-thread-message{background:var(--admin-bg);border-left:4px solid var(--admin-primary);border-radius:0 var(--radius-md) var(--radius-md) 0;padding:1.5rem;line-height:1.9;font-size:.95rem;color:var(--admin-text);white-space:pre-wrap;word-wrap:break-word;}
.ult-kontak-thread-message p{margin-bottom:1rem;}
.ult-kontak-thread-message p:last-child{margin-bottom:0;}

.ult-kontak-thread-actions{padding:1.25rem 1.5rem;border-top:1px solid var(--admin-border);display:flex;gap:.75rem;flex-wrap:wrap;justify-content:space-between;align-items:center;}
.ult-kontak-action-group{display:flex;gap:.5rem;flex-wrap:wrap;}

/* ===== SIDEBAR CARDS ===== */
.ult-kontak-sidebar{display:flex;flex-direction:column;gap:1.25rem;}
.ult-kontak-side-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;}
.ult-kontak-side-header{padding:1rem 1.25rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.625rem;}
.ult-kontak-side-header i{font-size:1.1rem;color:var(--admin-primary);}
.ult-kontak-side-header h4{font-size:.95rem;font-weight:700;margin:0;}
.ult-kontak-side-body{padding:1.25rem;}
.ult-kontak-side-row{display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;font-size:.85rem;border-bottom:1px dashed var(--admin-border);}
.ult-kontak-side-row:last-child{border-bottom:none;}
.ult-kontak-side-row span:first-child{color:var(--admin-text-muted);font-weight:600;}
.ult-kontak-side-row span:last-child{color:var(--admin-text);font-weight:500;}

/* Quick Reply */
.ult-kontak-quick-reply{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;}
.ult-kontak-quick-reply textarea{width:100%;padding:1rem;border:none;resize:vertical;min-height:100px;font-family:inherit;font-size:.9rem;line-height:1.6;background:transparent;color:var(--admin-text);}
.ult-kontak-quick-reply textarea:focus{outline:none;}
.ult-kontak-quick-reply-footer{padding:.75rem 1rem;border-top:1px solid var(--admin-border);display:flex;justify-content:space-between;align-items:center;background:var(--admin-bg);}

/* ===== RESPONSIVE ===== */
@media (max-width:640px){
    .ult-kontak-thread-header{flex-direction:column;text-align:center;}
    .ult-kontak-thread-meta{grid-template-columns:1fr;}
    .ult-kontak-thread-actions{flex-direction:column;align-items:stretch;}
    .ult-kontak-action-group{justify-content:center;}
}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Detail Pesan</h1>
        <p class="page-subtitle">Membaca pesan dari <?= htmlspecialchars($msg['name']) ?></p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: <?= urlencode($msg['subject']) ?>" class="btn btn-primary"><i class="bi bi-reply-fill"></i> Balas Email</a>
        <a href="<?= url('admin/kontak') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
</div>

<div class="ult-kontak-detail-layout">
    <!-- ===== MAIN THREAD ===== -->
    <div class="ult-kontak-thread">
        <div class="ult-kontak-thread-header">
            <div class="ult-kontak-thread-avatar"><?= strtoupper(substr($msg['name'], 0, 1)) ?></div>
            <div class="ult-kontak-thread-info">
                <div class="ult-kontak-thread-name">
                    <?= htmlspecialchars($msg['name']) ?>
                    <?php if ($isUnread): ?>
                        <span class="ult-kontak-thread-status" style="background:rgba(239,68,68,.15);color:#ef4444;">
                            <i class="bi bi-envelope-fill"></i> Baru
                        </span>
                    <?php elseif ($isReplied): ?>
                        <span class="ult-kontak-thread-status" style="background:rgba(139,92,246,.15);color:#8b5cf6;">
                            <i class="bi bi-reply-fill"></i> Dibalas
                        </span>
                    <?php else: ?>
                        <span class="ult-kontak-thread-status" style="background:rgba(16,185,129,.15);color:#10b981;">
                            <i class="bi bi-envelope-open-fill"></i> Dibaca
                        </span>
                    <?php endif; ?>
                </div>
                <div class="ult-kontak-thread-email">
                    <i class="bi bi-envelope-fill"></i>
                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" style="color:var(--admin-primary);text-decoration:none;"><?= htmlspecialchars($msg['email']) ?></a>
                    <?php if (!empty($msg['phone'])): ?>
                        <span style="margin-left:.75rem;"><i class="bi bi-telephone-fill" style="color:var(--admin-success);"></i> <?= htmlspecialchars($msg['phone']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="ult-kontak-thread-meta">
            <div class="ult-kontak-meta-item">
                <span class="ult-kontak-meta-label"><i class="bi bi-calendar-event-fill"></i> Diterima</span>
                <span class="ult-kontak-meta-value"><?= date('d F Y', strtotime($msg['created_at'])) ?></span>
            </div>
            <div class="ult-kontak-meta-item">
                <span class="ult-kontak-meta-label"><i class="bi bi-clock-fill"></i> Waktu</span>
                <span class="ult-kontak-meta-value"><?= date('H:i:s', strtotime($msg['created_at'])) ?> WITA</span>
            </div>
            <div class="ult-kontak-meta-item">
                <span class="ult-kontak-meta-label"><i class="bi bi-geo-alt-fill"></i> IP Address</span>
                <span class="ult-kontak-meta-value"><code><?= htmlspecialchars($msg['ip_address'] ?? 'Tidak tercatat') ?></code></span>
            </div>
            <div class="ult-kontak-meta-item">
                <span class="ult-kontak-meta-label"><i class="bi bi-tag-fill"></i> Subjek</span>
                <span class="ult-kontak-meta-value"><?= htmlspecialchars($msg['subject']) ?></span>
            </div>
        </div>
        
        <div class="ult-kontak-thread-subject">
            <h2><i class="bi bi-chat-square-text-fill"></i> <?= htmlspecialchars($msg['subject']) ?></h2>
        </div>
        
        <div class="ult-kontak-thread-body">
            <div class="ult-kontak-thread-message">
                <?= nl2br(htmlspecialchars($msg['message'])) ?>
            </div>
        </div>
        
        <div class="ult-kontak-thread-actions">
            <div class="ult-kontak-action-group">
                <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: <?= urlencode($msg['subject']) ?>" class="btn btn-primary">
                    <i class="bi bi-reply-fill"></i> Balas via Email
                </a>
                <?php if (!empty($msg['phone'])): ?>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $msg['phone']) ?>?text=Halo%20<?= urlencode($msg['name']) ?>,%20terkait%20pesan%20Anda..." target="_blank" class="btn btn-secondary" style="background:#25D366;color:#fff!important;border-color:#25D366;">
                    <i class="bi bi-whatsapp"></i> Chat WhatsApp
                </a>
                <?php endif; ?>
                <a href="tel:<?= htmlspecialchars($msg['phone'] ?? '') ?>" class="btn btn-secondary" <?= empty($msg['phone']) ? 'style="opacity:.5;pointer-events:none;"' : '' ?>>
                    <i class="bi bi-telephone-fill"></i> Telepon
                </a>
            </div>
            <div class="ult-kontak-action-group">
                <?php if ($canDelete ?? false): ?>
                <a href="<?= url('admin/kontak/hapus/' . $msg['id']) ?>" class="btn btn-secondary" style="color:var(--admin-danger)!important;" onclick="return confirm('Hapus pesan ini?');">
                    <i class="bi bi-trash3-fill"></i> Hapus
                </a>
                <?php endif; ?>
                <button class="btn btn-secondary" onclick="window.print()">
                    <i class="bi bi-printer-fill"></i> Cetak
                </button>
            </div>
        </div>
    </div>
    
    <!-- ===== SIDEBAR ===== -->
    <div class="ult-kontak-sidebar">
        <!-- Info Pengirim -->
        <div class="ult-kontak-side-card">
            <div class="ult-kontak-side-header">
                <i class="bi bi-person-fill"></i>
                <h4>Info Pengirim</h4>
            </div>
            <div class="ult-kontak-side-body">
                <div class="ult-kontak-side-row">
                    <span>Nama</span>
                    <span><?= htmlspecialchars($msg['name']) ?></span>
                </div>
                <div class="ult-kontak-side-row">
                    <span>Email</span>
                    <span><a href="mailto:<?= htmlspecialchars($msg['email']) ?>" style="color:var(--admin-primary);text-decoration:none;font-size:.8rem;"><?= htmlspecialchars($msg['email']) ?></a></span>
                </div>
                <?php if (!empty($msg['phone'])): ?>
                <div class="ult-kontak-side-row">
                    <span>Telepon</span>
                    <span><?= htmlspecialchars($msg['phone']) ?></span>
                </div>
                <?php endif; ?>
                <div class="ult-kontak-side-row">
                    <span>IP Address</span>
                    <span style="font-family:monospace;font-size:.8rem;"><?= htmlspecialchars($msg['ip_address'] ?? '-') ?></span>
                </div>
            </div>
        </div>
        
        <!-- Status Pesan -->
        <div class="ult-kontak-side-card">
            <div class="ult-kontak-side-header">
                <i class="bi bi-info-circle-fill"></i>
                <h4>Status Pesan</h4>
            </div>
            <div class="ult-kontak-side-body">
                <div class="ult-kontak-side-row">
                    <span>Dibaca</span>
                    <span style="color:<?= $msg['is_read'] ? 'var(--admin-success)' : 'var(--admin-warning)' ?>;font-weight:700;">
                        <i class="bi bi-<?= $msg['is_read'] ? 'check-circle-fill' : 'clock-fill' ?>"></i>
                        <?= $msg['is_read'] ? 'Ya' : 'Belum' ?>
                    </span>
                </div>
                <div class="ult-kontak-side-row">
                    <span>Dibalas</span>
                    <span style="color:<?= $msg['is_replied'] ? 'var(--admin-success)' : 'var(--admin-text-muted)' ?>;font-weight:700;">
                        <i class="bi bi-<?= $msg['is_replied'] ? 'reply-fill' : 'reply' ?>"></i>
                        <?= $msg['is_replied'] ? 'Ya' : 'Belum' ?>
                    </span>
                </div>
                <div class="ult-kontak-side-row">
                    <span>Diterima</span>
                    <span><?= date('d M Y', strtotime($msg['created_at'])) ?></span>
                </div>
            </div>
        </div>
        
        <!-- Quick Reply -->
        <div class="ult-kontak-quick-reply">
            <div class="ult-kontak-side-header">
                <i class="bi bi-lightning-fill"></i>
                <h4>Balas Cepat</h4>
            </div>
            <textarea placeholder="Tulis balasan cepat..." id="ultQuickReply"></textarea>
            <div class="ult-kontak-quick-reply-footer">
                <span style="font-size:.75rem;color:var(--admin-text-muted);"><i class="bi bi-info-circle"></i> Akan membuka email client</span>
                <button class="btn btn-primary" style="padding:.375rem .75rem;font-size:.8rem;" onclick="ultSendQuickReply()">
                    <i class="bi bi-send-fill"></i> Kirim
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function ultSendQuickReply() {
    const textarea = document.getElementById('ultQuickReply');
    const body = textarea.value.trim();
    if (!body) {
        if (typeof showToast !== 'undefined') showToast('Tulis pesan terlebih dahulu', 'warning');
        else alert('Tulis pesan terlebih dahulu');
        return;
    }
    const email = '<?= htmlspecialchars($msg['email']) ?>';
    const subject = 'Re: <?= urlencode($msg['subject']) ?>';
    window.location.href = 'mailto:' + email + '?subject=' + subject + '&body=' + encodeURIComponent(body);
}
</script>