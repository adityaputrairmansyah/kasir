<?php
session_start();
require_once '../config/database.php';

// Cek login
if(!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

// Cek id transaksi
if(!isset($_GET['id'])) {
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

// Jika transaksi tidak ditemukan
if(!$transaksi) {
    header("Location: index.php");
    exit();
}

// Cek hak akses: hanya kasir yang membuat transaksi
if($user['role'] != 'kasir' || $transaksi['id_user'] != $user['id']) {
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
    <title>Edit Transaksi - Sistem Kasir</title>
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Transaksi #<?php echo $transaksi['no_transaksi'] ?></h5>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form action="update.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $id ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">No Transaksi</label>
                            <input type="text" class="form-control" value="<?php echo $transaksi['no_transaksi'] ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal</label>
                            <input type="text" class="form-control" value="<?php echo date('d/m/Y H:i', strtotime($transaksi['tanggal'])) ?>" readonly>
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Produk</th>
                                    <th class="text-center" width="150">Jumlah</th>
                                    <th class="text-center" width="200">Harga</th>
                                    <th class="text-center" width="200">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($detail as $d): ?>
                                <tr>
                                    <td>
                                        <?php echo $d['kode_produk'] ?> - 
                                        <?php echo htmlspecialchars($d['nama_produk']) ?>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="qty[<?php echo $d['id'] ?>]" 
                                               class="form-control text-center" 
                                               value="<?php echo $d['jumlah'] ?>"
                                               min="1">
                                    </td>
                                    <td class="text-end">
                                        Rp <?php echo number_format($d['harga'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end">
                                        Rp <?php echo number_format($d['subtotal'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="text-end">Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.') ?></th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">Bayar</th>
                                    <td>
                                        <input type="number" 
                                               name="bayar" 
                                               class="form-control text-end" 
                                               value="<?php echo $transaksi['bayar'] ?>"
                                               min="<?php echo $transaksi['total_harga'] ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">Kembalian</th>
                                    <th class="text-end">Rp <?php echo number_format($transaksi['kembalian'], 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 