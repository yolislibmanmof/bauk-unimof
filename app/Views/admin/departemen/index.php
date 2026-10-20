<?php
use App\Helpers\Auth;
$canDelete = Auth::hasRole(['super_admin', 'admin_bauk']);

// Pastikan $pagination selalu ada (default jika belum diset dari route)
if (!isset($pagination)) {
    $pagination = ['page' => 1, 'totalPages' => 1, 'total' => 0];
}

$query = function (array $override = []) use ($pagination) {
    $params = array_merge(['page' => $pagination['page'] ?? 1], $override);
    $params = array_filter($params, static fn($v) => $v !== '' && $v !== null && $v !== 0);
    return url('admin/departemen') . ($params ? '?' . http_build_query($params) : '');
};
$statusMap = [
    'active'   => ['badge-success', 'Aktif', 'bi-check-circle-fill', '#10b981'],
    'inactive' => ['badge-warning', 'Nonaktif', 'bi-pause-circle-fill', '#f59e0b'],
];

// ===== STATS =====
$db = \App\Helpers\Database::getInstance();
try {
    $statTotal    = count($departemen);
    $statActive   = count(array_filter($departemen, fn($d) => $d['status'] === 'active'));
    $statInactive = count(array_filter($departemen, fn($d) => $d['status'] === 'inactive'));
    $totalPegawai = array_sum(array_column($departemen, 'pegawai_count'));
    $avgPegawai   = $statTotal > 0 ? round($totalPegawai / $statTotal, 1) : 0;
} catch (\Throwable $e) {
    $statTotal = $statActive = $statInactive = $totalPegawai = $avgPegawai = 0;
}

$currentView = $_GET['view'] ?? 'table';
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-dept-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-dept-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-dept-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#3b82f6);}
.ult-dept-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-dept-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-dept-stat-info{flex:1;min-width:0;}
.ult-dept-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-dept-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-dept-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-dept-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-dept-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-dept-dropdown{position:relative;display:inline-block;}
.ult-dept-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:150px;justify-content:space-between;}
.ult-dept-dropdown-trigger:hover{border-color:var(--admin-primary);background:var(--admin-bg);}
.ult-dept-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.05);}
.ult-dept-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-dept-dropdown.open .ult-dept-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-dept-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:300px;overflow-y:auto;}
.ult-dept-dropdown.open .ult-dept-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-dept-dropdown-menu.right{left:auto;right:0;}
.ult-dept-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-dept-dd-item:last-child{border-bottom:none;}
.ult-dept-dd-item:hover{background:var(--admin-bg);}
.ult-dept-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-dept-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-dept-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-dept-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-dept-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== VIEW TOGGLE ===== */
.ult-dept-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-dept-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-dept-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-dept-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-dept-table-wrap{overflow-x:auto;}
.ult-dept-cell{display:flex;align-items:center;gap:.875rem;min-width:240px;}
.ult-dept-icon{width:48px;height:48px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:#fff;flex-shrink:0;}
.ult-dept-info{flex:1;min-width:0;}
.ult-dept-name{font-weight:700;color:var(--admin-text);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-dept-desc{font-size:.75rem;color:var(--admin-text-muted);margin-top:.125rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-dept-code{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:6px;font-size:.78rem;font-weight:700;color:var(--admin-primary);font-family:monospace;}
.ult-dept-head{display:flex;align-items:center;gap:.5rem;}
.ult-dept-head-avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--admin-primary),var(--admin-primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;flex-shrink:0;}
.ult-dept-head-name{font-size:.85rem;font-weight:600;color:var(--admin-text);}
.ult-dept-head-role{font-size:.7rem;color:var(--admin-text-muted);}
.ult-dept-pegawai{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:rgba(26,82,118,.08);border-radius:50px;font-size:.8rem;font-weight:700;color:var(--admin-primary);}
.ult-dept-pegawai i{font-size:.7rem;}
.ult-dept-status{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}

/* ===== ROW ACTION ===== */
.ult-dept-row-actions{display:flex;gap:.25rem;justify-content:flex-end;}
.ult-dept-row-btn{width:32px;height:32px;border-radius:8px;background:transparent;border:1px solid transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:var(--admin-text-secondary);transition:all .2s;}
.ult-dept-row-btn:hover{background:var(--admin-bg);border-color:var(--admin-border);color:var(--admin-text);}
.ult-dept-row-btn.view:hover{color:var(--admin-primary);background:rgba(26,82,118,.08);}
.ult-dept-row-btn.edit:hover{color:#f59e0b;background:rgba(245,158,11,.08);}
.ult-dept-row-btn.delete:hover{color:var(--admin-danger);background:rgba(239,68,68,.08);}
.ult-dept-row-menu{min-width:220px;}
.ult-dept-row-menu .ult-dept-dd-item{padding:.5rem 1rem;font-size:.825rem;}
.ult-dept-row-menu .ult-dept-dd-item i:first-child{width:20px;color:var(--admin-text-secondary);}
.ult-dept-row-menu .ult-dept-dd-item.danger{color:var(--admin-danger);}
.ult-dept-row-menu .ult-dept-dd-item.danger:hover{background:rgba(239,68,68,.08);}

/* ===== GRID VIEW ===== */
.ult-dept-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.25rem;padding:1.5rem;}
.ult-dept-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;transition:all .3s;position:relative;}
.ult-dept-card:hover{transform:translateY(-4px);box-shadow:0 12px 35px rgba(0,0,0,.1);border-color:var(--admin-primary);}
.ult-dept-card-accent{height:6px;background:linear-gradient(90deg,var(--admin-primary),var(--admin-primary-light));}
.ult-dept-card-body{padding:1.5rem;}
.ult-dept-card-header{display:flex;align-items:center;gap:1rem;margin-bottom:1rem;}
.ult-dept-card-icon{width:56px;height:56px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;}
.ult-dept-card-title{flex:1;min-width:0;}
.ult-dept-card-title h4{font-size:1.05rem;font-weight:700;margin:0 0 .25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ult-dept-card-title code{font-size:.75rem;font-weight:700;color:var(--admin-primary);background:rgba(26,82,118,.08);padding:.125rem .5rem;border-radius:4px;}
.ult-dept-card-desc{font-size:.85rem;color:var(--admin-text-secondary);line-height:1.6;margin-bottom:1.25rem;min-height:48px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.ult-dept-card-meta{display:flex;flex-direction:column;gap:.625rem;padding-top:1rem;border-top:1px solid var(--admin-border);}
.ult-dept-card-row{display:flex;justify-content:space-between;align-items:center;font-size:.825rem;}
.ult-dept-card-row span:first-child{color:var(--admin-text-muted);font-weight:600;display:flex;align-items:center;gap:.375rem;}
.ult-dept-card-row span:last-child{color:var(--admin-text);font-weight:600;}
.ult-dept-card-footer{display:flex;gap:.5rem;padding:1rem 1.5rem;border-top:1px solid var(--admin-border);background:var(--admin-bg);}
.ult-dept-card-btn{flex:1;padding:.5rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:6px;font-size:.8rem;font-weight:600;color:var(--admin-text);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.375rem;transition:all .2s;text-decoration:none!important;}
.ult-dept-card-btn:hover{background:var(--admin-primary);color:#fff!important;border-color:var(--admin-primary);}
.ult-dept-card-btn.danger:hover{background:var(--admin-danger);border-color:var(--admin-danger);}
.ult-dept-card-actions{position:absolute;top:1rem;right:1rem;}

/* ===== EMPTY STATE ===== */
.ult-dept-empty{text-align:center;padding:4rem 2rem;}
.ult-dept-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:var(--admin-primary);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-dept-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(26,82,118,.3);border-radius:50%;animation:ult-dept-spin 20s linear infinite;}
@keyframes ult-dept-spin{to{transform:rotate(360deg);}}
.ult-dept-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-dept-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-dept-stats{grid-template-columns:repeat(2,1fr);}
    .ult-dept-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-dept-stats{grid-template-columns:1fr;}
    .ult-dept-grid{grid-template-columns:1fr;padding:1rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-dept-stats">
    <div class="ult-dept-stat" style="--ult-color:#3b82f6;">
        <div class="ult-dept-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-buildings-fill"></i></div>
        <div class="ult-dept-stat-info">
            <div class="ult-dept-stat-label">Total Departemen</div>
            <div class="ult-dept-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-dept-stat-sub"><i class="bi bi-database-fill"></i> Unit kerja terdaftar</div>
        </div>
    </div>
    <div class="ult-dept-stat" style="--ult-color:#10b981;">
        <div class="ult-dept-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-check-circle-fill"></i></div>
        <div class="ult-dept-stat-info">
            <div class="ult-dept-stat-label">Departemen Aktif</div>
            <div class="ult-dept-stat-value"><?= number_format($statActive) ?></div>
            <div class="ult-dept-stat-sub"><i class="bi bi-arrow-up-short"></i> <?= $statTotal > 0 ? round(($statActive / $statTotal) * 100) : 0 ?>% dari total</div>
        </div>
    </div>
    <div class="ult-dept-stat" style="--ult-color:#8b5cf6;">
        <div class="ult-dept-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-people-fill"></i></div>
        <div class="ult-dept-stat-info">
            <div class="ult-dept-stat-label">Total Pegawai</div>
            <div class="ult-dept-stat-value"><?= number_format($totalPegawai) ?></div>
            <div class="ult-dept-stat-sub"><i class="bi bi-person-badge"></i> Tersebar di semua unit</div>
        </div>
    </div>
    <div class="ult-dept-stat" style="--ult-color:#f59e0b;">
        <div class="ult-dept-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="bi bi-bar-chart-fill"></i></div>
        <div class="ult-dept-stat-info">
            <div class="ult-dept-stat-label">Rata-rata Pegawai</div>
            <div class="ult-dept-stat-value"><?= number_format($avgPegawai, 1) ?></div>
            <div class="ult-dept-stat-sub"><i class="bi bi-graph-up"></i> Per departemen</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Departemen / Unit Kerja</h1>
        <p class="page-subtitle">Kelola struktur organisasi kampus</p>
    </div>
    <div class="ult-dept-header-actions">
        <div class="ult-dept-view-toggle" id="ultDeptViewToggle">
            <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
            </button>
            <button type="button" class="<?= $currentView === 'grid' ? 'active' : '' ?>" data-view="grid" title="Tampilan Kartu">
                <i class="bi bi-grid-3x3-gap"></i> <span class="d-none-mobile">Kartu</span>
            </button>
        </div>
        <a href="<?= url('admin/departemen/tambah') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Departemen</a>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-dept-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-buildings-fill"></i> Daftar Departemen</h3>
                    <span class="pgn-result-info">
                        Total <strong><?= number_format($statTotal) ?></strong> departemen terdaftar
                    </span>
                </div>
            </div>
        </div>
        
        <!-- ===== TABLE VIEW ===== -->
        <div id="ultDeptTableView" class="ult-dept-table-wrap" style="<?= $currentView === 'grid' ? 'display:none;' : '' ?>">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th style="min-width:240px;">Nama Unit</th>
                        <th>Kepala Unit</th>
                        <th>Pegawai</th>
                        <th>Status</th>
                        <th style="width:140px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($departemen as $d):
                    [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $statusMap[$d['status']] ?? ['badge-info', $d['status'], 'bi-circle-fill', '#64748b'];
                    $deptColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];
                    $colorIdx = array_search($d['id'], array_column($departemen, 'id')) % count($deptColors);
                    $deptColor = $deptColors[$colorIdx];
                ?>
                    <tr>
                        <td><span class="ult-dept-code"><i class="bi bi-hash"></i> <?= htmlspecialchars($d['code']) ?></span></td>
                        <td>
                            <div class="ult-dept-cell">
                                <div class="ult-dept-icon" style="background:linear-gradient(135deg,<?= $deptColor ?>,<?= $deptColor ?>cc);">
                                    <i class="bi bi-building-fill"></i>
                                </div>
                                <div class="ult-dept-info">
                                    <div class="ult-dept-name"><?= htmlspecialchars($d['name']) ?></div>
                                    <div class="ult-dept-desc"><?= htmlspecialchars($d['description'] ?? 'Tidak ada deskripsi') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($d['head_name'])): ?>
                            <div class="ult-dept-head">
                                <div class="ult-dept-head-avatar"><?= strtoupper(substr($d['head_name'], 0, 1)) ?></div>
                                <div>
                                    <div class="ult-dept-head-name"><?= htmlspecialchars($d['head_name']) ?></div>
                                    <div class="ult-dept-head-role">Kepala Unit</div>
                                </div>
                            </div>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:.85rem;"><i class="bi bi-person-slash"></i> Belum ada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="ult-dept-pegawai">
                                <i class="bi bi-people-fill"></i> <?= (int)$d['pegawai_count'] ?> orang
                            </span>
                        </td>
                        <td>
                            <span class="ult-dept-status" style="background:<?= $badgeColor ?>20;color:<?= $badgeColor ?>;">
                                <i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                            </span>
                        </td>
                        <td>
                            <div class="ult-dept-row-actions">
                                <a href="<?= url('admin/departemen/hapus/' . $d['id']) ?>" class="ult-dept-row-btn delete" title="Hapus"
                                   onclick="return confirm('Hapus departemen <?= htmlspecialchars(addslashes($d['name'])) ?>? Data pegawai tidak akan terhapus.');"><i class="bi bi-trash3-fill"></i></a>
                                
                                <div class="ult-dept-dropdown" data-dropdown>
                                    <button type="button" class="ult-dept-row-btn" title="Aksi Lainnya"><i class="bi bi-three-dots-vertical"></i></button>
                                    <div class="ult-dept-dropdown-menu right ult-dept-row-menu">
                                        <div class="ult-dept-dd-header">Departemen</div>
                                        <a href="#" class="ult-dept-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Detail departemen segera hadir','info');return false;">
                                            <i class="bi bi-eye-fill"></i><span>Lihat Detail</span>
                                        </a>
                                        <a href="#" class="ult-dept-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Edit departemen segera hadir','info');return false;">
                                            <i class="bi bi-pencil-square"></i><span>Edit Data</span>
                                        </a>
                                        <div class="ult-dept-dd-divider"></div>
                                        <div class="ult-dept-dd-header">Kepegawaian</div>
                                        <a href="<?= url('admin/pegawai') ?>?dept=<?= $d['id'] ?>" class="ult-dept-dd-item">
                                            <i class="bi bi-people-fill"></i><span>Lihat Pegawai (<?= (int)$d['pegawai_count'] ?>)</span>
                                        </a>
                                        <a href="#" class="ult-dept-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Tambah pegawai segera hadir','info');return false;">
                                            <i class="bi bi-person-plus-fill"></i><span>Tambah Pegawai</span>
                                        </a>
                                        <div class="ult-dept-dd-divider"></div>
                                        <div class="ult-dept-dd-header">Administrasi</div>
                                        <a href="#" class="ult-dept-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Struktur organisasi segera hadir','info');return false;">
                                            <i class="bi bi-diagram-3-fill"></i><span>Struktur Organisasi</span>
                                        </a>
                                        <a href="#" class="ult-dept-dd-item" onclick="if(typeof showToast!=='undefined')showToast('Cetak profil unit segera hadir','info');return false;">
                                            <i class="bi bi-printer-fill"></i><span>Cetak Profil Unit</span>
                                        </a>
                                        <?php if ($canDelete): ?>
                                        <div class="ult-dept-dd-divider"></div>
                                        <a href="<?= url('admin/departemen/hapus/' . $d['id']) ?>" class="ult-dept-dd-item danger" onclick="return confirm('Hapus departemen <?= htmlspecialchars(addslashes($d['name'])) ?>?');">
                                            <i class="bi bi-trash3-fill"></i><span>Hapus Departemen</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$departemen): ?>
                    <tr><td colspan="6">
                        <div class="ult-dept-empty">
                            <div class="ult-dept-empty-icon"><i class="bi bi-building"></i></div>
                            <h3>Belum Ada Departemen</h3>
                            <p>Mulai tambahkan departemen atau unit kerja untuk membangun struktur organisasi kampus yang terorganisir.</p>
                            <a href="<?= url('admin/departemen/tambah') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Departemen Pertama</a>
                        </div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- ===== GRID VIEW ===== -->
        <div id="ultDeptGridView" class="ult-dept-grid" style="<?= $currentView === 'table' ? 'display:none;' : '' ?>">
            <?php foreach ($departemen as $d):
                [$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $statusMap[$d['status']] ?? ['badge-info', $d['status'], 'bi-circle-fill', '#64748b'];
                $deptColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];
                $colorIdx = array_search($d['id'], array_column($departemen, 'id')) % count($deptColors);
                $deptColor = $deptColors[$colorIdx];
            ?>
            <div class="ult-dept-card">
                <div class="ult-dept-card-accent" style="background:linear-gradient(90deg,<?= $deptColor ?>,<?= $deptColor ?>cc);"></div>
                <div class="ult-dept-card-actions">
                    <div class="ult-dept-dropdown" data-dropdown>
                        <button type="button" class="ult-dept-row-btn" title="Aksi" style="background:var(--admin-surface);"><i class="bi bi-three-dots-vertical"></i></button>
                        <div class="ult-dept-dropdown-menu right ult-dept-row-menu">
                            <a href="<?= url('admin/pegawai') ?>?dept=<?= $d['id'] ?>" class="ult-dept-dd-item"><i class="bi bi-people-fill"></i> Lihat Pegawai</a>
                            <?php if ($canDelete): ?>
                            <a href="<?= url('admin/departemen/hapus/' . $d['id']) ?>" class="ult-dept-dd-item danger" onclick="return confirm('Hapus?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="ult-dept-card-body">
                    <div class="ult-dept-card-header">
                        <div class="ult-dept-card-icon" style="background:linear-gradient(135deg,<?= $deptColor ?>,<?= $deptColor ?>cc);">
                            <i class="bi bi-building-fill"></i>
                        </div>
                        <div class="ult-dept-card-title">
                            <h4><?= htmlspecialchars($d['name']) ?></h4>
                            <code><?= htmlspecialchars($d['code']) ?></code>
                        </div>
                    </div>
                    
                    <div class="ult-dept-card-desc"><?= htmlspecialchars($d['description'] ?? 'Tidak ada deskripsi untuk departemen ini.') ?></div>
                    
                    <div class="ult-dept-card-meta">
                        <div class="ult-dept-card-row">
                            <span><i class="bi bi-person-badge-fill"></i> Kepala Unit</span>
                            <span><?= htmlspecialchars($d['head_name'] ?? 'Belum ada') ?></span>
                        </div>
                        <div class="ult-dept-card-row">
                            <span><i class="bi bi-people-fill"></i> Jumlah Pegawai</span>
                            <span class="ult-dept-pegawai" style="padding:.25rem .625rem;font-size:.75rem;"><i class="bi bi-people-fill"></i> <?= (int)$d['pegawai_count'] ?></span>
                        </div>
                        <div class="ult-dept-card-row">
                            <span><i class="bi bi-toggle-on"></i> Status</span>
                            <span class="ult-dept-status" style="background:<?= $badgeColor ?>20;color:<?= $badgeColor ?>;font-size:.7rem;">
                                <i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="ult-dept-card-footer">
                    <a href="<?= url('admin/pegawai') ?>?dept=<?= $d['id'] ?>" class="ult-dept-card-btn"><i class="bi bi-people-fill"></i> Pegawai</a>
                    <a href="<?= url('admin/departemen/hapus/' . $d['id']) ?>" class="ult-dept-card-btn danger" onclick="return confirm('Hapus departemen ini?');"><i class="bi bi-trash3-fill"></i> Hapus</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
    </div>
</div>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function(){
    'use strict';
    
    // ===== CUSTOM DROPDOWN =====
    document.querySelectorAll('.ult-dept-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-dept-dropdown-trigger, .ult-dept-row-btn');
        const menu = dd.querySelector('.ult-dept-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-dept-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
        });
        menu.querySelectorAll('.ult-dept-dd-item[data-value]').forEach(function(item){
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
                menu.querySelectorAll('.ult-dept-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' && val !== '0' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
            });
        });
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-dept-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultDeptViewToggle');
    const tv = document.getElementById('ultDeptTableView');
    const gv = document.getElementById('ultDeptGridView');
    if(vt){
        vt.querySelectorAll('button[data-view]').forEach(btn => {
            btn.addEventListener('click', function(){
                const v = this.getAttribute('data-view');
                vt.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                if(tv && gv){
                    if(v === 'grid'){ tv.style.display = 'none'; gv.style.display = 'grid'; }
                    else { tv.style.display = ''; gv.style.display = 'none'; }
                }
                try { localStorage.setItem('ult_dept_view', v); } catch(e){}
            });
        });
    }
})();
</script>