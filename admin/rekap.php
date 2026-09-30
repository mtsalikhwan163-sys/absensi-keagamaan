<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin']);

$pdo = db();
$pageTitle = 'Absensi Manual';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_absensi'])) {
    verifyCsrf();
    $jadwalId = (int) ($_POST['jadwal_id'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
    $metode = 'manual';

    if ($jadwalId <= 0) {
        setFlash('danger', 'Pilih jadwal absensi terlebih dahulu.');
        redirect('admin/absensi.php');
    }

    $siswaList = $pdo->prepare('SELECT s.id, s.nama, s.nis, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id WHERE s.status = "aktif" AND s.kelas_id = (SELECT kelas_id FROM jadwal WHERE id = ?) ORDER BY s.nama');
    $siswaList->execute([$jadwalId]);
    $students = $siswaList->fetchAll();

    foreach ($students as $s) {
        $status = strtoupper(trim($_POST['status'][$s['id']] ?? 'H'));
        $keterangan = trim($_POST['keterangan'][$s['id']] ?? '');
        $jam = date('H:i:s');

        $exists = $pdo->prepare('SELECT id FROM absensi WHERE siswa_id = ? AND jadwal_id = ? AND tanggal = ? LIMIT 1');
        $exists->execute([$s['id'], $jadwalId, $tanggal]);

        if ($exists->fetch()) {
            $stmt = $pdo->prepare('UPDATE absensi SET status=?, keterangan=?, jam=?, metode_absen=? WHERE siswa_id=? AND jadwal_id=? AND tanggal=?');
            $stmt->execute([$status, $keterangan, $jam, $metode, $s['id'], $jadwalId, $tanggal]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO absensi (siswa_id, jadwal_id, tanggal, jam, status, keterangan, metode_absen) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$s['id'], $jadwalId, $tanggal, $jam, $status, $keterangan, $metode]);
        }
    }

    setFlash('success', 'Absensi berhasil disimpan.');
    redirect('admin/absensi.php?jadwal_id=' . $jadwalId . '&tanggal=' . urlencode($tanggal));
}

$jadwalId = isset($_GET['jadwal_id']) ? (int) $_GET['jadwal_id'] : 0;
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

$jadwalList = $pdo->query('SELECT j.*, k.nama_kegiatan, kk.nama_kelas, g.nama AS nama_guru FROM jadwal j LEFT JOIN kegiatan k ON k.id = j.kegiatan_id LEFT JOIN kelas kk ON kk.id = j.kelas_id LEFT JOIN guru g ON g.id = j.guru_id ORDER BY j.tanggal DESC')->fetchAll();

$selected = null;
if ($jadwalId > 0) {
    $selected = $pdo->prepare('SELECT j.*, k.nama_kegiatan, kk.nama_kelas, g.nama AS nama_guru FROM jadwal j LEFT JOIN kegiatan k ON k.id = j.kegiatan_id LEFT JOIN kelas kk ON kk.id = j.kelas_id LEFT JOIN guru g ON g.id = j.guru_id WHERE j.id = ?');
    $selected->execute([$jadwalId]);
    $selected = $selected->fetch();
}

$students = [];
if ($selected) {
    $studentsQuery = $pdo->prepare('SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id WHERE s.kelas_id = ? AND s.status = "aktif" ORDER BY s.nama');
    $studentsQuery->execute([$selected['kelas_id']]);
    $students = $studentsQuery->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <?php showFlash(); ?>
    <div class="card custom-card mb-4">
        <div class="card-header">Pilih Jadwal Absensi</div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Jadwal</label>
                    <select name="jadwal_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Pilih Jadwal</option>
                        <?php foreach ($jadwalList as $j): ?>
                            <option value="<?php echo $j['id']; ?>" <?php echo ($jadwalId === (int) $j['id']) ? 'selected' : ''; ?>>
                                <?php echo e($j['nama_kegiatan'] . ' - ' . $j['nama_kelas'] . ' - ' . $j['tanggal']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
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
        <div class="card custom-card mb-4">
            <div class="card-header">Detail Kegiatan</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><strong>Nama Kegiatan:</strong> <?php echo e($selected['nama_kegiatan']); ?></div>
                    <div class="col-md-3"><strong>Tanggal:</strong> <?php echo e($selected['tanggal']); ?></div>
                    <div class="col-md-3"><strong>Jam:</strong> <?php echo e($selected['jam_mulai'] . ' - ' . $selected['jam_selesai']); ?></div>
                    <div class="col-md-3"><strong>Kelas:</strong> <?php echo e($selected['nama_kelas']); ?></div>
                    <div class="col-md-3 mt-2"><strong>Pembina:</strong> <?php echo e($selected['nama_guru'] ?? '-'); ?></div>
                </div>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
            <input type="hidden" name="save_absensi" value="1">
            <input type="hidden" name="jadwal_id" value="<?php echo $selected['id']; ?>">
            <input type="hidden" name="tanggal" value="<?php echo e($tanggal); ?>">

            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Daftar Siswa</span>
                    <button type="button" class="btn btn-success btn-sm hadir-semua">Hadir Semua</button>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
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
                                        <select name="status[<?php echo $s['id']; ?>]" class="form-select status-select">
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
                    <button type="reset" class="btn btn-outline-secondary">Reset</button>
                    <button type="submit" class="btn btn-primary">Simpan Absensi</button>
                    <button type="button" class="btn btn-info" onclick="window.print()">Cetak</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
