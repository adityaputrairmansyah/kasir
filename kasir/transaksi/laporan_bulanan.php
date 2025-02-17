<?php
session_start();
require_once '../config/database.php';

// Cek login
if(!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

// Cek role admin
if($user['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Set bulan dan tahun default
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Tambahkan daftar kasir untuk filter jika user adalah admin
if($user['role'] == 'admin') {
    // Ambil daftar kasir untuk dropdown filter
    $stmt = $db->query("SELECT id, nama_lengkap FROM users WHERE role = 'kasir' ORDER BY nama_lengkap");
    $kasir_list = $stmt->fetchAll();
    
    // Filter berdasarkan kasir yang dipilih
    $id_kasir = isset($_GET['id_kasir']) ? $_GET['id_kasir'] : '';
    
    if($id_kasir) {
        // Query untuk kasir tertentu
        $stmt = $db->prepare("SELECT t.*, u.nama_lengkap 
                            FROM transaksi t 
                            JOIN users u ON t.id_user = u.id 
                            WHERE MONTH(t.tanggal) = ? AND YEAR(t.tanggal) = ?
                            AND t.id_user = ?
                            ORDER BY t.tanggal DESC");
        $stmt->execute([$bulan, $tahun, $id_kasir]);
        
        // Total per kasir
        $stmt_total = $db->prepare("SELECT SUM(total_harga) as total 
                                   FROM transaksi 
                                   WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
                                   AND id_user = ?");
        $stmt_total->execute([$bulan, $tahun, $id_kasir]);
    } else {
        // Query untuk semua kasir
        $stmt = $db->prepare("SELECT t.*, u.nama_lengkap 
                            FROM transaksi t 
                            JOIN users u ON t.id_user = u.id 
                            WHERE MONTH(t.tanggal) = ? AND YEAR(t.tanggal) = ?
                            AND u.role = 'kasir'
                            ORDER BY t.tanggal DESC");
        $stmt->execute([$bulan, $tahun]);
        
        // Total semua kasir
        $stmt_total = $db->prepare("SELECT SUM(t.total_harga) as total 
                                   FROM transaksi t
                                   JOIN users u ON t.id_user = u.id 
                                   WHERE MONTH(t.tanggal) = ? AND YEAR(t.tanggal) = ?
                                   AND u.role = 'kasir'");
        $stmt_total->execute([$bulan, $tahun]);
    }
    
    $transaksi = $stmt->fetchAll();
    $total = $stmt_total->fetch()['total'] ?? 0;
} else {
    // Kasir hanya melihat transaksinya sendiri
    $stmt = $db->prepare("SELECT t.*, u.nama_lengkap 
                        FROM transaksi t 
                        JOIN users u ON t.id_user = u.id 
                        WHERE MONTH(t.tanggal) = ? AND YEAR(t.tanggal) = ?
                        AND t.id_user = ?
                        ORDER BY t.tanggal DESC");
    $stmt->execute([$bulan, $tahun, $user['id']]);
    $transaksi = $stmt->fetchAll();

    // Total untuk kasir
    $stmt_total = $db->prepare("SELECT SUM(total_harga) as total 
                               FROM transaksi 
                               WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
                               AND id_user = ?");
    $stmt_total->execute([$bulan, $tahun, $user['id']]);
    $total = $stmt_total->fetch()['total'] ?? 0;
}

// Tambahkan array nama bulan
$nama_bulan = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - Sistem Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">Sistem Kasir</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Transaksi</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Laporan Transaksi Bulanan</h5>
                        <div>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if($user['role'] == 'admin'): ?>
                            <form method="GET" class="mb-4">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <select name="bulan" class="form-select">
                                                <?php foreach($nama_bulan as $angka => $nama): ?>
                                                    <option value="<?php echo $angka ?>" 
                                                            <?php echo $bulan == $angka ? 'selected' : '' ?>>
                                                        <?php echo $nama ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <select name="tahun" class="form-select">
                                                <?php 
                                                $tahun_sekarang = date('Y');
                                                for($i = $tahun_sekarang; $i >= $tahun_sekarang - 5; $i--): 
                                                ?>
                                                    <option value="<?php echo $i ?>" <?php echo $tahun == $i ? 'selected' : '' ?>>
                                                        <?php echo $i ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <select name="id_kasir" class="form-select">
                                                <option value="">Semua Kasir</option>
                                                <?php foreach($kasir_list as $k): ?>
                                                    <option value="<?php echo $k['id'] ?>" 
                                                            <?php echo $id_kasir == $k['id'] ? 'selected' : '' ?>>
                                                        <?php echo htmlspecialchars($k['nama_lengkap']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <h4>Total: Rp <?php echo number_format($total, 0, ',', '.') ?></h4>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th class="text-center">No Transaksi</th>
                                        <th class="text-center">Tanggal</th>
                                        <th class="text-center">Kasir</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Bayar</th>
                                        <th class="text-center">Kembalian</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($transaksi)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada transaksi</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach($transaksi as $index => $t): ?>
                                        <tr>
                                            <td class="text-center"><?php echo $index + 1 ?></td>
                                            <td class="text-center"><?php echo $t['no_transaksi'] ?></td>
                                            <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($t['tanggal'])) ?></td>
                                            <td class="text-center"><?php echo $t['nama_lengkap'] ?? 'Admin' ?></td>
                                            <td class="text-center">Rp <?php echo number_format($t['total_harga'], 0, ',', '.') ?></td>
                                            <td class="text-center">Rp <?php echo number_format($t['bayar'], 0, ',', '.') ?></td>
                                            <td class="text-center">Rp <?php echo number_format($t['kembalian'], 0, ',', '.') ?></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="detail.php?id=<?php echo $t['id'] ?>" 
                                                       class="btn btn-sm btn-info" 
                                                       title="Detail"
                                                       data-bs-toggle="tooltip">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="cetak.php?id=<?php echo $t['id'] ?>" 
                                                       class="btn btn-sm btn-success" 
                                                       target="_blank" 
                                                       title="Cetak"
                                                       data-bs-toggle="tooltip">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fas fa-print"></i> Cetak Laporan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Aktifkan tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    </script>
</body>
</html> 