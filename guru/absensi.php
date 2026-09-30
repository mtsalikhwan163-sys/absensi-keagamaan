<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['guru']);

$pdo = db();
$pageTitle = 'Dashboard Guru';
$user = currentUser();
$guruId = $user['guru_id'] ?? 0;

$totalJadwal = (int) $pdo->prepare('SELECT COUNT(*) FROM jadwal WHERE guru_id = ?')->execute([$guruId]);
$stat = $pdo->prepare('SELECT COUNT(*) FROM jadwal WHERE guru_id = ? AND tanggal = CURDATE()');
$stat->execute([$guruId]);
$jadwalHariIni = (int) $stat->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="icon bg-primary"><i class="fa-solid fa-calendar"></i></div>
                <div>
                    <div class="label">Jadwal Hari Ini</div>
                    <div class="value"><?php echo $jadwalHariIni; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <div class="icon bg-success"><i class="fa-solid fa-clipboard-check"></i></div>
                <div>
                    <div class="label">Jumlah Kehadiran</div>
                    <div class="value"><?php echo $pdo->query('SELECT COUNT(*) FROM absensi a LEFT JOIN jadwal j ON j.id = a.jadwal_id WHERE j.guru_id = ' . (int) $guruId . ' AND a.status = "H"')->fetchColumn(); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
