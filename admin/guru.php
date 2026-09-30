<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin']);

$pdo = db();
$pageTitle = 'Data Kelas';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_kelas'])) {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama_kelas'] ?? '');
    $tingkat = trim($_POST['tingkat'] ?? '');
    $wali = trim($_POST['wali_kelas'] ?? '');
    $tahun = trim($_POST['tahun_pelajaran'] ?? '');
    $status = trim($_POST['status'] ?? 'aktif');

    if ($nama === '' || $tingkat === '') {
        setFlash('danger', 'Nama kelas dan tingkat wajib diisi.');
        redirect('admin/kelas.php');
    }

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE kelas SET nama_kelas=?, tingkat=?, wali_kelas=?, tahun_pelajaran=?, status=? WHERE id=?');
        $stmt->execute([$nama, $tingkat, $wali, $tahun, $status, $id]);
        setFlash('success', 'Data kelas berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO kelas (nama_kelas, tingkat, wali_kelas, tahun_pelajaran, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nama, $tingkat, $wali, $tahun, $status]);
        setFlash('success', 'Kelas baru berhasil ditambahkan.');
    }

    redirect('admin/kelas.php');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $pdo->prepare('DELETE FROM kelas WHERE id = ?')->execute([$id]);
        setFlash('success', 'Kelas berhasil dihapus.');
    }
    redirect('admin/kelas.php');
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editData = null;
if ($editId > 0) {
    $editData = $pdo->prepare('SELECT * FROM kelas WHERE id = ?');
    $editData->execute([$editId]);
    $editData = $editData->fetch();
}

$kelas = $pdo->query('SELECT * FROM kelas ORDER BY tingkat, nama_kelas')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <?php showFlash(); ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card custom-card">
                <div class="card-header"><?php echo $editData ? 'Edit Kelas' : 'Tambah Kelas'; ?></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                        <input type="hidden" name="save_kelas" value="1">
                        <input type="hidden" name="id" value="<?php echo e($editData['id'] ?? 0); ?>">
                        <div class="mb-3"><label class="form-label">Nama Kelas</label><input type="text" name="nama_kelas" class="form-control" required value="<?php echo e($editData['nama_kelas'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Tingkat</label><input type="text" name="tingkat" class="form-control" required value="<?php echo e($editData['tingkat'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Wali Kelas</label><input type="text" name="wali_kelas" class="form-control" value="<?php echo e($editData['wali_kelas'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Tahun Pelajaran</label><input type="text" name="tahun_pelajaran" class="form-control" value="<?php echo e($editData['tahun_pelajaran'] ?? '2025/2026'); ?>"></div>
                        <div class="mb-3"><label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="aktif" <?php echo (($editData['status'] ?? 'aktif') === 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="nonaktif" <?php echo (($editData['status'] ?? 'aktif') === 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card custom-card">
                <div class="card-header">Daftar Kelas</div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kelas</th>
                                <th>Tingkat</th>
                                <th>Wali Kelas</th>
                                <th>Tahun</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kelas as $i => $k): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo e($k['nama_kelas']); ?></td>
                                    <td><?php echo e($k['tingkat']); ?></td>
                                    <td><?php echo e($k['wali_kelas']); ?></td>
                                    <td><?php echo e($k['tahun_pelajaran']); ?></td>
                                    <td><span class="badge <?php echo $k['status'] === 'aktif' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo e($k['status']); ?></span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?edit=<?php echo $k['id']; ?>" class="btn btn-warning">Edit</a>
                                            <a href="?delete=<?php echo $k['id']; ?>" class="btn btn-danger" onclick="return confirm('Hapus kelas ini?')">Hapus</a>
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
