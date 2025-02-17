<?php
session_start();
require_once '../config/database.php';

// Cek login
if(!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

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
                            WHERE DATE(t.tanggal) = ?
                            AND t.id_user = ?
                            ORDER BY t.tanggal DESC");
        $stmt->execute([$tanggal, $id_kasir]);
        
        // Total per kasir
        $stmt_total = $db->prepare("SELECT SUM(total_harga) as total 
                                   FROM transaksi 
                                   WHERE DATE(tanggal) = ?
                                   AND id_user = ?");
        $stmt_total->execute([$tanggal, $id_kasir]);
    } else {
        // Query untuk semua kasir
        $stmt = $db->prepare("SELECT t.*, u.nama_lengkap 
                            FROM transaksi t 
                            JOIN users u ON t.id_user = u.id 
                            WHERE DATE(t.tanggal) = ?
                            AND u.role = 'kasir'
                            ORDER BY t.tanggal DESC");
        $stmt->execute([$tanggal]);
        
        // Total semua kasir
        $stmt_total = $db->prepare("SELECT SUM(t.total_harga) as total 
                                   FROM transaksi t
                                   JOIN users u ON t.id_user = u.id 
                                   WHERE DATE(t.tanggal) = ?
                                   AND u.role = 'kasir'");
        $stmt_total->execute([$tanggal]);
    }
    
    $transaksi = $stmt->fetchAll();
    $total = $stmt_total->fetch()['total'] ?? 0;
} else {
    // Kasir hanya melihat transaksinya sendiri
    $stmt = $db->prepare("SELECT t.*, u.nama_lengkap 
                        FROM transaksi t 
                        JOIN users u ON t.id_user = u.id 
                        WHERE DATE(t.tanggal) = ?
                        AND t.id_user = ?
                        ORDER BY t.tanggal DESC");
    $stmt->execute([$tanggal, $user['id']]);
    $transaksi = $stmt->fetchAll();

    // Total pendapatan kasir hari ini
    $stmt_total = $db->prepare("SELECT SUM(total_harga) as total 
                               FROM transaksi 
                               WHERE DATE(tanggal) = ?
                               AND id_user = ?");
    $stmt_total->execute([$tanggal, $user['id']]);
    $total = $stmt_total->fetch()['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian - Sistem Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">Sistem Kasir</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if($user['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">Dashboard</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Transaksi</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($user['nama_lengkap']) ?> (<?php echo ucfirst($user['role']) ?>)
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                        </ul>
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
                        <h5 class="mb-0">Laporan Transaksi Harian</h5>
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
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="date" class="form-control" name="tanggal" value="<?php echo $tanggal ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
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
                                    <div class="col-md-4 text-end">
                                        <h4>Total Pendapatan: Rp <?php echo number_format($total, 0, ',', '.') ?></h4>
                                    </div>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="date" class="form-control" name="tanggal" value="<?php echo $tanggal ?>">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </div>
                                </div>
                                <div class="col-md-8 text-end">
                                    <h4>Total Pendapatan Hari Ini: Rp <?php echo number_format($total, 0, ',', '.') ?></h4>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>No Transaksi</th>
                                        <th>Waktu</th>
                                        <th>Kasir</th>
                                        <th>Total</th>
                                        <th>Bayar</th>
                                        <th>Kembalian</th>
                                        <th>Aksi</th>
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
                                            <td><?php echo $index + 1 ?></td>
                                            <td><?php echo $t['no_transaksi'] ?></td>
                                            <td><?php echo date('H:i', strtotime($t['tanggal'])) ?></td>
                                            <td><?php echo $t['nama_lengkap'] ?? 'Admin' ?></td>
                                            <td>Rp <?php echo number_format($t['total_harga'], 0, ',', '.') ?></td>
                                            <td>Rp <?php echo number_format($t['bayar'], 0, ',', '.') ?></td>
                                            <td>Rp <?php echo number_format($t['kembalian'], 0, ',', '.') ?></td>
                                            <td>
                                                <a href="detail.php?id=<?php echo $t['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="cetak.php?id=<?php echo $t['id'] ?>" class="btn btn-sm btn-success" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
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
</body>
</html> 