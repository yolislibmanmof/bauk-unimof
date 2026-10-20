<?php
/**
 * =====================================================
 * BAUK UNIMOF - SETTINGS CENTER (ULTIMATE v2)
 * Branding Studio • Live Mockup • Change Tracker
 * =====================================================
 */
use App\Helpers\Auth;

$canEdit = Auth::hasRole(['super_admin', 'admin_bauk']);

if (!isset($settings) || !is_array($settings)) $settings = [];

// ===== GROUP MAP =====
$groupMap = [
    'general'  => ['label' => 'Umum & Branding', 'icon' => 'bi-palette-fill',        'color' => '#3b82f6'],
    'content'  => ['label' => 'Konten Halaman',  'icon' => 'bi-file-earmark-text-fill', 'color' => '#10b981'],
    'contact'  => ['label' => 'Kontak',          'icon' => 'bi-telephone-fill',      'color' => '#f59e0b'],
    'security' => ['label' => 'Keamanan',        'icon' => 'bi-shield-lock-fill',    'color' => '#ef4444'],
    'system'   => ['label' => 'Sistem',          'icon' => 'bi-cpu-fill',            'color' => '#64748b'],
];

// ===== STATS =====
$statTotal    = count($settings);
$statGroups   = count(array_unique(array_map(fn($s) => $s['group'] ?? 'general', $settings)));
$statEditable = count(array_filter($settings, fn($s) => in_array($s['type'] ?? '', ['text','number','boolean','textarea','file'], true)));
$statSecurity = count(array_filter($settings, fn($s) => ($s['group'] ?? '') === 'security'));
$lastUpdated  = '';
foreach ($settings as $s) {
    if (!empty($s['updated_at']) && $s['updated_at'] > $lastUpdated) $lastUpdated = $s['updated_at'];
}

// ===== RELATIVE TIME =====
if (!function_exists('ult2_rel_time')) {
    function ult2_rel_time(?string $dt): string {
        if (!$dt) return '-';
        $diff = time() - strtotime($dt);
        if ($diff < 60) return 'Baru saja';
        if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
        if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
        return date('d M Y', strtotime($dt));
    }
}

// ===== HIGHLIGHT SEARCH =====
if (!function_exists('ult2_hl')) {
    function ult2_hl(string $text, string $q): string {
        $safe = htmlspecialchars($text);
        if ($q === '') return $safe;
        return preg_replace('/(' . preg_quote(htmlspecialchars($q), '/') . ')/i', '<mark class="ult2-mark">$1</mark>', $safe);
    }
}

// ===== GROUPING & FILTER =====
$groupedSettings = [];
foreach ($settings as $s) {
    $groupedSettings[$s['group'] ?? 'general'][] = $s;
}
$viewParam   = (string)($_GET['view'] ?? 'grouped');
$currentView = in_array($viewParam, ['grouped', 'table', 'cards'], true) ? $viewParam : 'grouped';
$activeGroup = (string)($_GET['group'] ?? 'all');
$searchQuery = trim((string)($_GET['q'] ?? ''));

$fileSettings = array_values(array_filter($settings, fn($s) => ($s['type'] ?? '') === 'file'));

// ===== NILAI FILE BRANDING UNTUK MOCKUP (aman, tanpa helper eksternal) =====
$__logoVal = '';
$__favVal  = '';
foreach ($settings as $s) {
    $k = (string)($s['key'] ?? '');
    if ($k === 'site_logo'    && !empty($s['value'])) $__logoVal = (string)$s['value'];
    if ($k === 'site_favicon' && !empty($s['value'])) $__favVal  = (string)$s['value'];
}

// ===== RENDER INPUT =====
if (!function_exists('ult2_render_input')) {
    function ult2_render_input(array $s, bool $canEdit, string $context = 'row'): string {
        $key  = (string)($s['key'] ?? '');
        $val  = (string)($s['value'] ?? '');
        $type = (string)($s['type'] ?? 'text');
        $keyE = htmlspecialchars($key, ENT_QUOTES);
        $valE = htmlspecialchars($val, ENT_QUOTES);

        // ---- READ ONLY ----
        if (!$canEdit) {
            if ($type === 'file' && $val !== '') return '<img src="' . url('uploads/settings/' . $val) . '" class="ult2-thumb" alt="">';
            if ($type === 'boolean') return '<span class="ult2-badge ' . ($val == '1' ? 'ok' : 'off') . '">' . ($val == '1' ? 'Aktif' : 'Nonaktif') . '</span>';
            return '<span class="ult2-readonly">' . ($valE !== '' ? $valE : '-') . '</span>';
        }

        // ---- FILE ----
        if ($type === 'file') {
            if ($context === 'studio') {
                $fileMetaLocal = [
                    'site_logo'                 => ['label' => 'Logo Website',        'hint' => 'PNG/JPG/SVG/WebP • maks 2MB • tampil di navbar & footer'],
                    'site_favicon'              => ['label' => 'Favicon Browser',     'hint' => 'ICO/PNG/SVG • maks 1MB • ikon tab browser'],
                    'kepala_bauk_photo'         => ['label' => 'Foto Kepala BAUK',    'hint' => 'JPG/PNG/WebP • maks 2MB • halaman sambutan & profil'],
                    'struktur_organisasi_photo' => ['label' => 'Bagan Struktur Org.', 'hint' => 'JPG/PNG/WebP • maks 5MB • halaman struktur organisasi'],
                ];
                $meta   = $fileMetaLocal[$key] ?? ['label' => ucfirst(str_replace('_', ' ', $key)), 'hint' => 'Maks 5MB'];
                $imgSrc = $val !== '' ? url('uploads/settings/' . $val) : '';
                $accept = $key === 'site_favicon' ? '.ico,.png,.svg' : '.png,.jpg,.jpeg,.svg,.webp';
                return '<div class="ult2-studio-card" data-setting-key="' . $keyE . '">
                    <div class="ult2-studio-preview' . ($imgSrc === '' ? ' empty' : '') . '">
                        <img src="' . ($imgSrc !== '' ? htmlspecialchars($imgSrc) : '') . '" class="js-setting-preview" data-setting-key="' . $keyE . '" alt="" style="' . ($imgSrc !== '' ? '' : 'display:none') . '">
                        <div class="ult2-studio-ph js-studio-ph" data-setting-key="' . $keyE . '" style="' . ($imgSrc !== '' ? 'display:none' : '') . '"><i class="bi bi-image"></i></div>
                    </div>
                    <div class="ult2-studio-info">
                        <strong>' . htmlspecialchars($meta['label']) . '</strong>
                        <span class="ult2-studio-key"><code>' . $keyE . '</code></span>
                        <span class="ult2-studio-hint">' . htmlspecialchars($meta['hint']) . '</span>
                    </div>
                    <label class="ult2-upload-btn">
                        <i class="bi bi-upload"></i> Pilih File
                        <input type="file" name="' . $keyE . '" data-setting-key="' . $keyE . '" accept="' . $accept . '" hidden>
                    </label>
                    <div class="ult2-studio-file" data-filelabel="' . $keyE . '"><i class="bi bi-paperclip"></i> Belum ada file baru dipilih</div>
                </div>';
            }
            // context row: preview only
            $imgSrc = $val !== '' ? url('uploads/settings/' . $val) : '';
            return '<div class="ult2-row-file">'
                . ($imgSrc !== '' ? '<img src="' . htmlspecialchars($imgSrc) . '" class="ult2-thumb js-setting-preview" data-setting-key="' . $keyE . '" alt="">' : '<span class="ult2-thumb ult2-thumb-empty"><i class="bi bi-image"></i></span>')
                . '<a href="#ult2Studio" class="ult2-mini-link"><i class="bi bi-magic"></i> Kelola di Studio Branding</a></div>';
        }

        // ---- BOOLEAN (switch) ----
        if ($type === 'boolean') {
            $on = ($val == '1');
            return '<label class="ult2-switch">
                <input type="hidden" name="settings[' . $keyE . ']" value="0">
                <input type="checkbox" name="settings[' . $keyE . ']" value="1"' . ($on ? ' checked' : '') . '>
                <span class="ult2-switch-track"><span class="ult2-switch-thumb"></span></span>
                <span class="ult2-switch-label ' . ($on ? 'on' : 'off') . '">' . ($on ? 'Aktif' : 'Nonaktif') . '</span>
            </label>';
        }

        // ---- NUMBER ----
        if ($type === 'number') {
            return '<input type="number" name="settings[' . $keyE . ']" value="' . $valE . '" step="any" class="ult2-input">';
        }

        // ---- TEXTAREA ----
        if ($type === 'textarea' || in_array($key, ['visi_text','misi_text','tujuan_text','contact_address'], true)) {
            $len = mb_strlen($val);
            return '<div class="ult2-ta-wrap">
                <textarea name="settings[' . $keyE . ']" rows="4" maxlength="5000" class="ult2-input ult2-textarea js-counter" data-counter-for="' . $keyE . '">' . $valE . '</textarea>
                <span class="ult2-counter" id="cnt_' . $keyE . '">' . $len . ' / 5000</span>
            </div>';
        }

        // ---- TEXT (default) ----
        return '<input type="text" name="settings[' . $keyE . ']" value="' . $valE . '" maxlength="500" class="ult2-input">';
    }
}

// Type icon map
$typeIcon = fn(string $t): string => match ($t) {
    'boolean'  => 'toggle-on',
    'number'   => 'bi-123',
    'file'     => 'image-fill',
    'textarea' => 'textarea',
    'json'     => 'braces',
    default    => 'input-cursor-text',
};
?>

<!-- =====================================================
     SETTINGS CENTER v2 STYLES
     ===================================================== -->
<style>
/* ===== HERO HEADER ===== */
.ult2-hero{background:linear-gradient(135deg,#0d2f4a 0%,#1a5276 55%,#2980b9 100%);border-radius:20px;padding:2.25rem 2.5rem;color:#fff;position:relative;overflow:hidden;margin-bottom:1.5rem;}
.ult2-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='1.5'/%3E%3C/g%3E%3C/svg%3E");}
.ult2-hero::after{content:'';position:absolute;top:-60%;right:-8%;width:380px;height:380px;background:radial-gradient(circle,rgba(230,126,34,.25) 0%,transparent 70%);border-radius:50%;}
.ult2-hero-inner{position:relative;z-index:2;display:flex;justify-content:space-between;align-items:center;gap:1.5rem;flex-wrap:wrap;}
.ult2-hero-title{display:flex;align-items:center;gap:1.1rem;}
.ult2-hero-icon{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.14);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:1.7rem;flex-shrink:0;}
.ult2-hero h1{font-family:'Playfair Display',serif;font-size:1.7rem;margin:0 0 .3rem;color:#fff;}
.ult2-hero p{margin:0;font-size:.88rem;color:rgba(255,255,255,.82);}
.ult2-hero-actions{display:flex;gap:.6rem;flex-wrap:wrap;}
.ult2-hero-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.65rem 1.15rem;border-radius:12px;font-size:.84rem;font-weight:700;cursor:pointer;transition:all .25s;border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.1);color:#fff;}
.ult2-hero-btn:hover{background:rgba(255,255,255,.2);transform:translateY(-2px);}
.ult2-hero-btn.primary{background:linear-gradient(135deg,#e67e22,#f39c12);border-color:transparent;box-shadow:0 8px 22px rgba(230,126,34,.4);}
.ult2-hero-btn.primary:hover{box-shadow:0 12px 30px rgba(230,126,34,.5);}

/* ===== STATS ===== */
.ult2-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin-bottom:1.5rem;}
.ult2-stat{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:16px;padding:1.15rem 1.25rem;display:flex;align-items:center;gap:.9rem;position:relative;overflow:hidden;transition:all .3s;}
.ult2-stat::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--sc,#3b82f6);}
.ult2-stat:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(0,0,0,.08);}
.ult2-stat-icon{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff;flex-shrink:0;}
.ult2-stat-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.6px;font-weight:700;color:var(--admin-text-muted);}
.ult2-stat-value{font-size:1.5rem;font-weight:800;color:var(--admin-text);line-height:1.15;}
.ult2-stat-sub{font-size:.7rem;color:var(--admin-text-muted);}

/* ===== STUDIO BRANDING ===== */
.ult2-studio{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:18px;overflow:hidden;margin-bottom:1.5rem;}
.ult2-studio-head{padding:1.25rem 1.5rem;background:linear-gradient(to right,rgba(230,126,34,.08),transparent);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.85rem;flex-wrap:wrap;}
.ult2-studio-head-icon{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.15rem;box-shadow:0 6px 16px rgba(230,126,34,.35);}
.ult2-studio-head h2{font-size:1.1rem;font-weight:800;margin:0;color:var(--admin-text);}
.ult2-studio-head p{margin:.15rem 0 0;font-size:.78rem;color:var(--admin-text-secondary);}
.ult2-studio-body{padding:1.5rem;display:grid;grid-template-columns:340px 1fr;gap:1.5rem;}
@media (max-width:1100px){.ult2-studio-body{grid-template-columns:1fr;}}

/* Live mockup */
.ult2-mockup{border:1px solid var(--admin-border);border-radius:14px;overflow:hidden;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,.08);align-self:start;position:sticky;top:90px;}
.ult2-mockup-bar{display:flex;align-items:center;gap:.5rem;background:#e8ecf1;padding:.55rem .8rem;}
.ult2-mockup-dot{width:10px;height:10px;border-radius:50%;}
.ult2-mockup-tab{display:flex;align-items:center;gap:.45rem;background:#fff;border-radius:8px 8px 0 0;padding:.35rem .7rem;font-size:.72rem;font-weight:600;color:#33475b;max-width:180px;overflow:hidden;white-space:nowrap;}
.ult2-mockup-tab img{width:14px;height:14px;object-fit:contain;border-radius:3px;}
.ult2-mockup-tab .ult2-mock-ph{width:14px;height:14px;border-radius:3px;background:#1a5276;color:#fff;font-size:.55rem;display:flex;align-items:center;justify-content:center;font-weight:800;}
.ult2-mockup-url{flex:1;background:#f4f6f9;border-radius:20px;padding:.3rem .8rem;font-size:.68rem;color:#7a8ba0;display:flex;align-items:center;gap:.35rem;}
.ult2-mockup-url i{color:#10b981;font-size:.6rem;}
.ult2-mockup-nav{display:flex;align-items:center;gap:.7rem;padding:.8rem 1rem;border-bottom:1px solid #eef1f5;}
.ult2-mockup-nav img{width:34px;height:34px;object-fit:contain;border-radius:8px;}
.ult2-mockup-nav .ult2-mock-logo-ph{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,#0d2f4a,#2980b9);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;}
.ult2-mockup-brand{font-family:'Playfair Display',serif;font-weight:800;font-size:.95rem;color:#0d2f4a;}
.ult2-mockup-menu{margin-left:auto;display:flex;gap:.6rem;font-size:.68rem;color:#7a8ba0;font-weight:600;}
.ult2-mockup-body{padding:1.4rem 1rem;background:linear-gradient(180deg,#f8fafc,#fff);min-height:90px;display:flex;align-items:center;justify-content:center;}
.ult2-mockup-body span{font-size:.72rem;color:#9aa8b8;font-style:italic;}
.ult2-mockup-caption{padding:.6rem .9rem;background:#f8fafc;font-size:.68rem;color:#7a8ba0;display:flex;align-items:center;gap:.4rem;border-top:1px solid #eef1f5;}
.ult2-mockup-caption i{color:#e67e22;}

/* Studio cards */
.ult2-studio-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:1rem;}
.ult2-studio-card{border:1.5px dashed var(--admin-border);border-radius:14px;padding:1rem;background:var(--admin-bg);display:flex;flex-direction:column;gap:.7rem;transition:all .25s;position:relative;}
.ult2-studio-card:hover{border-color:var(--admin-primary);background:rgba(26,82,118,.04);}
.ult2-studio-card.dragover{border-color:#e67e22;background:rgba(230,126,34,.08);transform:scale(1.02);}
.ult2-studio-preview{height:110px;border-radius:10px;background:#fff;border:1px solid var(--admin-border);display:flex;align-items:center;justify-content:center;overflow:hidden;}
.ult2-studio-preview img{max-width:100%;max-height:100%;object-fit:contain;}
.ult2-studio-ph{color:#c3ced9;font-size:2rem;}
.ult2-studio-info{display:flex;flex-direction:column;gap:.2rem;}
.ult2-studio-info strong{font-size:.88rem;color:var(--admin-text);}
.ult2-studio-key code{font-size:.68rem;background:rgba(26,82,118,.08);color:var(--admin-primary);padding:.1rem .45rem;border-radius:5px;font-weight:700;}
.ult2-studio-hint{font-size:.68rem;color:var(--admin-text-muted);line-height:1.45;}
.ult2-upload-btn{display:flex;align-items:center;justify-content:center;gap:.45rem;padding:.6rem 1rem;border-radius:10px;background:var(--admin-primary);color:#fff;font-size:.8rem;font-weight:700;cursor:pointer;transition:all .2s;text-align:center;}
.ult2-upload-btn:hover{background:#0d2f4a;transform:translateY(-1px);}
.ult2-studio-file{font-size:.7rem;color:var(--admin-text-muted);display:flex;align-items:center;gap:.35rem;}
.ult2-studio-file.has-file{color:#10b981;font-weight:600;}

/* ===== TABS ===== */
.ult2-tabs{display:flex;gap:.5rem;overflow-x:auto;padding:.25rem .25rem .75rem;margin-bottom:1rem;scrollbar-width:none;}
.ult2-tabs::-webkit-scrollbar{display:none;}
.ult2-tab{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.05rem;border-radius:50px;border:1.5px solid var(--admin-border);background:var(--admin-surface);color:var(--admin-text-secondary);font-size:.82rem;font-weight:700;text-decoration:none!important;white-space:nowrap;transition:all .2s;}
.ult2-tab:hover{border-color:var(--admin-primary);color:var(--admin-primary);}
.ult2-tab.active{background:var(--admin-primary);border-color:var(--admin-primary);color:#fff;box-shadow:0 6px 18px rgba(26,82,118,.3);}
.ult2-tab .ult2-tab-count{background:rgba(0,0,0,.12);padding:.05rem .5rem;border-radius:20px;font-size:.68rem;}
.ult2-tab.active .ult2-tab-count{background:rgba(255,255,255,.22);}

/* ===== TOOLBAR ===== */
.ult2-toolbar{display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem;}
.ult2-searchbox{position:relative;flex:1;min-width:220px;}
.ult2-searchbox i{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--admin-text-muted);}
.ult2-searchbox input{width:100%;padding:.7rem 1rem .7rem 2.6rem;border:1.5px solid var(--admin-border);border-radius:12px;background:var(--admin-surface);color:var(--admin-text);font-size:.88rem;transition:all .2s;}
.ult2-searchbox input:focus{outline:none;border-color:var(--admin-primary);box-shadow:0 0 0 3px rgba(26,82,118,.1);}
.ult2-viewtoggle{display:flex;background:var(--admin-bg);border:1px solid var(--admin-border);border-radius:12px;overflow:hidden;}
.ult2-viewtoggle button{background:none;border:none;padding:.6rem .9rem;cursor:pointer;color:var(--admin-text-secondary);font-size:.82rem;font-weight:700;display:flex;align-items:center;gap:.4rem;transition:all .2s;}
.ult2-viewtoggle button.active{background:var(--admin-primary);color:#fff;}
.ult2-viewtoggle button:hover:not(.active){background:var(--admin-border);}

/* ===== SECURITY NOTICE ===== */
.ult2-notice{display:flex;gap:.85rem;align-items:flex-start;background:linear-gradient(135deg,#fef3c7,#fde68a);border:1px solid #f59e0b;border-radius:14px;padding:1rem 1.25rem;margin-bottom:1rem;}
.ult2-notice i{font-size:1.4rem;color:#b45309;flex-shrink:0;}
.ult2-notice strong{display:block;color:#92400e;font-size:.9rem;margin-bottom:.15rem;}
.ult2-notice p{margin:0;font-size:.8rem;color:#78350f;line-height:1.55;}

/* ===== GROUP SECTIONS ===== */
.ult2-group{margin-bottom:1.5rem;border:1px solid var(--admin-border);border-radius:16px;overflow:hidden;background:var(--admin-surface);}
.ult2-group-head{display:flex;align-items:center;gap:.85rem;padding:1rem 1.4rem;background:linear-gradient(to right,var(--admin-bg),transparent);cursor:pointer;user-select:none;transition:background .2s;}
.ult2-group-head:hover{background:var(--admin-bg);}
.ult2-group-icon{width:40px;height:40px;border-radius:11px;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0;}
.ult2-group-title{font-size:1rem;font-weight:800;color:var(--admin-text);flex:1;}
.ult2-group-count{font-size:.72rem;font-weight:700;background:var(--admin-bg);border:1px solid var(--admin-border);color:var(--admin-text-secondary);padding:.2rem .65rem;border-radius:20px;}
.ult2-group-chev{color:var(--admin-text-muted);transition:transform .3s;}
.ult2-group.collapsed .ult2-group-chev{transform:rotate(-90deg);}
.ult2-group.collapsed .ult2-group-body{display:none;}
.ult2-table{width:100%;border-collapse:collapse;}
.ult2-table th{font-size:.7rem;text-transform:uppercase;letter-spacing:.8px;color:var(--admin-text-muted);text-align:left;padding:.7rem 1.4rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);}
.ult2-table td{padding:.85rem 1.4rem;border-bottom:1px solid var(--admin-border);vertical-align:top;font-size:.86rem;color:var(--admin-text);}
.ult2-table tr:last-child td{border-bottom:none;}
.ult2-table tr:hover td{background:rgba(26,82,118,.025);}

/* Key cell */
.ult2-keycell{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
.ult2-key{font-family:ui-monospace,monospace;font-size:.78rem;font-weight:700;color:var(--admin-primary);background:rgba(26,82,118,.08);border:1px solid rgba(26,82,118,.15);padding:.2rem .6rem;border-radius:7px;}
.ult2-copy{background:none;border:none;color:var(--admin-text-muted);cursor:pointer;font-size:.8rem;padding:.2rem;transition:color .2s;}
.ult2-copy:hover{color:var(--admin-primary);}

/* Inputs */
.ult2-input{width:100%;padding:.6rem .85rem;border:1.5px solid var(--admin-border);border-radius:10px;font-size:.86rem;background:var(--admin-bg);color:var(--admin-text);transition:all .2s;font-family:inherit;}
.ult2-input:focus{outline:none;border-color:var(--admin-primary);background:var(--admin-surface);box-shadow:0 0 0 3px rgba(26,82,118,.1);}
.ult2-textarea{resize:vertical;min-height:90px;line-height:1.6;}
.ult2-ta-wrap{position:relative;}
.ult2-counter{position:absolute;right:.6rem;bottom:.5rem;font-size:.66rem;color:var(--admin-text-muted);background:var(--admin-surface);padding:.05rem .4rem;border-radius:6px;}
.ult2-readonly{color:var(--admin-text-secondary);}
.ult2-badge{padding:.25rem .7rem;border-radius:20px;font-size:.72rem;font-weight:700;}
.ult2-badge.ok{background:rgba(16,185,129,.12);color:#059669;}
.ult2-badge.off{background:rgba(100,116,139,.12);color:#64748b;}
.ult2-type{display:inline-flex;align-items:center;gap:.35rem;font-size:.7rem;font-weight:700;color:var(--admin-text-muted);background:var(--admin-bg);border:1px solid var(--admin-border);padding:.2rem .6rem;border-radius:20px;}
.ult2-desc{font-size:.78rem;color:var(--admin-text-secondary);line-height:1.5;}
.ult2-mark{background:#fde68a;color:#92400e;border-radius:3px;padding:0 2px;}

/* Switch */
.ult2-switch{display:inline-flex;align-items:center;gap:.6rem;cursor:pointer;}
.ult2-switch input[type="checkbox"]{position:absolute;opacity:0;width:0;height:0;}
.ult2-switch-track{width:44px;height:24px;border-radius:20px;background:var(--admin-border);position:relative;transition:background .25s;flex-shrink:0;}
.ult2-switch-thumb{position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 2px 6px rgba(0,0,0,.25);transition:transform .25s;}
.ult2-switch input:checked + .ult2-switch-track{background:#10b981;}
.ult2-switch input:checked + .ult2-switch-track .ult2-switch-thumb{transform:translateX(20px);}
.ult2-switch-label{font-size:.78rem;font-weight:700;}
.ult2-switch-label.on{color:#059669;} .ult2-switch-label.off{color:var(--admin-text-muted);}

/* File thumb in rows */
.ult2-row-file{display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;}
.ult2-thumb{width:52px;height:52px;object-fit:contain;border-radius:10px;border:1px solid var(--admin-border);background:#fff;padding:3px;}
.ult2-thumb-empty{display:flex;align-items:center;justify-content:center;color:#c3ced9;font-size:1.2rem;}
.ult2-mini-link{font-size:.74rem;font-weight:700;color:var(--admin-primary);text-decoration:none!important;display:inline-flex;align-items:center;gap:.3rem;}
.ult2-mini-link:hover{color:#e67e22;}

/* ===== CARDS VIEW ===== */
.ult2-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.1rem;}
.ult2-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:14px;overflow:hidden;transition:all .25s;}
.ult2-card:hover{border-color:var(--admin-primary);box-shadow:0 10px 28px rgba(0,0,0,.08);}
.ult2-card-head{display:flex;align-items:center;gap:.6rem;padding:.85rem 1.1rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);}
.ult2-card-head .ult2-key{flex:1;}
.ult2-card-body{padding:1.1rem;}
.ult2-card-desc{margin-top:.85rem;padding-top:.85rem;border-top:1px dashed var(--admin-border);font-size:.76rem;color:var(--admin-text-secondary);line-height:1.55;}

/* ===== ACTION BAR ===== */
.ult2-actionbar{position:sticky;bottom:0;z-index:60;background:var(--admin-surface);border-top:1px solid var(--admin-border);padding:.9rem 1.5rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;box-shadow:0 -6px 20px rgba(0,0,0,.06);margin:0 -1.5rem -1.5rem;border-radius:0 0 16px 16px;}
.ult2-actionbar-info{display:flex;align-items:center;gap:.7rem;font-size:.82rem;color:var(--admin-text-secondary);}
.ult2-changes{display:none;align-items:center;gap:.4rem;background:rgba(230,126,34,.12);color:#c2610a;border:1px solid rgba(230,126,34,.35);padding:.3rem .8rem;border-radius:20px;font-size:.76rem;font-weight:700;}
.ult2-changes.show{display:inline-flex;animation:ult2_pop .25s ease;}
@keyframes ult2_pop{from{transform:scale(.85);}to{transform:scale(1);}}
.ult2-actionbar-btns{display:flex;gap:.6rem;}
@media (max-width:768px){.ult2-actionbar{position:fixed;left:0;right:0;bottom:0;margin:0;border-radius:0;z-index:1000;}}

/* ===== EMPTY ===== */
.ult2-empty{text-align:center;padding:3.5rem 1.5rem;}
.ult2-empty-icon{width:90px;height:90px;margin:0 auto 1.2rem;border-radius:50%;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#1a5276;display:flex;align-items:center;justify-content:center;font-size:2.4rem;position:relative;}
.ult2-empty-icon::before{content:'';position:absolute;inset:-8px;border:2px dashed rgba(26,82,118,.3);border-radius:50%;animation:ult2_spin 18s linear infinite;}
@keyframes ult2_spin{to{transform:rotate(360deg);}}
.ult2-empty h3{margin:0 0 .35rem;font-size:1.15rem;color:var(--admin-text);}
.ult2-empty p{margin:0;color:var(--admin-text-secondary);font-size:.86rem;}

@media (max-width:640px){
    .ult2-hero{padding:1.5rem;}
    .ult2-studio-body{padding:1rem;}
    .ult2-table th:nth-child(4),.ult2-table td:nth-child(4){display:none;}
}
</style>

<!-- ===== HERO HEADER ===== -->
<div class="ult2-hero">
    <div class="ult2-hero-inner">
        <div class="ult2-hero-title">
            <div class="ult2-hero-icon"><i class="bi bi-sliders-fill"></i></div>
            <div>
                <h1>Pengaturan Sistem</h1>
                <p>Pusat kendali branding, konten, kontak & keamanan BAUK UNIMOF</p>
            </div>
        </div>
        <div class="ult2-hero-actions">
            <button type="button" class="ult2-hero-btn" id="ult2ExportJson"><i class="bi bi-download"></i> Export JSON</button>
            <a href="#ult2Studio" class="ult2-hero-btn"><i class="bi bi-magic"></i> Studio Branding</a>
            <?php if ($canEdit): ?>
            <button type="submit" form="ult2SettingsForm" class="ult2-hero-btn primary"><i class="bi bi-save-fill"></i> Simpan Semua</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== STATS ===== -->
<div class="ult2-stats">
    <div class="ult2-stat" style="--sc:#3b82f6;">
        <div class="ult2-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-gear-fill"></i></div>
        <div><div class="ult2-stat-label">Total Pengaturan</div><div class="ult2-stat-value"><?= number_format($statTotal) ?></div><div class="ult2-stat-sub">Seluruh sistem</div></div>
    </div>
    <div class="ult2-stat" style="--sc:#10b981;">
        <div class="ult2-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-folder-fill"></i></div>
        <div><div class="ult2-stat-label">Grup Aktif</div><div class="ult2-stat-value"><?= number_format($statGroups) ?></div><div class="ult2-stat-sub">Kategori pengaturan</div></div>
    </div>
    <div class="ult2-stat" style="--sc:#f59e0b;">
        <div class="ult2-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="bi bi-pencil-fill"></i></div>
        <div><div class="ult2-stat-label">Dapat Diedit</div><div class="ult2-stat-value"><?= number_format($statEditable) ?></div><div class="ult2-stat-sub">Text/Number/File</div></div>
    </div>
    <div class="ult2-stat" style="--sc:#ef4444;">
        <div class="ult2-stat-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="bi bi-shield-lock-fill"></i></div>
        <div><div class="ult2-stat-label">Keamanan</div><div class="ult2-stat-value"><?= number_format($statSecurity) ?></div><div class="ult2-stat-sub">Critical settings</div></div>
    </div>
    <div class="ult2-stat" style="--sc:#8b5cf6;">
        <div class="ult2-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-clock-history"></i></div>
        <div><div class="ult2-stat-label">Update Terakhir</div><div class="ult2-stat-value" style="font-size:.95rem;padding-top:.35rem;"><?= htmlspecialchars(ult2_rel_time($lastUpdated ?: null)) ?></div><div class="ult2-stat-sub"><?= $lastUpdated ? htmlspecialchars(date('d M Y H:i', strtotime($lastUpdated))) : '-' ?></div></div>
    </div>
</div>

<!-- ===== FORM UTAMA ===== -->
<?php if ($canEdit): ?>
<form method="POST" action="<?= url('admin/pengaturan/simpan') ?>" enctype="multipart/form-data" id="ultSettingsForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
<?php endif; ?>

<!-- ===== STUDIO BRANDING ===== -->
<div class="ult2-studio" id="ult2Studio">
    <div class="ult2-studio-head">
        <div class="ult2-studio-head-icon"><i class="bi bi-magic"></i></div>
        <div style="flex:1;">
            <h2>Studio Branding</h2>
            <p>Upload logo, favicon & foto — perubahan langsung terlihat di mockup preview</p>
        </div>
        <span class="ult2-badge ok"><i class="bi bi-lightning-charge-fill"></i> Live Preview</span>
    </div>
    <div class="ult2-studio-body">
        <!-- Live mockup -->
        <div class="ult2-mockup" aria-hidden="true">
            <div class="ult2-mockup-bar">
                <span class="ult2-mockup-dot" style="background:#ef4444;"></span>
                <span class="ult2-mockup-dot" style="background:#f59e0b;"></span>
                <span class="ult2-mockup-dot" style="background:#10b981;"></span>
                <div class="ult2-mockup-tab">
                    <img class="js-setting-preview" data-setting-key="site_favicon" src="<?= $__favVal !== '' ? htmlspecialchars(url('uploads/settings/' . $__favVal)) : '' ?>" alt="" style="<?= $__favVal !== '' ? '' : 'display:none' ?>">
                    <span class="ult2-mock-ph js-mock-favicon-ph" style="<?= $__favVal !== '' ? 'display:none' : '' ?>">B</span>
                    <span class="js-mock-title">BAUK UNIMOF</span>
                </div>
                <div class="ult2-mockup-url"><i class="bi bi-lock-fill"></i> bauk.unimof.ac.id</div>
            </div>
            <div class="ult2-mockup-nav">
                <img class="js-setting-preview" data-setting-key="site_logo" src="<?= $__logoVal !== '' ? htmlspecialchars(url('uploads/settings/' . $__logoVal)) : '' ?>" alt="" style="<?= $__logoVal !== '' ? '' : 'display:none' ?>">
                <span class="ult2-mock-logo-ph js-mock-logo-ph" style="<?= $__logoVal !== '' ? 'display:none' : '' ?>">B</span>
                <span class="ult2-mockup-brand js-mock-title">BAUK UNIMOF</span>
                <div class="ult2-mockup-menu"><span>Beranda</span><span>Profil</span><span>Layanan</span></div>
            </div>
            <div class="ult2-mockup-body"><span>— Pratinjau tampilan website publik —</span></div>
            <div class="ult2-mockup-caption"><i class="bi bi-info-circle-fill"></i> Mockup diperbarui otomatis saat file dipilih</div>
        </div>

        <!-- Upload cards -->
        <div class="ult2-studio-grid">
            <?php if (!empty($fileSettings)): ?>
                <?php foreach ($fileSettings as $fs): ?>
                    <?= ult2_render_input($fs, $canEdit, 'studio') ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="ult2-empty" style="grid-column:1/-1;padding:2rem;">
                    <div class="ult2-empty-icon"><i class="bi bi-image"></i></div>
                    <h3>Belum Ada Setting File</h3>
                    <p>Setting bertipe file (logo, favicon, dll) akan muncul di sini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== TABS GRUP ===== -->
<div class="ult2-tabs">
    <a href="<?= url('admin/pengaturan') ?><?= $searchQuery !== '' ? '?q=' . urlencode($searchQuery) : '' ?>" class="ult2-tab <?= $activeGroup === 'all' ? 'active' : '' ?>">
        <i class="bi bi-grid-fill"></i> Semua <span class="ult2-tab-count"><?= $statTotal ?></span>
    </a>
    <?php foreach ($groupMap as $gKey => $gInfo):
        $gCount = count($groupedSettings[$gKey] ?? []);
        if ($gCount === 0) continue;
        $gHref = url('admin/pengaturan') . '?group=' . $gKey . ($searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '');
    ?>
    <a href="<?= $gHref ?>" class="ult2-tab <?= $activeGroup === $gKey ? 'active' : '' ?>">
        <i class="bi <?= $gInfo['icon'] ?>"></i> <?= $gInfo['label'] ?> <span class="ult2-tab-count"><?= $gCount ?></span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ===== SECURITY NOTICE ===== -->
<?php if ($activeGroup === 'security'): ?>
<div class="ult2-notice">
    <i class="bi bi-shield-fill-exclamation"></i>
    <div>
        <strong>Zona Sensitif — Grup Keamanan</strong>
        <p>Perubahan pada grup ini mempengaruhi keamanan seluruh sistem (CSRF, lockout, kebijakan password). Pastikan Anda memahami dampak setiap perubahan sebelum menyimpan.</p>
    </div>
</div>
<?php endif; ?>

<!-- ===== TOOLBAR ===== -->
<div class="ult2-toolbar">
    <form method="GET" action="<?= url('admin/pengaturan') ?>" style="display:flex;gap:.75rem;flex:1;flex-wrap:wrap;align-items:center;">
        <?php if ($activeGroup !== 'all'): ?><input type="hidden" name="group" value="<?= htmlspecialchars($activeGroup) ?>"><?php endif; ?>
        <div class="ult2-searchbox">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Cari key, deskripsi, atau nilai... (filter instan)">
        </div>
        <button type="submit" class="ult2-hero-btn" style="background:var(--admin-primary);border-color:var(--admin-primary);"><i class="bi bi-funnel-fill"></i> Terapkan</button>
        <?php if ($searchQuery !== ''): ?>
            <a href="<?= url('admin/pengaturan') ?><?= $activeGroup !== 'all' ? '?group=' . $activeGroup : '' ?>" class="ult2-hero-btn" style="background:var(--admin-surface);color:var(--admin-text);"><i class="bi bi-x-circle"></i> Reset</a>
        <?php endif; ?>
    </form>
    <div class="ult2-viewtoggle" id="ult2ViewToggle">
        <button type="button" data-view="grouped" class="<?= $currentView === 'grouped' ? 'active' : '' ?>" title="Tampilan Grup"><i class="bi bi-folder-fill"></i></button>
        <button type="button" data-view="table" class="<?= $currentView === 'table' ? 'active' : '' ?>" title="Tampilan Tabel"><i class="bi bi-list-ul"></i></button>
        <button type="button" data-view="cards" class="<?= $currentView === 'cards' ? 'active' : '' ?>" title="Tampilan Kartu"><i class="bi bi-grid-3x3-gap"></i></button>
    </div>
</div>

<!-- =====================================================
     VIEW: GROUPED
     ===================================================== -->
<div id="ult2ViewGrouped" class="ult2-view">
    <?php
    $anyGroup = false;
    foreach ($groupedSettings as $group => $items):
        if ($activeGroup !== 'all' && $activeGroup !== $group) continue;
        $gInfo = $groupMap[$group] ?? ['label' => ucfirst($group), 'icon' => 'bi-folder-fill', 'color' => '#64748b'];
        $filtered = $items;
        if ($searchQuery !== '') {
            $filtered = array_filter($items, fn($s) => stripos($s['key'] ?? '', $searchQuery) !== false || stripos($s['description'] ?? '', $searchQuery) !== false || stripos($s['value'] ?? '', $searchQuery) !== false);
        }
        if (empty($filtered)) continue;
        $anyGroup = true;
    ?>
    <div class="ult2-group" data-group="<?= htmlspecialchars($group) ?>">
        <div class="ult2-group-head" data-toggle-group>
            <div class="ult2-group-icon" style="background:linear-gradient(135deg,<?= $gInfo['color'] ?>,<?= $gInfo['color'] ?>cc);"><i class="bi <?= $gInfo['icon'] ?>"></i></div>
            <div class="ult2-group-title"><?= $gInfo['label'] ?></div>
            <span class="ult2-group-count"><?= count($filtered) ?> pengaturan</span>
            <i class="bi bi-chevron-down ult2-group-chev"></i>
        </div>
        <div class="ult2-group-body">
            <table class="ult2-table">
                <thead><tr><th style="width:26%;">Key</th><th style="width:38%;">Nilai</th><th style="width:12%;">Tipe</th><th style="width:24%;">Keterangan</th></tr></thead>
                <tbody>
                <?php foreach ($filtered as $s): ?>
                    <tr>
                        <td>
                            <div class="ult2-keycell">
                                <span class="ult2-key"><?= ult2_hl((string)$s['key'], $searchQuery) ?></span>
                                <button type="button" class="ult2-copy" data-copy="<?= htmlspecialchars((string)$s['key'], ENT_QUOTES) ?>" title="Salin key"><i class="bi bi-clipboard"></i></button>
                            </div>
                        </td>
                        <td><?= ult2_render_input($s, $canEdit, 'row') ?></td>
                        <td><span class="ult2-type"><i class="bi <?= $typeIcon((string)$s['type']) ?>"></i> <?= ucfirst((string)$s['type']) ?></span></td>
                        <td class="ult2-desc"><?= ult2_hl((string)($s['description'] ?? '-'), $searchQuery) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$anyGroup): ?>
    <div class="ult2-empty">
        <div class="ult2-empty-icon"><i class="bi bi-search"></i></div>
        <h3>Tidak Ditemukan</h3>
        <p>Tidak ada pengaturan yang cocok dengan filter saat ini.</p>
    </div>
    <?php endif; ?>
</div>

<!-- =====================================================
     VIEW: TABLE
     ===================================================== -->
<div id="ult2ViewTable" class="ult2-view" style="display:none;">
    <div class="ult2-group">
        <table class="ult2-table">
            <thead><tr><th style="width:22%;">Key</th><th style="width:34%;">Nilai</th><th style="width:16%;">Grup</th><th style="width:10%;">Tipe</th><th style="width:18%;">Keterangan</th></tr></thead>
            <tbody>
            <?php
            $anyRow = false;
            foreach ($settings as $s):
                $g = $s['group'] ?? 'general';
                if ($activeGroup !== 'all' && $activeGroup !== $g) continue;
                if ($searchQuery !== '' && stripos($s['key'] ?? '', $searchQuery) === false && stripos($s['description'] ?? '', $searchQuery) === false) continue;
                $anyRow = true;
                $gInfo = $groupMap[$g] ?? ['label' => ucfirst($g), 'icon' => 'bi-folder-fill', 'color' => '#64748b'];
            ?>
            <tr>
                <td><div class="ult2-keycell"><span class="ult2-key"><?= ult2_hl((string)$s['key'], $searchQuery) ?></span><button type="button" class="ult2-copy" data-copy="<?= htmlspecialchars((string)$s['key'], ENT_QUOTES) ?>" title="Salin key"><i class="bi bi-clipboard"></i></button></div></td>
                <td><?= ult2_render_input($s, $canEdit, 'row') ?></td>
                <td><span class="ult2-badge" style="background:<?= $gInfo['color'] ?>18;color:<?= $gInfo['color'] ?>;"><i class="bi <?= $gInfo['icon'] ?>"></i> <?= $gInfo['label'] ?></span></td>
                <td><span class="ult2-type"><i class="bi <?= $typeIcon((string)$s['type']) ?>"></i> <?= ucfirst((string)$s['type']) ?></span></td>
                <td class="ult2-desc"><?= ult2_hl((string)($s['description'] ?? '-'), $searchQuery) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$anyRow): ?>
            <tr><td colspan="5"><div class="ult2-empty"><div class="ult2-empty-icon"><i class="bi bi-inbox"></i></div><h3>Tidak Ada Data</h3><p>Belum ada pengaturan yang cocok.</p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- =====================================================
     VIEW: CARDS
     ===================================================== -->
<div id="ult2ViewCards" class="ult2-view" style="display:none;">
    <div class="ult2-cards">
        <?php
        $anyCard = false;
        foreach ($settings as $s):
            $g = $s['group'] ?? 'general';
            if ($activeGroup !== 'all' && $activeGroup !== $g) continue;
            if ($searchQuery !== '' && stripos($s['key'] ?? '', $searchQuery) === false && stripos($s['description'] ?? '', $searchQuery) === false) continue;
            $anyCard = true;
            $gInfo = $groupMap[$g] ?? ['label' => ucfirst($g), 'icon' => 'bi-folder-fill', 'color' => '#64748b'];
        ?>
        <div class="ult2-card">
            <div class="ult2-card-head">
                <span class="ult2-key"><?= ult2_hl((string)$s['key'], $searchQuery) ?></span>
                <span class="ult2-type"><i class="bi <?= $typeIcon((string)$s['type']) ?>"></i> <?= ucfirst((string)$s['type']) ?></span>
            </div>
            <div class="ult2-card-body">
                <?= ult2_render_input($s, $canEdit, 'row') ?>
                <div class="ult2-card-desc">
                    <i class="bi <?= $gInfo['icon'] ?>" style="color:<?= $gInfo['color'] ?>;"></i> <strong><?= $gInfo['label'] ?></strong><br>
                    <?= ult2_hl((string)($s['description'] ?? 'Tanpa deskripsi'), $searchQuery) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (!$anyCard): ?>
        <div class="ult2-empty" style="grid-column:1/-1;"><div class="ult2-empty-icon"><i class="bi bi-inbox"></i></div><h3>Tidak Ada Data</h3><p>Belum ada pengaturan yang cocok.</p></div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== ACTION BAR ===== -->
<?php if ($canEdit): ?>
<div class="ult2-actionbar">
    <div class="ult2-actionbar-info">
        <i class="bi bi-info-circle-fill" style="color:var(--admin-primary);"></i>
        <span>Perubahan disimpan ke database</span>
        <span class="ult2-changes" id="ult2Changes"><i class="bi bi-exclamation-circle-fill"></i> <span id="ult2ChangeCount">0</span> perubahan belum disimpan</span>
    </div>
    <div class="ult2-actionbar-btns">
        <button type="reset" class="ult2-hero-btn" style="background:var(--admin-bg);color:var(--admin-text);border-color:var(--admin-border);" id="ult2ResetBtn"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
        <button type="submit" class="ult2-hero-btn primary" id="ult2SubmitBtn"><i class="bi bi-save-fill"></i> Simpan Semua Perubahan</button>
    </div>
</div>
</form>
<?php endif; ?>

<!-- =====================================================
     SCRIPTS
     ===================================================== -->
<script>
(function(){
    'use strict';

    var form = document.getElementById('ultSettingsForm');
    var canEdit = <?= $canEdit ? 'true' : 'false' ?>;

    // ===== VIEW SWITCHER (fix bug: disable input di view tersembunyi) =====
    var views = {
        grouped: document.getElementById('ult2ViewGrouped'),
        table:   document.getElementById('ult2ViewTable'),
        cards:   document.getElementById('ult2ViewCards')
    };
    function switchView(name) {
        Object.keys(views).forEach(function(k) {
            var el = views[k];
            if (!el) return;
            var active = (k === name);
            el.style.display = active ? '' : 'none';
            el.querySelectorAll('input, select, textarea').forEach(function(inp) {
                inp.disabled = !active;
            });
        });
        document.querySelectorAll('#ult2ViewToggle button').forEach(function(b) {
            b.classList.toggle('active', b.getAttribute('data-view') === name);
        });
        try { localStorage.setItem('ult2_settings_view', name); } catch(e) {}
        updateChanges();
    }
    document.getElementById('ult2ViewToggle')?.querySelectorAll('button[data-view]').forEach(function(btn) {
        btn.addEventListener('click', function(){ switchView(this.getAttribute('data-view')); });
    });
    // Restore: prioritas URL (?view=), lalu localStorage
    var initialView = '<?= htmlspecialchars($currentView, ENT_QUOTES) ?>';
    var stored = null;
    try { stored = localStorage.getItem('ult2_settings_view'); } catch(e) {}
    var urlHasView = window.location.search.indexOf('view=') !== -1;
    switchView(urlHasView ? initialView : (stored || initialView));

    // ===== CHANGE TRACKER =====
    var snapshot = '';
    function serializeActive() {
        if (!form) return '';
        var parts = [];
        new FormData(form).forEach(function(v, k) {
            if (typeof v === 'string') parts.push(k + '=' + v);
        });
        return parts.sort().join('&');
    }
    function updateChanges() {
        if (!form || !canEdit) return;
        var current = serializeActive();
        var changed = 0;
        if (snapshot !== '') {
            var a = snapshot.split('&'), b = current.split('&');
            var mapA = {};
            a.forEach(function(p){ var i = p.indexOf('='); mapA[p.slice(0,i)] = p.slice(i+1); });
            var mapB = {};
            b.forEach(function(p){ var i = p.indexOf('='); mapB[p.slice(0,i)] = p.slice(i+1); });
            Object.keys(mapB).forEach(function(k){ if (mapA[k] !== mapB[k]) changed++; });
            Object.keys(mapA).forEach(function(k){ if (!(k in mapB)) changed++; });
        }
        var badge = document.getElementById('ult2Changes');
        var cnt = document.getElementById('ult2ChangeCount');
        if (badge && cnt) {
            cnt.textContent = changed;
            badge.classList.toggle('show', changed > 0);
        }
        return changed;
    }
    if (form) {
        snapshot = serializeActive();
        form.addEventListener('input', function(){ updateChanges(); });
        form.addEventListener('change', function(){ updateChanges(); });
        form.addEventListener('reset', function(){ setTimeout(function(){ snapshot = serializeActive(); updateChanges(); }, 50); });
        window.addEventListener('beforeunload', function(e) {
            if (updateChanges() > 0) { e.preventDefault(); e.returnValue = ''; }
        });
        // Ctrl+S
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault();
                form.requestSubmit ? form.requestSubmit() : form.submit();
            }
        });
        document.getElementById('ult2ResetBtn')?.addEventListener('click', function(e) {
            if (!confirm('Batalkan semua perubahan yang belum disimpan?')) e.preventDefault();
        });
    }

    // ===== FILE PREVIEW (delegasi, tanpa inline handler) =====
    function applyPreview(key, dataUrl) {
        document.querySelectorAll('.js-setting-preview[data-setting-key="' + key + '"]').forEach(function(img) {
            img.src = dataUrl;
            img.style.display = '';
        });
        // Mockup placeholders
        if (key === 'site_logo') {
            document.querySelectorAll('.js-mock-logo-ph').forEach(function(el){ el.style.display = 'none'; });
        }
        if (key === 'site_favicon') {
            document.querySelectorAll('.js-mock-favicon-ph').forEach(function(el){ el.style.display = 'none'; });
        }
        document.querySelectorAll('.js-studio-ph[data-setting-key="' + key + '"]').forEach(function(ph){ ph.style.display = 'none'; });
    }
    document.addEventListener('change', function(e) {
        var input = e.target;
        if (input.matches && input.matches('input[type="file"][data-setting-key]')) {
            var key = input.getAttribute('data-setting-key');
            var file = input.files && input.files[0];
            var label = document.querySelector('[data-filelabel="' + key + '"]');
            if (!file) { if (label) { label.classList.remove('has-file'); label.innerHTML = '<i class="bi bi-paperclip"></i> Belum ada file baru dipilih'; } return; }
            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file maksimal 5MB!');
                input.value = '';
                return;
            }
            if (label) { label.classList.add('has-file'); label.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + file.name; }
            var reader = new FileReader();
            reader.onload = function(ev){ applyPreview(key, ev.target.result); };
            reader.readAsDataURL(file);
        }
    });

    // ===== DRAG & DROP di studio cards =====
    document.querySelectorAll('.ult2-studio-card').forEach(function(card) {
        var key = card.getAttribute('data-setting-key');
        var input = card.querySelector('input[type="file"]');
        if (!input) return;
        ['dragover','dragenter'].forEach(function(ev){
            card.addEventListener(ev, function(e){ e.preventDefault(); card.classList.add('dragover'); });
        });
        ['dragleave','drop'].forEach(function(ev){
            card.addEventListener(ev, function(e){ e.preventDefault(); card.classList.remove('dragover'); });
        });
        card.addEventListener('drop', function(e) {
            if (e.dataTransfer.files && e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });

    // ===== MOCKUP TITLE dari site_name =====
    function syncMockTitle() {
        var inp = form ? form.querySelector('input[name="settings[site_name]"]:not(:disabled)') : null;
        var val = inp && inp.value ? inp.value : 'BAUK UNIMOF';
        document.querySelectorAll('.js-mock-title').forEach(function(el){ el.textContent = val; });
    }
    if (form) form.addEventListener('input', function(e){
        if (e.target && e.target.name === 'settings[site_name]') syncMockTitle();
    });
    syncMockTitle();

    // ===== COPY KEY =====
    document.addEventListener('click', function(e) {
        var btn = e.target.closest ? e.target.closest('.ult2-copy') : null;
        if (!btn) return;
        var txt = btn.getAttribute('data-copy') || '';
        if (navigator.clipboard) {
            navigator.clipboard.writeText(txt).then(function(){
                var icon = btn.querySelector('i');
                if (icon) { icon.className = 'bi bi-check-lg'; setTimeout(function(){ icon.className = 'bi bi-clipboard'; }, 1200); }
                if (window.showToast) showToast('Key disalin: ' + txt, 'success');
            });
        }
    });

    // ===== TEXTAREA COUNTER =====
    document.querySelectorAll('.js-counter').forEach(function(ta) {
        var cnt = document.getElementById('cnt_' + ta.getAttribute('data-counter-for'));
        var upd = function(){ if (cnt) cnt.textContent = ta.value.length + ' / 5000'; };
        ta.addEventListener('input', upd);
        upd();
    });

    // ===== COLLAPSIBLE GROUPS (dengan memori) =====
    document.querySelectorAll('[data-toggle-group]').forEach(function(head) {
        var group = head.closest('.ult2-group');
        var gkey = group ? group.getAttribute('data-group') : '';
        try {
            if (localStorage.getItem('ult2_grp_' + gkey) === '1') group.classList.add('collapsed');
        } catch(e) {}
        head.addEventListener('click', function() {
            group.classList.toggle('collapsed');
            try { localStorage.setItem('ult2_grp_' + gkey, group.classList.contains('collapsed') ? '1' : '0'); } catch(e) {}
        });
    });

    // ===== EXPORT JSON =====
    document.getElementById('ult2ExportJson')?.addEventListener('click', function() {
        var obj = {};
        if (form) {
            new FormData(form).forEach(function(v, k) {
                if (typeof v === 'string' && k.indexOf('settings[') === 0) {
                    obj[k.replace('settings[','').replace(']','')] = v;
                }
            });
        }
        // tambahkan info file
        document.querySelectorAll('.ult2-studio-card').forEach(function(c){
            var k = c.getAttribute('data-setting-key');
            obj[k] = obj[k] || '(file)';
        });
        var blob = new Blob([JSON.stringify(obj, null, 2)], { type: 'application/json' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'bauk-settings-' + new Date().toISOString().slice(0,10) + '.json';
        a.click();
        URL.revokeObjectURL(a.href);
        if (window.showToast) showToast('Settings berhasil di-export', 'success');
    });
})();
</script>