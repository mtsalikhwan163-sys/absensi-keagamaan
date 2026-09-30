<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verifyCsrf();

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    setFlash('danger', 'Username dan password wajib diisi.');
    redirect('index.php');
}

$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

$valid = false;
if ($user) {
    if (password_verify($password, $user['password']) || $user['password'] === $password) {
        $valid = true;
        if ($user['password'] === $password && !password_get_info($user['password'])['algo']) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $upd->execute([$newHash, $user['id']]);
            $user['password'] = $newHash;
        }
    }
}

if (!$valid) {
    setFlash('danger', 'Username atau password salah.');
    redirect('index.php');
}

if ($user['status'] !== 'aktif') {
    setFlash('warning', 'Akun Anda saat ini nonaktif.');
    redirect('index.php');
}

session_regenerate_id(true);
$_SESSION['user'] = [
    'id' => (int) $user['id'],
    'username' => $user['username'],
    'nama' => $user['nama'],
    'role' => $user['role'],
    'status' => $user['status'],
    'guru_id' => $user['guru_id'] ?? null,
    'siswa_id' => $user['siswa_id'] ?? null,
];

logActivity((int) $user['id'], 'Login', 'User masuk ke sistem');

if ($user['role'] === 'admin') {
    redirect('admin/dashboard.php');
}
if ($user['role'] === 'guru') {
    redirect('guru/dashboard.php');
}
redirect('siswa/dashboard.php');
