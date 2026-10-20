<?php
$photoUrl = !empty($data['photo']) ? url('uploads/pegawai/' . $data['photo']) : null;
$statusMap = [
    'active'   => ['badge-success', 'Aktif', 'bi-check-circle-fill', '#10b981'],
    'inactive' => ['badge-warning', 'Nonaktif', 'bi-pause-circle-fill', '#f59e0b'],
    'retired'  => ['badge-info', 'Pensiun', 'bi-hourglass-split', '#3b82f6'],
    'resigned' => ['badge-danger', 'Keluar', 'bi-x-circle-fill', '#ef4444'],
];
[$badgeClass, $badgeLabel, $badgeIcon, $badgeColor] = $statusMap[$data['status']] ?? ['badge-info', $data['status'], 'bi-circle-fill', '#64748b'];

$masaKerja = $data['join_date'] ? floor((time() - strtotime($data['join_date'])) / (365 * 86400)) : null;
?>

<!-- ===== ULTIMATE DETAIL STYLES (Scoped) ===== -->
<style>
/* ===== PROFILE HEADER CARD ===== */
.ult-pgn-detail-profile{background:linear-gradient(135deg, var(--admin-surface) 0%, var(--admin-bg) 100%);border:1px solid var(--admin-border);border-radius:var(--radius-xl);padding:2rem;margin-bottom:1.5rem;position:relative;overflow:hidden;}
.ult-pgn-detail-profile::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg, var(--admin-primary), var(--admin-accent, #e67e22));}
.ult-pgn-detail-profile-grid{display:grid;grid-template-columns:auto 1fr;gap:2rem;align-items:center;}
@media (max-width:768px){.ult-pgn-detail-profile-grid{grid-template-columns:1fr;text-align:center;}}

.ult-pgn-detail-photo-wrap{position:relative;}
.ult-pgn-detail-photo{width:120px;height:120px;border-radius:var(--radius-xl);object-fit:cover;border:4px solid var(--admin-surface);box-shadow:0 8px 25px rgba(0,0,0,.1);}
.ult-pgn-detail-initial{width:120px;height:120px;border-radius:var(--radius-xl);display:flex;align-items:center;justify-content:center;font-size:3rem;font-weight:700;color:#fff;background:linear-gradient(135deg, <?= $badgeColor ?>, <?= $badgeColor ?>cc);border:4px solid var(--admin-surface);box-shadow:0 8px 25px rgba(0,0,0,.1);}

.ult-pgn-detail-info h2{font-size:1.75rem;font-weight:700;color:var(--admin-text);margin-bottom:.5rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;}
@media (max-width:768px){.ult-pgn-detail-info h2{justify-content:center;}}
.ult-pgn-detail-subtitle{font-size:1rem;color:var(--admin-text-secondary);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
@media (max-width:768px){.ult-pgn-detail-subtitle{justify-content:center;}}
.ult-pgn-detail-subtitle code{background:var(--admin-bg);padding:.25rem .625rem;border-radius:6px;font-weight:600;color:var(--admin-primary);border:1px solid var(--admin-border);}

.ult-pgn-detail-badges{display:flex;gap:.5rem;flex-wrap:wrap;}
@media (max-width:768px){.ult-pgn-detail-badges{justify-content:center;}}
.ult-pgn-pill{display:inline-flex;align-items:center;gap:.375rem;padding:.375rem .875rem;border-radius:50px;font-size:.8rem;font-weight:600;background:var(--admin-bg);color:var(--admin-text-secondary);border:1px solid var(--admin-border);}
.ult-pgn-pill.status{background:<?= $badgeColor ?>15;color:<?= $badgeColor ?>;border-color:<?= $badgeColor ?>33;}
.ult-pgn-pill.status i{font-size:.7rem;}

/* ===== DATA CARDS ===== */
.ult-pgn-detail-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(400px, 1fr));gap:1.5rem;}
@media (max-width:768px){.ult-pgn-detail-grid{grid-template-columns:1fr;}}
.ult-pgn-detail-card{background:var(--admin-surface);border:1px solid var(--admin-border);border-radius:var(--radius-lg);overflow:hidden;}
.ult-pgn-detail-card-header{padding:1.25rem 1.5rem;background:var(--admin-bg);border-bottom:1px solid var(--admin-border);display:flex;align-items:center;gap:.75rem;}
.ult-pgn-detail-card-header i{font-size:1.25rem;color:var(--admin-primary);}
.ult-pgn-detail-card-header h3{font-size:1.1rem;font-weight:700;color:var(--admin-text);margin:0;}
.ult-pgn-detail-card-body{padding:1.5rem;}

/* ===== INFO LIST ===== */
.ult-pgn-info-list{display:flex;flex-direction:column;gap:1rem;}
.ult-pgn-info-item{display:grid;grid-template-columns:160px 1fr;gap:1rem;align-items:start;padding-bottom:1rem;border-bottom:1px dashed var(--admin-border);}
.ult-pgn-info-item:last-child{border-bottom:none;padding-bottom:0;}
@media (max-width:768px){.ult-pgn-info-item{grid-template-columns:1fr;gap:.25rem;}}
.ult-pgn-info-label{font-size:.8rem;font-weight:600;color:var(--admin-text-muted);text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:.5rem;}
.ult-pgn-info-label i{font-size:.9rem;color:var(--admin-primary);width:16px;text-align:center;}
.ult-pgn-info-value{font-size:.95rem;font-weight:500;color:var(--admin-text);line-height:1.6;word-break:break-word;}
.ult-pgn-info-value.code{font-family:monospace;background:var(--admin-bg);padding:.125rem .375rem;border-radius:4px;font-size:.85rem;}

/* ===== PRINT STYLES ===== */
@media print {
    .sidebar, .admin-header, .page-header, .ult-pgn-sticky-actions, .btn { display: none !important; }
    .main-wrapper { margin-left: 0 !important; }
    .ult-pgn-detail-profile { box-shadow: none !important; border: 1px solid #ccc !important; background: #fff !important; }
    .ult-pgn-detail-card { break-inside: avoid; border: 1px solid #ccc !important; box-shadow: none !important; }
    body { background: #fff !important; color: #000 !important; }
}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Detail Pegawai</h1>
        <p class="page-subtitle">Profil lengkap data kepegawaian</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <button class="btn btn-secondary" onclick="window.print()"><i class="bi bi-printer-fill"></i> Cetak Profil</button>
        <a href="<?= url('admin/pegawai/edit/' . $data['id']) ?>" class="btn btn-primary"><i class="bi bi-pencil-fill"></i> Edit Data</a>
        <a href="<?= url('admin/pegawai') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
</div>

<!-- ===== PROFILE HEADER ===== -->
<div class="ult-pgn-detail-profile">
    <div class="ult-pgn-detail-profile-grid">
        <div class="ult-pgn-detail-photo-wrap">
            <?php if ($photoUrl): ?>
                <img src="<?= $photoUrl ?>" alt="<?= htmlspecialchars($data['full_name']) ?>" class="ult-pgn-detail-photo">
            <?php else: ?>
                <div class="ult-pgn-detail-initial"><?= strtoupper(substr($data['full_name'], 0, 1)) ?></div>
            <?php endif; ?>
        </div>
        <div class="ult-pgn-detail-info">
            <h2>
                <?= htmlspecialchars($data['full_name']) ?>
                <span class="ult-pgn-pill status"><i class="bi <?= $badgeIcon ?>"></i> <?= $badgeLabel ?></span>
            </h2>
            <div class="ult-pgn-detail-subtitle">
                <code><?= htmlspecialchars($data['nip']) ?></code>
                <span>&bull;</span>
                <span><?= htmlspecialchars($data['position'] ?? 'Belum ada jabatan') ?></span>
                <span>&bull;</span>
                <span><?= htmlspecialchars($data['department_name'] ?? 'Belum ada departemen') ?></span>
            </div>
            <div class="ult-pgn-detail-badges">
                <span class="ult-pgn-pill"><i class="bi bi-patch-check-fill"></i> <?= htmlspecialchars($data['employment_status']) ?></span>
                <span class="ult-pgn-pill"><i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($data['education_level'] ?? '-') ?></span>
                <?php if ($data['join_date']): ?>
                    <span class="ult-pgn-pill"><i class="bi bi-calendar-check-fill"></i> Masuk: <?= date('d M Y', strtotime($data['join_date'])) ?></span>
                    <?php if ($masaKerja !== null): ?>
                        <span class="ult-pgn-pill"><i class="bi bi-clock-history"></i> Masa Kerja: <?= $masaKerja ?> Tahun</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ===== DATA GRID ===== -->
<div class="ult-pgn-detail-grid">
    
    <!-- DATA PRIBADI -->
    <div class="ult-pgn-detail-card">
        <div class="ult-pgn-detail-card-header">
            <i class="bi bi-person-vcard-fill"></i>
            <h3>Data Pribadi</h3>
        </div>
        <div class="ult-pgn-detail-card-body">
            <div class="ult-pgn-info-list">
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-gender-ambiguous"></i> Jenis Kelamin</div>
                    <div class="ult-pgn-info-value"><?= $data['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-geo-alt-fill"></i> Tempat, Tanggal Lahir</div>
                    <div class="ult-pgn-info-value">
                        <?= htmlspecialchars($data['birth_place'] ?? '-') ?>, 
                        <?= $data['birth_date'] ? date('d F Y', strtotime($data['birth_date'])) : '-' ?>
                    </div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-star-fill"></i> Agama</div>
                    <div class="ult-pgn-info-value"><?= htmlspecialchars($data['religion'] ?? '-') ?></div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-heart-fill"></i> Status Pernikahan</div>
                    <div class="ult-pgn-info-value"><?= htmlspecialchars($data['marital_status'] ?? '-') ?></div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-telephone-fill"></i> Telepon</div>
                    <div class="ult-pgn-info-value">
                        <?= $data['phone'] ? '<a href="tel:' . htmlspecialchars($data['phone']) . '" style="color:var(--admin-primary);text-decoration:none;">' . htmlspecialchars($data['phone']) . '</a>' : '-' ?>
                    </div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-envelope-fill"></i> Email</div>
                    <div class="ult-pgn-info-value">
                        <?= $data['email'] ? '<a href="mailto:' . htmlspecialchars($data['email']) . '" style="color:var(--admin-primary);text-decoration:none;">' . htmlspecialchars($data['email']) . '</a>' : '-' ?>
                    </div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-house-door-fill"></i> Alamat</div>
                    <div class="ult-pgn-info-value"><?= nl2br(htmlspecialchars($data['address'] ?? '-')) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- DATA KEPEGAWAIAN -->
    <div class="ult-pgn-detail-card">
        <div class="ult-pgn-detail-card-header">
            <i class="bi bi-briefcase-fill"></i>
            <h3>Data Kepegawaian</h3>
        </div>
        <div class="ult-pgn-detail-card-body">
            <div class="ult-pgn-info-list">
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-hash"></i> NIP</div>
                    <div class="ult-pgn-info-value"><span class="code"><?= htmlspecialchars($data['nip']) ?></span></div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-card-text"></i> NIK / NUPTK</div>
                    <div class="ult-pgn-info-value">
                        <?= htmlspecialchars($data['nik'] ?? '-') ?> / <?= htmlspecialchars($data['nuptk'] ?? '-') ?>
                    </div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-building-fill"></i> Departemen</div>
                    <div class="ult-pgn-info-value"><?= htmlspecialchars($data['department_name'] ?? '-') ?></div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-briefcase-fill"></i> Jabatan</div>
                    <div class="ult-pgn-info-value"><?= htmlspecialchars($data['position'] ?? '-') ?></div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-patch-check-fill"></i> Status Kepegawaian</div>
                    <div class="ult-pgn-info-value"><?= htmlspecialchars($data['employment_status']) ?></div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-mortarboard-fill"></i> Pendidikan Terakhir</div>
                    <div class="ult-pgn-info-value"><?= htmlspecialchars($data['education_level'] ?? '-') ?></div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-calendar-check-fill"></i> Periode Kerja</div>
                    <div class="ult-pgn-info-value">
                        <?= $data['join_date'] ? date('d M Y', strtotime($data['join_date'])) : '-' ?>
                        s/d 
                        <?= $data['end_date'] ? date('d M Y', strtotime($data['end_date'])) : 'Sekarang' ?>
                    </div>
                </div>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-cash-stack"></i> Gaji Pokok</div>
                    <div class="ult-pgn-info-value" style="font-weight:700;color:var(--admin-success);">
                        Rp <?= number_format((float)$data['salary'], 0, ',', '.') ?>
                    </div>
                </div>
                <?php if (!empty($data['notes'])): ?>
                <div class="ult-pgn-info-item">
                    <div class="ult-pgn-info-label"><i class="bi bi-journal-text"></i> Catatan Internal</div>
                    <div class="ult-pgn-info-value" style="background:var(--admin-bg);padding:.75rem;border-radius:8px;font-size:.85rem;"><?= nl2br(htmlspecialchars($data['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</div>