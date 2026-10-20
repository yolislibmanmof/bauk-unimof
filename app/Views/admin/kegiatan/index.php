<?php
// ===== STATUS MAP =====
$statusMap = [
    'planned'   => ['badge-info', 'Planned', 'bi-calendar-event-fill', '#3b82f6'],
    'ongoing'   => ['badge-warning', 'Ongoing', 'bi-play-circle-fill', '#f59e0b'],
    'completed' => ['badge-success', 'Completed', 'bi-check-circle-fill', '#10b981'],
    'cancelled' => ['badge-danger', 'Cancelled', 'bi-x-circle-fill', '#ef4444'],
];

// ===== STATS =====
try {
    $statTotal     = count($kegiatan);
    $statPlanned   = count(array_filter($kegiatan, fn($k) => $k['status'] === 'planned'));
    $statOngoing   = count(array_filter($kegiatan, fn($k) => $k['status'] === 'ongoing'));
    $statCompleted = count(array_filter($kegiatan, fn($k) => $k['status'] === 'completed'));
} catch (\Throwable $e) {
    $statTotal = $statPlanned = $statOngoing = $statCompleted = 0;
}

$currentView = $_GET['view'] ?? 'table';
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-keg-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-keg-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-keg-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#3b82f6);}
.ult-keg-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-keg-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-keg-stat-info{flex:1;min-width:0;}
.ult-keg-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-keg-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-keg-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-keg-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-keg-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-keg-dropdown{position:relative;display:inline-block;}
.ult-keg-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:150px;justify-content:space-between;}
.ult-keg-dropdown-trigger:hover{border-color:var(--admin-primary);background:var(--admin-bg);}
.ult-keg-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.05);}
.ult-keg-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-keg-dropdown.open .ult-keg-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-keg-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:300px;overflow-y:auto;}
.ult-keg-dropdown.open .ult-keg-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-keg-dropdown-menu.right{left:auto;right:0;}
.ult-keg-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-keg-dd-item:last-child{border-bottom:none;}
.ult-keg-dd-item:hover{background:var(--admin-bg);}
.ult-keg-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-keg-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-keg-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-keg-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-keg-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== FILTER CHIPS ===== */
.ult-keg-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);align-items:center;}
.ult-keg-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-keg-chip i{font-size:.7rem;}
.ult-keg-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-keg-chip-close:hover{color:var(--admin-danger);}
.ult-keg-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;}
.ult-keg-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== VIEW TOGGLE ===== */
.ult-keg-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-keg-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-keg-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-keg-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-keg-table-wrap{overflow-x:auto;}
.ult-keg-cell{display:flex;align-items:center;gap:.875rem;min-width:240px;}
.ult-keg-date-box{width:50px;height:50px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--admin-primary),var(--admin-primary-light));color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;text-align:center;}
.ult-keg-date-day{font-size:1.25rem;font-weight:700;line-height:1;}
.ult-keg-date-month{font-size:.65rem;text-transform:uppercase;letter-spacing:.5px;opacity:.9;}
.ult-keg-info{flex:1;min-width:0;}
.ult-keg-title{font-weight:700;color:var(--admin-text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-keg-time{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;display:flex;align-items:center;gap:.375rem;}
.ult-keg-time i{font-size:.7rem;}
.ult-keg-location{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:var(--admin-bg);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text-secondary);}
.ult-keg-location i{font-size:.7rem;color:var(--admin-primary);}
.ult-keg-dept{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:rgba(139,92,246,.08);border-radius:50px;font-size:.78rem;font-weight:600;color:#8b5cf6;}
.ult-keg-dept i{font-size:.7rem;}
.ult-keg-status{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}

/* ===== ROW ACTION ===== */
.ult-keg-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-keg-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-keg-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-keg-row-btn.view:hover{color:var(--admin-primary);background:rgba(26,82,118,.08);}
.ult-keg-row-btn.edit:hover{color:#f59e0b;background:rgba(245,158,11,.08);}
.ult-keg-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-keg-row-menu{min-width:200px;}
.ult-keg-row-menu .ult-keg-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-keg-row-menu .ult-keg-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-keg-row-menu .ult-keg-dd-item.danger{color:var(--admin-danger);}
.ult-keg-row-menu .ult-keg-dd-item.danger:hover{background:rgba(239,68,68,.08);}

/* ===== GRID VIEW (Timeline Style) ===== */
.ult-keg-grid{padding:1.5rem;}
.ult-keg-timeline{position:relative;padding-left:2rem;}
.ult-keg-timeline::before{content:'';position:absolute;left:19px;top:0;bottom:0;width:2px;background:linear-gradient(180deg,var(--admin-primary),var(--admin-primary-light));}
.ult-keg-timeline-item{position:relative;margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px dashed var(--admin-border);}
.ult-keg-timeline-item:last-child{margin-bottom:0;padding-bottom:0;border-bottom:none;}
.ult-keg-timeline-dot{position:absolute;left:-2rem;top:0;width:40px;height:40px;border-radius:50%;background:var(--admin-surface);border:3px solid var(--admin-primary);display:flex;align-items:center;justify-content:center;font-size:1rem;color:var(--admin-primary);z-index:2;}
.ult-keg-timeline-content{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);padding:1.25rem;transition:all .3s;}
.ult-keg-timeline-content:hover{box-shadow:0 8px 25px rgba(0,0,0,.08);border-color:var(--admin-primary);}
.ult-keg-timeline-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:.75rem;flex-wrap:wrap;}
.ult-keg-timeline-title{font-size:1.05rem;font-weight:700;color:var(--admin-text);line-height:1.4;}
.ult-keg-timeline-meta{display:flex;gap:1rem;margin-top:.75rem;flex-wrap:wrap;}
.ult-keg-timeline-meta span{display:flex;align-items:center;gap:.375rem;font-size:.8rem;color:var(--admin-text-secondary);}
.ult-keg-timeline-meta i{font-size:.75rem;color:var(--admin-primary);}
.ult-keg-timeline-actions{display:flex;gap:.375rem;margin-top:1rem;}
.ult-keg-timeline-btn{padding:.375rem .75rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:6px;font-size:.75rem;font-weight:600;color:var(--admin-text);cursor:pointer;display:inline-flex;align-items:center;gap:.375rem;transition:all .2s;text-decoration:none!important;}
.ult-keg-timeline-btn:hover{background:var(--admin-primary);color:#fff!important;border-color:var(--admin-primary);}
.ult-keg-timeline-btn.danger:hover{background:var(--admin-danger);border-color:var(--admin-danger);}

/* ===== EMPTY STATE ===== */
.ult-keg-empty{text-align:center;padding:4rem 2rem;}
.ult-keg-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:var(--admin-primary);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-keg-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(26,82,118,.3);border-radius:50%;animation:ult-keg-spin 20s linear infinite;}
@keyframes ult-keg-spin{to{transform:rotate(360deg);}}
.ult-keg-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-keg-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-keg-stats{grid-template-columns:repeat(2,1fr);}
    .ult-keg-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-keg-stats{grid-template-columns:1fr;}
    .ult-keg-grid{padding:1rem;}
    .ult-keg-timeline{padding-left:1.5rem;}
    .ult-keg-timeline::before{left:14px;}
    .ult-keg-timeline-dot{left:-1.5rem;width:32px;height:32px;font-size:.85rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-keg-stats">
    <div class="ult-keg-stat" style="--ult-color:#3b82f6;">
        <div class="ult-keg-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-calendar-event-fill"></i></div>
        <div class="ult-keg-stat-info">
            <div class="ult-keg-stat-label">Total Kegiatan</div>
            <div class="ult-keg-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-keg-stat-sub"><i class="bi bi-database-fill"></i> Keseluruhan</div>
        </div>
    </div>
    <div class="ult-keg-stat" style="--ult-color:#3b82f6;">
        <div class="ult-keg-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-calendar-plus-fill"></i></div>
        <div class="ult-keg-stat-info">
            <div class="ult-keg-stat-label">Planned</div>
            <div class="ult-keg-stat-value"><?= number_format($statPlanned) ?></div>
            <div class="ult-keg-stat-sub"><i class="bi bi-clock-history"></i> Akan datang</div>
        </div>
    </div>
    <div class="ult-keg-stat" style="--ult-color:#f59e0b;">
        <div class="ult-keg-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="bi bi-play-circle-fill"></i></div>
        <div class="ult-keg-stat-info">
            <div class="ult-keg-stat-label">Ongoing</div>
            <div class="ult-keg-stat-value"><?= number_format($statOngoing) ?></div>
            <div class="ult-keg-stat-sub"><i class="bi bi-activity"></i> Sedang berlangsung</div>
        </div>
    </div>
    <div class="ult-keg-stat" style="--ult-color:#10b981;">
        <div class="ult-keg-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-check-circle-fill"></i></div>
        <div class="ult-keg-stat-info">
            <div class="ult-keg-stat-label">Completed</div>
            <div class="ult-keg-stat-value"><?= number_format($statCompleted) ?></div>
            <div class="ult-keg-stat-sub"><i class="bi bi-trophy-fill"></i> Selesai</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Agenda Kegiatan</h1>
        <p class="page-subtitle">Kalender event & kegiatan kampus</p>
    </div>
    <div class="ult-keg-header-actions">
        <div class="ult-keg-view-toggle" id="ultKegViewToggle">
            <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
            </button>
            <button type="button" class="<?= $currentView === 'grid' ? 'active' : '' ?>" data-view="grid" title="Tampilan Timeline">
                <i class="bi bi-diagram-3-fill"></i> <span class="d-none-mobile">Timeline</span>
            </button>
        </div>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-keg-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-calendar-event-fill"></i> Daftar Kegiatan</h3>
                    <span class="pgn-result-info">
                        Total <strong><?= number_format($statTotal) ?></strong> kegiatan terdaftar
                    </span>
                </div>
            </div>
        </div>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultKegTableView" class="ult-keg-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead><tr><th>Tanggal & Waktu</th><th>Judul Kegiatan</th><th>Lokasi</th><th>Unit</th><th>Status</th><th style="width:140px;text-align:right;">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($kegiatan as $k): 
                    [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $statusMap[$k['status']] ?? ['badge-info', $k['status'], 'bi-circle-fill', '#64748b'];
                    $dateObj = new DateTime($k['start_datetime']);
                ?>
                    <tr>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:.125rem;">
                                <span style="font-weight:700;color:var(--admin-text);font-size:.875rem;"><?= $dateObj->format('d M Y') ?></span>
                                <span style="font-size:.75rem;color:var(--admin-text-muted);display:flex;align-items:center;gap:.25rem;"><i class="bi bi-clock-fill" style="font-size:.65rem;"></i> <?= $dateObj->format('H:i') ?> WITA</span>
                            </div>
                        </td>
                        <td>
                            <div class="ult-keg-cell">
                                <div class="ult-keg-date-box">
                                    <div class="ult-keg-date-day"><?= $dateObj->format('d') ?></div>
                                    <div class="ult-keg-date-month"><?= $dateObj->format('M') ?></div>
                                </div>
                                <div class="ult-keg-info">
                                    <div class="ult-keg-title"><?= htmlspecialchars($k['title']) ?></div>
                                    <?php if (!empty($k['description'])): ?>
                                    <div style="font-size:.75rem;color:var(--admin-text-muted);margin-top:.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars(mb_substr($k['description'], 0, 60)) ?><?= strlen($k['description']) > 60 ? '...' : '' ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($k['location'])): ?>
                            <span class="ult-keg-location"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($k['location']) ?></span>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:.85rem;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($k['dept_name'])): ?>
                            <span class="ult-keg-dept"><i class="bi bi-building-fill"></i> <?= htmlspecialchars($k['dept_name']) ?></span>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:.85rem;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="ult-keg-status" style="background:<?= $badgeColor ?>20;color:<?= $badgeColor ?>;">
                                <i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                            </span>
                        </td>
                        <td>
                            <div class="ult-keg-row-actions">
                                <div class="ult-keg-dropdown" data-dropdown>
                                    <button type="button" class="ult-keg-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="ult-keg-dropdown-menu right ult-keg-row-menu">
                                        <div class="ult-keg-dd-header">Kegiatan</div>
                                        <a href="#" class="ult-keg-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Detail kegiatan segera hadir','info');return false;">
                                            <i class="bi bi-eye-fill"></i><span>Lihat Detail</span>
                                        </a>
                                        <a href="#" class="ult-keg-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Edit kegiatan segera hadir','info');return false;">
                                            <i class="bi bi-pencil-square"></i><span>Edit Kegiatan</span>
                                        </a>
                                        <div class="ult-keg-dd-divider"></div>
                                        <div class="ult-keg-dd-header">Status</div>
                                        <a href="#" class="ult-keg-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Ubah status segera hadir','info');return false;">
                                            <i class="bi bi-play-circle-fill"></i><span>Mulai Kegiatan</span>
                                        </a>
                                        <a href="#" class="ult-keg-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Tandai selesai segera hadir','info');return false;">
                                            <i class="bi bi-check-circle-fill"></i><span>Tandai Selesai</span>
                                        </a>
                                        <div class="ult-keg-dd-divider"></div>
                                        <div class="ult-keg-dd-header">Dokumen</div>
                                        <a href="#" class="ult-keg-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Cetak agenda segera hadir','info');return false;">
                                            <i class="bi bi-printer-fill"></i><span>Cetak Agenda</span>
                                        </a>
                                        <a href="#" class="ult-keg-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Export PDF segera hadir','info');return false;">
                                            <i class="bi bi-file-earmark-pdf-fill"></i><span>Export PDF</span>
                                        </a>
                                        <div class="ult-keg-dd-divider"></div>
                                        <a href="<?= url('admin/kegiatan/hapus/' . $k['id']) ?>" class="ult-keg-dd-item danger" onclick="return confirm('Hapus kegiatan <?= htmlspecialchars(addslashes($k['title'])) ?>?');">
                                            <i class="bi bi-trash3-fill"></i><span>Hapus Kegiatan</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$kegiatan): ?><tr><td colspan="6">
                    <div class="ult-keg-empty">
                        <div class="ult-keg-empty-icon"><i class="bi bi-calendar-x"></i></div>
                        <h3>Belum Ada Agenda</h3>
                        <p>Belum ada kegiatan yang dijadwalkan. Mulai tambahkan agenda untuk mengatur event kampus dengan lebih terorganisir.</p>
                    </div>
                </td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== GRID VIEW (Timeline) ===== -->
        <div id="ultKegGridView" class="ult-keg-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php if ($kegiatan): ?>
            <div class="ult-keg-timeline">
                <?php foreach ($kegiatan as $k): 
                    [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $statusMap[$k['status']] ?? ['badge-info', $k['status'], 'bi-circle-fill', '#64748b'];
                    $dateObj = new DateTime($k['start_datetime']);
                ?>
                <div class="ult-keg-timeline-item">
                    <div class="ult-keg-timeline-dot" style="border-color:<?= $badgeColor ?>;color:<?= $badgeColor ?>;">
                        <i class="bi <?= $badgeIcon ?>"></i>
                    </div>
                    <div class="ult-keg-timeline-content">
                        <div class="ult-keg-timeline-header">
                            <div class="ult-keg-timeline-title"><?= htmlspecialchars($k['title']) ?></div>
                            <span class="ult-keg-status" style="background:<?= $badgeColor ?>20;color:<?= $badgeColor ?>;font-size:.7rem;">
                                <i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                            </span>
                        </div>
                        <?php if (!empty($k['description'])): ?>
                        <div style="font-size:.85rem;color:var(--admin-text-secondary);line-height:1.6;margin-bottom:.75rem;"><?= htmlspecialchars(mb_substr($k['description'], 0, 150)) ?><?= strlen($k['description']) > 150 ? '...' : '' ?></div>
                        <?php endif; ?>
                        <div class="ult-keg-timeline-meta">
                            <span><i class="bi bi-calendar-event-fill"></i> <?= $dateObj->format('d F Y') ?></span>
                            <span><i class="bi bi-clock-fill"></i> <?= $dateObj->format('H:i') ?> WITA</span>
                            <?php if (!empty($k['location'])): ?><span><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($k['location']) ?></span><?php endif; ?>
                            <?php if (!empty($k['dept_name'])): ?><span><i class="bi bi-building-fill"></i> <?= htmlspecialchars($k['dept_name']) ?></span><?php endif; ?>
                        </div>
                        <div class="ult-keg-timeline-actions">
                            <a href="#" class="ult-keg-timeline-btn" onclick="if(typeof showToast!=='undefined')showToast('Detail segera hadir','info');return false;"><i class="bi bi-eye-fill"></i> Detail</a>
                            <a href="#" class="ult-keg-timeline-btn" onclick="if(typeof showToast!=='undefined')showToast('Edit segera hadir','info');return false;"><i class="bi bi-pencil-fill"></i> Edit</a>
                            <a href="<?= url('admin/kegiatan/hapus/' . $k['id']) ?>" class="ult-keg-timeline-btn danger" onclick="return confirm('Hapus kegiatan ini?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="ult-keg-empty">
                <div class="ult-keg-empty-icon"><i class="bi bi-calendar-x"></i></div>
                <h3>Belum Ada Agenda</h3>
                <p>Belum ada kegiatan yang dijadwalkan. Mulai tambahkan agenda untuk mengatur event kampus.</p>
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
    document.querySelectorAll('.ult-keg-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-keg-dropdown-trigger, .ult-keg-row-btn');
        const menu = dd.querySelector('.ult-keg-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-keg-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
        });
        menu.querySelectorAll('.ult-keg-dd-item[data-value]').forEach(function(item){
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
                menu.querySelectorAll('.ult-keg-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' && val !== '0' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
            });
        });
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-keg-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultKegViewToggle');
    const tv = document.getElementById('ultKegTableView');
    const gv = document.getElementById('ultKegGridView');
    if(vt){
        vt.querySelectorAll('button[data-view]').forEach(btn => {
            btn.addEventListener('click', function(){
                const v = this.getAttribute('data-view');
                vt.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                if(tv && gv){
                    if(v === 'grid'){ tv.style.display = 'none'; gv.style.display = 'block'; }
                    else { tv.style.display = ''; gv.style.display = 'none'; }
                }
                try { localStorage.setItem('ult_keg_view', v); } catch(e){}
            });
        });
    }
})();
</script>