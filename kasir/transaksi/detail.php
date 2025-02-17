<?php
session_start();
require_once '../config/database.php';

// Cek login
if(!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Ambil data transaksi
$stmt = $db->prepare("SELECT t.*, u.nama_lengkap 
                      FROM transaksi t 
                      LEFT JOIN users u ON t.id_user = u.id 
                      WHERE t.id = ?");
$stmt->execute([$id]);
$transaksi = $stmt->fetch();

if (!$transaksi) {
    header("Location: index.php");
    exit();
}

// Ambil detail transaksi
$stmt = $db->prepare("SELECT d.*, p.nama_produk, p.kode_produk 
                      FROM detail_transaksi d 
                      JOIN produk p ON d.id_produk = p.id 
                      WHERE d.id_transaksi = ?");
$stmt->execute([$id]);
$detail = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi - Sistem Kasir</title>
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Transaksi</h5>
                        <div>
                            <a href="cetak.php?id=<?php echo $id ?>" class="btn btn-success" target="_blank">
                                <i class="fas fa-print"></i> Cetak
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td>No Transaksi</td>
                                        <td>: <?php echo $transaksi['no_transaksi'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal</td>
                                        <td>: <?php echo date('d/m/Y H:i', strtotime($transaksi['tanggal'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Kasir</td>
                                        <td>: <?php echo $transaksi['nama_lengkap'] ?? 'Admin' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($detail as $index => $d): ?>
                                <tr>
                                    <td><?php echo $index + 1 ?></td>
                                    <td><?php echo $d['kode_produk'] ?></td>
                                    <td><?php echo $d['nama_produk'] ?></td>
                                    <td>Rp <?php echo number_format($d['harga'], 0, ',', '.') ?></td>
                                    <td><?php echo $d['jumlah'] ?></td>
                                    <td>Rp <?php echo number_format($d['subtotal'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Total</strong></td>
                                    <td><strong>Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.') ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end">Bayar</td>
                                    <td>Rp <?php echo number_format($transaksi['bayar'], 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end">Kembalian</td>
                                    <td>Rp <?php echo number_format($transaksi['kembalian'], 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 