<?php
use App\Helpers\Auth;
$canEdit = Auth::hasRole(['super_admin', 'admin_bauk']);

// ===== GROUP MAP =====
$groupMap = [
    'general'   => ['label' => 'Umum', 'icon' => 'bi-gear-fill', 'color' => '#3b82f6'],
    'contact'   => ['label' => 'Kontak', 'icon' => 'bi-telephone-fill', 'color' => '#10b981'],
    'security'  => ['label' => 'Keamanan', 'icon' => 'bi-shield-lock-fill', 'color' => '#ef4444'],
    'email'     => ['label' => 'Email', 'icon' => 'bi-envelope-fill', 'color' => '#8b5cf6'],
    'social'    => ['label' => 'Sosial Media', 'icon' => 'bi-share-fill', 'color' => '#f59e0b'],
    'seo'       => ['label' => 'SEO & Meta', 'icon' => 'bi-search', 'color' => '#06b6d4'],
    'upload'    => ['label' => 'Upload', 'icon' => 'bi-cloud-upload-fill', 'color' => '#ec4899'],
    'system'    => ['label' => 'Sistem', 'icon' => 'bi-cpu-fill', 'color' => '#64748b'],
];

// ===== STATS =====
try {
    $statTotal = count($settings);
    $statGroups = count(array_unique(array_column($settings, 'group')));
    $statEditable = count(array_filter($settings, fn($s) => in_array($s['type'], ['text', 'number', 'boolean'])));
    $statSecurity = count(array_filter($settings, fn($s) => $s['group'] === 'security'));
} catch (\Throwable $e) {
    $statTotal = $statGroups = $statEditable = $statSecurity = 0;
}

// Group settings
$groupedSettings = [];
foreach ($settings as $s) {
    $groupedSettings[$s['group']][] = $s;
}

$currentView = $_GET['view'] ?? 'grouped';
$activeGroup = $_GET['group'] ?? 'all';
$searchQuery = $_GET['q'] ?? '';
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS CARDS ===== */
.ult-settings-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult-settings-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;display:flex;align-items:center;gap:1rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-settings-stat::before{content:'';position:absolute;top:0;left:0;width:4px;height:100%;background:var(--ult-color,#3b82f6);}
.ult-settings-stat:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-settings-stat-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-settings-stat-info{flex:1;min-width:0;}
.ult-settings-stat-label{font-size:.75rem;color:var(--admin-text-secondary);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:.25rem;}
.ult-settings-stat-value{font-size:1.6rem;font-weight:700;color:var(--admin-text);line-height:1;}
.ult-settings-stat-sub{font-size:.7rem;color:var(--admin-text-muted);margin-top:.25rem;display:flex;align-items:center;gap:.25rem;}

/* ===== HEADER ACTIONS ===== */
.ult-settings-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.ult-settings-header-actions{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}

/* ===== CUSTOM DROPDOWN ===== */
.ult-settings-dropdown{position:relative;display:inline-block;}
.ult-settings-dropdown-trigger{display:flex;align-items:center;gap:.5rem;padding:.625rem 1rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);cursor:pointer;font-size:.85rem;font-weight:600;color:var(--admin-text);transition:all .2s;min-width:160px;justify-content:space-between;}
.ult-settings-dropdown-trigger:hover{border-color:var(--admin-primary);background:var(--admin-bg);}
.ult-settings-dropdown-trigger.has-value{border-color:var(--admin-primary);background:rgba(26,82,118,.05);}
.ult-settings-dropdown-trigger i.chevron{font-size:.7rem;transition:transform .2s;color:var(--admin-text-muted);}
.ult-settings-dropdown.open .ult-settings-dropdown-trigger i.chevron{transform:rotate(180deg);}
.ult-settings-dropdown-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:100%;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-md);box-shadow:0 15px 40px rgba(0,0,0,.15);opacity:0;visibility:hidden;transform:translateY(-8px);transition:all .2s;z-index:100;max-height:300px;overflow-y:auto;}
.ult-settings-dropdown.open .ult-settings-dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
.ult-settings-dropdown-menu.right{left:auto;right:0;}
.ult-settings-dd-search{padding:.625rem;border-bottom:1px solid var(--admin-border);position:sticky;top:0;background:var(--admin-surface);}
.ult-settings-dd-search input{width:100%;padding:.5rem .75rem;border:1px solid var(--admin-border);border-radius:6px;font-size:.825rem;background:var(--admin-bg);}
.ult-settings-dd-search input:focus{outline:none;border-color:var(--admin-primary);}
.ult-settings-dd-item{display:flex;align-items:center;gap:.625rem;padding:.625rem 1rem;cursor:pointer;font-size:.85rem;color:var(--admin-text);transition:background .15s;border-bottom:1px solid rgba(0,0,0,.03);}
.ult-settings-dd-item:last-child{border-bottom:none;}
.ult-settings-dd-item:hover{background:var(--admin-bg);}
.ult-settings-dd-item.selected{background:rgba(26,82,118,.08);color:var(--admin-primary);font-weight:600;}
.ult-settings-dd-item i.bi-check-lg{margin-left:auto;color:var(--admin-primary);opacity:0;}
.ult-settings-dd-item.selected i.bi-check-lg{opacity:1;}
.ult-settings-dd-divider{height:1px;background:var(--admin-border);margin:.25rem 0;}
.ult-settings-dd-header{padding:.5rem 1rem;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:700;background:var(--admin-bg);}

/* ===== FILTER CHIPS ===== */
.ult-settings-chips{display:flex;flex-wrap:wrap;gap:.5rem;padding:.875rem 1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);align-items:center;}
.ult-settings-chip{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .75rem;background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:50px;font-size:.78rem;font-weight:600;color:var(--admin-text);}
.ult-settings-chip i{font-size:.7rem;}
.ult-settings-chip-close{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;padding:0;margin-left:.125rem;display:flex;align-items:center;}
.ult-settings-chip-close:hover{color:var(--admin-danger);}
.ult-settings-chips-label{font-size:.78rem;color:var(--admin-text-secondary);font-weight:600;}
.ult-settings-chips-clear{margin-left:auto;background:none;border:none;color:var(--admin-danger);font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:.25rem;}

/* ===== VIEW TOGGLE ===== */
.ult-settings-view-toggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:var(--radius-md);overflow:hidden;}
.ult-settings-view-toggle button{background:none;border:none;padding:.5rem .875rem;cursor:pointer;color:var(--admin-text-secondary);transition:all .2s;display:flex;align-items:center;gap:.375rem;font-size:.825rem;font-weight:600;}
.ult-settings-view-toggle button.active{background:var(--admin-primary);color:#fff;}
.ult-settings-view-toggle button:hover:not(.active){background:var(--admin-border);}

/* ===== TABLE ENHANCEMENTS ===== */
.ult-settings-table-wrap{overflow-x:auto;}
.ult-settings-cell{display:flex;align-items:center;gap:.75rem;min-width:180px;}
.ult-settings-key{font-family:monospace;font-size:.825rem;font-weight:700;color:var(--admin-primary);background:rgba(26,82,118,.08);padding:.25rem .625rem;border-radius:6px;border:1px solid rgba(26,82,118,.15);}
.ult-settings-value{font-size:.875rem;color:var(--admin-text);font-weight:500;}
.ult-settings-value input,.ult-settings-value textarea,.ult-settings-value select{width:100%;padding:.5rem .75rem;border:1.5px solid var(--admin-border);border-radius:var(--radius-md);font-size:.85rem;background:var(--admin-bg);transition:all .2s;font-family:inherit;}
.ult-settings-value input:focus,.ult-settings-value textarea:focus,.ult-settings-value select:focus{outline:none;border-color:var(--admin-primary);background:#fff;box-shadow:0 0 0 3px rgba(26,82,118,.08);}
.ult-settings-value textarea{resize:vertical;min-height:60px;}
.ult-settings-group{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;}
.ult-settings-desc{font-size:.8rem;color:var(--admin-text-secondary);line-height:1.5;}
.ult-settings-type{display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .625rem;background:var(--admin-bg);border-radius:50px;font-size:.7rem;font-weight:600;color:var(--admin-text-muted);}

/* ===== GROUP SECTIONS ===== */
.ult-settings-group-section{margin-bottom:2rem;}
.ult-settings-group-header{padding:1rem 1.5rem;background:linear-gradient(to right, var(--admin-bg), transparent);border:1px solid var(--admin-border);border-radius:var(--radius-lg) var(--radius-lg) 0 0;display:flex;align-items:center;gap:.75rem;cursor:pointer;transition:all .2s;}
.ult-settings-group-header:hover{background:var(--admin-bg);}
.ult-settings-group-icon{width:40px;height:40px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff;flex-shrink:0;}
.ult-settings-group-title{font-size:1.05rem;font-weight:700;color:var(--admin-text);flex:1;}
.ult-settings-group-count{font-size:.75rem;font-weight:700;padding:.25rem .625rem;border-radius:50px;background:var(--admin-bg);color:var(--admin-text-secondary);}
.ult-settings-group-toggle{font-size:1.25rem;color:var(--admin-text-muted);transition:transform .3s;}
.ult-settings-group-header.collapsed .ult-settings-group-toggle{transform:rotate(-90deg);}
.ult-settings-group-body{border:1px solid var(--admin-border);border-top:none;border-radius:0 0 var(--radius-lg) var(--radius-lg);overflow:hidden;}
.ult-settings-group-body.collapsed{display:none;}

/* ===== CARD VIEW ===== */
.ult-settings-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.25rem;padding:1.5rem;}
.ult-settings-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;transition:all .3s;}
.ult-settings-card:hover{box-shadow:0 8px 25px rgba(0,0,0,.08);border-color:var(--admin-primary);}
.ult-settings-card-header{padding:1rem 1.25rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-settings-card-key{font-family:monospace;font-size:.8rem;font-weight:700;color:var(--admin-primary);flex:1;}
.ult-settings-card-type{font-size:.7rem;font-weight:600;padding:.125rem .5rem;border-radius:50px;background:var(--admin-surface);color:var(--admin-text-muted);border:1px solid var(--admin-border);}
.ult-settings-card-body{padding:1.25rem;}
.ult-settings-card-value{margin-bottom:1rem;}
.ult-settings-card-value label{display:block;font-size:.75rem;font-weight:600;color:var(--admin-text-muted);margin-bottom:.375rem;text-transform:uppercase;letter-spacing:.5px;}
.ult-settings-card-desc{font-size:.8rem;color:var(--admin-text-secondary);line-height:1.6;padding-top:1rem;border-top:1px solid var(--admin-border);}

/* ===== ACTION BAR ===== */
.ult-settings-action-bar{position:sticky;bottom:0;background:var(--admin-surface);border-top:1px solid var(--admin-border);padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;z-index:50;box-shadow:0 -4px 15px rgba(0,0,0,.05);margin:0 -1.5rem -1.5rem;border-radius:0 0 var(--radius-lg) var(--radius-lg);}
@media (max-width:768px){.ult-settings-action-bar{position:fixed;bottom:0;left:0;right:0;margin:0;border-radius:0;z-index:1000;padding:1rem;}}

/* ===== EMPTY STATE ===== */
.ult-settings-empty{text-align:center;padding:4rem 2rem;}
.ult-settings-empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:var(--admin-primary);display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto 1.25rem;position:relative;}
.ult-settings-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(26,82,118,.3);border-radius:50%;animation:ult-settings-spin 20s linear infinite;}
@keyframes ult-settings-spin{to{transform:rotate(360deg);}}
.ult-settings-empty h3{font-size:1.25rem;margin-bottom:.375rem;color:var(--admin-text);}
.ult-settings-empty p{color:var(--admin-text-secondary);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-settings-stats{grid-template-columns:repeat(2,1fr);}
    .ult-settings-header{flex-direction:column;align-items:stretch;}
}
@media (max-width:640px){
    .ult-settings-stats{grid-template-columns:1fr;}
    .ult-settings-cards{grid-template-columns:1fr;padding:1rem;}
}
</style>

<!-- ===== STATS CARDS ===== -->
<div class="ult-settings-stats">
    <div class="ult-settings-stat" style="--ult-color:#3b82f6;">
        <div class="ult-settings-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-gear-fill"></i></div>
        <div class="ult-settings-stat-info">
            <div class="ult-settings-stat-label">Total Pengaturan</div>
            <div class="ult-settings-stat-value"><?= number_format($statTotal) ?></div>
            <div class="ult-settings-stat-sub"><i class="bi bi-database-fill"></i> Keseluruhan</div>
        </div>
    </div>
    <div class="ult-settings-stat" style="--ult-color:#10b981;">
        <div class="ult-settings-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-folder-fill"></i></div>
        <div class="ult-settings-stat-info">
            <div class="ult-settings-stat-label">Grup Aktif</div>
            <div class="ult-settings-stat-value"><?= number_format($statGroups) ?></div>
            <div class="ult-settings-stat-sub"><i class="bi bi-diagram-3-fill"></i> Kategori</div>
        </div>
    </div>
    <div class="ult-settings-stat" style="--ult-color:#f59e0b;">
        <div class="ult-settings-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="bi bi-pencil-fill"></i></div>
        <div class="ult-settings-stat-info">
            <div class="ult-settings-stat-label">Dapat Diedit</div>
            <div class="ult-settings-stat-value"><?= number_format($statEditable) ?></div>
            <div class="ult-settings-stat-sub"><i class="bi bi-check-circle-fill"></i> Text/Number/Boolean</div>
        </div>
    </div>
    <div class="ult-settings-stat" style="--ult-color:#ef4444;">
        <div class="ult-settings-stat-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="bi bi-shield-lock-fill"></i></div>
        <div class="ult-settings-stat-info">
            <div class="ult-settings-stat-label">Keamanan</div>
            <div class="ult-settings-stat-value"><?= number_format($statSecurity) ?></div>
            <div class="ult-settings-stat-sub"><i class="bi bi-lock-fill"></i> Critical settings</div>
        </div>
    </div>
</div>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
    <div>
        <h1 class="page-title">Pengaturan Sistem</h1>
        <p class="page-subtitle">Konfigurasi sistem & keamanan BAUK UNIMOF</p>
    </div>
    <div class="ult-settings-header-actions">
        <div class="ult-settings-view-toggle" id="ultSettingsViewToggle">
            <button type="button" class="<?= $currentView === 'grouped' ? 'active' : '' ?>" data-view="grouped" title="Tampilan Grup">
                <i class="bi bi-folder-fill"></i> <span class="d-none-mobile">Grup</span>
            </button>
            <button type="button" class="<?= $currentView === 'table' ? 'active' : '' ?>" data-view="table" title="Tampilan Tabel">
                <i class="bi bi-list-ul"></i> <span class="d-none-mobile">Tabel</span>
            </button>
            <button type="button" class="<?= $currentView === 'cards' ? 'active' : '' ?>" data-view="cards" title="Tampilan Kartu">
                <i class="bi bi-grid-3x3-gap"></i> <span class="d-none-mobile">Kartu</span>
            </button>
        </div>
        <?php if ($canEdit): ?>
        <button type="button" class="btn btn-secondary" onclick="if(typeof showToast!=='undefined')showToast('Reset ke default segera hadir','info');">
            <i class="bi bi-arrow-counterclockwise"></i> Reset Default
        </button>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('ultSettingsForm').submit()">
            <i class="bi bi-save-fill"></i> Simpan Semua
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- ===== MAIN CARD ===== -->
<div class="card">
    <div class="card-body" style="padding:0;">
        
        <!-- Card Header -->
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="ult-settings-header">
                <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                    <h3 class="card-title" style="margin:0;"><i class="bi bi-gear-fill"></i> Konfigurasi Sistem</h3>
                    <span class="pgn-result-info">
                        Total <strong><?= number_format($statTotal) ?></strong> pengaturan dalam <strong><?= number_format($statGroups) ?></strong> grup
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Toolbar -->
        <form method="GET" action="<?= url('admin/pengaturan') ?>" class="pgn-toolbar" style="padding:1rem 1.5rem;border-bottom:1px solid var(--admin-border);">
            <div class="toolbar-search" style="flex:2;min-width:220px;">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Cari pengaturan...">
            </div>
            
            <!-- Custom Group Dropdown -->
            <div class="ult-settings-dropdown" data-dropdown>
                <input type="hidden" name="group" id="ultSettingsGroupValue" value="<?= htmlspecialchars($activeGroup) ?>">
                <button type="button" class="ult-settings-dropdown-trigger <?= $activeGroup !== 'all' ? 'has-value' : '' ?>" id="ultSettingsGroupTrigger">
                    <span style="display:flex;align-items:center;gap:.5rem;">
                        <?php if ($activeGroup !== 'all' && isset($groupMap[$activeGroup])): 
                            $gInfo = $groupMap[$activeGroup]; ?>
                            <i class="bi <?= $gInfo['icon'] ?>" style="color:<?= $gInfo['color'] ?>;"></i>
                            <span><?= $gInfo['label'] ?></span>
                        <?php else: ?>
                            <i class="bi bi-folder-fill" style="color:var(--admin-primary);"></i>
                            <span>Semua Grup</span>
                        <?php endif; ?>
                    </span>
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="ult-settings-dropdown-menu" style="min-width:220px;">
                    <div class="ult-settings-dd-search"><input type="text" placeholder="Cari grup..." data-search></div>
                    <div class="ult-settings-dd-item <?= $activeGroup === 'all' ? 'selected' : '' ?>" data-value="all">
                        <i class="bi bi-folder-fill" style="color:var(--admin-text-muted);"></i><span>Semua Grup</span><i class="bi bi-check-lg"></i>
                    </div>
                    <div class="ult-settings-dd-divider"></div>
                    <?php foreach ($groupMap as $key => $info): 
                        $count = count($groupedSettings[$key] ?? []);
                        if ($count === 0) continue;
                    ?>
                    <div class="ult-settings-dd-item <?= $activeGroup === $key ? 'selected' : '' ?>" data-value="<?= $key ?>">
                        <i class="bi <?= $info['icon'] ?>" style="color:<?= $info['color'] ?>;"></i>
                        <span><?= $info['label'] ?></span>
                        <span style="margin-left:auto;font-size:.75rem;color:var(--admin-text-muted);background:var(--admin-bg);padding:.125rem .5rem;border-radius:50px;"><?= $count ?></span>
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Terapkan</button>
            <?php if ($searchQuery !== '' || $activeGroup !== 'all'): ?>
                <a href="<?= url('admin/pengaturan') ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
            <?php endif; ?>
        </form>
        
        <!-- Filter Chips -->
        <?php if ($searchQuery !== '' || $activeGroup !== 'all'): ?>
        <div class="ult-settings-chips">
            <span class="ult-settings-chips-label"><i class="bi bi-funnel-fill"></i> Filter Aktif:</span>
            <?php if ($searchQuery !== ''): ?>
                <span class="ult-settings-chip">
                    <i class="bi bi-search"></i> Pencarian: "<strong><?= htmlspecialchars($searchQuery) ?></strong>"
                    <a href="<?= url('admin/pengaturan') . '?group=' . $activeGroup ?>" class="ult-settings-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <?php if ($activeGroup !== 'all' && isset($groupMap[$activeGroup])): ?>
                <span class="ult-settings-chip">
                    <i class="bi <?= $groupMap[$activeGroup]['icon'] ?>"></i> Grup: <strong><?= $groupMap[$activeGroup]['label'] ?></strong>
                    <a href="<?= url('admin/pengaturan') . '?q=' . $searchQuery ?>" class="ult-settings-chip-close"><i class="bi bi-x-lg"></i></a>
                </span>
            <?php endif; ?>
            <a href="<?= url('admin/pengaturan') ?>" class="ult-settings-chips-clear"><i class="bi bi-x-circle"></i> Hapus Semua</a>
        </div>
        <?php endif; ?>
        
        <?php if ($canEdit): ?>
        <form method="POST" action="<?= url('admin/pengaturan/simpan') ?>" id="ultSettingsForm">
            <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
        <?php endif; ?>
        
        <!-- ===== GROUPED VIEW ===== -->
        <?php if ($currentView === 'grouped'): ?>
        <div id="ultSettingsGroupedView" style="padding:1.5rem;">
            <?php foreach ($groupedSettings as $group => $items): 
                if ($activeGroup !== 'all' && $activeGroup !== $group) continue;
                $gInfo = $groupMap[$group] ?? ['label' => ucfirst($group), 'icon' => 'bi-folder-fill', 'color' => '#64748b'];
                
                // Filter by search
                $filteredItems = $items;
                if ($searchQuery !== '') {
                    $filteredItems = array_filter($items, function($s) use ($searchQuery) {
                        return stripos($s['key'], $searchQuery) !== false || 
                               stripos($s['description'] ?? '', $searchQuery) !== false ||
                               stripos($s['value'] ?? '', $searchQuery) !== false;
                    });
                }
                
                if (empty($filteredItems)) continue;
            ?>
            <div class="ult-settings-group-section">
                <div class="ult-settings-group-header" data-toggle-group>
                    <div class="ult-settings-group-icon" style="background:linear-gradient(135deg,<?= $gInfo['color'] ?>,<?= $gInfo['color'] ?>cc);">
                        <i class="bi <?= $gInfo['icon'] ?>"></i>
                    </div>
                    <div class="ult-settings-group-title"><?= $gInfo['label'] ?></div>
                    <div class="ult-settings-group-count"><?= count($filteredItems) ?> pengaturan</div>
                    <i class="bi bi-chevron-down ult-settings-group-toggle"></i>
                </div>
                <div class="ult-settings-group-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:25%;">Key</th>
                                <th style="width:35%;">Value</th>
                                <th style="width:15%;">Tipe</th>
                                <th style="width:25%;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($filteredItems as $s): ?>
                            <tr>
                                <td><span class="ult-settings-key"><?= htmlspecialchars($s['key']) ?></span></td>
                                <td class="ult-settings-value">
                                    <?php if ($canEdit): ?>
                                        <?php if ($s['type'] === 'boolean'): ?>
                                            <select name="settings[<?= htmlspecialchars($s['key']) ?>]">
                                                <option value="1" <?= ($s['value'] ?? '0') == '1' ? 'selected' : '' ?>>Aktif</option>
                                                <option value="0" <?= ($s['value'] ?? '0') == '0' ? 'selected' : '' ?>>Nonaktif</option>
                                            </select>
                                        <?php elseif ($s['type'] === 'number'): ?>
                                            <input type="number" name="settings[<?= htmlspecialchars($s['key']) ?>]" value="<?= htmlspecialchars($s['value'] ?? '') ?>" step="any">
                                        <?php else: ?>
                                            <input type="text" name="settings[<?= htmlspecialchars($s['key']) ?>]" value="<?= htmlspecialchars($s['value'] ?? '') ?>" maxlength="500">
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="ult-settings-value"><?= htmlspecialchars($s['value'] ?? '-') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="ult-settings-type"><i class="bi bi-<?= $s['type'] === 'boolean' ? 'toggle-on' : ($s['type'] === 'number' ? '123' : 'type') ?>"></i> <?= ucfirst($s['type']) ?></span></td>
                                <td class="ult-settings-desc"><?= htmlspecialchars($s['description'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($groupedSettings)): ?>
            <div class="ult-settings-empty">
                <div class="ult-settings-empty-icon"><i class="bi bi-gear"></i></div>
                <h3>Tidak Ada Pengaturan</h3>
                <p>Belum ada pengaturan dalam sistem. Pengaturan akan muncul setelah diinisialisasi.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- ===== TABLE VIEW ===== -->
        <?php if ($currentView === 'table'): ?>
        <div id="ultSettingsTableView" class="ult-settings-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:20%;">Key</th>
                        <th style="width:30%;">Value</th>
                        <th style="width:15%;">Grup</th>
                        <th style="width:15%;">Tipe</th>
                        <th style="width:20%;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($settings as $s): 
                    if ($activeGroup !== 'all' && $activeGroup !== $s['group']) continue;
                    if ($searchQuery !== '' && stripos($s['key'], $searchQuery) === false && stripos($s['description'] ?? '', $searchQuery) === false) continue;
                    $gInfo = $groupMap[$s['group']] ?? ['label' => ucfirst($s['group']), 'icon' => 'bi-folder-fill', 'color' => '#64748b'];
                ?>
                    <tr>
                        <td><span class="ult-settings-key"><?= htmlspecialchars($s['key']) ?></span></td>
                        <td class="ult-settings-value">
                            <?php if ($canEdit): ?>
                                <?php if ($s['type'] === 'boolean'): ?>
                                    <select name="settings[<?= htmlspecialchars($s['key']) ?>]">
                                        <option value="1" <?= ($s['value'] ?? '0') == '1' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= ($s['value'] ?? '0') == '0' ? 'selected' : '' ?>>Nonaktif</option>
                                    </select>
                                <?php elseif ($s['type'] === 'number'): ?>
                                    <input type="number" name="settings[<?= htmlspecialchars($s['key']) ?>]" value="<?= htmlspecialchars($s['value'] ?? '') ?>" step="any">
                                <?php else: ?>
                                    <input type="text" name="settings[<?= htmlspecialchars($s['key']) ?>]" value="<?= htmlspecialchars($s['value'] ?? '') ?>" maxlength="500">
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="ult-settings-value"><?= htmlspecialchars($s['value'] ?? '-') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="ult-settings-group" style="background:<?= $gInfo['color'] ?>20;color:<?= $gInfo['color'] ?>;">
                                <i class="bi <?= $gInfo['icon'] ?>"></i> <?= $gInfo['label'] ?>
                            </span>
                        </td>
                        <td><span class="ult-settings-type"><i class="bi bi-<?= $s['type'] === 'boolean' ? 'toggle-on' : ($s['type'] === 'number' ? '123' : 'type') ?>"></i> <?= ucfirst($s['type']) ?></span></td>
                        <td class="ult-settings-desc"><?= htmlspecialchars($s['description'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$settings): ?>
                <tr><td colspan="5">
                    <div class="ult-settings-empty">
                        <div class="ult-settings-empty-icon"><i class="bi bi-gear"></i></div>
                        <h3>Tidak Ada Pengaturan</h3>
                        <p>Belum ada pengaturan dalam sistem.</p>
                    </div>
                </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- ===== CARDS VIEW ===== -->
        <?php if ($currentView === 'cards'): ?>
        <div id="ultSettingsCardsView" class="ult-settings-cards">
            <?php foreach ($settings as $s): 
                if ($activeGroup !== 'all' && $activeGroup !== $s['group']) continue;
                if ($searchQuery !== '' && stripos($s['key'], $searchQuery) === false && stripos($s['description'] ?? '', $searchQuery) === false) continue;
                $gInfo = $groupMap[$s['group']] ?? ['label' => ucfirst($s['group']), 'icon' => 'bi-folder-fill', 'color' => '#64748b'];
            ?>
            <div class="ult-settings-card">
                <div class="ult-settings-card-header">
                    <div class="ult-settings-card-key"><?= htmlspecialchars($s['key']) ?></div>
                    <span class="ult-settings-card-type"><?= ucfirst($s['type']) ?></span>
                </div>
                <div class="ult-settings-card-body">
                    <div class="ult-settings-card-value">
                        <label>Nilai</label>
                        <?php if ($canEdit): ?>
                            <?php if ($s['type'] === 'boolean'): ?>
                                <select name="settings[<?= htmlspecialchars($s['key']) ?>]">
                                    <option value="1" <?= ($s['value'] ?? '0') == '1' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= ($s['value'] ?? '0') == '0' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            <?php elseif ($s['type'] === 'number'): ?>
                                <input type="number" name="settings[<?= htmlspecialchars($s['key']) ?>]" value="<?= htmlspecialchars($s['value'] ?? '') ?>" step="any">
                            <?php else: ?>
                                <input type="text" name="settings[<?= htmlspecialchars($s['key']) ?>]" value="<?= htmlspecialchars($s['value'] ?? '') ?>" maxlength="500">
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="padding:.5rem;background:var(--admin-bg);border-radius:6px;font-size:.875rem;"><?= htmlspecialchars($s['value'] ?? '-') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="ult-settings-card-desc">
                        <i class="bi <?= $gInfo['icon'] ?>" style="color:<?= $gInfo['color'] ?>;"></i>
                        <strong><?= $gInfo['label'] ?></strong><br>
                        <?= htmlspecialchars($s['description'] ?? 'Tidak ada deskripsi') ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (!$settings): ?>
            <div class="ult-settings-empty" style="grid-column:1/-1;">
                <div class="ult-settings-empty-icon"><i class="bi bi-gear"></i></div>
                <h3>Tidak Ada Pengaturan</h3>
                <p>Belum ada pengaturan dalam sistem.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($canEdit): ?>
        <!-- ===== ACTION BAR ===== -->
        <div class="ult-settings-action-bar">
            <div style="font-size:.85rem;color:var(--admin-text-secondary);">
                <i class="bi bi-info-circle-fill"></i> Perubahan akan disimpan ke database
            </div>
            <div style="display:flex;gap:.5rem;">
                <button type="button" class="btn btn-secondary" onclick="if(typeof showToast!=='undefined')showToast('Reset ke default segera hadir','info');">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save-fill"></i> Simpan Semua Perubahan
                </button>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($canEdit): ?>
        </form>
        <?php endif; ?>
        
    </div>
</div>

<!-- ===== INLINE SCRIPT ===== -->
<script>
(function(){
    'use strict';
    
    // ===== CUSTOM DROPDOWN =====
    document.querySelectorAll('.ult-settings-dropdown[data-dropdown]').forEach(function(dd){
        const trigger = dd.querySelector('.ult-settings-dropdown-trigger');
        const menu = dd.querySelector('.ult-settings-dropdown-menu');
        if(!trigger || !menu) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            document.querySelectorAll('.ult-settings-dropdown.open').forEach(o => { if(o!==dd) o.classList.remove('open'); });
            dd.classList.toggle('open');
            if(dd.classList.contains('open')){
                const s = menu.querySelector('[data-search]');
                if(s) setTimeout(() => s.focus(), 50);
            }
        });
        menu.querySelectorAll('.ult-settings-dd-item[data-value]').forEach(function(item){
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
                menu.querySelectorAll('.ult-settings-dd-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                val !== '' && val !== 'all' ? trigger.classList.add('has-value') : trigger.classList.remove('has-value');
                dd.classList.remove('open');
                if(hidden && hidden.name === 'group'){
                    const form = dd.closest('form');
                    if(form) form.submit();
                }
            });
        });
        const s = menu.querySelector('[data-search]');
        if(s){
            s.addEventListener('input', function(){
                const q = this.value.toLowerCase();
                menu.querySelectorAll('.ult-settings-dd-item').forEach(i => {
                    if(i.hasAttribute('data-value')) i.style.display = i.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    });
    document.addEventListener('click', () => document.querySelectorAll('.ult-settings-dropdown.open').forEach(d => d.classList.remove('open')));
    
    // ===== VIEW TOGGLE =====
    const vt = document.getElementById('ultSettingsViewToggle');
    const gv = document.getElementById('ultSettingsGroupedView');
    const tv = document.getElementById('ultSettingsTableView');
    const cv = document.getElementById('ultSettingsCardsView');
    if(vt){
        vt.querySelectorAll('button[data-view]').forEach(btn => {
            btn.addEventListener('click', function(){
                const v = this.getAttribute('data-view');
                vt.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                if(gv) gv.style.display = v === 'grouped' ? '' : 'none';
                if(tv) tv.style.display = v === 'table' ? '' : 'none';
                if(cv) cv.style.display = v === 'cards' ? '' : 'none';
                try { localStorage.setItem('ult_settings_view', v); } catch(e){}
            });
        });
    }
    
    // ===== GROUP TOGGLE (Collapsible) =====
    document.querySelectorAll('[data-toggle-group]').forEach(function(header){
        header.addEventListener('click', function(){
            this.classList.toggle('collapsed');
            const body = this.nextElementSibling;
            if(body) body.classList.toggle('collapsed');
        });
    });
})();
</script>