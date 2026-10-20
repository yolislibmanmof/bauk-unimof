<?php
/**
 * BAUK UNIMOF - Halaman Visi Misi (ULTIMATE)
 * Dinamis dari tabel settings
 */
$db = \App\Helpers\Database::getInstance();

$__cfg = [];
try {
    foreach ($db->fetchAll("SELECT `key`, `value` FROM settings") as $__s) {
        $__cfg[$__s['key']] = $__s['value'];
    }
} catch (\Throwable $__e) {}

$visi    = $__cfg['visi_text'] ?? '';
$misiRaw = $__cfg['misi_text'] ?? '';
$tujuanRaw = $__cfg['tujuan_text'] ?? '';

$misi   = array_filter(array_map('trim', explode("\n", $misiRaw)));
$tujuan = array_filter(array_map('trim', explode("\n", $tujuanRaw)));
?>

<style>
.ult-visi-hero{background:linear-gradient(135deg,#0d2f4a,#1a5276 50%,#2980b9);color:#fff;padding:5rem 0 6rem;text-align:center;position:relative;overflow:hidden;}
.ult-visi-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/svg%3E");z-index:1;}
.ult-visi-hero > *{position:relative;z-index:2;}
.ult-visi-hero h1{font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3.2rem);margin-bottom:1rem;}
.ult-visi-hero p{max-width:700px;margin:0 auto;color:rgba(255,255,255,.9);font-size:1.05rem;line-height:1.8;}

.ult-vm-section{padding:5rem 0;}
.ult-vm-section:nth-child(even){background:linear-gradient(180deg,#f8f9fa,#fff);}

.ult-visi-card{max-width:900px;margin:0 auto;background:#fff;border-radius:24px;box-shadow:0 20px 60px rgba(0,0,0,.1);padding:3.5rem 3rem;position:relative;overflow:hidden;}
.ult-visi-card::before{content:'';position:absolute;top:0;left:0;right:0;height:6px;background:linear-gradient(90deg,#1a5276,#e67e22,#27ae60);}
.ult-visi-icon{width:90px;height:90px;border-radius:24px;background:linear-gradient(135deg,#1a5276,#2980b9);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2.5rem;margin:0 auto 1.5rem;box-shadow:0 15px 35px rgba(26,82,118,.3);}
.ult-visi-card h2{font-family:'Playfair Display',serif;font-size:2rem;text-align:center;margin-bottom:1.5rem;color:var(--color-text);}
.ult-visi-text{font-size:1.15rem;line-height:1.9;color:var(--color-text-secondary);text-align:center;max-width:720px;margin:0 auto;padding:1.5rem 0;border-top:1px dashed var(--color-border-light);border-bottom:1px dashed var(--color-border-light);}

.ult-misi-list{max-width:900px;margin:0 auto;display:grid;gap:1rem;}
.ult-misi-item{background:#fff;border-radius:16px;padding:1.5rem 1.75rem;box-shadow:0 5px 20px rgba(0,0,0,.06);display:flex;gap:1.25rem;align-items:flex-start;transition:all .3s;border:1px solid var(--color-border-light);position:relative;overflow:hidden;}
.ult-misi-item::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,#e67e22,#f39c12);transform:scaleY(0);transform-origin:top;transition:transform .3s;}
.ult-misi-item:hover{transform:translateX(6px);box-shadow:0 10px 30px rgba(0,0,0,.1);}
.ult-misi-item:hover::before{transform:scaleY(1);}
.ult-misi-number{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:700;flex-shrink:0;box-shadow:0 5px 15px rgba(230,126,34,.3);}
.ult-misi-text{flex:1;font-size:1.02rem;color:var(--color-text);line-height:1.7;margin:0;}

.ult-tujuan-grid{max-width:1000px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.25rem;}
.ult-tujuan-card{background:#fff;border-radius:16px;padding:2rem 1.5rem;box-shadow:0 5px 20px rgba(0,0,0,.06);border:1px solid var(--color-border-light);transition:all .3s;position:relative;overflow:hidden;}
.ult-tujuan-card:hover{transform:translateY(-5px);box-shadow:0 15px 35px rgba(0,0,0,.1);border-color:#27ae60;}
.ult-tujuan-icon{width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#27ae60,#2ecc71);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:1rem;box-shadow:0 8px 20px rgba(39,174,96,.25);}
.ult-tujuan-card p{font-size:.95rem;color:var(--color-text-secondary);line-height:1.7;margin:0;}
.ult-tujuan-card .ult-tujuan-num{font-family:'Playfair Display',serif;font-size:3rem;font-weight:700;color:rgba(39,174,96,.15);position:absolute;top:.5rem;right:1rem;line-height:1;}

@media (max-width:768px){
    .ult-visi-card{padding:2rem 1.5rem;}
    .ult-misi-item{flex-direction:column;text-align:center;}
    .ult-misi-number{margin:0 auto;}
}
</style>

<section class="ult-visi-hero">
    <div class="container">
        <span class="section-badge" style="background:rgba(255,255,255,.15);color:#fff;"><i class="bi bi-compass-fill"></i> Arah Strategis</span>
        <h1>Visi, Misi & <span style="color:#f39c12;">Tujuan</span></h1>
        <p>Landasan strategis BAUK UNIMOF dalam mewujudkan tata kelola universitas yang baik dan berdaya saing.</p>
    </div>
</section>

<!-- VISI -->
<section class="ult-vm-section">
    <div class="container">
        <div class="ult-visi-card">
            <div class="ult-visi-icon"><i class="bi bi-eye-fill"></i></div>
            <h2>Visi</h2>
            <p class="ult-visi-text"><?= $visi !== '' ? nl2br(htmlspecialchars($visi)) : '<em>Visi belum diatur. Silakan atur di Admin → Pengaturan.</em>' ?></p>
        </div>
    </div>
</section>

<!-- MISI -->
<section class="ult-vm-section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-bullseye"></i> Misi Kami</span>
                <h2 class="section-title">Misi</h2>
                <p class="section-subtitle" style="margin:0 auto;">Langkah konkret mewujudkan visi BAUK UNIMOF</p>
            </div>
        </div>
        <?php if ($misi): ?>
        <div class="ult-misi-list">
            <?php foreach ($misi as $i => $m): ?>
            <div class="ult-misi-item">
                <div class="ult-misi-number"><?= $i + 1 ?></div>
                <p class="ult-misi-text"><?= htmlspecialchars($m) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-info-circle"></i><p>Misi belum diatur.</p></div>
        <?php endif; ?>
    </div>
</section>

<!-- TUJUAN -->
<section class="ult-vm-section">
    <div class="container">
        <div class="section-header text-center">
            <div>
                <span class="section-badge"><i class="bi bi-target"></i> Tujuan</span>
                <h2 class="section-title">Tujuan</h2>
                <p class="section-subtitle" style="margin:0 auto;">Hasil yang ingin dicapai dalam jangka menengah</p>
            </div>
        </div>
        <?php if ($tujuan): ?>
        <div class="ult-tujuan-grid">
            <?php foreach ($tujuan as $i => $t): ?>
            <div class="ult-tujuan-card">
                <span class="ult-tujuan-num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <div class="ult-tujuan-icon"><i class="bi bi-check2-circle"></i></div>
                <p><?= htmlspecialchars($t) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-info-circle"></i><p>Tujuan belum diatur.</p></div>
        <?php endif; ?>
    </div>
</section>