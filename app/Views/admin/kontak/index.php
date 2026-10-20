<?php
use App\Helpers\Auth;
$canDelete = Auth::hasRole('super_admin');

// ===== STATS =====
$db = \App\Helpers\Database::getInstance();
try {
    $statTotal   = count($messages);
    $statUnread  = count(array_filter($messages, fn($m) => !$m['is_read']));
    $statRead    = count(array_filter($messages, fn($m) => $m['is_read']));
    $statReplied = count(array_filter($messages, fn($m) => $m['is_replied']));
} catch (\Throwable $e) {
    $statTotal = $statUnread = $statRead = $statReplied = 0;
}

$currentView = $_GET['view'] ?? 'table';
$activeFilter = $_GET['read'] ?? '';
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-kontak-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-kontak-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-kontak-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#3b82f6);}
.ult-kontak-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-kontak-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-kontak-stat-info{flex:1;min-width:0;}
.ult-kontak-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-kontak-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-kontak-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-kontak-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-kontak-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-kontak-dropdown{position:relative;display:inline-block;}
.ult-kontak-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:160px;justify-content:space-between;}
.ult-kontak-dropdown-trigger:hover{border-color:var(--admin-primary);background:var(--admin-bg);}
.ult-kontak-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.05);}
.ult-kontak-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-kontak-dropdown.open .ult-kontak-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-kontak-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;}
.ult-kontak-dropdown.open .ult-kontak-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-kontak-dropdown-menu.right{left:auto;right:0;}
.ult-kontak-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-kontak-dd-item:last-child{border-bottom:none;}
.ult-kontak-dd-item:hover{background:var(--admin-bg);}
.ult-kontak-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-kontak-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-kontak-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-kontak-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}

/* ===== FILTER CHIPS ===== */
.ult-kontak-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);align-items:center;}
.ult-kontak-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-kontak-chip i{font-size:.7rem;}
.ult-kontak-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-kontak-chip-close:hover{color:var(--admin-danger);}
.ult-kontak-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;}
.ult-kontak-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== VIEW TOGGLE ===== */
.ult-kontak-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-kontak-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-kontak-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-kontak-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-kontak-table-wrap{overflow-x:auto;}
.ult-kontak-cell{display:flex;align-items:center;gap:.875rem;min-width:220px;}
.ult-kontak-avatar{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;flex-shrink:0;font-size:1rem;}
.ult-kontak-avatar.unread{background:linear-gradient(135deg,#3b82f6,#2563eb);box-shadow:0 4px 12px rgba(59,130,246,.3);}
.ult-kontak-avatar.read{background:linear-gradient(135deg,#94a3b8,#64748b);}
.ult-kontak-info{flex:1;min-width:0;}
.ult-kontak-name{font-weight:700;color:var(--admin-text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-kontak-name.unread{color:var(--admin-primary);}
.ult-kontak-email{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;display:flex;align-items:center;gap:.25rem;}
.ult-kontak-email i{font-size:.65rem;}
.ult-kontak-subject{font-weight:600;color:var(--admin-text);line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-kontak-subject.unread{color:var(--admin-primary);font-weight:700;}
.ult-kontak-date{display:flex;flex-direction:column;gap:.125rem;}
.ult-kontak-date-main{font-weight:600;font-size:.875rem;color:var(--admin-text);}
.ult-kontak-date-sub{font-size:.7rem;color:var(--admin-text-muted);display:flex;align-items:center;gap:.25rem;}
.ult-kontak-status{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
.ult-kontak-unread-dot{width:8px;height:8px;border-radius:50%;background:#3b82f6;display:inline-block;margin-right:.375rem;animation:ult-kontak-pulse 1.5s infinite;}
@keyframes ult-kontak-pulse{0%,100%{opacity:1;}50%{opacity:.3;}}

/* ===== ROW ACTION ===== */
.ult-kontak-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-kontak-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-kontak-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-kontak-row-btn.view:hover{color:var(--admin-primary);background:rgba(26,82,118,.08);}
.ult-kontak-row-btn.reply:hover{color:#10b981;background:rgba(16,185,129,.08);}
.ult-kontak-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-kontak-row-menu{min-width:200px;}
.ult-kontak-row-menu .ult-kontak-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-kontak-row-menu .ult-kontak-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-kontak-row-menu .ult-kontak-dd-item.danger{color:var(--admin-danger);}
.ult-kontak-row-menu .ult-kontak-dd-item.danger:hover{background:rgba(239,68,68,.08);}

/* ===== GRID VIEW (Inbox Style) ===== */
.ult-kontak-grid{display:flex;flex-direction:column;gap:.75rem;padding:1.5rem;}
.ult-kontak-inbox-item{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);padding:1.25rem;display:grid;grid-template-columns:auto 1fr auto;gap:1.25rem;align-items:center;transition:all .3s;cursor:pointer;}
.ult-kontak-inbox-item:hover{box-shadow:0 8px 25px rgba(0,0,0,.08);border-color:var(--admin-primary);}
.ult-kontak-inbox-item.unread{background:linear-gradient(to right, rgba(59,130,246,.04), transparent);border-left:3px solid #3b82f6;}
.ult-kontak-inbox-item.unread:hover{border-left-color:var(--admin-primary);}
@media (max-width:768px){.ult-kontak-inbox-item{grid-template-columns:1fr;}}
.ult-kontak-inbox-sender{display:flex;align-items:center;gap:.875rem;}
.ult-kontak-inbox-content{min-width:0;}
.ult-kontak-inbox-subject{font-weight:700;color:var(--admin-text);margin-bottom:.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-kontak-inbox-item.unread .ult-kontak-inbox-subject{color:var(--admin-primary);}
.ult-kontak-inbox-preview{font-size:.825rem;color:var(--admin-text-secondary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:.375rem;}
.ult-kontak-inbox-meta{display:flex;gap:1rem;font-size:.75rem;color:var(--admin-text-muted);flex-wrap:wrap;}
.ult-kontak-inbox-meta span{display:flex;align-items:center;gap:.25rem;}
.ult-kontak-inbox-meta i{font-size:.7rem;}
.ult-kontak-inbox-actions{display:flex;gap:.375rem;align-items:center;}

/* ===== EMPTY STATE ===== */
.ult-kontak-empty{text-align:center;padding:4rem 2rem;}
.ult-kontak-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:var(--admin-primary);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-kontak-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(26,82,118,.3);border-radius:50%;animation:ult-kontak-spin 20s linear infinite;}
@keyframes ult-kontak-spin{to{transform:rotate(360deg);}}
.ult-kontak-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-kontak-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-kontak-stats{grid-template-columns:repeat(2,1fr);}
    .ult-kontak-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-kontak-stats{grid-template-columns:1fr;}
    .ult-kontak-grid{padding:1rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-kontak-stats">
    <div class="ult-kontak-stat" style="--ult-color:#3b82f6;">
        <div class="ult-kontak-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-envelope-fill"></i></div>
        <div class="ult-kontak-stat-info">
            <div class="ult-kontak-stat-label">Total Pesan</div>
            <div class="ult-kontak-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-kontak-stat-sub"><i class="bi bi-database-fill"></i> Keseluruhan</div>
        </div>
    </div>
    <div class="ult-kontak-stat" style="--ult-color:#ef4444;">
        <div class="ult-kontak-stat-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="bi bi-envelope-fill"></i></div>
        <div class="ult-kontak-stat-info">
            <div class="ult-kontak-stat-label">Belum Dibaca</div>
            <div class="ult-kontak-stat-value"><?= number_format($statUnread) ?></div>
            <div class="ult-kontak-stat-sub"><i class="bi bi-exclamation-circle-fill"></i> Perlu ditindaklanjuti</div>
        </div>
    </div>
    <div class="ult-kontak-stat" style="--ult-color:#10b981;">
        <div class="ult-kontak-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-envelope-open-fill"></i></div>
        <div class="ult-kontak-stat-info">
            <div class="ult-kontak-stat-label">Sudah Dibaca</div>
            <div class="ult-kontak-stat-value"><?= number_format($statRead) ?></div>
            <div class="ult-kontak-stat-sub"><i class="bi bi-check-circle-fill"></i> <?= $statTotal > 0 ? round(($statRead / $statTotal) * 100) : 0 ?>% dari total</div>
        </div>
    </div>
    <div class="ult-kontak-stat" style="--ult-color:#8b5cf6;">
        <div class="ult-kontak-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-reply-fill"></i></div>
        <div class="ult-kontak-stat-info">
            <div class="ult-kontak-stat-label">Sudah Dibalas</div>
            <div class="ult-kontak-stat-value"><?= number_format($statReplied) ?></div>
            <div class="ult-kontak-stat-sub"><i class="bi bi-chat-dots-fill"></i> Respon terkirim</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Pesan Kontak</h1>
        <p class="page-subtitle">Pesan dari pengunjung website</p>
    </div>
    <div class="ult-kontak-header-actions">
        <div class="ult-kontak-view-toggle" id="ultKontakViewToggle">
            <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
            </button>
            <button type="button" class="<?= $currentView === 'grid' ? 'active' : '' ?>" data-view="grid" title="Tampilan Inbox">
                <i class="bi bi-inbox-fill"></i> <span class="d-none-mobile">Inbox</span>
            </button>
        </div>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-kontak-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-envelope-fill"></i> Kotak Masuk</h3>
                    <span class="pgn-result-info">
                        Total <strong><?= number_format($statTotal) ?></strong> pesan
                        <?php if ($statUnread > 0): ?>
                            <span style="color:var(--admin-danger);font-weight:700;">• <?= $statUnread ?> belum dibaca</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Toolbar -->
        <form method="GET" action="<?= url('admin/kontak') ?>" class="pgn-toolbar" style="padding:1rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="toolbar-search" style="flex:2;min-width:220px;">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Cari nama, email, atau subjek...">
            </div>
            
            <!-- Custom Status Dropdown -->
            <div class="ult-kontak-dropdown" data-dropdown>
                <input type="hidden" name="read" id="ultKontakReadValue" value="<?= htmlspecialchars($read ?? '') ?>">
                <button type="button" class="ult-kontak-dropdown-trigger <?= ($read ?? '') !== '' ? 'has-value' : '' ?>" id="ultKontakReadTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;">
                        <?php if (($read ?? '') === 'unread'): ?>
                            <i class="bi bi-envelope-fill" style="color:#ef4444;"></i>
                            <span>Belum Dibaca</span>
                        <?php elseif (($read ?? '') === 'read'): ?>
                            <i class="bi bi-envelope-open-fill" style="color:#10b981;"></i>
                            <span>Sudah Dibaca</span>
                        <?php else: ?>
                            <i class="bi bi-funnel" style="color:var(--admin-primary);"></i>
                            <span>Semua Pesan</span>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-kontak-dropdown-menu" style="min-width:200px;">
                    <div class="ult-kontak-dd-item <?= ($read ?? '') === '' ? 'selected' : '' ?>" data-value="">
                        <i class="bi bi-funnel" style="color:var(--admin-text-muted);"></i><span>Semua Pesan</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-kontak-dd-divider"></div>
                    <div class="ult-kontak-dd-item <?= ($read ?? '') === 'unread' ? 'selected' : '' ?>" data-value="unread">
                        <i class="bi bi-envelope-fill" style="color:#ef4444;"></i><span>Belum Dibaca</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-kontak-dd-item <?= ($read ?? '') === 'read' ? 'selected' : '' ?>" data-value="read">
                        <i class="bi bi-envelope-open-fill" style="color:#10b981;"></i><span>Sudah Dibaca</span><i class="bi bi-check-lg"></i>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Terapkan</button>
            <?php if (($search ?? '') !== '' || ($read ?? '') !== ''): ?>
                <a href="<?= url('admin/kontak') ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
            <?php endif; ?>
        </form>
        
        <!-- Filter Chips -->
        <?php if (($search ?? '') !== '' || ($read ?? '') !== ''): ?>
        <div class="ult-kontak-chips">
            <span class="ult-kontak-chips-label"><i class="bi bi-funnel-fill"></i> Filter Aktif:</span>
            <?php if (($search ?? '') !== ''): ?>
                <span class="ult-kontak-chip">
                    <i class="bi bi-search"></i> Pencarian: "<strong><?= htmlspecialchars($search) ?></strong>"
                    <a href="<?= url('admin/kontak') . '?read=' . ($read ?? '') ?>" class="ult-kontak-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if (($read ?? '') === 'unread'): ?>
                <span class="ult-kontak-chip">
                    <i class="bi bi-envelope-fill"></i> Status: <strong>Belum Dibaca</strong>
                    <a href="<?= url('admin/kontak') . '?q=' . ($search ?? '') ?>" class="ult-kontak-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php elseif (($read ?? '') === 'read'): ?>
                <span class="ult-kontak-chip">
                    <i class="bi bi-envelope-open-fill"></i> Status: <strong>Sudah Dibaca</strong>
                    <a href="<?= url('admin/kontak') . '?q=' . ($search ?? '') ?>" class="ult-kontak-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <a href="<?= url('admin/kontak') ?>" class="ult-kontak-chips-clear"><i class="bi bi-x-circle"></i> Hapus Semua</a>
        </div>
        <?php endif; ?>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultKontakTableView" class="ult-kontak-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead><tr><th>Tanggal</th><th style="min-width:220px;">Pengirim</th><th>Subjek</th><th>Status</th><th style="width:140px;text-align:right;">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($messages as $m): 
                    $isUnread = !$m['is_read'];
                    $avatarColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];
                    $colorIdx = crc32($m['email'] ?? $m['name']) % count($avatarColors);
                    $avatarColor = $avatarColors[$colorIdx];
                ?>
                    <tr style="<?= $isUnread ? 'background:rgba(59,130,246,.03);' : '' ?>">
                        <td>
                            <div class="ult-kontak-date">
                                <span class="ult-kontak-date-main"><?= date('d M Y', strtotime($m['created_at'])) ?></span>
                                <span class="ult-kontak-date-sub"><i class="bi bi-clock"></i> <?= date('H:i', strtotime($m['created_at'])) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="ult-kontak-cell">
                                <div class="ult-kontak-avatar <?= $isUnread ? 'unread' : 'read' ?>" style="background:linear-gradient(135deg,<?= $avatarColor ?>,<?= $avatarColor ?>cc);">
                                    <?= strtoupper(substr($m['name'], 0, 1)) ?>
                                </div>
                                <div class="ult-kontak-info">
                                    <div class="ult-kontak-name <?= $isUnread ? 'unread' : '' ?>">
                                        <?= $isUnread ? '<span class="ult-kontak-unread-dot"></span>' : '' ?>
                                        <?= htmlspecialchars($m['name']) ?>
                                    </div>
                                    <div class="ult-kontak-email"><i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($m['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="ult-kontak-subject <?= $isUnread ? 'unread' : '' ?>"><?= htmlspecialchars($m['subject']) ?></div>
                        </td>
                        <td>
                            <?php if ($m['is_replied']): ?>
                                <span class="ult-kontak-status" style="background:rgba(139,92,246,.15);color:#8b5cf6;">
                                    <i class="bi bi-reply-fill"></i> Dibalas
                                </span>
                            <?php elseif ($isUnread): ?>
                                <span class="ult-kontak-status" style="background:rgba(239,68,68,.15);color:#ef4444;">
                                    <i class="bi bi-envelope-fill"></i> Baru
                                </span>
                            <?php else: ?>
                                <span class="ult-kontak-status" style="background:rgba(16,185,129,.15);color:#10b981;">
                                    <i class="bi bi-envelope-open-fill"></i> Dibaca
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="ult-kontak-row-actions">
                                <a href="<?= url('admin/kontak/baca/' . $m['id']) ?>" class="ult-kontak-row-btn view" title="Baca"><i class="bi bi-eye-fill"></i></a>
                                <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=Re: <?= urlencode($m['subject']) ?>" class="ult-kontak-row-btn reply" title="Balas via Email"><i class="bi bi-reply-fill"></i></a>
                                
                                <div class="ult-kontak-dropdown" data-dropdown>
                                    <button type="button" class="ult-kontak-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="ult-kontak-dropdown-menu right ult-kontak-row-menu">
                                        <div class="ult-kontak-dd-header">Pesan</div>
                                        <a href="<?= url('admin/kontak/baca/' . $m['id']) ?>" class="ult-kontak-dd-item">
                                            <i class="bi bi-eye-fill"></i><span>Baca Pesan</span>
                                        </a>
                                        <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=Re: <?= urlencode($m['subject']) ?>" class="ult-kontak-dd-item">
                                            <i class="bi bi-reply-fill"></i><span>Balas via Email</span>
                                        </a>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $m['phone'] ?? '') ?>?text=Halo%20<?= urlencode($m['name']) ?>,%20terkait%20pesan%20Anda..." target="_blank" class="ult-kontak-dd-item">
                                            <i class="bi bi-whatsapp"></i><span>Chat WhatsApp</span>
                                        </a>
                                        <div class="ult-kontak-dd-divider"></div>
                                        <div class="ult-kontak-dd-header">Informasi</div>
                                        <div class="ult-kontak-dd-item" style="cursor:default;">
                                            <i class="bi bi-geo-alt-fill"></i><span>IP: <?= htmlspecialchars($m['ip_address'] ?? '-') ?></span>
                                        </div>
                                        <div class="ult-kontak-dd-item" style="cursor:default;">
                                            <i class="bi bi-clock-fill"></i><span>Dikirim: <?= date('d M Y H:i', strtotime($m['created_at'])) ?></span>
                                        </div>
                                        <?php if ($canDelete): ?>
                                        <div class="ult-kontak-dd-divider"></div>
                                        <a href="<?= url('admin/kontak/hapus/' . $m['id']) ?>" class="ult-kontak-dd-item danger" onclick="return confirm('Hapus pesan dari <?= htmlspecialchars(addslashes($m['name'])) ?>?');">
                                            <i class="bi bi-trash3-fill"></i><span>Hapus Pesan</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$messages): ?><tr><td colspan="5">
                    <div class="ult-kontak-empty">
                        <div class="ult-kontak-empty-icon"><i class="bi bi-inbox"></i></div>
                        <h3>Kotak Masuk Kosong</h3>
                        <p>
                            <?php if (($search ?? '') !== '' || ($read ?? '') !== ''): ?>
                                Tidak ada pesan yang cocok dengan filter Anda. Coba ubah kriteria pencarian.
                            <?php else: ?>
                                Belum ada pesan masuk dari pengunjung website. Pesan akan muncul di sini setelah ada yang menghubungi melalui form kontak.
                            <?php endif; ?>
                        </p>
                        <?php if (($search ?? '') !== '' || ($read ?? '') !== ''): ?>
                            <a href="<?= url('admin/kontak') ?>" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset Filter</a>
                        <?php endif; ?>
                    </div>
                </td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== GRID VIEW (Inbox Style) ===== -->
        <div id="ultKontakGridView" class="ult-kontak-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php foreach ($messages as $m): 
                $isUnread = !$m['is_read'];
                $avatarColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];
                $colorIdx = crc32($m['email'] ?? $m['name']) % count($avatarColors);
                $avatarColor = $avatarColors[$colorIdx];
            ?>
            <a href="<?= url('admin/kontak/baca/' . $m['id']) ?>" class="ult-kontak-inbox-item <?= $isUnread ? 'unread' : '' ?>">
                <div class="ult-kontak-inbox-sender">
                    <div class="ult-kontak-avatar <?= $isUnread ? 'unread' : 'read' ?>" style="background:linear-gradient(135deg,<?= $avatarColor ?>,<?= $avatarColor ?>cc);">
                        <?= strtoupper(substr($m['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="ult-kontak-name <?= $isUnread ? 'unread' : '' ?>" style="font-size:.9rem;">
                            <?= $isUnread ? '<span class="ult-kontak-unread-dot"></span>' : '' ?>
                            <?= htmlspecialchars($m['name']) ?>
                        </div>
                        <div class="ult-kontak-email" style="font-size:.75rem;"><i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($m['email']) ?></div>
                    </div>
                </div>
                <div class="ult-kontak-inbox-content">
                    <div class="ult-kontak-inbox-subject"><?= htmlspecialchars($m['subject']) ?></div>
                    <div class="ult-kontak-inbox-preview"><?= htmlspecialchars(mb_substr($m['message'], 0, 100)) ?><?= strlen($m['message']) > 100 ? '...' : '' ?></div>
                    <div class="ult-kontak-inbox-meta">
                        <span><i class="bi bi-calendar"></i> <?= date('d M Y H:i', strtotime($m['created_at'])) ?></span>
                        <?php if ($m['is_replied']): ?><span style="color:#8b5cf6;"><i class="bi bi-reply-fill"></i> Sudah dibalas</span><?php endif; ?>
                    </div>
                </div>
                <div class="ult-kontak-inbox-actions">
                    <?php if ($m['is_replied']): ?>
                        <span class="ult-kontak-status" style="background:rgba(139,92,246,.15);color:#8b5cf6;font-size:.7rem;"><i class="bi bi-reply-fill"></i> Dibalas</span>
                    <?php elseif ($isUnread): ?>
                        <span class="ult-kontak-status" style="background:rgba(239,68,68,.15);color:#ef4444;font-size:.7rem;"><i class="bi bi-envelope-fill"></i> Baru</span>
                    <?php else: ?>
                        <span class="ult-kontak-status" style="background:rgba(16,185,129,.15);color:#10b981;font-size:.7rem;"><i class="bi bi-envelope-open-fill"></i> Dibaca</span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
            
            <?php if (!$messages): ?>
            <div class="ult-kontak-empty">
                <div class="ult-kontak-empty-icon"><i class="bi bi-inbox"></i></div>
                <h3>Kotak Masuk Kosong</h3>
                <p>Belum ada pesan masuk dari pengunjung website.</p>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function(){
    'use strict';
    
    // ===== CUSTOM DROPDOWN =====
    document.querySelectorAll('.ult-kontak-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-kontak-dropdown-trigger, .ult-kontak-row-btn');
        const menu = dd.querySelector('.ult-kontak-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-kontak-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
        });
        menu.querySelectorAll('.ult-kontak-dd-item[data-value]').forEach(function(item){
            item.addEventListener('click', function(e){
                e.preventDefault();
                const val = this.getAttribute('data-value');
                const lbl = this.getAttribute('data-label') || this.querySelector('span').textContent.trim();
                const hidden = dd.querySelector('input[type="hidden"]');
                if(hidden) hidden.value = val;
                const span = trigger.querySelector('span');
                if(span){
                    const icon = trigger.querySelector('i:not(.chevron)');
                    if(icon){
                        span.innerHTML = '';
                        span.appendChild(icon.cloneNode(true));
                        const ts = document.createElement('span');
                        ts.style.cssText = 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
                        ts.textContent = lbl;
                        span.appendChild(ts);
                    } else { span.textContent = lbl; }
                }
                menu.querySelectorAll('.ult-kontak-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' && val !== '0' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
                if(hidden && hidden.name === 'read'){
                    const form = dd.closest('form');
                    if(form) form.submit();
                }
            });
        });
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-kontak-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultKontakViewToggle');
    const tv = document.getElementById('ultKontakTableView');
    const gv = document.getElementById('ultKontakGridView');
    if(vt){
        vt.querySelectorAll('button[data-view]').forEach(btn => {
            btn.addEventListener('click', function(){
                const v = this.getAttribute('data-view');
                vt.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                if(tv && gv){
                    if(v === 'grid'){ tv.style.display = 'none'; gv.style.display = 'flex'; }
                    else { tv.style.display = ''; gv.style.display = 'none'; }
                }
                try { localStorage.setItem('ult_kontak_view', v); } catch(e){}
            });
        });
    }
})();
</script>