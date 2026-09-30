<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin']);

$pdo = db();
$pageTitle = 'Data Kegiatan Keagamaan';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_kegiatan'])) {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama_kegiatan'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $status = trim($_POST['status'] ?? 'aktif');

    if ($nama === '') {
        setFlash('danger', 'Nama kegiatan wajib diisi.');
        redirect('admin/kegiatan.php');
    }

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE kegiatan SET nama_kegiatan=?, deskripsi=?, lokasi=?, status=? WHERE id=?');
        $stmt->execute([$nama, $deskripsi, $lokasi, $status, $id]);
        setFlash('success', 'Data kegiatan berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO kegiatan (nama_kegiatan, deskripsi, lokasi, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nama, $deskripsi, $lokasi, $status]);
        setFlash('success', 'Kegiatan baru berhasil ditambahkan.');
    }

    redirect('admin/kegiatan.php');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $pdo->prepare('DELETE FROM kegiatan WHERE id = ?')->execute([$id]);
        setFlash('success', 'Kegiatan berhasil dihapus.');
    }
    redirect('admin/kegiatan.php');
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editData = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM kegiatan WHERE id = ?');
    $stmt->execute([$editId]);
    $editData = $stmt->fetch();
}

$kegiatan = $pdo->query('SELECT * FROM kegiatan ORDER BY nama_kegiatan')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <?php showFlash(); ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card custom-card">
                <div class="card-header"><?php echo $editData ? 'Edit Kegiatan' : 'Tambah Kegiatan'; ?></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                        <input type="hidden" name="save_kegiatan" value="1">
                        <input type="hidden" name="id" value="<?php echo e($editData['id'] ?? 0); ?>">
                        <div class="mb-3"><label class="form-label">Nama Kegiatan</label><input type="text" name="nama_kegiatan" class="form-control" required value="<?php echo e($editData['nama_kegiatan'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="3"><?php echo e($editData['deskripsi'] ?? ''); ?></textarea></div>
                        <div class="mb-3"><label class="form-label">Lokasi</label><input type="text" name="lokasi" class="form-control" value="<?php echo e($editData['lokasi'] ?? ''); ?>"></div>
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
                <div class="card-header">Daftar Kegiatan</div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kegiatan as $i => $kg): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo e($kg['nama_kegiatan']); ?></td>
                                    <td><?php echo e($kg['deskripsi']); ?></td>
                                    <td><?php echo e($kg['lokasi']); ?></td>
                                    <td><span class="badge <?php echo $kg['status'] === 'aktif' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo e($kg['status']); ?></span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?edit=<?php echo $kg['id']; ?>" class="btn btn-warning">Edit</a>
                                            <a href="?delete=<?php echo $kg['id']; ?>" class="btn btn-danger" onclick="return confirm('Hapus kegiatan ini?')">Hapus</a>
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
