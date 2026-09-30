<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin']);

$pdo = db();
$pageTitle = 'Jadwal Kegiatan';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_jadwal'])) {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $kegiatanId = (int) ($_POST['kegiatan_id'] ?? 0);
    $kelasId = (int) ($_POST['kelas_id'] ?? 0);
    $guruId = (int) ($_POST['guru_id'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? '');
    $jamMulai = trim($_POST['jam_mulai'] ?? '');
    $jamSelesai = trim($_POST['jam_selesai'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($kegiatanId === 0 || $kelasId === 0 || $tanggal === '') {
        setFlash('danger', 'Kegiatan, kelas, dan tanggal wajib diisi.');
        redirect('admin/jadwal.php');
    }

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE jadwal SET kegiatan_id=?, kelas_id=?, guru_id=?, tanggal=?, jam_mulai=?, jam_selesai=?, keterangan=? WHERE id=?');
        $stmt->execute([$kegiatanId, $kelasId, $guruId ?: null, $tanggal, $jamMulai, $jamSelesai, $keterangan, $id]);
        setFlash('success', 'Jadwal berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO jadwal (kegiatan_id, kelas_id, guru_id, tanggal, jam_mulai, jam_selesai, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$kegiatanId, $kelasId, $guruId ?: null, $tanggal, $jamMulai, $jamSelesai, $keterangan]);
        setFlash('success', 'Jadwal baru berhasil ditambahkan.');
    }

    redirect('admin/jadwal.php');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare('DELETE FROM jadwal WHERE id = ?')->execute([$id]);
    setFlash('success', 'Jadwal berhasil dihapus.');
    redirect('admin/jadwal.php');
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editData = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM jadwal WHERE id = ?');
    $stmt->execute([$editId]);
    $editData = $stmt->fetch();
}

$kegiatanList = $pdo->query('SELECT * FROM kegiatan WHERE status = "aktif" ORDER BY nama_kegiatan')->fetchAll();
$kelasList = $pdo->query('SELECT * FROM kelas WHERE status = "aktif" ORDER BY nama_kelas')->fetchAll();
$guruList = $pdo->query('SELECT * FROM guru WHERE status = "aktif" ORDER BY nama')->fetchAll();
$jadwal = $pdo->query('SELECT j.*, k.nama_kegiatan, kk.nama_kelas, g.nama AS nama_guru FROM jadwal j LEFT JOIN kegiatan k ON k.id = j.kegiatan_id LEFT JOIN kelas kk ON kk.id = j.kelas_id LEFT JOIN guru g ON g.id = j.guru_id ORDER BY j.tanggal DESC')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <?php showFlash(); ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card custom-card">
                <div class="card-header"><?php echo $editData ? 'Edit Jadwal' : 'Tambah Jadwal'; ?></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                        <input type="hidden" name="save_jadwal" value="1">
                        <input type="hidden" name="id" value="<?php echo e($editData['id'] ?? 0); ?>">
                        <div class="mb-3">
                            <label class="form-label">Kegiatan</label>
                            <select name="kegiatan_id" class="form-select" required>
                                <option value="">Pilih Kegiatan</option>
                                <?php foreach ($kegiatanList as $k): ?>
                                    <option value="<?php echo $k['id']; ?>" <?php echo (($editData['kegiatan_id'] ?? '') == $k['id']) ? 'selected' : ''; ?>><?php echo e($k['nama_kegiatan']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <select name="kelas_id" class="form-select" required>
                                <option value="">Pilih Kelas</option>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?php echo $k['id']; ?>" <?php echo (($editData['kelas_id'] ?? '') == $k['id']) ? 'selected' : ''; ?>><?php echo e($k['nama_kelas']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pembina</label>
                            <select name="guru_id" class="form-select">
                                <option value="">Pilih Pembina</option>
                                <?php foreach ($guruList as $g): ?>
                                    <option value="<?php echo $g['id']; ?>" <?php echo (($editData['guru_id'] ?? '') == $g['id']) ? 'selected' : ''; ?>><?php echo e($g['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" required value="<?php echo e($editData['tanggal'] ?? date('Y-m-d')); ?>"></div>
                        <div class="row">
                            <div class="col-md-6"><div class="mb-3"><label class="form-label">Jam Mulai</label><input type="time" name="jam_mulai" class="form-control" value="<?php echo e($editData['jam_mulai'] ?? '07:00'); ?>"></div></div>
                            <div class="col-md-6"><div class="mb-3"><label class="form-label">Jam Selesai</label><input type="time" name="jam_selesai" class="form-control" value="<?php echo e($editData['jam_selesai'] ?? '08:00'); ?>"></div></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="3"><?php echo e($editData['keterangan'] ?? ''); ?></textarea></div>
                        <button class="btn btn-primary w-100">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card custom-card">
                <div class="card-header">Daftar Jadwal</div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kegiatan</th>
                                <th>Kelas</th>
                                <th>Pembina</th>
                                <th>Jam</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jadwal as $item): ?>
                                <tr>
                                    <td><?php echo e($item['tanggal']); ?></td>
                                    <td><?php echo e($item['nama_kegiatan']); ?></td>
                                    <td><?php echo e($item['nama_kelas']); ?></td>
                                    <td><?php echo e($item['nama_guru'] ?? '-'); ?></td>
                                    <td><?php echo e($item['jam_mulai'] . ' - ' . $item['jam_selesai']); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?edit=<?php echo $item['id']; ?>" class="btn btn-warning">Edit</a>
                                            <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-danger" onclick="return confirm('Hapus jadwal ini?')">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
