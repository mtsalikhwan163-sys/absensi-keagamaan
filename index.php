<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = '127.0.0.1';
        $db   = 'db_absensi_keagamaan';
        $user = 'root';
        $pass = '';

        try {
            $pdo = new PDO(
                "mysql:host={$host};dbname={$db};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                ]
            );
        } catch (PDOException $e) {
            die('Koneksi database gagal: ' . $e->getMessage());
        }
    }

    return $pdo;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function redirect(string $path): void
{
    $base = 'http://localhost/absensi-keagamaan';
    header('Location: ' . rtrim($base, '/') . '/' . ltrim($path, '/'));
    exit;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('index.php');
    }
}

function requireRole(array $roles): void
{
    requireLogin();

    $user = currentUser();
    if (!in_array($user['role'], $roles, true)) {
        redirect('index.php');
    }
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        die('Token CSRF tidak valid.');
    }
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function showFlash(): void
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo '<div class="alert alert-' . e($flash['type']) . ' alert-dismissible fade show" role="alert">'
            . e($flash['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
            . '</div>';
        unset($_SESSION['flash']);
    }
}

function uploadFile(array $file, string $folder): ?string
{
    if (!isset($file['name']) || $file['name'] === '') {
        return null;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed, true)) {
        return null;
    }

    $folderPath = __DIR__ . '/../' . ltrim($folder, '/');
    if (!is_dir($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $file['name']);
    $target = $folderPath . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $folder . '/' . $filename;
    }

    return null;
}

function generateQrCodeValue(string $value): string
{
    return 'ABSENSI-' . strtoupper(trim($value));
}

function generateQrImage(string $code): string
{
    $code = urlencode($code);
    $url = 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=' . $code;
    return $url;
}

function formatDateId(string $date): string
{
    if (empty($date)) {
        return '-';
    }

    $dt = new DateTime($date);
    return $dt->format('d-m-Y');
}

function statusLabel(string $status): string
{
    $labels = ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispensasi'];
    return $labels[$status] ?? $status;
}

function formatText(string $text): string
{
    return trim((string) $text);
}

function logActivity(int $userId, string $aksi, ?string $detail = null): void
{
    try {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO activity_log (user_id, aksi, detail) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $aksi, $detail]);
    } catch (Throwable $e) {
        // abaikan jika tabel tidak ada atau error logging
    }
}
