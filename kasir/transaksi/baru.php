<?php
session_start();
require_once '../config/database.php';

// Cek login
if(!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

// Cek role: hanya kasir yang boleh melakukan transaksi
if($user['role'] != 'kasir') {
    header("Location: index.php");
    exit();
}

// Ambil data produk untuk pilihan
$search = isset($_GET['cari']) ? $_GET['cari'] : '';
if($search) {
    $stmt = $db->prepare("SELECT p.*, k.nama_kategori 
                        FROM produk p 
                        LEFT JOIN kategori k ON p.id_kategori = k.id 
                        WHERE (p.kode_produk LIKE ? OR p.nama_produk LIKE ?) 
                        AND p.stok > 0
                        ORDER BY p.nama_produk");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $db->query("SELECT p.*, k.nama_kategori 
                        FROM produk p 
                        LEFT JOIN kategori k ON p.id_kategori = k.id 
                        WHERE p.stok > 0
                        ORDER BY p.nama_produk");
}
$produk = $stmt->fetchAll();

// Generate nomor transaksi otomatis
$today = date('Ymd');
$stmt = $db->query("SELECT MAX(no_transaksi) as last_no FROM transaksi WHERE no_transaksi LIKE '$today%'");
$last = $stmt->fetch();
$last_no = (int) substr($last['last_no'] ?? $today.'000', -3);
$new_no = $today . sprintf('%03d', $last_no + 1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Baru - Sistem Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-list {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
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
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0">Pilih Produk</h5>
                            </div>
                            <div class="col-md-6">
                                <form action="" method="GET" class="mb-0">
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control" 
                                               name="cari" 
                                               placeholder="Cari kode/nama produk..."
                                               value="<?php echo isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : '' ?>">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i> Cari
                                        </button>
                                        <?php if(isset($_GET['cari'])): ?>
                                            <a href="baru.php" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Reset
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body product-list">
                        <?php if(empty($produk) && isset($_GET['cari'])): ?>
                            <div class="alert alert-info">
                                Tidak ditemukan produk dengan kata kunci: <strong><?php echo htmlspecialchars($_GET['cari']) ?></strong>
                            </div>
                        <?php endif; ?>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">Kode</th>
                                        <th class="text-center">Nama Produk</th>
                                        <th class="text-center">Kategori</th>
                                        <th class="text-center">Harga</th>
                                        <th class="text-center">Stok</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($produk as $p): ?>
                                    <tr>
                                        <td class="text-center"><?php echo htmlspecialchars($p['kode_produk']) ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($p['nama_produk']) ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($p['nama_kategori']) ?></td>
                                        <td class="text-center">Rp <?php echo number_format($p['harga_jual'], 0, ',', '.') ?></td>
                                        <td class="text-center"><?php echo $p['stok'] ?></td>
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="btn btn-sm btn-success add-product" 
                                                    data-id="<?php echo $p['id'] ?>"
                                                    data-nama="<?php echo htmlspecialchars($p['nama_produk']) ?>"
                                                    data-harga="<?php echo $p['harga_jual'] ?>"
                                                    data-stok="<?php echo $p['stok'] ?>"
                                                    title="Tambah ke keranjang"
                                                    data-bs-toggle="tooltip">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Detail Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <form action="proses.php" method="POST" id="form-transaksi">
                            <div class="mb-3">
                                <label class="form-label">No Transaksi</label>
                                <input type="text" class="form-control" name="no_transaksi" value="<?php echo $new_no ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="text" class="form-control" value="<?php echo date('d/m/Y H:i') ?>" readonly>
                            </div>

                            <div id="cart-items">
                                <!-- Items will be added here via JavaScript -->
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Total</label>
                                <input type="number" class="form-control" name="total" id="total" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Bayar</label>
                                <input type="number" class="form-control" name="bayar" id="bayar" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kembalian</label>
                                <input type="number" class="form-control" id="kembalian" readonly>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" id="btn-bayar">
                                    <i class="fas fa-save"></i> Simpan Transaksi
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = [];
        let total = 0;

        // Validasi sebelum submit
        document.getElementById('form-transaksi').onsubmit = function(e) {
            if (cart.length === 0) {
                alert('Pilih produk terlebih dahulu!');
                e.preventDefault();
                return false;
            }

            const bayar = parseFloat(document.getElementById('bayar').value) || 0;
            if (bayar < total) {
                alert('Pembayaran kurang!');
                e.preventDefault();
                return false;
            }
        };

        document.querySelectorAll('.add-product').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama;
                const harga = parseFloat(this.dataset.harga);
                const stok = parseInt(this.dataset.stok);
                
                // Cek apakah produk sudah ada di keranjang
                let item = cart.find(item => item.id === id);
                if (item) {
                    if (item.qty < stok) {
                        item.qty++;
                    } else {
                        alert('Stok tidak mencukupi!');
                        return;
                    }
                } else {
                    cart.push({
                        id: id,
                        nama: nama,
                        harga: harga,
                        qty: 1
                    });
                }
                
                updateCart();
            });
        });

        document.getElementById('bayar').addEventListener('input', function() {
            const bayar = parseFloat(this.value) || 0;
            const kembalian = bayar - total;
            document.getElementById('kembalian').value = kembalian;
        });

        function updateCart() {
            const cartDiv = document.getElementById('cart-items');
            cartDiv.innerHTML = '';
            total = 0;

            cart.forEach((item, index) => {
                const subtotal = item.harga * item.qty;
                total += subtotal;

                cartDiv.innerHTML += `
                    <div class="mb-3 border-bottom pb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${item.nama}</strong><br>
                                ${item.qty} x Rp ${item.harga.toLocaleString('id-ID')}
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="items[${index}][id]" value="${item.id}">
                        <input type="hidden" name="items[${index}][qty]" value="${item.qty}">
                        <input type="hidden" name="items[${index}][harga]" value="${item.harga}">
                    </div>
                `;
            });

            document.getElementById('total').value = total;
            document.getElementById('bayar').value = '';
            document.getElementById('kembalian').value = '';
        }

        function removeItem(index) {
            cart.splice(index, 1);
            updateCart();
        }
    </script>
</body>
</html> 