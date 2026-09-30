<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['siswa']);

$pdo = db();
$pageTitle = 'Riwayat Kehadiran';
$user = currentUser();

$rows = $pdo->prepare('SELECT a.status, a.keterangan, a.jam, j.tanggal, kg.nama_kegiatan FROM absensi a LEFT JOIN jadwal j ON j.id = a.jadwal_id LEFT JOIN kegiatan kg ON kg.id = j.kegiatan_id WHERE a.siswa_id = ? ORDER BY j.tanggal DESC');
$rows->execute([$user['siswa_id']]);
$rows = $rows->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <div class="card custom-card">
        <div class="card-header">Riwayat Kehadiran</div>
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kegiatan</th>
                        <th>Status</th>
                        <th>Jam</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo e($r['tanggal']); ?></td>
                            <td><?php echo e($r['nama_kegiatan']); ?></td>
                            <td><?php echo e($r['status']); ?></td>
                            <td><?php echo e($r['jam']); ?></td>
                            <td><?php echo e($r['keterangan']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
