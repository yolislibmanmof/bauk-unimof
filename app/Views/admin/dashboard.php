<?php $u = \App\Helpers\Auth::user(); ?>
<div class="page-header">
    <div><h1 class="page-title">Dashboard</h1><p class="page-subtitle">Selamat datang kembali, <?= htmlspecialchars($u['full_name']) ?>!</p></div>
    <div class="page-actions"><span class="current-date"><i class="bi bi-calendar"></i> <?= date('l, d F Y') ?></span></div>
</div>

<div class="stats-row">
    <div class="stat-card stat-card-blue"><div class="stat-card-body"><div class="stat-card-info"><span class="stat-label">Total Pegawai</span><h3 class="stat-value"><?= number_format($stats['total_pegawai']) ?></h3><span class="stat-change positive">Pegawai aktif</span></div><div class="stat-card-icon"><i class="bi bi-people"></i></div></div></div>
    <div class="stat-card stat-card-green"><div class="stat-card-body"><div class="stat-card-info"><span class="stat-label">Total Aset</span><h3 class="stat-value"><?= number_format($stats['total_aset']) ?></h3><span class="stat-change positive">Aset terdaftar</span></div><div class="stat-card-icon"><i class="bi bi-box-seam"></i></div></div></div>
    <div class="stat-card stat-card-orange"><div class="stat-card-body"><div class="stat-card-info"><span class="stat-label">Transaksi Bulan Ini</span><h3 class="stat-value"><?= number_format($stats['transaksi_bulan_ini']) ?></h3><span class="stat-change neutral">Bulan berjalan</span></div><div class="stat-card-icon"><i class="bi bi-cash-stack"></i></div></div></div>
    <div class="stat-card stat-card-purple"><div class="stat-card-body"><div class="stat-card-info"><span class="stat-label">Pengumuman Aktif</span><h3 class="stat-value"><?= number_format($stats['pengumuman_aktif']) ?></h3><span class="stat-change neutral">Sedang tampil</span></div><div class="stat-card-icon"><i class="bi bi-megaphone"></i></div></div></div>
</div>

<div class="dashboard-grid">
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

<div class="dashboard-grid mt-4">
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="bi bi-calendar-event"></i> Agenda Mendatang</h3></div>
        <div class="card-body">
            <?php $events = \App\Helpers\Database::getInstance()->fetchAll("SELECT * FROM kegiatan WHERE start_datetime >= NOW() AND status != 'cancelled' ORDER BY start_datetime ASC LIMIT 5"); ?>
            <?php if ($events): ?><div class="events-list">
                <?php foreach ($events as $e): ?>
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
                <tr><td>Versi Aplikasi</td><td>v1.0.0</td></tr>
                <tr><td>Versi PHP</td><td><?= PHP_VERSION ?></td></tr>
                <tr><td>Database</td><td>MySQL / MariaDB</td></tr>
                <tr><td>Memory Usage</td><td><?= round(memory_get_usage() / 1048576, 2) ?> MB</td></tr>
                <tr><td>Base URL</td><td><?= htmlspecialchars(BASE_URL ?: '(root)') ?></td></tr>
            </table>
        </div>
    </div>
</div>