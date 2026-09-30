<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin']);

$pdo = db();
$pageTitle = 'Rekap Absensi';

$selectedKelas = $_GET['kelas_id'] ?? '';
$selectedSiswa = $_GET['siswa_id'] ?? '';
$selectedKegiatan = $_GET['kegiatan_id'] ?? '';
$selectedStatus = $_GET['status'] ?? '';
$start = $_GET['start_date'] ?? date('Y-m-01');
$end = $_GET['end_date'] ?? date('Y-m-d');

$where = [];
$params = [];

if ($selectedKelas !== '') {
    $where[] = 's.kelas_id = ?';
    $params[] = $selectedKelas;
}
if ($selectedSiswa !== '') {
    $where[] = 's.id = ?';
    $params[] = $selectedSiswa;
}
if ($selectedKegiatan !== '') {
    $where[] = 'j.kegiatan_id = ?';
    $params[] = $selectedKegiatan;
}
if ($selectedStatus !== '') {
    $where[] = 'a.status = ?';
    $params[] = $selectedStatus;
}
if ($start !== '') {
    $where[] = 'a.tanggal >= ?';
    $params[] = $start;
}
if ($end !== '') {
    $where[] = 'a.tanggal <= ?';
    $params[] = $end;
}

$sql = 'SELECT s.nis, s.nama, k.nama_kelas,
        SUM(CASE WHEN a.status = "H" THEN 1 ELSE 0 END) AS hadir,
        SUM(CASE WHEN a.status = "S" THEN 1 ELSE 0 END) AS sakit,
        SUM(CASE WHEN a.status = "I" THEN 1 ELSE 0 END) AS izin,
        SUM(CASE WHEN a.status = "A" THEN 1 ELSE 0 END) AS alpa,
        COUNT(a.id) AS total_pertemuan
        FROM siswa s
        LEFT JOIN kelas k ON k.id = s.kelas_id
        LEFT JOIN absensi a ON a.siswa_id = s.id
        LEFT JOIN jadwal j ON j.id = a.jadwal_id ' . (!empty($where) ? 'WHERE ' . implode(' AND ', $where) : '') . '
        GROUP BY s.id
        ORDER BY s.nama';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$kelasList = $pdo->query('SELECT * FROM kelas WHERE status = "aktif" ORDER BY nama_kelas')->fetchAll();
$siswaList = $pdo->query('SELECT * FROM siswa WHERE status = "aktif" ORDER BY nama')->fetchAll();
$kegiatanList = $pdo->query('SELECT * FROM kegiatan WHERE status = "aktif" ORDER BY nama_kegiatan')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <div class="card custom-card mb-4">
        <div class="card-header">Filter Rekap</div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2"><label class="form-label">Tanggal Mulai</label><input type="date" name="start_date" class="form-control" value="<?php echo e($start); ?>"></div>
                <div class="col-md-2"><label class="form-label">Tanggal Selesai</label><input type="date" name="end_date" class="form-control" value="<?php echo e($end); ?>"></div>
                <div class="col-md-2"><label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select">
                        <option value="">Semua</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?php echo $k['id']; ?>" <?php echo $selectedKelas == $k['id'] ? 'selected' : ''; ?>><?php echo e($k['nama_kelas']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Siswa</label>
                    <select name="siswa_id" class="form-select">
                        <option value="">Semua</option>
                        <?php foreach ($siswaList as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo $selectedSiswa == $s['id'] ? 'selected' : ''; ?>><?php echo e($s['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Kegiatan</label>
                    <select name="kegiatan_id" class="form-select">
                        <option value="">Semua</option>
                        <?php foreach ($kegiatanList as $k): ?>
                            <option value="<?php echo $k['id']; ?>" <?php echo $selectedKegiatan == $k['id'] ? 'selected' : ''; ?>><?php echo e($k['nama_kegiatan']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="H" <?php echo $selectedStatus === 'H' ? 'selected' : ''; ?>>Hadir</option>
                        <option value="S" <?php echo $selectedStatus === 'S' ? 'selected' : ''; ?>>Sakit</option>
                        <option value="I" <?php echo $selectedStatus === 'I' ? 'selected' : ''; ?>>Izin</option>
                        <option value="A" <?php echo $selectedStatus === 'A' ? 'selected' : ''; ?>>Alpa</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header">Hasil Rekap</div>
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
                        <th>Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $i => $r): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo e($r['nis']); ?></td>
                            <td><?php echo e($r['nama']); ?></td>
                            <td><?php echo e($r['nama_kelas'] ?? '-'); ?></td>
                            <td><?php echo (int) ($r['hadir'] ?? 0); ?></td>
                            <td><?php echo (int) ($r['sakit'] ?? 0); ?></td>
                            <td><?php echo (int) ($r['izin'] ?? 0); ?></td>
                            <td><?php echo (int) ($r['alpa'] ?? 0); ?></td>
                            <td>
                                <?php
                                $total = (int) ($r['total_pertemuan'] ?? 0);
                                $hadir = (int) ($r['hadir'] ?? 0);
                                $pct = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;
                                echo $pct . '%';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
