<?php
/**
 * BAUK UNIMOF - Halaman Struktur Organisasi (ULTIMATE)
 * Tree hierarchy dinamis dari DB
 */
$db = \App\Helpers\Database::getInstance();

$__cfg = [];
try {
    foreach ($db->fetchAll("SELECT `key`, `value` FROM settings WHERE `key` = 'struktur_organisasi_photo'") as $__s) {
        $__cfg[$__s['key']] = $__s['value'];
    }
} catch (\Throwable $__e) {}

$strukturPhoto = !empty($__cfg['struktur_organisasi_photo']) ? url('uploads/settings/' . $__cfg['struktur_organisasi_photo']) : null;

// Ambil struktur aktif, urutkan per level
$allNodes = $db->fetchAll("SELECT * FROM struktur_organisasi WHERE deleted_at IS NULL AND is_active = 1 ORDER BY level ASC, sort_order ASC, id ASC");

// Group by level
$byLevel = [];
foreach ($allNodes as $n) {
    $byLevel[$n['level']][] = $n;
}
ksort($byLevel);
?>

<style>
.ult-struktur-hero{background:linear-gradient(135deg,#0d2f4a,#1a5276 50%,#2980b9);color:#fff;padding:5rem 0 6rem;text-align:center;position:relative;overflow:hidden;}
.ult-struktur-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/svg%3E");}
.ult-struktur-hero > *{position:relative;z-index:2;}
.ult-struktur-hero h1{font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3.2rem);margin-bottom:1rem;}

.ult-struktur-section{padding:5rem 0;background:#fff;}

/* Tree hierarchy */
.ult-org-tree{display:flex;flex-direction:column;align-items:center;gap:0;}
.ult-tree-level{display:flex;justify-content:center;gap:2rem;flex-wrap:wrap;position:relative;padding:1.5rem 0;}
.ult-tree-level::before{content:'';position:absolute;top:0;left:50%;width:2px;height:1.5rem;background:linear-gradient(180deg,transparent,#1a5276);transform:translateX(-50%);}
.ult-tree-level:first-child::before{display:none;}

.ult-tree-node{background:#fff;border-radius:16px;padding:1.5rem 1.75rem;text-align:center;min-width:220px;max-width:260px;box-shadow:0 10px 30px rgba(0,0,0,.08);border:2px solid var(--color-border-light);transition:all .3s;position:relative;}
.ult-tree-node:hover{transform:translateY(-6px);box-shadow:0 20px 50px rgba(0,0,0,.15);border-color:#1a5276;}
.ult-tree-node.leader{background:linear-gradient(135deg,#1a5276,#2980b9);color:#fff;border-color:#1a5276;min-width:280px;}
.ult-tree-node.leader:hover{box-shadow:0 20px 50px rgba(26,82,118,.4);}

.ult-node-avatar{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#f39c12,#e67e22);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 1rem;border:4px solid #fff;box-shadow:0 8px 20px rgba(0,0,0,.15);overflow:hidden;}
.ult-tree-node.leader .ult-node-avatar{width:100px;height:100px;font-size:2.5rem;border-color:rgba(255,255,255,.3);}
.ult-node-avatar img{width:100%;height:100%;object-fit:cover;}

.ult-node-jabatan{font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;color:#e67e22;margin-bottom:.5rem;}
.ult-tree-node.leader .ult-node-jabatan{color:#f39c12;}
.ult-node-nama{font-size:1.05rem;font-weight:700;margin-bottom:.25rem;}
.ult-tree-node.leader .ult-node-nama{font-size:1.2rem;}
.ult-node-nip{font-size:.8rem;color:var(--color-text-secondary);font-family:monospace;}
.ult-tree-node.leader .ult-node-nip{color:rgba(255,255,255,.8);}

/* Connector lines */
.ult-tree-connector{width:2px;height:2rem;background:linear-gradient(180deg,#1a5276,transparent);margin:0 auto;}
.ult-tree-hline{display:flex;justify-content:center;align-items:center;position:relative;height:2rem;}
.ult-tree-hline::before{content:'';position:absolute;top:0;left:15%;right:15%;height:2px;background:#1a5276;}

/* Official image (if uploaded) */
.ult-official-image{max-width:1000px;margin:3rem auto;background:#fff;border-radius:20px;padding:2rem;box-shadow:0 10px 30px rgba(0,0,0,.08);}
.ult-official-image img{width:100%;height:auto;border-radius:12px;}
.ult-official-image h3{text-align:center;margin-bottom:1rem;color:var(--color-text);}

@media (max-width:992px){
    .ult-tree-level{gap:1rem;}
    .ult-tree-node{min-width:180px;padding:1.25rem;}
    .ult-tree-hline::before{display:none;}
}
@media (max-width:640px){
    .ult-tree-level{flex-direction:column;align-items:center;}
    .ult-tree-node{width:100%;max-width:280px;}
}
</style>

<section class="ult-struktur-hero">
    <div class="container">
        <span class="section-badge" style="background:rgba(255,255,255,.15);color:#fff;"><i class="bi bi-diagram-3-fill"></i> Organisasi</span>
        <h1>Struktur <span style="color:#f39c12;">Organisasi</span></h1>
        <p style="max-width:700px;margin:0 auto;color:rgba(255,255,255,.9);">Susunan kepemimpinan dan pembagian tugas dalam Biro Administrasi Umum dan Keuangan UNIMOF.</p>
    </div>
</section>

<section class="ult-struktur-section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-people-fill"></i> Hierarki</span>
                <h2 class="section-title">Bagan Organisasi</h2>
                <p class="section-subtitle" style="margin:0 auto;">Garis komando dan koordinasi antar unit kerja BAUK</p>
            </div>
        </div>

        <?php if (!empty($byLevel)): ?>
        <div class="ult-org-tree">
            <?php foreach ($byLevel as $level => $nodes): ?>
                <?php if ($level > 1): ?><div class="ult-tree-connector"></div><?php endif; ?>
                <div class="ult-tree-level">
                    <?php foreach ($nodes as $n): ?>
                    <div class="ult-tree-node <?= $level === 1 ? 'leader' : '' ?>">
                        <div class="ult-node-avatar">
                            <?php if (!empty($n['foto']) && is_file(UPLOAD_DIR . 'struktur/' . $n['foto'])): ?>
                                <img src="<?= url('uploads/struktur/' . $n['foto']) ?>" alt="<?= htmlspecialchars($n['nama_pejabat']) ?>">
                            <?php else: ?>
                                <?php
                                $initial = $n['nama_pejabat'] ? strtoupper(substr($n['nama_pejabat'], 0, 1)) : strtoupper(substr($n['nama_jabatan'], 0, 1));
                                ?>
                                <span style="font-weight:700;"><?= $initial ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="ult-node-jabatan"><?= htmlspecialchars($n['nama_jabatan']) ?></div>
                        <?php if (!empty($n['nama_pejabat'])): ?>
                            <div class="ult-node-nama"><?= htmlspecialchars($n['nama_pejabat']) ?></div>
                        <?php else: ?>
                            <div class="ult-node-nama" style="color:var(--color-text-muted);font-style:italic;">(Belum diisi)</div>
                        <?php endif; ?>
                        <?php if (!empty($n['nip'])): ?>
                            <div class="ult-node-nip"><?= htmlspecialchars($n['nip']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-diagram-3"></i><p>Struktur organisasi belum ditambahkan.</p></div>
        <?php endif; ?>

        <?php if ($strukturPhoto): ?>
        <div class="ult-official-image">
            <h3><i class="bi bi-image"></i> Bagan Resmi</h3>
            <img src="<?= htmlspecialchars($strukturPhoto) ?>" alt="Struktur Organisasi Resmi BAUK UNIMOF">
        </div>
        <?php endif; ?>
    </div>
</section>