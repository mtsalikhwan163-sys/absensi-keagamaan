<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin']);

$pdo = db();
$pageTitle = 'Data Guru / Pembina';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_guru'])) {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $noHp = trim($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = trim($_POST['status'] ?? 'aktif');

    if ($nama === '') {
        setFlash('danger', 'Nama guru wajib diisi.');
        redirect('admin/guru.php');
    }

    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE guru SET nip=?, nama=?, no_hp=?, email=?, status=? WHERE id=?');
        $stmt->execute([$nip, $nama, $noHp, $email, $status, $id]);
        setFlash('success', 'Data guru berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO guru (nip, nama, no_hp, email, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nip, $nama, $noHp, $email, $status]);
        setFlash('success', 'Guru baru berhasil ditambahkan.');
    }

    redirect('admin/guru.php');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare('DELETE FROM guru WHERE id = ?')->execute([$id]);
    setFlash('success', 'Guru berhasil dihapus.');
    redirect('admin/guru.php');
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editData = null;
if ($editId > 0) {
    $row = $pdo->prepare('SELECT * FROM guru WHERE id = ?');
    $row->execute([$editId]);
    $editData = $row->fetch();
}

$guru = $pdo->query('SELECT * FROM guru ORDER BY nama')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <?php showFlash(); ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card custom-card">
                <div class="card-header"><?php echo $editData ? 'Edit Guru' : 'Tambah Guru'; ?></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                        <input type="hidden" name="save_guru" value="1">
                        <input type="hidden" name="id" value="<?php echo e($editData['id'] ?? 0); ?>">
                        <div class="mb-3"><label class="form-label">NIP</label><input type="text" name="nip" class="form-control" value="<?php echo e($editData['nip'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="nama" class="form-control" required value="<?php echo e($editData['nama'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">No HP</label><input type="text" name="no_hp" class="form-control" value="<?php echo e($editData['no_hp'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo e($editData['email'] ?? ''); ?>"></div>
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
                <div class="card-header">Daftar Guru</div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>No HP</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($guru as $i => $g): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo e($g['nip']); ?></td>
                                    <td><?php echo e($g['nama']); ?></td>
                                    <td><?php echo e($g['no_hp']); ?></td>
                                    <td><?php echo e($g['email']); ?></td>
                                    <td><span class="badge <?php echo $g['status'] === 'aktif' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo e($g['status']); ?></span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?edit=<?php echo $g['id']; ?>" class="btn btn-warning">Edit</a>
                                            <a href="?delete=<?php echo $g['id']; ?>" class="btn btn-danger" onclick="return confirm('Hapus guru ini?')">Hapus</a>
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
