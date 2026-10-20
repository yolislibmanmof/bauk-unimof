<?php
// ===== EXTENDED STATS =====
$db = \App\Helpers\Database::getInstance();
try {
    $statPegawai       = $stats['total_pegawai'] ?? 0;
    $statAset          = $stats['total_aset'] ?? 0;
    $statAsetBaik      = $stats['aset_baik'] ?? 0;
    $statAsetRusak     = $stats['aset_rusak'] ?? 0;
    $statPemasukan     = (float)($stats['pemasukan'] ?? 0);
    $statPengeluaran   = (float)($stats['pengeluaran'] ?? 0);
    $statPending       = $stats['transaksi_pending'] ?? 0;
    $statSaldo         = $statPemasukan - $statPengeluaran;
    
    // Additional stats
    $statBerita        = $db->count('berita', "is_published = 1 AND deleted_at IS NULL");
    $statPengumuman    = $db->count('pengumuman', "is_active = 1");
    $statPesanBaru     = $db->count('contact_messages', "is_read = 0");
    $statKegiatan      = $db->count('kegiatan', "status != 'cancelled' AND start_datetime >= NOW()");
} catch (\Throwable $e) {
    $statPegawai = $statAset = $statAsetBaik = $statAsetRusak = 0;
    $statPemasukan = $statPengeluaran = $statPending = $statSaldo = 0;
    $statBerita = $statPengumuman = $statPesanBaru = $statKegiatan = 0;
}

$assetHealthPct = $statAset > 0 ? round(($statAsetBaik / $statAset) * 100, 1) : 0;
?>

<!-- ===== ULTIMATE STYLES ===== -->
<style>
/* ===== STATS GRID ===== */
.ult-lap-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:2rem;}
.ult-lap-stat{background:var(--admin-surface);border-radius:var(--radius-lg);padding:1.5rem;border:1px solid var(--admin-border);transition:all .3s;position:relative;overflow:hidden;}
.ult-lap-stat::before{content:'';position:absolute;top:0;left:0;width:100%;height:4px;background:var(--ult-color,#3b82f6);}
.ult-lap-stat:hover{transform:translateY(-4px);box-shadow:0 12px 35px rgba(0,0,0,.1);}
.ult-lap-stat-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;}
.ult-lap-stat-icon{width:48px;height:48px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.35rem;color:#fff;box-shadow:0 6px 15px rgba(0,0,0,.1);}
.ult-lap-stat-trend{font-size:.75rem;font-weight:700;padding:.25rem .625rem;border-radius:50px;display:inline-flex;align-items:center;gap:.25rem;}
.ult-lap-stat-trend.up{background:rgba(16,185,129,.12);color:#10b981;}
.ult-lap-stat-trend.down{background:rgba(239,68,68,.12);color:#ef4444;}
.ult-lap-stat-trend.neutral{background:rgba(59,130,246,.12);color:#3b82f6;}
.ult-lap-stat-value{font-size:2rem;font-weight:800;color:var(--admin-text);line-height:1;margin-bottom:.375rem;}
.ult-lap-stat-value.money{font-size:1.35rem;}
.ult-lap-stat-label{font-size:.8rem;color:var(--admin-text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.5px;}

/* ===== SECTION CARDS ===== */
.ult-lap-section{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1.5rem;}
.ult-lap-section-header{padding:1.25rem 1.5rem;background:linear-gradient(to right, var(--admin-bg), transparent);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-lap-section-header i{font-size:1.25rem;color:var(--admin-primary);}
.ult-lap-section-header h3{font-size:1.1rem;font-weight:700;margin:0;flex:1;}
.ult-lap-section-header .ult-lap-badge{font-size:.75rem;font-weight:700;padding:.25rem .75rem;border-radius:50px;background:var(--admin-primary);color:#fff;}
.ult-lap-section-body{padding:1.5rem;}

/* ===== FINANCE SUMMARY ===== */
.ult-lap-finance-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:1.5rem;}
.ult-lap-finance-item{background:var(--admin-bg);border-radius:var(--radius-md);padding:1.25rem;border:1px solid var(--admin-border);text-align:center;}
.ult-lap-finance-item.income{border-left:4px solid #10b981;}
.ult-lap-finance-item.expense{border-left:4px solid #ef4444;}
.ult-lap-finance-item.balance{border-left:4px solid var(--admin-primary);}
.ult-lap-finance-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:var(--admin-text-muted);font-weight:600;margin-bottom:.5rem;}
.ult-lap-finance-value{font-size:1.5rem;font-weight:800;margin-bottom:.25rem;}
.ult-lap-finance-value.income{color:#10b981;}
.ult-lap-finance-value.expense{color:#ef4444;}
.ult-lap-finance-value.balance{color:var(--admin-primary);}
.ult-lap-finance-sub{font-size:.75rem;color:var(--admin-text-muted);}

/* ===== PROGRESS BARS ===== */
.ult-lap-progress-item{margin-bottom:1.25rem;}
.ult-lap-progress-item:last-child{margin-bottom:0;}
.ult-lap-progress-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;}
.ult-lap-progress-label{font-size:.85rem;font-weight:600;color:var(--admin-text);display:flex;align-items:center;gap:.5rem;}
.ult-lap-progress-label i{color:var(--admin-primary);}
.ult-lap-progress-value{font-size:.85rem;font-weight:700;color:var(--admin-text);}
.ult-lap-progress-bar{height:8px;background:var(--admin-bg);border-radius:50px;overflow:hidden;}
.ult-lap-progress-fill{height:100%;border-radius:50px;transition:width 1s ease;background:linear-gradient(90deg,var(--admin-primary),var(--admin-primary-light));}
.ult-lap-progress-fill.success{background:linear-gradient(90deg,#10b981,#059669);}
.ult-lap-progress-fill.warning{background:linear-gradient(90deg,#f59e0b,#d97706);}
.ult-lap-progress-fill.danger{background:linear-gradient(90deg,#ef4444,#dc2626);}

/* ===== EXPORT BUTTONS ===== */
.ult-lap-export-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;}
.ult-lap-export-btn{background:var(--admin-bg);border:2px solid var(--admin-border);border-radius:var(--radius-lg);padding:1.25rem;text-align:center;cursor:pointer;transition:all .3s;display:flex;flex-direction:column;align-items:center;gap:.75rem;color:var(--admin-text)!important;text-decoration:none!important;}
.ult-lap-export-btn:hover{transform:translateY(-3px);box-shadow:0 8px 25px rgba(0,0,0,.1);border-color:var(--admin-primary);}
.ult-lap-export-btn i{font-size:2rem;color:var(--admin-primary);}
.ult-lap-export-btn strong{font-size:.9rem;font-weight:700;}
.ult-lap-export-btn small{font-size:.75rem;color:var(--admin-text-muted);}
.ult-lap-export-btn.excel:hover{border-color:#217346;}
.ult-lap-export-btn.excel i{color:#217346;}
.ult-lap-export-btn.pdf:hover{border-color:#dc2626;}
.ult-lap-export-btn.pdf i{color:#dc2626;}
.ult-lap-export-btn.print:hover{border-color:#64748b;}
.ult-lap-export-btn.print i{color:#64748b;}

/* ===== RESPONSIVE ===== */
@media (max-width:992px){
    .ult-lap-stats{grid-template-columns:repeat(2,1fr);}
}
@media (max-width:640px){
    .ult-lap-stats{grid-template-columns:1fr;}
    .ult-lap-finance-grid{grid-template-columns:1fr;}
    .ult-lap-export-grid{grid-template-columns:1fr 1fr;}
}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Laporan & Statistik</h1>
        <p class="page-subtitle">Ringkasan kinerja sistem BAUK UNIMOF</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <button class="btn btn-secondary" onclick="window.print()"><i class="bi bi-printer-fill"></i> Cetak Laporan</button>
        <a href="<?= url('admin/backup') ?>" class="btn btn-secondary"><i class="bi bi-database-fill"></i> Backup DB</a>
    </div>
</div>

<!-- ===== KEY METRICS ===== -->
<div class="ult-lap-stats">
    <div class="ult-lap-stat" style="--ult-color:#3b82f6;">
        <div class="ult-lap-stat-header">
            <div class="ult-lap-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-people-fill"></i></div>
            <span class="ult-lap-stat-trend neutral"><i class="bi bi-arrow-up-short"></i> SDM</span>
        </div>
        <div class="ult-lap-stat-value"><?= number_format($statPegawai) ?></div>
        <div class="ult-lap-stat-label">Pegawai Aktif</div>
    </div>
    
    <div class="ult-lap-stat" style="--ult-color:#10b981;">
        <div class="ult-lap-stat-header">
            <div class="ult-lap-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="bi bi-box-seam-fill"></i></div>
            <span class="ult-lap-stat-trend up"><i class="bi bi-arrow-up-short"></i> <?= $assetHealthPct ?>% sehat</span>
        </div>
        <div class="ult-lap-stat-value"><?= number_format($statAset) ?></div>
        <div class="ult-lap-stat-label">Total Aset</div>
    </div>
    
    <div class="ult-lap-stat" style="--ult-color:<?= $statSaldo >= 0 ? '#10b981' : '#ef4444' ?>;">
        <div class="ult-lap-stat-header">
            <div class="ult-lap-stat-icon" style="background:linear-gradient(135deg,<?= $statSaldo >= 0 ? '#10b981,#059669' : '#ef4444,#dc2626' ?>);"><i class="bi bi-<?= $statSaldo >= 0 ? 'piggy-bank-fill' : 'exclamation-triangle-fill' ?>"></i></div>
            <span class="ult-lap-stat-trend <?= $statSaldo >= 0 ? 'up' : 'down' ?>"><i class="bi bi-arrow-<?= $statSaldo >= 0 ? 'up' : 'down' ?>-short"></i> Saldo</span>
        </div>
        <div class="ult-lap-stat-value money">Rp <?= number_format($statSaldo, 0, ',', '.') ?></div>
        <div class="ult-lap-stat-label">Saldo Bersih</div>
    </div>
    
    <div class="ult-lap-stat" style="--ult-color:#f59e0b;">
        <div class="ult-lap-stat-header">
            <div class="ult-lap-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="bi bi-hourglass-split"></i></div>
            <span class="ult-lap-stat-trend warning"><i class="bi bi-clock-fill"></i> Pending</span>
        </div>
        <div class="ult-lap-stat-value"><?= number_format($statPending) ?></div>
        <div class="ult-lap-stat-label">Transaksi Pending</div>
    </div>
</div>

<!-- ===== FINANCE SUMMARY ===== -->
<div class="ult-lap-section">
    <div class="ult-lap-section-header">
        <i class="bi bi-cash-stack"></i>
        <h3>Ringkasan Keuangan</h3>
        <span class="ult-lap-badge">Real-time</span>
    </div>
    <div class="ult-lap-section-body">
        <div class="ult-lap-finance-grid">
            <div class="ult-lap-finance-item income">
                <div class="ult-lap-finance-label"><i class="bi bi-arrow-down-circle-fill" style="color:#10b981;"></i> Pemasukan</div>
                <div class="ult-lap-finance-value income">Rp <?= number_format($statPemasukan, 0, ',', '.') ?></div>
                <div class="ult-lap-finance-sub">Transaksi approved</div>
            </div>
            <div class="ult-lap-finance-item expense">
                <div class="ult-lap-finance-label"><i class="bi bi-arrow-up-circle-fill" style="color:#ef4444;"></i> Pengeluaran</div>
                <div class="ult-lap-finance-value expense">Rp <?= number_format($statPengeluaran, 0, ',', '.') ?></div>
                <div class="ult-lap-finance-sub">Transaksi approved</div>
            </div>
            <div class="ult-lap-finance-item balance">
                <div class="ult-lap-finance-label"><i class="bi bi-piggy-bank-fill" style="color:var(--admin-primary);"></i> Saldo Bersih</div>
                <div class="ult-lap-finance-value balance">Rp <?= number_format($statSaldo, 0, ',', '.') ?></div>
                <div class="ult-lap-finance-sub">Pemasukan - Pengeluaran</div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- ===== ASSET HEALTH ===== -->
    <div class="ult-lap-section">
        <div class="ult-lap-section-header">
            <i class="bi bi-box-seam-fill"></i>
            <h3>Kesehatan Aset</h3>
            <span class="ult-lap-badge"><?= $assetHealthPct ?>% Baik</span>
        </div>
        <div class="ult-lap-section-body">
            <div class="ult-lap-progress-item">
                <div class="ult-lap-progress-header">
                    <span class="ult-lap-progress-label"><i class="bi bi-check-circle-fill"></i> Kondisi Baik</span>
                    <span class="ult-lap-progress-value"><?= number_format($statAsetBaik) ?> unit</span>
                </div>
                <div class="ult-lap-progress-bar">
                    <div class="ult-lap-progress-fill success" style="width:<?= $assetHealthPct ?>%;"></div>
                </div>
            </div>
            <div class="ult-lap-progress-item">
                <div class="ult-lap-progress-header">
                    <span class="ult-lap-progress-label"><i class="bi bi-exclamation-triangle-fill"></i> Perlu Perhatian</span>
                    <span class="ult-lap-progress-value"><?= number_format($statAsetRusak) ?> unit</span>
                </div>
                <div class="ult-lap-progress-bar">
                    <div class="ult-lap-progress-fill warning" style="width:<?= $statAset > 0 ? round(($statAsetRusak / $statAset) * 100, 1) : 0 ?>%;"></div>
                </div>
            </div>
            <div class="ult-lap-progress-item">
                <div class="ult-lap-progress-header">
                    <span class="ult-lap-progress-label"><i class="bi bi-box-seam-fill"></i> Total Aset</span>
                    <span class="ult-lap-progress-value"><?= number_format($statAset) ?> unit</span>
                </div>
                <div class="ult-lap-progress-bar">
                    <div class="ult-lap-progress-fill" style="width:100%;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ===== ACTIVITY OVERVIEW ===== -->
    <div class="ult-lap-section">
        <div class="ult-lap-section-header">
            <i class="bi bi-activity"></i>
            <h3>Aktivitas Sistem</h3>
            <span class="ult-lap-badge">Live</span>
        </div>
        <div class="ult-lap-section-body">
            <div class="ult-lap-progress-item">
                <div class="ult-lap-progress-header">
                    <span class="ult-lap-progress-label"><i class="bi bi-newspaper"></i> Berita Dipublikasi</span>
                    <span class="ult-lap-progress-value"><?= number_format($statBerita) ?></span>
                </div>
                <div class="ult-lap-progress-bar">
                    <div class="ult-lap-progress-fill" style="width:<?= min(100, $statBerita * 5) ?>%;"></div>
                </div>
            </div>
            <div class="ult-lap-progress-item">
                <div class="ult-lap-progress-header">
                    <span class="ult-lap-progress-label"><i class="bi bi-megaphone-fill"></i> Pengumuman Aktif</span>
                    <span class="ult-lap-progress-value"><?= number_format($statPengumuman) ?></span>
                </div>
                <div class="ult-lap-progress-bar">
                    <div class="ult-lap-progress-fill success" style="width:<?= min(100, $statPengumuman * 10) ?>%;"></div>
                </div>
            </div>
            <div class="ult-lap-progress-item">
                <div class="ult-lap-progress-header">
                    <span class="ult-lap-progress-label"><i class="bi bi-envelope-fill"></i> Pesan Belum Dibaca</span>
                    <span class="ult-lap-progress-value" style="color:<?= $statPesanBaru > 0 ? 'var(--admin-danger)' : 'var(--admin-text)' ?>;"><?= number_format($statPesanBaru) ?></span>
                </div>
                <div class="ult-lap-progress-bar">
                    <div class="ult-lap-progress-fill <?= $statPesanBaru > 0 ? 'danger' : 'success' ?>" style="width:<?= min(100, $statPesanBaru * 10) ?>%;"></div>
                </div>
            </div>
            <div class="ult-lap-progress-item">
                <div class="ult-lap-progress-header">
                    <span class="ult-lap-progress-label"><i class="bi bi-calendar-event-fill"></i> Kegiatan Mendatang</span>
                    <span class="ult-lap-progress-value"><?= number_format($statKegiatan) ?></span>
                </div>
                <div class="ult-lap-progress-bar">
                    <div class="ult-lap-progress-fill" style="width:<?= min(100, $statKegiatan * 15) ?>%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== EXPORT SECTION ===== -->
<div class="ult-lap-section">
    <div class="ult-lap-section-header">
        <i class="bi bi-download"></i>
        <h3>Export Laporan</h3>
        <span class="ult-lap-badge">Download</span>
    </div>
    <div class="ult-lap-section-body">
        <p style="color:var(--admin-text-secondary);margin-bottom:1.25rem;font-size:.9rem;">Download laporan lengkap dalam format pilihan Anda untuk keperluan dokumentasi dan presentasi.</p>
        <div class="ult-lap-export-grid">
            <a href="<?= url('admin/pegawai/export') ?>" class="ult-lap-export-btn excel">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <strong>Data Pegawai</strong>
                <small>Format CSV / Excel</small>
            </a>
            <a href="<?= url('admin/keuangan/export') ?>" class="ult-lap-export-btn excel">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <strong>Transaksi Keuangan</strong>
                <small>Format CSV / Excel</small>
            </a>
            <button class="ult-lap-export-btn pdf" onclick="if(typeof showToast!=='undefined')showToast('Export PDF segera hadir','info');">
                <i class="bi bi-file-earmark-pdf-fill"></i>
                <strong>Laporan PDF</strong>
                <small>Dokumen lengkap</small>
            </button>
            <button class="ult-lap-export-btn print" onclick="window.print()">
                <i class="bi bi-printer-fill"></i>
                <strong>Cetak Laporan</strong>
                <small>Print-friendly</small>
            </button>
        </div>
    </div>
</div>

<!-- ===== PRINT STYLES ===== -->
<style>
@media print {
    .sidebar, .admin-header, .page-header .btn, .ult-lap-export-grid, .ult-lap-badge { display: none !important; }
    .main-wrapper { margin-left: 0 !important; }
    .ult-lap-stat, .ult-lap-section { break-inside: avoid; box-shadow: none !important; border: 1px solid #ddd !important; }
    body { background: #fff !important; }
}
</style>