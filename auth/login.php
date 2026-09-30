<?php
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') {
        redirect('admin/dashboard.php');
    }
    if ($role === 'guru') {
        redirect('guru/dashboard.php');
    }
    redirect('siswa/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Absensi Keagamaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="container py-5">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0 rounded-4 login-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="brand-icon">
                                <i class="fa-solid fa-mosque"></i>
                            </div>
                            <h2 class="fw-bold mt-3">Absensi Keagamaan</h2>
                            <p class="text-muted mb-0">Madrasah / Sekolah</p>
                        </div>

                        <?php if (!empty($_SESSION['flash'])): ?>
                            <?php showFlash(); ?>
                        <?php endif; ?>

                        <form action="auth/login.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" required placeholder="Masukkan username">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-login w-100">
                                <i class="fa-solid fa-right-to-bracket me-2"></i>Login
                            </button>
                        </form>

                        <div class="mt-4 small text-center text-muted">
                            Demo akun:<br>
                            <strong>Admin</strong>: admin / admin123<br>
                            <strong>Guru</strong>: guru / guru123
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
