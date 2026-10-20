<?php
/**
 * =====================================================
 * BAUK UNIMOF - DASHBOARD ULTIMATE (dengan Chart.js)
 * =====================================================
 */
use App\Helpers\{Auth, Chart, Dashboard, Database};

$u = Auth::user();

// ===== DEFENSIVE DEFAULTS (aman walau route lama belum diupdate) =====
$financialTrend     = $financialTrend     ?? Dashboard::getFinancialTrend();
$periodComparison   = $periodComparison   ?? Dashboard::getPeriodComparison();
$unreadNotifications = $unreadNotifications ?? 0;
$recentNotifications = $recentNotifications ?? [];
$upcomingEvents     = $upcomingEvents     ?? Database::getInstance()->fetchAll(
    "SELECT * FROM kegiatan WHERE start_datetime >= NOW() AND status != 'cancelled' ORDER BY start_datetime ASC LIMIT 5"
);

// ===== SIAPKAN DATA CHART =====
$chartTrend = Chart::financialTrendChart($financialTrend);
$chartDept  = Chart::departmentDistributionChart($stats['pegawai_per_departemen'] ?? []);
$chartAset  = Chart::asetConditionChart($stats);

// ===== HELPERT PERTUMBUHAN =====
$growthIcon = fn(float $g) => $g >= 0
    ? '<i class="bi bi-arrow-up-right"></i> ' . $g . '%'
    : '<i class="bi bi-arrow-down-right"></i> ' . abs($g) . '%';
$growthStyle = fn(float $g) => $g >= 0 ? 'color:#1cc88a' : 'color:#e74a3b';
?>

<!-- ===== STYLE KHUSUS CHART (scoped, tidak ganggu CSS utama) ===== -->
<style>
    .chart-box { position: relative; height: 320px; width: 100%; }
    .chart-box-sm { position: relative; height: 260px; width: 100%; }
    .chart-fallback { display:flex; align-items:center; justify-content:center; height:100%;
        background:#f8f9fc; border:1px dashed #d1d3e2; border-radius:8px; color:#858796; font-size:.9rem; padding:20px; text-align:center; }
    .notif-wrap { position: relative; }
    .notif-bell { position:relative; display:inline-flex; align-items:center; justify-content:center;
        width:42px; height:42px; border-radius:10px; background:#fff; border:1px solid #e3e6f0;
        color:#4e73df; font-size:1.15rem; cursor:pointer; transition:.2s; }
    .notif-bell:hover { background:#4e73df; color:#fff; }
    .notif-count { position:absolute; top:-6px; right:-6px; min-width:20px; height:20px; padding:0 5px;
        border-radius:10px; background:#e74a3b; color:#fff; font-size:.68rem; font-weight:700;
        display:flex; align-items:center; justify-content:center; border:2px solid #fff; }
    .notif-dropdown { position:absolute; right:0; top:52px; width:330px; background:#fff; border:1px solid #e3e6f0;
        border-radius:12px; box-shadow:0 .8rem 2rem rgba(58,59,69,.18); z-index:1050; display:none; overflow:hidden; }
    .notif-dropdown.show { display:block; }
    .notif-dropdown-header { padding:12px 16px; background:#f8f9fc; border-bottom:1px solid #e3e6f0;
        font-weight:700; font-size:.85rem; color:#3a3b45; }
    .notif-item { display:flex; gap:10px; padding:12px 16px; border-bottom:1px solid #f1f2f6; font-size:.83rem; }
    .notif-item:last-child { border-bottom:0; }
    .notif-item .ni-icon { width:34px; height:34px; flex:0 0 34px; border-radius:50%;
        display:flex; align-items:center; justify-content:center; font-size:.9rem; }
    .ni-info { background:#e7f0fd; color:#4e73df; } .ni-success { background:#e6f9f0; color:#1cc88a; }
    .ni-warning { background:#fdf6e3; color:#f6c23e; } .ni-error { background:#fdecea; color:#e74a3b; }
    .ni-approval { background:#fdf6e3; color:#f6c23e; } .ni-system { background:#f1f2f6; color:#858796; }
    .notif-item p { margin:0; color:#3a3b45; } .notif-item small { color:#9ba0ad; }
    .stat-value-money { font-size: 1.35rem !important; }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Selamat datang kembali, <?= htmlspecialchars($u['full_name']) ?>! 🚀</p>
    </div>
    <div class="page-actions">
        <span class="current-date"><i class="bi bi-calendar"></i> <?= date('l, d F Y') ?></span>
        <!-- ===== LONCENG NOTIFIKASI ===== -->
        <div class="notif-wrap">
            <button type="button" class="notif-bell" id="notifBell" title="Notifikasi">
                <i class="bi bi-bell"></i>
                <?php if ($unreadNotifications > 0): ?>
                    <span class="notif-count"><?= $unreadNotifications > 99 ? '99+' : $unreadNotifications ?></span>
                <?php endif; ?>
            </button>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-dropdown-header">
                    <i class="bi bi-bell-fill"></i> Notifikasi
                    <?php if ($unreadNotifications > 0): ?>
                        <a href="<?= url('api/notifications/read-all') ?>" style="float:right;font-size:.75rem;color:#4e73df;"
                           onclick="event.preventDefault(); fetch(this.href, {method:'POST'}).then(()=>location.reload());">
                            Tandai dibaca
                        </a>
                    <?php endif; ?>
                </div>
                <?php if ($recentNotifications): ?>
                    <?php foreach ($recentNotifications as $n): ?>
                        <div class="notif-item">
                            <div class="ni-icon ni-<?= htmlspecialchars($n['type']) ?>">
                                <i class="bi bi-<?= $n['type'] === 'approval' ? 'check2-circle' : ($n['type'] === 'error' ? 'x-octagon' : ($n['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle')) ?>"></i>
                            </div>
                            <div>
                                <p><?= htmlspecialchars($n['title'] ?? ucfirst($n['type'])) ?></p>
                                <p><?= htmlspecialchars($n['message']) ?></p>
                                <small><?= date('d M Y H:i', strtotime($n['created_at'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="notif-item"><div class="ni-icon ni-system"><i class="bi bi-check2"></i></div>
                        <div><p>Tidak ada notifikasi baru</p><small>Semua sudah terbaca</small></div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ===== STAT CARD BARIS 1 (ASLI - TETAP) ===== -->
<div class="stats-row">
    <div class="stat-card stat-card-blue"><div class="stat-card-body"><div class="stat-card-info"><span class="stat-label">Total Pegawai</span><h3 class="stat-value"><?= number_format($stats['total_pegawai']) ?></h3><span class="stat-change positive">Pegawai aktif</span></div><div class="stat-card-icon"><i class="bi bi-people"></i></div></div></div>
    <div class="stat-card stat-card-green"><div class="stat-card-body"><div class="stat-card-info"><span class="stat-label">Total Aset</span><h3 class="stat-value"><?= number_format($stats['total_aset']) ?></h3><span class="stat-change positive">Aset terdaftar</span></div><div class="stat-card-icon"><i class="bi bi-box-seam"></i></div></div></div>
    <div class="stat-card stat-card-orange"><div class="stat-card-body"><div class="stat-card-info"><span class="stat-label">Transaksi Bulan Ini</span><h3 class="stat-value"><?= number_format($stats['transaksi_bulan_ini']) ?></h3><span class="stat-change neutral">Bulan berjalan</span></div><div class="stat-card-icon"><i class="bi bi-cash-stack"></i></div></div></div>
    <div class="stat-card stat-card-purple"><div class="stat-card-body"><div class="stat-card-info"><span class="stat-label">Pengumuman Aktif</span><h3 class="stat-value"><?= number_format($stats['pengumuman_aktif']) ?></h3><span class="stat-change neutral">Sedang tampil</span></div><div class="stat-card-icon"><i class="bi bi-megaphone"></i></div></div></div>
</div>

<!-- ===== 🆕 STAT CARD BARIS 2: KEUANGAN ULTIMATE ===== -->
<div class="stats-row">
    <div class="stat-card stat-card-green">
        <div class="stat-card-body"><div class="stat-card-info">
            <span class="stat-label">Pemasukan Bulan Ini</span>
            <h3 class="stat-value stat-value-money"><?= Dashboard::formatCurrency($periodComparison['pemasukan_current'] ?? 0) ?></h3>
            <span class="stat-change" style="<?= $growthStyle((float)($periodComparison['pemasukan_growth'] ?? 0)) ?>">
                <?= $growthIcon((float)($periodComparison['pemasukan_growth'] ?? 0)) ?> vs bulan lalu
            </span>
        </div><div class="stat-card-icon"><i class="bi bi-graph-up-arrow"></i></div></div>
    </div>
    <div class="stat-card stat-card-orange">
        <div class="stat-card-body"><div class="stat-card-info">
            <span class="stat-label">Pengeluaran Bulan Ini</span>
            <h3 class="stat-value stat-value-money"><?= Dashboard::formatCurrency($periodComparison['pengeluaran_current'] ?? 0) ?></h3>
            <span class="stat-change" style="<?= $growthStyle(-1 * (float)($periodComparison['pengeluaran_growth'] ?? 0)) ?>">
                <?= $growthIcon((float)($periodComparison['pengeluaran_growth'] ?? 0)) ?> vs bulan lalu
            </span>
        </div><div class="stat-card-icon"><i class="bi bi-graph-down-arrow"></i></div></div>
    </div>
    <div class="stat-card stat-card-blue">
        <div class="stat-card-body"><div class="stat-card-info">
            <span class="stat-label">Menunggu Approval</span>
            <h3 class="stat-value"><?= number_format($stats['transaksi_pending'] ?? 0) ?></h3>
            <span class="stat-change <?= ($stats['transaksi_pending'] ?? 0) > 0 ? 'neutral' : 'positive' ?>">
                <?= ($stats['transaksi_pending'] ?? 0) > 0 ? 'Perlu tindakan' : 'Semua beres' ?>
            </span>
        </div><div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div></div>
    </div>
    <div class="stat-card stat-card-purple">
        <div class="stat-card-body"><div class="stat-card-info">
            <span class="stat-label">Nilai Total Aset</span>
            <h3 class="stat-value stat-value-money"><?= Dashboard::formatCurrency($stats['nilai_total_aset'] ?? 0) ?></h3>
            <span class="stat-change positive">Aset tercatat</span>
        </div><div class="stat-card-icon"><i class="bi bi-safe"></i></div></div>
    </div>
</div>

<!-- ===== 🆕 GRAFIK UTAMA: TREN KEUANGAN 12 BULAN ===== -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title"><i class="bi bi-graph-up"></i> Tren Keuangan 12 Bulan Terakhir</h3>
        <span class="card-link" style="cursor:default;"><i class="bi bi-info-circle"></i> Status: Approved</span>
    </div>
    <div class="card-body">
        <div class="chart-box"><canvas id="chartTrend"></canvas></div>
    </div>
</div>

<!-- ===== 🆕 GRAFIK DONAT: PEGAWAI & ASET ===== -->
<div class="dashboard-grid mt-4">
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-pie-chart"></i> Distribusi Pegawai per Departemen</h3></div>
        <div class="card-body"><div class="chart-box-sm"><canvas id="chartDept"></canvas></div></div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-pie-chart-fill"></i> Kondisi Aset</h3></div>
        <div class="card-body"><div class="chart-box-sm"><canvas id="chartAset"></canvas></div></div>
    </div>
</div>

<!-- ===== KONTEN ASLI: AKTIVITAS & AKSI CEPAT ===== -->
<div class="dashboard-grid mt-4">
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-clock-history"></i> Aktivitas Terbaru</h3><a href="<?= url('admin/aktivitas') ?>" class="card-link">Lihat Semua</a></div>
        <div class="card-body">
            <div class="activity-list">
                <?php foreach ($recentActivities as $a): ?>
                    <div class="activity-item">
                        <div class="activity-avatar"><?= strtoupper(substr($a['full_name'] ?? 'S', 0, 1)) ?></div>
                        <div class="activity-content">
                            <p class="activity-text"><strong><?= htmlspecialchars($a['full_name'] ?? 'System') ?></strong> <?= htmlspecialchars($a['description']) ?></p>
                            <span class="activity-time"><i class="bi bi-clock"></i> <?= date('d M Y H:i', strtotime($a['created_at'])) ?></span>
                        </div>
                        <span class="activity-badge badge-<?= htmlspecialchars($a['action']) ?>"><?= ucfirst(htmlspecialchars($a['action'])) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (!$recentActivities): ?><div class="empty-state-sm"><i class="bi bi-inbox"></i><p>Belum ada aktivitas</p></div><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-lightning"></i> Aksi Cepat</h3></div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="<?= url('admin/pegawai') ?>" class="quick-action-item"><i class="bi bi-person-plus"></i><span>Data Pegawai</span></a>
                <a href="<?= url('admin/keuangan') ?>" class="quick-action-item"><i class="bi bi-plus-circle"></i><span>Transaksi</span></a>
                <a href="<?= url('admin/aset') ?>" class="quick-action-item"><i class="bi bi-box-seam"></i><span>Data Aset</span></a>
                <a href="<?= url('admin/berita') ?>" class="quick-action-item"><i class="bi bi-pencil-square"></i><span>Berita</span></a>
                <a href="<?= url('admin/pengumuman') ?>" class="quick-action-item"><i class="bi bi-megaphone"></i><span>Pengumuman</span></a>
                <a href="<?= url('admin/regulasi') ?>" class="quick-action-item"><i class="bi bi-file-earmark-plus"></i><span>Regulasi</span></a>
            </div>
        </div>
    </div>
</div>

<!-- ===== KONTEN ASLI: AGENDA & INFO SISTEM ===== -->
<div class="dashboard-grid mt-4">
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-calendar-event"></i> Agenda Mendatang</h3></div>
        <div class="card-body">
            <?php if ($upcomingEvents): ?><div class="events-list">
                <?php foreach ($upcomingEvents as $e): ?>
                    <div class="event-item">
                        <div class="event-date"><span class="event-day"><?= date('d', strtotime($e['start_datetime'])) ?></span><span class="event-month"><?= date('M', strtotime($e['start_datetime'])) ?></span></div>
                        <div class="event-info"><h4><?= htmlspecialchars($e['title']) ?></h4><p><i class="bi bi-clock"></i> <?= date('H:i', strtotime($e['start_datetime'])) ?> WITA</p><p><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($e['location'] ?? '-') ?></p></div>
                    </div>
                <?php endforeach; ?></div>
            <?php else: ?><div class="empty-state-sm"><i class="bi bi-calendar-x"></i><p>Tidak ada agenda mendatang</p></div><?php endif; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-info-circle"></i> Informasi Sistem</h3></div>
        <div class="card-body">
            <table class="info-table">
                <tr><td>Versi Aplikasi</td><td>v2.0.0 ULTIMATE 🚀</td></tr>
                <tr><td>Versi PHP</td><td><?= PHP_VERSION ?></td></tr>
                <tr><td>Database</td><td>MySQL / MariaDB</td></tr>
                <tr><td>Memory Usage</td><td><?= round(memory_get_usage() / 1048576, 2) ?> MB</td></tr>
                <tr><td>Base URL</td><td><?= htmlspecialchars(BASE_URL ?: '(root)') ?></td></tr>
                <tr><td>Login Hari Ini</td><td><?= number_format($stats['login_hari_ini'] ?? 0) ?> kali</td></tr>
            </table>
        </div>
    </div>
</div>

<!-- ===================================================== -->
<!-- ===== CHART.JS CDN + INITIALIZER (ANTI-ERROR) ===== -->
<!-- ===================================================== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<!-- OPSI OFFLINE: setelah download file chart.umd.min.js ke public/assets/js/, ganti baris atas dengan:
<script src="<?= asset('js/chart.umd.min.js') ?>"></script>
-->
<script>
// Inisialisasi chart dengan fallback jika CDN gagal (misal offline)
function baukInitChart(canvasId, config) {
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById(canvasId);
        if (!el) return;
        if (typeof Chart === 'undefined') {
            el.parentElement.innerHTML = '<div class="chart-fallback"><div>📊 Grafik tidak dapat dimuat.<br>Periksa koneksi internet (CDN Chart.js) atau aktifkan mode offline.</div></div>';
            return;
        }
        // Format angka rupiah di tooltip
        if (config.options && config.options.scales && config.options.scales.y) {
            config.options.scales.y.ticks = {
                callback: function (value) {
                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                }
            };
        }
        new Chart(el, config);
    });
}

// Toggle dropdown notifikasi
document.addEventListener('DOMContentLoaded', function () {
    var bell = document.getElementById('notifBell');
    var dd = document.getElementById('notifDropdown');
    if (bell && dd) {
        bell.addEventListener('click', function (e) {
            e.stopPropagation();
            dd.classList.toggle('show');
        });
        document.addEventListener('click', function (e) {
            if (!dd.contains(e.target) && e.target !== bell) dd.classList.remove('show');
        });
    }
});

// ===== RENDER 3 GRAFIK =====
baukInitChart('chartTrend', <?= json_encode($chartTrend, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
baukInitChart('chartDept',  <?= json_encode($chartDept,  JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
baukInitChart('chartAset',  <?= json_encode($chartAset,  JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
</script>