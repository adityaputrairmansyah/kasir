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

// Cek role admin
if($user['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Ambil data kategori
$stmt = $db->query("SELECT * FROM kategori ORDER BY nama_kategori");
$kategori = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Sistem Kasir</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Kategori</a>
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
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                    if($_GET['success'] == 'tambah') echo "Kategori berhasil ditambahkan!";
                    elseif($_GET['success'] == 'edit') echo "Kategori berhasil diupdate!";
                    elseif($_GET['success'] == 'hapus') echo "Kategori berhasil dihapus!";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Kategori</h5>
                <a href="tambah.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Kategori
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Nama Kategori</th>
                                <th class="text-center">Jumlah Produk</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($kategori as $index => $k): 
                                // Hitung jumlah produk per kategori
                                $stmt = $db->prepare("SELECT COUNT(*) as total FROM produk WHERE id_kategori = ?");
                                $stmt->execute([$k['id']]);
                                $jumlah_produk = $stmt->fetch()['total'];
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $index + 1 ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($k['nama_kategori']) ?></td>
                                <td class="text-center"><?php echo $jumlah_produk ?> produk</td>
                                <td class="text-center">
                                    <a href="edit.php?id=<?php echo $k['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if($jumlah_produk == 0): ?>
                                        <a href="hapus.php?id=<?php echo $k['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                disabled 
                                                data-bs-toggle="tooltip" 
                                                title="Kategori tidak dapat dihapus karena masih memiliki <?php echo $jumlah_produk ?> produk">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($kategori)): ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data kategori</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 