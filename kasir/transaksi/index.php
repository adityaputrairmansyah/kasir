<?php
session_start();
require_once '../config/database.php';

// Cek login
if(!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil data user yang login
$user = $_SESSION['user'];

// Query untuk mengambil data transaksi
if($user['role'] == 'admin') {
    // Admin bisa melihat semua transaksi
    $stmt = $db->query("SELECT t.*, u.nama_lengkap 
                        FROM transaksi t 
                        LEFT JOIN users u ON t.id_user = u.id 
                        ORDER BY t.tanggal DESC");
} else {
    // Kasir hanya bisa melihat transaksinya sendiri
    $stmt = $db->prepare("SELECT t.*, u.nama_lengkap 
                         FROM transaksi t 
                         LEFT JOIN users u ON t.id_user = u.id 
                         WHERE t.id_user = ? 
                         ORDER BY t.tanggal DESC");
    $stmt->execute([$user['id']]);
}
$transaksi = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Sistem Kasir</title>
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Transaksi</h5>
                <div>
                    <a href="laporan_harian.php" class="btn btn-info me-2">
                        <i class="fas fa-file-alt"></i> Laporan Harian
                    </a>
                    <?php if($user['role'] == 'admin'): ?>
                    <a href="laporan_bulanan.php" class="btn btn-info me-2">
                        <i class="fas fa-calendar-alt"></i> Laporan Bulanan
                    </a>
                    <?php endif; ?>
                    <?php if($user['role'] == 'kasir'): ?>
                    <a href="baru.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Transaksi Baru
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
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
                                <td colspan="8" class="text-center">Tidak ada data transaksi</td>
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
                                            <?php if($user['role'] == 'kasir' && $t['id_user'] == $user['id']): ?>
                                            <a href="edit.php?id=<?php echo $t['id'] ?>" 
                                               class="btn btn-sm btn-warning" 
                                               title="Edit"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="hapus.php?id=<?php echo $t['id'] ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menghapus transaksi ini? Stok produk akan dikembalikan.')" 
                                               title="Hapus"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
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