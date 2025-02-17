<?php
session_start();
require_once 'config/database.php';

// Cek login
if(!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit();
}

// Ambil data user yang login
$user = $_SESSION['user'];

// Jika role kasir mencoba akses halaman admin, redirect ke transaksi
if($user['role'] == 'kasir' && strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false) {
    header("Location: transaksi/");
    exit();
}

// Ambil statistik stok per produk
if($user['role'] == 'admin') {
    // Admin melihat semua stok produk
    $stmt = $db->query("SELECT p.kode_produk,
                               p.nama_produk,
                               k.nama_kategori,
                               p.stok
                        FROM produk p
                        LEFT JOIN kategori k ON p.id_kategori = k.id
                        ORDER BY k.nama_kategori, p.nama_produk");
} else {
    // Kasir hanya melihat stok yang tersedia
    $stmt = $db->query("SELECT p.kode_produk,
                               p.nama_produk,
                               k.nama_kategori,
                               p.stok
                        FROM produk p
                        LEFT JOIN kategori k ON p.id_kategori = k.id
                        WHERE p.stok > 0
                        ORDER BY k.nama_kategori, p.nama_produk");
}
$stok_produk = $stmt->fetchAll();

// Total semua stok
$total_produk = array_sum(array_column($stok_produk, 'stok'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tambahkan Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Sistem Kasir</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if($user['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($user['nama_lengkap']) ?> (<?php echo ucfirst($user['role']) ?>)
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if($user['role'] == 'admin'): ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>Selamat Datang, <?php echo htmlspecialchars($user['nama_lengkap']); ?></h2>
                <p>Anda login sebagai <?php echo ucfirst($user['role']); ?></p>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Menu Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="produk/" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-box"></i> Kelola Produk
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="kategori/" class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-tags"></i> Kelola Kategori
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="users/" class="btn btn-info w-100 mb-2">
                                    <i class="fas fa-users"></i> Kelola Users
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="transaksi/" class="btn btn-warning w-100 mb-2">
                                    <i class="fas fa-cash-register"></i> Laporan Keuangan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Stok Produk</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary active" data-chart-type="bar">Bar Chart</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-chart-type="line">Line Chart</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-chart-type="pie">Pie Chart</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select class="form-select" id="kategoriFilter">
                                <option value="">Semua Kategori</option>
                                <?php 
                                $kategori_list = array_unique(array_column($stok_produk, 'nama_kategori'));
                                foreach($kategori_list as $kategori): 
                                ?>
                                    <option value="<?php echo $kategori ?>"><?php echo $kategori ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="height: 400px;">
                            <canvas id="stokChart"></canvas>
                        </div>
                        <div class="text-end mt-3">
                            <h5>Total Stok: <span class="badge bg-primary"><?php echo number_format($total_produk) ?> unit</span></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('stokChart').getContext('2d');
        let currentChart = null;
        
        // Data dari PHP
        const stokData = <?php echo json_encode($stok_produk); ?>;
        
        // Warna yang lebih menarik dan konsisten
        const colors = [
            'rgba(255, 99, 132, 0.5)',   // Merah
            'rgba(54, 162, 235, 0.5)',   // Biru
            'rgba(255, 206, 86, 0.5)',   // Kuning
            'rgba(75, 192, 192, 0.5)',   // Tosca
            'rgba(153, 102, 255, 0.5)',  // Ungu
            'rgba(255, 159, 64, 0.5)',   // Orange
        ];

        function createChart(type = 'bar', filteredData = stokData) {
            if (currentChart) {
                currentChart.destroy();
            }

            const labels = filteredData.map(item => `${item.nama_produk} (${item.kode_produk})`);
            const values = filteredData.map(item => item.stok);
            const backgroundColors = values.map((_, i) => colors[i % colors.length]);
            const borderColors = backgroundColors.map(color => color.replace('0.5', '1'));

            const config = {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Stok Produk',
                        data: values,
                        backgroundColor: type === 'line' ? colors[0] : backgroundColors,
                        borderColor: type === 'line' ? borderColors[0] : borderColors,
                        borderWidth: 2,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: type === 'pie',
                            position: 'right'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = type === 'pie' ? context.label + ': ' : '';
                                    label += context.parsed.y || context.parsed + ' unit';
                                    return label;
                                }
                            }
                        }
                    },
                    scales: type !== 'pie' ? {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah Stok',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: {
                                display: false
                            }
                        }
                    } : {}
                }
            };

            currentChart = new Chart(ctx, config);
        }

        // Filter berdasarkan kategori
        document.getElementById('kategoriFilter').addEventListener('change', function(e) {
            const kategori = e.target.value;
            const filteredData = kategori ? 
                stokData.filter(item => item.nama_kategori === kategori) : 
                stokData;
            createChart(document.querySelector('.btn-group .active').dataset.chartType, filteredData);
        });

        // Switch tipe chart
        document.querySelectorAll('[data-chart-type]').forEach(button => {
            button.addEventListener('click', function(e) {
                document.querySelectorAll('[data-chart-type]').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                const kategori = document.getElementById('kategoriFilter').value;
                const filteredData = kategori ? 
                    stokData.filter(item => item.nama_kategori === kategori) : 
                    stokData;
                createChart(this.dataset.chartType, filteredData);
            });
        });

        // Buat chart awal
        createChart('bar');
    });
    </script>

    <style>
    .card-header .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .card-header .btn-group .btn.active {
        background-color: #0d6efd;
        color: white;
    }
    </style>
</body>
</html> 