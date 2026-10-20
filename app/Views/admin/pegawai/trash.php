<?php use App\Helpers\Auth; $isSuper = Auth::hasRole('super_admin'); ?>
<div class="page-header">
    <div><h1 class="page-title">Tempat Sampah Pegawai</h1><p class="page-subtitle">Data yang dihapus sementara (soft delete) — dapat dipulihkan</p></div>
    <a href="<?= url('admin/pegawai') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="alert alert-success"><i class="bi bi-info-circle-fill"></i> Data di tempat sampah tidak tampil di laporan maupun pencarian publik. Hapus permanen hanya dapat dilakukan Super Admin dan tidak dapat dibatalkan.</div>

<div class="card">
    <div class="card-body data-table-wrapper">
        <table class="data-table">
            <thead><tr><th>NIP</th><th>Nama</th><th>Departemen</th><th>Dihapus Pada</th><th style="width:160px;">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($trash as $t): ?>
                <tr>
                    <td><code><?= htmlspecialchars($t['nip']) ?></code></td>
                    <td><?= htmlspecialchars($t['full_name']) ?></td>
                    <td><?= htmlspecialchars($t['department_name'] ?? '-') ?></td>
                    <td><?= date('d M Y H:i', strtotime($t['deleted_at'])) ?></td>
                    <td>
                        <div class="actions">
                            <a href="<?= url('admin/pegawai/pulihkan/' . $t['id']) ?>" class="btn-action view" title="Pulihkan"
                               onclick="return confirm('Pulihkan pegawai <?= htmlspecialchars(addslashes($t['full_name'])) ?>?');"><i class="bi bi-arrow-counterclockwise"></i></a>
                            <?php if ($isSuper): ?>
                            <a href="<?= url('admin/pegawai/hapus-permanen/' . $t['id']) ?>" class="btn-action delete" title="Hapus Permanen"
                               onclick="return confirm('PERHATIAN: Hapus permanen <?= htmlspecialchars(addslashes($t['full_name'])) ?>?\nTindakan ini TIDAK DAPAT dibatalkan!');"><i class="bi bi-x-octagon"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$trash): ?><tr><td colspan="5"><div class="empty-state-sm"><i class="bi bi-trash3"></i><p>Tempat sampah kosong</p></div></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>