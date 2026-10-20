<?php
// ===== STATS =====
try {
    $db = \App\Helpers\Database::getInstance();
    $statTotal      = count($maintenances);
    $statRutin      = count(array_filter($maintenances, fn($m) => $m['maintenance_type'] === 'rutin'));
    $statPerbaikan  = count(array_filter($maintenances, fn($m) => $m['maintenance_type'] === 'perbaikan'));
    $statTotalCost  = array_sum(array_column($maintenances, 'cost'));
} catch (\Throwable $e) {
    $statTotal = $statRutin = $statPerbaikan = $statTotalCost = 0;
}

$typeMap = [
    'rutin'            => ['badge-info', 'Rutin', 'bi-arrow-repeat', '#3b82f6'],
    'perbaikan'        => ['badge-warning', 'Perbaikan', 'bi-tools', '#f59e0b'],
    'penggantian_part' => ['badge-primary', 'Ganti Part', 'bi-gear-wide-connected', '#8b5cf6'],
    'kalibrasi'        => ['badge-success', 'Kalibrasi', 'bi-sliders', '#10b981'],
];

$currentView = $_GET['view'] ?? 'table';
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-maint-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-maint-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-maint-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#3b82f6);}
.ult-maint-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-maint-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-maint-stat-info{flex:1;min-width:0;}
.ult-maint-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-maint-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-maint-stat-value.money{font-size:1.15rem;}
.ult-maint-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-maint-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-maint-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== VIEW TOGGLE ===== */
.ult-maint-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-maint-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-maint-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-maint-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-maint-table-wrap{overflow-x:auto;}
.ult-maint-cell{display:flex;align-items:center;gap:.75rem;min-width:200px;}
.ult-maint-asset-icon{width:44px;height:44px;border-radius:var(--radius-md);background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.ult-maint-asset-info{flex:1;min-width:0;}
.ult-maint-asset-code{font-family:monospace;font-size:.78rem;font-weight:700;color:var(--admin-primary);background:rgba(139,92,246,.08);padding:.125rem .5rem;border-radius:4px;display:inline-block;margin-bottom:.125rem;}
.ult-maint-asset-name{font-weight:700;color:var(--admin-text);font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-maint-date{display:flex;flex-direction:column;gap:.125rem;}
.ult-maint-date-main{font-weight:600;font-size:.875rem;color:var(--admin-text);}
.ult-maint-date-sub{font-size:.7rem;color:var(--admin-text-muted);display:flex;align-items:center;gap:.25rem;}
.ult-maint-type{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
.ult-maint-cost{font-weight:700;color:var(--admin-success);font-size:.9rem;}
.ult-maint-desc{font-size:.825rem;color:var(--admin-text-secondary);line-height:1.5;}
.ult-maint-officer{display:inline-flex;align-items:center;gap:.5rem;padding:.375rem .75rem;background:var(--admin-bg);border-radius:50px;font-size:.8rem;font-weight:600;color:var(--admin-text);}
.ult-maint-officer-avatar{width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,var(--admin-primary),var(--admin-primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;}

/* ===== TIMELINE VIEW ===== */
.ult-maint-timeline{padding:1.5rem;}
.ult-maint-timeline-wrap{position:relative;padding-left:2rem;}
.ult-maint-timeline-wrap::before{content:'';position:absolute;left:19px;top:0;bottom:0;width:2px;background:linear-gradient(180deg,var(--admin-primary),var(--admin-primary-light));}
.ult-maint-timeline-item{position:relative;margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px dashed var(--admin-border);}
.ult-maint-timeline-item:last-child{margin-bottom:0;padding-bottom:0;border-bottom:none;}
.ult-maint-timeline-dot{position:absolute;left:-2rem;top:0;width:40px;height:40px;border-radius:50%;background:var(--admin-surface);border:3px solid var(--admin-primary);display:flex;align-items:center;justify-content:center;font-size:1rem;color:var(--admin-primary);z-index:2;}
.ult-maint-timeline-content{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);padding:1.25rem;transition:all .3s;}
.ult-maint-timeline-content:hover{box-shadow:0 8px 25px rgba(0,0,0,.08);border-color:var(--admin-primary);}
.ult-maint-timeline-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:.75rem;flex-wrap:wrap;}
.ult-maint-timeline-title{font-size:1.05rem;font-weight:700;color:var(--admin-text);line-height:1.4;}
.ult-maint-timeline-meta{display:flex;gap:1rem;margin-top:.75rem;flex-wrap:wrap;}
.ult-maint-timeline-meta span{display:flex;align-items:center;gap:.375rem;font-size:.8rem;color:var(--admin-text-secondary);}
.ult-maint-timeline-meta i{font-size:.75rem;color:var(--admin-primary);}

/* ===== EMPTY STATE ===== */
.ult-maint-empty{text-align:center;padding:4rem 2rem;}
.ult-maint-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#8b5cf6;display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-maint-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(139,92,246,.3);border-radius:50%;animation:ult-maint-spin 20s linear infinite;}
@keyframes ult-maint-spin{to{transform:rotate(360deg);}}
.ult-maint-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-maint-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-maint-stats{grid-template-columns:repeat(2,1fr);}
    .ult-maint-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-maint-stats{grid-template-columns:1fr;}
    .ult-maint-timeline{padding:1rem;}
    .ult-maint-timeline-wrap{padding-left:1.5rem;}
    .ult-maint-timeline-wrap::before{left:14px;}
    .ult-maint-timeline-dot{left:-1.5rem;width:32px;height:32px;font-size:.85rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-maint-stats">
    <div class="ult-maint-stat" style="--ult-color:#8b5cf6;">
        <div class="ult-maint-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-tools"></i></div>
        <div class="ult-maint-stat-info">
            <div class="ult-maint-stat-label">Total Pemeliharaan</div>
            <div class="ult-maint-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-maint-stat-sub"><i class="bi bi-database-fill"></i> Keseluruhan</div>
        </div>
    </div>
    <div class="ult-maint-stat" style="--ult-color:#3b82f6;">
        <div class="ult-maint-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-arrow-repeat"></i></div>
        <div class="ult-maint-stat-info">
            <div class="ult-maint-stat-label">Pemeliharaan Rutin</div>
            <div class="ult-maint-stat-value"><?= number_format($statRutin) ?></div>
            <div class="ult-maint-stat-sub"><i class="bi bi-clock-history"></i> Terjadwal</div>
        </div>
    </div>
    <div class="ult-maint-stat" style="--ult-color:#f59e0b;">
        <div class="ult-maint-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="bi bi-wrench-adjustable"></i></div>
        <div class="ult-maint-stat-info">
            <div class="ult-maint-stat-label">Perbaikan</div>
            <div class="ult-maint-stat-value"><?= number_format($statPerbaikan) ?></div>
            <div class="ult-maint-stat-sub"><i class="bi bi-exclamation-triangle-fill"></i> Kerusakan</div>
        </div>
    </div>
    <div class="ult-maint-stat" style="--ult-color:#10b981;">
        <div class="ult-maint-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-cash-stack"></i></div>
        <div class="ult-maint-stat-info">
            <div class="ult-maint-stat-label">Total Biaya</div>
            <div class="ult-maint-stat-value money">Rp <?= number_format($statTotalCost, 0, ',', '.') ?></div>
            <div class="ult-maint-stat-sub"><i class="bi bi-graph-up"></i> Semua jenis</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Pemeliharaan Aset</h1>
        <p class="page-subtitle">Riwayat perawatan inventaris</p>
    </div>
    <div class="ult-maint-header-actions">
        <div class="ult-maint-view-toggle" id="ultMaintViewToggle">
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
            <div class="ult-maint-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-tools"></i> Riwayat Pemeliharaan</h3>
                    <span class="pgn-result-info">
                        Total <strong><?= number_format($statTotal) ?></strong> catatan pemeliharaan
                    </span>
                </div>
            </div>
        </div>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultMaintTableView" class="ult-maint-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead><tr><th>Tanggal</th><th style="min-width:200px;">Aset</th><th>Jenis</th><th>Deskripsi</th><th>Biaya</th><th>Petugas</th></tr></thead>
                <tbody>
                <?php foreach ($maintenances as $m): 
                    [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $typeMap[$m['maintenance_type']] ?? ['badge-info', $m['maintenance_type'], 'bi-tools', '#64748b'];
                ?>
                    <tr>
                        <td>
                            <div class="ult-maint-date">
                                <span class="ult-maint-date-main"><?= date('d M Y', strtotime($m['maintenance_date'])) ?></span>
                                <span class="ult-maint-date-sub"><i class="bi bi-clock"></i> <?= date('Y', strtotime($m['maintenance_date'])) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="ult-maint-cell">
                                <div class="ult-maint-asset-icon"><i class="bi bi-box-seam-fill"></i></div>
                                <div class="ult-maint-asset-info">
                                    <div class="ult-maint-asset-code"><?= htmlspecialchars($m['asset_code'] ?? '-') ?></div>
                                    <div class="ult-maint-asset-name"><?= htmlspecialchars($m['asset_name'] ?? '-') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="ult-maint-type" style="background:<?= $badgeColor ?>20;color:<?= $badgeColor ?>;">
                                <i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                            </span>
                        </td>
                        <td class="ult-maint-desc"><?= htmlspecialchars(mb_substr($m['description'], 0, 60)) ?><?= strlen($m['description']) > 60 ? '...' : '' ?></td>
                        <td>
                            <span class="ult-maint-cost">Rp <?= number_format((float)$m['cost'], 0, ',', '.') ?></span>
                        </td>
                        <td>
                            <?php if (!empty($m['performed_by'])): ?>
                            <span class="ult-maint-officer">
                                <span class="ult-maint-officer-avatar"><?= strtoupper(substr($m['performed_by'], 0, 1)) ?></span>
                                <?= htmlspecialchars($m['performed_by']) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:.85rem;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$maintenances): ?><tr><td colspan="6">
                    <div class="ult-maint-empty">
                        <div class="ult-maint-empty-icon"><i class="bi bi-tools"></i></div>
                        <h3>Belum Ada Riwayat Pemeliharaan</h3>
                        <p>Belum ada catatan pemeliharaan aset. Mulai catat perawatan untuk menjaga kondisi inventaris kampus.</p>
                    </div>
                </td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== TIMELINE VIEW ===== -->
        <div id="ultMaintGridView" class="ult-maint-timeline" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php if ($maintenances): ?>
            <div class="ult-maint-timeline-wrap">
                <?php foreach ($maintenances as $m): 
                    [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $typeMap[$m['maintenance_type']] ?? ['badge-info', $m['maintenance_type'], 'bi-tools', '#64748b'];
                ?>
                <div class="ult-maint-timeline-item">
                    <div class="ult-maint-timeline-dot" style="border-color:<?= $badgeColor ?>;color:<?= $badgeColor ?>;">
                        <i class="bi <?= $badgeIcon ?>"></i>
                    </div>
                    <div class="ult-maint-timeline-content">
                        <div class="ult-maint-timeline-header">
                            <div>
                                <div class="ult-maint-asset-code" style="margin-bottom:.25rem;"><?= htmlspecialchars($m['asset_code'] ?? '-') ?></div>
                                <div class="ult-maint-timeline-title"><?= htmlspecialchars($m['asset_name'] ?? '-') ?></div>
                            </div>
                            <span class="ult-maint-type" style="background:<?= $badgeColor ?>20;color:<?= $badgeColor ?>;font-size:.7rem;">
                                <i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                            </span>
                        </div>
                        <div style="font-size:.875rem;color:var(--admin-text-secondary);line-height:1.6;margin-bottom:.75rem;"><?= htmlspecialchars($m['description']) ?></div>
                        <div class="ult-maint-timeline-meta">
                            <span><i class="bi bi-calendar-event-fill"></i> <?= date('d F Y', strtotime($m['maintenance_date'])) ?></span>
                            <?php if ((float)$m['cost'] > 0): ?><span><i class="bi bi-cash-stack"></i> Rp <?= number_format((float)$m['cost'], 0, ',', '.') ?></span><?php endif; ?>
                            <?php if (!empty($m['performed_by'])): ?><span><i class="bi bi-person-fill"></i> <?= htmlspecialchars($m['performed_by']) ?></span><?php endif; ?>
                            <?php if (!empty($m['next_maintenance_date'])): ?><span><i class="bi bi-calendar-check-fill"></i> Berikutnya: <?= date('d M Y', strtotime($m['next_maintenance_date'])) ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="ult-maint-empty">
                <div class="ult-maint-empty-icon"><i class="bi bi-tools"></i></div>
                <h3>Belum Ada Riwayat Pemeliharaan</h3>
                <p>Belum ada catatan pemeliharaan aset.</p>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function(){
    'use strict';
    const vt = document.getElementById('ultMaintViewToggle');
    const tv = document.getElementById('ultMaintTableView');
    const gv = document.getElementById('ultMaintGridView');
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
                try { localStorage.setItem('ult_maint_view', v); } catch(e){}
            });
        });
    }
})();
</script>