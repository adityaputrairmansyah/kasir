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
    <title>Struk - <?php echo $transaksi['no_transaksi'] ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-1 { margin-bottom: 10px; }
        .divider { 
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        table { width: 100%; }
        .print-area { max-width: 300px; margin: 0 auto; }
        @media print {
            @page { margin: 0; }
            body { margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-area">
        <div class="text-center mb-1">
            <h3 style="margin:0">TOKO SAYA</h3>
            <p style="margin:0">Jl. Contoh No. 123</p>
            <p style="margin:0">Telp: 081234567890</p>
        </div>

        <div class="divider"></div>

        <table class="mb-1">
            <tr>
                <td>No: <?php echo $transaksi['no_transaksi'] ?></td>
                <td class="text-right"><?php echo date('d/m/Y H:i', strtotime($transaksi['tanggal'])) ?></td>
            </tr>
            <tr>
                <td>Kasir: <?php echo $transaksi['nama_lengkap'] ?? 'Admin' ?></td>
            </tr>
        </table>

        <div class="divider"></div>

        <table class="mb-1">
            <?php foreach($detail as $d): ?>
            <tr>
                <td colspan="3"><?php echo $d['nama_produk'] ?></td>
            </tr>
            <tr>
                <td><?php echo $d['jumlah'] ?> x</td>
                <td>Rp <?php echo number_format($d['harga'], 0, ',', '.') ?></td>
                <td class="text-right">Rp <?php echo number_format($d['subtotal'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <div class="divider"></div>

        <table class="mb-1">
            <tr>
                <td>Total</td>
                <td class="text-right">Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td>Bayar</td>
                <td class="text-right">Rp <?php echo number_format($transaksi['bayar'], 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td>Kembalian</td>
                <td class="text-right">Rp <?php echo number_format($transaksi['kembalian'], 0, ',', '.') ?></td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="text-center">
            <p>Terima kasih atas kunjungan Anda</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar</p>
        </div>

        <div class="no-print text-center" style="margin-top: 20px;">
            <button onclick="window.print()">Cetak</button>
        </div>
    </div>
</body>
</html> 