<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin']);

$pageTitle = 'Dashboard Admin';
$pdo = db();

$totalSiswa = (int) $pdo->query('SELECT COUNT(*) FROM siswa WHERE status = "aktif"')->fetchColumn();
$totalKelas = (int) $pdo->query('SELECT COUNT(*) FROM kelas WHERE status = "aktif"')->fetchColumn();
$totalKegiatan = (int) $pdo->query('SELECT COUNT(*) FROM kegiatan WHERE status = "aktif"')->fetchColumn();
$hadirHariIni = (int) $pdo->query('SELECT COUNT(*) FROM absensi WHERE DATE(tanggal) = CURDATE() AND status = "H"')->fetchColumn();
$sakitHariIni = (int) $pdo->query('SELECT COUNT(*) FROM absensi WHERE DATE(tanggal) = CURDATE() AND status = "S"')->fetchColumn();
$izinHariIni = (int) $pdo->query('SELECT COUNT(*) FROM absensi WHERE DATE(tanggal) = CURDATE() AND status = "I"')->fetchColumn();
$alpaHariIni = (int) $pdo->query('SELECT COUNT(*) FROM absensi WHERE DATE(tanggal) = CURDATE() AND status = "A"')->fetchColumn();

$kelasData = $pdo->query('SELECT k.nama_kelas, COUNT(a.id) AS total, SUM(CASE WHEN a.status = "H" THEN 1 ELSE 0 END) AS hadir FROM kelas k LEFT JOIN siswa s ON s.kelas_id = k.id LEFT JOIN absensi a ON a.siswa_id = s.id AND DATE(a.tanggal) = CURDATE() GROUP BY k.id ORDER BY k.nama_kelas')->fetchAll();
$kegiatanData = $pdo->query('SELECT kg.nama_kegiatan, COUNT(a.id) AS total, SUM(CASE WHEN a.status = "H" THEN 1 ELSE 0 END) AS hadir FROM kegiatan kg LEFT JOIN jadwal j ON j.kegiatan_id = kg.id LEFT JOIN absensi a ON a.jadwal_id = j.id AND DATE(a.tanggal) = CURDATE() GROUP BY kg.id ORDER BY kg.nama_kegiatan')->fetchAll();
$bulanData = $pdo->query("SELECT DATE_FORMAT(tanggal, '%Y-%m') AS bulan, COUNT(*) AS total, SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) AS hadir FROM absensi GROUP BY DATE_FORMAT(tanggal, '%Y-%m') ORDER BY bulan DESC LIMIT 6")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="icon bg-primary"><i class="fa-solid fa-user-graduate"></i></div>
                <div>
                    <div class="label">Total Siswa</div>
                    <div class="value"><?php echo $totalSiswa; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="icon bg-success"><i class="fa-solid fa-school"></i></div>
                <div>
                    <div class="label">Total Kelas</div>
                    <div class="value"><?php echo $totalKelas; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="icon bg-warning"><i class="fa-solid fa-book-open"></i></div>
                <div>
                    <div class="label">Total Kegiatan</div>
                    <div class="value"><?php echo $totalKegiatan; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="icon bg-info"><i class="fa-solid fa-clipboard-check"></i></div>
                <div>
                    <div class="label">Kehadiran Hari Ini</div>
                    <div class="value"><?php echo $hadirHariIni; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="mini-card warning">
                <h6>Sakit</h6>
                <strong><?php echo $sakitHariIni; ?></strong>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="mini-card danger">
                <h6>Izin</h6>
                <strong><?php echo $izinHariIni; ?></strong>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="mini-card secondary">
                <h6>Alpa</h6>
                <strong><?php echo $alpaHariIni; ?></strong>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="mini-card success">
                <h6>Hadir</h6>
                <strong><?php echo $hadirHariIni; ?></strong>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card custom-card">
                <div class="card-header">Persentase Kehadiran</div>
                <div class="card-body">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card custom-card">
                <div class="card-header">Rekap Kehadiran Per Kelas</div>
                <div class="card-body">
                    <canvas id="kelasChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card custom-card">
                <div class="card-header">Rekap Kehadiran Per Kegiatan</div>
                <div class="card-body">
                    <canvas id="kegiatanChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card custom-card">
                <div class="card-header">Statistik Kehadiran Bulanan</div>
                <div class="card-body">
                    <canvas id="bulanChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const attendanceCtx = document.getElementById('attendanceChart');
new Chart(attendanceCtx, {
    type: 'doughnut',
    data: {
        labels: ['Hadir', 'Sakit', 'Izin', 'Alpa'],
        datasets: [{
            data: [<?php echo $hadirHariIni; ?>, <?php echo $sakitHariIni; ?>, <?php echo $izinHariIni; ?>, <?php echo $alpaHariIni; ?>],
            backgroundColor: ['#20c997', '#ffc107', '#fd7e14', '#dc3545']
        }]
    }
});

const kelasLabels = [<?php echo implode(',', array_map(function ($d) { return '"' . addslashes($d['nama_kelas']) . '"'; }, $kelasData)); ?>];
const kelasData = [<?php echo implode(',', array_column($kelasData, 'hadir')); ?>];
new Chart(document.getElementById('kelasChart'), {
    type: 'bar',
    data: {
        labels: kelasLabels,
        datasets: [{
            label: 'Kehadiran',
            data: kelasData,
            backgroundColor: '#4c8f70'
        }]
    }
});

const kegiatanLabels = [<?php echo implode(',', array_map(function ($d) { return '"' . addslashes($d['nama_kegiatan']) . '"'; }, $kegiatanData)); ?>];
const kegiatanData = [<?php echo implode(',', array_column($kegiatanData, 'hadir')); ?>];
new Chart(document.getElementById('kegiatanChart'), {
    type: 'line',
    data: {
        labels: kegiatanLabels,
        datasets: [{
            label: 'Jumlah Hadir',
            data: kegiatanData,
            borderColor: '#0d6efd',
            fill: false,
            tension: 0.3
        }]
    }
});

const bulanLabels = [<?php echo implode(',', array_map(function ($d) { return '"' . addslashes($d['bulan']) . '"'; }, $bulanData)); ?>];
const bulanData = [<?php echo implode(',', array_map(function ($d) { return (int) $d['hadir']; }, $bulanData)); ?>];
new Chart(document.getElementById('bulanChart'), {
    type: 'bar',
    data: {
        labels: bulanLabels,
        datasets: [{
            label: 'Hadir Bulanan',
            data: bulanData,
            backgroundColor: '#0dcaf0'
        }]
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
