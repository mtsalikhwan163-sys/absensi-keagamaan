<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['guru']);

$pdo = db();
$pageTitle = 'Absensi Guru';
$user = currentUser();
$guruId = $user['guru_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_absensi'])) {
    verifyCsrf();
    $jadwalId = (int) ($_POST['jadwal_id'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
    $kelasId = (int) ($_POST['kelas_id'] ?? 0);

    $students = $pdo->prepare('SELECT s.id, s.nama, s.nis FROM siswa s WHERE s.kelas_id = ? AND s.status = "aktif" ORDER BY s.nama');
    $students->execute([$kelasId]);
    $rows = $students->fetchAll();

    foreach ($rows as $s) {
        $status = trim($_POST['status'][$s['id']] ?? 'H');
        $keterangan = trim($_POST['keterangan'][$s['id']] ?? '');
        $exists = $pdo->prepare('SELECT id FROM absensi WHERE siswa_id = ? AND jadwal_id = ? AND tanggal = ? LIMIT 1');
        $exists->execute([$s['id'], $jadwalId, $tanggal]);
        if ($exists->fetch()) {
            $stmt = $pdo->prepare('UPDATE absensi SET status=?, keterangan=?, jam=? WHERE siswa_id=? AND jadwal_id=? AND tanggal=?');
            $stmt->execute([$status, $keterangan, date('H:i:s'), $s['id'], $jadwalId, $tanggal]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO absensi (siswa_id, jadwal_id, tanggal, jam, status, keterangan, metode_absen) VALUES (?, ?, ?, ?, ?, ?, "manual")');
            $stmt->execute([$s['id'], $jadwalId, $tanggal, date('H:i:s'), $status, $keterangan]);
        }
    }

    setFlash('success', 'Absensi berhasil disimpan.');
    redirect('guru/absensi.php?jadwal_id=' . $jadwalId . '&tanggal=' . urlencode($tanggal));
}

$jadwalId = isset($_GET['jadwal_id']) ? (int) $_GET['jadwal_id'] : 0;
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

$jadwal = $pdo->prepare('SELECT j.*, k.nama_kegiatan, kk.nama_kelas, g.nama AS nama_guru FROM jadwal j LEFT JOIN kegiatan k ON k.id = j.kegiatan_id LEFT JOIN kelas kk ON kk.id = j.kelas_id LEFT JOIN guru g ON g.id = j.guru_id WHERE j.guru_id = ? ORDER BY j.tanggal DESC');
$jadwal->execute([$guruId]);
$jadwalList = $jadwal->fetchAll();

$selected = null;
if ($jadwalId > 0) {
    $selectedStmt = $pdo->prepare('SELECT j.*, k.nama_kegiatan, kk.nama_kelas FROM jadwal j LEFT JOIN kegiatan k ON k.id = j.kegiatan_id LEFT JOIN kelas kk ON kk.id = j.kelas_id WHERE j.id = ? AND j.guru_id = ?');
    $selectedStmt->execute([$jadwalId, $guruId]);
    $selected = $selectedStmt->fetch();
}

$students = [];
if ($selected) {
    $sStmt = $pdo->prepare('SELECT s.* FROM siswa s WHERE s.kelas_id = ? AND s.status = "aktif" ORDER BY s.nama');
    $sStmt->execute([$selected['kelas_id']]);
    $students = $sStmt->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <?php showFlash(); ?>
    <div class="card custom-card mb-4">
        <div class="card-header">Jadwal Saya</div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Jadwal</label>
                    <select name="jadwal_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Pilih Jadwal</option>
                        <?php foreach ($jadwalList as $item): ?>
                            <option value="<?php echo $item['id']; ?>" <?php echo ($jadwalId === (int) $item['id']) ? 'selected' : ''; ?>><?php echo e($item['nama_kegiatan'] . ' - ' . $item['nama_kelas'] . ' - ' . $item['tanggal']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?php echo e($tanggal); ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selected): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
            <input type="hidden" name="save_absensi" value="1">
            <input type="hidden" name="jadwal_id" value="<?php echo $selected['id']; ?>">
            <input type="hidden" name="tanggal" value="<?php echo e($tanggal); ?>">
            <input type="hidden" name="kelas_id" value="<?php echo $selected['kelas_id']; ?>">

            <div class="card custom-card">
                <div class="card-header">Absensi Kelas <?php echo e($selected['nama_kelas']); ?></div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $i => $s): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo e($s['nis']); ?></td>
                                    <td><?php echo e($s['nama']); ?></td>
                                    <td>
                                        <select name="status[<?php echo $s['id']; ?>]" class="form-select">
                                            <option value="H">H</option>
                                            <option value="S">S</option>
                                            <option value="I">I</option>
                                            <option value="A">A</option>
                                            <option value="D">D</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="keterangan[<?php echo $s['id']; ?>]" class="form-control" placeholder="Keterangan"></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Simpan Absensi</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
