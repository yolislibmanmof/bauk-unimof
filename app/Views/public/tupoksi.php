<?php
/**
 * BAUK UNIMOF - Halaman Tupoksi (ULTIMATE)
 * Tugas Pokok & Fungsi per unit kerja - dinamis dari DB
 */
$db = \App\Helpers\Database::getInstance();

$tupoksi = [];
try {
    $tupoksi = $db->fetchAll("SELECT * FROM tupoksi WHERE deleted_at IS NULL AND is_active = 1 ORDER BY sort_order ASC, id ASC");
} catch (\Throwable $__e) {}
?>

<style>
.ult-tupoksi-hero{background:linear-gradient(135deg,#0d2f4a,#1a5276 50%,#2980b9);color:#fff;padding:5rem 0 6rem;text-align:center;position:relative;overflow:hidden;}
.ult-tupoksi-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/svg%3E");}
.ult-tupoksi-hero > *{position:relative;z-index:2;}
.ult-tupoksi-hero h1{font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3.2rem);margin-bottom:1rem;}

.ult-tupoksi-section{padding:5rem 0;background:linear-gradient(180deg,#f8f9fa,#fff);}

.ult-tupoksi-grid{max-width:1200px;margin:3rem auto 0;display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;}
.ult-tp-card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.08);transition:all .4s;border:1px solid var(--color-border-light);}
.ult-tp-card:hover{transform:translateY(-8px);box-shadow:0 25px 60px rgba(0,0,0,.15);}

.ult-tp-header{padding:2rem;color:#fff;position:relative;overflow:hidden;}
.ult-tp-header::before{content:'';position:absolute;top:-50%;right:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.1) 0%,transparent 70%);}
.ult-tp-icon{width:70px;height:70px;border-radius:18px;background:rgba(255,255,255,.2);backdrop-filter:blur(10px);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:1rem;position:relative;z-index:2;border:1px solid rgba(255,255,255,.3);}
.ult-tp-kode{display:inline-block;font-size:.7rem;text-transform:uppercase;letter-spacing:2px;font-weight:700;background:rgba(255,255,255,.25);padding:.25rem .75rem;border-radius:20px;margin-bottom:.5rem;position:relative;z-index:2;}
.ult-tp-header h3{font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:.25rem;position:relative;z-index:2;}
.ult-tp-header p{font-size:.9rem;color:rgba(255,255,255,.9);margin:0;position:relative;z-index:2;}

.ult-tp-body{padding:2rem;}
.ult-tp-label{display:flex;align-items:center;gap:.5rem;font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;color:var(--color-text-muted);margin-bottom:.75rem;}
.ult-tp-label i{font-size:.9rem;}
.ult-tp-tugas{font-size:.98rem;color:var(--color-text);line-height:1.7;margin-bottom:1.5rem;padding:.75rem 1rem;background:var(--color-primary-50);border-left:4px solid var(--color-primary);border-radius:6px;}
.ult-tp-fungsi{list-style:none;padding:0;margin:0;}
.ult-tp-fungsi li{display:flex;gap:.75rem;padding:.625rem 0;color:var(--color-text-secondary);font-size:.92rem;line-height:1.6;border-bottom:1px dashed var(--color-border-light);}
.ult-tp-fungsi li:last-child{border-bottom:none;}
.ult-tp-fungsi-num{width:26px;height:26px;border-radius:50%;background:var(--color-primary-100);color:var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0;margin-top:2px;}

@media (max-width:768px){
    .ult-tp-header{padding:1.5rem;}
    .ult-tp-body{padding:1.5rem;}
}
</style>

<section class="ult-tupoksi-hero">
    <div class="container">
        <span class="section-badge" style="background:rgba(255,255,255,.15);color:#fff;"><i class="bi bi-clipboard-check-fill"></i> Tupoksi</span>
        <h1>Tugas Pokok & <span style="color:#f39c12;">Fungsi</span></h1>
        <p style="max-width:700px;margin:0 auto;color:rgba(255,255,255,.9);">Uraian tugas pokok dan fungsi setiap unit kerja dalam BAUK UNIMOF.</p>
    </div>
</section>

<section class="ult-tupoksi-section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-briefcase-fill"></i> Unit Kerja</span>
                <h2 class="section-title">Unit Kerja BAUK</h2>
                <p class="section-subtitle" style="margin:0 auto;">Pembagian tugas dan tanggung jawab tiap bagian</p>
            </div>
        </div>

        <?php if ($tupoksi): ?>
        <div class="ult-tupoksi-grid">
            <?php foreach ($tupoksi as $t):
                $warna = $t['warna'] ?? '#1a5276';
                $fungsiList = array_filter(array_map('trim', explode("\n", (string)$t['fungsi'])));
                // bersihkan nomor "1. ", "2. " dari tiap poin
                $fungsiList = array_map(function($p) {
                    return preg_replace('/^\d+[\.\)]\s*/', '', $p);
                }, $fungsiList);
            ?>
            <div class="ult-tp-card">
                <div class="ult-tp-header" style="background:linear-gradient(135deg,<?= htmlspecialchars($warna) ?>,<?= htmlspecialchars($warna) ?>dd);">
                    <div class="ult-tp-icon"><i class="bi <?= htmlspecialchars($t['icon']) ?>"></i></div>
                    <?php if (!empty($t['kode_unit'])): ?>
                        <span class="ult-tp-kode"><?= htmlspecialchars($t['kode_unit']) ?></span>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($t['unit_kerja']) ?></h3>
                    <p><?= htmlspecialchars($t['nama_jabatan']) ?></p>
                </div>
                <div class="ult-tp-body">
                    <div class="ult-tp-label"><i class="bi bi-bullseye"></i> Tugas Pokok</div>
                    <div class="ult-tp-tugas"><?= htmlspecialchars($t['tugas_pokok']) ?></div>

                    <?php if ($fungsiList): ?>
                    <div class="ult-tp-label"><i class="bi bi-list-check"></i> Fungsi</div>
                    <ul class="ult-tp-fungsi">
                        <?php foreach ($fungsiList as $i => $f): ?>
                        <li>
                            <span class="ult-tp-fungsi-num"><?= $i + 1 ?></span>
                            <span><?= htmlspecialchars($f) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-clipboard-x"></i><p>Data tupoksi belum tersedia.</p></div>
        <?php endif; ?>
    </div>
</section>