<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin']);

$pdo = db();
$pageTitle = 'Data Siswa';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_siswa'])) {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $nis = trim($_POST['nis'] ?? '');
    $nisn = trim($_POST['nisn'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jk = trim($_POST['jk'] ?? 'L');
    $tempatLahir = trim($_POST['tempat_lahir'] ?? '');
    $tanggalLahir = trim($_POST['tanggal_lahir'] ?? '');
    $kelasId = (int) ($_POST['kelas_id'] ?? 0);
    $noHp = trim($_POST['no_hp'] ?? '');
    $namaOrangtua = trim($_POST['nama_orangtua'] ?? '');
    $noWaOrangtua = trim($_POST['no_wa_orangtua'] ?? '');
    $status = trim($_POST['status'] ?? 'aktif');

    if ($nis === '' || $nama === '') {
        setFlash('danger', 'NIS dan nama siswa wajib diisi.');
        redirect('admin/siswa.php');
    }

    $fotoPath = null;
    if (!empty($_FILES['foto']['name'])) {
        $fotoPath = uploadFile($_FILES['foto'], 'uploads/siswa');
        if ($fotoPath === null) {
            setFlash('danger', 'Format foto tidak valid. Gunakan JPG, JPEG, PNG, atau GIF.');
            redirect('admin/siswa.php');
        }
    }

    $qrCode = generateQrCodeValue($nis . '-' . ($nisn ?: $nama));
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT foto FROM siswa WHERE id = ?');
        $stmt->execute([$id]);
        $old = $stmt->fetch();

        if ($fotoPath === null) {
            $fotoPath = $old['foto'] ?? null;
        }

        $stmt = $pdo->prepare('UPDATE siswa SET nis=?, nisn=?, nama=?, jk=?, tempat_lahir=?, tanggal_lahir=?, kelas_id=?, no_hp=?, nama_orangtua=?, no_wa_orangtua=?, foto=?, qr_code=?, status=? WHERE id=?');
        $stmt->execute([$nis, $nisn, $nama, $jk, $tempatLahir, $tanggalLahir, $kelasId ?: null, $noHp, $namaOrangtua, $noWaOrangtua, $fotoPath, $qrCode, $status, $id]);
        setFlash('success', 'Data siswa berhasil diperbarui.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO siswa (nis, nisn, nama, jk, tempat_lahir, tanggal_lahir, kelas_id, no_hp, nama_orangtua, no_wa_orangtua, foto, qr_code, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$nis, $nisn, $nama, $jk, $tempatLahir, $tanggalLahir, $kelasId ?: null, $noHp, $namaOrangtua, $noWaOrangtua, $fotoPath, $qrCode, $status]);
        setFlash('success', 'Siswa baru berhasil ditambahkan.');
    }

    redirect('admin/siswa.php');
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare('DELETE FROM siswa WHERE id = ?')->execute([$id]);
    setFlash('success', 'Data siswa berhasil dihapus.');
    redirect('admin/siswa.php');
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editData = null;
if ($editId > 0) {
    $editData = $pdo->prepare('SELECT * FROM siswa WHERE id = ?');
    $editData->execute([$editId]);
    $editData = $editData->fetch();
}

$kelasList = $pdo->query('SELECT * FROM kelas WHERE status = "aktif" ORDER BY nama_kelas')->fetchAll();
$students = $pdo->query('SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id ORDER BY s.nama')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <?php showFlash(); ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card custom-card">
                <div class="card-header"><?php echo $editData ? 'Edit Siswa' : 'Tambah Siswa'; ?></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                        <input type="hidden" name="save_siswa" value="1">
                        <input type="hidden" name="id" value="<?php echo e($editData['id'] ?? 0); ?>">

                        <div class="mb-3"><label class="form-label">NIS</label><input type="text" name="nis" class="form-control" required value="<?php echo e($editData['nis'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">NISN</label><input type="text" name="nisn" class="form-control" value="<?php echo e($editData['nisn'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" name="nama" class="form-control" required value="<?php echo e($editData['nama'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Jenis Kelamin</label>
                            <select name="jk" class="form-select">
                                <option value="L" <?php echo (($editData['jk'] ?? 'L') === 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="P" <?php echo (($editData['jk'] ?? 'L') === 'P') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Tempat Lahir</label><input type="text" name="tempat_lahir" class="form-control" value="<?php echo e($editData['tempat_lahir'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Tanggal Lahir</label><input type="date" name="tanggal_lahir" class="form-control" value="<?php echo e($editData['tanggal_lahir'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Kelas</label>
                            <select name="kelas_id" class="form-select">
                                <option value="">- Pilih Kelas -</option>
                                <?php foreach ($kelasList as $kelas): ?>
                                    <option value="<?php echo $kelas['id']; ?>" <?php echo (($editData['kelas_id'] ?? '') == $kelas['id']) ? 'selected' : ''; ?>><?php echo e($kelas['nama_kelas']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Nomor HP</label><input type="text" name="no_hp" class="form-control" value="<?php echo e($editData['no_hp'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Nama Orang Tua/Wali</label><input type="text" name="nama_orangtua" class="form-control" value="<?php echo e($editData['nama_orangtua'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">WhatsApp Orang Tua</label><input type="text" name="no_wa_orangtua" class="form-control" value="<?php echo e($editData['no_wa_orangtua'] ?? ''); ?>"></div>
                        <div class="mb-3"><label class="form-label">Foto</label><input type="file" name="foto" class="form-control"></div>
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
                <div class="card-header">Daftar Siswa</div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Foto</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>QR</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $i => $s): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td>
                                        <?php if (!empty($s['foto'])): ?>
                                            <img src="<?php echo e($s['foto']); ?>" alt="Foto siswa" class="avatar-sm">
                                        <?php else: ?>
                                            <div class="avatar-sm bg-light text-center"><i class="fa-solid fa-user"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($s['nis']); ?></td>
                                    <td><?php echo e($s['nama']); ?></td>
                                    <td><?php echo e($s['nama_kelas'] ?? '-'); ?></td>
                                    <td>
                                        <?php if (!empty($s['qr_code'])): ?>
                                            <img src="<?php echo generateQrImage($s['qr_code']); ?>" width="50" alt="QR">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?edit=<?php echo $s['id']; ?>" class="btn btn-warning">Edit</a>
                                            <a href="?delete=<?php echo $s['id']; ?>" class="btn btn-danger" onclick="return confirm('Hapus siswa ini?')">Hapus</a>
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
