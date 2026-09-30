<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin']);

$pdo = db();
$pageTitle = 'Laporan & Export';

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_absensi.csv"');

    $rows = $pdo->query('SELECT a.id, s.nis, s.nama, k.nama_kelas, j.tanggal, kg.nama_kegiatan, a.status, a.keterangan FROM absensi a LEFT JOIN siswa s ON s.id = a.siswa_id LEFT JOIN kelas k ON k.id = s.kelas_id LEFT JOIN jadwal j ON j.id = a.jadwal_id LEFT JOIN kegiatan kg ON kg.id = j.kegiatan_id ORDER BY a.tanggal DESC')->fetchAll();

    echo "ID,NIS,Nama,Kelas,Tanggal,Kegiatan,Status,Keterangan\n";
    foreach ($rows as $r) {
        echo implode(',', [
            $r['id'],
            str_replace(',', ' ', $r['nis']),
            str_replace(',', ' ', $r['nama']),
            str_replace(',', ' ', $r['nama_kelas']),
            $r['tanggal'],
            str_replace(',', ' ', $r['nama_kegiatan']),
            $r['status'],
            str_replace(',', ' ', $r['keterangan'])
        ]) . "\n";
    }
    exit;
}

$rows = $pdo->query('SELECT a.id, s.nis, s.nama, k.nama_kelas, j.tanggal, kg.nama_kegiatan, a.status, a.keterangan FROM absensi a LEFT JOIN siswa s ON s.id = a.siswa_id LEFT JOIN kelas k ON k.id = s.kelas_id LEFT JOIN jadwal j ON j.id = a.jadwal_id LEFT JOIN kegiatan kg ON kg.id = j.kegiatan_id ORDER BY a.tanggal DESC LIMIT 50')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
    <div class="card custom-card mb-4">
        <div class="card-header">Export & Cetak</div>
        <div class="card-body">
            <div class="btn-group gap-2">
                <a href="?export=csv" class="btn btn-success">Export CSV</a>
                <button class="btn btn-primary" onclick="window.print()">Cetak Laporan</button>
            </div>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header">Laporan Absensi Terakhir</div>
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Kegiatan</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $i => $r): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo e($r['nis']); ?></td>
                            <td><?php echo e($r['nama']); ?></td>
                            <td><?php echo e($r['nama_kelas']); ?></td>
                            <td><?php echo e($r['tanggal']); ?></td>
                            <td><?php echo e($r['nama_kegiatan']); ?></td>
                            <td><?php echo e($r['status']); ?></td>
                            <td><?php echo e($r['keterangan']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
