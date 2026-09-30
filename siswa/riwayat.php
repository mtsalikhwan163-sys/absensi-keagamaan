<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['siswa']);

$pdo = db();
$pageTitle = 'Dashboard Siswa';
$user = currentUser();
$siswa = $pdo->prepare('SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id WHERE s.id = ?');
siswa->execute([$user['siswa_id']]);
$siswa = $siswa->fetch();

$absensi = $pdo->prepare('SELECT a.status, a.keterangan, a.jam, j.tanggal, kg.nama_kegiatan FROM absensi a LEFT JOIN jadwal j ON j.id = a.jadwal_id LEFT JOIN kegiatan kg ON kg.id = j.kegiatan_id WHERE a.siswa_id = ? ORDER BY j.tanggal DESC LIMIT 10');
$absensi->execute([$user['siswa_id']]);
$history = $absensi->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card custom-card">
                <div class="card-body text-center">
                    <?php if (!empty($siswa['foto'])): ?>
                        <img src="<?php echo e($siswa['foto']); ?>" class="avatar-xl">
                    <?php else: ?>
                        <div class="avatar-xl bg-light text-dark"><i class="fa-solid fa-user"></i></div>
                    <?php endif; ?>
                    <h4 class="mt-3"><?php echo e($siswa['nama']); ?></h4>
                    <p class="text-muted mb-0"><?php echo e($siswa['nama_kelas'] ?? '-'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card custom-card">
                <div class="card-header">Profil Siswa</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6"><strong>NIS:</strong> <?php echo e($siswa['nis']); ?></div>
                        <div class="col-md-6"><strong>NISN:</strong> <?php echo e($siswa['nisn']); ?></div>
                        <div class="col-md-6"><strong>Tempat Tanggal Lahir:</strong> <?php echo e($siswa['tempat_lahir']); ?>, <?php echo e($siswa['tanggal_lahir']); ?></div>
                        <div class="col-md-6"><strong>No HP:</strong> <?php echo e($siswa['no_hp']); ?></div>
                        <div class="col-md-6"><strong>Orang Tua:</strong> <?php echo e($siswa['nama_orangtua']); ?></div>
                        <div class="col-md-6"><strong>WhatsApp:</strong> <?php echo e($siswa['no_wa_orangtua']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card custom-card mt-4">
        <div class="card-header">Riwayat Kehadiran Terbaru</div>
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
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?php echo e($h['tanggal']); ?></td>
                            <td><?php echo e($h['nama_kegiatan']); ?></td>
                            <td><?php echo e($h['status']); ?></td>
                            <td><?php echo e($h['jam']); ?></td>
                            <td><?php echo e($h['keterangan']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
