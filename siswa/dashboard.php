<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['guru']);

$pdo = db();
$pageTitle = 'Rekap Guru';
$user = currentUser();
$guruId = $user['guru_id'] ?? 0;

$rows = $pdo->prepare('SELECT s.nis, s.nama, k.nama_kelas, SUM(CASE WHEN a.status = "H" THEN 1 ELSE 0 END) AS hadir, SUM(CASE WHEN a.status = "S" THEN 1 ELSE 0 END) AS sakit, SUM(CASE WHEN a.status = "I" THEN 1 ELSE 0 END) AS izin, SUM(CASE WHEN a.status = "A" THEN 1 ELSE 0 END) AS alpa, COUNT(a.id) AS total FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id LEFT JOIN absensi a ON a.siswa_id = s.id LEFT JOIN jadwal j ON j.id = a.jadwal_id WHERE j.guru_id = ? GROUP BY s.id ORDER BY s.nama');
$rows->execute([$guruId]);
$rows = $rows->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <div class="card custom-card">
        <div class="card-header">Rekap Kehadiran Siswa</div>
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Hadir</th>
                        <th>Sakit</th>
                        <th>Izin</th>
                        <th>Alpa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $i => $r): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo e($r['nis']); ?></td>
                            <td><?php echo e($r['nama']); ?></td>
                            <td><?php echo e($r['nama_kelas']); ?></td>
                            <td><?php echo (int) ($r['hadir'] ?? 0); ?></td>
                            <td><?php echo (int) ($r['sakit'] ?? 0); ?></td>
                            <td><?php echo (int) ($r['izin'] ?? 0); ?></td>
                            <td><?php echo (int) ($r['alpa'] ?? 0); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
