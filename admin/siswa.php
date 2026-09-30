<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin']);

$pdo = db();
$pageTitle = 'Manajemen Pengguna';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $role = trim($_POST['role'] ?? 'admin');
    $status = trim($_POST['status'] ?? 'aktif');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $nama === '') {
        setFlash('danger', 'Username dan nama wajib diisi.');
        redirect('admin/users.php');
    }

    if ($id > 0) {
        if ($password !== '') {
            $stmt = $pdo->prepare('UPDATE users SET username=?, nama=?, role=?, status=?, password=? WHERE id=?');
            $stmt->execute([$username, $nama, $role, $status, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET username=?, nama=?, role=?, status=? WHERE id=?');
            $stmt->execute([$username, $nama, $role, $status, $id]);
        }
        setFlash('success', 'Data pengguna berhasil diperbarui.');
    } else {
        if ($password === '') {
            setFlash('danger', 'Password wajib diisi untuk pengguna baru.');
            redirect('admin/users.php');
        }

        $check = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $check->execute([$username]);
        if ($check->fetch()) {
            setFlash('danger', 'Username sudah terdaftar.');
            redirect('admin/users.php');
        }

        $stmt = $pdo->prepare('INSERT INTO users (username, password, nama, role, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $nama, $role, $status]);
        setFlash('success', 'Pengguna berhasil ditambahkan.');
    }

    redirect('admin/users.php');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        setFlash('success', 'Pengguna berhasil dihapus.');
    }
    redirect('admin/users.php');
}

$users = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <?php showFlash(); ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card custom-card">
                <div class="card-header">Tambah / Edit Pengguna</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                        <input type="hidden" name="save_user" value="1">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="admin">Admin</option>
                                <option value="guru">Guru</option>
                                <option value="siswa">Siswa</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <button class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card custom-card">
                <div class="card-header">Daftar Pengguna</div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Nama</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $i => $user): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo e($user['username']); ?></td>
                                    <td><?php echo e($user['nama']); ?></td>
                                    <td><?php echo e($user['role']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['status'] === 'aktif' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo e($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-warning">Edit</a>
                                            <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Hapus pengguna ini?')">Hapus</a>
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
