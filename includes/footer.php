<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = currentUser();
$base = 'http://localhost/absensi-keagamaan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Absensi Keagamaan'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.3/chart.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand-box">
                <div class="brand-icon"><i class="fa-solid fa-mosque"></i></div>
                <div>
                    <h5 class="mb-0">Absensi</h5>
                    <small>Keagamaan</small>
                </div>
            </div>

            <nav class="nav flex-column mt-4">
                <?php if ($user['role'] === 'admin'): ?>
                    <a class="nav-link" href="<?php echo $base; ?>/admin/dashboard.php"><i class="fa-solid fa-gauge-high me-2"></i>Dashboard</a>
                    <div class="nav-section">Data Master</div>
                    <a class="nav-link" href="<?php echo $base; ?>/admin/siswa.php"><i class="fa-solid fa-user-graduate me-2"></i>Siswa</a>
                    <a class="nav-link" href="<?php echo $base; ?>/admin/kelas.php"><i class="fa-solid fa-school me-2"></i>Kelas</a>
                    <a class="nav-link" href="<?php echo $base; ?>/admin/guru.php"><i class="fa-solid fa-chalkboard-user me-2"></i>Guru/Pembina</a>
                    <a class="nav-link" href="<?php echo $base; ?>/admin/kegiatan.php"><i class="fa-solid fa-book-open me-2"></i>Kegiatan</a>
                    <a class="nav-link" href="<?php echo $base; ?>/admin/jadwal.php"><i class="fa-regular fa-calendar-days me-2"></i>Jadwal</a>
                    <a class="nav-link" href="<?php echo $base; ?>/admin/absensi.php"><i class="fa-solid fa-clipboard-check me-2"></i>Absensi</a>
                    <a class="nav-link" href="<?php echo $base; ?>/scan/scan.php"><i class="fa-solid fa-qrcode me-2"></i>Scan QR Code</a>
                    <a class="nav-link" href="<?php echo $base; ?>/admin/rekap.php"><i class="fa-solid fa-chart-column me-2"></i>Rekap Absensi</a>
                    <a class="nav-link" href="<?php echo $base; ?>/admin/laporan.php"><i class="fa-solid fa-file-lines me-2"></i>Laporan</a>
                    <a class="nav-link" href="<?php echo $base; ?>/admin/users.php"><i class="fa-solid fa-users me-2"></i>Pengguna</a>
                    <a class="nav-link" href="<?php echo $base; ?>/index.php"><i class="fa-solid fa-gear me-2"></i>Pengaturan</a>
                <?php elseif ($user['role'] === 'guru'): ?>
                    <a class="nav-link" href="<?php echo $base; ?>/guru/dashboard.php"><i class="fa-solid fa-gauge-high me-2"></i>Dashboard</a>
                    <a class="nav-link" href="<?php echo $base; ?>/guru/absensi.php"><i class="fa-solid fa-clipboard-check me-2"></i>Absensi</a>
                    <a class="nav-link" href="<?php echo $base; ?>/guru/rekap.php"><i class="fa-solid fa-chart-column me-2"></i>Rekap</a>
                    <a class="nav-link" href="<?php echo $base; ?>/scan/scan.php"><i class="fa-solid fa-qrcode me-2"></i>Scan QR</a>
                <?php else: ?>
                    <a class="nav-link" href="<?php echo $base; ?>/siswa/dashboard.php"><i class="fa-solid fa-house me-2"></i>Dashboard</a>
                    <a class="nav-link" href="<?php echo $base; ?>/siswa/riwayat.php"><i class="fa-solid fa-clock-rotate-left me-2"></i>Riwayat</a>
                <?php endif; ?>
                <a class="nav-link" href="<?php echo $base; ?>/auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="topbar">
                <div>
                    <h4 class="mb-0"><?php echo $pageTitle ?? 'Dashboard'; ?></h4>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-light text-dark"><?php echo e($user['role']); ?></span>
                    <span class="fw-semibold"><?php echo e($user['nama']); ?></span>
                </div>
            </div>

            <div class="content-body">
